<?php
/**
 * Dashboard Administración
 * Panel principal para administradores
 * Liga Panteras
 */

// Verificar autenticación
require_once("../includes/auth_check.php");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Liga Panteras</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS Base -->
    <link rel="stylesheet" href="../css/base.css">
    
    <!-- CSS Dashboard Admin -->
    <link rel="stylesheet" href="../css/dashboard_admin.css">
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>
<body>
    <div class="dashboard-wrapper">
        <!-- Sidebar -->
        <?php include("../includes/sidebar_admin.php"); ?>
        
        <!-- Overlay para móvil -->
        <div class="sidebar-overlay" id="sidebar-overlay"></div>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Topbar -->
            <?php include("../includes/topbar_admin.php"); ?>
            
            <!-- Dashboard Content -->
            <div class="dashboard-content">
                <!-- Título del Dashboard -->
                <div class="dashboard-title">
                    <h1>Dashboard Principal</h1>
                    <p class="dashboard-subtitle">Bienvenido al panel de control de Liga Panteras</p>
                </div>
                
                <!-- KPI Cards -->
                <div class="kpi-grid">
                    <div class="kpi-card primary">
                        <div class="kpi-header">
                            <div class="kpi-title">Total Equipos</div>
                            <div class="kpi-icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                        </div>
                        <div class="kpi-value" id="kpi-equipos">
                            <i class="fas fa-spinner fa-spin"></i>
                        </div>
                        <div class="kpi-footer">
                            <span class="text-muted">Equipos registrados</span>
                        </div>
                    </div>
                    
                    <div class="kpi-card success">
                        <div class="kpi-header">
                            <div class="kpi-title">Jugadores Registrados</div>
                            <div class="kpi-icon">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        <div class="kpi-value" id="kpi-jugadores">
                            <i class="fas fa-spinner fa-spin"></i>
                        </div>
                        <div class="kpi-footer">
                            <span class="text-muted">Jugadores activos</span>
                        </div>
                    </div>
                    
                    <div class="kpi-card info">
                        <div class="kpi-header">
                            <div class="kpi-title">Partidos Jugados</div>
                            <div class="kpi-icon">
                                <i class="fas fa-futbol"></i>
                            </div>
                        </div>
                        <div class="kpi-value" id="kpi-partidos">
                            <i class="fas fa-spinner fa-spin"></i>
                        </div>
                        <div class="kpi-footer">
                            <span class="text-muted">Partidos finalizados</span>
                        </div>
                    </div>
                    
                    <div class="kpi-card warning">
                        <div class="kpi-header">
                            <div class="kpi-title">Total Ligas</div>
                            <div class="kpi-icon">
                                <i class="fas fa-trophy"></i>
                            </div>
                        </div>
                        <div class="kpi-value" id="kpi-ligas">
                            <i class="fas fa-spinner fa-spin"></i>
                        </div>
                        <div class="kpi-footer">
                            <span class="text-muted">Ligas activas</span>
                        </div>
                    </div>
                </div>
                
                <!-- Módulos de Acceso Rápido -->
                <div class="quick-modules">
                    <h2 class="mb-3">Accesos Rápidos</h2>
                    <div class="modules-grid">
                        <a href="liga.php" class="module-card">
                            <div class="module-icon">
                                <i class="fas fa-trophy"></i>
                            </div>
                            <div class="module-title">Divisiones</div>
                            <p class="module-description">Gestionar ligas y divisiones</p>
                        </a>
                        
                        <a href="equipos.php" class="module-card">
                            <div class="module-icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <div class="module-title">Equipos</div>
                            <p class="module-description">Administrar equipos</p>
                        </a>
                        
                        <a href="jugadores.php" class="module-card">
                            <div class="module-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="module-title">Jugadores</div>
                            <p class="module-description">Gestionar jugadores</p>
                        </a>
                        
                        <a href="partidos.php" class="module-card">
                            <div class="module-icon">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="module-title">Partidos</div>
                            <p class="module-description">Programar partidos</p>
                        </a>
                        
                        <a href="resultados.php" class="module-card">
                            <div class="module-icon">
                                <i class="fas fa-list-ol"></i>
                            </div>
                            <div class="module-title">Resultados</div>
                            <p class="module-description">Capturar resultados</p>
                        </a>
                        
                        <a href="pagos.php" class="module-card">
                            <div class="module-icon">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <div class="module-title">Pagos</div>
                            <p class="module-description">Control de pagos</p>
                        </a>
                    </div>
                </div>
                
                <!-- Gráficos y Estadísticas -->
                <div class="stats-grid">
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3 class="chart-title">
                                <i class="fas fa-chart-line"></i>
                                Partidos por Jornada
                            </h3>
                            <div class="chart-actions">
                                <button class="chart-action-btn" title="Actualizar" onclick="cargarDatosGraficos()">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                        </div>
                        <div class="chart-body">
                            <canvas id="chartPartidos"></canvas>
                        </div>
                    </div>
                    
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3 class="chart-title">
                                <i class="fas fa-trophy"></i>
                                Top 5 Goleadores
                            </h3>
                            <div class="chart-actions">
                                <button class="chart-action-btn" title="Ver todos" onclick="window.location.href='goleadores.php'">
                                    <i class="fas fa-expand"></i>
                                </button>
                            </div>
                        </div>
                        <div class="chart-body">
                            <canvas id="chartGoleadores"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Partidos de la Semana -->
                <div class="activity-card">
                    <div class="activity-header">
                        <h3 class="card-title-custom">
                            <i class="fas fa-calendar-week"></i>
                            Partidos de esta Semana
                        </h3>
                    </div>
                    <div class="activity-list" id="partidos-semana">
                        <div class="text-center py-4">
                            <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                            <p class="text-muted mt-2">Cargando partidos...</p>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
    
    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="../js/dashboard_admin.js"></script>
    
    <script>
    // Variables globales para los gráficos
    let chartPartidos, chartGoleadores;
    
    // Cargar datos al iniciar
    $(document).ready(function() {
        cargarEstadisticas();
        cargarPartidosSemana();
        cargarDatosGraficos();
    });
    
    // ================================================
    // CARGAR ESTADÍSTICAS (KPIs)
    // ================================================
    function cargarEstadisticas() {
        $.ajax({
            url: '../controller/dashboard_controller.php',
            type: 'POST',
            data: { action: 'estadisticas_generales' },
            dataType: 'json',
            success: function(response) {
                // Animar y mostrar valores
                animarKPI('#kpi-equipos', response.total_equipos);
                animarKPI('#kpi-jugadores', response.total_jugadores);
                animarKPI('#kpi-partidos', response.total_partidos);
                animarKPI('#kpi-ligas', response.total_ligas);
            },
            error: function(xhr, status, error) {
                console.error('Error al cargar estadísticas:', error);
                $('#kpi-equipos').text('--');
                $('#kpi-jugadores').text('--');
                $('#kpi-partidos').text('--');
                $('#kpi-ligas').text('--');
            }
        });
    }
    
    // ================================================
    // CARGAR PARTIDOS DE LA SEMANA
    // ================================================
    function cargarPartidosSemana() {
        const fechaInicio = new Date().toISOString().split('T')[0];
        
        $.ajax({
            url: '../controller/dashboard_controller.php',
            type: 'POST',
            data: { 
                action: 'partidos_semana',
                fecha_inicio: fechaInicio
            },
            dataType: 'json',
            success: function(partidos) {
                let html = '';
                
                if (partidos.length === 0) {
                    html = `
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-times fa-2x text-muted"></i>
                            <p class="text-muted mt-2">No hay partidos programados esta semana</p>
                        </div>
                    `;
                } else {
                    partidos.forEach(function(partido) {
                        const fecha = new Date(partido.fecha_partido + ' ' + partido.hora_partido);
                        const fechaFormateada = fecha.toLocaleDateString('es-MX', { 
                            weekday: 'short', 
                            day: 'numeric', 
                            month: 'short' 
                        });
                        const horaFormateada = fecha.toLocaleTimeString('es-MX', { 
                            hour: '2-digit', 
                            minute: '2-digit' 
                        });
                        
                        html += `
                            <div class="activity-item">
                                <div class="activity-icon info">
                                    <i class="fas fa-futbol"></i>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-title">${partido.equipo_local} vs ${partido.equipo_visitante}</div>
                                    <p class="activity-description">${partido.liga_nombre} - Jornada ${partido.jornada_numero || 'Playoff'}</p>
                                </div>
                                <span class="activity-time">${fechaFormateada} ${horaFormateada}</span>
                            </div>
                        `;
                    });
                }
                
                $('#partidos-semana').html(html);
            },
            error: function(xhr, status, error) {
                console.error('Error al cargar partidos:', error);
                $('#partidos-semana').html(`
                    <div class="text-center py-4">
                        <i class="fas fa-exclamation-triangle fa-2x text-danger"></i>
                        <p class="text-danger mt-2">Error al cargar partidos</p>
                    </div>
                `);
            }
        });
    }
    
    // ================================================
    // CARGAR DATOS PARA GRÁFICOS
    // ================================================
    function cargarDatosGraficos() {
        // Obtener datos de partidos por jornada
        $.ajax({
            url: '../controller/dashboard_controller.php',
            type: 'POST',
            data: { action: 'estadisticas_generales' },
            dataType: 'json',
            success: function(response) {
                // Gráfico de Partidos (datos de ejemplo - puedes mejorar esto)
                const ctxPartidos = document.getElementById('chartPartidos');
                if (ctxPartidos) {
                    if (chartPartidos) chartPartidos.destroy();
                    
                    chartPartidos = new Chart(ctxPartidos, {
                        type: 'line',
                        data: {
                            labels: ['Jornada 1', 'Jornada 2', 'Jornada 3', 'Jornada 4', 'Jornada 5'],
                            datasets: [{
                                label: 'Partidos',
                                data: [8, 8, 6, 8, 7],
                                borderColor: '#e94560',
                                backgroundColor: 'rgba(233, 69, 96, 0.1)',
                                tension: 0.4,
                                fill: true
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false }
                            }
                        }
                    });
                }
            }
        });
        
        // Obtener tabla de goleo
        $.ajax({
            url: '../controller/dashboard_controller.php',
            type: 'POST',
            data: { 
                action: 'tabla_goleo',
                temporada_id: 1, // Puedes hacerlo dinámico
                limite: 5
            },
            dataType: 'json',
            success: function(goleadores) {
                const ctxGoleadores = document.getElementById('chartGoleadores');
                if (ctxGoleadores && goleadores.length > 0) {
                    if (chartGoleadores) chartGoleadores.destroy();
                    
                    const nombres = goleadores.map(g => g.jugador_nombre.split(' ')[0]);
                    const goles = goleadores.map(g => parseInt(g.total_goles));
                    
                    chartGoleadores = new Chart(ctxGoleadores, {
                        type: 'bar',
                        data: {
                            labels: nombres,
                            datasets: [{
                                label: 'Goles',
                                data: goles,
                                backgroundColor: '#ffd700',
                                borderRadius: 8
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false }
                            },
                            scales: {
                                y: { beginAtZero: true }
                            }
                        }
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al cargar goleadores:', error);
            }
        });
    }
    
    // ================================================
    // ANIMAR KPIs
    // ================================================
    function animarKPI(selector, valorFinal) {
        const elemento = $(selector);
        const valorInicial = 0;
        const duracion = 1000;
        const incremento = valorFinal / (duracion / 16);
        let valorActual = valorInicial;
        
        const timer = setInterval(() => {
            valorActual += incremento;
            if (valorActual >= valorFinal) {
                elemento.text(Math.round(valorFinal));
                clearInterval(timer);
            } else {
                elemento.text(Math.round(valorActual));
            }
        }, 16);
    }
    </script>
</body>
</html>