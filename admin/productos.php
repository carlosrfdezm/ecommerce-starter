<?php
// =====================================================
// ADMIN: PRODUCTOS - VERSIÓN COMPLETA
// =====================================================
require_once '../includes/config.php';
require_once '../includes/auth.php';
verificarAdmin();

$mensaje = '';
$error = '';

// =====================================================
// ELIMINAR PRODUCTO
// =====================================================
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM productos WHERE id = ?");
        $stmt->execute([$id]);
        $mensaje = '✅ Producto eliminado correctamente';
    } catch (Exception $e) {
        $error = '❌ No se puede eliminar el producto (puede tener pedidos asociados)';
    }
}

// =====================================================
// CREAR / ACTUALIZAR PRODUCTO
// =====================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $descripcion_larga = trim($_POST['descripcion_larga'] ?? '');
    $garantia_envio = trim($_POST['garantia_envio'] ?? 'Envío disponible a todo el país');
    $garantia_seguridad = trim($_POST['garantia_seguridad'] ?? 'Compra 100% segura con Stripe');
    $garantia_devolucion = trim($_POST['garantia_devolucion'] ?? '30 días de garantía de devolución');
    $precio = (float)$_POST['precio'];
    $precio_promocion = !empty($_POST['precio_promocion']) ? (float)$_POST['precio_promocion'] : null;
    $en_promocion = isset($_POST['en_promocion']) ? 1 : 0;
    $stock = (int)$_POST['stock'];
    $categoria_id = !empty($_POST['categoria_id']) ? (int)$_POST['categoria_id'] : null;
    $imagen_url = trim($_POST['imagen_url'] ?? '');
    $destacado = isset($_POST['destacado']) ? 1 : 0;
    $activo = isset($_POST['activo']) ? 1 : 0;
    
    if (empty($nombre) || $precio <= 0) {
        $error = '❌ Nombre y precio son obligatorios';
    } else {
        try {
            if ($id > 0) {
                // ACTUALIZAR
                $stmt = $pdo->prepare("
                    UPDATE productos 
                    SET nombre = ?, descripcion = ?, descripcion_larga = ?,
                        garantia_envio = ?, garantia_seguridad = ?, garantia_devolucion = ?,
                        precio = ?, precio_promocion = ?, en_promocion = ?, stock = ?,
                        categoria_id = ?, imagen_url = ?, destacado = ?, activo = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $nombre, $descripcion, $descripcion_larga,
                    $garantia_envio, $garantia_seguridad, $garantia_devolucion,
                    $precio, $precio_promocion, $en_promocion, $stock,
                    $categoria_id, $imagen_url, $destacado, $activo, $id
                ]);
                $mensaje = '✅ Producto actualizado correctamente';
            } else {
                // CREAR
                $stmt = $pdo->prepare("
                    INSERT INTO productos 
                    (nombre, descripcion, descripcion_larga, garantia_envio, garantia_seguridad, 
                     garantia_devolucion, precio, precio_promocion, en_promocion, stock, 
                     categoria_id, imagen_url, destacado, activo)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $nombre, $descripcion, $descripcion_larga,
                    $garantia_envio, $garantia_seguridad, $garantia_devolucion,
                    $precio, $precio_promocion, $en_promocion, $stock,
                    $categoria_id, $imagen_url, $destacado, $activo
                ]);
                $mensaje = '✅ Producto creado correctamente';
            }
        } catch (Exception $e) {
            $error = '❌ Error al guardar: ' . $e->getMessage();
        }
    }
}

// =====================================================
// OBTENER PRODUCTO PARA EDITAR
// =====================================================
$producto_edit = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
    $stmt->execute([$id]);
    $producto_edit = $stmt->fetch();
}

