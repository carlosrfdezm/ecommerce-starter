<?php
// =====================================================
// LOGIN DE CLIENTES - VERSIÓN CORREGIDA
// =====================================================
session_start();
require_once 'includes/config.php';

$page_title = 'Iniciar sesión';
$error = '';
$success = '';

// Si ya está logueado, redirigir según el rol
if (isset($_SESSION['usuario_id'])) {
    if ($_SESSION['usuario_rol'] === 'admin') {
        header('Location: admin/index.php');
    } else {
        header('Location: index.php');
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $redirect = $_GET['redirect'] ?? 'index.php';
    
    if (empty($email) || empty($password)) {
        $error = 'Por favor, completa todos los campos';
    } else {
        try {
            // Buscar usuario por email
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            // Depuración: Verificar si se encontró el usuario
            if (!$user) {
                $error = 'Email o contraseña incorrectos';
                error_log("Login fallido: Usuario no encontrado - $email");
            } elseif ($user['activo'] != 1) {
                $error = 'Tu cuenta está desactivada. Contacta con el administrador.';
                error_log("Login fallido: Usuario inactivo - $email");
            } elseif (!password_verify($password, $user['password_hash'])) {
                $error = 'Email o contraseña incorrectos';
                error_log("Login fallido: Contraseña incorrecta - $email");
            } else {
                // Login exitoso
                $_SESSION['usuario_id'] = $user['id'];
                $_SESSION['usuario_nombre'] = $user['nombre'];
                $_SESSION['usuario_email'] = $user['email'];
                $_SESSION['usuario_rol'] = $user['rol'];
                
                // Actualizar último acceso
                $stmt = $pdo->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?");
                $stmt->execute([$user['id']]);
                
                error_log("Login exitoso: $email - Rol: " . $user['rol']);
                
                // Redirigir según el rol
                if ($user['rol'] === 'admin') {
                    header('Location: admin/index.php');
                } else {
                    header('Location: ' . $redirect);
                }
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Error al procesar la solicitud';
            error_log("Error en login: " . $e->getMessage());
        }
    }
}

// Obtener el redirect de la URL
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';
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
            max-width: 420px;
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
        @media (max-width: 480px) {
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
                <a href="registro.php">Registrarse</a>
            </nav>
        </div>
    </header>

    <!-- LOGIN -->
    <main>
        <div class="container">
            <div class="auth-container">
                <h1>🔐 Iniciar sesión</h1>
                <p class="subtitle">Accede a tu cuenta para comprar</p>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="login.php<?php echo $redirect ? '?redirect=' . urlencode($redirect) : ''; ?>">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" 
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                               placeholder="tu@email.com" required />
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <input type="password" id="password" name="password" 
                               placeholder="••••••••" required />
                    </div>
                    
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-sign-in-alt"></i> Iniciar sesión
                    </button>
                </form>
                
                <div class="auth-footer">
                    ¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a>
                </div>
                <div class="auth-footer" style="margin-top:0.5rem;font-size:0.85rem;">
                    <a href="admin/login.php">Acceso administrador</a>
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

    <!-- Depuración: Mostrar estado de la sesión -->
    <?php if (isset($_SESSION['usuario_id'])): ?>
        <script>
            console.log('✅ Usuario logueado:', <?php echo json_encode($_SESSION['usuario_nombre']); ?>);
        </script>
    <?php endif; ?>
</body>
</html>