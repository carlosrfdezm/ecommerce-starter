<?php
// ============================================================
// INICIAR SESIÓN (SOLO UNA VEZ)
// ============================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Por favor, completa todos los campos';
    } else {
        try {
            // Buscar usuario por email
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ? AND activo = 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password_hash'])) {
                // Login exitoso
                $_SESSION['admin_logged'] = true;
                $_SESSION['admin_user_id'] = $user['id'];
                $_SESSION['admin_user_name'] = $user['nombre'];
                $_SESSION['admin_user_email'] = $user['email'];
                $_SESSION['admin_user_rol'] = $user['rol'];
                
                header('Location: index.php');
                exit;
            } else {
                $error = 'Email o contraseña incorrectos';
            }
        } catch (PDOException $e) {
            $error = 'Error al procesar la solicitud';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Panel Administración</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <i class="fas fa-store"></i>
                <h1>Panel Admin</h1>
                <p>Accede para gestionar tu tienda</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="login-form">
                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i>
                        Email
                    </label>
                    <input type="email" id="email" name="email" 
                           placeholder="admin@tiendapro.com" required>
                </div>
                
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i>
                        Contraseña
                    </label>
                    <input type="password" id="password" name="password" 
                           placeholder="••••••••" required>
                </div>
                
                <button type="submit" class="btn-primary btn-block">
                    <i class="fas fa-sign-in-alt"></i>
                    Iniciar sesión
                </button>
            </form>
            
            <div class="login-footer">
                <p>Credenciales: <strong>admin@tiendapro.com</strong> / <strong>admin123</strong></p>
            </div>
        </div>
    </div>
</body>
</html>