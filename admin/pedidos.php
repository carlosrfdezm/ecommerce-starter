<?php
// =====================================================
// ADMIN: PEDIDOS - VERSIÓN MEJORADA
// =====================================================
require_once '../includes/config.php';
require_once '../includes/auth.php';
verificarAdmin();

$mensaje = '';
$error = '';

// =====================================================
// ELIMINAR PEDIDO
// =====================================================
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    try {
        // Verificar que el pedido existe
        $stmt = $pdo->prepare("SELECT * FROM pedidos WHERE id = ?");
        $stmt->execute([$id]);
        $pedido = $stmt->fetch();
        
        if (!$pedido) {
            $error = 'Pedido no encontrado';
        } else {
            // Eliminar detalles del pedido primero (por la clave foránea)
            $stmt = $pdo->prepare("DELETE FROM detalles_pedido WHERE pedido_id = ?");
            $stmt->execute([$id]);
            
            // Eliminar el pedido
            $stmt = $pdo->prepare("DELETE FROM pedidos WHERE id = ?");
            $stmt->execute([$id]);
            
            $mensaje = '✅ Pedido #' . $pedido['numero_pedido'] . ' eliminado correctamente';
        }
    } catch (Exception $e) {
        $error = 'Error al eliminar el pedido: ' . $e->getMessage();
    }
}

// =====================================================
// ACTUALIZAR ESTADO DEL PEDIDO
// =====================================================
if (isset($_GET['update_status']) && isset($_GET['status'])) {
    $id = (int)$_GET['update_status'];
    $estado = $_GET['status'];
    
    $estados_validos = ['pendiente', 'pagado', 'enviado', 'entregado', 'cancelado'];
    
    if (in_array($estado, $estados_validos)) {
        $stmt = $pdo->prepare("UPDATE pedidos SET estado = ? WHERE id = ?");
        $stmt->execute([$estado, $id]);
        $mensaje = '✅ Estado del pedido actualizado correctamente';
    } else {
        $error = 'Estado no válido';
    }
}

// =====================================================
// VER DETALLE DEL PEDIDO
// =====================================================
$pedido_detalle = null;
$detalles_items = [];

if (isset($_GET['view'])) {
    $id = (int)$_GET['view'];
    
    $stmt = $pdo->prepare("SELECT * FROM pedidos WHERE id = ?");
    $stmt->execute([$id]);
    $pedido_detalle = $stmt->fetch();
    
    if ($pedido_detalle) {
        $stmt = $pdo->prepare("SELECT * FROM detalles_pedido WHERE pedido_id = ?");
        $stmt->execute([$id]);
        $detalles_items = $stmt->fetchAll();
    }
}

// =====================================================
// LISTAR PEDIDOS
// =====================================================
$filtro_estado = isset($_GET['estado']) ? $_GET['estado'] : '';

$sql = "SELECT * FROM pedidos";
$params = [];

