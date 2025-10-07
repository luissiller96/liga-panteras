<?php
require_once("../config/conexion.php");
require_once("../includes/auth_check.php");

// Verificar que sea administrador
if (!es_admin()) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Liga Panteras</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- CSS Dashboard -->
    <link rel="stylesheet" href="../css/dashboard_admin.css">
</head>
<body>

    <?php include("../includes/sidebar_admin.php"); ?>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <?php include("../includes/topbar_admin.php"); ?>

        <!-- Loader -->
        <div class="dashboard-loader" id="dashboardLoader">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-3">Cargando información...</p>
        </div>

        <!-- Dashboard Content -->
        <div class="dashboard-content" id="dashboardContent" style="display: none;">
            
            <!-- KPIs Section -->
            <div class="row g-4 mb-4">
                <!-- KPI: Total Equipos -->
                <div class="col-lg-3 col-md-6">
                    <div class="kpi-card primary">
                        <div class="kpi-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div class="kpi-info">
                            <div class="kpi-label">Total Equipos</div>
                            <div class="kpi-value" id="kpi-total-equipos">0</div>
                            <div class="kpi-subtitle">En todas las divisiones</div>
                        </div>
                    </div>
                </div>

                <!-- KPI: Total Jugadores -->
                <div class="col-lg-3 col-md-6">
                    <div class="kpi-card success">
                        <div class="kpi-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="kpi-info">
                            <div class="kpi-label">Total Jugadores</div>
                            <div class="kpi-value" id="kpi-total-jugadores">0</div>
                            <div class="kpi-subtitle">Registrados activos</div>
                        </div>
                    </div>
                </div>

                <!-- KPI: Partidos Jornada -->
                <div class="col-lg-3 col-md-6">
                    <div class="kpi-card warning">
                        <div class="kpi-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="kpi-info">
                            <div class="kpi-label">Partidos Jornada Actual</div>
                            <div class="kpi-value" id="kpi-partidos-jornada">0</div>
                            <div class="kpi-subtitle">En curso o próximos</div>
                        </div>
                    </div>
                </div>

                <!-- KPI: Pagos Pendientes -->
                <div class="col-lg-3 col-md-6">
                    <div class="kpi-card danger">
                        <div class="kpi-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="kpi-info">
                            <div class="kpi-label">Pagos Pendientes</div>
                            <div class="kpi-value" id="kpi-pagos-pendientes">$0</div>
                            <div class="kpi-subtitle">Por cobrar</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resumen por División -->
            <div class="section-card mb-4">
                <div class="section-header">
                    <h2><i class="fas fa-trophy"></i> Resumen por División</h2>
                </div>
                <div class="section-body">
                    <div class="row g-3" id="resumen-divisiones">
                        <!-- Se llena dinámicamente -->
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <!-- Próximos Partidos -->
                <div class="col-lg-6">
                    <div class="section-card">
                        <div class="section-header">
                            <h2><i class="fas fa-calendar-day"></i> Próximos Partidos</h2>
                        </div>
                        <div class="section-body">
                            <div id="proximos-partidos" class="partidos-list">
                                <!-- Se llena dinámicamente -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Últimos Resultados -->
                <div class="col-lg-6">
                    <div class="section-card">
                        <div class="section-header">
                            <h2><i class="fas fa-check-circle"></i> Últimos Resultados</h2>
                        </div>
                        <div class="section-body">
                            <div id="ultimos-resultados" class="resultados-list">
                                <!-- Se llena dinámicamente -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de Goleo General -->
            <div class="section-card mb-4">
                <div class="section-header">
                    <h2><i class="fas fa-futbol"></i> Tabla de Goleo (Top 10)</h2>
                </div>
                <div class="section-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="tabla-goleo">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Jugador</th>
                                    <th>Equipo</th>
                                    <th>División</th>
                                    <th class="text-center">Goles</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Se llena dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Equipos con Pagos Pendientes -->
            <div class="section-card">
                <div class="section-header">
                    <h2><i class="fas fa-money-bill-wave"></i> Equipos con Pagos Pendientes</h2>
                </div>
                <div class="section-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="tabla-pagos-pendientes">
                            <thead>
                                <tr>
                                    <th>Equipo</th>
                                    <th>División</th>
                                    <th>Temporada</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end">Pagado</th>
                                    <th class="text-end">Pendiente</th>
                                    <th class="text-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Se llena dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Dashboard JS -->
    <script src="../js/dashboard_admin.js"></script>

</body>
</html>
