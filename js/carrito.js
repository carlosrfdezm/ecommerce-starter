// =====================================================
// CARRITO - VERSIÓN CORREGIDA (IMÁGENES)
// =====================================================

console.log('🛒 Cargando carrito.js...');

var carrito = {
    items: [],

    // ✅ FIX: identifica la sesión del navegador (persistente)
    getSesionId: function() {
        var id = localStorage.getItem('carrito_sesion_id');
        if (!id) {
            id = 'ses_' + Math.random().toString(36).substring(2) + Date.now().toString(36);
            localStorage.setItem('carrito_sesion_id', id);
        }
        return id;
    },
    
    cargar: function() {
        var guardado = localStorage.getItem('carrito');
        if (guardado) {
            try {
                this.items = JSON.parse(guardado);
            } catch (e) {
                this.items = [];
            }
        }
        this.actualizarUI();
        return this.items;
    },
    
   guardar: function() {
    localStorage.setItem('carrito', JSON.stringify(this.items));
    this.actualizarUI();
    this.actualizarContador();
    
    // ✅ Si estamos en carrito.php, renderizar
    if (document.getElementById('cartItems')) {
        this.renderizar();
    }
},
    
    agregar: function(producto) {
        console.log('🛒 Agregando:', producto);
        
        if (!producto || !producto.id) {
            alert('❌ Producto inválido');
            return false;
        }
        
        // ✅ FIX: usar stock_disponible si existe, si no caer a stock
        var stockReal = (typeof producto.stock_disponible !== 'undefined') 
                    ? parseInt(producto.stock_disponible) 
                    : parseInt(producto.stock);
        
        if (stockReal <= 0) {
            alert('❌ Producto sin stock');
            return false;
        }
        
        // Cantidad actual en el carrito
        var cantidadEnCarrito = 0;
        var existente = null;
        for (var i = 0; i < this.items.length; i++) {
            if (this.items[i].id === producto.id) {
                existente = this.items[i];
                cantidadEnCarrito = existente.cantidad;
                break;
            }
        }
        
        if (cantidadEnCarrito + 1 > stockReal) {
            alert('❌ Solo hay ' + stockReal + ' unidades disponibles');
            return false;
        }
        
        // ✅ RESERVA: llamar a la API antes de modificar el carrito
        var self = this;
        var payload = {
            producto_id: producto.id,
            cantidad: 1,
            sesion_id: this.getSesionId()
        };
        
        // Deshabilitar botón temporalmente para evitar doble click
        var btn = event && event.target ? event.target.closest('button') : null;
        if (btn) btn.disabled = true;
        
        fetch('api/reservar.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (btn) btn.disabled = false;
            
            if (!data.success) {
                alert('❌ ' + (data.mensaje || 'No se pudo reservar el stock'));
                return;
            }
            
            // ✅ Reserva OK → actualizar el carrito local
            var imagenProducto = producto.imagen_url || producto.imagen || 'https://via.placeholder.com/100x100?text=Sin+imagen';
            var categoriaProducto = producto.categoria_nombre || producto.categoria || 'General';
            
            if (existente) {
                existente.cantidad += 1;
                if (!existente.imagen || existente.imagen.indexOf('placeholder') !== -1) {
                    existente.imagen = imagenProducto;
                }
            } else {
                self.items.push({
                    id: producto.id,
                    nombre: producto.nombre || 'Producto',
                    precio: parseFloat(producto.precio) || 0,
                    imagen: imagenProducto,
                    cantidad: 1,
                    stock: stockReal,
                    categoria: categoriaProducto
                });
            }
            
            self.guardar();
            self.mostrarNotificacion('✅ ' + producto.nombre + ' agregado');
        })
        .catch(function(err) {
            if (btn) btn.disabled = false;
            console.error('Error reservando:', err);
            alert('❌ Error de conexión al reservar');
        });
        
        return true;
    },
    
   eliminar: function(productoId) {
        var self = this;
        productoId = parseInt(productoId, 10);

        var item = null;
        for (var i = 0; i < this.items.length; i++) {
            if (parseInt(this.items[i].id, 10) === productoId) {
                item = this.items[i];
                break;
            }
        }
        if (!item) return;

        // ✅ LIBERAR TODA la reserva de este producto
        fetch('api/liberar.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                producto_id: productoId,
                sesion_id: this.getSesionId()
            })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            console.log('✅ Reserva liberada:', data);
        })
        .catch(function(err) {
            console.error('❌ Error liberando reserva:', err);
        });

        // Actualizar carrito local
        var nuevos = [];
        for (var j = 0; j < this.items.length; j++) {
            if (parseInt(this.items[j].id, 10) !== productoId) {
                nuevos.push(this.items[j]);
            }
        }
        this.items = nuevos;
        this.guardar();
        this.mostrarNotificacion('🗑️ Producto eliminado');
        this.actualizarUI();
        if (typeof this.renderizar === 'function') this.renderizar();
        if (typeof window.renderizarPanel === 'function') window.renderizarPanel();
    },
    
    actualizarCantidad: function(productoId, nuevaCantidad) {
        var self = this;
        
        productoId = parseInt(productoId, 10);
        nuevaCantidad = parseInt(nuevaCantidad, 10);
        
        if (isNaN(productoId) || isNaN(nuevaCantidad)) {
            console.warn('actualizarCantidad: parámetros inválidos', productoId, nuevaCantidad);
            return;
        }
        
        var item = null;
        for (var i = 0; i < this.items.length; i++) {
            if (parseInt(this.items[i].id, 10) === productoId) {
                item = this.items[i];
                break;
            }
        }
        if (!item) return;
        
        item.cantidad = parseInt(item.cantidad, 10) || 0;
        item.stock = parseInt(item.stock, 10) || 0;
        
        if (nuevaCantidad < 1) {
            this.eliminar(productoId);
            return;
        }
        
        if (nuevaCantidad > item.stock) {
            alert('❌ Solo hay ' + item.stock + ' unidades disponibles');
            return;
        }
        
        var diferencia = nuevaCantidad - item.cantidad;
        
        if (diferencia === 0) {
            return;
        }
        
        if (diferencia < 0) {
            fetch('api/liberar.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    producto_id: productoId,
                    sesion_id: this.getSesionId(),
                    cantidad_liberar: Math.abs(diferencia)
                })
            }).catch(function(err) { console.error('Error liberando:', err); });
            
            item.cantidad = nuevaCantidad;
            this.guardar();
            this.actualizarUI();
            if (typeof this.renderizar === 'function') this.renderizar();
            if (typeof window.renderizarPanel === 'function') window.renderizarPanel();
            return;
        }
        
        fetch('api/reservar.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                producto_id: productoId,
                cantidad: diferencia,
                sesion_id: this.getSesionId()
            })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.success) {
                alert('❌ ' + (data.mensaje || 'No se pudo reservar'));
                return;
            }
            item.cantidad = nuevaCantidad;
            self.guardar();
            self.actualizarUI();
            if (typeof self.renderizar === 'function') self.renderizar();
            if (typeof window.renderizarPanel === 'function') window.renderizarPanel();
        })
        .catch(function(err) {
            console.error('Error reservando:', err);
            alert('❌ Error de conexión');
        });
    },
    
   vaciar: function() {
        if (this.items.length === 0) return;
        if (!confirm('¿Seguro que quieres vaciar el carrito?')) return;

        var self = this;

        // ✅ LIBERAR TODAS las reservas de esta sesión
        fetch('api/liberar.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                todo: true,
                sesion_id: this.getSesionId()
            })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            console.log('✅ Reservas liberadas:', data);
        })
        .catch(function(err) {
            console.error('❌ Error liberando reservas:', err);
        });

        // Vaciar carrito local
        this.items = [];
        this.guardar();
        this.mostrarNotificacion('🔄 Carrito vaciado');
        this.actualizarUI();
        if (typeof this.renderizar === 'function') this.renderizar();
        if (typeof window.renderizarPanel === 'function') window.renderizarPanel();
    },
    
    getTotal: function() {
        var total = 0;
        for (var i = 0; i < this.items.length; i++) {
            total += this.items[i].precio * this.items[i].cantidad;
        }
        return total;
    },
    
    getCantidad: function() {
        var total = 0;
        for (var i = 0; i < this.items.length; i++) {
            total += this.items[i].cantidad;
        }
        return total;
    },
    
    actualizarContador: function() {
        var contadores = document.querySelectorAll('.cart-count');
        var total = this.getCantidad();
        for (var i = 0; i < contadores.length; i++) {
            contadores[i].textContent = total;
            contadores[i].style.display = total > 0 ? 'inline-block' : 'none';
        }
    },
    
    actualizarUI: function() {
        this.actualizarContador();
        var totales = document.querySelectorAll('.cart-total-preview');
        var total = this.getTotal();
        for (var i = 0; i < totales.length; i++) {
            totales[i].textContent = '$' + total.toFixed(2);
        }
    },
    
    renderizar: function() {
        var container = document.getElementById('cartItems');
        var totalContainer = document.getElementById('cartTotal');
        var subtotalContainer = document.getElementById('cartSubtotal');
        var vacio = document.getElementById('cartVacio');
        var resumen = document.getElementById('cartResumen');

        if (!container) return;

        if (this.items.length === 0) {
            container.innerHTML = '';
            if (vacio) vacio.style.display = 'block';
            if (resumen) resumen.style.display = 'none';
            if (totalContainer) totalContainer.textContent = '$0.00';
            if (subtotalContainer) subtotalContainer.textContent = '$0.00';
            return;
        }

        if (vacio) vacio.style.display = 'none';
        if (resumen) resumen.style.display = 'block';

        var html = '';
        for (var i = 0; i < this.items.length; i++) {
            var item = this.items[i];
            var precio = parseFloat(item.precio) || 0;
            var cantidad = parseInt(item.cantidad) || 0;
            var subtotal = (precio * cantidad).toFixed(2);

            html += `
                <div class="cart-item" data-id="${item.id}">
                    <div class="cart-item-image">
                        <img src="${item.imagen || 'https://via.placeholder.com/80x80?text=Sin+imagen'}" 
                             alt="${item.nombre || 'Producto'}" 
                             loading="lazy"
                             onerror="this.src='https://via.placeholder.com/80x80?text=Error'" />
                    </div>
                    <div class="cart-item-info">
                        <h3>${item.nombre || 'Producto'}</h3>
                        <p class="cart-item-category">${item.categoria || 'General'}</p>
                        <p class="cart-item-price">$${precio.toFixed(2)}</p>
                    </div>
                    <div class="cart-item-actions">
                        <div class="quantity-control">
                            <button class="qty-btn" onclick="carrito.actualizarCantidad(${item.id}, ${cantidad - 1})">
                                <i class="fas fa-minus"></i>
                            </button>
                            <span class="qty-number">${cantidad}</span>
                            <button class="qty-btn" onclick="carrito.actualizarCantidad(${item.id}, ${cantidad + 1})">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        <div class="cart-item-subtotal">
                            $${subtotal}
                        </div>
                        <button class="btn-remove" onclick="carrito.eliminar(${item.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
        }

        container.innerHTML = html;

        var total = this.getTotal();
        var envio = 5.99;
        var totalConEnvio = total + envio;

        if (totalContainer) totalContainer.textContent = '$' + totalConEnvio.toFixed(2);
        if (subtotalContainer) subtotalContainer.textContent = '$' + total.toFixed(2);
    },
    
    mostrarNotificacion: function(mensaje) {
        var notificacion = document.querySelector('.toast-notification');
        if (!notificacion) {
            notificacion = document.createElement('div');
            notificacion.className = 'toast-notification';
            document.body.appendChild(notificacion);
        }
        notificacion.textContent = mensaje;
        notificacion.style.display = 'block';
        notificacion.style.opacity = '1';
        clearTimeout(notificacion._timeout);
        notificacion._timeout = setTimeout(function() {
            notificacion.style.opacity = '0';
            setTimeout(function() {
                notificacion.style.display = 'none';
            }, 300);
        }, 3000);
    }
};

window.carrito = carrito;

console.log('✅ carrito.js cargado');