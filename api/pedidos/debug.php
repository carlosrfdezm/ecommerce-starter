<?php
// =====================================================
// DEBUG: VER ERRORES REALES
// =====================================================

// Activar errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Debug de crear.php</h1>";

// 1. Verificar DOCUMENT_ROOT
echo "<h2>1. DOCUMENT_ROOT:</h2>";
echo "<p>" . $_SERVER['DOCUMENT_ROOT'] . "</p>";

// 2. Verificar que config.php existe
echo "<h2>2. Config.php:</h2>";
$config_path = '../../includes/config.php';
if (file_exists($config_path)) {
    echo "<p style='color:green'>✅ Existe: " . $config_path . "</p>";
    try {
        require_once $config_path;
        echo "<p style='color:green'>✅ Config cargado</p>";
    } catch (Exception $e) {
        echo "<p style='color:red'>❌ Error: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color:red'>❌ No existe: " . $config_path . "</p>";
}

// 3. Verificar conexión a BD
echo "<h2>3. Conexión a BD:</h2>";
if (isset($pdo) && $pdo) {
    echo "<p style='color:green'>✅ BD conectada</p>";
} else {
    echo "<p style='color:red'>❌ BD NO conectada</p>";
}

// 4. Verificar Stripe
echo "<h2>4. Stripe:</h2>";
$stripe_paths = [
    '../../vendor/stripe/stripe-php-master/init.php',
    '../../vendor/stripe/stripe-php-master/lib/Stripe.php',
    '../../vendor/autoload.php',
    $_SERVER['DOCUMENT_ROOT'] . '/vendor/stripe/stripe-php-master/init.php',
    $_SERVER['DOCUMENT_ROOT'] . '/vendor/stripe/stripe-php-master/lib/Stripe.php',
];

$stripe_found = false;
foreach ($stripe_paths as $path) {
    if (file_exists($path)) {
        echo "<p style='color:green'>✅ Encontrado: " . $path . "</p>";
        $stripe_found = true;
    } else {
        echo "<p style='color:red'>❌ No existe: " . $path . "</p>";
    }
}

// 5. Verificar tablas
echo "<h2>5. Tablas de BD:</h2>";
if (isset($pdo) && $pdo) {
    try {
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "<p>Tablas: " . implode(', ', $tables) . "</p>";
        
        // Verificar estructura de pedidos
        echo "<h3>Estructura de pedidos:</h3>";
        $stmt = $pdo->query("DESCRIBE pedidos");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<ul>";
        foreach ($columns as $col) {
            echo "<li>" . $col['Field'] . " (" . $col['Type'] . ")</li>";
        }
        echo "</ul>";
    } catch (Exception $e) {
        echo "<p style='color:red'>❌ Error: " . $e->getMessage() . "</p>";
    }
}
?>