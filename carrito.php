<?php
// =====================================================
// PÁGINA DEL CARRITO DE COMPRAS
// =====================================================
require_once 'includes/config.php';

$page_title = 'Mi Carrito';
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
        /* ===== ESTILOS DEL CARRITO ===== */
        .cart-page {
            padding: 3rem 0;
            min-height: 60vh;
        }
        .page-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 2rem;
        }
        .cart-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }
        .cart-items {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            min-height: 200px;
        }
        .cart-item {
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 1.5rem;
            padding: 1rem 0;
            border-bottom: 1px solid #f1f5f9;
            align-items: center;
        }
        .cart-item:last-child {
            border-bottom: none;
        }
        .cart-item-image {
            width: 80px;
            height: 80px;
            flex-shrink: 0;
            border-radius: 10px;
            overflow: hidden;
        }
        .cart-item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .cart-item-info {
            flex: 1;
        }
        .cart-item-info h3 {
            font-size: 1rem;
            margin-bottom: 0.2rem;
        }
        .cart-item-category {
            font-size: 0.8rem;
            color: #94a3b8;
        }
        .cart-item-price {
            font-weight: 700;
            color: #3b82f6;
            margin-top: 0.3rem;
        }
        .cart-item-actions {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
        .quantity-control {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
        }
        .qty-btn {
            background: none;
            border: none;
            padding: 0.3rem 0.8rem;
            cursor: pointer;
            color: #0f172a;
            transition: background 0.2s;
            font-size: 1rem;
        }
        .qty-btn:hover {
            background: #f1f5f9;
        }
        .qty-number {
            min-width: 30px;
            text-align: center;
            font-weight: 600;
        }
        .cart-item-subtotal {
            font-weight: 700;
            min-width: 70px;
            text-align: right;
        }
        .btn-remove {
            background: none;
            border: none;
            color: #ef4444;
            cursor: pointer;
            padding: 0.3rem 0.5rem;
            transition: all 0.2s;
            border-radius: 6px;
            font-size: 1rem;
        }
        .btn-remove:hover {
            background: #fef2f2;
        }
        /* Carrito vacío */
        .cart-empty {
            text-align: center;
            padding: 4rem 2rem;
        }
        .cart-empty i {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
        }
        .cart-empty h2 {
            font-size: 1.5rem;
            margin: 1rem 0;
        }
        .cart-empty p {
            color: #64748b;
            margin-bottom: 1.5rem;
        }
        /* Resumen */
        .cart-summary {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            height: fit-content;
            position: sticky;
            top: 100px;
        }
        .cart-summary h3 {
            font-size: 1.2rem;
            margin-bottom: 1.5rem;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            color: #475569;
        }
        .summary-total {
            font-size: 1.2rem;
            font-weight: 800;
            color: #0f172a;
            border-top: 2px solid #e2e8f0;
            margin-top: 0.5rem;
            padding-top: 1rem;
        }
        .btn-checkout {
            width: 100%;
            justify-content: center;
            margin-top: 1rem;
            padding: 0.8rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
        }
        .btn-checkout:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59,130,246,0.3);
        }
        .btn-empty-cart {
            width: 100%;
            justify-content: center;
            margin-top: 0.5rem;
            padding: 0.8rem;
            background: #fef2f2;
            color: #ef4444;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-empty-cart:hover {
            background: #fecaca;
        }
        .btn-link {
            display: block;
            text-align: center;
            margin-top: 1rem;
            color: #3b82f6;
            text-decoration: none;
        }
        .btn-link:hover {
            text-decoration: underline;
        }
        .toast-notification {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: #0f172a;
            color: white;
            padding: 1rem 2rem;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            z-index: 9999;
            display: none;
            transition: opacity 0.3s;
            max-width: 350px;
        }
        /* Responsive */
        @media (max-width: 768px) {
            .cart-container {
                grid-template-columns: 1fr;
            }
            .cart-item {
                grid-template-columns: 1fr;
                gap: 0.8rem;
                text-align: center;
            }
            .cart-item-image {
                margin: 0 auto;
            }
            .cart-item-actions {
                justify-content: center;
                flex-wrap: wrap;
            }
            .cart-summary {
                position: static;
            }
        }
        @media (max-width: 480px) {
            .cart-item-actions {
                flex-direction: column;
                gap: 0.5rem;
            }
            .cart-item-subtotal {
                text-align: center;
            }
        }
    </style>
