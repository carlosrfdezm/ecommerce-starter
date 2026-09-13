// =====================================================
// MAIN.JS - VERSIÓN COMPLETA CON MODAL DE PRODUCTO
// =====================================================

console.log('📦 Cargando main.js...');

// =====================================================
// 1. CONFIGURACIÓN
// =====================================================
const BASE_PATH = window.location.pathname.replace(/\/[^\/]*$/, '');
const API_URL = window.location.origin + BASE_PATH + '/api/productos.php';
const CATEGORIAS_URL = window.location.origin + BASE_PATH + '/api/categorias.php';

// Placeholder local
const PLACEHOLDER_IMG = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgZmlsbD0iI2YxZjVmOSIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTIiIGZpbGw9IiM5NGEzYTgiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5TaW4gaW1hZ2VuPC90ZXh0Pjwvc3ZnPg==';

console.log('🌐 API_URL:', API_URL);

// =====================================================
// 2. PRODUCTOS DE RESPALDO
// =====================================================
const backupProducts = [
    { id: 1, nombre: 'Auriculares Pro X', precio: 89.00, descripcion: 'Cancelación de ruido activa, 30h de batería.', imagen_url: 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400', categoria_nombre: 'Electrónica', stock: 50, total_vendido: 45 },
    { id: 2, nombre: 'Teclado Mecánico RGB', precio: 129.00, descripcion: 'Switches rojos, retroiluminación RGB personalizable.', imagen_url: 'https://images.unsplash.com/photo-1511467687858-23d96c32e4ae?w=400', categoria_nombre: 'Electrónica', stock: 30, total_vendido: 30 },
    { id: 3, nombre: 'Mochila Ejecutiva', precio: 59.00, descripcion: 'Impermeable, compartimento para portátil 15".', imagen_url: 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=400', categoria_nombre: 'Moda', stock: 100, total_vendido: 15 },
    { id: 4, nombre: 'Lámpara LED Inteligente', precio: 45.00, descripcion: 'Control por voz, 16M de colores.', imagen_url: 'https://images.unsplash.com/photo-1513506003901-1e6a229e2d15?w=400', categoria_nombre: 'Hogar', stock: 75, total_vendido: 60 },
    { id: 5, nombre: 'Zapatillas Running Air', precio: 110.00, descripcion: 'Suela de aire, tejido transpirable.', imagen_url: 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400', categoria_nombre: 'Deportes', stock: 40, total_vendido: 25 },
    { id: 6, nombre: 'Reloj Inteligente Pro', precio: 199.00, descripcion: 'Monitor de ritmo cardíaco, GPS, 7 días de batería.', imagen_url: 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=400', categoria_nombre: 'Electrónica', stock: 25, total_vendido: 10 }
];

// =====================================================
// 3. CREAR TARJETA DE PRODUCTO
// =====================================================
function crearCardProducto(product) {
    const card = document.createElement('div');
    card.className = 'product-card';
    
    const id = product.id;
    const nombre = product.nombre || 'Producto';
    const precio = parseFloat(product.precio || 0).toFixed(2);
    const imagen = product.imagen_url || product.imagen || PLACEHOLDER_IMG;
    const categoria = product.categoria_nombre || product.categoria || 'General';
    const descripcion = product.descripcion || '';
    const descripcionLarga = product.descripcion_larga || product.descripcion || 'Sin descripción disponible.';
    const garantiaEnvio = product.garantia_envio || 'Envío disponible a todo el país';
    const garantiaSeguridad = product.garantia_seguridad || 'Compra 100% segura con Stripe';
    const garantiaDevolucion = product.garantia_devolucion || '30 días de garantía de devolución';
    const tieneStock = (product.stock || 0) > 0;
    const stock = product.stock || 0;
    const totalVendido = product.total_vendido || 0;
    const enPromocion = product.en_promocion == 1;
    const precioPromocion = product.precio_promocion ? parseFloat(product.precio_promocion).toFixed(2) : null;
    const precioMostrar = enPromocion && precioPromocion ? precioPromocion : precio;
    const precioOriginal = enPromocion && precioPromocion ? precio : null;
    
    // Guardar producto globalmente para el modal
    if (!window.productosModal) window.productosModal = {};
    window.productosModal[id] = {
        id: id,
        nombre: nombre,
        precio: precio,
        precioMostrar: precioMostrar,
        precioOriginal: precioOriginal,
        imagen: imagen,
        categoria: categoria,
        descripcion: descripcion,
        descripcionLarga: descripcionLarga,
        garantiaEnvio: garantiaEnvio,
        garantiaSeguridad: garantiaSeguridad,
        garantiaDevolucion: garantiaDevolucion,
        stock: stock,
        totalVendido: totalVendido,
        enPromocion: enPromocion
    };
    
    card.innerHTML = `
        <img src="${imagen}" alt="${nombre}" loading="lazy" 
             onclick="abrirModalProducto(${id})"
             style="cursor: pointer;"
             onerror="this.src='${PLACEHOLDER_IMG}'" />
        <div class="info">
            ${enPromocion ? `<span class="badge-promo">🔥 OFERTA</span>` : ''}
            <span class="category-tag">${categoria}</span>
            <h3 onclick="abrirModalProducto(${id})" style="cursor:pointer;">${nombre}</h3>
            <div class="price">
                ${enPromocion && precioOriginal ? `
                    <span style="text-decoration:line-through;color:#94a3b8;font-size:0.9rem;">$${precioOriginal}</span>
                    <span style="color:#ef4444;">$${precioMostrar}</span>
                ` : `
                    <span>$${precioMostrar}</span>
                `}
            </div>
            <p class="description">${descripcion}</p>
            <div class="product-stock ${tieneStock ? 'in-stock' : 'out-of-stock'}">
                ${tieneStock ? `✅ ${stock} disponibles` : '❌ Sin stock'}
            </div>
            ${totalVendido > 0 ? `<div style="font-size:0.8rem;color:#64748b;margin-bottom:0.5rem;">⭐ ${totalVendido} vendidos</div>` : ''}
            <div style="display:flex;gap:0.5rem;">
                ${tieneStock ? `
                    <button class="btn-add-to-cart" onclick="agregarAlCarrito(${id})" style="flex:1;">
                        <i class="fas fa-plus-circle"></i> Agregar
                    </button>
                ` : `
                    <button class="btn-add-to-cart disabled" disabled style="flex:1;">
                        <i class="fas fa-ban"></i> Sin stock
                    </button>
                `}
                <button onclick="abrirModalProducto(${id})" 
                        style="padding:0.7rem 1rem;background:#f1f5f9;border:none;border-radius:30px;cursor:pointer;color:#3b82f6;font-weight:600;">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
        </div>
    `;
    
    return card;
}

// =====================================================
// 4. MODAL DE PRODUCTO
// =====================================================
function abrirModalProducto(productoId) {
    console.log('🔍 Abriendo modal del producto:', productoId);
    
    const producto = window.productosModal ? window.productosModal[productoId] : null;
    
    if (!producto) {
        console.error('❌ Producto no encontrado:', productoId);
        return;
    }
    
    // Crear modal si no existe
    let modal = document.getElementById('productModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'productModal';
        modal.className = 'product-modal';
        modal.innerHTML = `
            <div class="product-modal-content">
                <button class="product-modal-close" onclick="cerrarModalProducto()">
                    <i class="fas fa-times"></i>
                </button>
                <div class="product-modal-grid" id="productModalGrid"></div>
            </div>
        `;
        document.body.appendChild(modal);
        
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                cerrarModalProducto();
            }
        });
    }
    
    const grid = document.getElementById('productModalGrid');
    const tieneStock = producto.stock > 0;
    
    grid.innerHTML = `
        <div class="product-modal-image">
            <img src="${producto.imagen}" alt="${producto.nombre}" 
                 onerror="this.src='${PLACEHOLDER_IMG}'" />
        </div>
        <div class="product-modal-info">
            <span class="product-modal-category">${producto.categoria}</span>
            <h2 class="product-modal-title">${producto.nombre}</h2>
            
            <div class="product-modal-price">
                ${producto.enPromocion && producto.precioOriginal ? `
                    <span class="old-price">$${producto.precioOriginal}</span>
                    <span class="sale-price">$${producto.precioMostrar}</span>
                    <span style="font-size:0.85rem;background:#ef4444;color:white;padding:0.2rem 0.8rem;border-radius:20px;font-weight:700;">
                        🔥 OFERTA
                    </span>
                ` : `
                    <span>$${producto.precioMostrar}</span>
                `}
            </div>
            
            <p class="product-modal-description">${producto.descripcionLarga}</p>
            
            <div class="product-modal-stock ${tieneStock ? 'in-stock' : 'out-of-stock'}">
                <i class="fas ${tieneStock ? 'fa-check-circle' : 'fa-times-circle'}"></i>
                ${tieneStock ? `${producto.stock} unidades disponibles` : 'Sin stock'}
            </div>
            
            ${producto.totalVendido > 0 ? `
                <div class="product-modal-features">
                    <div class="product-modal-feature">
                        <i class="fas fa-star"></i>
                        <span>${producto.totalVendido} personas han comprado este producto</span>
                    </div>
                </div>
            ` : ''}
            
            <div class="product-modal-features">
                <div class="product-modal-feature">
                    <i class="fas fa-truck"></i>
                    <span>${producto.garantiaEnvio}</span>
                </div>
                <div class="product-modal-feature">
                    <i class="fas fa-shield-alt"></i>
                    <span>${producto.garantiaSeguridad}</span>
                </div>
                <div class="product-modal-feature">
                    <i class="fas fa-undo"></i>
                    <span>${producto.garantiaDevolucion}</span>
                </div>
            </div>
            
            <div class="product-modal-actions">
                ${tieneStock ? `
                    <button class="btn-add-to-cart" onclick="agregarAlCarrito(${producto.id}); cerrarModalProducto();">
                        <i class="fas fa-plus-circle"></i> Agregar al carrito
                    </button>
                    <button class="btn-buy-now" onclick="comprarAhora(${producto.id})">
                        <i class="fas fa-bolt"></i> Comprar ahora
                    </button>
                ` : `
                    <button class="btn-add-to-cart disabled" disabled style="width:100%;">
                        <i class="fas fa-ban"></i> Producto sin stock
                    </button>
                `}
            </div>
        </div>
    `;
    
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function cerrarModalProducto() {
    const modal = document.getElementById('productModal');
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

function comprarAhora(productoId) {
    agregarAlCarrito(productoId);
    cerrarModalProducto();
    setTimeout(function() {
        window.location.href = 'checkout.php';
    }, 500);
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        cerrarModalProducto();
    }
});

// =====================================================
// 5. RENDERIZAR CARRUSEL
// =====================================================
function renderCarousel(trackId, products) {
    const track = document.getElementById(trackId);
    if (!track) return;
    
    track.innerHTML = '';
    
    if (!products || products.length === 0) {
        track.innerHTML = `<div style="flex:0 0 100%;text-align:center;padding:2rem;color:#94a3b8;">No hay productos</div>`;
        return;
    }
    
    products.forEach(product => {
        const card = crearCardProducto(product);
        track.appendChild(card);
    });
    
    setTimeout(function() {
        if (typeof iniciarAutoplay === 'function') {
            iniciarAutoplay(trackId);
        }
    }, 500);
}

// =====================================================
// 6. RENDERIZAR GRID
// =====================================================
function renderGrid(products) {
    const grid = document.getElementById('productGrid');
    if (!grid) return;
    
    grid.innerHTML = '';
    
    if (!products || products.length === 0) {
        grid.innerHTML = `<div style="grid-column:1/-1;text-align:center;padding:2rem;color:#94a3b8;">No hay productos disponibles</div>`;
        return;
    }
    
    products.forEach(product => {
        const card = crearCardProducto(product);
        grid.appendChild(card);
    });
}

// =====================================================
// 7. CARGAR PRODUCTOS
// =====================================================
async function cargarProductos(url, callback, nombre) {
    try {
        const response = await fetch(url);
        if (!response.ok) throw new Error('HTTP error ' + response.status);
        const data = await response.json();
        
        let productos = [];
        if (data.success && Array.isArray(data.productos)) {
            productos = data.productos;
        } else if (Array.isArray(data)) {
            productos = data;
        } else {
            productos = backupProducts;
        }
        
        if (productos.length === 0) productos = backupProducts;
        
        window.productos = productos;
        callback(productos);
        
    } catch (error) {
        console.error(`❌ Error cargando ${nombre}:`, error);
        callback(backupProducts);
    }
}

// =====================================================
// 8. FUNCIONES DE CARGA
// =====================================================
async function loadFeaturedProducts() {
    await cargarProductos(API_URL + '?destacados=true', function(products) {
        renderCarousel('featuredTrack', products);
    }, 'destacados');
}

async function loadBestsellerProducts() {
    await cargarProductos(API_URL + '?orden=mas_vendidos&limite=10', function(products) {
        renderCarousel('bestsellerTrack', products);
    }, 'más vendidos');
}

async function loadNewProducts() {
    await cargarProductos(API_URL + '?orden=ultimos&limite=10', function(products) {
        renderCarousel('newTrack', products);
    }, 'nuevos');
}

async function loadAllProducts() {
    await cargarProductos(API_URL, function(products) {
        renderGrid(products);
    }, 'todos');
}

// =====================================================
// 9. CATEGORÍAS
// =====================================================
async function loadCategories() {
    try {
        const response = await fetch(CATEGORIAS_URL);
        if (!response.ok) throw new Error('HTTP error ' + response.status);
        const data = await response.json();
        
        // ✅ Manejar ambos formatos
        let categories = [];
        if (data.success && Array.isArray(data.categorias)) {
            categories = data.categorias;
        } else if (Array.isArray(data)) {
            categories = data;
        } else {
            console.warn('⚠️ Formato de categorías inesperado:', data);
            categories = [];
        }
        
        renderCategories(categories);
    } catch (error) {
        console.error('❌ Error cargando categorías:', error);
    }
}

function renderCategories(categories) {
    const grid = document.getElementById('categoriesGrid');
    if (!grid) return;
    grid.innerHTML = '';
    if (!categories || categories.length === 0) return;
    
    categories.forEach(cat => {
        const card = document.createElement('div');
        card.className = 'category-card';
        card.style.cursor = 'pointer';
        card.innerHTML = `
            <div class="category-icon"><i class="fas ${cat.icono || 'fa-tag'}"></i></div>
            <h3>${cat.nombre}</h3>
            <p>${cat.total_productos || 0} productos</p>
        `;
        card.addEventListener('click', function() {
            filtrarPorCategoria(cat.id, cat.nombre);
            document.querySelectorAll('.category-card').forEach(c => c.classList.remove('active'));
            this.classList.add('active');
        });
        grid.appendChild(card);
    });
}

// =====================================================
// 10. FILTRAR POR CATEGORÍA
// =====================================================
async function filtrarPorCategoria(categoriaId, categoriaNombre) {
    const grid = document.getElementById('productGrid');
    if (!grid) return;
    
    grid.innerHTML = `<div style="grid-column:1/-1;text-align:center;padding:2rem;color:#94a3b8;">Cargando...</div>`;
    
    try {
        const response = await fetch(API_URL + '?categoria=' + categoriaId);
        const data = await response.json();
        let products = data.success && data.productos ? data.productos : (Array.isArray(data) ? data : []);
        window.productos = products;
        renderGrid(products);
        const section = document.getElementById('productos');
        if (section) section.scrollIntoView({ behavior: 'smooth' });
    } catch (error) {
        console.error('❌ Error:', error);
    }
}

function cargarTodosLosProductos() {
    loadAllProducts();
}

// =====================================================
// 11. AGREGAR AL CARRITO
// =====================================================
function agregarAlCarrito(productoId) {
    if (typeof carrito === 'undefined') {
        alert('Error: El carrito no está disponible');
        return;
    }
    const producto = window.productos?.find(p => p.id === productoId);
    if (!producto) {
        alert('❌ Producto no encontrado');
        return;
    }
    if (producto.stock <= 0) {
        alert('❌ Producto sin stock');
        return;
    }
    carrito.agregar(producto);
}

// =====================================================
// 12. EXPONER FUNCIONES
// =====================================================
window.agregarAlCarrito = agregarAlCarrito;
window.abrirModalProducto = abrirModalProducto;
window.cerrarModalProducto = cerrarModalProducto;
window.comprarAhora = comprarAhora;
window.loadFeaturedProducts = loadFeaturedProducts;
window.loadBestsellerProducts = loadBestsellerProducts;
window.loadNewProducts = loadNewProducts;
window.loadAllProducts = loadAllProducts;
window.loadCategories = loadCategories;
window.cargarTodosLosProductos = cargarTodosLosProductos;
window.filtrarPorCategoria = filtrarPorCategoria;

// =====================================================
// 13. INICIALIZAR
// =====================================================
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Inicializando tienda...');
    
    setTimeout(function() {
        loadFeaturedProducts();
        loadBestsellerProducts();
        loadNewProducts();
        loadAllProducts();
        loadCategories();
        
        if (typeof carrito !== 'undefined') {
            carrito.cargar();
        }
        
        console.log('✅ Tienda inicializada');
    }, 500);
});


