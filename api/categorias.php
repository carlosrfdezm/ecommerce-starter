<?php
// =====================================================
// API: CATEGORÍAS CON JERARQUÍA
// =====================================================
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

error_reporting(0);
ini_set('display_errors', 0);

require_once '../includes/config.php';

try {
    // Obtener categorías padre (sin padre_id)
    $stmt = $pdo->query("
        SELECT c.*, 
               (SELECT COUNT(*) FROM productos p WHERE p.categoria_id = c.id AND p.activo = 1) as total_productos
        FROM categorias c 
        WHERE c.padre_id IS NULL 
        ORDER BY c.orden ASC, c.nombre ASC
    ");
    $categorias_padre = $stmt->fetchAll();
    
    // Para cada categoría padre, obtener sus subcategorías
    foreach ($categorias_padre as &$padre) {
        $stmt = $pdo->prepare("
            SELECT c.*, 
                   (SELECT COUNT(*) FROM productos p WHERE p.categoria_id = c.id AND p.activo = 1) as total_productos
            FROM categorias c 
            WHERE c.padre_id = ? 
            ORDER BY c.orden ASC, c.nombre ASC
        ");
        $stmt->execute([$padre['id']]);
        $padre['subcategorias'] = $stmt->fetchAll();
    }
    
    echo json_encode([
        'success' => true,
        'total' => count($categorias_padre),
        'categorias' => $categorias_padre
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>