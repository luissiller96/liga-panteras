<?php
// Establecer zona horaria de México Centro
date_default_timezone_set('America/Mexico_City');

if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
$puesto_nivel = $_SESSION["puesto_nivel"] ?? 0;
$puesto_nombre = $_SESSION["puesto_nombre"] ?? 'N/A';
?>
<nav class="bottom-nav-bar" id="bottom-nav-bar">
  <a href="dashboard.php" class="nav-item active" data-page="dashboard">
    <i class="fas fa-home"></i>
    <span>Inicio</span>
  </a>

  <a href="movimientos_planta.php" class="nav-item" data-page="movimientos">
    <i class="fas fa-exchange-alt"></i>
    <span>Movimientos</span>
  </a>

  <a href="produccion_planta.php" class="nav-item" data-page="produccion">
    <i class="fas fa-industry"></i>
    <span>Producción</span>
  </a>

  <a href="#" class="nav-item" id="settings-nav-item" data-page="configuracion">
    <i class="fas fa-bars"></i>
    <span>Más</span>
  </a>
</nav>

<div class="settings-drawer" id="settings-drawer">
  <div class="drawer-header">
    <h3>Menú</h3>
    <button class="close-drawer-button" id="close-drawer-button">&times;</button>
  </div>

  <div class="drawer-content">
<div class="drawer-grid">
  
  <a href="inventario_ajustes.php" class="drawer-card">
    <div class="drawer-card-icon icon-inventario"><i class="fas fa-sliders-h"></i></div>
    <span>Ajustes Inventario</span>
  </a>
  
<a href="../helpers/ReportesPlanta.php?tipo=inventario&fecha_inicio=<?php echo date('Y-m-01'); ?>&fecha_fin=<?php echo date('Y-m-t'); ?>" class="drawer-card" target="_blank">
    <div class="drawer-card-icon icon-reportes"><i class="fas fa-file-pdf"></i></div>
    <span>Reportes</span>
</a>
  
</div>

    <hr class="drawer-divider">

    <ul class="drawer-list">
      <li>
        <a href="#" id="toggleDarkMode">
          <div class="list-icon"><i class="fas fa-moon"></i></div>
          <span>Modo Oscuro</span>
          <i class="fas fa-chevron-right arrow-icon"></i>
        </a>
      </li>
      <li>
        <a href="../logout.php">
          <div class="list-icon"><i class="fas fa-sign-out-alt"></i></div>
          <span>Cerrar Sesión</span>
          <i class="fas fa-chevron-right arrow-icon"></i>
        </a>
      </li>
    </ul>
  </div>

<div class="drawer-user-profile">
    <div class="user-info">
      <span class="user-name"><?php echo htmlspecialchars($_SESSION['usu_nom'] ?? 'Usuario'); ?></span>
      <span class="user-role"><?php echo htmlspecialchars($puesto_nombre); ?></span>
    </div>
</div>
</div>

<div class="drawer-overlay" id="drawer-overlay"></div>