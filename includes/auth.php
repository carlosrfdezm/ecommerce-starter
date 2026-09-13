<?php
// =====================================================
// MIDDLEWARE DE AUTENTICACIÓN
// =====================================================

function verificarAdmin() {
    session_start();
    
    if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
        header('Location: login.php');
        exit;
    }
    
    // Verificar que el usuario sigue activo en la base de datos
    global $pdo;
    $stmt = $pdo->prepare("SELECT activo FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['admin_user_id']]);
    $user = $stmt->fetch();
    
    if (!$user || !$user['activo']) {
        session_destroy();
        header('Location: login.php');
        exit;
    }
}

function verificarRol($roles_permitidos = ['admin']) {
    verificarAdmin();
    
    if (!in_array($_SESSION['admin_user_rol'], $roles_permitidos)) {
        die('❌ No tienes permisos para acceder a esta sección');
    }
}

function obtenerUsuarioActual($pdo) {
    if (!isset($_SESSION['admin_user_id'])) {
        return null;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['admin_user_id']]);
    return $stmt->fetch();
}