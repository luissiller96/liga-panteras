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
    
    <!-- SweetAlert2 (opcional para notificaciones) -->
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
                        <div class="kpi-value">32</div>
                        <div class="kpi-footer">
                            <span class="kpi-change positive">
                                <i class="fas fa-arrow-up"></i> 8%
                            </span>
                            <span class="text-muted">vs temporada anterior</span>
                        </div>
                    </div>
                    
                    <div class="kpi-card success">
                        <div class="kpi-header">
                            <div class="kpi-title">Jugadores Registrados</div>
                            <div class="kpi-icon">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        <div class="kpi-value">384</div>
                        <div class="kpi-footer">
                            <span class="kpi-change positive">
                                <i class="fas fa-arrow-up"></i> 12%
                            </span>
                            <span class="text-muted">vs temporada anterior</span>
                        </div>
                    </div>
                    
                    <div class="kpi-card info">
                        <div class="kpi-header">
                            <div class="kpi-title">Partidos Jugados</div>
                            <div class="kpi-icon">
                                <i class="fas fa-futbol"></i>
                            </div>
                        </div>
                        <div class="kpi-value">128</div>
                        <div class="kpi-footer">
                            <span class="kpi-change positive">
                                <i class="fas fa-arrow-up"></i> 24 esta semana
                            </span>
                        </div>
                    </div>
                    
                    <div class="kpi-card warning">
                        <div class="kpi-header">
                            <div class="kpi-title">Pagos Pendientes</div>
                            <div class="kpi-icon">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                        </div>
                        <div class="kpi-value">5</div>
                        <div class="kpi-footer">
                            <span class="kpi-change negative">
                                <i class="fas fa-arrow-down"></i> 3 menos
                            </span>
                            <span class="text-muted">vs semana anterior</span>
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
                                Partidos por Semana
                            </h3>
                            <div class="chart-actions">
                                <button class="chart-action-btn" title="Actualizar">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                                <button class="chart-action-btn" title="Descargar">
                                    <i class="fas fa-download"></i>
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
                                Goleadores Destacados
                            </h3>
                            <div class="chart-actions">
                                <button class="chart-action-btn" title="Ver todos">
                                    <i class="fas fa-expand"></i>
                                </button>
                            </div>
                        </div>
                        <div class="chart-body">
                            <canvas id="chartGoleadores"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Actividad Reciente -->
                <div class="activity-card">
                    <div class="activity-header">
                        <h3 class="card-title-custom">
                            <i class="fas fa-clock"></i>
                            Actividad Reciente
                        </h3>
                    </div>
                    <div class="activity-list">
                        <div class="activity-item">
                            <div class="activity-icon success">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">Partido finalizado</div>
                                <p class="activity-description">Tigres vs Águilas - 3-2</p>
                            </div>
                            <span class="activity-time">Hace 10 min</span>
                        </div>
                        
                        <div class="activity-item">
                            <div class="activity-icon info">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">Nuevo jugador registrado</div>
                                <p class="activity-description">Carlos Martínez - Leones FC</p>
                            </div>
                            <span class="activity-time">Hace 1 hora</span>
                        </div>
                        
                        <div class="activity-item">
                            <div class="activity-icon warning">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">Pago pendiente</div>
                                <p class="activity-description">Pumas FC - $2,500.00</p>
                            </div>
                            <span class="activity-time">Hace 3 horas</span>
                        </div>
                        
                        <div class="activity-item">
                            <div class="activity-icon success">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">Pago recibido</div>
                                <p class="activity-description">Halcones FC - $3,000.00</p>
                            </div>
                            <span class="activity-time">Hace 5 horas</span>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
    
    <!-- JavaScript -->
    <!-- jQuery (si usas DataTables) -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Chart.js (para gráficos) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <!-- Dashboard Admin JS -->
    <script src="../js/dashboard_admin.js"></script>
    
    <!-- Inicializar gráficos de ejemplo -->
    <script>
    // Gráfico de Partidos
    const ctxPartidos = document.getElementById('chartPartidos');
    if (ctxPartidos) {
        new Chart(ctxPartidos, {
            type: 'line',
            data: {
                labels: ['Semana 1', 'Semana 2', 'Semana 3', 'Semana 4', 'Semana 5'],
                datasets: [{
                    label: 'Partidos Jugados',
                    data: [12, 19, 15, 25, 22],
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
                    legend: {
                        display: false
                    }
                }
            }
        });
    }
    
    // Gráfico de Goleadores
    const ctxGoleadores = document.getElementById('chartGoleadores');
    if (ctxGoleadores) {
        new Chart(ctxGoleadores, {
            type: 'bar',
            data: {
                labels: ['J. Pérez', 'M. González', 'R. López', 'C. Martínez', 'A. Sánchez'],
                datasets: [{
                    label: 'Goles',
                    data: [15, 12, 10, 9, 8],
                    backgroundColor: '#ffd700',
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
    </script>
</body>
</html>