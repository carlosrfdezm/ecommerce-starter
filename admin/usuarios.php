<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
verificarRol(['admin']); // Solo administradores principales

$mensaje = '';
$error = '';

// =====================================================
// ELIMINAR USUARIO
// =====================================================
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // No permitir eliminar el propio usuario
    if ($id == $_SESSION['admin_user_id']) {
        $error = 'No puedes eliminar tu propio usuario';
    } else {
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        $mensaje = 'Usuario eliminado correctamente';
    }
}

// =====================================================
// CREAR / ACTUALIZAR USUARIO
// =====================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = $_POST['password'] ?? '';
    $rol = $_POST['rol'];
    $activo = isset($_POST['activo']) ? 1 : 0;
    
    if (empty($nombre) || empty($email)) {
        $error = 'Nombre y email son obligatorios';
    } else {
        try {
            if ($id > 0) {
                // Actualizar
                if (!empty($password)) {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE usuarios SET nombre = ?, email = ?, password_hash = ?, rol = ?, activo = ? WHERE id = ?");
                    $stmt->execute([$nombre, $email, $hash, $rol, $activo, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE usuarios SET nombre = ?, email = ?, rol = ?, activo = ? WHERE id = ?");
                    $stmt->execute([$nombre, $email, $rol, $activo, $id]);
                }
                $mensaje = 'Usuario actualizado correctamente';
            } else {
                // Crear
                if (empty($password)) {
                    $error = 'La contraseña es obligatoria para nuevos usuarios';
                } else {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password_hash, rol, activo) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$nombre, $email, $hash, $rol, $activo]);
                    $mensaje = 'Usuario creado correctamente';
                }
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = 'Este email ya está registrado';
            } else {
                $error = 'Error al guardar el usuario: ' . $e->getMessage();
            }
        }
    }
}

// =====================================================
// OBTENER USUARIO PARA EDITAR
// =====================================================
$usuario_edit = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
    $usuario_edit = $stmt->fetch();
}

// =====================================================
// LISTAR USUARIOS
// =====================================================
$stmt = $pdo->query("SELECT * FROM usuarios ORDER BY created_at DESC");
$usuarios = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios - Panel Administración</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <header class="content-header">
                <h1><i class="fas fa-users"></i> Usuarios</h1>
                <button class="btn-primary" onclick="abrirModal()">
                    <i class="fas fa-plus"></i> Nuevo usuario
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
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $user): ?>
                                <tr>
                                    <td>#<?php echo $user['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($user['nombre']); ?></strong>
                                        <?php if ($user['id'] == $_SESSION['admin_user_id']): ?>
                                            <span class="badge badge-info">(tú)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $user['rol'] == 'admin' ? 'success' : 'muted'; ?>">
                                            <?php echo ucfirst($user['rol']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($user['activo']): ?>
                                            <span class="badge badge-success">Activo</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <a href="?edit=<?php echo $user['id']; ?>" class="btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($user['id'] != $_SESSION['admin_user_id']): ?>
                                            <a href="?delete=<?php echo $user['id']; ?>" 
                                               class="btn-sm btn-danger" 
                                               onclick="return confirm('¿Eliminar este usuario?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    
    <!-- ===== MODAL ===== -->
    <div id="usuarioModal" class="modal" style="display: <?php echo $usuario_edit ? 'flex' : 'none'; ?>">
        <div class="modal-content">
            <div class="modal-header">
                <h2><?php echo $usuario_edit ? 'Editar usuario' : 'Nuevo usuario'; ?></h2>
                <button class="modal-close" onclick="cerrarModal()">&times;</button>
            </div>
            
            <form method="POST">
                <?php if ($usuario_edit): ?>
                    <input type="hidden" name="id" value="<?php echo $usuario_edit['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="nombre">Nombre *</label>
                    <input type="text" id="nombre" name="nombre" 
                           value="<?php echo $usuario_edit ? htmlspecialchars($usuario_edit['nombre']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" 
                           value="<?php echo $usuario_edit ? htmlspecialchars($usuario_edit['email']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password">
                        <?php echo $usuario_edit ? 'Contraseña (dejar en blanco para no cambiar)' : 'Contraseña *'; ?>
                    </label>
                    <input type="password" id="password" name="password" 
                           <?php echo $usuario_edit ? '' : 'required'; ?>>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="rol">Rol</label>
                        <select id="rol" name="rol">
                            <option value="admin" <?php echo ($usuario_edit && $usuario_edit['rol'] == 'admin') ? 'selected' : ''; ?>>Administrador</option>
                            <option value="editor" <?php echo ($usuario_edit && $usuario_edit['rol'] == 'editor') ? 'selected' : ''; ?>>Editor</option>
                            <option value="viewer" <?php echo ($usuario_edit && $usuario_edit['rol'] == 'viewer') ? 'selected' : ''; ?>>Solo lectura</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="activo" value="1"
                                <?php echo (!$usuario_edit || $usuario_edit['activo']) ? 'checked' : ''; ?>>
                            Usuario activo
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
            document.getElementById('usuarioModal').style.display = 'flex';
            document.querySelector('#usuarioModal form').reset();
            document.querySelector('input[name="id"]')?.remove();
            document.querySelector('#usuarioModal h2').textContent = 'Nuevo usuario';
            document.getElementById('password').required = true;
        }
        
        function cerrarModal() {
            document.getElementById('usuarioModal').style.display = 'none';
            window.location.href = 'usuarios.php';
        }
    </script>
</body>
</html>