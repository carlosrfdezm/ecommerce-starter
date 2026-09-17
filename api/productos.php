<?php
// =====================================================
// API: PRODUCTOS CON BÚSQUEDA, FILTROS Y ORDENAMIENTO
// =====================================================
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

error_reporting(0);
ini_set('display_errors', 0);

require_once '../includes/config.php';

function jsonError($message) {
    echo json_encode(['error' => $message, 'success' => false]);
    exit;
}

try {
    // =====================================================
    // CONSULTA BASE
    // =====================================================
    $sql = "SELECT 
            p.*, 
            c.nombre as categoria_nombre,
            c.icono as categoria_icono,
            p.total_vendido,
            (p.stock - COALESCE((
                SELECT SUM(r.cantidad) 
                FROM reservas_stock r 
                WHERE r.producto_id = p.id 
                  AND r.estado = 'activa' 
                  AND r.expira_en > NOW()
            ), 0)) as stock_disponible
        FROM productos p 
        LEFT JOIN categorias c ON p.categoria_id = c.id 
        WHERE p.activo = 1";
    
    $params = [];
    $filters = [];
    
    // =====================================================
    // 1. BÚSQUEDA POR TEXTO
    // =====================================================
    if (isset($_GET['buscar']) && !empty($_GET['buscar'])) {
        $buscar = '%' . $_GET['buscar'] . '%';
        $filters[] = "(p.nombre LIKE ? OR p.descripcion LIKE ?)";
        $params[] = $buscar;
        $params[] = $buscar;
    }
    
    // =====================================================
    // 2. FILTRO POR CATEGORÍA (incluye subcategorías)
    // =====================================================
    if (isset($_GET['categoria']) && !empty($_GET['categoria']) && is_numeric($_GET['categoria'])) {
        $cat_id = intval($_GET['categoria']);
        
        // Obtener la categoría y sus subcategorías
        $stmt_sub = $pdo->prepare("SELECT id FROM categorias WHERE id = ? OR padre_id = ?");
        $stmt_sub->execute([$cat_id, $cat_id]);
        $ids_categorias = $stmt_sub->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($ids_categorias)) {
            $ids_categorias = [$cat_id];
        }
        
        // Convertir a placeholders (?, ?, ?)
        $placeholders = implode(',', array_fill(0, count($ids_categorias), '?'));
        $filters[] = "p.categoria_id IN ($placeholders)";
        
        foreach ($ids_categorias as $id) {
            $params[] = $id;
        }
    }
    
    // =====================================================
    // 3. FILTRO POR DESTACADOS
    // =====================================================
    if (isset($_GET['destacados']) && $_GET['destacados'] === 'true') {
        $filters[] = "p.destacado = 1";
    }
    
    // =====================================================
    // 4. FILTRO POR PROMOCIÓN
    // =====================================================
    if (isset($_GET['promocion']) && $_GET['promocion'] === 'true') {
        $filters[] = "p.en_promocion = 1";
    }
    
    // =====================================================
    // 5. FILTRO POR STOCK DISPONIBLE
    // =====================================================
    if (isset($_GET['stock']) && $_GET['stock'] === 'disponible') {
        $filters[] = "p.stock > 0";
    }
    
    // Aplicar filtros
    if (!empty($filters)) {
        $sql .= " AND " . implode(" AND ", $filters);
    }
    
    // =====================================================
    // 6. ORDENAMIENTO
    // =====================================================
    $orden = isset($_GET['orden']) ? $_GET['orden'] : 'destacado';
    
    switch ($orden) {
        case 'mas_vendidos':
            $sql .= " ORDER BY total_vendido DESC";
            break;
        case 'ultimos':
            $sql .= " ORDER BY p.created_at DESC";
            break;
        case 'promociones':
            $sql .= " ORDER BY p.en_promocion DESC, p.precio_promocion ASC";
            break;
        case 'precio_asc':
            $sql .= " ORDER BY p.precio ASC";
            break;
        case 'precio_desc':
            $sql .= " ORDER BY p.precio DESC";
            break;
        case 'nombre_asc':
            $sql .= " ORDER BY p.nombre ASC";
            break;
        case 'nombre_desc':
            $sql .= " ORDER BY p.nombre DESC";
            break;
        case 'destacado':
        default:
            $sql .= " ORDER BY p.destacado DESC, p.created_at DESC";
            break;
    }
    
    // =====================================================
    // 7. LÍMITE DE RESULTADOS
    // =====================================================
    if (isset($_GET['limite']) && is_numeric($_GET['limite'])) {
        $sql .= " LIMIT " . intval($_GET['limite']);
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $productos = $stmt->fetchAll();

    // Si se filtró por categoría, devolver el nombre de esa categoría
    $categoria_info = null;
    if (isset($_GET['categoria']) && is_numeric($_GET['categoria'])) {
        $stmt_cat = $pdo->prepare("SELECT id, nombre, icono FROM categorias WHERE id = ?");
        $stmt_cat->execute([intval($_GET['categoria'])]);
        $categoria_info = $stmt_cat->fetch();
    }

    echo json_encode([
        'success' => true,
        'total' => count($productos),
        'categoria' => $categoria_info,
        'productos' => $productos
    ]);
    
} catch (Exception $e) {
    jsonError($e->getMessage());
}
?>