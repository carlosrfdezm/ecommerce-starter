<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
verificarAdmin();

$mensaje = '';
$error = '';

// =====================================================
// GUARDAR / ACTUALIZAR BANNER
// =====================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'guardar') {
    try {
        $id = intval($_POST['id'] ?? 0);
        $titulo = trim($_POST['titulo'] ?? '');
        $subtitulo = trim($_POST['subtitulo'] ?? '');
        $texto_boton = trim($_POST['texto_boton'] ?? 'Ver más');
        $enlace = trim($_POST['enlace'] ?? '');
        $imagen_url = trim($_POST['imagen_url'] ?? '');
        $icono = trim($_POST['icono'] ?? 'fa-bullhorn');
        $color_inicio = trim($_POST['color_inicio'] ?? '#3b82f6');
        $color_fin = trim($_POST['color_fin'] ?? '#8b5cf6');
        $orden = intval($_POST['orden'] ?? 0);
        $activo = isset($_POST['activo']) ? 1 : 0;

        if (empty($titulo)) {
            throw new Exception('El título es obligatorio');
        }

        if ($id > 0) {
            // Actualizar
            $stmt = $pdo->prepare("
                UPDATE banners SET
                    titulo = ?, subtitulo = ?, texto_boton = ?, enlace = ?,
                    imagen_url = ?, icono = ?, color_inicio = ?, color_fin = ?, orden = ?, activo = ?
                WHERE id = ?
            ");
            $stmt->execute([$titulo, $subtitulo, $texto_boton, $enlace, $imagen_url, $icono, $color_inicio, $color_fin, $orden, $activo, $id]);
            $mensaje = 'Banner actualizado correctamente';
        } else {
            // Crear nuevo
            $stmt = $pdo->prepare("
                INSERT INTO banners
                    (titulo, subtitulo, texto_boton, enlace, imagen_url, icono, color_inicio, color_fin, orden, activo)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$titulo, $subtitulo, $texto_boton, $enlace, $imagen_url, $icono, $color_inicio, $color_fin, $orden, $activo]);
            $mensaje = 'Banner creado correctamente';
        }
    } catch (Exception $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}

// =====================================================
// ELIMINAR BANNER
// =====================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'eliminar') {
    try {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM banners WHERE id = ?");
            $stmt->execute([$id]);
            $mensaje = 'Banner eliminado correctamente';
        }
    } catch (Exception $e) {
        $error = 'Error al eliminar: ' . $e->getMessage();
    }
}

// =====================================================
// OBTENER BANNERS
// =====================================================
$stmt = $pdo->query("SELECT * FROM banners ORDER BY orden ASC, id ASC");
$banners = $stmt->fetchAll();

