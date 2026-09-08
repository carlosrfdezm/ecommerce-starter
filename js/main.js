// Datos de ejemplo (normalmente vendrían de un JSON externo)
const products = [
    {
        id: "1",
        name: "Auriculares Pro X",
        price: 89.00,
        description: "Cancelación de ruido, 30h de batería.",
        image: "https://picsum.photos/400/300?random=1",
        categories: ["Electrónica"]
    },
    {
        id: "2",
        name: "Teclado Mecánico RGB",
        price: 129.00,
        description: "Switches rojos, retroiluminación RGB.",
        image: "https://picsum.photos/400/300?random=2",
        categories: ["Electrónica"]
    },
    {
        id: "3",
        name: "Mochila Ejecutiva",
        price: 59.00,
        description: "Impermeable, compartimento para portátil.",
        image: "https://picsum.photos/400/300?random=3",
        categories: ["Accesorios"]
    },
    {
        id: "4",
        name: "Lámpara LED Inteligente",
        price: 45.00,
        description: "Control por voz, 16M de colores.",
        image: "https://picsum.photos/400/300?random=4",
        categories: ["Hogar"]
    }
];

// Renderizar productos en el grid
const grid = document.getElementById("productGrid");

products.forEach(product => {
    const card = document.createElement("div");
    card.className = "product-card";
    card.innerHTML = `
        <img src="${product.image}" alt="${product.name}" loading="lazy" />
        <div class="info">
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
            >
                🛒 Agregar al carrito
            </button>
        </div>
    `;
    grid.appendChild(card);
});

// Alerta de bienvenida (solo para la demo)
console.log("🚀 Tienda Headless lista para vender.");