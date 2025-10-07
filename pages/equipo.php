<?php
session_start();
require_once '../config/database.php';
require_once '../models/Equipo.php';
require_once '../models/Liga.php';

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();
$equipo = new Equipo($db);
$liga = new Liga($db);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Equipos - Liga Panteras</title>
    
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
    <link rel="stylesheet" href="../css/equipo.css">
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
                <li>
                    <a href="liga.php">
                        <i class="fas fa-trophy"></i>
                        <span>Ligas</span>
                    </a>
                </li>
                <li class="active">
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
                            <h1 class="h3 mb-0"><i class="fas fa-shield-alt text-primary"></i> Gestión de Equipos</h1>
                            <p class="text-muted mb-0">Administra los equipos de todas las ligas</p>
                        </div>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalEquipo">
                            <i class="fas fa-plus"></i> Nuevo Equipo
                        </button>
                    </div>

                    <!-- Filtros -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Filtrar por Liga:</label>
                                    <select class="form-select" id="filtroLiga">
                                        <option value="">Todas las ligas</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Filtrar por Estado de Pago:</label>
                                    <select class="form-select" id="filtroPago">
                                        <option value="">Todos</option>
                                        <option value="pagado">Pagado</option>
                                        <option value="pendiente">Pendiente</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Filtrar por Estado:</label>
                                    <select class="form-select" id="filtroEstado">
                                        <option value="">Todos</option>
                                        <option value="activo">Activo</option>
                                        <option value="inactivo">Inactivo</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de Equipos -->
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="tablaEquipos" class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Logo</th>
                                            <th>Nombre</th>
                                            <th>Liga</th>
                                            <th>Representante</th>
                                            <th>Teléfono</th>
                                            <th>Pago</th>
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

    <!-- Modal Nueva/Editar Equipo -->
    <div class="modal fade" id="modalEquipo" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEquipoTitle">
                        <i class="fas fa-shield-alt"></i> Nuevo Equipo
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formEquipo" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" id="equipo_id" name="equipo_id">
                        <input type="hidden" id="logo_actual" name="logo_actual">
                        
                        <div class="row">
                            <!-- Liga -->
                            <div class="col-md-6 mb-3">
                                <label for="liga_id" class="form-label">
                                    Liga <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="liga_id" name="liga_id" required>
                                    <option value="">Seleccione una liga</option>
                                </select>
                            </div>

                            <!-- Nombre del Equipo -->
                            <div class="col-md-6 mb-3">
                                <label for="nombre" class="form-label">
                                    Nombre del Equipo <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="nombre" name="nombre" 
                                       placeholder="Ej: Tigres FC" required>
                            </div>

                            <!-- Logo -->
                            <div class="col-md-6 mb-3">
                                <label for="logo" class="form-label">Logo del Equipo</label>
                                <input type="file" class="form-control" id="logo" name="logo" 
                                       accept="image/*">
                                <small class="text-muted">Formatos: JPG, PNG. Máx: 2MB</small>
                                <div id="previewLogo" class="mt-2"></div>
                            </div>

                            <!-- Color Principal -->
                            <div class="col-md-6 mb-3">
                                <label for="color_principal" class="form-label">
                                    Color Principal <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="color" class="form-control form-control-color" 
                                           id="color_principal" name="color_principal" value="#000000" required>
                                    <input type="text" class="form-control" id="color_principal_hex" 
                                           placeholder="#000000" maxlength="7">
                                </div>
                            </div>

                            <!-- Color Secundario -->
                            <div class="col-md-6 mb-3">
                                <label for="color_secundario" class="form-label">Color Secundario</label>
                                <div class="input-group">
                                    <input type="color" class="form-control form-control-color" 
                                           id="color_secundario" name="color_secundario" value="#FFFFFF">
                                    <input type="text" class="form-control" id="color_secundario_hex" 
                                           placeholder="#FFFFFF" maxlength="7">
                                </div>
                            </div>

                            <!-- Representante -->
                            <div class="col-md-6 mb-3">
                                <label for="representante" class="form-label">
                                    Representante <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="representante" 
                                       name="representante" placeholder="Nombre del representante" required>
                            </div>

                            <!-- Teléfono -->
                            <div class="col-md-6 mb-3">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="tel" class="form-control" id="telefono" name="telefono" 
                                       placeholder="8112345678">
                            </div>

                            <!-- Email -->
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       placeholder="equipo@ejemplo.com">
                            </div>

                            <!-- Monto Inscripción -->
                            <div class="col-md-6 mb-3">
                                <label for="monto_inscripcion" class="form-label">Monto Inscripción</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="monto_inscripcion" 
                                           name="monto_inscripcion" step="0.01" min="0" value="0">
                                </div>
                            </div>

                            <!-- Estado de Pago -->
                            <div class="col-md-6 mb-3">
                                <label for="estado_pago" class="form-label">Estado de Pago</label>
                                <select class="form-select" id="estado_pago" name="estado_pago">
                                    <option value="pendiente">Pendiente</option>
                                    <option value="pagado">Pagado</option>
                                </select>
                            </div>

                            <!-- Estado -->
                            <div class="col-md-6 mb-3">
                                <label for="estado" class="form-label">Estado</label>
                                <select class="form-select" id="estado" name="estado">
                                    <option value="activo">Activo</option>
                                    <option value="inactivo">Inactivo</option>
                                </select>
                            </div>

                            <!-- Notas -->
                            <div class="col-12 mb-3">
                                <label for="notas" class="form-label">Notas</label>
                                <textarea class="form-control" id="notas" name="notas" rows="2" 
                                          placeholder="Información adicional del equipo..."></textarea>
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
    <script src="../js/equipo.js"></script>
</body>
</html>