// =====================================================
// 14. SISTEMA DE CAROUSEL (AÑADIDO - NO TOCA NADA DE ARRIBA)
// =====================================================
var _carouselState = {};

function _getItemsPerView() {
    if (window.innerWidth < 640) return 1;
    if (window.innerWidth < 1024) return 2;
    return 3;
}

function moverCarousel(trackId, direccion) {
    var track = document.getElementById(trackId);
    if (!track) return;
    
    var cards = track.querySelectorAll('.product-card');
    if (cards.length === 0) return;
    
    if (!_carouselState[trackId]) _carouselState[trackId] = { index: 0 };
    var state = _carouselState[trackId];
    var porVista = _getItemsPerView();
    var maxIndex = Math.max(0, cards.length - porVista);
    
    state.index += direccion;
    if (state.index < 0) state.index = maxIndex;
    if (state.index > maxIndex) state.index = 0;
    
    _aplicarTransform(trackId);
    _actualizarDots(trackId);
}

function _aplicarTransform(trackId) {
    var track = document.getElementById(trackId);
    if (!track) return;
    
    var cards = track.querySelectorAll('.product-card');
    if (cards.length === 0) return;
    
    var state = _carouselState[trackId] || { index: 0 };
    var cardWidth = cards[0].getBoundingClientRect().width;
    var gap = parseFloat(getComputedStyle(track).gap) || 20;
    var offset = state.index * (cardWidth + gap);
    
    track.style.transform = 'translateX(-' + offset + 'px)';
    track.style.transition = 'transform 0.4s ease';
}

