<?php
// =====================================================
// PÁGINA DE CONFIRMACIÓN
// =====================================================
require_once 'includes/config.php';

$numero_pedido = $_GET['pedido'] ?? '';
$page_title = '¡Pedido confirmado!';
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
        .confirmation-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 4rem 2rem;
            text-align: center;
        }
        .confirmation-icon {
            font-size: 5rem;
            color: #10b981;
            background: #d1fae5;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
        }
        .confirmation-container h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        .confirmation-container .order-number {
            background: #f8fafc;
            padding: 1rem;
            border-radius: 10px;
            font-size: 1.2rem;
            margin: 1.5rem 0;
        }
    </style>
</head>
<body>
    <header class="navbar">
        <div class="container">
            <div class="nav-brand">
                <i class="fas fa-bolt" style="color: #3b82f6;"></i>
                <span><?php echo TIENDA_NOMBRE; ?></span>
            </div>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="confirmation-container">
                <div class="confirmation-icon">
                    <i class="fas fa-check"></i>
                </div>
                
                <h1>¡Pedido confirmado!</h1>
                <p>Tu pedido se ha realizado con éxito.</p>
                
                <div class="order-number">
                    <strong>Número de pedido:</strong> #<?php echo htmlspecialchars($numero_pedido); ?>
                </div>
                
                <p>Te contactaremos en breve para coordinar el envío.</p>
                
                <a href="index.php#productos" class="btn-primary" style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.8rem 2rem;border-radius:50px;background:linear-gradient(135deg,#3b82f6,#2563eb);color:white;text-decoration:none;font-weight:600;">
                    <i class="fas fa-arrow-left"></i> Seguir comprando
                </a>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">
                <p>© <?php echo date('Y'); ?> <?php echo TIENDA_NOMBRE; ?>. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>
</body>
</html>