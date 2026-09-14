// =====================================================
// DASHBOARD ADMIN - GRÁFICOS Y ESTADÍSTICAS
// =====================================================
console.log('📊 Dashboard admin cargando...');

// Ruta a la API (desde admin/index.php → ../api/admin/estadisticas.php)
const API_STATS = '../api/admin/estadisticas.php';

// Configuración global de Chart.js
if (typeof Chart !== 'undefined') {
    Chart.defaults.font.family = "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif";
    Chart.defaults.font.size = 12;
    Chart.defaults.color = '#64748b';
    Chart.defaults.plugins.legend.labels.usePointStyle = true;
    Chart.defaults.plugins.legend.labels.padding = 15;
}

// -----------------------------------------------------
// Helper: fetch JSON
// -----------------------------------------------------
async function fetchJSON(url) {
    const r = await fetch(url);
    if (!r.ok) throw new Error('HTTP ' + r.status);
    return r.json();
}

// -----------------------------------------------------
// Formatear moneda
// -----------------------------------------------------
function money(n) {
    return '$' + Number(n).toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function moneyShort(n) {
    n = Number(n);
    if (n >= 1000000) return '$' + (n / 1000000).toFixed(1) + 'M';
    if (n >= 1000) return '$' + (n / 1000).toFixed(1) + 'k';
    return '$' + n.toFixed(0);
}

// -----------------------------------------------------
// 1. CARGAR RESUMEN (tarjetas)
// -----------------------------------------------------
async function cargarResumen() {
    try {
        const data = await fetchJSON(API_STATS + '?accion=resumen');

        document.getElementById('stat-productos').textContent = data.productos;
        document.getElementById('stat-pedidos').textContent = data.pedidos;
        document.getElementById('stat-ingresos').textContent = money(data.ingresos);
        document.getElementById('stat-clientes').textContent = data.clientes;

        actualizarCambio('cambio-productos', data.cambio_productos, 'nuevos');
        actualizarCambio('cambio-pedidos', data.cambio_pedidos);
        actualizarCambio('cambio-ingresos', data.cambio_ingresos);
        actualizarCambio('cambio-clientes', data.cambio_clientes);

    } catch (err) {
        console.error('Error cargando resumen:', err);
    }
}

function actualizarCambio(elementId, valor, sufijo) {
    const el = document.getElementById(elementId);
    if (!el) return;

    const num = parseFloat(valor) || 0;
    const esPositivo = num > 0;
    const esNegativo = num < 0;

    let texto, clase;
    if (esPositivo) {
        texto = '+' + num + (sufijo ? ' ' + sufijo : '%');
        clase = 'stat-cambio positivo';
    } else if (esNegativo) {
        texto = num + '%';
        clase = 'stat-cambio negativo';
    } else {
        texto = sufijo ? '0 ' + sufijo : '0%';
        clase = 'stat-cambio neutro';
    }

    el.textContent = texto;
    el.className = clase;
}

// -----------------------------------------------------
// 2. GRÁFICO DE VENTAS (línea)
// -----------------------------------------------------
async function cargarGraficoVentas() {
    try {
        const data = await fetchJSON(API_STATS + '?accion=ventas-diarias&dias=30');

        const labels = data.map(d => {
            const f = new Date(d.fecha + 'T00:00:00');
            return f.getDate() + '/' + (f.getMonth() + 1);
        });
        const totales = data.map(d => d.total);
        const pedidos = data.map(d => d.pedidos);

        const ctx = document.getElementById('chartVentas').getContext('2d');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Ingresos ($)',
                    data: totales,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    pointRadius: 0,
                    pointHoverRadius: 5,
                    yAxisID: 'y'
                }, {
                    label: 'Pedidos',
                    data: pedidos,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 2,
                    fill: false,
                    tension: 0.3,
                    pointRadius: 0,
                    pointHoverRadius: 5,
                    yAxisID: 'y1',
                    borderDash: [5, 5]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'top', align: 'end' },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                if (ctx.dataset.label === 'Ingresos ($)') {
                                    return ctx.dataset.label + ': ' + money(ctx.parsed.y);
                                }
                                return ctx.dataset.label + ': ' + ctx.parsed.y;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        beginAtZero: true,
                        ticks: { callback: v => moneyShort(v) },
                        grid: { color: 'rgba(0,0,0,0.05)' }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        beginAtZero: true,
                        grid: { drawOnChartArea: false }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });

    } catch (err) {
        console.error('Error cargando gráfico ventas:', err);
    }
}

// -----------------------------------------------------
// 3. TOP PRODUCTOS (barras horizontales)
// -----------------------------------------------------
async function cargarTopProductos() {
    try {
        const data = await fetchJSON(API_STATS + '?accion=top-productos&limite=5');

        if (data.length === 0) {
            document.getElementById('topProductosVacio').style.display = 'block';
            return;
        }

        const labels = data.map(d => d.nombre.length > 25 ? d.nombre.slice(0, 22) + '...' : d.nombre);
        const cantidades = data.map(d => d.cantidad);

        const ctx = document.getElementById('chartTopProductos').getContext('2d');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Unidades vendidas',
                    data: cantidades,
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(139, 92, 246, 0.8)',
                        'rgba(236, 72, 153, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(16, 185, 129, 0.8)'
                    ],
                    borderRadius: 6,
                    borderSkipped: false
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                const item = data[ctx.dataIndex];
                                return [
                                    item.cantidad + ' unidades',
                                    'Ingresos: ' + money(item.ingresos)
                                ];
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.05)' }
                    },
                    y: {
                        grid: { display: false }
                    }
                }
            }
        });

    } catch (err) {
        console.error('Error cargando top productos:', err);
    }
}

// -----------------------------------------------------
// 4. VENTAS POR CATEGORÍA (donut)
// -----------------------------------------------------
async function cargarVentasCategoria() {
    try {
        const data = await fetchJSON(API_STATS + '?accion=ventas-categoria');

        if (data.length === 0) {
            document.getElementById('ventasCategoriaVacio').style.display = 'block';
            return;
        }

        const labels = data.map(d => d.categoria);
        const totales = data.map(d => d.total);

        const colores = [
            '#3b82f6', '#8b5cf6', '#ec4899', '#f59e0b',
            '#10b981', '#06b6d4', '#f43f5e', '#64748b'
        ];

        const ctx = document.getElementById('chartVentasCategoria').getContext('2d');

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: totales,
                    backgroundColor: colores.slice(0, data.length),
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: { position: 'right', align: 'center' },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                const porcentaje = ((ctx.parsed / total) * 100).toFixed(1);
                                return [
                                    ctx.label,
                                    money(ctx.parsed) + ' (' + porcentaje + '%)'
                                ];
                            }
                        }
                    }
                }
            }
        });

    } catch (err) {
        console.error('Error cargando ventas por categoría:', err);
    }
}

// -----------------------------------------------------
// INICIALIZACIÓN
// -----------------------------------------------------
document.addEventListener('DOMContentLoaded', function() {
    cargarResumen();
    cargarGraficoVentas();
    cargarTopProductos();
    cargarVentasCategoria();
});