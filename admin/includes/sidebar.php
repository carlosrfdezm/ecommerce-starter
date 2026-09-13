<!-- Sidebar -->
<aside class="sidebar">
    <div class="sidebar-brand">
        <i class="fas fa-store"></i>
        <span>Admin</span>
    </div>
    
    <nav class="sidebar-nav">
        <a href="index.php" <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'class="active"' : ''; ?>>
            <i class="fas fa-chart-line"></i>
            <span>Dashboard</span>
        </a>
        <a href="productos.php" <?php echo basename($_SERVER['PHP_SELF']) == 'productos.php' ? 'class="active"' : ''; ?>>
            <i class="fas fa-box"></i>
            <span>Productos</span>
        </a>
        <a href="inventario.php" <?php echo basename($_SERVER['PHP_SELF']) == 'inventario.php' ? 'class="active"' : ''; ?>>
            <i class="fas fa-boxes"></i>
            <span>Inventario</span>
        </a>
        <a href="categorias.php" <?php echo basename($_SERVER['PHP_SELF']) == 'categorias.php' ? 'class="active"' : ''; ?>>
            <i class="fas fa-tags"></i>
            <span>Categorías</span>
        </a>
        <a href="pedidos.php" <?php echo basename($_SERVER['PHP_SELF']) == 'pedidos.php' ? 'class="active"' : ''; ?>>
            <i class="fas fa-shopping-bag"></i>
            <span>Pedidos</span>
        </a>
        <a href="usuarios.php" <?php echo basename($_SERVER['PHP_SELF']) == 'usuarios.php' ? 'class="active"' : ''; ?>>
            <i class="fas fa-users"></i>
            <span>Usuarios</span>
        </a>
        <a href="configuracion.php" <?php echo basename($_SERVER['PHP_SELF']) == 'configuracion.php' ? 'class="active"' : ''; ?>>
            <i class="fas fa-cog"></i>
            <span>Configuración</span>
        </a>
    </nav>
    
    <div class="sidebar-footer">
        <div class="user-info">
            <i class="fas fa-user-circle"></i>
            <div>
                <strong><?php echo htmlspecialchars($_SESSION['admin_user_name']); ?></strong>
                <small><?php echo htmlspecialchars($_SESSION['admin_user_rol']); ?></small>
            </div>
        </div>
        <a href="logout.php" class="btn-logout">
            <i class="fas fa-sign-out-alt"></i>
            <span>Cerrar sesión</span>
        </a>
    </div>
</aside>