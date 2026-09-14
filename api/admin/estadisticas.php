<?php
// =====================================================
// API: ESTADÍSTICAS DEL DASHBOARD (solo admin)
// =====================================================
require_once '../../includes/config.php';
require_once '../../includes/auth.php';

verificarAdmin();

header('Content-Type: application/json; charset=utf-8');

function responder($data) {
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

$estados_vendidos = "'pagado','enviado','entregado'";
$accion = isset($_GET['accion']) ? $_GET['accion'] : '';

try {
    switch ($accion) {

        // =====================================================
        // RESUMEN GENERAL
        // =====================================================
        case 'resumen':
            $r = [];

            $stmt = $pdo->query("SELECT COUNT(*) FROM productos WHERE activo = 1");
            $r['productos'] = intval($stmt->fetchColumn());

            $stmt = $pdo->query("SELECT COUNT(*) FROM pedidos");
            $r['pedidos'] = intval($stmt->fetchColumn());

            $stmt = $pdo->query("SELECT COALESCE(SUM(total), 0) FROM pedidos WHERE estado IN ($estados_vendidos)");
            $r['ingresos'] = floatval($stmt->fetchColumn());

            $stmt = $pdo->query("SELECT COUNT(DISTINCT cliente_email) FROM pedidos");
            $r['clientes'] = intval($stmt->fetchColumn());

            // Mes actual
            $stmt = $pdo->query("
                SELECT COUNT(*) FROM pedidos 
                WHERE created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')
            ");
            $pedidos_mes_actual = intval($stmt->fetchColumn());

            $stmt = $pdo->query("
                SELECT COUNT(*) FROM pedidos 
                WHERE created_at >= DATE_FORMAT(NOW() - INTERVAL 1 MONTH, '%Y-%m-01')
                  AND created_at < DATE_FORMAT(NOW(), '%Y-%m-01')
            ");
            $pedidos_mes_anterior = intval($stmt->fetchColumn());

            $stmt = $pdo->query("
                SELECT COALESCE(SUM(total), 0) FROM pedidos 
                WHERE estado IN ($estados_vendidos)
                  AND created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')
            ");
            $ingresos_mes_actual = floatval($stmt->fetchColumn());

            $stmt = $pdo->query("
                SELECT COALESCE(SUM(total), 0) FROM pedidos 
                WHERE estado IN ($estados_vendidos)
                  AND created_at >= DATE_FORMAT(NOW() - INTERVAL 1 MONTH, '%Y-%m-01')
                  AND created_at < DATE_FORMAT(NOW(), '%Y-%m-01')
            ");
            $ingresos_mes_anterior = floatval($stmt->fetchColumn());

            $stmt = $pdo->query("
                SELECT COUNT(DISTINCT cliente_email) FROM pedidos 
                WHERE created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')
            ");
            $clientes_mes_actual = intval($stmt->fetchColumn());

            $stmt = $pdo->query("
                SELECT COUNT(DISTINCT cliente_email) FROM pedidos 
                WHERE created_at >= DATE_FORMAT(NOW() - INTERVAL 1 MONTH, '%Y-%m-01')
                  AND created_at < DATE_FORMAT(NOW(), '%Y-%m-01')
            ");
            $clientes_mes_anterior = intval($stmt->fetchColumn());

            $calcCambio = function($actual, $anterior) {
                if ($anterior == 0) return $actual > 0 ? 100 : 0;
                return round((($actual - $anterior) / $anterior) * 100, 1);
            };

            $r['cambio_pedidos']  = $calcCambio($pedidos_mes_actual, $pedidos_mes_anterior);
            $r['cambio_ingresos'] = $calcCambio($ingresos_mes_actual, $ingresos_mes_anterior);
            $r['cambio_clientes'] = $calcCambio($clientes_mes_actual, $clientes_mes_anterior);

            $stmt = $pdo->query("
                SELECT COUNT(*) FROM productos 
                WHERE created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')
            ");
            $r['cambio_productos'] = intval($stmt->fetchColumn());

            responder($r);
            break;

        // =====================================================
        // VENTAS ÚLTIMOS N DÍAS
        // =====================================================
        case 'ventas-diarias':
            $dias = isset($_GET['dias']) ? intval($_GET['dias']) : 30;
            if ($dias < 7 || $dias > 365) $dias = 30;

            $stmt = $pdo->prepare("
                SELECT 
                    DATE(created_at) as fecha,
                    COALESCE(SUM(total), 0) as total,
                    COUNT(*) as pedidos
                FROM pedidos
                WHERE estado IN ($estados_vendidos)
                  AND created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                GROUP BY DATE(created_at)
                ORDER BY fecha ASC
            ");
            $stmt->execute([$dias]);
            $datos = $stmt->fetchAll();

            $por_fecha = [];
            foreach ($datos as $d) {
                $por_fecha[$d['fecha']] = $d;
            }

            $resultado = [];
            for ($i = $dias - 1; $i >= 0; $i--) {
                $fecha = date('Y-m-d', strtotime("-$i days"));
                if (isset($por_fecha[$fecha])) {
                    $resultado[] = [
                        'fecha'   => $fecha,
                        'total'   => floatval($por_fecha[$fecha]['total']),
                        'pedidos' => intval($por_fecha[$fecha]['pedidos'])
                    ];
                } else {
                    $resultado[] = [
                        'fecha'   => $fecha,
                        'total'   => 0,
                        'pedidos' => 0
                    ];
                }
            }

            responder($resultado);
            break;

        // =====================================================
        // TOP PRODUCTOS
        // =====================================================
        case 'top-productos':
            $limite = isset($_GET['limite']) ? intval($_GET['limite']) : 5;
            if ($limite < 1 || $limite > 20) $limite = 5;

            $stmt = $pdo->prepare("
                SELECT 
                    dp.nombre_producto as nombre,
                    SUM(dp.cantidad) as cantidad,
                    SUM(dp.subtotal) as ingresos
                FROM detalles_pedido dp
                INNER JOIN pedidos p ON dp.pedido_id = p.id
                WHERE p.estado IN ($estados_vendidos)
                GROUP BY dp.producto_id, dp.nombre_producto
                ORDER BY cantidad DESC
                LIMIT ?
            ");
            $stmt->execute([$limite]);
            $datos = $stmt->fetchAll();

            $resultado = [];
            foreach ($datos as $d) {
                $resultado[] = [
                    'nombre'   => $d['nombre'],
                    'cantidad' => intval($d['cantidad']),
                    'ingresos' => floatval($d['ingresos'])
                ];
            }

            responder($resultado);
            break;

        // =====================================================
        // VENTAS POR CATEGORÍA
        // =====================================================
        case 'ventas-categoria':
            $stmt = $pdo->query("
                SELECT 
                    COALESCE(c.nombre, 'Sin categoría') as categoria,
                    SUM(dp.subtotal) as total
                FROM detalles_pedido dp
                INNER JOIN pedidos p ON dp.pedido_id = p.id
                LEFT JOIN productos prod ON dp.producto_id = prod.id
                LEFT JOIN categorias c ON prod.categoria_id = c.id
                WHERE p.estado IN ($estados_vendidos)
                GROUP BY c.id, c.nombre
                ORDER BY total DESC
                LIMIT 8
            ");
            $datos = $stmt->fetchAll();

            $resultado = [];
            foreach ($datos as $d) {
                $resultado[] = [
                    'categoria' => $d['categoria'],
                    'total'     => floatval($d['total'])
                ];
            }

            responder($resultado);
            break;

        default:
            http_response_code(400);
            responder(['error' => 'Acción no válida']);
    }

} catch (Exception $e) {
    http_response_code(500);
    responder(['error' => 'Error del servidor: ' . $e->getMessage()]);
}