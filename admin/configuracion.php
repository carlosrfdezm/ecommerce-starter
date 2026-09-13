<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
verificarAdmin();

$mensaje = '';
$error = '';

// =====================================================
// GUARDAR CONFIGURACIÓN
// =====================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $configuraciones = [
            'nombre_tienda' => $_POST['nombre_tienda'],
            'email_contacto' => $_POST['email_contacto'],
            'telefono' => $_POST['telefono'],
            'direccion' => $_POST['direccion'],
            'envio_costo_fijo' => $_POST['envio_costo_fijo'],
            'impuesto_porcentaje' => $_POST['impuesto_porcentaje']
        ];
        
        foreach ($configuraciones as $clave => $valor) {
            $stmt = $pdo->prepare("UPDATE configuracion SET valor = ? WHERE clave = ?");
            $stmt->execute([$valor, $clave]);
        }
        
        $mensaje = 'Configuración actualizada correctamente';
        
        // Recargar configuración
        $config = obtenerConfiguracion($pdo);
        
    } catch (Exception $e) {
        $error = 'Error al guardar la configuración: ' . $e->getMessage();
    }
}

// Obtener configuración actual
$stmt = $pdo->query("SELECT * FROM configuracion");
$config_data = [];
while ($row = $stmt->fetch()) {
    $config_data[$row['clave']] = $row['valor'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - Panel Administración</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <header class="content-header">
                <h1><i class="fas fa-cog"></i> Configuración</h1>
            </header>
            
            <?php if ($mensaje): ?>
                <div class="alert alert-success"><?php echo $mensaje; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-body">
                    <form method="POST">
                        <h3>Información de la tienda</h3>
                        
                        <div class="form-group">
                            <label for="nombre_tienda">Nombre de la tienda</label>
                            <input type="text" id="nombre_tienda" name="nombre_tienda" 
                                   value="<?php echo htmlspecialchars($config_data['nombre_tienda'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="email_contacto">Email de contacto</label>
                                <input type="email" id="email_contacto" name="email_contacto" 
                                       value="<?php echo htmlspecialchars($config_data['email_contacto'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="telefono">Teléfono</label>
                                <input type="text" id="telefono" name="telefono" 
                                       value="<?php echo htmlspecialchars($config_data['telefono'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="direccion">Dirección</label>
                            <input type="text" id="direccion" name="direccion" 
                                   value="<?php echo htmlspecialchars($config_data['direccion'] ?? ''); ?>">
                        </div>
                        
                        <hr style="margin: 2rem 0;">
                        
                        <h3>Envíos e impuestos</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="envio_costo_fijo">Costo fijo de envío ($)</label>
                                <input type="number" id="envio_costo_fijo" name="envio_costo_fijo" step="0.01" 
                                       value="<?php echo $config_data['envio_costo_fijo'] ?? 5.99; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="impuesto_porcentaje">Impuesto (%)</label>
                                <input type="number" id="impuesto_porcentaje" name="impuesto_porcentaje" step="0.1" 
                                       value="<?php echo $config_data['impuesto_porcentaje'] ?? 21; ?>">
                            </div>
                        </div>
                        
                        <div class="modal-actions">
                            <button type="submit" class="btn-primary">
                                <i class="fas fa-save"></i> Guardar configuración
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>