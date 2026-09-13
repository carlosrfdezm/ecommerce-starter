// =====================================================
// MENÚ LATERAL DE CATEGORÍAS
// =====================================================

console.log('📂 Cargando menú de categorías...');

(function() {
    // =====================================================
    // 1. ESTILOS
    // =====================================================
    var style = document.createElement('style');
    style.textContent = `
        /* Overlay */
        #catMenuOverlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.4);
            z-index: 99998;
            display: none;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        #catMenuOverlay.active {
            display: block;
            opacity: 1;
        }
        
        /* Panel lateral */
        #catMenuPanel {
            position: fixed;
            top: 0;
            left: 0;
            width: 320px;
            max-width: 85%;
            height: 100%;
            height: 100dvh;
            background: white;
            z-index: 99999;
            transform: translateX(-100%);
            transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            box-shadow: 10px 0 40px rgba(0,0,0,0.15);
            box-sizing: border-box;
            overflow: hidden;
        }
        #catMenuPanel.active {
            transform: translateX(0);
        }
        
        /* Header */
        .cat-menu-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.2rem;
            border-bottom: 1px solid #e2e8f0;
            flex-shrink: 0;
            background: white;
        }
        .cat-menu-header h3 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .cat-menu-close {
            background: #f1f5f9;
            border: none;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            cursor: pointer;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }
        
        /* Body */
        .cat-menu-body {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 0.5rem 0;
            -webkit-overflow-scrolling: touch;
        }
        
        /* Item de categoría */
        .cat-menu-item {
            border-bottom: 1px solid #f1f5f9;
        }
        .cat-menu-item:last-child {
            border-bottom: none;
        }
        
        /* Header del item (padre) */
        .cat-menu-parent {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.2rem;
            cursor: pointer;
            transition: background 0.2s;
            user-select: none;
        }
        .cat-menu-parent:hover {
            background: #f8fafc;
        }
        .cat-menu-parent-left {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            flex: 1;
            min-width: 0;
        }
        .cat-menu-parent-left i.cat-icon {
            width: 24px;
            text-align: center;
            color: #3b82f6;
            font-size: 1.1rem;
            flex-shrink: 0;
        }
        .cat-menu-parent-left .cat-name {
            font-weight: 600;
            font-size: 0.95rem;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .cat-menu-parent-left .cat-count {
            font-size: 0.75rem;
            color: #94a3b8;
            font-weight: 400;
            margin-left: 0.3rem;
        }
        .cat-menu-arrow {
            color: #94a3b8;
            font-size: 0.8rem;
            transition: transform 0.3s;
            flex-shrink: 0;
        }
        .cat-menu-arrow.rotated {
            transform: rotate(90deg);
        }
        
        /* Subcategorías */
        .cat-menu-children {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.35s ease;
            background: #f8fafc;
        }
        .cat-menu-children.open {
            max-height: 500px;
        }
        .cat-menu-child {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            padding: 0.7rem 1.2rem 0.7rem 2.8rem;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.9rem;
            color: #475569;
            position: relative;
        }
        .cat-menu-child::before {
            content: '';
            position: absolute;
            left: 1.5rem;
            top: 50%;
            width: 12px;
            height: 1px;
            background: #cbd5e1;
        }
        .cat-menu-child:hover {
            background: #e2e8f0;
            color: #3b82f6;
        }
        .cat-menu-child i {
            width: 18px;
            text-align: center;
            color: #64748b;
            font-size: 0.85rem;
        }
        .cat-menu-child .cat-count {
            margin-left: auto;
            font-size: 0.75rem;
            color: #94a3b8;
        }
        
        /* Footer del menú */
        .cat-menu-footer {
            padding: 1rem 1.2rem;
            border-top: 1px solid #e2e8f0;
            flex-shrink: 0;
            background: white;
        }
        .cat-menu-footer a {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.7rem 0;
            color: #3b82f6;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: color 0.2s;
        }
        .cat-menu-footer a:hover {
            color: #2563eb;
        }
        
        /* Botón en el header */
        .btn-categories {
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
        .btn-categories:hover {
            background: #f1f5f9;
            color: #3b82f6;
        }
        .btn-categories i {
            font-size: 1rem;
            color: #3b82f6;
        }
    `;
    document.head.appendChild(style);

    // =====================================================
    // 2. CREAR ELEMENTOS
    // =====================================================
    var overlay = document.createElement('div');
    overlay.id = 'catMenuOverlay';
    document.body.appendChild(overlay);

    var panel = document.createElement('div');
    panel.id = 'catMenuPanel';
    panel.innerHTML = `
        <div class="cat-menu-header">
            <h3>📂 Categorías</h3>
            <button class="cat-menu-close" onclick="cerrarMenuCategorias()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="cat-menu-body" id="catMenuBody">
            <div style="text-align:center;padding:2rem;color:#94a3b8;">
                Cargando categorías...
            </div>
        </div>
        <div class="cat-menu-footer">
            <a href="#" onclick="cerrarMenuCategorias(); mostrarTodosLosProductos(); return false;">
                <i class="fas fa-th-large"></i> Ver todos los productos
            </a>
            <a href="#" onclick="cerrarMenuCategorias(); filtrarPromocionesMenu(); return false;" style="color:#ef4444;">
                <i class="fas fa-fire"></i> Productos en oferta
            </a>
            <a href="#" onclick="cerrarMenuCategorias(); filtrarDestacadosMenu(); return false;" style="color:#f59e0b;">
                <i class="fas fa-star"></i> Productos destacados
            </a>
        </div>
    `;
    document.body.appendChild(panel);

    // =====================================================
    // 3. FUNCIONES ABRIR/CERRAR
    // =====================================================
    window.abrirMenuCategorias = function() {
        panel.classList.add('active');
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
        cargarCategoriasMenu();
    };

    window.cerrarMenuCategorias = function() {
        panel.classList.remove('active');
        overlay.classList.remove('active');
        document.body.style.overflow = '';
    };

    // =====================================================
    // 4. CARGAR CATEGORÍAS
    // =====================================================
    async function cargarCategoriasMenu() {
        var body = document.getElementById('catMenuBody');
        
        try {
            var response = await fetch(window.location.origin + window.location.pathname.replace(/\/[^\/]*$/, '') + '/api/categorias.php');
            var data = await response.json();
            
            var categorias = [];
            if (data.success && Array.isArray(data.categorias)) {
                categorias = data.categorias;
            } else if (Array.isArray(data)) {
                categorias = data;
            }
            
            if (categorias.length === 0) {
                body.innerHTML = '<div style="text-align:center;padding:2rem;color:#94a3b8;">No hay categorías</div>';
                return;
            }
            
            var html = '';
            categorias.forEach(function(cat) {
                var tieneHijos = cat.subcategorias && cat.subcategorias.length > 0;
                
                html += '<div class="cat-menu-item">';
                html += '  <div class="cat-menu-parent" onclick="toggleCategoria(' + cat.id + ', ' + (tieneHijos ? 'true' : 'false') + ')">';
                html += '    <div class="cat-menu-parent-left">';
                html += '      <i class="fas ' + (cat.icono || 'fa-tag') + ' cat-icon"></i>';
                html += '      <span class="cat-name">' + cat.nombre + '</span>';
                html += '      <span class="cat-count">(' + (cat.total_productos || 0) + ')</span>';
                html += '    </div>';
                if (tieneHijos) {
                    html += '    <i class="fas fa-chevron-right cat-menu-arrow" id="arrow-' + cat.id + '"></i>';
                }
                html += '  </div>';
                
                if (tieneHijos) {
                    html += '  <div class="cat-menu-children" id="children-' + cat.id + '">';
                    cat.subcategorias.forEach(function(sub) {
                        html += '    <div class="cat-menu-child" onclick="event.stopPropagation(); filtrarPorCategoria(' + sub.id + ', \'' + sub.nombre.replace(/'/g, "\\'") + '\'); cerrarMenuCategorias();">';
                        html += '      <i class="fas ' + (sub.icono || 'fa-tag') + '"></i>';
                        html += '      <span>' + sub.nombre + '</span>';
                        html += '      <span class="cat-count">' + (sub.total_productos || 0) + '</span>';
                        html += '    </div>';
                    });
                    html += '  </div>';
                }
                
                html += '</div>';
            });
            
            body.innerHTML = html;
            
        } catch (error) {
            console.error('Error cargando categorías:', error);
            body.innerHTML = '<div style="text-align:center;padding:2rem;color:#ef4444;">Error al cargar categorías</div>';
        }
    }

    // =====================================================
    // 5. TOGGLE SUBCATEGORÍAS
    // =====================================================
    window.toggleCategoria = function(catId, tieneHijos) {
        if (!tieneHijos) {
            var catName = document.querySelector('#catMenuPanel .cat-menu-parent[onclick*="' + catId + '"] .cat-name');
            if (catName) {
                filtrarPorCategoria(catId, catName.textContent);
                cerrarMenuCategorias();
            }
            return;
        }
        
        var children = document.getElementById('children-' + catId);
        var arrow = document.getElementById('arrow-' + catId);
        
        if (children.classList.contains('open')) {
            children.classList.remove('open');
            arrow.classList.remove('rotated');
        } else {
            document.querySelectorAll('.cat-menu-children.open').forEach(function(el) {
                el.classList.remove('open');
            });
            document.querySelectorAll('.cat-menu-arrow.rotated').forEach(function(el) {
                el.classList.remove('rotated');
            });
            
            children.classList.add('open');
            arrow.classList.add('rotated');
        }
    };

    // =====================================================
    // 6. FUNCIONES ADICIONALES
    // =====================================================
    window.filtrarPromocionesMenu = function() {
        console.log('🔥 Filtrando promociones...');
        if (typeof loadPromociones === 'function') {
            loadPromociones();
        } else if (typeof cargarProductos === 'function') {
            cargarProductos(window.location.origin + '/api/productos.php?promocion=true', function(products) {
                if (typeof renderGrid === 'function') renderGrid(products);
                var section = document.getElementById('productos');
                if (section) section.scrollIntoView({ behavior: 'smooth' });
            }, 'promociones');
        }
    };

    window.filtrarDestacadosMenu = function() {
        console.log('⭐ Filtrando destacados...');
        if (typeof loadFeaturedProducts === 'function') {
            loadFeaturedProducts();
        } else if (typeof cargarProductos === 'function') {
            cargarProductos(window.location.origin + '/api/productos.php?destacados=true', function(products) {
                if (typeof renderGrid === 'function') renderGrid(products);
                var section = document.getElementById('productos');
                if (section) section.scrollIntoView({ behavior: 'smooth' });
            }, 'destacados');
        }
    };

    // =====================================================
    // 7. EVENTOS
    // =====================================================
    overlay.addEventListener('click', cerrarMenuCategorias);

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            cerrarMenuCategorias();
        }
    });

    console.log('✅ Menú de categorías cargado');
})();