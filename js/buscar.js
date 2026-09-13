// =====================================================
// BUSCADOR Y FILTROS - VERSIÓN CORREGIDA
// =====================================================

console.log('🔍 Cargando buscar.js...');

// =====================================================
// 1. FUNCIÓN PRINCIPAL DE BÚSQUEDA
// =====================================================
function buscarProductos(filtros) {
    filtros = filtros || {};
    console.log('🔍 Buscando con filtros:', filtros);
    
    var grid = document.getElementById('productGrid');
    if (!grid) {
        console.error('❌ No se encontró #productGrid');
        return;
    }
    
    // Loading
    grid.innerHTML = '<p style="text-align:center;padding:2rem;color:#94a3b8;">Buscando...</p>';
    
    // Construir URL
    var API = window.API_URL 
       || (window.location.origin + window.location.pathname.replace(/\/[^\/]*$/, '') + '/api/productos.php');
    var params = [];
    
    if (filtros.buscar) params.push('buscar=' + encodeURIComponent(filtros.buscar));
    if (filtros.categoria) params.push('categoria=' + filtros.categoria);
    if (filtros.orden) params.push('orden=' + filtros.orden);
    if (filtros.promocion) params.push('promocion=true');
    if (filtros.limite) params.push('limite=' + filtros.limite);
    
    var url = API + (params.length > 0 ? '?' + params.join('&') : '');
    console.log('📡 Fetch:', url);
    
    fetch(url)
        .then(function(response) {
            if (!response.ok) throw new Error('HTTP ' + response.status);
            return response.json();
        })
        .then(function(data) {
            console.log('📦 Datos recibidos:', data);
            
            var productos = [];
            if (data.success && Array.isArray(data.productos)) {
                productos = data.productos;
            } else if (Array.isArray(data)) {
                productos = data;
            }
            
            console.log('📦 Productos:', productos.length);
            
            // Guardar globalmente
            window.productos = productos;
            
            // Renderizar
            renderizarProductosBusqueda(productos);
            
            // Contador
            var contador = document.getElementById('resultadosContador');
            if (contador) {
                contador.textContent = productos.length + ' productos encontrados';
            }
            
            // Scroll
            var section = document.getElementById('productos');
            if (section) section.scrollIntoView({ behavior: 'smooth' });
        })
        .catch(function(error) {
            console.error('❌ Error en búsqueda:', error);
            grid.innerHTML = '<p style="grid-column:1/-1;text-align:center;padding:2rem;color:#ef4444;">Error al buscar</p>';
        });
}

// =====================================================
// 2. RENDERIZAR PRODUCTOS (USA crearCardProducto de main.js)
// =====================================================
function renderizarProductosBusqueda(productos) {
    var grid = document.getElementById('productGrid');
    if (!grid) return;
    
    grid.innerHTML = '';
    
    if (!productos || productos.length === 0) {
        grid.innerHTML = '<p style="grid-column:1/-1;text-align:center;padding:2rem;color:#94a3b8;">No se encontraron productos</p>';
        return;
    }
    
    // ✅ Usar crearCardProducto de main.js si existe
    if (typeof crearCardProducto === 'function') {
        productos.forEach(function(producto) {
            var card = crearCardProducto(producto);
            grid.appendChild(card);
        });
    } else {
        // Fallback: crear tarjeta básica
        console.warn('⚠️ crearCardProducto no disponible, usando fallback');
        productos.forEach(function(producto) {
            var card = document.createElement('div');
            card.className = 'product-card';
            card.innerHTML = `
                <img src="${producto.imagen_url || ''}" alt="${producto.nombre}" />
                <div class="info">
                    <h3>${producto.nombre}</h3>
                    <div class="price">$${parseFloat(producto.precio).toFixed(2)}</div>
                </div>
            `;
            grid.appendChild(card);
        });
    }
}

// =====================================================
// 3. FUNCIONES DE FILTRO
// =====================================================
function filtrarMasVendidos() {
    console.log('🔥 Filtrando más vendidos');
    buscarProductos({ orden: 'mas_vendidos' });
}

function filtrarUltimos() {
    console.log('🆕 Filtrando últimos');
    buscarProductos({ orden: 'ultimos' });
}

function filtrarPromociones() {
    console.log('🏷️ Filtrando promociones');
    buscarProductos({ promocion: true });
}

function filtrarPrecioAsc() {
    console.log('⬆️ Precio ascendente');
    buscarProductos({ orden: 'precio_asc' });
}

function filtrarPrecioDesc() {
    console.log('⬇️ Precio descendente');
    buscarProductos({ orden: 'precio_desc' });
}

function resetearFiltros() {
    console.log('🔄 Reseteando filtros');
    
    var input = document.getElementById('searchInput');
    if (input) input.value = '';
    
    var contador = document.getElementById('resultadosContador');
    if (contador) contador.textContent = '';
    
    // Recargar todos los productos
    if (typeof loadAllProducts === 'function') {
        loadAllProducts();
    } else {
        buscarProductos({});
    }
}

// =====================================================
// 4. EXPONER FUNCIONES GLOBALMENTE
// =====================================================
window.buscarProductos = buscarProductos;
window.renderizarProductosBusqueda = renderizarProductosBusqueda;
window.filtrarMasVendidos = filtrarMasVendidos;
window.filtrarUltimos = filtrarUltimos;
window.filtrarPromociones = filtrarPromociones;
window.filtrarPrecioAsc = filtrarPrecioAsc;
window.filtrarPrecioDesc = filtrarPrecioDesc;
window.resetearFiltros = resetearFiltros;

// =====================================================
// 5. INICIALIZAR BUSCADOR
// =====================================================
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ Inicializando buscador');
    
    var searchForm = document.getElementById('searchForm');
    var searchInput = document.getElementById('searchInput');
    
    // Submit del formulario
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            var termino = searchInput ? searchInput.value.trim() : '';
            console.log('🔍 Submit búsqueda:', termino);
            
            if (termino.length > 0) {
                buscarProductos({ buscar: termino });
            } else {
                resetearFiltros();
            }
        });
    }
    
    // Búsqueda en tiempo real
    if (searchInput) {
        var timeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(timeout);
            var termino = this.value.trim();
            
            timeout = setTimeout(function() {
                console.log('⌨️ Input:', termino);
                
                if (termino.length >= 2) {
                    buscarProductos({ buscar: termino });
                } else if (termino.length === 0) {
                    resetearFiltros();
                }
            }, 500);
        });
    }
    
    console.log('✅ Buscador inicializado');
});

console.log('✅ buscar.js cargado completamente');