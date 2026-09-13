<?php
// =====================================================
// REGISTRO DE USUARIOS - VERSIÓN CORREGIDA
// =====================================================
session_start();
require_once 'includes/config.php';

$page_title = 'Crear cuenta';
$error = '';
$success = '';

// Si ya está logueado, redirigir
if (isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $telefono = trim($_POST['telefono'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    
    // Validaciones
    if (empty($nombre) || empty($email) || empty($password)) {
        $error = 'Por favor, completa todos los campos obligatorios';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Por favor, introduce un email válido';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres';
    } elseif ($password !== $password_confirm) {
        $error = 'Las contraseñas no coinciden';
    } else {
        try {
            // Verificar si el email ya existe
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'Este email ya está registrado';
            } else {
                // Crear usuario
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("
                    INSERT INTO usuarios (nombre, email, password_hash, telefono, direccion, rol, activo)
                    VALUES (?, ?, ?, ?, ?, 'cliente', 1)
                ");
                $stmt->execute([$nombre, $email, $hash, $telefono, $direccion]);
                
                $success = '✅ ¡Cuenta creada con éxito! Redirigiendo al login...';
                
                // Redirigir al login después de 2 segundos
                header('Refresh: 2; url=login.php');
            }
        } catch (PDOException $e) {
            $error = 'Error al crear la cuenta: ' . $e->getMessage();
        }
    }
}
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
        .auth-container {
            max-width: 500px;
            margin: 3rem auto;
            padding: 2rem;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }
        .auth-container h1 {
            font-size: 2rem;
            text-align: center;
            margin-bottom: 0.5rem;
        }
        .auth-container .subtitle {
            text-align: center;
            color: #64748b;
            margin-bottom: 2rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.3rem;
            font-size: 0.95rem;
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
        .btn-primary {
            width: 100%;
            padding: 0.8rem;
            font-size: 1.1rem;
            justify-content: center;
        }
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
        }
        .alert-danger {
            background: #fecaca;
            color: #991b1b;
        }
        .alert-success {
            background: #d1fae5;
            color: #065f46;
        }
        .auth-footer {
            text-align: center;
            margin-top: 1.5rem;
            color: #64748b;
        }
        .auth-footer a {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 600;
        }
        .auth-footer a:hover {
            text-decoration: underline;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        @media (max-width: 480px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            .auth-container {
                padding: 1.5rem;
                margin: 1rem;
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
                <a href="login.php">Iniciar sesión</a>
            </nav>
        </div>
    </header>

    <!-- REGISTRO -->
    <main>
        <div class="container">
            <div class="auth-container">
                <h1>📝 Crear cuenta</h1>
                <p class="subtitle">Regístrate para comprar más fácilmente</p>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="nombre">Nombre completo *</label>
                        <input type="text" id="nombre" name="nombre" 
                               value="<?php echo htmlspecialchars($_POST['nombre'] ?? ''); ?>" 
                               placeholder="Juan Pérez" required />
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" 
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                               placeholder="juan@email.com" required />
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="password">Contraseña *</label>
                            <input type="password" id="password" name="password" 
                                   placeholder="Mínimo 6 caracteres" required />
                        </div>
                        <div class="form-group">
                            <label for="password_confirm">Repetir contraseña *</label>
                            <input type="password" id="password_confirm" name="password_confirm" 
                                   placeholder="Repite tu contraseña" required />
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="telefono">Teléfono</label>
                        <input type="tel" id="telefono" name="telefono" 
                               value="<?php echo htmlspecialchars($_POST['telefono'] ?? ''); ?>" 
                               placeholder="+34 600 000 000" />
                    </div>
                    
                    <div class="form-group">
                        <label for="direccion">Dirección de envío</label>
                        <input type="text" id="direccion" name="direccion" 
                               placeholder="Calle, número, ciudad" 
                               value="<?php echo htmlspecialchars($_POST['direccion'] ?? ''); ?>" />
                    </div>
                    
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-user-plus"></i> Crear cuenta
                    </button>
                </form>
                
                <div class="auth-footer">
                    ¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a>
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