<?php
// =====================================================
// PERFIL DE USUARIO
// =====================================================
session_start();
require_once 'includes/config.php';

// Verificar que el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php?redirect=perfil.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$mensaje = '';
$error = '';

// Obtener datos del usuario
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$usuario_id]);
$usuario = $stmt->fetch();

if (!$usuario) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Obtener pedidos del usuario
$stmt = $pdo->prepare("SELECT * FROM pedidos WHERE usuario_id = ? OR cliente_email = ? ORDER BY created_at DESC");
$stmt->execute([$usuario_id, $usuario['email']]);
$pedidos = $stmt->fetchAll();

// Actualizar datos
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    
    if (empty($nombre)) {
        $error = 'El nombre es obligatorio';
    } else {
        try {
            $sql = "UPDATE usuarios SET nombre = ?, telefono = ?, direccion = ?";
            $params = [$nombre, $telefono, $direccion];
            
            if (!empty($password)) {
                if (strlen($password) < 6) {
                    $error = 'La contraseña debe tener al menos 6 caracteres';
                } elseif ($password !== $password_confirm) {
                    $error = 'Las contraseñas no coinciden';
                } else {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $sql .= ", password_hash = ?";
                    $params[] = $hash;
                }
            }
            
            if (empty($error)) {
                $sql .= " WHERE id = ?";
                $params[] = $usuario_id;
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                
                $mensaje = '✅ Datos actualizados correctamente';
                
                // Actualizar sesión
                $_SESSION['usuario_nombre'] = $nombre;
                
                // Recargar datos
                $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
                $stmt->execute([$usuario_id]);
                $usuario = $stmt->fetch();
            }
        } catch (PDOException $e) {
            $error = 'Error al actualizar: ' . $e->getMessage();
        }
    }
}

