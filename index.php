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
    
    <style>
        /* =====================================================
           MENÚ MÓVIL
           ===================================================== */
        .btn-mobile {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #0f172a;
            padding: 0.3rem;
        }
        
        .btn-user {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.4rem 1rem;
            color: #334155;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            border-radius: 30px;
            transition: all 0.2s;
            background: transparent;
            border: none;
            cursor: pointer;
        }
        .btn-user:hover {
            background: #f1f5f9;
            color: #3b82f6;
        }
        .btn-user i {
            font-size: 1.1rem;
            color: #3b82f6;
        }
        .btn-register {
            background: #3b82f6;
            color: white;
        }
        .btn-register:hover {
            background: #2563eb;
            color: white;
        }
        .btn-register i {
            color: white;
        }
        .user-menu {
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }
        .btn-logout-header {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.4rem 0.6rem;
            color: #94a3b8;
            text-decoration: none;
            border-radius: 30px;
            transition: all 0.2s;
            font-size: 0.9rem;
        }
        .btn-logout-header:hover {
            color: #ef4444;
            background: #fef2f2;
        }
        
        @media (max-width: 768px) {
            .btn-mobile {
                display: block;
            }
            .nav-links {
                display: none;
                flex-direction: column;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: white;
                padding: 2rem;
                box-shadow: 0 10px 30px rgba(0,0,0,0.1);
                z-index: 999;
                gap: 1rem;
            }
            .nav-links.active {
                display: flex;
            }
            .nav-links a {
                padding: 0.5rem 0;
                border-bottom: 1px solid #f1f5f9;
            }
            .btn-user span {
                display: none;
            }
            .btn-user {
                padding: 0.4rem 0.6rem;
            }
            .btn-user i {
                font-size: 1.3rem;
            }
        }
        
        @media (max-width: 480px) {
            .nav-actions {
                gap: 0.3rem;
            }
            .btn-user {
                padding: 0.3rem 0.5rem;
            }
            .btn-user i {
                font-size: 1.1rem;
            }
        }
        
        /* =====================================================
           BUSCADOR Y FILTROS
           ===================================================== */
        .search-section {
            padding: 2rem 0 0;
            background: #f8fafc;
        }
        
        .search-container {
            background: white;
            padding: 1.5rem;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        
        .search-form {
            width: 100%;
        }
        
        .search-wrapper {
            display: flex;
            align-items: center;
            background: #f1f5f9;
            border-radius: 50px;
            padding: 0 1rem;
            transition: all 0.3s;
            border: 2px solid transparent;
        }
        
        .search-wrapper:focus-within {
            border-color: #3b82f6;
            background: white;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
        }
        
        .search-wrapper i {
            color: #94a3b8;
            font-size: 1.1rem;
        }
        
        .search-wrapper input {
            flex: 1;
            border: none;
            padding: 0.8rem 1rem;
            font-size: 1rem;
            background: transparent;
            outline: none;
            color: #0f172a;
        }
        
        .search-wrapper input::placeholder {
            color: #94a3b8;
        }
        
        .btn-search {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            border: none;
            padding: 0.6rem 1.2rem;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-search:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(59,130,246,0.3);
        }
        
        .filter-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 1rem;
            align-items: center;
        }
        
        .filter-btn {
            padding: 0.4rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 30px;
            background: white;
            color: #475569;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }
        
        .filter-btn:hover {
            border-color: #3b82f6;
            color: #3b82f6;
        }
        
        .filter-btn.active {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }
        
        .filter-dropdown {
            position: relative;
            display: inline-block;
        }
        
        .filter-dropdown .dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background: white;
            min-width: 200px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 0.5rem;
            z-index: 100;
            margin-top: 0.3rem;
            border: 1px solid #e2e8f0;
        }
        
        .filter-dropdown:hover .dropdown-menu {
            display: block;
        }
        
        .filter-dropdown .dropdown-menu a {
            display: block;
            padding: 0.5rem 1rem;
            color: #475569;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.2s;
            font-size: 0.85rem;
        }
        
        .filter-dropdown .dropdown-menu a:hover {
            background: #f1f5f9;
            color: #3b82f6;
        }
        
        .resultados-contador {
            margin-top: 0.5rem;
            font-size: 0.9rem;
            color: #94a3b8;
            text-align: center;
        }
        
        .badge-promo {
            display: inline-block;
            background: #ef4444;
            color: white;
            padding: 0.1rem 0.6rem;
            border-radius: 20px;
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 0.3rem;
        }
        
        @media (max-width: 768px) {
            .filter-tabs {
                gap: 0.3rem;
            }
            .filter-btn {
                font-size: 0.75rem;
                padding: 0.3rem 0.7rem;
            }
            .filter-dropdown .dropdown-menu {
                left: auto;
                right: 0;
            }
        }
    </style>
