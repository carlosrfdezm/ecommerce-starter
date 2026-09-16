<?php
// =====================================================
// PÁGINA PRINCIPAL DE LA TIENDA
// =====================================================
session_start();
require_once 'includes/config.php';
$page_title = 'Inicio';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo $page_title; ?> - <?php echo TIENDA_NOMBRE; ?></title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="css/style.css" />
    
    
</head>
<body class="has-bars">

        <!-- ===================================================== -->
    <!-- HEADER CON BUSCADOR Y NAVEGACIÓN INTEGRADOS -->
    <!-- ===================================================== -->
        <header class="navbar">
        <div class="container">
            <div class="nav-brand">
                <i class="fas fa-bolt" style="color: #3b82f6;"></i>
                <span><?php echo TIENDA_NOMBRE; ?></span>
            </div>
            
            <nav class="nav-links" id="navLinks">
                <a href="index.php" class="active">Inicio</a>
                <a href="javascript:void(0)" onclick="abrirMenuCategorias(); cerrarMenuMovil();">
                    <i class="fas fa-th-large" style="margin-right:0.3rem;"></i> Categorías
                </a>
                <a href="#productos">Productos</a>
                <a href="#testimonios">Testimonios</a>
                <a href="#contacto">Contacto</a>
            </nav>
            
            <div class="nav-actions">
                <button type="button" class="btn-mobile" id="menuToggle">
                    <i class="fas fa-bars"></i>
                </button>
                
                <button type="button" class="btn-cart" onclick="abrirPanelCarrito()">
                    <i class="fas fa-shopping-bag"></i>
                    <span class="cart-count" style="display:none;">0</span>
                    <span class="cart-total-preview">$0.00</span>
                </button>
                
                <?php if (isset($_SESSION['usuario_id']) && $_SESSION['usuario_id'] > 0): ?>
                    <div class="user-menu">
                        <a href="perfil.php" class="btn-user" title="Mi perfil">
                            <i class="fas fa-user-circle"></i>
                            <span class="user-name"><?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></span>
                        </a>
                        <a href="logout.php" class="btn-logout-header" title="Cerrar sesión">
                            <i class="fas fa-sign-out-alt"></i>
                        </a>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn-user">
                        <i class="fas fa-user"></i>
                        <span>Iniciar sesión</span>
                    </a>
                    <a href="registro.php" class="btn-user btn-register">
                        <i class="fas fa-user-plus"></i>
                        <span>Registrarse</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    

    <!-- ===================================================== -->
    <!-- CARRUSEL DE BANNERS (solo si hay banners activos) -->
    <!-- ===================================================== -->
    <?php
    // Cargar banners activos
    $stmt_banners = $pdo->query("SELECT * FROM banners WHERE activo = 1 ORDER BY orden ASC, id ASC");
    $banners_home = $stmt_banners->fetchAll();
    
    if (count($banners_home) > 0):
    ?>
    <div class="banners-carousel" id="bannersCarousel">
        <div class="banners-track" id="bannersTrack">
            <?php foreach ($banners_home as $b): ?>
                <?php
                // Estilo del fondo según si hay imagen o no
                if (!empty($b['imagen_url'])) {
                    $bg_style = "background: linear-gradient(rgba(0,0,0,0.45), rgba(0,0,0,0.45)), url('" . htmlspecialchars($b['imagen_url']) . "') center/cover no-repeat;";
                } else {
                    $bg_style = "background: linear-gradient(135deg, " . htmlspecialchars($b['color_inicio']) . " 0%, " . htmlspecialchars($b['color_fin']) . " 100%);";
                }
                ?>
                <a href="<?php echo htmlspecialchars($b['enlace'] ?: '#'); ?>" 
                class="banner-slide"
                style="<?php echo $bg_style; ?>">
                    <i class="fas <?php echo htmlspecialchars($b['icono']); ?> banner-slide-icon"></i>
                    <div class="banner-slide-content">
                        <h3 class="banner-slide-title"><?php echo htmlspecialchars($b['titulo']); ?></h3>
                        <?php if ($b['subtitulo']): ?>
                            <p class="banner-slide-subtitle"><?php echo htmlspecialchars($b['subtitulo']); ?></p>
                        <?php endif; ?>
                    </div>
                    <?php if ($b['texto_boton']): ?>
                        <span class="banner-slide-cta">
                            <?php echo htmlspecialchars($b['texto_boton']); ?>
                            <i class="fas fa-arrow-right"></i>
                        </span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
        
        <?php if (count($banners_home) > 1): ?>
            <button class="banners-btn banners-btn-prev" onclick="moverBanner(-1)">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="banners-btn banners-btn-next" onclick="moverBanner(1)">
                <i class="fas fa-chevron-right"></i>
            </button>
            
            <div class="banners-dots" id="bannersDots">
                <?php foreach ($banners_home as $i => $b): ?>
                    <button class="banners-dot <?php echo $i === 0 ? 'active' : ''; ?>" 
                            onclick="irABanner(<?php echo $i; ?>)"></button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- ===================================================== -->
    <!-- BANNER PROMO FINO -->
    <!-- ===================================================== -->
    <div class="promo-strip">
        <i class="fas fa-truck"></i> Envío gratis en compras +$50
        <span class="sep hide-mobile">·</span>
        <span class="hide-mobile"><i class="fas fa-shield-alt"></i> Pago 100% seguro</span>
        <span class="sep hide-mobile">·</span>
        <span class="hide-mobile"><i class="fas fa-headset"></i> Soporte 24/7</span>
    </div>

        <!-- ===================================================== -->
    <!-- BARRA DE BÚSQUEDA (sticky) -->
    <!-- ===================================================== -->
    <div class="search-section">
        <div class="container">
            <form id="searchForm" class="search-form" onsubmit="event.preventDefault(); buscarProductos({ buscar: document.getElementById('searchInput').value });">
                <div class="search-wrapper">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Buscar productos, marcas y más..." />
                    <button type="submit" class="btn-search">
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

        <!-- ===================================================== -->
    <!-- FILTROS (sticky) -->
    <!-- ===================================================== -->
    <div class="filters-bar">
        <div class="container">
            <div class="filter-tabs">
                <button class="filter-btn active" onclick="buscarProductos({})">
                    <i class="fas fa-th-large"></i> Todos
                </button>
                <button class="filter-btn" onclick="filtrarMasVendidos()">
                    <i class="fas fa-fire"></i> Más vendidos
                </button>
                <button class="filter-btn" onclick="filtrarUltimos()">
                    <i class="fas fa-clock"></i> Últimos
                </button>
                <button class="filter-btn" onclick="filtrarPromociones()">
                    <i class="fas fa-tags"></i> Promociones
                </button>
                <div class="filter-dropdown">
                    <button class="filter-btn dropdown-toggle">
                        <i class="fas fa-sort"></i> Ordenar
                        <i class="fas fa-chevron-down" style="font-size:0.7rem;"></i>
                    </button>
                    <div class="dropdown-menu">
                        <a href="#" onclick="filtrarPrecioAsc(); return false;">
                            <i class="fas fa-arrow-up"></i> Precio: menor a mayor
                        </a>
                        <a href="#" onclick="filtrarPrecioDesc(); return false;">
                            <i class="fas fa-arrow-down"></i> Precio: mayor a menor
                        </a>
                        <a href="#" onclick="filtrarMasVendidos(); return false;">
                            <i class="fas fa-fire"></i> Más vendidos
                        </a>
                        <a href="#" onclick="filtrarUltimos(); return false;">
                            <i class="fas fa-clock"></i> Más recientes
                        </a>
                    </div>
                </div>
                <button class="filter-btn" onclick="resetearFiltros()" style="color:#ef4444;">
                    <i class="fas fa-times"></i> Limpiar
                </button>
            </div>
            <div id="resultadosContador" class="resultados-contador"></div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- SECCIÓN: DESTACADOS -->
    <!-- ===================================================== -->
    <section class="products featured-products" id="productos-destacados">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">⭐ Destacados</span>
                <h2>Lo más <span class="gradient-text">destacado</span></h2>
            </div>
            <div class="carousel-wrapper">
                <div class="carousel-container" id="featuredCarousel">
                    <div class="carousel-track" id="featuredTrack"></div>
                </div>
                <button class="carousel-btn carousel-btn-prev" onclick="moverCarousel('featuredTrack', -1)">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="carousel-btn carousel-btn-next" onclick="moverCarousel('featuredTrack', 1)">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
            <div class="carousel-dots" id="featuredDots"></div>
        </div>
    </section>

    <!-- ===================================================== -->
    <!-- SECCIÓN: MÁS VENDIDOS -->
    <!-- ===================================================== -->
    <section class="products best-sellers" id="productos-vendidos">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">🔥 Más vendidos</span>
                <h2>Los más <span class="gradient-text">vendidos</span></h2>
            </div>
            <div class="carousel-wrapper">
                <div class="carousel-container" id="bestsellerCarousel">
                    <div class="carousel-track" id="bestsellerTrack"></div>
                </div>
                <button class="carousel-btn carousel-btn-prev" onclick="moverCarousel('bestsellerTrack', -1)">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="carousel-btn carousel-btn-next" onclick="moverCarousel('bestsellerTrack', 1)">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
            <div class="carousel-dots" id="bestsellerDots"></div>
        </div>
    </section>

    <!-- ===================================================== -->
    <!-- SECCIÓN: NUEVOS PRODUCTOS -->
    <!-- ===================================================== -->
    <section class="products new-products" id="productos-nuevos">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">🆕 Nuevos</span>
                <h2>Últimos <span class="gradient-text">lanzamientos</span></h2>
            </div>
            <div class="carousel-wrapper">
                <div class="carousel-container" id="newCarousel">
                    <div class="carousel-track" id="newTrack"></div>
                </div>
                <button class="carousel-btn carousel-btn-prev" onclick="moverCarousel('newTrack', -1)">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="carousel-btn carousel-btn-next" onclick="moverCarousel('newTrack', 1)">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
            <div class="carousel-dots" id="newDots"></div>
        </div>
    </section>

    <!-- ===================================================== -->
    <!-- SECCIÓN: TODOS LOS PRODUCTOS -->
    <!-- ===================================================== -->
    <section class="products all-products" id="productos">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">📦 Catálogo completo</span>
                <h2>Todos los <span class="gradient-text">productos</span></h2>
            </div>
            <div class="product-grid" id="productGrid"></div>
        </div>
    </section>

    <!-- ===================================================== -->
    <!-- TESTIMONIOS -->
    <!-- ===================================================== -->
    <section class="testimonials" id="testimonios">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">Testimonios</span>
                <h2>Lo que dicen <span class="gradient-text">nuestros clientes</span></h2>
            </div>
            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <div class="testimonial-stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                    <p>"Mi tienda pasó de cargar en 4 segundos a 0.8 segundos. Mis ventas subieron un 23%."</p>
                    <div class="testimonial-author">
                        <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="" />
                        <div><strong>Carlos M.</strong><span>Dueño de tienda de moda</span></div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                    <p>"El soporte es increíble. En menos de 1 hora ya tenía mi tienda funcionando."</p>
                    <div class="testimonial-author">
                        <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="" />
                        <div><strong>Laura G.</strong><span>Emprendedora digital</span></div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                    <p>"La mejor inversión para mi negocio. Ahora puedo gestionar mis productos sin complicaciones."</p>
                    <div class="testimonial-author">
                        <img src="https://randomuser.me/api/portraits/men/67.jpg" alt="" />
                        <div><strong>Javier R.</strong><span>CEO de tienda tech</span></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===================================================== -->
    <!-- NEWSLETTER -->
    <!-- ===================================================== -->
    <section class="newsletter" id="contacto">
        <div class="container">
            <div class="newsletter-content">
                <h2>¿Listo para <span class="gradient-text">impulsar tu negocio</span>?</h2>
                <p>Suscríbete para recibir ofertas exclusivas</p>
                <form class="newsletter-form" id="newsletterForm">
                    <input type="email" placeholder="tu@email.com" required />
                    <button type="submit" class="btn-primary"><i class="fas fa-paper-plane"></i> Suscribirme</button>
                </form>
            </div>
        </div>
    </section>

    <!-- ===================================================== -->
    <!-- FOOTER -->
    <!-- ===================================================== -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <div class="nav-brand"><i class="fas fa-bolt" style="color:#3b82f6;"></i> <span><?php echo TIENDA_NOMBRE; ?></span></div>
                    <p>La tienda online más rápida y segura del mercado.</p>
                    <div class="footer-social">
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="footer-links">
                    <h4>Enlaces</h4>
                    <a href="index.php">Inicio</a>
                    <a href="#productos">Productos</a>
                    <a href="#testimonios">Testimonios</a>
                    <a href="#contacto">Contacto</a>
                </div>
                <div class="footer-links">
                    <h4>Soporte</h4>
                    <a href="#">Preguntas frecuentes</a>
                    <a href="#">Política de privacidad</a>
                    <a href="#">Términos y condiciones</a>
                </div>
                <div class="footer-contact">
                    <h4>Contacto</h4>
                    <p><i class="fas fa-envelope"></i> <?php echo TIENDA_EMAIL; ?></p>
                    <p><i class="fas fa-phone"></i> <?php echo TIENDA_TELEFONO; ?></p>
                    <p><i class="fas fa-map-marker-alt"></i> <?php echo TIENDA_DIRECCION; ?></p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>© <?php echo date('Y'); ?> <?php echo TIENDA_NOMBRE; ?>.</p>
                <div class="footer-payments">
                    <i class="fab fa-cc-visa"></i>
                    <i class="fab fa-cc-mastercard"></i>
                    <i class="fab fa-cc-paypal"></i>
                    <i class="fab fa-cc-stripe"></i>
                </div>
            </div>
        </div>
    </footer>

    <!-- ===================================================== -->
    <!-- SCRIPTS -->
    <!-- ===================================================== -->
    <script src="js/main.js"></script>
    <script src="js/carrito.js"></script>
    <script src="js/buscar.js"></script>
    <script src="js/slide-cart.js"></script>
    <script src="js/menu-categorias.js"></script>
    
    <script>
    function cerrarMenuMovil() {
        var navLinks = document.getElementById('navLinks');
        var menuToggle = document.getElementById('menuToggle');
        if (navLinks) navLinks.classList.remove('active');
        if (menuToggle) menuToggle.innerHTML = '<i class="fas fa-bars"></i>';
    }

    (function() {
        console.log('🚀 Iniciando tienda...');
        
        if (typeof carrito !== 'undefined') carrito.cargar();
        if (typeof loadFeaturedProducts === 'function') loadFeaturedProducts();
        if (typeof loadBestsellerProducts === 'function') loadBestsellerProducts();
        if (typeof loadNewProducts === 'function') loadNewProducts();
        if (typeof loadAllProducts === 'function') loadAllProducts();
        
        var menuToggle = document.getElementById('menuToggle');
        var navLinks = document.getElementById('navLinks');
        
        if (menuToggle && navLinks) {
            menuToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                navLinks.classList.toggle('active');
                this.innerHTML = navLinks.classList.contains('active') 
                    ? '<i class="fas fa-times"></i>' 
                    : '<i class="fas fa-bars"></i>';
            });
            
            navLinks.querySelectorAll('a').forEach(function(link) {
                link.addEventListener('click', function() {
                    navLinks.classList.remove('active');
                    menuToggle.innerHTML = '<i class="fas fa-bars"></i>';
                });
            });
            
            document.addEventListener('click', function(e) {
                if (!navLinks.contains(e.target) && !menuToggle.contains(e.target)) {
                    navLinks.classList.remove('active');
                    menuToggle.innerHTML = '<i class="fas fa-bars"></i>';
                }
            });
        }
        
        var newsletterForm = document.getElementById('newsletterForm');
        if (newsletterForm) {
            newsletterForm.addEventListener('submit', function(e) {
                e.preventDefault();
                var input = this.querySelector('input[type="email"]');
                if (input && input.value) {
                    alert('✅ ¡Gracias por suscribirte!');
                    input.value = '';
                }
            });
        }
        
        document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
            anchor.addEventListener('click', function(e) {
                var href = this.getAttribute('href');
                
                // Ignorar enlaces que solo son "#" o "#algo-vacío"
                if (!href || href === '#') return;
                
                // Ignorar enlaces que tengan onclick (filtros, dropdown, etc.)
                if (this.hasAttribute('onclick')) return;
                
                e.preventDefault();
                try {
                    var target = document.querySelector(href);
                    if (target) target.scrollIntoView({ behavior: 'smooth' });
                } catch (err) {
                    console.warn('Selector inválido:', href, err);
                }
            });
        });

        // =====================================================
        // DROPDOWN DE ORDENAR - POSICIONAMIENTO FIJO
        // =====================================================
        (function() {
            var dropdown = document.querySelector('.filter-dropdown');
            if (!dropdown) return;
            
            var trigger = dropdown.querySelector('.dropdown-toggle');
            var menu = dropdown.querySelector('.dropdown-menu');
            if (!trigger || !menu) return;
            
            function abrir() {
                var rect = trigger.getBoundingClientRect();
                menu.style.top = (rect.bottom + 4) + 'px';
                menu.style.left = rect.left + 'px';
                dropdown.classList.add('open');
            }
            
            function cerrar() {
                dropdown.classList.remove('open');
            }
            
            // Abrir al pasar el mouse por el botón
            trigger.addEventListener('mouseenter', abrir);
            
            // Mantener abierto mientras el mouse esté sobre el botón o el menú
            var timeout;
            function resetTimeout() {
                clearTimeout(timeout);
                timeout = setTimeout(function() {
                    if (!dropdown.matches(':hover') && !menu.matches(':hover')) {
                        cerrar();
                    }
                }, 200);
            }
            
            trigger.addEventListener('mouseleave', resetTimeout);
            menu.addEventListener('mouseleave', resetTimeout);
            menu.addEventListener('mouseenter', function() { clearTimeout(timeout); });
            
            // Cerrar al hacer clic fuera
            document.addEventListener('click', function(e) {
                if (!dropdown.contains(e.target) && !menu.contains(e.target)) {
                    cerrar();
                }
            });
            
            // Cerrar al hacer scroll (evita que quede flotando lejos)
            window.addEventListener('scroll', cerrar, { passive: true });
            
            // Cerrar al hacer clic en una opción
            menu.querySelectorAll('a').forEach(function(link) {
                link.addEventListener('click', cerrar);
            });
        })();

        // =====================================================
        // CARRUSEL DE BANNERS
        // =====================================================
        (function() {
            var track = document.getElementById('bannersTrack');
            var dotsContainer = document.getElementById('bannersDots');
            if (!track) return;
            
            var slides = track.querySelectorAll('.banner-slide');
            var total = slides.length;
            if (total <= 1) return;
            
            var index = 0;
            var autoplayTimer = null;
            
            function aplicarTransform() {
                track.style.transform = 'translateX(-' + (index * 100) + '%)';
            }
            
            function actualizarDots() {
                if (!dotsContainer) return;
                var dots = dotsContainer.querySelectorAll('.banners-dot');
                dots.forEach(function(d, i) {
                    d.classList.toggle('active', i === index);
                });
            }
            
            window.moverBanner = function(direccion) {
                index += direccion;
                if (index < 0) index = total - 1;
                if (index >= total) index = 0;
                aplicarTransform();
                actualizarDots();
            };
            
            window.irABanner = function(i) {
                index = i;
                aplicarTransform();
                actualizarDots();
            };
            
            function iniciarAutoplay() {
                detenerAutoplay();
                autoplayTimer = setInterval(function() {
                    window.moverBanner(1);
                }, 5000);
            }
            
            function detenerAutoplay() {
                if (autoplayTimer) {
                    clearInterval(autoplayTimer);
                    autoplayTimer = null;
                }
            }
            
            // Pausar al hover
            var carousel = document.getElementById('bannersCarousel');
            if (carousel) {
                carousel.addEventListener('mouseenter', detenerAutoplay);
                carousel.addEventListener('mouseleave', iniciarAutoplay);
            }
            
            // Swipe táctil
            var startX = 0, endX = 0, isSwiping = false;
            track.addEventListener('touchstart', function(e) {
                startX = e.changedTouches[0].screenX;
                isSwiping = true;
                detenerAutoplay();
            }, { passive: true });
            
            track.addEventListener('touchmove', function(e) {
                if (!isSwiping) return;
                endX = e.changedTouches[0].screenX;
            }, { passive: true });
            
            track.addEventListener('touchend', function() {
                if (!isSwiping) return;
                isSwiping = false;
                var delta = endX - startX;
                if (Math.abs(delta) > 50) {
                    window.moverBanner(delta < 0 ? 1 : -1);
                }
                iniciarAutoplay();
            }, { passive: true });
            
            iniciarAutoplay();
        })();
        
        console.log('✅ Tienda inicializada');
    })();


    // =====================================================
    // OCULTAR BANNER AL HACER SCROLL
    // =====================================================
    (function() {
        var lastScrollY = window.scrollY;
        var scrollThreshold = 100; // píxeles antes de ocultar el banner
        var body = document.body;
        
        function handleScroll() {
            var currentScrollY = window.scrollY;
            
            // Si hemos bajado más de X píxeles → ocultar banner
            if (currentScrollY > scrollThreshold) {
                body.classList.add('banner-hidden');
            } else {
                body.classList.remove('banner-hidden');
            }
            
            lastScrollY = currentScrollY;
        }
        
        // Throttle con requestAnimationFrame para mejor rendimiento
        var ticking = false;
        window.addEventListener('scroll', function() {
            if (!ticking) {
                window.requestAnimationFrame(function() {
                    handleScroll();
                    ticking = false;
                });
                ticking = true;
            }
        }, { passive: true });
    })();
    </script>
</body>
</html>