// =====================================================
// BANNER PARA EDITAR
// =====================================================
$banner_editar = null;
if (isset($_GET['editar'])) {
    $stmt = $pdo->prepare("SELECT * FROM banners WHERE id = ?");
    $stmt->execute([intval($_GET['editar'])]);
    $banner_editar = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banners - Panel Administración</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .banner-preview {
            height: 80px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0 1.25rem;
            color: white;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .banner-preview i {
            font-size: 1.5rem;
        }
        .banner-preview .banner-preview-title {
            font-size: 1rem;
        }
        .banner-preview .banner-preview-sub {
            font-size: 0.8rem;
            opacity: 0.9;
            font-weight: 400;
        }
        .color-picker-row {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }
        .color-picker-row input[type="color"] {
            width: 50px;
            height: 40px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            padding: 0;
        }
        .color-picker-row input[type="text"] {
            flex: 1;
            font-family: monospace;
        }
        .badge-activo {
            background: #d1fae5;
            color: #059669;
            padding: 0.15rem 0.6rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .badge-inactivo {
            background: #f1f5f9;
            color: #64748b;
            padding: 0.15rem 0.6rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        /* =====================================================
        SELECTOR VISUAL DE ICONOS
        ===================================================== */
        .icon-picker {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 1rem;
            background: #f8fafc;
        }

        .icon-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(52px, 1fr));
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .icon-option {
            width: 52px;
            height: 52px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            background: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: #475569;
            transition: all 0.2s;
            padding: 0;
        }

        .icon-option:hover {
            border-color: #3b82f6;
            color: #3b82f6;
            background: #f0f9ff;
            transform: scale(1.05);
        }

        .icon-option.selected {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            border-color: #3b82f6;
            color: white;
            box-shadow: 0 4px 12px rgba(59,130,246,0.35);
            transform: scale(1.08);
        }

        .icon-custom-row {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            align-items: center;
            padding-top: 1rem;
            border-top: 1px solid #e2e8f0;
            margin-bottom: 0.75rem;
        }

        .icon-custom-row label {
            width: 100%;
            margin-bottom: 0.2rem;
        }

        .icon-custom-row input {
            flex: 1;
            min-width: 120px;
            padding: 0.5rem 0.75rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.85rem;
            font-family: monospace;
        }

        .icon-custom-row input:focus {
            outline: none;
            border-color: #3b82f6;
        }

        .icon-selected-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1rem;
            background: white;
            border-radius: 8px;
            font-size: 0.85rem;
            color: #475569;
            border: 1px dashed #e2e8f0;
        }

        .icon-selected-info i {
            color: #3b82f6;
            font-size: 1.1rem;
        }

        .icon-selected-info strong {
            color: #0f172a;
            font-family: monospace;
            font-size: 0.8rem;
        }   
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <header class="content-header">
                <h1><i class="fas fa-bullhorn"></i> Banners</h1>
            </header>
            
            <?php if ($mensaje): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:2rem;align-items:start;">
                
                <!-- LISTA DE BANNERS -->
                <div class="card">
                    <div class="card-header">
                        <h2>Banners actuales</h2>
                    </div>
                    <div class="card-body">
                        <?php if (count($banners) === 0): ?>
                            <p style="text-align:center;color:#94a3b8;padding:2rem;">
                                No hay banners todavía. Crea el primero →
                            </p>
                        <?php else: ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Vista previa</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($banners as $b): ?>
                                        <tr>
                                            <td>
                                                <?php if (!empty($b['imagen_url'])): ?>
                                                    <div class="banner-preview" style="background: linear-gradient(135deg, <?php echo htmlspecialchars($b['color_inicio']); ?> 0%, <?php echo htmlspecialchars($b['color_fin']); ?> 100%), url('<?php echo htmlspecialchars($b['imagen_url']); ?>') center/cover;">
                                                        <i class="fas <?php echo htmlspecialchars($b['icono']); ?>"></i>
                                                        <div>
                                                            <div class="banner-preview-title"><?php echo htmlspecialchars($b['titulo']); ?></div>
                                                            <div class="banner-preview-sub"><?php echo htmlspecialchars($b['subtitulo']); ?></div>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="banner-preview" style="background: linear-gradient(135deg, <?php echo htmlspecialchars($b['color_inicio']); ?>, <?php echo htmlspecialchars($b['color_fin']); ?>);">
                                                        <i class="fas <?php echo htmlspecialchars($b['icono']); ?>"></i>
                                                        <div>
                                                            <div class="banner-preview-title"><?php echo htmlspecialchars($b['titulo']); ?></div>
                                                            <div class="banner-preview-sub"><?php echo htmlspecialchars($b['subtitulo']); ?></div>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                                <?php if ($b['activo']): ?>
                                                    <span class="badge-activo">Activo</span>
                                                <?php else: ?>
                                                    <span class="badge-inactivo">Inactivo</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="?editar=<?php echo $b['id']; ?>" class="btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar este banner?');">
                                                    <input type="hidden" name="accion" value="eliminar">
                                                    <input type="hidden" name="id" value="<?php echo $b['id']; ?>">
                                                    <button type="submit" class="btn-sm" style="background:#fef2f2;color:#ef4444;border:1px solid #fecaca;">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- FORMULARIO CREAR/EDITAR -->
                <div class="card">
                    <div class="card-header">
                        <h2><?php echo $banner_editar ? 'Editar banner' : 'Nuevo banner'; ?></h2>
                        <?php if ($banner_editar): ?>
                            <a href="banners.php" class="btn-link">+ Crear nuevo</a>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="accion" value="guardar">
                            <input type="hidden" name="id" value="<?php echo $banner_editar['id'] ?? 0; ?>">
                            
                            <div class="form-group">
                                <label for="titulo">Título *</label>
                                <input type="text" id="titulo" name="titulo" required
                                       value="<?php echo htmlspecialchars($banner_editar['titulo'] ?? ''); ?>"
                                       placeholder="Gran Venta de Verano">
                            </div>
                            
                            <div class="form-group">
                                <label for="subtitulo">Subtítulo</label>
                                <input type="text" id="subtitulo" name="subtitulo"
                                       value="<?php echo htmlspecialchars($banner_editar['subtitulo'] ?? ''); ?>"
                                       placeholder="Hasta 50% OFF en productos seleccionados">
                            </div>
                            
                           <div class="form-row">
                                <div class="form-group">
                                    <label for="texto_boton">Texto del botón</label>
                                    <input type="text" id="texto_boton" name="texto_boton"
                                        value="<?php echo htmlspecialchars($banner_editar['texto_boton'] ?? 'Ver más'); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="orden">Orden</label>
                                    <input type="number" id="orden" name="orden"
                                        value="<?php echo intval($banner_editar['orden'] ?? 0); ?>">
                                </div>
                            </div>

                            <!-- Selector visual de iconos -->
                            <div class="form-group">
                                <label>Icono *</label>
                                <div class="icon-picker">
                                    <?php
                                    $iconos_disponibles = [
                                        'fa-bullhorn'     => 'Megáfono',
                                        'fa-fire'         => 'Fuego',
                                        'fa-star'         => 'Estrella',
                                        'fa-truck'        => 'Envío',
                                        'fa-gift'         => 'Regalo',
                                        'fa-tags'         => 'Etiquetas',
                                        'fa-bolt'         => 'Rayo',
                                        'fa-percent'      => 'Porcentaje',
                                        'fa-crown'        => 'Corona',
                                        'fa-heart'        => 'Corazón',
                                        'fa-bell'         => 'Campana',
                                        'fa-shopping-cart'=> 'Carrito',
                                        'fa-rocket'       => 'Cohete',
                                        'fa-trophy'       => 'Trofeo',
                                        'fa-money-bill-wave' => 'Dinero',
                                        'fa-hand-holding-usd'=> 'Ahorro',
                                        'fa-clock'        => 'Reloj',
                                        'fa-calendar'     => 'Calendario',
                                        'fa-thumbs-up'    => 'Pulgar arriba',
                                        'fa-credit-card'  => 'Tarjeta',
                                        'fa-shield-alt'   => 'Seguridad',
                                        'fa-sparkles'     => 'Destellos',
                                    ];
                                    $icono_actual = $banner_editar['icono'] ?? 'fa-bullhorn';
                                    ?>
                                    
                                    <div class="icon-grid">
                                        <?php foreach ($iconos_disponibles as $icon_class => $label): ?>
                                            <button type="button" 
                                                    class="icon-option <?php echo $icono_actual === $icon_class ? 'selected' : ''; ?>"
                                                    data-icon="<?php echo $icon_class; ?>"
                                                    title="<?php echo htmlspecialchars($label); ?>"
                                                    onclick="seleccionarIcono('<?php echo $icon_class; ?>', this)">
                                                <i class="fas <?php echo $icon_class; ?>"></i>
                                            </button>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <div class="icon-custom-row">
                                        <label style="font-size:0.85rem;color:#64748b;">
                                            O escribe otro icono FontAwesome:
                                        </label>
                                        <input type="text" 
                                            id="icono_custom"
                                            placeholder="fa-algo"
                                            oninput="usarIconoCustom(this.value)"
                                            value="">
                                        <a href="https://fontawesome.com/icons" target="_blank" style="font-size:0.8rem;color:#3b82f6;">
                                            Ver catálogo completo →
                                        </a>
                                    </div>
                                    
                                    <!-- El valor real que se guarda en la BD -->
                                    <input type="hidden" id="icono" name="icono" value="<?php echo htmlspecialchars($icono_actual); ?>">
                                    
                                    <div class="icon-selected-info">
                                        <i class="fas <?php echo htmlspecialchars($icono_actual); ?>"></i>
                                        <span>Seleccionado: <strong id="icono_nombre"><?php echo htmlspecialchars($icono_actual); ?></strong></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="enlace">Enlace</label>
                                <input type="text" id="enlace" name="enlace"
                                       value="<?php echo htmlspecialchars($banner_editar['enlace'] ?? ''); ?>"
                                       placeholder="index.php#productos o https://...">
                            </div>

                            <div class="form-group">
                                <label for="imagen_url">URL de imagen de fondo (opcional)</label>
                                <input type="text" id="imagen_url" name="imagen_url"
                                    value="<?php echo htmlspecialchars($banner_editar['imagen_url'] ?? ''); ?>"
                                    placeholder="https://ejemplo.com/imagen.jpg o img/banners/verano.jpg">
                                <small style="color:#94a3b8;font-size:0.8rem;display:block;margin-top:0.3rem;">
                                    Si dejas vacío, se usará el degradado de colores. Si pones una URL, se mostrará como fondo con overlay oscuro.
                                </small>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Color inicio</label>
                                    <div class="color-picker-row">
                                        <input type="color" name="color_inicio" 
                                               value="<?php echo htmlspecialchars($banner_editar['color_inicio'] ?? '#3b82f6'); ?>"
                                               onchange="this.nextElementSibling.value=this.value">
                                        <input type="text" 
                                               value="<?php echo htmlspecialchars($banner_editar['color_inicio'] ?? '#3b82f6'); ?>"
                                               onchange="this.previousElementSibling.value=this.value">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Color fin</label>
                                    <div class="color-picker-row">
                                        <input type="color" name="color_fin"
                                               value="<?php echo htmlspecialchars($banner_editar['color_fin'] ?? '#8b5cf6'); ?>"
                                               onchange="this.nextElementSibling.value=this.value">
                                        <input type="text"
                                               value="<?php echo htmlspecialchars($banner_editar['color_fin'] ?? '#8b5cf6'); ?>"
                                               onchange="this.previousElementSibling.value=this.value">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="orden">Orden</label>
                                    <input type="number" id="orden" name="orden"
                                           value="<?php echo intval($banner_editar['orden'] ?? 0); ?>">
                                </div>
                                <div class="form-group" style="display:flex;align-items:flex-end;">
                                    <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;margin:0;">
                                        <input type="checkbox" name="activo" value="1"
                                               <?php echo (!$banner_editar || $banner_editar['activo']) ? 'checked' : ''; ?>>
                                        Activo
                                    </label>
                                </div>
                            </div>
                            
                            <div class="modal-actions">
                                <button type="submit" class="btn-primary">
                                    <i class="fas fa-save"></i> <?php echo $banner_editar ? 'Actualizar' : 'Crear banner'; ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
// =====================================================
// SELECTOR DE ICONOS
// =====================================================
function seleccionarIcono(iconClass, elemento) {
    // Actualizar el input hidden
    document.getElementById('icono').value = iconClass;
    
    // Actualizar el texto informativo
    document.getElementById('icono_nombre').textContent = iconClass;
    
    // Actualizar el icono del panel informativo
    var infoIcon = document.querySelector('.icon-selected-info i');
    if (infoIcon) {
        infoIcon.className = 'fas ' + iconClass;
    }
    
    // Quitar "selected" de todos y marcar el nuevo
    document.querySelectorAll('.icon-option').forEach(function(btn) {
        btn.classList.remove('selected');
    });
    if (elemento) {
        elemento.classList.add('selected');
    }
    
    // Limpiar el input custom
    var customInput = document.getElementById('icono_custom');
    if (customInput) customInput.value = '';
}

function usarIconoCustom(valor) {
    valor = valor.trim();
    
    // Si está vacío, no hacer nada
    if (!valor) return;
    
    // Normalizar: si no empieza con "fa-", añadirlo
    if (!valor.startsWith('fa-')) {
        valor = 'fa-' + valor;
    }
    
    // Actualizar el input hidden
    document.getElementById('icono').value = valor;
    document.getElementById('icono_nombre').textContent = valor;
    
    // Actualizar el icono del panel informativo
    var infoIcon = document.querySelector('.icon-selected-info i');
    if (infoIcon) {
        infoIcon.className = 'fas ' + valor;
    }
    
    // Deseleccionar todos los del grid
    document.querySelectorAll('.icon-option').forEach(function(btn) {
        btn.classList.remove('selected');
    });
}
</script>
</body>
</html>