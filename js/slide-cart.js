// =====================================================
// SLIDE CART - VERSIÓN COMPLETA CON LIBERACIÓN DE STOCK
// =====================================================

console.log('📦 Cargando slide-cart.js...');

(function() {
    if (typeof carrito === 'undefined') {
        console.warn('⚠️ carrito no disponible');
        return;
    }

    // =====================================================
    // 1. AGREGAR ESTILOS GLOBALES
    // =====================================================
    var style = document.createElement('style');
    style.textContent = `
        html, body {
            overflow-x: hidden !important;
            max-width: 100% !important;
            width: 100% !important;
        }
        
        #cartPanel {
            position: fixed !important;
            top: 0 !important;
            right: 0 !important;
            width: 90% !important;
            max-width: 420px !important;
            height: 100% !important;
            height: 100dvh !important;
            background: white !important;
            z-index: 99999 !important;
            transform: translateX(100%) !important;
            transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1) !important;
            display: flex !important;
            flex-direction: column !important;
            overflow: hidden !important;
            box-sizing: border-box !important;
            margin: 0 !important;
            padding: 0 !important;
            box-shadow: -10px 0 40px rgba(0,0,0,0.15) !important;
            border-radius: 16px 0 0 16px !important;
        }
        
        #cartPanel.active {
            transform: translateX(0) !important;
        }
        
        #cartOverlay {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            height: 100% !important;
            background: rgba(0,0,0,0.4) !important;
            z-index: 99998 !important;
            display: none;
            opacity: 0;
            transition: opacity 0.3s ease !important;
        }
        
        #cartOverlay.active {
            display: block !important;
            opacity: 1 !important;
        }
        
        #cartPanelBody {
            flex: 1 !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            padding: 1rem !important;
            box-sizing: border-box !important;
            width: 100% !important;
            -webkit-overflow-scrolling: touch !important;
        }
        
        @media (max-width: 400px) {
            #cartPanel { width: 85% !important; }
        }
        
        @media (min-width: 769px) {
            #cartPanel {
                width: 420px !important;
                border-radius: 0 !important;
            }
        }
        
        #cartPanel * {
            box-sizing: border-box !important;
            max-width: 100% !important;
        }
    `;
    document.head.appendChild(style);

    // =====================================================
    // 2. CREAR OVERLAY
    // =====================================================
    var overlay = document.createElement('div');
    overlay.id = 'cartOverlay';
    document.body.appendChild(overlay);

    // =====================================================
    // 3. CREAR PANEL
    // =====================================================
    var panel = document.createElement('div');
    panel.id = 'cartPanel';
    panel.innerHTML = `
        <div style="display:flex;justify-content:space-between;align-items:center;padding:1rem;border-bottom:1px solid #e2e8f0;flex-shrink:0;background:white;width:100%;box-sizing:border-box;">
            <h3 style="margin:0;font-size:1.1rem;font-weight:700;display:flex;align-items:center;gap:0.5rem;">
                🛒 Mi Carrito
            </h3>
            <button id="closeCartPanel" style="background:#f1f5f9;border:none;width:36px;height:36px;border-radius:50%;cursor:pointer;color:#64748b;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div id="cartPanelBody">
            <div style="text-align:center;padding:3rem 1rem;color:#94a3b8;">
                <i class="fas fa-shopping-bag" style="font-size:3rem;color:#cbd5e1;"></i>
                <p style="margin-top:0.5rem;font-size:1rem;">Tu carrito está vacío</p>
                <button onclick="cerrarPanelCarrito()" style="margin-top:1rem;padding:0.7rem 2rem;border-radius:30px;background:linear-gradient(135deg,#3b82f6,#2563eb);color:white;border:none;cursor:pointer;font-weight:600;font-size:0.95rem;">
                    Seguir comprando
                </button>
            </div>
        </div>

        <div id="cartPanelFooter" style="display:none;padding:1rem;border-top:1px solid #e2e8f0;flex-shrink:0;background:white;box-sizing:border-box;width:100%;">
            <div style="display:flex;justify-content:space-between;font-size:0.95rem;font-weight:700;margin-bottom:0.3rem;">
                <span>Subtotal:</span>
                <span id="cartPanelSubtotal">$0.00</span>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:0.85rem;color:#64748b;margin-bottom:0.8rem;">
                <span>Envío:</span>
                <span>$5.99</span>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:1.1rem;font-weight:800;margin-bottom:1rem;padding-top:0.5rem;border-top:2px solid #e2e8f0;">
                <span>Total:</span>
                <span id="cartPanelTotal">$0.00</span>
            </div>
            <div style="display:flex;gap:0.5rem;width:100%;">
                <button onclick="vaciarCarritoPanel()" style="flex:1;padding:0.8rem;background:#fef2f2;color:#ef4444;border:1px solid #fecaca;border-radius:10px;font-weight:600;cursor:pointer;font-size:0.85rem;">
                    <i class="fas fa-trash"></i> Vaciar
                </button>
                <a href="carrito.php" style="flex:2;display:inline-flex;align-items:center;justify-content:center;gap:0.5rem;padding:0.8rem;background:linear-gradient(135deg,#3b82f6,#2563eb);color:white;border:none;border-radius:10px;font-weight:600;cursor:pointer;text-decoration:none;font-size:0.9rem;">
                    <i class="fas fa-arrow-right"></i> Ver carrito
                </a>
            </div>
        </div>
    `;
    document.body.appendChild(panel);

    // =====================================================
    // 4. FUNCIONES ABRIR/CERRAR
    // =====================================================
    window.abrirPanelCarrito = function() {
        panel.classList.add('active');
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
        renderizarPanel();
    };

    window.cerrarPanelCarrito = function() {
        panel.classList.remove('active');
        overlay.classList.remove('active');
        document.body.style.overflow = '';
    };

    // =====================================================
    // 5. RENDERIZAR CONTENIDO
    // =====================================================
    function renderizarPanel() {
        var body = document.getElementById('cartPanelBody');
        var footer = document.getElementById('cartPanelFooter');
        var subtotalEl = document.getElementById('cartPanelSubtotal');
        var totalEl = document.getElementById('cartPanelTotal');

        if (!body) return;

        // ✅ Normalizar tipos
        for (var n = 0; n < carrito.items.length; n++) {
            carrito.items[n].id = parseInt(carrito.items[n].id, 10);
            carrito.items[n].cantidad = parseInt(carrito.items[n].cantidad, 10) || 0;
        }

        if (carrito.items.length === 0) {
            body.innerHTML = `
                <div style="text-align:center;padding:3rem 1rem;color:#94a3b8;">
                    <i class="fas fa-shopping-bag" style="font-size:3rem;color:#cbd5e1;"></i>
                    <p style="margin-top:0.5rem;font-size:1rem;">Tu carrito está vacío</p>
                    <button onclick="cerrarPanelCarrito()" style="margin-top:1rem;padding:0.7rem 2rem;border-radius:30px;background:linear-gradient(135deg,#3b82f6,#2563eb);color:white;border:none;cursor:pointer;font-weight:600;font-size:0.95rem;">
                        Seguir comprando
                    </button>
                </div>
            `;
            footer.style.display = 'none';
            return;
        }

        footer.style.display = 'block';

        var html = '';
        for (var i = 0; i < carrito.items.length; i++) {
            var item = carrito.items[i];
            var precio = parseFloat(item.precio) || 0;
            var cantidad = parseInt(item.cantidad) || 0;

            html += `
                <div style="display:flex;align-items:center;gap:0.6rem;padding:0.7rem 0;border-bottom:1px solid #f1f5f9;width:100%;box-sizing:border-box;">
                    <div style="width:50px;height:50px;flex-shrink:0;border-radius:8px;overflow:hidden;background:#f1f5f9;">
                        <img src="${item.imagen || 'https://via.placeholder.com/50x50?text=No+image'}" 
                             alt="${item.nombre || 'Producto'}" 
                             style="width:100%;height:100%;object-fit:cover;"
                             onerror="this.style.display='none'" />
                    </div>
                    <div style="flex:1;min-width:0;">
                        <h4 style="margin:0;font-size:0.85rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${item.nombre || 'Producto'}</h4>
                        <p style="margin:0.15rem 0 0;font-size:0.75rem;color:#64748b;">$${precio.toFixed(2)} × ${cantidad}</p>
                    </div>
                    <div style="display:flex;align-items:center;gap:0.2rem;flex-shrink:0;">
                        <button onclick="actualizarCantidadPanel(${item.id}, ${cantidad - 1})" 
                                style="background:#f1f5f9;border:none;width:26px;height:26px;border-radius:6px;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:0.65rem;">
                            <i class="fas fa-minus"></i>
                        </button>
                        <span style="min-width:18px;text-align:center;font-size:0.8rem;font-weight:600;">${cantidad}</span>
                        <button onclick="actualizarCantidadPanel(${item.id}, ${cantidad + 1})" 
                                style="background:#f1f5f9;border:none;width:26px;height:26px;border-radius:6px;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:0.65rem;">
                            <i class="fas fa-plus"></i>
                        </button>
                        <button onclick="eliminarProductoPanel(${item.id})" 
                                style="background:none;border:none;color:#ef4444;cursor:pointer;padding:0.2rem;font-size:0.75rem;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
        }

        body.innerHTML = html;

        var subtotal = carrito.getTotal();
        var envio = 5.99;
        var total = subtotal + envio;

        if (subtotalEl) subtotalEl.textContent = '$' + subtotal.toFixed(2);
        if (totalEl) totalEl.textContent = '$' + total.toFixed(2);
    }

    // Exponer para que carrito.js pueda llamarla
    window.renderizarPanel = renderizarPanel;

    // =====================================================
    // 6. FUNCIONES DEL PANEL (CON LIBERACIÓN DE STOCK)
    // =====================================================

    window.eliminarProductoPanel = function(productoId) {
        productoId = parseInt(productoId, 10);
        if (isNaN(productoId)) return;
        
        if (confirm('¿Eliminar este producto del carrito?')) {
            // ✅ carrito.eliminar() ya llama a liberar.php internamente
            carrito.eliminar(productoId);
            
            // Refrescar UI
            setTimeout(function() {
                renderizarPanel();
            }, 300);
        }
    };

    window.actualizarCantidadPanel = function(productoId, nuevaCantidad) {
        productoId = parseInt(productoId, 10);
        nuevaCantidad = parseInt(nuevaCantidad, 10);
        
        if (isNaN(productoId) || isNaN(nuevaCantidad)) {
            console.warn('actualizarCantidadPanel: parámetros inválidos', productoId, nuevaCantidad);
            return;
        }
        
        // ✅ carrito.actualizarCantidad() ya llama a reservar.php o liberar.php
        carrito.actualizarCantidad(productoId, nuevaCantidad);
        
        // Refrescar tras dar tiempo al fetch
        setTimeout(function() {
            renderizarPanel();
        }, 500);
    };

    window.vaciarCarritoPanel = function() {
        if (carrito.items.length === 0) return;
        if (!confirm('¿Seguro que quieres vaciar el carrito?')) return;
        
        // ✅ carrito.vaciar() debe llamar a liberar.php con todo:true
        carrito.vaciar();
        
        setTimeout(function() {
            renderizarPanel();
        }, 500);
    };

    // =====================================================
    // 7. EVENTOS
    // =====================================================
    document.getElementById('closeCartPanel').addEventListener('click', cerrarPanelCarrito);
    overlay.addEventListener('click', cerrarPanelCarrito);

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            cerrarPanelCarrito();
        }
    });

    var cartBtn = document.querySelector('.btn-cart');
    if (cartBtn) {
        cartBtn.addEventListener('click', function(e) {
            e.preventDefault();
            abrirPanelCarrito();
        });
    }

    console.log('✅ Panel lateral responsive cargado correctamente');
})();