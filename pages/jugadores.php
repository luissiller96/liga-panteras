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
    <title>Gestión de Jugadores - Liga Panteras</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <!-- CSS Dashboard -->
    <link rel="stylesheet" href="../css/dashboard_admin.css">
    <link rel="stylesheet" href="../css/jugadores.css">
</head>
<body>

    <?php include("../includes/sidebar_admin.php"); ?>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <?php include("../includes/topbar_admin.php"); ?>

        <!-- Dashboard Content -->
        <div class="dashboard-content">
            
            <!-- KPIs -->
            <div class="row g-3 mb-4">
                <div class="col-lg-3 col-md-6">
                    <div class="kpi-card-small primary">
                        <div class="kpi-icon-small">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="kpi-info-small">
                            <div class="kpi-label-small">Total Jugadores</div>
                            <div class="kpi-value-small" id="kpi-total-jugadores">0</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="kpi-card-small success">
                        <div class="kpi-icon-small">
                            <i class="fas fa-futbol"></i>
                        </div>
                        <div class="kpi-info-small">
                            <div class="kpi-label-small">Goles Totales</div>
                            <div class="kpi-value-small" id="kpi-total-goles">0</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="kpi-card-small warning">
                        <div class="kpi-icon-small">
                            <i class="fas fa-square"></i>
                        </div>
                        <div class="kpi-info-small">
                            <div class="kpi-label-small">Tarjetas Amarillas</div>
                            <div class="kpi-value-small" id="kpi-tarjetas-amarillas">0</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="kpi-card-small danger">
                        <div class="kpi-icon-small">
                            <i class="fas fa-square"></i>
                        </div>
                        <div class="kpi-info-small">
                            <div class="kpi-label-small">Tarjetas Rojas</div>
                            <div class="kpi-value-small" id="kpi-tarjetas-rojas">0</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="action-buttons mb-4">
                <button class="btn btn-primary-custom" onclick="abrirModalNuevo()">
                    <i class="fas fa-plus"></i> Nuevo Jugador
                </button>
                <button class="btn btn-success-custom" onclick="exportarExcel()">
                    <i class="fas fa-file-excel"></i> Exportar Excel
                </button>
            </div>

            <!-- Filtros -->
            <div class="filtros-card mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Equipo</label>
                        <select class="form-select" id="filtroEquipo">
                            <option value="">Todos los equipos</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">División</label>
                        <select class="form-select" id="filtroDivision">
                            <option value="">Todas las divisiones</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Posición</label>
                        <select class="form-select" id="filtroPosicion">
                            <option value="">Todas las posiciones</option>
                            <option value="Portero">Portero</option>
                            <option value="Defensa">Defensa</option>
                            <option value="Medio">Medio</option>
                            <option value="Delantero">Delantero</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Estado</label>
                        <select class="form-select" id="filtroEstado">
                            <option value="">Todos</option>
                            <option value="1">Activos</option>
                            <option value="0">Inactivos</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Tabla de Jugadores -->
            <div class="section-card">
                <div class="section-body">
                    <div class="table-responsive">
                        <table id="tablaJugadores" class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Foto</th>
                                    <th>Nombre</th>
                                    <th>Equipo</th>
                                    <th>Número</th>
                                    <th>Posición</th>
                                    <th class="text-center">Goles</th>
                                    <th class="text-center">TA</th>
                                    <th class="text-center">TR</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
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

    <!-- Modal Nuevo/Editar Jugador -->
    <div class="modal fade" id="modalJugador" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tituloModal">
                        <i class="fas fa-user-plus"></i> Nuevo Jugador
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formJugador" enctype="multipart/form-data">
                        <input type="hidden" id="jugador_id" name="jugador_id">
                        
                        <!-- Pestañas -->
                        <ul class="nav nav-tabs mb-4" id="tabsJugador" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="tab-datos-generales" data-bs-toggle="tab" 
                                        data-bs-target="#datos-generales" type="button">
                                    <i class="fas fa-user"></i> Datos Generales
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="tab-equipo" data-bs-toggle="tab" 
                                        data-bs-target="#equipo" type="button">
                                    <i class="fas fa-shield-alt"></i> Equipo y Posición
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="tab-estadisticas" data-bs-toggle="tab" 
                                        data-bs-target="#estadisticas" type="button">
                                    <i class="fas fa-chart-line"></i> Estadísticas
                                </button>
                            </li>
                        </ul>

                        <!-- Contenido de pestañas -->
                        <div class="tab-content" id="tabsJugadorContent">
                            
                            <!-- Tab: Datos Generales -->
                            <div class="tab-pane fade show active" id="datos-generales">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label required">Nombre(s)</label>
                                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label required">Apellido Paterno</label>
                                        <input type="text" class="form-control" id="apellido_paterno" name="apellido_paterno" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Apellido Materno</label>
                                        <input type="text" class="form-control" id="apellido_materno" name="apellido_materno">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Fecha de Nacimiento</label>
                                        <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Género</label>
                                        <select class="form-select" id="genero" name="genero">
                                            <option value="">Seleccionar</option>
                                            <option value="M">Masculino</option>
                                            <option value="F">Femenino</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Teléfono</label>
                                        <input type="tel" class="form-control" id="telefono" name="telefono" placeholder="10 dígitos">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">CURP</label>
                                        <input type="text" class="form-control text-uppercase" id="curp" name="curp" maxlength="18" placeholder="18 caracteres">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Fotografía</label>
                                        <input type="file" class="form-control" id="foto" name="foto" accept="image/*">
                                        <small class="text-muted">Formatos: JPG, PNG. Max: 2MB</small>
                                    </div>
                                    <div class="col-12" id="fotoPreviewContainer" style="display: none;">
                                        <div class="foto-preview">
                                            <img id="fotoPreview" src="" alt="Preview">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tab: Equipo y Posición -->
                            <div class="tab-pane fade" id="equipo">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label required">Equipo</label>
                                        <select class="form-select" id="equipo_id" name="equipo_id" required>
                                            <option value="">Seleccione un equipo</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Número de Playera</label>
                                        <input type="number" class="form-control" id="numero_playera" name="numero_playera" min="1" max="99">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Posición</label>
                                        <select class="form-select" id="posicion" name="posicion">
                                            <option value="">Seleccionar</option>
                                            <option value="Portero">Portero</option>
                                            <option value="Defensa">Defensa</option>
                                            <option value="Medio">Medio</option>
                                            <option value="Delantero">Delantero</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Observaciones</label>
                                        <textarea class="form-control" id="observaciones" name="observaciones" rows="4" placeholder="Información adicional del jugador..."></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Tab: Estadísticas -->
                            <div class="tab-pane fade" id="estadisticas">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Goles</label>
                                        <input type="number" class="form-control" id="goles" name="goles" min="0" value="0">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Tarjetas Amarillas</label>
                                        <input type="number" class="form-control" id="tarjetas_amarillas" name="tarjetas_amarillas" min="0" value="0">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Tarjetas Rojas</label>
                                        <input type="number" class="form-control" id="tarjetas_rojas" name="tarjetas_rojas" min="0" value="0">
                                    </div>
                                    <div class="col-12">
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle"></i>
                                            <strong>Nota:</strong> Las estadísticas se actualizan automáticamente al registrar partidos.
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="activo" name="activo" checked>
                                            <label class="form-check-label" for="activo">Jugador Activo</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="button" class="btn btn-primary" onclick="guardarJugador()">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Dashboard JS -->
    <script src="../js/dashboard_admin.js"></script>
    <script src="../js/jugadores.js"></script>

</body>
</html>