function _actualizarDots(trackId) {
    var dotsId = trackId.replace('Track', 'Dots');
    var dotsContainer = document.getElementById(dotsId);
    var track = document.getElementById(trackId);
    if (!dotsContainer || !track) return;
    
    var cards = track.querySelectorAll('.product-card');
    var porVista = _getItemsPerView();
    var totalDots = Math.max(1, cards.length - porVista + 1);
    var state = _carouselState[trackId] || { index: 0 };
    
    dotsContainer.innerHTML = '';
    for (var i = 0; i < totalDots; i++) {
        var dot = document.createElement('button');
        dot.className = 'carousel-dot' + (i === state.index ? ' active' : '');
        dot.setAttribute('aria-label', 'Ir al slide ' + (i + 1));
        (function(idx) {
            dot.addEventListener('click', function() {
                if (!_carouselState[trackId]) _carouselState[trackId] = { index: 0 };
                _carouselState[trackId].index = idx;
                _aplicarTransform(trackId);
                _actualizarDots(trackId);
            });
        })(i);
        dotsContainer.appendChild(dot);
    }
}

var _autoplayTimers = {};

function iniciarAutoplay(trackId, intervalo) {
    intervalo = intervalo || 5000;
    detenerAutoplay(trackId);
    
    _autoplayTimers[trackId] = setInterval(function() {
        moverCarousel(trackId, 1);
    }, intervalo);
    
    var track = document.getElementById(trackId);
    if (track) {
        var wrapper = track.closest('.carousel-wrapper');
        if (wrapper && !wrapper.dataset.autoplayBound) {
            wrapper.dataset.autoplayBound = '1';
            wrapper.addEventListener('mouseenter', function() { detenerAutoplay(trackId); });
            wrapper.addEventListener('mouseleave', function() { iniciarAutoplay(trackId, intervalo); });
        }
    }
}