$page_title = 'Mi Perfil';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo $page_title; ?> - <?php echo TIENDA_NOMBRE; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="css/style.css" />
    <style>
        .profile-container {
            max-width: 800px;
            margin: 2rem auto;
        }
        .profile-header {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            padding: 2rem;
            background: white;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }
        .profile-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: white;
            flex-shrink: 0;
        }
        .profile-info h1 {
            font-size: 1.8rem;
            margin: 0;
        }
        .profile-info p {
            color: #64748b;
            margin: 0.2rem 0 0;
        }
        .profile-tabs {
            display: flex;
            gap: 0.5rem;
            border-bottom: 2px solid #e2e8f0;
            margin-bottom: 2rem;
        }
        .profile-tabs a {
            padding: 0.8rem 1.5rem;
            text-decoration: none;
            color: #64748b;
            font-weight: 600;
            border-bottom: 2px solid transparent;
            margin-bottom: -2px;
            transition: all 0.2s;
        }
        .profile-tabs a.active {
            color: #3b82f6;
            border-bottom-color: #3b82f6;
        }
        .profile-tabs a:hover {
            color: #3b82f6;
        }
        .profile-section {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.3rem;
        }
        .form-group input {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.2s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #3b82f6;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        .btn-primary {
            padding: 0.8rem 2rem;
            font-size: 1rem;
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
        .pedido-item {
            padding: 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            margin-bottom: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .pedido-item .badge {
            padding: 0.2rem 0.8rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .badge-pendiente { background: #fef3c7; color: #92400e; }
        .badge-pagado { background: #dbeafe; color: #1e40af; }
        .badge-enviado { background: #d1fae5; color: #065f46; }
        .badge-entregado { background: #d1fae5; color: #065f46; }
        .badge-cancelado { background: #fecaca; color: #991b1b; }
        .btn-logout {
            color: #ef4444;
            background: #fef2f2;
            border: 1px solid #fecaca;
            padding: 0.6rem 1.5rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
        }
        .btn-logout:hover {
            background: #fecaca;
        }
        @media (max-width: 480px) {
            .profile-header {
                flex-direction: column;
                text-align: center;
            }
            .form-row {
                grid-template-columns: 1fr;
            }
            .profile-tabs {
                flex-wrap: wrap;
            }
            .pedido-item {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- HEADER -->
    <header class="navbar">
        <div class="container">
            <div class="nav-brand">
                <i class="fas fa-bolt" style="color: #3b82f6;"></i>
                <span><?php echo TIENDA_NOMBRE; ?></span>
            </div>
            <nav class="nav-links">
                <a href="index.php">Inicio</a>
                <a href="index.php#productos">Productos</a>
                <a href="logout.php" class="btn-logout" style="padding:0.3rem 1rem;font-size:0.85rem;">
                    <i class="fas fa-sign-out-alt"></i> Salir
                </a>
            </nav>
        </div>
    </header>

    <!-- PERFIL -->
    <main>
        <div class="container">
            <div class="profile-container">
                <!-- Header -->
                <div class="profile-header">
                    <div class="profile-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="profile-info">
                        <h1><?php echo htmlspecialchars($usuario['nombre']); ?></h1>
                        <p><?php echo htmlspecialchars($usuario['email']); ?></p>
                        <p style="font-size:0.85rem;margin-top:0.3rem;">
                            <i class="fas fa-calendar-alt"></i> Miembro desde: <?php echo date('d/m/Y', strtotime($usuario['fecha_registro'])); ?>
                        </p>
                    </div>
                    <div style="margin-left:auto;">
                        <a href="logout.php" class="btn-logout">
                            <i class="fas fa-sign-out-alt"></i> Cerrar sesión
                        </a>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="profile-tabs">
                    <a href="perfil.php" class="active">👤 Mi perfil</a>
                    <a href="perfil.php?tab=pedidos">📦 Mis pedidos</a>
                </div>

                <!-- Contenido -->
                <div class="profile-section">
                    <?php if ($mensaje): ?>
                        <div class="alert alert-success"><?php echo $mensaje; ?></div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <?php if (isset($_GET['tab']) && $_GET['tab'] === 'pedidos'): ?>
                        <!-- PEDIDOS -->
                        <h2>📦 Mis pedidos</h2>
                        <?php if (count($pedidos) > 0): ?>
                            <?php foreach ($pedidos as $pedido): ?>
                                <div class="pedido-item">
                                    <div>
                                        <strong>#<?php echo htmlspecialchars($pedido['numero_pedido']); ?></strong>
                                        <br />
                                        <small style="color:#64748b;"><?php echo date('d/m/Y H:i', strtotime($pedido['created_at'])); ?></small>
                                    </div>
                                    <div>
                                        <span class="badge badge-<?php echo $pedido['estado']; ?>">
                                            <?php echo ucfirst($pedido['estado']); ?>
                                        </span>
                                    </div>
                                    <div>
                                        <strong>$<?php echo number_format($pedido['total'], 2); ?></strong>
                                    </div>
                                    <a href="confirmacion.php?pedido=<?php echo $pedido['numero_pedido']; ?>" 
                                       style="color:#3b82f6;text-decoration:none;font-weight:600;">
                                        Ver detalles →
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="color:#94a3b8;">Aún no has realizado ningún pedido.</p>
                        <?php endif; ?>
                    <?php else: ?>
                        <!-- PERFIL -->
                        <h2>👤 Editar perfil</h2>
                        <form method="POST">
                            <div class="form-group">
                                <label for="nombre">Nombre completo *</label>
                                <input type="text" id="nombre" name="nombre" 
                                       value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required />
                            </div>

                            <div class="form-group">
                                <label for="telefono">Teléfono</label>
                                <input type="tel" id="telefono" name="telefono" 
                                       value="<?php echo htmlspecialchars($usuario['telefono'] ?? ''); ?>" />
                            </div>

                            <div class="form-group">
                                <label for="direccion">Dirección de envío</label>
                                <input type="text" id="direccion" name="direccion" 
                                       value="<?php echo htmlspecialchars($usuario['direccion'] ?? ''); ?>" 
                                       placeholder="Calle, número, ciudad" />
                            </div>

                            <hr style="margin:2rem 0;border:none;border-top:1px solid #e2e8f0;" />

                            <h3>🔑 Cambiar contraseña</h3>
                            <p style="color:#94a3b8;font-size:0.9rem;">Deja en blanco si no quieres cambiarla</p>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="password">Nueva contraseña</label>
                                    <input type="password" id="password" name="password" />
                                </div>
                                <div class="form-group">
                                    <label for="password_confirm">Repetir contraseña</label>
                                    <input type="password" id="password_confirm" name="password_confirm" />
                                </div>
                            </div>

                            <button type="submit" class="btn-primary">
                                <i class="fas fa-save"></i> Guardar cambios
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- FOOTER -->
    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">
                <p>© <?php echo date('Y'); ?> <?php echo TIENDA_NOMBRE; ?>. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>
</body>
</html>