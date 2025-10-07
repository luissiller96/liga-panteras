<?php
/**
 * Topbar Admin
 * Barra superior de navegación para administradores
 * Liga Panteras
 */

// Obtener título de la página según el archivo actual
$pagina_actual = basename($_SERVER['PHP_SELF']);
$titulos_paginas = [
    'dashboard_admin.php' => ['Dashboard', 'Panel de control principal'],
    'liga.php' => ['Divisiones', 'Gestión de ligas y divisiones'],
    'ligas.php' => ['Divisiones', 'Gestión de ligas y divisiones'],
    'temporadas.php' => ['Temporadas', 'Administración de temporadas'],
    'equipos.php' => ['Equipos', 'Gestión de equipos'],
    'jugadores.php' => ['Jugadores', 'Administración de jugadores'],
    'partidos.php' => ['Partidos', 'Calendario y gestión de partidos'],
    'resultados.php' => ['Resultados', 'Resultados y estadísticas'],
    'estadisticas.php' => ['Estadísticas', 'Análisis y reportes'],
    'pagos.php' => ['Pagos', 'Gestión de pagos y cuotas'],
    'banners.php' => ['Banners', 'Gestión de banners publicitarios']
];

$titulo = $titulos_paginas[$pagina_actual][0] ?? 'Liga Panteras';
$subtitulo = $titulos_paginas[$pagina_actual][1] ?? '';
?>

<!-- Top Bar -->
<div class="topbar">
    <button class="btn-toggle-sidebar" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <div class="topbar-title">
        <h1><?php echo $titulo; ?></h1>
        <?php if ($subtitulo): ?>
            <p><?php echo $subtitulo; ?></p>
        <?php endif; ?>
    </div>

    <div class="topbar-user">
        <i class="fas fa-user-circle"></i>
        <span>Hola, <?php echo htmlspecialchars(obtener_nombre()); ?></span>
    </div>
</div>
