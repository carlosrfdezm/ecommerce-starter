<?php
// =====================================================
// API: CREAR PEDIDO - VERSIÓN SIMPLE (SIN STRIPE)
// =====================================================
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

error_reporting(0);
ini_set('display_errors', 0);

// =====================================================
// 1. RECIBIR DATOS
// =====================================================
$input = file_get_contents('php://input');

if (empty($input)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'No se recibieron datos']);
    exit;
}

$data = json_decode($input, true);

if ($data === null) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Error al decodificar JSON']);
    exit;
}

// =====================================================
// 2. VALIDAR DATOS
// =====================================================
$nombre = trim($data['nombre'] ?? '');
$email = trim($data['email'] ?? '');
$telefono = trim($data['telefono'] ?? '');
$direccion = trim($data['direccion'] ?? '');
$items = $data['items'] ?? [];
$total = floatval($data['total'] ?? 0);

if (empty($nombre) || empty($email) || empty($direccion)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Faltan datos del cliente']);
    exit;
}

if (empty($items)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'El carrito está vacío']);
    exit;
}

// =====================================================
// 3. PROCESAR PEDIDO
// =====================================================
try {
    // Generar número de pedido
    $numero_pedido = 'PED-' . date('Ymd') . '-' . rand(1000, 9999);
    
    // =====================================================
    // 4. DEVOLVER RESPUESTA
    // =====================================================
    echo json_encode([
        'success' => true,
        'pedido_id' => rand(1, 9999),
        'numero_pedido' => $numero_pedido,
        'message' => '✅ Pedido creado correctamente',
        'debug' => [
            'nombre' => $nombre,
            'email' => $email,
            'direccion' => $direccion,
            'total' => $total,
            'items' => count($items)
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error: ' . $e->getMessage()
    ]);
}
?>