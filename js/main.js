// ===== PRODUCTOS =====
const products = [
    {
        id: "1",
        name: "Auriculares Pro X",
        price: 89.00,
        description: "Cancelación de ruido activa, 30h de batería, sonido envolvente.",
        image: "https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400",
        category: "Electrónica"
    },
    {
        id: "2",
        name: "Teclado Mecánico RGB",
        price: 129.00,
        description: "Switches rojos, retroiluminación RGB personalizable, USB-C.",
        image: "https://images.unsplash.com/photo-1511467687858-23d96c32e4ae?w=400",
        category: "Electrónica"
    },
    {
        id: "3",
        name: "Mochila Ejecutiva",
        price: 59.00,
        description: "Impermeable, compartimento para portátil 15\", diseño minimalista.",
        image: "https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=400",
        category: "Accesorios"
    },
    {
        id: "4",
        name: "Lámpara LED Inteligente",
        price: 45.00,
        description: "Control por voz, 16M de colores, compatible con Alexa y Google.",
        image: "https://images.unsplash.com/photo-1513506003901-1e6a229e2d15?w=400",
        category: "Hogar"
    },
    {
        id: "5",
        name: "Zapatillas Running Air",
        price: 110.00,
        description: "Suela de aire, tejido transpirable, amortiguación premium.",
        image: "https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400",
        category: "Deportes"
    },
    {
        id: "6",
        name: "Reloj Inteligente Pro",
        price: 199.00,
        description: "Monitor de ritmo cardíaco, GPS, 7 días de batería, pantalla AMOLED.",
        image: "https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=400",
        category: "Electrónica"
    }
];

// ===== RENDERIZAR PRODUCTOS =====
const grid = document.getElementById('productGrid');

products.forEach(product => {
    const card = document.createElement('div');
    card.className = 'product-card';
    card.innerHTML = `
        <img src="${product.image}" alt="${product.name}" loading="lazy" />
        <div class="info">
            <span class="category-tag">${product.category}</span>
            <h3>${product.name}</h3>
            <div class="price">$${product.price.toFixed(2)}</div>
            <p class="description">${product.description}</p>
            <button 
                class="snipcart-add-item"
                data-item-id="${product.id}"
                data-item-price="${product.price}"
                data-item-url="/"
                data-item-name="${product.name}"
                data-item-image="${product.image}"
                data-item-description="${product.description}"
            >
                <i class="fas fa-plus-circle"></i> Agregar al carrito
            </button>
        </div>
    `;
    grid.appendChild(card);
});

// ===== NEWSLETTER =====
document.getElementById('newsletterForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const email = this.querySelector('input').value;
    if (email) {
        alert('✅ ¡Gracias por suscribirte! Te mantendremos informado.');
        this.querySelector('input').value = '';
    }
});

// ===== MENÚ MÓVIL =====
document.getElementById('menuToggle').addEventListener('click', function() {
    const navLinks = document.querySelector('.nav-links');
    if (navLinks.style.display === 'flex') {
        navLinks.style.display = 'none';
        this.innerHTML = '<i class="fas fa-bars"></i>';
    } else {
        navLinks.style.display = 'flex';
        navLinks.style.flexDirection = 'column';
        navLinks.style.position = 'absolute';
        navLinks.style.top = '100%';
        navLinks.style.left = '0';
        navLinks.style.right = '0';
        navLinks.style.background = 'white';
        navLinks.style.padding = '2rem';
        navLinks.style.boxShadow = '0 10px 30px rgba(0,0,0,0.1)';
        this.innerHTML = '<i class="fas fa-times"></i>';
    }
});

// ===== SCROLL SUAVE PARA ANCLAS =====
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});

console.log('🚀 TiendaPro - Ecommerce Headless');
console.log('⚡ Carga ultrarrápida, diseño premium');