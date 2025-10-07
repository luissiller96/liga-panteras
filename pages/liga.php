<?php
require_once("../config/conexion.php");
require_once("../includes/auth_check.php");

// Verificar que sea administrador
if (!es_admin()) {
    header("Location: login.php");
    exit();
}

$database = new Conectar();
$db = $database->getConnection();
$liga = new Liga($db);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Ligas - Liga Panteras</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- CSS Custom -->
    <link rel="stylesheet" href="../css/dashboard_admin.css">
    <link rel="stylesheet" href="../css/liga.css">
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h3><i class="fas fa-futbol"></i> Liga Panteras</h3>
            </div>
            <ul class="sidebar-menu">
                <li>
                    <a href="dashboard_admin.php">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="active">
                    <a href="liga.php">
                        <i class="fas fa-trophy"></i>
                        <span>Ligas</span>
                    </a>
                </li>
                <li>
                    <a href="equipo.php">
                        <i class="fas fa-shield-alt"></i>
                        <span>Equipos</span>
                    </a>
                </li>
                <li>
                    <a href="jugador.php">
                        <i class="fas fa-users"></i>
                        <span>Jugadores</span>
                    </a>
                </li>
                <li>
                    <a href="jornada.php">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Jornadas</span>
                    </a>
                </li>
                <li>
                    <a href="partido.php">
                        <i class="fas fa-futbol"></i>
                        <span>Partidos</span>
                    </a>
                </li>
                <li>
                    <a href="estadistica.php">
                        <i class="fas fa-chart-line"></i>
                        <span>Estadísticas</span>
                    </a>
                </li>
                <li>
                    <a href="banner.php">
                        <i class="fas fa-image"></i>
                        <span>Banners</span>
                    </a>
                </li>
                <li>
                    <a href="usuario.php">
                        <i class="fas fa-user-cog"></i>
                        <span>Usuarios</span>
                    </a>
                </li>
                <li>
                    <a href="../controllers/logout.php" class="text-danger">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Cerrar Sesión</span>
                    </a>
                </li>
            </ul>
        </aside>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Navbar -->
            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
                <div class="container-fluid">
                    <button class="btn btn-link" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="ms-auto d-flex align-items-center">
                        <span class="me-3">
                            <i class="fas fa-user-circle"></i> 
                            <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>
                        </span>
                    </div>
                </div>
            </nav>

            <!-- Page Content -->
            <div class="content-wrapper">
                <div class="container-fluid">
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h1 class="h3 mb-0"><i class="fas fa-trophy text-warning"></i> Gestión de Ligas</h1>
                            <p class="text-muted mb-0">Administra las divisiones de la Liga Panteras</p>
                        </div>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalLiga">
                            <i class="fas fa-plus"></i> Nueva Liga
                        </button>
                    </div>

                    <!-- Tabla de Ligas -->
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="tablaLigas" class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nombre</th>
                                            <th>Descripción</th>
                                            <th>Color</th>
                                            <th>Temporada Actual</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Datos cargados por AJAX -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Nueva/Editar Liga -->
    <div class="modal fade" id="modalLiga" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLigaTitle">
                        <i class="fas fa-trophy"></i> Nueva Liga
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formLiga">
                    <div class="modal-body">
                        <input type="hidden" id="liga_id" name="liga_id">
                        
                        <div class="row">
                            <!-- Nombre -->
                            <div class="col-md-6 mb-3">
                                <label for="nombre" class="form-label">
                                    Nombre de la Liga <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="nombre" name="nombre" 
                                       placeholder="Ej: Panteras Champions" required>
                            </div>

                            <!-- Color -->
                            <div class="col-md-6 mb-3">
                                <label for="color" class="form-label">
                                    Color Representativo <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="color" class="form-control form-control-color" 
                                           id="color" name="color" value="#1a73e8" required>
                                    <input type="text" class="form-control" id="color_hex" 
                                           placeholder="#1a73e8" maxlength="7">
                                </div>
                            </div>

                            <!-- Descripción -->
                            <div class="col-12 mb-3">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" 
                                          rows="3" placeholder="Descripción de la liga..."></textarea>
                            </div>

                            <!-- Temporada Actual -->
                            <div class="col-md-6 mb-3">
                                <label for="temporada_actual" class="form-label">
                                    Temporada Actual <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="temporada_actual" 
                                       name="temporada_actual" placeholder="Ej: 2024-2025" required>
                            </div>

                            <!-- Número de Equipos -->
                            <div class="col-md-6 mb-3">
                                <label for="numero_equipos" class="form-label">
                                    Número de Equipos
                                </label>
                                <input type="number" class="form-control" id="numero_equipos" 
                                       name="numero_equipos" min="4" max="20" value="8">
                                <small class="text-muted">Entre 4 y 20 equipos</small>
                            </div>

                            <!-- Jornadas Totales -->
                            <div class="col-md-6 mb-3">
                                <label for="jornadas_totales" class="form-label">
                                    Jornadas Totales
                                </label>
                                <input type="number" class="form-control" id="jornadas_totales" 
                                       name="jornadas_totales" min="1" max="50" value="8">
                            </div>

                            <!-- Equipos Clasifican Playoffs -->
                            <div class="col-md-6 mb-3">
                                <label for="equipos_clasifican" class="form-label">
                                    Equipos que Clasifican a Playoffs
                                </label>
                                <input type="number" class="form-control" id="equipos_clasifican" 
                                       name="equipos_clasifican" min="2" max="12" value="8">
                            </div>

                            <!-- Estado -->
                            <div class="col-md-6 mb-3">
                                <label for="estado" class="form-label">Estado</label>
                                <select class="form-select" id="estado" name="estado">
                                    <option value="activo">Activo</option>
                                    <option value="inactivo">Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary" id="btnGuardar">
                            <i class="fas fa-save"></i> Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- Bootstrap 5 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- JS Custom -->
    <script src="../js/liga.js"></script>
</body>
</html>