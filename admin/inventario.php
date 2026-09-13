<?php
// =====================================================
// ADMIN: INVENTARIO - VERSIÓN COMPLETA
// =====================================================
require_once '../includes/config.php';
require_once '../includes/auth.php';
verificarAdmin();

$mensaje = '';
$error = '';

// =====================================================
// ACTUALIZAR STOCK
// =====================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_stock') {
    $producto_id = (int)$_POST['producto_id'];
    $nuevo_stock = (int)$_POST['stock'];
    $motivo = trim($_POST['motivo'] ?? 'Actualización manual');
    
    if ($nuevo_stock < 0) {
        $error = 'El stock no puede ser negativo';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE productos SET stock = ? WHERE id = ?");
            $stmt->execute([$nuevo_stock, $producto_id]);
            $mensaje = '✅ Stock actualizado correctamente';
        } catch (Exception $e) {
            $error = 'Error al actualizar stock: ' . $e->getMessage();
        }
    }
}

// =====================================================
// OBTENER DATOS DE INVENTARIO
// =====================================================
$stmt = $pdo->query("
    SELECT 
        p.*, 
        c.nombre as categoria_nombre,
        (SELECT COUNT(*) FROM detalles_pedido dp WHERE dp.producto_id = p.id) as total_vendido
    FROM productos p
    LEFT JOIN categorias c ON p.categoria_id = c.id
    ORDER BY p.stock ASC
");
$productos = $stmt->fetchAll();

// Estadísticas de inventario
$total_productos = count($productos);
$stock_bajo = array_filter($productos, function($p) { return $p['stock'] < 10 && $p['activo'] == 1; });
$stock_agotado = array_filter($productos, function($p) { return $p['stock'] == 0 && $p['activo'] == 1; });
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario - Panel Administración</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <header class="content-header">
                <h1><i class="fas fa-boxes"></i> Inventario</h1>
            </header>
            
            <?php if ($mensaje): ?>
                <div class="alert alert-success"><?php echo $mensaje; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <!-- Estadísticas -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-number"><?php echo $total_productos; ?></span>
                        <span class="stat-label">Total productos</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-number"><?php echo count($stock_bajo); ?></span>
                        <span class="stat-label">Stock bajo (&lt; 10)</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon red">
                        <i class="fas fa-ban"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-number"><?php echo count($stock_agotado); ?></span>
                        <span class="stat-label">Agotados</span>
                    </div>
                </div>
            </div>
            
            <!-- Tabla de inventario -->
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-list"></i> Todos los productos</h2>
                    <span class="badge badge-info"><?php echo $total_productos; ?> productos</span>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Producto</th>
                                <th>Categoría</th>
                                <th>Precio</th>
                                <th>Stock</th>
                                <th>Vendidos</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productos as $producto): ?>
                                <tr>
                                    <td>#<?php echo $producto['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($producto['nombre']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($producto['categoria_nombre'] ?? '-'); ?></td>
                                    <td>$<?php echo number_format($producto['precio'], 2); ?></td>
                                    <td>
                                        <?php
                                        $stock = $producto['stock'];
                                        $stock_class = $stock == 0 ? 'badge-danger' : ($stock < 10 ? 'badge-warning' : 'badge-success');
                                        ?>
                                        <span class="badge <?php echo $stock_class; ?>">
                                            <?php echo $stock; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $producto['total_vendido'] ?? 0; ?></td>
                                    <td>
                                        <?php if ($producto['activo']): ?>
                                            <span class="badge badge-success">Activo</span>
                                        <?php else: ?>
                                            <span class="badge badge-muted">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn-sm btn-primary" onclick="openStockModal(<?php echo $producto['id']; ?>, '<?php echo htmlspecialchars($producto['nombre']); ?>', <?php echo $producto['stock']; ?>)">
                                            <i class="fas fa-edit"></i> Stock
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    
    <!-- =====================================================
    MODAL PARA ACTUALIZAR STOCK
    ===================================================== -->
    <div id="stockModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="stockModalTitle">Actualizar stock</h2>
                <button class="modal-close" onclick="closeStockModal()">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="update_stock">
                <input type="hidden" id="stockProductId" name="producto_id">
                
                <div class="form-group">
                    <label for="stockProductName">Producto</label>
                    <input type="text" id="stockProductName" disabled style="background:#f1f5f9;">
                </div>
                
                <div class="form-group">
                    <label for="stockValue">Nuevo stock</label>
                    <input type="number" id="stockValue" name="stock" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="stockMotivo">Motivo (opcional)</label>
                    <input type="text" id="stockMotivo" name="motivo" placeholder="Ej: Reposición de inventario">
                </div>
                
                <div class="modal-actions">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save"></i> Actualizar stock
                    </button>
                    <button type="button" class="btn-secondary" onclick="closeStockModal()">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <style>
        .stat-icon.red {
            background: #fecaca;
            color: #dc2626;
        }
        .stat-icon.orange {
            background: #fef3c7;
            color: #d97706;
        }
        .badge-warning {
            background: #fef3c7;
            color: #92400e;
        }
    </style>
    
    <script>
        function openStockModal(productId, productName, currentStock) {
            document.getElementById('stockProductId').value = productId;
            document.getElementById('stockProductName').value = productName + ' (Stock actual: ' + currentStock + ')';
            document.getElementById('stockValue').value = currentStock;
            document.getElementById('stockModal').style.display = 'flex';
        }
        
        function closeStockModal() {
            document.getElementById('stockModal').style.display = 'none';
        }
        
        // Cerrar modal al hacer clic fuera
        document.getElementById('stockModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeStockModal();
            }
        });
    </script>
</body>
</html>