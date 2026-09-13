<?php
// =====================================================
// API: LIBERAR RESERVA (total o parcial)
// =====================================================
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../includes/config.php';

function responder($success, $mensaje = '', $extra = []) {
    echo json_encode(array_merge([
        'success' => $success,
        'mensaje' => $mensaje
    ], $extra));
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) $input = $_POST;

$producto_id      = isset($input['producto_id'])      ? intval($input['producto_id'])      : 0;
$cantidad_liberar = isset($input['cantidad_liberar']) ? intval($input['cantidad_liberar']) : null;
$liberar_todo     = isset($input['todo']) && $input['todo'] === true;

$sesion_id = null;
if (isset($input['sesion_id']) && !empty($input['sesion_id'])) {
    $sesion_id = preg_replace('/[^a-zA-Z0-9_\-]/', '', $input['sesion_id']);
}
if (!$sesion_id) {
    $sesion_id = session_id() ?: '';
}

if (!$sesion_id) {
    responder(false, 'Sesión no válida');
}

try {
    // CASO 1: Liberar TODAS las reservas de esta sesión (vaciar carrito)
    if ($liberar_todo) {
        $stmt = $pdo->prepare("
            UPDATE reservas_stock 
            SET estado = 'cancelada'
            WHERE sesion_id = ? AND estado = 'activa'
        ");
        $stmt->execute([$sesion_id]);
        responder(true, 'Todas las reservas liberadas', [
            'liberadas' => $stmt->rowCount()
        ]);
    }

    if ($producto_id <= 0) {
        responder(false, 'Producto inválido');
    }

    // CASO 2: Liberar cantidad parcial
    if ($cantidad_liberar && $cantidad_liberar > 0) {
        // Buscar la reserva activa de este producto/sesión
        $stmt = $pdo->prepare("
            SELECT id, cantidad FROM reservas_stock 
            WHERE producto_id = ? AND sesion_id = ? AND estado = 'activa'
            ORDER BY id ASC LIMIT 1
        ");
        $stmt->execute([$producto_id, $sesion_id]);
        $reserva = $stmt->fetch();
        
        if (!$reserva) {
            responder(false, 'No hay reserva activa para este producto');
        }
        
        $nueva_cantidad = intval($reserva['cantidad']) - $cantidad_liberar;
        
        if ($nueva_cantidad <= 0) {
            // Cancelar toda la reserva
            $stmt = $pdo->prepare("UPDATE reservas_stock SET estado = 'cancelada' WHERE id = ?");
            $stmt->execute([$reserva['id']]);
        } else {
            // Reducir cantidad
            $stmt = $pdo->prepare("UPDATE reservas_stock SET cantidad = ? WHERE id = ?");
            $stmt->execute([$nueva_cantidad, $reserva['id']]);
        }
        
        responder(true, 'Reserva parcial liberada', [
            'cantidad_restante' => max(0, $nueva_cantidad)
        ]);
    }

    // CASO 3: Liberar TODA la reserva del producto (eliminar producto)
    $stmt = $pdo->prepare("
        UPDATE reservas_stock 
        SET estado = 'cancelada'
        WHERE producto_id = ? 
          AND sesion_id = ? 
          AND estado = 'activa'
    ");
    $stmt->execute([$producto_id, $sesion_id]);

    responder(true, 'Reserva liberada', [
        'liberadas' => $stmt->rowCount()
    ]);

} catch (Exception $e) {
    responder(false, 'Error: ' . $e->getMessage());
}