function detenerAutoplay(trackId) {
    if (_autoplayTimers[trackId]) {
        clearInterval(_autoplayTimers[trackId]);
        delete _autoplayTimers[trackId];
    }
}

var _resizeTimeoutCarousel;
window.addEventListener('resize', function() {
    clearTimeout(_resizeTimeoutCarousel);
    _resizeTimeoutCarousel = setTimeout(function() {
        Object.keys(_carouselState).forEach(function(trackId) {
            _carouselState[trackId].index = 0;
            _aplicarTransform(trackId);
            _actualizarDots(trackId);
        });
    }, 200);
});

// Exponer globalmente para los onclick del HTML
window.moverCarousel = moverCarousel;
window.iniciarAutoplay = iniciarAutoplay;
window.detenerAutoplay = detenerAutoplay;

// =====================================================
// 15. SWIPE TÁCTIL
// =====================================================
(function() {
    var SWIPE_THRESHOLD = 50;
    
    function iniciarSwipe(trackId) {
        var track = document.getElementById(trackId);
        if (!track) return;
        
        track.style.touchAction = 'pan-y';
        
        var touchStartX = 0, touchStartY = 0, touchEndX = 0, touchEndY = 0;
        var isSwiping = false;
        
        track.addEventListener('touchstart', function(e) {
            var touch = e.changedTouches[0];
            touchStartX = touch.screenX;
            touchStartY = touch.screenY;
            touchEndX = touchStartX;
            touchEndY = touchStartY;
            isSwiping = true;
            detenerAutoplay(trackId);
        }, { passive: true });
        
        track.addEventListener('touchmove', function(e) {
            if (!isSwiping) return;
            var touch = e.changedTouches[0];
            touchEndX = touch.screenX;
            touchEndY = touch.screenY;
        }, { passive: true });
        
        track.addEventListener('touchend', function() {
            if (!isSwiping) return;
            isSwiping = false;
            
            var deltaX = touchEndX - touchStartX;
            var deltaY = touchEndY - touchStartY;
            
            if (Math.abs(deltaY) > Math.abs(deltaX) || Math.abs(deltaX) < SWIPE_THRESHOLD) {
                iniciarAutoplay(trackId);
                return;
            }
            
            if (deltaX < 0) moverCarousel(trackId, 1);
            else moverCarousel(trackId, -1);
            
            setTimeout(function() { iniciarAutoplay(trackId); }, 300);
        }, { passive: true });
        
        track.addEventListener('touchcancel', function() {
            isSwiping = false;
            iniciarAutoplay(trackId);
        }, { passive: true });
    }
    
    function activarSwipes() {
        ['featuredTrack', 'bestsellerTrack', 'newTrack'].forEach(function(trackId) {
            if (document.getElementById(trackId)) iniciarSwipe(trackId);
        });
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(activarSwipes, 1500);
        });
    } else {
        setTimeout(activarSwipes, 1500);
    }
})();