// =====================================================
// LISTAR PRODUCTOS
// =====================================================
$stmt = $pdo->query("
    SELECT p.*, c.nombre as categoria_nombre 
    FROM productos p 
    LEFT JOIN categorias c ON p.categoria_id = c.id 
    ORDER BY p.created_at DESC
");
$productos = $stmt->fetchAll();

// Categorías
$stmt = $pdo->query("SELECT * FROM categorias ORDER BY nombre");
$categorias = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos - Panel Administración</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .modal-lg { max-width: 800px !important; }
        .form-help { font-size: 0.8rem; color: #94a3b8; margin-top: 0.2rem; }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <header class="content-header">
                <h1><i class="fas fa-box"></i> Productos</h1>
                <button class="btn-primary" onclick="abrirModal()">
                    <i class="fas fa-plus"></i> Nuevo producto
                </button>
            </header>
            
            <?php if ($mensaje): ?>
                <div class="alert alert-success"><?php echo $mensaje; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Imagen</th>
                                <th>Nombre</th>
                                <th>Categoría</th>
                                <th>Precio</th>
                                <th>Stock</th>
                                <th>Destacado</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productos as $producto): ?>
                                <tr>
                                    <td>
                                        <?php if ($producto['imagen_url']): ?>
                                            <img src="<?php echo htmlspecialchars($producto['imagen_url']); ?>" 
                                                 alt="" style="width:50px;height:50px;object-fit:cover;border-radius:8px;">
                                        <?php else: ?>
                                            <div style="width:50px;height:50px;background:#f1f5f9;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#94a3b8;">
                                                <i class="fas fa-image"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?php echo htmlspecialchars($producto['nombre']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($producto['categoria_nombre'] ?? '-'); ?></td>
                                    <td>$<?php echo number_format($producto['precio'], 2); ?></td>
                                    <td>
                                        <?php if ($producto['stock'] < 10): ?>
                                            <span class="badge badge-danger"><?php echo $producto['stock']; ?></span>
                                        <?php else: ?>
                                            <?php echo $producto['stock']; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($producto['destacado']): ?>
                                            <span class="badge badge-success">★</span>
                                        <?php else: ?>
                                            <span class="badge badge-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($producto['activo']): ?>
                                            <span class="badge badge-success">Activo</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="?edit=<?php echo $producto['id']; ?>" class="btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="?delete=<?php echo $producto['id']; ?>" 
                                           class="btn-sm btn-danger" 
                                           onclick="return confirm('¿Eliminar este producto?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
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
    MODAL PARA CREAR/EDITAR PRODUCTO
    ===================================================== -->
    <div id="productModal" class="modal" style="display: <?php echo $producto_edit ? 'flex' : 'none'; ?>;">
        <div class="modal-content modal-lg">
            <div class="modal-header">
                <h2><?php echo $producto_edit ? 'Editar producto' : 'Nuevo producto'; ?></h2>
                <button class="modal-close" onclick="cerrarModal()">&times;</button>
            </div>
            
            <form method="POST" id="productForm">
                <?php if ($producto_edit): ?>
                    <input type="hidden" name="id" value="<?php echo $producto_edit['id']; ?>">
                <?php endif; ?>
                
                <!-- INFORMACIÓN BÁSICA -->
                <h3 style="margin-bottom:1rem;color:#3b82f6;">📦 Información básica</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="nombre">Nombre *</label>
                        <input type="text" id="nombre" name="nombre" 
                               value="<?php echo $producto_edit ? htmlspecialchars($producto_edit['nombre']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="categoria_id">Categoría</label>
                        <select id="categoria_id" name="categoria_id">
                            <option value="">Sin categoría</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"
                                    <?php echo ($producto_edit && $producto_edit['categoria_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="descripcion">Descripción corta (tarjeta)</label>
                    <textarea id="descripcion" name="descripcion" rows="2" 
                              placeholder="Descripción breve que aparece en la tarjeta del producto"><?php echo $producto_edit ? htmlspecialchars($producto_edit['descripcion']) : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="descripcion_larga">Descripción larga (modal)</label>
                    <textarea id="descripcion_larga" name="descripcion_larga" rows="4" 
                              placeholder="Descripción detallada que aparece en el modal del producto"><?php echo $producto_edit ? htmlspecialchars($producto_edit['descripcion_larga'] ?? '') : ''; ?></textarea>
                </div>
                
                <!-- PRECIOS -->
                <h3 style="margin:1.5rem 0 1rem;color:#3b82f6;">💰 Precios</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="precio">Precio normal *</label>
                        <input type="number" id="precio" name="precio" step="0.01" 
                               value="<?php echo $producto_edit ? $producto_edit['precio'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="precio_promocion">Precio promoción</label>
                        <input type="number" id="precio_promocion" name="precio_promocion" step="0.01" 
                               value="<?php echo $producto_edit ? $producto_edit['precio_promocion'] : ''; ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="en_promocion" value="1"
                                <?php echo ($producto_edit && $producto_edit['en_promocion']) ? 'checked' : ''; ?>>
                            En promoción
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label for="stock">Stock</label>
                        <input type="number" id="stock" name="stock" 
                               value="<?php echo $producto_edit ? $producto_edit['stock'] : 0; ?>">
                    </div>
                </div>
                
                <!-- IMAGEN -->
                <h3 style="margin:1.5rem 0 1rem;color:#3b82f6;">🖼️ Imagen</h3>
                
                <div class="form-group">
                    <label for="imagen_url">URL de la imagen</label>
                    <input type="url" id="imagen_url" name="imagen_url" 
                           placeholder="https://ejemplo.com/imagen.jpg" 
                           value="<?php echo $producto_edit ? htmlspecialchars($producto_edit['imagen_url']) : ''; ?>">
                    <p class="form-help">Pega la URL de la imagen del producto</p>
                </div>
                
                <!-- GARANTÍAS -->
                <h3 style="margin:1.5rem 0 1rem;color:#3b82f6;">🛡️ Garantías (modal)</h3>
                
                <div class="form-group">
                    <label for="garantia_envio">Garantía de envío</label>
                    <input type="text" id="garantia_envio" name="garantia_envio" 
                           placeholder="Envío disponible a todo el país"
                           value="<?php echo $producto_edit ? htmlspecialchars($producto_edit['garantia_envio'] ?? 'Envío disponible a todo el país') : 'Envío disponible a todo el país'; ?>">
                </div>
                
                <div class="form-group">
                    <label for="garantia_seguridad">Garantía de seguridad</label>
                    <input type="text" id="garantia_seguridad" name="garantia_seguridad" 
                           placeholder="Compra 100% segura con Stripe"
                           value="<?php echo $producto_edit ? htmlspecialchars($producto_edit['garantia_seguridad'] ?? 'Compra 100% segura con Stripe') : 'Compra 100% segura con Stripe'; ?>">
                </div>
                
                <div class="form-group">
                    <label for="garantia_devolucion">Garantía de devolución</label>
                    <input type="text" id="garantia_devolucion" name="garantia_devolucion" 
                           placeholder="30 días de garantía de devolución"
                           value="<?php echo $producto_edit ? htmlspecialchars($producto_edit['garantia_devolucion'] ?? '30 días de garantía de devolución') : '30 días de garantía de devolución'; ?>">
                </div>
                
                <!-- VISIBILIDAD -->
                <h3 style="margin:1.5rem 0 1rem;color:#3b82f6;">👁️ Visibilidad</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="destacado" value="1"
                                <?php echo ($producto_edit && $producto_edit['destacado']) ? 'checked' : ''; ?>>
                            Destacar producto
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="activo" value="1"
                                <?php echo (!$producto_edit || $producto_edit['activo']) ? 'checked' : ''; ?>>
                            Activo (visible en la tienda)
                        </label>
                    </div>
                </div>
                
                <div class="modal-actions">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                    <button type="button" class="btn-secondary" onclick="cerrarModal()">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function abrirModal() {
            document.getElementById('productModal').style.display = 'flex';
            document.getElementById('productForm').reset();
            document.querySelector('input[name="id"]')?.remove();
            document.querySelector('#productModal h2').textContent = 'Nuevo producto';
        }
        
        function cerrarModal() {
            document.getElementById('productModal').style.display = 'none';
            window.location.href = 'productos.php';
        }
    </script>
</body>
</html>