if ($filtro_estado) {
    $sql .= " WHERE estado = ?";
    $params[] = $filtro_estado;
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pedidos = $stmt->fetchAll();

// Estadísticas de pedidos
$stmt = $pdo->query("SELECT estado, COUNT(*) as total FROM pedidos GROUP BY estado");
$estados_stats = [];
while ($row = $stmt->fetch()) {
    $estados_stats[$row['estado']] = $row['total'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos - Panel Administración</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <header class="content-header">
                <h1><i class="fas fa-shopping-bag"></i> Pedidos</h1>
            </header>
            
            <?php if ($mensaje): ?>
                <div class="alert alert-success"><?php echo $mensaje; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <!-- Filtros por estado -->
            <div class="filter-bar">
                <a href="pedidos.php" class="btn-filter <?php echo !$filtro_estado ? 'active' : ''; ?>">
                    Todos (<?php echo array_sum($estados_stats); ?>)
                </a>
                <a href="?estado=pendiente" class="btn-filter <?php echo $filtro_estado == 'pendiente' ? 'active' : ''; ?>">
                    Pendientes (<?php echo $estados_stats['pendiente'] ?? 0; ?>)
                </a>
                <a href="?estado=pagado" class="btn-filter <?php echo $filtro_estado == 'pagado' ? 'active' : ''; ?>">
                    Pagados (<?php echo $estados_stats['pagado'] ?? 0; ?>)
                </a>
                <a href="?estado=enviado" class="btn-filter <?php echo $filtro_estado == 'enviado' ? 'active' : ''; ?>">
                    Enviados (<?php echo $estados_stats['enviado'] ?? 0; ?>)
                </a>
                <a href="?estado=entregado" class="btn-filter <?php echo $filtro_estado == 'entregado' ? 'active' : ''; ?>">
                    Entregados (<?php echo $estados_stats['entregado'] ?? 0; ?>)
                </a>
                <a href="?estado=cancelado" class="btn-filter <?php echo $filtro_estado == 'cancelado' ? 'active' : ''; ?>">
                    Cancelados (<?php echo $estados_stats['cancelado'] ?? 0; ?>)
                </a>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#Pedido</th>
                                <th>Cliente</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($pedidos) > 0): ?>
                                <?php foreach ($pedidos as $pedido): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($pedido['numero_pedido']); ?></strong></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($pedido['cliente_nombre']); ?></strong><br>
                                            <small><?php echo htmlspecialchars($pedido['cliente_email']); ?></small>
                                        </td>
                                        <td><strong>$<?php echo number_format($pedido['total'], 2); ?></strong></td>
                                        <td>
                                            <span class="badge badge-<?php echo $pedido['estado']; ?>">
                                                <?php echo ucfirst($pedido['estado']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($pedido['created_at'])); ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="?view=<?php echo $pedido['id']; ?>" class="btn-sm btn-primary" title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <select onchange="window.location.href='?update_status=<?php echo $pedido['id']; ?>&status='+this.value" 
                                                        class="status-select">
                                                    <option value="pendiente" <?php echo $pedido['estado'] == 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                                                    <option value="pagado" <?php echo $pedido['estado'] == 'pagado' ? 'selected' : ''; ?>>Pagado</option>
                                                    <option value="enviado" <?php echo $pedido['estado'] == 'enviado' ? 'selected' : ''; ?>>Enviado</option>
                                                    <option value="entregado" <?php echo $pedido['estado'] == 'entregado' ? 'selected' : ''; ?>>Entregado</option>
                                                    <option value="cancelado" <?php echo $pedido['estado'] == 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                                                </select>
                                                <a href="?delete=<?php echo $pedido['id']; ?>" 
                                                   class="btn-sm btn-danger" 
                                                   onclick="return confirm('¿Estás seguro de eliminar el pedido #<?php echo $pedido['numero_pedido']; ?>? Esta acción no se puede deshacer.')" 
                                                   title="Eliminar pedido">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">No hay pedidos</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    
    <!-- =====================================================
    MODAL: DETALLE DEL PEDIDO
    ===================================================== -->
    <?php if ($pedido_detalle): ?>
    <div id="pedidoModal" class="modal" style="display: flex;">
        <div class="modal-content modal-lg">
            <div class="modal-header">
                <h2>Pedido #<?php echo htmlspecialchars($pedido_detalle['numero_pedido']); ?></h2>
                <button class="modal-close" onclick="window.location.href='pedidos.php'">&times;</button>
            </div>
            
            <div style="padding: 1.5rem;">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:2rem;">
                    <div>
                        <h4>Datos del cliente</h4>
                        <p><strong>Nombre:</strong> <?php echo htmlspecialchars($pedido_detalle['cliente_nombre']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($pedido_detalle['cliente_email']); ?></p>
                        <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($pedido_detalle['cliente_telefono'] ?? '-'); ?></p>
                        <p><strong>Dirección:</strong> <?php echo htmlspecialchars($pedido_detalle['cliente_direccion'] ?? '-'); ?></p>
                    </div>
                    <div>
                        <h4>Información del pedido</h4>
                        <p><strong>Estado:</strong> <span class="badge badge-<?php echo $pedido_detalle['estado']; ?>"><?php echo ucfirst($pedido_detalle['estado']); ?></span></p>
                        <p><strong>Método de pago:</strong> <?php echo htmlspecialchars($pedido_detalle['metodo_pago'] ?? '-'); ?></p>
                        <p><strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($pedido_detalle['created_at'])); ?></p>
                        <p><strong>Total:</strong> <strong>$<?php echo number_format($pedido_detalle['total'], 2); ?></strong></p>
                    </div>
                </div>
                
                <h4>Productos</h4>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio unit.</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($detalles_items as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['nombre_producto']); ?></td>
                                <td><?php echo $item['cantidad']; ?></td>
                                <td>$<?php echo number_format($item['precio_unitario'], 2); ?></td>
                                <td>$<?php echo number_format($item['subtotal'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div style="margin-top:1.5rem;display:flex;justify-content:flex-end;gap:1rem;">
                    <a href="?delete=<?php echo $pedido_detalle['id']; ?>" 
                       class="btn-danger" 
                       onclick="return confirm('¿Eliminar este pedido?')"
                       style="padding:0.6rem 1.5rem;border-radius:10px;text-decoration:none;display:inline-flex;align-items:center;gap:0.5rem;">
                        <i class="fas fa-trash"></i> Eliminar pedido
                    </a>
                    <a href="pedidos.php" class="btn-secondary">Cerrar</a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <style>
        .filter-bar {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
        }
        .btn-filter {
            padding: 0.4rem 1rem;
            border-radius: 20px;
            background: #f1f5f9;
            color: #64748b;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.2s;
        }
        .btn-filter:hover {
            background: #e2e8f0;
        }
        .btn-filter.active {
            background: #3b82f6;
            color: white;
        }
        .status-select {
            padding: 0.2rem 0.5rem;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 0.8rem;
            cursor: pointer;
        }
        .btn-group {
            display: flex;
            gap: 0.3rem;
            align-items: center;
            flex-wrap: wrap;
        }
        .btn-sm {
            padding: 0.3rem 0.6rem;
            font-size: 0.8rem;
            border-radius: 6px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }
        .btn-danger {
            background: #ef4444;
            color: white;
        }
        .btn-danger:hover {
            background: #dc2626;
        }
        .text-center {
            text-align: center;
        }
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
        }
        .alert-success {
            background: #d1fae5;
            color: #065f46;
        }
        .alert-danger {
            background: #fecaca;
            color: #991b1b;
        }
    </style>
</body>
</html>