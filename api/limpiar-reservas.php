<?php
// Marcar reservas expiradas (limpieza)
require_once '../includes/config.php';

try {
    $stmt = $pdo->prepare("
        UPDATE reservas_stock 
        SET estado = 'expirada' 
        WHERE estado = 'activa' AND expira_en < NOW()
    ");
    $stmt->execute();
    echo "OK - Reservas expiradas: " . $stmt->rowCount();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}