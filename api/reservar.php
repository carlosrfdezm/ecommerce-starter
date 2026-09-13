<?php
// =====================================================
// API: RESERVAR STOCK AL AÑADIR AL CARRITO
// =====================================================
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../includes/config.php';

// -----------------------------------------------------
// Limpieza automática de reservas canceladas viejas
// (máximo 1 vez cada hora, sin cron)
// -----------------------------------------------------
$lock_file = sys_get_temp_dir() . '/limpieza_canceladas.lock';
$ahora = time();
$ultima = file_exists($lock_file) ? intval(file_get_contents($lock_file)) : 0;

if ($ahora - $ultima > 3600) {  // 3600s = 1 hora
    file_put_contents($lock_file, $ahora);
    
    try {
        // Borrar canceladas de más de 7 días
        $pdo->exec("
            DELETE FROM reservas_stock 
            WHERE estado = 'cancelada' 
              AND creada_en < DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");
    } catch (Exception $e) {
        // Silencioso: si falla, no rompemos la reserva
        error_log('Error limpieza reservas: ' . $e->getMessage());
    }
}

const RESERVA_MINUTOS = 30;

function responder($success, $mensaje = '', $extra = []) {
    echo json_encode(array_merge([
        'success' => $success,
        'mensaje' => $mensaje
    ], $extra));
    exit;
}

// -----------------------------------------------------
// Leer datos de entrada
// -----------------------------------------------------
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) $input = $_POST;

$producto_id = isset($input['producto_id']) ? intval($input['producto_id']) : 0;
$cantidad    = isset($input['cantidad'])    ? intval($input['cantidad'])    : 1;

if ($producto_id <= 0 || $cantidad <= 0) {
    responder(false, 'Datos inválidos');
}

// -----------------------------------------------------
// Identificar usuario o sesión
// -----------------------------------------------------
$usuario_id = isset($_SESSION['usuario_id']) ? intval($_SESSION['usuario_id']) : null;

// Si no hay sesión PHP, usamos un ID de sesión enviado por el cliente
$sesion_id = null;
if (isset($input['sesion_id']) && !empty($input['sesion_id'])) {
    $sesion_id = preg_replace('/[^a-zA-Z0-9_\-]/', '', $input['sesion_id']);
}
if (!$sesion_id) {
    $sesion_id = session_id() ?: uniqid('anon_', true);
}

try {
    $pdo->beginTransaction();

    // -----------------------------------------------------
    // 1. Bloquear fila del producto para evitar race conditions
    // -----------------------------------------------------
    $stmt = $pdo->prepare("SELECT id, nombre, stock FROM productos WHERE id = ? AND activo = 1 FOR UPDATE");
    $stmt->execute([$producto_id]);
    $producto = $stmt->fetch();

    if (!$producto) {
        $pdo->rollBack();
        responder(false, 'Producto no encontrado');
    }

    // -----------------------------------------------------
    // 2. Calcular stock disponible = stock - reservas activas
    // -----------------------------------------------------
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(cantidad), 0) as reservado
        FROM reservas_stock
        WHERE producto_id = ? 
          AND estado = 'activa' 
          AND expira_en > NOW()
    ");
    $stmt->execute([$producto_id]);
    $reservado = intval($stmt->fetchColumn());

    $stock_disponible = intval($producto['stock']) - $reservado;

    // -----------------------------------------------------
    // 3. ¿Ya tiene una reserva activa este usuario/sesión?
    // -----------------------------------------------------
    $stmt = $pdo->prepare("
        SELECT id, cantidad FROM reservas_stock
        WHERE producto_id = ? 
          AND sesion_id = ? 
          AND estado = 'activa' 
          AND expira_en > NOW()
    ");
    $stmt->execute([$producto_id, $sesion_id]);
    $reserva_existente = $stmt->fetch();

    if ($reserva_existente) {
        // Actualizar la reserva existente (sumar cantidad)
        $nueva_cantidad = intval($reserva_existente['cantidad']) + $cantidad;

        if ($nueva_cantidad > $stock_disponible + intval($reserva_existente['cantidad'])) {
            $pdo->rollBack();
            responder(false, 'No hay suficiente stock disponible', [
                'stock_disponible' => $stock_disponible
            ]);
        }

        $stmt = $pdo->prepare("
            UPDATE reservas_stock 
            SET cantidad = ?, expira_en = DATE_ADD(NOW(), INTERVAL ? MINUTE)
            WHERE id = ?
        ");
        $stmt->execute([$nueva_cantidad, RESERVA_MINUTOS, $reserva_existente['id']]);

        $pdo->commit();
        responder(true, 'Reserva actualizada', [
            'reserva_id'       => intval($reserva_existente['id']),
            'cantidad'         => $nueva_cantidad,
            'stock_disponible' => $stock_disponible,
            'expira_en'        => date('c', strtotime('+' . RESERVA_MINUTOS . ' minutes'))
        ]);
    }

    // -----------------------------------------------------
    // 4. Crear reserva nueva
    // -----------------------------------------------------
    if ($cantidad > $stock_disponible) {
        $pdo->rollBack();
        responder(false, 'No hay suficiente stock disponible', [
            'stock_disponible' => $stock_disponible,
            'solicitado'       => $cantidad
        ]);
    }

    $stmt = $pdo->prepare("
        INSERT INTO reservas_stock 
            (producto_id, usuario_id, sesion_id, cantidad, expira_en)
        VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL ? MINUTE))
    ");
    $stmt->execute([
        $producto_id,
        $usuario_id,
        $sesion_id,
        $cantidad,
        RESERVA_MINUTOS
    ]);

    $reserva_id = $pdo->lastInsertId();

    $pdo->commit();

    responder(true, 'Stock reservado', [
        'reserva_id'       => intval($reserva_id),
        'cantidad'         => $cantidad,
        'stock_disponible' => $stock_disponible,
        'expira_en'        => date('c', strtotime('+' . RESERVA_MINUTOS . ' minutes'))
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    responder(false, 'Error: ' . $e->getMessage());
}