</head>
<body>

    <!-- ===== HEADER ===== -->
    <header class="navbar">
        <div class="container">
            <div class="nav-brand">
                <i class="fas fa-bolt" style="color: #3b82f6;"></i>
                <span><?php echo TIENDA_NOMBRE; ?></span>
            </div>
            <nav class="nav-links">
                <a href="index.php">Inicio</a>
                <a href="index.php#productos">Productos</a>
                <a href="carrito.php" class="active">Carrito</a>
            </nav>
            <div class="nav-actions">
                <a href="carrito.php" class="btn-cart">
                    <i class="fas fa-shopping-bag"></i>
                    <span class="cart-count" style="display:none;">0</span>
                    <span class="cart-total-preview">$0.00</span>
                </a>
            </div>
        </div>
    </header>

    <!-- ===== CARRITO ===== -->
    <main class="cart-page">
        <div class="container">
            <h1 class="page-title">🛒 Mi Carrito</h1>
            
            <div class="cart-container">
                <!-- Lista de productos -->
                <div class="cart-items" id="cartItems">
                    <!-- Renderizado por JavaScript -->
                </div>
                
                <!-- Mensaje carrito vacío -->
                <div id="cartVacio" class="cart-empty" style="display:none;">
                    <i class="fas fa-shopping-bag"></i>
                    <h2>Tu carrito está vacío</h2>
                    <p>¡Explora nuestros productos y comienza a comprar!</p>
                    <a href="index.php#productos" class="btn-primary" style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.8rem 2rem;border-radius:50px;background:linear-gradient(135deg,#3b82f6,#2563eb);color:white;text-decoration:none;font-weight:600;">
                        <i class="fas fa-arrow-left"></i> Ver productos
                    </a>
                </div>
                
                <!-- Resumen -->
                <div id="cartResumen" class="cart-summary" style="display:none;">
                    <h3>Resumen del pedido</h3>
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span id="cartSubtotal">$0.00</span>
                    </div>
                    <div class="summary-row">
                        <span>Envío</span>
                        <span>$<?php echo number_format(ENVIO_COSTO, 2); ?></span>
                    </div>
                    <div class="summary-row summary-total">
                        <span>Total</span>
                        <span id="cartTotal">$0.00</span>
                    </div>
                    <a href="checkout.php" class="btn-checkout">
                        <i class="fas fa-credit-card"></i> Proceder al pago
                    </a>
                    <button onclick="carrito.vaciar()" class="btn-empty-cart">
                        <i class="fas fa-trash"></i> Vaciar carrito
                    </button>
                    <a href="index.php#productos" class="btn-link">← Seguir comprando</a>
                </div>
            </div>
        </div>
    </main>

    <!-- ===== FOOTER ===== -->
    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">
                <p>© <?php echo date('Y'); ?> <?php echo TIENDA_NOMBRE; ?>. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- ===== SCRIPTS ===== -->
    <script src="js/carrito.js"></script>
    <script>
        // =====================================================
        // INICIALIZAR CARRITO
        // =====================================================
        document.addEventListener('DOMContentLoaded', function() {
            // Cargar carrito
            carrito.cargar();
            
            // Renderizar carrito
            carrito.renderizar();
            
            // Actualizar totales
            const subtotal = carrito.getTotal();
            const envio = 5.99;
            const total = subtotal + envio;
            
            const subtotalEl = document.getElementById('cartSubtotal');
            const totalEl = document.getElementById('cartTotal');
            
            if (subtotalEl) subtotalEl.textContent = `$${subtotal.toFixed(2)}`;
            if (totalEl) totalEl.textContent = `$${total.toFixed(2)}`;
        });
        
        // =====================================================
        // EXPONER FUNCIONES GLOBALES
        // =====================================================
        window.carrito = carrito;
    </script>
</body>
</html>