<?php
// =====================================================
// PÁGINA DE CHECKOUT - VERSIÓN CON STRIPE
// =====================================================
require_once 'includes/config.php';

$page_title = 'Checkout';

// =====================================================
// CONFIGURAR STRIPE (CLAVE PÚBLICA)
// =====================================================
$stripe_public_key = STRIPE_PUBLIC_KEY;
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
        /* ===== ESTILOS DEL CHECKOUT ===== */
        .checkout-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 2rem 0;
        }
        .checkout-form .form-group {
            margin-bottom: 1.5rem;
        }
        .checkout-form label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.3rem;
            font-size: 0.95rem;
        }
        .checkout-form input {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.2s;
        }
        .checkout-form input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
        }
        .checkout-form input::placeholder {
            color: #94a3b8;
        }
        .checkout-summary {
            background: #f8fafc;
            padding: 1.5rem;
            border-radius: 16px;
            margin: 2rem 0;
            border: 1px solid #e2e8f0;
        }
        .checkout-summary h3 {
            font-size: 1.1rem;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e2e8f0;
        }
        .checkout-summary .row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            color: #475569;
        }
        .checkout-summary .row:last-child {
            border-bottom: none;
        }
        .checkout-summary .total {
            font-size: 1.3rem;
            font-weight: 800;
            color: #0f172a;
            border-top: 2px solid #e2e8f0;
            padding-top: 1rem;
            margin-top: 0.5rem;
        }
        .btn-checkout {
            width: 100%;
            padding: 1rem;
            font-size: 1.2rem;
            justify-content: center;
            display: inline-flex;
            align-items: center;
            gap: 0.8rem;
        }
        .btn-checkout:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }
        .btn-checkout:disabled:hover {
            transform: none !important;
            box-shadow: none !important;
        }
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }
        .alert-danger {
            background: #fecaca;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        .text-muted {
            color: #94a3b8;
            font-size: 0.85rem;
        }
        .text-center {
            text-align: center;
        }
        .mt-1 {
            margin-top: 1rem;
        }
        .mb-1 {
            margin-bottom: 1rem;
        }
        
        /* ===== STRIPE ELEMENT ===== */
        #card-element {
            padding: 0.8rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            background: white;
            transition: border-color 0.2s;
            min-height: 48px;
        }
        #card-element:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
        }
        #card-errors {
            color: #ef4444;
            margin-top: 0.5rem;
            font-size: 0.9rem;
        }
        
        /* ===== RESPONSIVE ===== */
        @media (max-width: 640px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            .checkout-container {
                padding: 1rem;
            }
            .page-title {
                font-size: 1.8rem;
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
                <a href="carrito.php">Carrito</a>
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

    <!-- ===== CHECKOUT ===== -->
    <main class="checkout-page">
        <div class="container">
            <div class="checkout-container">
                
                <h1 class="page-title">💳 Finalizar compra</h1>
                
                <!-- Mensajes de error/éxito -->
                <div id="checkoutError" class="alert alert-danger" style="display:none;">
                    <i class="fas fa-exclamation-circle"></i>
                    <span id="checkoutErrorText"></span>
                </div>
                <div id="checkoutSuccess" class="alert alert-success" style="display:none;">
                    <i class="fas fa-check-circle"></i>
                    <span id="checkoutSuccessText"></span>
                </div>
                
                <!-- ===== FORMULARIO ===== -->
                <form id="checkoutForm" class="checkout-form" autocomplete="on">
                    
                    <!-- Datos del cliente -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nombre">
                                <i class="fas fa-user"></i> Nombre completo *
                            </label>
                            <input type="text" id="nombre" name="nombre" 
                                   placeholder="Juan Pérez" required />
                        </div>
                        <div class="form-group">
                            <label for="email">
                                <i class="fas fa-envelope"></i> Email *
                            </label>
                            <input type="email" id="email" name="email" 
                                   placeholder="juan@email.com" required />
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="telefono">
                            <i class="fas fa-phone"></i> Teléfono
                        </label>
                        <input type="tel" id="telefono" name="telefono" 
                               placeholder="+34 600 000 000" />
                    </div>
                    
                    <div class="form-group">
                        <label for="direccion">
                            <i class="fas fa-map-marker-alt"></i> Dirección de envío *
                        </label>
                        <input type="text" id="direccion" name="direccion" 
                               placeholder="Calle, número, ciudad, código postal" required />
                    </div>
                    
                    <!-- Resumen del pedido -->
                    <div class="checkout-summary" id="checkoutSummary">
                        <h3>📋 Resumen del pedido</h3>
                        <div id="checkoutItems">
                            <!-- Cargado por JavaScript -->
                        </div>
                        <div class="row">
                            <span>Subtotal</span>
                            <span id="checkoutSubtotal">$0.00</span>
                        </div>
                        <div class="row">
                            <span>Envío</span>
                            <span id="checkoutEnvio">$<?php echo number_format(ENVIO_COSTO, 2); ?></span>
                        </div>
                        <div class="row total">
                            <span>Total</span>
                            <span id="checkoutTotal">$0.00</span>
                        </div>
                    </div>
                    
                    <!-- ===== STRIPE: TARJETA ===== -->
                    <div class="form-group">
                        <label for="card-element">
                            <i class="fas fa-credit-card"></i> Datos de la tarjeta
                        </label>
                        <div id="card-element"></div>
                        <div id="card-errors" role="alert"></div>
                    </div>
                    
                    <!-- Botón de confirmación -->
                    <button type="submit" class="btn-primary btn-checkout" id="submitBtn">
                        <i class="fas fa-lock"></i> Pagar ahora
                    </button>
                    
                    <div class="text-center mt-1">
                        <p class="text-muted">
                            <i class="fas fa-lock"></i> Pagos seguros con Stripe
                        </p>
                        <p class="text-muted" style="font-size:0.8rem;margin-top:0.3rem;">
                            Al confirmar, aceptas nuestros términos y condiciones.
                        </p>
                    </div>
                    
                </form>
                
            </div>
        </div>
    </main>

    <!-- ===== FOOTER ===== -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <div class="nav-brand">
                        <i class="fas fa-bolt" style="color: #3b82f6;"></i>
                        <span><?php echo TIENDA_NOMBRE; ?></span>
                    </div>
                    <p><?php echo TIENDA_DIRECCION; ?></p>
                </div>
                <div class="footer-links">
                    <h4>Enlaces</h4>
                    <a href="index.php">Inicio</a>
                    <a href="index.php#productos">Productos</a>
                    <a href="carrito.php">Carrito</a>
                </div>
                <div class="footer-contact">
                    <h4>Contacto</h4>
                    <p><i class="fas fa-envelope"></i> <?php echo TIENDA_EMAIL; ?></p>
                    <p><i class="fas fa-phone"></i> <?php echo TIENDA_TELEFONO; ?></p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>© <?php echo date('Y'); ?> <?php echo TIENDA_NOMBRE; ?>. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- ===== SCRIPTS ===== -->
    <script src="https://js.stripe.com/v3/"></script>
    <script src="js/carrito.js"></script>
    
    <script>
        // =====================================================
        // CONFIGURACIÓN STRIPE
        // =====================================================
        const stripe = Stripe('<?php echo $stripe_public_key; ?>');
        const elements = stripe.elements();
        
        // Crear elemento de tarjeta
        const cardElement = elements.create('card', {
            style: {
                base: {
                    fontSize: '16px',
                    color: '#0f172a',
                    '::placeholder': {
                        color: '#94a3b8'
                    }
                }
            }
        });
        cardElement.mount('#card-element');
        
        // Manejar errores de la tarjeta
        cardElement.addEventListener('change', function(event) {
            const displayError = document.getElementById('card-errors');
            if (event.error) {
                displayError.textContent = event.error.message;
            } else {
                displayError.textContent = '';
            }
        });
        
        // =====================================================
        // INICIALIZAR
        // =====================================================
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🚀 Inicializando checkout con Stripe...');
            
            // Verificar que el carrito existe
            if (typeof carrito === 'undefined') {
                console.error('❌ Carrito no disponible');
                alert('Error: El carrito no está disponible');
                return;
            }
            
            // Cargar carrito
            carrito.cargar();
            
            // Verificar que el carrito no esté vacío
            if (carrito.items.length === 0) {
                window.location.href = 'carrito.php';
                return;
            }
            
            // Cargar resumen
            cargarResumen();
            
            console.log('✅ Checkout inicializado');
            console.log('📦 Productos en carrito:', carrito.items.length);
        });
        
        // =====================================================
        // CARGAR RESUMEN DEL PEDIDO
        // =====================================================
        function cargarResumen() {
            const items = carrito.items;
            const container = document.getElementById('checkoutItems');
            
            if (items.length === 0) {
                container.innerHTML = '<p style="color:#94a3b8;text-align:center;">No hay productos en el carrito</p>';
                return;
            }
            
            let html = '';
            items.forEach(item => {
                const precio = parseFloat(item.precio) || 0;
                const cantidad = parseInt(item.cantidad) || 0;
                const subtotal = (precio * cantidad).toFixed(2);
                
                html += `
                    <div class="row">
                        <span>${item.nombre} × ${cantidad}</span>
                        <span>$${subtotal}</span>
                    </div>
                `;
            });
            container.innerHTML = html;
            
            const subtotal = carrito.getTotal();
            const envio = <?php echo ENVIO_COSTO; ?>;
            const total = subtotal + envio;
            
            document.getElementById('checkoutSubtotal').textContent = `$${subtotal.toFixed(2)}`;
            document.getElementById('checkoutTotal').textContent = `$${total.toFixed(2)}`;
        }
        
        // =====================================================
        // PROCESAR PAGO CON STRIPE
        // =====================================================
        document.getElementById('checkoutForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Ocultar mensajes anteriores
            document.getElementById('checkoutError').style.display = 'none';
            document.getElementById('checkoutSuccess').style.display = 'none';
            
            // Deshabilitar botón
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando pago...';
            
            // Obtener datos del cliente
            const nombre = document.getElementById('nombre').value.trim();
            const email = document.getElementById('email').value.trim();
            const telefono = document.getElementById('telefono').value.trim();
            const direccion = document.getElementById('direccion').value.trim();
            
            // Validar campos obligatorios
            if (!nombre || !email || !direccion) {
                mostrarError('Por favor, completa todos los campos obligatorios');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-lock"></i> Pagar ahora';
                return;
            }
            
            // Validar email
            if (!email.includes('@') || !email.includes('.')) {
                mostrarError('Por favor, ingresa un email válido');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-lock"></i> Pagar ahora';
                return;
            }
            
            // Verificar que el carrito no esté vacío
            if (carrito.items.length === 0) {
                mostrarError('El carrito está vacío');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-lock"></i> Pagar ahora';
                return;
            }
            
            // Construir datos del pedido
            const total = carrito.getTotal() + <?php echo ENVIO_COSTO; ?>;
            const datos = {
                usuario_id: <?php echo isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : 'null'; ?>,
                sesion_id: (typeof carrito !== 'undefined' && carrito.getSesionId) ? carrito.getSesionId() : null,
                nombre: nombre,
                email: email,
                telefono: telefono || 'No especificado',
                direccion: direccion,
                items: carrito.items.map(item => ({
                    id: item.id,
                    nombre: item.nombre,
                    precio: parseFloat(item.precio) || 0,
                    cantidad: parseInt(item.cantidad) || 0
                })),
                total: total
            };
            
            console.log('📦 Enviando pedido:', datos);
            
            try {
                // =====================================================
                // 1. CREAR PEDIDO EN EL BACKEND
                // =====================================================
                const response = await fetch('api/pedidos/crear.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(datos)
                });
                
                const result = await response.json();
                console.log('📥 Respuesta del servidor:', result);
                
                if (!result.success) {
                    throw new Error(result.error || 'Error al crear el pedido');
                }
                
                // =====================================================
                // 2. CONFIRMAR PAGO CON STRIPE
                // =====================================================
                const { error, paymentIntent } = await stripe.confirmCardPayment(
                    result.client_secret,
                    {
                        payment_method: {
                            card: cardElement,
                            billing_details: {
                                name: nombre,
                                email: email
                            }
                        }
                    }
                );
                
                if (error) {
                    throw new Error(error.message);
                }
                
                // =====================================================
                // 3. PAGO EXITOSO
                // =====================================================
                if (paymentIntent.status === 'succeeded') {
                    // ✅ NUEVO: Confirmar el pedido en el backend (descuenta stock)
                    try {
                        const confResp = await fetch('api/pedidos/confirmar.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ pedido_id: result.pedido_id })
                        });
                        const confData = await confResp.json();
                        
                        if (!confData.success) {
                            console.error('Error confirmando pedido:', confData.error);
                            // El pago está hecho pero el stock falló → avisar al usuario
                            mostrarError('El pago se realizó pero hubo un problema: ' + confData.error + '. Contacta con soporte con el pedido #' + result.numero_pedido);
                            return;
                        }
                    } catch (err) {
                        console.error('Error de red confirmando pedido:', err);
                        mostrarError('El pago se realizó pero no pudimos confirmar el pedido. Contacta con soporte con el #' + result.numero_pedido);
                        return;
                    }
                    
                    // Vaciar carrito (también libera reservas)
                    carrito.vaciar();
                    
                    mostrarExito(`¡Pago completado! Pedido #${result.numero_pedido}`);
                    submitBtn.innerHTML = '<i class="fas fa-check"></i> Pago completado';
                    
                    setTimeout(() => {
                        window.location.href = `confirmacion.php?pedido=${result.numero_pedido}`;
                    }, 2000);
                }
            } catch (err) {
                console.error('❌ Error en el proceso de pago:', err);
                mostrarError(err.message || 'Ocurrió un error inesperado');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-lock"></i> Pagar ahora';
            }

            
        });
        
        // =====================================================
        // FUNCIONES DE UTILIDAD
        // =====================================================
        function mostrarError(mensaje) {
            const errorDiv = document.getElementById('checkoutError');
            document.getElementById('checkoutErrorText').textContent = mensaje;
            errorDiv.style.display = 'flex';
        }
        
        function mostrarExito(mensaje) {
            const successDiv = document.getElementById('checkoutSuccess');
            document.getElementById('checkoutSuccessText').textContent = mensaje;
            successDiv.style.display = 'flex';
        }
    </script>
</body>
</html>