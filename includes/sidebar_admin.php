<?php
/**
 * Sidebar Admin
 * Barra lateral de navegación para administradores
 * Liga Panteras
 */

// Obtener la página actual para resaltar el item activo
$pagina_actual = basename($_SERVER['PHP_SELF']);
?>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="logo-container">
            <i class="fas fa-futbol"></i>
            <h3>Liga Panteras</h3>
        </div>
        <button class="btn-close-sidebar" onclick="toggleSidebar()">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <div class="sidebar-menu">
        <a href="dashboard_admin.php" class="menu-item <?php echo ($pagina_actual == 'dashboard_admin.php') ? 'active' : ''; ?>">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>
        <a href="liga.php" class="menu-item <?php echo ($pagina_actual == 'liga.php' || $pagina_actual == 'ligas.php') ? 'active' : ''; ?>">
            <i class="fas fa-trophy"></i>
            <span>Divisiones</span>
        </a>
        <a href="temporadas.php" class="menu-item <?php echo ($pagina_actual == 'temporadas.php') ? 'active' : ''; ?>">
            <i class="fas fa-calendar-alt"></i>
            <span>Temporadas</span>
        </a>
        <a href="equipos.php" class="menu-item <?php echo ($pagina_actual == 'equipos.php') ? 'active' : ''; ?>">
            <i class="fas fa-shield-alt"></i>
            <span>Equipos</span>
        </a>
        <a href="jugadores.php" class="menu-item <?php echo ($pagina_actual == 'jugadores.php') ? 'active' : ''; ?>">
            <i class="fas fa-users"></i>
            <span>Jugadores</span>
        </a>
        <a href="partidos.php" class="menu-item <?php echo ($pagina_actual == 'partidos.php') ? 'active' : ''; ?>">
            <i class="fas fa-calendar-check"></i>
            <span>Partidos</span>
        </a>
        <a href="resultados.php" class="menu-item <?php echo ($pagina_actual == 'resultados.php') ? 'active' : ''; ?>">
            <i class="fas fa-list-ol"></i>
            <span>Resultados</span>
        </a>
        <a href="estadisticas.php" class="menu-item <?php echo ($pagina_actual == 'estadisticas.php') ? 'active' : ''; ?>">
            <i class="fas fa-chart-bar"></i>
            <span>Estadísticas</span>
        </a>
        <a href="pagos.php" class="menu-item <?php echo ($pagina_actual == 'pagos.php') ? 'active' : ''; ?>">
            <i class="fas fa-money-bill-wave"></i>
            <span>Pagos</span>
        </a>
        <a href="banners.php" class="menu-item <?php echo ($pagina_actual == 'banners.php') ? 'active' : ''; ?>">
            <i class="fas fa-image"></i>
            <span>Banners</span>
        </a>
    </div>

    <div class="sidebar-footer">
        <a href="../controller/usuario_controller.php?action=salir" class="btn-logout">
            <i class="fas fa-sign-out-alt"></i>
            <span>Cerrar Sesión</span>
        </a>
    </div>
</div>
