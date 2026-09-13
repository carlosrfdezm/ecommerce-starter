<?php
// =====================================================
// API: CREAR PEDIDO CON STRIPE OBLIGATORIO
// =====================================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// -----------------------------------------------------
// Funciones helper
// -----------------------------------------------------
function sendError($message, $code = 400) {
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $message]);
    exit;
}

function sendSuccess($data) {
    echo json_encode(array_merge(['success' => true], $data));
    exit;
}

// -----------------------------------------------------
// Configuración
// -----------------------------------------------------
require_once '../../includes/config.php';

// -----------------------------------------------------
// Cargar Stripe
// -----------------------------------------------------
$stripe_loaded = false;
$rutas_stripe = [
    '../../vendor/stripe/stripe-php-master/init.php',
    '../../vendor/stripe/stripe-php-master/lib/Stripe.php',
    '../../vendor/autoload.php',
    $_SERVER['DOCUMENT_ROOT'] . '/vendor/stripe/stripe-php-master/init.php',
    $_SERVER['DOCUMENT_ROOT'] . '/vendor/stripe/stripe-php-master/lib/Stripe.php',
    $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php',
];

foreach ($rutas_stripe as $ruta) {
    if (file_exists($ruta)) {
        require_once $ruta;
        $stripe_loaded = true;
        break;
    }
}

if (!$stripe_loaded) {
    sendError('Error crítico: Stripe no está instalado.');
}

if (!class_exists('\Stripe\Stripe')) {
    sendError('Error crítico: La librería de Stripe no se cargó correctamente.');
}

$stripe_secret_key = STRIPE_SECRET_KEY;

try {
    \Stripe\Stripe::setApiKey($stripe_secret_key);
} catch (Exception $e) {
    sendError('Error al configurar Stripe: ' . $e->getMessage());
}

// -----------------------------------------------------
// Recibir datos
// -----------------------------------------------------
$input = file_get_contents('php://input');

if (empty($input)) {
    sendError('No se recibieron datos');
}

$data = json_decode($input, true);

if ($data === null) {
    sendError('Error al decodificar JSON');
}

// -----------------------------------------------------
// Validar datos
// -----------------------------------------------------
$usuario_id = isset($data['usuario_id']) && $data['usuario_id'] ? intval($data['usuario_id']) : null;
$sesion_id  = isset($data['sesion_id']) ? trim($data['sesion_id']) : '';
$nombre     = trim($data['nombre'] ?? '');
$email      = trim($data['email'] ?? '');
$telefono   = trim($data['telefono'] ?? '');
$direccion  = trim($data['direccion'] ?? '');
$items      = $data['items'] ?? [];
$total      = floatval($data['total'] ?? 0);

if (empty($nombre) || empty($email) || empty($direccion)) {
    sendError('Faltan datos del cliente');
}

if (empty($items)) {
    sendError('El carrito está vacío');
}

if ($total <= 0) {
    sendError('El total del pedido debe ser mayor a 0');
}

// =====================================================
// Procesar pedido (SIN descontar stock todavía)
// =====================================================
try {
    $pdo->beginTransaction();
    
    // -----------------------------------------------------
    // 1. Verificar stock disponible (con reservas de otros)
    // -----------------------------------------------------
    foreach ($items as $item) {
        $prod_id = intval($item['id']);
        $cant    = intval($item['cantidad']);
        
        $stmt = $pdo->prepare("SELECT stock FROM productos WHERE id = ? AND activo = 1 FOR UPDATE");
        $stmt->execute([$prod_id]);
        $prod = $stmt->fetch();
        
        if (!$prod) {
            throw new Exception("Producto #$prod_id no encontrado");
        }
        
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(cantidad), 0) 
            FROM reservas_stock 
            WHERE producto_id = ? 
              AND estado = 'activa' 
              AND expira_en > NOW()
              AND sesion_id != ?
        ");
        $stmt->execute([$prod_id, $sesion_id]);
        $reservado_por_otros = intval($stmt->fetchColumn());
        
        $stock_disponible = intval($prod['stock']) - $reservado_por_otros;
        
        if ($cant > $stock_disponible) {
            throw new Exception("Sin stock suficiente de '{$item['nombre']}'. Disponible: $stock_disponible");
        }
    }
    
    // -----------------------------------------------------
    // 2. Crear el pedido en estado 'pendiente'
    // -----------------------------------------------------
    $numero_pedido = 'PED-' . date('Ymd') . '-' . rand(1000, 9999);
    
    $stmt = $pdo->prepare("
        INSERT INTO pedidos (
            usuario_id, numero_pedido, cliente_nombre, cliente_email, 
            cliente_telefono, cliente_direccion, total, 
            metodo_pago, estado
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 'stripe', 'pendiente')
    ");
    
    $stmt->execute([
        $usuario_id, $numero_pedido, $nombre, $email,
        $telefono, $direccion, $total
    ]);
    
    $pedido_id = $pdo->lastInsertId();
    
    // -----------------------------------------------------
    // 3. Guardar detalles (SIN tocar stock ni total_vendido)
    // -----------------------------------------------------
    foreach ($items as $item) {
        $stmt = $pdo->prepare("
            INSERT INTO detalles_pedido (
                pedido_id, producto_id, nombre_producto, 
                precio_unitario, cantidad, subtotal
            ) VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $subtotal = floatval($item['precio']) * intval($item['cantidad']);
        $stmt->execute([
            $pedido_id,
            intval($item['id']),
            $item['nombre'],
            floatval($item['precio']),
            intval($item['cantidad']),
            $subtotal
        ]);
    }
    
    // -----------------------------------------------------
    // 4. Ligar las reservas de esta sesión al pedido
    // -----------------------------------------------------
    if (!empty($sesion_id)) {
        $stmt = $pdo->prepare("
            UPDATE reservas_stock 
            SET pedido_id = ?
            WHERE sesion_id = ? AND estado = 'activa' AND expira_en > NOW()
        ");
        $stmt->execute([$pedido_id, $sesion_id]);
    }
    
    // -----------------------------------------------------
    // 5. Crear PaymentIntent en Stripe
    // -----------------------------------------------------
    try {
        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => intval($total * 100),
            'currency' => 'usd',
            'payment_method_types' => ['card'],
            'metadata' => [
                'pedido_id'     => $pedido_id,
                'numero_pedido' => $numero_pedido,
                'cliente'       => $nombre,
                'email'         => $email
            ]
        ]);
        
        $stmt = $pdo->prepare("UPDATE pedidos SET stripe_payment_id = ? WHERE id = ?");
        $stmt->execute([$paymentIntent->id, $pedido_id]);
        
        $pdo->commit();
        
        sendSuccess([
            'pedido_id'     => $pedido_id,
            'numero_pedido' => $numero_pedido,
            'client_secret' => $paymentIntent->client_secret
        ]);
        
    } catch (\Stripe\Exception\CardException $e) {
        throw new Exception('Error en la tarjeta: ' . $e->getMessage());
    } catch (\Stripe\Exception\AuthenticationException $e) {
        throw new Exception('Error de autenticación con Stripe. Verifica tu API Key.');
    } catch (\Stripe\Exception\ApiConnectionException $e) {
        throw new Exception('Error de conexión con Stripe');
    } catch (\Stripe\Exception\ApiErrorException $e) {
        throw new Exception('Error en Stripe: ' . $e->getMessage());
    }
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    sendError($e->getMessage(), 500);
}