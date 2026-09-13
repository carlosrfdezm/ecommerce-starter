<?php
// =====================================================
// ADMIN: DASHBOARD
// =====================================================
require_once '../includes/config.php';
require_once '../includes/auth.php';
verificarAdmin();

// Estadísticas
$stats = [];

// Total de productos
$stmt = $pdo->query("SELECT COUNT(*) as total FROM productos");
$stats['productos'] = $stmt->fetch()['total'];

// Total de pedidos
$stmt = $pdo->query("SELECT COUNT(*) as total FROM pedidos");
$stats['pedidos'] = $stmt->fetch()['total'];

// Total de ingresos
$stmt = $pdo->query("SELECT SUM(total) as total FROM pedidos WHERE estado != 'cancelado'");
$stats['ingresos'] = $stmt->fetch()['total'] ?? 0;

// Total de clientes (email únicos)
$stmt = $pdo->query("SELECT COUNT(DISTINCT cliente_email) as total FROM pedidos");
$stats['clientes'] = $stmt->fetch()['total'];

// Últimos pedidos
$stmt = $pdo->query("SELECT * FROM pedidos ORDER BY created_at DESC LIMIT 10");
$ultimos_pedidos = $stmt->fetchAll();

// Productos con poco stock
$stmt = $pdo->query("SELECT * FROM productos WHERE stock < 10 AND activo = 1 ORDER BY stock ASC LIMIT 10");
$stock_bajo = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Panel Administración</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <!-- ===================================================== -->
        <!-- SIDEBAR -->
        <!-- ===================================================== -->
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- ===================================================== -->
        <!-- MAIN CONTENT -->
        <!-- ===================================================== -->
        <main class="main-content">
            <header class="content-header">
                <h1>Dashboard</h1>
                <span class="date-time">
                    <i class="fas fa-calendar-alt"></i>
                    <?php echo date('d/m/Y H:i'); ?>
                </span>
            </header>
            
            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-number"><?php echo $stats['productos']; ?></span>
                        <span class="stat-label">Productos</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-number"><?php echo $stats['pedidos']; ?></span>
                        <span class="stat-label">Pedidos</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon purple">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-number">$<?php echo number_format($stats['ingresos'], 2); ?></span>
                        <span class="stat-label">Ingresos</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-number"><?php echo $stats['clientes']; ?></span>
                        <span class="stat-label">Clientes</span>
                    </div>
                </div>
            </div>
            
            <!-- Últimos Pedidos -->
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-clock"></i> Últimos pedidos</h2>
                    <a href="pedidos.php" class="btn-link">Ver todos →</a>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#Pedido</th>
                                <th>Cliente</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($ultimos_pedidos) > 0): ?>
                                <?php foreach ($ultimos_pedidos as $pedido): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($pedido['numero_pedido']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($pedido['cliente_nombre']); ?></td>
                                        <td>$<?php echo number_format($pedido['total'], 2); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $pedido['estado']; ?>">
                                                <?php echo $pedido['estado']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($pedido['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">No hay pedidos aún</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Stock Bajo -->
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-exclamation-triangle"></i> Productos con stock bajo</h2>
                    <a href="inventario.php" class="btn-link">Ir a inventario →</a>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Stock</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($stock_bajo) > 0): ?>
                                <?php foreach ($stock_bajo as $producto): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                                        <td>
                                            <span class="badge badge-danger"><?php echo $producto['stock']; ?></span>
                                        </td>
                                        <td>
                                            <a href="inventario.php" class="btn-sm btn-primary">
                                                Actualizar stock
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center">✅ Todos los productos tienen stock suficiente</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>