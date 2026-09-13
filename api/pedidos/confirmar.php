<?php
// =====================================================
// API: CONFIRMAR PAGO → CONVERTIR RESERVAS EN VENTA
// =====================================================
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../../includes/config.php';

function sendError($msg, $code = 400) {
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $msg]);
    exit;
}

function sendSuccess($data = []) {
    echo json_encode(array_merge(['success' => true], $data));
    exit;
}

// -----------------------------------------------------
// 1. Cargar Stripe
// -----------------------------------------------------
$stripe_loaded = false;
$rutas_stripe = [
    '../../vendor/stripe/stripe-php-master/init.php',
    '../../vendor/autoload.php',
];
foreach ($rutas_stripe as $ruta) {
    if (file_exists($ruta)) { require_once $ruta; $stripe_loaded = true; break; }
}
if (!$stripe_loaded) sendError('Stripe no disponible');

$stripe_secret_key = STRIPE_SECRET_KEY;
\Stripe\Stripe::setApiKey($stripe_secret_key);

// -----------------------------------------------------
// 2. Leer pedido_id
// -----------------------------------------------------
$input = json_decode(file_get_contents('php://input'), true);
$pedido_id = isset($input['pedido_id']) ? intval($input['pedido_id']) : 0;
if ($pedido_id <= 0) sendError('pedido_id inválido');

try {
    // -----------------------------------------------------
    // 3. Cargar pedido
    // -----------------------------------------------------
    $stmt = $pdo->prepare("SELECT * FROM pedidos WHERE id = ?");
    $stmt->execute([$pedido_id]);
    $pedido = $stmt->fetch();
    
    if (!$pedido) sendError('Pedido no encontrado');
    
    // Si ya está pagado, no hacemos nada (idempotente)
    if ($pedido['estado'] === 'pagado') {
        sendSuccess(['mensaje' => 'Pedido ya confirmado', 'numero_pedido' => $pedido['numero_pedido']]);
    }
    
    if (empty($pedido['stripe_payment_id'])) sendError('El pedido no tiene PaymentIntent');
    
    // -----------------------------------------------------
    // 4. Verificar con Stripe que el pago está confirmado
    // -----------------------------------------------------
    try {
        $intent = \Stripe\PaymentIntent::retrieve($pedido['stripe_payment_id']);
    } catch (Exception $e) {
        sendError('Error consultando Stripe: ' . $e->getMessage());
    }
    
    if ($intent->status !== 'succeeded') {
        sendError('El pago no está confirmado en Stripe. Estado: ' . $intent->status);
    }
    
    // Verificar que el importe coincide
    if (intval($intent->amount) !== intval($pedido['total'] * 100)) {
        sendError('El importe del pago no coincide con el pedido');
    }
    
    // -----------------------------------------------------
    // 5. Descontar stock e incrementar total_vendido
    // -----------------------------------------------------
    $pdo->beginTransaction();
    
    // Cargar detalles del pedido
    $stmt = $pdo->prepare("SELECT * FROM detalles_pedido WHERE pedido_id = ?");
    $stmt->execute([$pedido_id]);
    $detalles = $stmt->fetchAll();
    
    foreach ($detalles as $det) {
        // Bloquear fila del producto
        $stmt = $pdo->prepare("SELECT stock FROM productos WHERE id = ? FOR UPDATE");
        $stmt->execute([$det['producto_id']]);
        $prod = $stmt->fetch();
        
        if (!$prod) {
            throw new Exception("Producto #{$det['producto_id']} desapareció");
        }
        
        if ($prod['stock'] < $det['cantidad']) {
            // Sobreventa: hacemos rollback y avisamos
            throw new Exception("Stock insuficiente para '{$det['nombre_producto']}'. Disponible: {$prod['stock']}, solicitado: {$det['cantidad']}. Contacta con soporte.");
        }
        
        // Descontar stock
        $stmt = $pdo->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?");
        $stmt->execute([$det['cantidad'], $det['producto_id']]);
        
        // Incrementar total_vendido
        $stmt = $pdo->prepare("UPDATE productos SET total_vendido = total_vendido + ? WHERE id = ?");
        $stmt->execute([$det['cantidad'], $det['producto_id']]);
    }
    
    // -----------------------------------------------------
    // 6. Convertir reservas en ventas
    // -----------------------------------------------------
    $stmt = $pdo->prepare("
        UPDATE reservas_stock 
        SET estado = 'convertida'
        WHERE pedido_id = ? AND estado = 'activa'
    ");
    $stmt->execute([$pedido_id]);
    
    // -----------------------------------------------------
    // 7. Marcar pedido como pagado
    // -----------------------------------------------------
    $stmt = $pdo->prepare("
        UPDATE pedidos 
        SET estado = 'pagado', fecha_pago = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$pedido_id]);
    
    $pdo->commit();
    
    sendSuccess([
        'numero_pedido' => $pedido['numero_pedido'],
        'mensaje' => 'Pedido confirmado correctamente'
    ]);
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    sendError($e->getMessage(), 500);
}