</head>
<body>

    <!-- ===================================================== -->
    <!-- HEADER -->
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
    <!-- HERO -->
    <!-- ===================================================== -->
    <section class="hero" id="inicio">
        <div class="container">
            <div class="hero-content">
                <span class="hero-badge"><i class="fas fa-rocket"></i> Lanzamiento 2026</span>
                <h1>La tienda online más<br /><span class="gradient-text">rápida del mercado</span></h1>
                <p class="hero-description">Ecommerce con arquitectura headless. Carga en <strong>0.3 segundos</strong>.</p>
                <div class="hero-buttons">
                    <a href="#productos" class="btn-primary"><i class="fas fa-shopping-cart"></i> Ver productos</a>
                    <a href="#contacto" class="btn-secondary"><i class="fas fa-headset"></i> Contactar</a>
                </div>
                <div class="hero-stats">
                    <div class="stat"><span class="stat-number">0.3s</span><span class="stat-label">Tiempo de carga</span></div>
                    <div class="stat"><span class="stat-number">100%</span><span class="stat-label">Seguro y cifrado</span></div>
                    <div class="stat"><span class="stat-number">24/7</span><span class="stat-label">Soporte técnico</span></div>
                </div>
            </div>
            <div class="hero-image">
                <img src="https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=600" loading="lazy" />
                <div class="floating-card card-1"><i class="fas fa-shopping-bag"></i><span>+150 ventas</span></div>
                <div class="floating-card card-2"><i class="fas fa-star"></i><span>4.9 ★</span></div>
            </div>
        </div>
        <div class="hero-wave">
            <svg viewBox="0 0 1440 120"><path d="M0,60 C360,120 720,0 1080,60 C1260,90 1380,70 1440,50 L1440,120 L0,120Z" fill="#f8fafc" /></svg>
        </div>
    </section>

    <!-- ===================================================== -->
    <!-- BUSCADOR Y FILTROS -->
    <!-- ===================================================== -->
    <section class="search-section">
        <div class="container">
            <div class="search-container">
                <form id="searchForm" class="search-form" onsubmit="event.preventDefault(); buscarProductos({ buscar: document.getElementById('searchInput').value });">
                    <div class="search-wrapper">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Buscar productos..." />
                        <button type="submit" class="btn-search">
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </form>
                
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
                            <i class="fas fa-chevron-down"></i>
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
    </section>

    <!-- ===================================================== -->
    <!-- SECCIÓN: DESTACADOS -->
    <!-- ===================================================== -->
    <section class="products featured-products" id="productos-destacados">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">⭐ Destacados</span>
                <h2>Lo más <span class="gradient-text">destacado</span></h2>
                <p>Los productos que recomendamos especialmente para ti</p>
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
                <p>Lo que más está comprando nuestra comunidad</p>
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
                <p>Lo más reciente que hemos añadido al catálogo</p>
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
                <span class="section-tag">📦 Todos</span>
                <h2>Todos los <span class="gradient-text">productos</span></h2>
                <p>Explora nuestro catálogo completo</p>
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
                    <a href="#inicio">Inicio</a>
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
    // =====================================================
    // CERRAR MENÚ MÓVIL
    // =====================================================
    function cerrarMenuMovil() {
        var navLinks = document.getElementById('navLinks');
        var menuToggle = document.getElementById('menuToggle');
        if (navLinks) navLinks.classList.remove('active');
        if (menuToggle) menuToggle.innerHTML = '<i class="fas fa-bars"></i>';
    }

    // =====================================================
    // INICIALIZACIÓN
    // =====================================================
    (function() {
        console.log('🚀 Iniciando tienda...');
        
        // Cargar carrito
        if (typeof carrito !== 'undefined') {
            carrito.cargar();
        }
        
        // Cargar productos
        if (typeof loadFeaturedProducts === 'function') {
            loadFeaturedProducts();
        }
        if (typeof loadBestsellerProducts === 'function') {
            loadBestsellerProducts();
        }
        if (typeof loadNewProducts === 'function') {
            loadNewProducts();
        }
        if (typeof loadAllProducts === 'function') {
            loadAllProducts();
        }
        
        // Menú móvil
        var menuToggle = document.getElementById('menuToggle');
        var navLinks = document.getElementById('navLinks');
        
        if (menuToggle && navLinks) {
            menuToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                navLinks.classList.toggle('active');
                if (navLinks.classList.contains('active')) {
                    this.innerHTML = '<i class="fas fa-times"></i>';
                } else {
                    this.innerHTML = '<i class="fas fa-bars"></i>';
                }
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
        
        // Newsletter
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
        
        // Scroll suave
        document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                var target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
        
        console.log('✅ Tienda inicializada');
    })();
    </script>
</body>
</html>