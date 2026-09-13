<?php
// =====================================================
// ADMIN: CATEGORÍAS CON SUBCATEGORÍAS
// =====================================================
require_once '../includes/config.php';
require_once '../includes/auth.php';
verificarAdmin();

$mensaje = '';
$error = '';

// ELIMINAR
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM productos WHERE categoria_id = ?");
        $stmt->execute([$id]);
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            $error = "No se puede eliminar: tiene $count productos asociados";
        } else {
            $stmt = $pdo->prepare("DELETE FROM categorias WHERE id = ?");
            $stmt->execute([$id]);
            $mensaje = '✅ Categoría eliminada';
        }
    } catch (Exception $e) {
        $error = 'Error al eliminar';
    }
}

// CREAR/ACTUALIZAR
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $nombre = trim($_POST['nombre'] ?? '');
    $icono = trim($_POST['icono'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $padre_id = !empty($_POST['padre_id']) ? (int)$_POST['padre_id'] : null;
    $orden = (int)($_POST['orden'] ?? 0);
    
    if (empty($nombre)) {
        $error = 'El nombre es obligatorio';
    } else {
        try {
            if ($id > 0) {
                $stmt = $pdo->prepare("UPDATE categorias SET nombre = ?, icono = ?, descripcion = ?, padre_id = ?, orden = ? WHERE id = ?");
                $stmt->execute([$nombre, $icono, $descripcion, $padre_id, $orden, $id]);
                $mensaje = '✅ Categoría actualizada';
            } else {
                $stmt = $pdo->prepare("INSERT INTO categorias (nombre, icono, descripcion, padre_id, orden) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$nombre, $icono, $descripcion, $padre_id, $orden]);
                $mensaje = '✅ Categoría creada';
            }
        } catch (Exception $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}

// OBTENER PARA EDITAR
$categoria_edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM categorias WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $categoria_edit = $stmt->fetch();
}

// LISTAR PADRES
$stmt = $pdo->query("SELECT * FROM categorias WHERE padre_id IS NULL ORDER BY orden, nombre");
$categorias_padre = $stmt->fetchAll();

// LISTAR TODAS (para selector de padre)
$stmt = $pdo->query("SELECT * FROM categorias ORDER BY nombre");
$todas_categorias = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categorías - Panel Admin</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .cat-tree { padding-left: 0; }
        .cat-tree .cat-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.8rem 1rem;
            border-bottom: 1px solid #f1f5f9;
        }
        .cat-tree .cat-item:hover { background: #f8fafc; }
        .cat-tree .cat-padre { background: #f8fafc; font-weight: 600; }
        .cat-tree .cat-hijo {
            padding-left: 3rem;
            font-size: 0.9rem;
            color: #64748b;
        }
        .cat-tree .cat-hijo::before {
            content: '└─ ';
            color: #cbd5e1;
        }
        .cat-actions { display: flex; gap: 0.3rem; }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <header class="content-header">
                <h1><i class="fas fa-tags"></i> Categorías</h1>
                <button class="btn-primary" onclick="abrirModal()">
                    <i class="fas fa-plus"></i> Nueva categoría
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
                    <div class="cat-tree">
                        <?php foreach ($categorias_padre as $padre): ?>
                            <!-- CATEGORÍA PADRE -->
                            <div class="cat-item cat-padre">
                                <div>
                                    <i class="fas <?php echo htmlspecialchars($padre['icono'] ?? 'fa-tag'); ?>"></i>
                                    <strong><?php echo htmlspecialchars($padre['nombre']); ?></strong>
                                </div>
                                <div class="cat-actions">
                                    <a href="?edit=<?php echo $padre['id']; ?>" class="btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="?delete=<?php echo $padre['id']; ?>" 
                                       class="btn-sm btn-danger" 
                                       onclick="return confirm('¿Eliminar?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                            
                            <!-- SUBCATEGORÍAS -->
                            <?php
                            $stmt = $pdo->prepare("SELECT * FROM categorias WHERE padre_id = ? ORDER BY orden, nombre");
                            $stmt->execute([$padre['id']]);
                            $hijos = $stmt->fetchAll();
                            foreach ($hijos as $hijo):
                            ?>
                                <div class="cat-item cat-hijo">
                                    <div>
                                        <i class="fas <?php echo htmlspecialchars($hijo['icono'] ?? 'fa-tag'); ?>"></i>
                                        <?php echo htmlspecialchars($hijo['nombre']); ?>
                                    </div>
                                    <div class="cat-actions">
                                        <a href="?edit=<?php echo $hijo['id']; ?>" class="btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="?delete=<?php echo $hijo['id']; ?>" 
                                           class="btn-sm btn-danger" 
                                           onclick="return confirm('¿Eliminar?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <!-- MODAL -->
    <div id="categoriaModal" class="modal" style="display: <?php echo $categoria_edit ? 'flex' : 'none'; ?>;">
        <div class="modal-content">
            <div class="modal-header">
                <h2><?php echo $categoria_edit ? 'Editar' : 'Nueva'; ?> categoría</h2>
                <button class="modal-close" onclick="cerrarModal()">&times;</button>
            </div>
            
            <form method="POST">
                <?php if ($categoria_edit): ?>
                    <input type="hidden" name="id" value="<?php echo $categoria_edit['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="nombre">Nombre *</label>
                    <input type="text" id="nombre" name="nombre" 
                           value="<?php echo $categoria_edit ? htmlspecialchars($categoria_edit['nombre']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="padre_id">Categoría padre (opcional)</label>
                    <select id="padre_id" name="padre_id">
                        <option value="">-- Ninguna (categoría principal) --</option>
                        <?php foreach ($todas_categorias as $cat): ?>
                            <?php if ($categoria_edit && $cat['id'] == $categoria_edit['id']) continue; ?>
                            <option value="<?php echo $cat['id']; ?>"
                                <?php echo ($categoria_edit && $categoria_edit['padre_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="icono">Icono Font Awesome</label>
                    <input type="text" id="icono" name="icono" 
                           placeholder="fa-tag, fa-laptop..."
                           value="<?php echo $categoria_edit ? htmlspecialchars($categoria_edit['icono']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="descripcion">Descripción</label>
                    <textarea id="descripcion" name="descripcion" rows="2"><?php echo $categoria_edit ? htmlspecialchars($categoria_edit['descripcion']) : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="orden">Orden</label>
                    <input type="number" id="orden" name="orden" 
                           value="<?php echo $categoria_edit ? $categoria_edit['orden'] : 0; ?>">
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
            document.getElementById('categoriaModal').style.display = 'flex';
            document.querySelector('#categoriaModal form').reset();
            document.querySelector('input[name="id"]')?.remove();
            document.querySelector('#categoriaModal h2').textContent = 'Nueva categoría';
        }
        
        function cerrarModal() {
            document.getElementById('categoriaModal').style.display = 'none';
            window.location.href = 'categorias.php';
        }
    </script>
</body>
</html>