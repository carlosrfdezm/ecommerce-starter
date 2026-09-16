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
        // Contar productos en esta categoría Y en sus subcategorías
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM productos WHERE categoria_id = ?");
        $stmt->execute([$id]);
        $count_prod = $stmt->fetchColumn();
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM productos WHERE categoria_id IN (SELECT id FROM categorias WHERE padre_id = ?)");
        $stmt->execute([$id]);
        $count_sub_prod = $stmt->fetchColumn();
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM categorias WHERE padre_id = ?");
        $stmt->execute([$id]);
        $count_subs = $stmt->fetchColumn();
        
        if ($count_prod > 0) {
            $error = "No se puede eliminar: tiene $count_prod productos asociados directamente";
        } elseif ($count_sub_prod > 0) {
            $error = "No se puede eliminar: sus $count_subs subcategorías tienen $count_sub_prod productos asociados";
        } else {
            // Eliminar subcategorías primero
            $stmt = $pdo->prepare("DELETE FROM categorias WHERE padre_id = ?");
            $stmt->execute([$id]);
            
            $stmt = $pdo->prepare("DELETE FROM categorias WHERE id = ?");
            $stmt->execute([$id]);
            
            $mensaje = '✅ Categoría eliminada correctamente';
        }
    } catch (Exception $e) {
        $error = 'Error al eliminar: ' . $e->getMessage();
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
            // Validar que no se esté creando un ciclo (no puede ser padre de sí mismo)
            if ($id > 0 && $padre_id === $id) {
                throw new Exception('Una categoría no puede ser su propia categoría padre');
            }
            
            // Validar que el padre no sea un hijo (evitar ciclos indirectos)
            if ($id > 0 && $padre_id) {
                $stmt = $pdo->prepare("SELECT padre_id FROM categorias WHERE id = ?");
                $stmt->execute([$padre_id]);
                $abuelo = $stmt->fetchColumn();
                if ($abuelo == $id) {
                    throw new Exception('No se puede crear un ciclo entre categorías');
                }
            }
            
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

// Contar subcategorías de cada padre
$stmt = $pdo->query("SELECT padre_id, COUNT(*) as total FROM categorias WHERE padre_id IS NOT NULL GROUP BY padre_id");
$contadores = [];
while ($row = $stmt->fetch()) {
    $contadores[$row['padre_id']] = $row['total'];
}
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
        .cat-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.8rem 1rem;
            border-bottom: 1px solid #f1f5f9;
            transition: background 0.2s;
        }
        .cat-item:hover { background: #f8fafc; }
        .cat-item.cat-padre {
            background: #f8fafc;
            font-weight: 600;
            border-left: 4px solid #3b82f6;
        }
        .cat-item.cat-hijo {
            padding-left: 3rem;
            font-size: 0.9rem;
            color: #475569;
            background: white;
        }
        .cat-item.cat-hijo::before {
            content: '└─';
            color: #cbd5e1;
            margin-right: 0.5rem;
            font-weight: 400;
        }
        .cat-info {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            flex: 1;
            min-width: 0;
        }
        .cat-info i {
            color: #3b82f6;
            width: 20px;
            text-align: center;
        }
        .cat-item.cat-hijo .cat-info i {
            color: #94a3b8;
        }
        .cat-badge {
            background: #e0e7ff;
            color: #3b82f6;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 0.15rem 0.5rem;
            border-radius: 10px;
            margin-left: 0.5rem;
        }
        .cat-actions {
            display: flex;
            gap: 0.3rem;
            flex-shrink: 0;
        }
        .cat-actions .btn-add-sub {
            background: #dbeafe;
            color: #2563eb;
            border: 1px solid #bfdbfe;
            padding: 0.35rem 0.7rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            transition: all 0.2s;
        }
        .cat-actions .btn-add-sub:hover {
            background: #bfdbfe;
        }
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #94a3b8;
        }
        .empty-state i {
            font-size: 3rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
            display: block;
        }
        /* Estilos del modal */
        .modal-subtitle {
            font-size: 0.85rem;
            color: #64748b;
            margin-top: 0.3rem;
        }
        .form-group small.help {
            display: block;
            color: #94a3b8;
            font-size: 0.8rem;
            margin-top: 0.3rem;
        }
        .icon-preview {
            display: inline-block;
            margin-left: 0.5rem;
            color: #3b82f6;
            font-size: 1.1rem;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <header class="content-header">
                <div>
                    <h1><i class="fas fa-tags"></i> Categorías</h1>
                    <p style="color:#64748b;margin-top:0.3rem;font-size:0.9rem;">
                        Gestiona las categorías principales y sus subcategorías
                    </p>
                </div>
                <button class="btn-primary" onclick="abrirModalNueva()">
                    <i class="fas fa-plus"></i> Nueva categoría principal
                </button>
            </header>
            
            <?php if ($mensaje): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-body">
                    <?php if (count($categorias_padre) === 0): ?>
                        <div class="empty-state">
                            <i class="fas fa-tags"></i>
                            <p>No hay categorías todavía</p>
                            <button class="btn-primary" onclick="abrirModalNueva()" style="margin-top:1rem;">
                                <i class="fas fa-plus"></i> Crear la primera
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="cat-tree">
                            <?php foreach ($categorias_padre as $padre): ?>
                                <?php
                                $total_hijos = $contadores[$padre['id']] ?? 0;
                                $stmt = $pdo->prepare("SELECT * FROM categorias WHERE padre_id = ? ORDER BY orden, nombre");
                                $stmt->execute([$padre['id']]);
                                $hijos = $stmt->fetchAll();
                                ?>
                                
                                <!-- CATEGORÍA PADRE -->
                                <div class="cat-item cat-padre">
                                    <div class="cat-info">
                                        <i class="fas <?php echo htmlspecialchars($padre['icono'] ?? 'fa-tag'); ?>"></i>
                                        <strong><?php echo htmlspecialchars($padre['nombre']); ?></strong>
                                        <?php if ($total_hijos > 0): ?>
                                            <span class="cat-badge"><?php echo $total_hijos; ?> sub</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="cat-actions">
                                        <button class="btn-add-sub" onclick="abrirModalSub(<?php echo $padre['id']; ?>, '<?php echo htmlspecialchars(addslashes($padre['nombre'])); ?>')">
                                            <i class="fas fa-plus"></i> Subcategoría
                                        </button>
                                        <a href="?edit=<?php echo $padre['id']; ?>" class="btn-sm btn-primary" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="?delete=<?php echo $padre['id']; ?>" 
                                           class="btn-sm btn-danger" 
                                           title="Eliminar"
                                           onclick="return confirm('¿Eliminar esta categoría y sus subcategorías?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </div>
                                
                                <!-- SUBCATEGORÍAS -->
                                <?php foreach ($hijos as $hijo): ?>
                                    <div class="cat-item cat-hijo">
                                        <div class="cat-info">
                                            <i class="fas <?php echo htmlspecialchars($hijo['icono'] ?? 'fa-tag'); ?>"></i>
                                            <span><?php echo htmlspecialchars($hijo['nombre']); ?></span>
                                        </div>
                                        <div class="cat-actions">
                                            <a href="?edit=<?php echo $hijo['id']; ?>" class="btn-sm btn-primary" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?delete=<?php echo $hijo['id']; ?>" 
                                               class="btn-sm btn-danger" 
                                               title="Eliminar"
                                               onclick="return confirm('¿Eliminar esta subcategoría?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <!-- MODAL -->
    <div id="categoriaModal" class="modal" style="display: <?php echo $categoria_edit ? 'flex' : 'none'; ?>;">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h2 id="modalTitulo"><?php echo $categoria_edit ? 'Editar categoría' : 'Nueva categoría'; ?></h2>
                    <p class="modal-subtitle" id="modalSubtitulo"></p>
                </div>
                <button class="modal-close" onclick="cerrarModal()">&times;</button>
            </div>
            
            <form method="POST" id="categoriaForm">
                <?php if ($categoria_edit): ?>
                    <input type="hidden" name="id" value="<?php echo $categoria_edit['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="nombre">Nombre *</label>
                    <input type="text" id="nombre" name="nombre" 
                           value="<?php echo $categoria_edit ? htmlspecialchars($categoria_edit['nombre']) : ''; ?>" 
                           required
                           placeholder="Ej: Electrónica, Ropa, Hogar...">
                </div>
                
                <div class="form-group">
                    <label for="padre_id">Categoría padre</label>
                    <select id="padre_id" name="padre_id">
                        <option value="">-- Ninguna (categoría principal) --</option>
                        <?php foreach ($todas_categorias as $cat): ?>
                            <?php 
                            // No permitir que sea padre de sí misma
                            if ($categoria_edit && $cat['id'] == $categoria_edit['id']) continue;
                            // No permitir que sea padre una subcategoría (solo categorías principales)
                            if ($cat['padre_id'] !== null) continue;
                            ?>
                            <option value="<?php echo $cat['id']; ?>"
                                <?php echo ($categoria_edit && $categoria_edit['padre_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="help">Si seleccionas una categoría padre, esta se creará como subcategoría</small>
                </div>
                
                <div class="form-group">
                    <label for="icono">Icono Font Awesome <span class="icon-preview" id="iconoPreview"></span></label>
                    <input type="text" id="icono" name="icono" 
                           placeholder="fa-tag, fa-laptop, fa-tshirt..."
                           value="<?php echo $categoria_edit ? htmlspecialchars($categoria_edit['icono']) : 'fa-tag'; ?>"
                           oninput="actualizarPreviewIcono(this.value)">
                    <small class="help">
                        Ver todos en <a href="https://fontawesome.com/icons" target="_blank">fontawesome.com/icons</a>
                    </small>
                </div>
                
                <div class="form-group">
                    <label for="descripcion">Descripción</label>
                    <textarea id="descripcion" name="descripcion" rows="2"
                              placeholder="Opcional"><?php echo $categoria_edit ? htmlspecialchars($categoria_edit['descripcion']) : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="orden">Orden</label>
                    <input type="number" id="orden" name="orden" 
                           value="<?php echo $categoria_edit ? $categoria_edit['orden'] : 0; ?>">
                    <small class="help">Menor número = aparece primero</small>
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
        const modal = document.getElementById('categoriaModal');
        const form = document.getElementById('categoriaForm');
        const selectPadre = document.getElementById('padre_id');
        const inputIcono = document.getElementById('icono');
        const previewIcono = document.getElementById('iconoPreview');
        
        function abrirModalNueva() {
            modal.style.display = 'flex';
            form.reset();
            document.querySelector('input[name="id"]')?.remove();
            document.getElementById('modalTitulo').textContent = 'Nueva categoría principal';
            document.getElementById('modalSubtitulo').textContent = 'Se creará en el nivel superior del menú';
            selectPadre.value = '';
            inputIcono.value = 'fa-tag';
            actualizarPreviewIcono('fa-tag');
        }
        
        function abrirModalSub(padreId, padreNombre) {
            modal.style.display = 'flex';
            form.reset();
            document.querySelector('input[name="id"]')?.remove();
            document.getElementById('modalTitulo').textContent = 'Nueva subcategoría';
            document.getElementById('modalSubtitulo').textContent = 'Se creará dentro de: ' + padreNombre;
            selectPadre.value = padreId;
            inputIcono.value = 'fa-tag';
            actualizarPreviewIcono('fa-tag');
        }
        
        function cerrarModal() {
            modal.style.display = 'none';
            window.location.href = 'categorias.php';
        }
        
        function actualizarPreviewIcono(valor) {
            valor = (valor || '').trim();
            if (!valor.startsWith('fa-')) valor = 'fa-' + valor;
            previewIcono.className = 'fas ' + valor + ' icon-preview';
        }
        
        // Actualizar subtítulo del modal al cambiar el selector de padre manualmente
        selectPadre.addEventListener('change', function() {
            const padreNombre = this.options[this.selectedIndex]?.text || '';
            if (this.value) {
                document.getElementById('modalTitulo').textContent = 'Nueva subcategoría';
                document.getElementById('modalSubtitulo').textContent = 'Se creará dentro de: ' + padreNombre;
            } else {
                document.getElementById('modalTitulo').textContent = 'Nueva categoría principal';
                document.getElementById('modalSubtitulo').textContent = 'Se creará en el nivel superior del menú';
            }
        });
        
        // Cerrar modal al hacer clic fuera
        modal.addEventListener('click', function(e) {
            if (e.target === modal) cerrarModal();
        });
        
        // Inicializar preview del icono
        <?php if ($categoria_edit): ?>
        document.addEventListener('DOMContentLoaded', function() {
            actualizarPreviewIcono(inputIcono.value);
            document.getElementById('modalTitulo').textContent = 'Editar categoría';
            document.getElementById('modalSubtitulo').textContent = '<?php echo htmlspecialchars(addslashes($categoria_edit['nombre'])); ?>';
        });
        <?php endif; ?>
    </script>
</body>
</html>