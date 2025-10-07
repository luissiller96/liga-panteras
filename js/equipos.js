// ============================================
// GESTIÓN DE EQUIPOS - LIGA PANTERAS
// ============================================

let tablaEquipos;
let modalEquipo;

$(document).ready(function() {
    inicializarModulo();
});

// ============================================
// INICIALIZACIÓN
// ============================================
function inicializarModulo() {
    // Inicializar modal
    modalEquipo = new bootstrap.Modal(document.getElementById('modalEquipo'));
    
    // Cargar combos
    cargarDivisiones();
    cargarTemporadas();
    
    // Inicializar DataTable
    inicializarDataTable();
    
    // Cargar datos
    cargarEquipos();
    
    // Eventos
    inicializarEventos();
}

function inicializarEventos() {
    // Cambio en filtros
    $('#filtroDivision, #filtroTemporada, #filtroEstado').on('change', function() {
        cargarEquipos();
    });
    
    // Preview de color
    $('#color_uniforme').on('input', function() {
        $('#color_uniforme_text').val($(this).val());
    });
    
    // Preview de logo
    $('#logo').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            if (file.size > 2 * 1024 * 1024) {
                Swal.fire({
                    icon: 'error',
                    title: 'Archivo muy grande',
                    text: 'El logo no debe superar los 2MB'
                });
                $(this).val('');
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#logoPreview').attr('src', e.target.result);
                $('#logoPreviewContainer').show();
            };
            reader.readAsDataURL(file);
        } else {
            $('#logoPreviewContainer').hide();
        }
    });
}

// ============================================
// DATATABLES
// ============================================
function inicializarDataTable() {
    tablaEquipos = $('#tablaEquipos').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
        },
        responsive: true,
        order: [[1, 'asc']],
        pageLength: 25,
        columnDefs: [
            { orderable: false, targets: [0, 8] }
        ]
    });
}

// ============================================
// CARGAR COMBOS
// ============================================
function cargarDivisiones() {
    $.ajax({
        url: '../controller/liga_controller.php',
        type: 'POST',
        data: { action: 'listar' },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                let options = '<option value="">Todas las divisiones</option>';
                let optionsModal = '<option value="">Seleccione una división</option>';
                
                response.data.forEach(liga => {
                    options += `<option value="${liga.liga_id}">${liga.liga_nombre}</option>`;
                    optionsModal += `<option value="${liga.liga_id}">${liga.liga_nombre}</option>`;
                });
                
                $('#filtroDivision').html(options);
                $('#liga_id').html(optionsModal);
            }
        }
    });
}

function cargarTemporadas() {
    $.ajax({
        url: '../controller/temporada_controller.php',
        type: 'POST',
        data: { action: 'listar' },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                let options = '<option value="">Todas las temporadas</option>';
                let optionsModal = '<option value="">Seleccione una temporada</option>';
                
                response.data.forEach(temp => {
                    options += `<option value="${temp.temporada_id}">${temp.temporada_nombre}</option>`;
                    optionsModal += `<option value="${temp.temporada_id}">${temp.temporada_nombre}</option>`;
                });
                
                $('#filtroTemporada').html(options);
                $('#temporada_id').html(optionsModal);
            }
        }
    });
}

// ============================================
// CARGAR EQUIPOS
// ============================================
function cargarEquipos() {
    const data = {
        action: 'listar'
    };
    
    // Aplicar filtros
    const division = $('#filtroDivision').val();
    const temporada = $('#filtroTemporada').val();
    const estado = $('#filtroEstado').val();
    
    if (division) data.liga_id = division;
    if (temporada) data.temporada_id = temporada;
    if (estado !== '') data.activo = estado;
    
    $.ajax({
        url: '../controller/equipo_controller.php',
        type: 'POST',
        data: data,
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                renderizarTabla(response.data);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message || 'Error al cargar equipos'
                });
            }
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al comunicarse con el servidor'
            });
        }
    });
}

function renderizarTabla(equipos) {
    tablaEquipos.clear();
    
    if (!equipos || equipos.length === 0) {
        tablaEquipos.draw();
        return;
    }
    
    equipos.forEach(equipo => {
        // Logo
        const logo = equipo.logo 
            ? `<img src="${equipo.logo}" alt="${equipo.equipo_nombre}" class="equipo-logo-tabla">` 
            : `<div class="equipo-logo-placeholder"><i class="fas fa-shield-alt"></i></div>`;
        
        // Color de uniforme
        const color = equipo.color_uniforme 
            ? `<div class="color-badge" style="background-color: ${equipo.color_uniforme}" title="${equipo.color_uniforme}"></div>`
            : '<span class="text-muted">-</span>';
        
        // Estado
        const estadoBadge = equipo.activo == 1
            ? '<span class="badge bg-success">Activo</span>'
            : '<span class="badge bg-danger">Inactivo</span>';
        
        // Número de jugadores
        const jugadores = equipo.total_jugadores || 0;
        const jugadoresHtml = `<button class="btn btn-sm btn-info" onclick="verJugadores(${equipo.equipo_id})" title="Ver jugadores">
            <i class="fas fa-users"></i> ${jugadores}
        </button>`;
        
        // Acciones
        const acciones = `
            <div class="btn-group" role="group">
                <button class="btn btn-sm btn-warning" onclick="editarEquipo(${equipo.equipo_id})" title="Editar">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-danger" onclick="eliminarEquipo(${equipo.equipo_id})" title="Eliminar">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        
        tablaEquipos.row.add([
            logo,
            equipo.equipo_nombre,
            equipo.liga_nombre || '-',
            equipo.temporada_nombre || '-',
            equipo.capitan || '-',
            color,
            jugadoresHtml,
            estadoBadge,
            acciones
        ]);
    });
    
    tablaEquipos.draw();
}

// ============================================
// MODAL NUEVO EQUIPO
// ============================================
function abrirModalNuevo() {
    $('#formEquipo')[0].reset();
    $('#equipo_id').val('');
    $('#tituloModal').html('<i class="fas fa-shield-alt"></i> Nuevo Equipo');
    $('#logoPreviewContainer').hide();
    $('#color_uniforme').val('#1a1a2e');
    $('#color_uniforme_text').val('#1a1a2e');
    $('#activo').prop('checked', true);
    modalEquipo.show();
}

// ============================================
// EDITAR EQUIPO
// ============================================
function editarEquipo(equipoId) {
    $.ajax({
        url: '../controller/equipo_controller.php',
        type: 'POST',
        data: { 
            action: 'obtener', 
            equipo_id: equipoId 
        },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                const equipo = response.data;
                
                $('#equipo_id').val(equipo.equipo_id);
                $('#equipo_nombre').val(equipo.equipo_nombre);
                $('#liga_id').val(equipo.liga_id);
                $('#temporada_id').val(equipo.temporada_id);
                $('#capitan').val(equipo.capitan);
                $('#color_uniforme').val(equipo.color_uniforme || '#1a1a2e');
                $('#color_uniforme_text').val(equipo.color_uniforme || '#1a1a2e');
                $('#observaciones').val(equipo.observaciones);
                $('#activo').prop('checked', equipo.activo == 1);
                
                // Mostrar logo actual si existe
                if (equipo.logo) {
                    $('#logoPreview').attr('src', equipo.logo);
                    $('#logoPreviewContainer').show();
                } else {
                    $('#logoPreviewContainer').hide();
                }
                
                $('#tituloModal').html('<i class="fas fa-edit"></i> Editar Equipo');
                modalEquipo.show();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message || 'Error al obtener datos del equipo'
                });
            }
        }
    });
}

// ============================================
// GUARDAR EQUIPO
// ============================================
function guardarEquipo() {
    // Validar formulario
    if (!$('#formEquipo')[0].checkValidity()) {
        $('#formEquipo')[0].reportValidity();
        return;
    }
    
    const formData = new FormData($('#formEquipo')[0]);
    const equipoId = $('#equipo_id').val();
    
    formData.append('action', equipoId ? 'actualizar' : 'insertar');
    formData.append('activo', $('#activo').is(':checked') ? 1 : 0);
    
    // Mostrar loading
    Swal.fire({
        title: 'Guardando...',
        text: 'Por favor espere',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    $.ajax({
        url: '../controller/equipo_controller.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: response.message,
                    timer: 1500,
                    showConfirmButton: false
                });
                
                modalEquipo.hide();
                cargarEquipos();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message || 'Error al guardar el equipo'
                });
            }
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al comunicarse con el servidor'
            });
        }
    });
}

// ============================================
// ELIMINAR EQUIPO
// ============================================
function eliminarEquipo(equipoId) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "Esta acción no se puede deshacer. Se eliminarán todos los datos asociados al equipo.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e94560',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '../controller/equipo_controller.php',
                type: 'POST',
                data: {
                    action: 'eliminar',
                    equipo_id: equipoId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Eliminado!',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                        
                        cargarEquipos();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'No se pudo eliminar el equipo'
                        });
                    }
                }
            });
        }
    });
}

// ============================================
// VER JUGADORES DEL EQUIPO
// ============================================
function verJugadores(equipoId) {
    $.ajax({
        url: '../controller/jugador_controller.php',
        type: 'POST',
        data: {
            action: 'listar_por_equipo',
            equipo_id: equipoId
        },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                renderizarJugadores(response.data);
                const modalJugadores = new bootstrap.Modal(document.getElementById('modalJugadores'));
                modalJugadores.show();
            } else {
                Swal.fire({
                    icon: 'info',
                    title: 'Sin jugadores',
                    text: 'Este equipo aún no tiene jugadores registrados'
                });
            }
        }
    });
}

function renderizarJugadores(jugadores) {
    const container = $('#listaJugadores');
    container.empty();
    
    if (!jugadores || jugadores.length === 0) {
        container.html('<div class="alert alert-info">No hay jugadores registrados en este equipo</div>');
        return;
    }
    
    let html = '<div class="row g-3">';
    
    jugadores.forEach(jugador => {
        const foto = jugador.foto 
            ? `<img src="${jugador.foto}" alt="${jugador.nombre_completo}">` 
            : '<i class="fas fa-user-circle"></i>';
        
        html += `
            <div class="col-md-6">
                <div class="jugador-card">
                    <div class="jugador-foto">${foto}</div>
                    <div class="jugador-info-card">
                        <h5>${jugador.nombre_completo}</h5>
                        <p class="mb-1"><i class="fas fa-tshirt"></i> #${jugador.numero_playera || 'S/N'}</p>
                        <p class="mb-1"><i class="fas fa-running"></i> ${jugador.posicion || 'Sin posición'}</p>
                        <div class="jugador-stats">
                            <span class="stat-item">
                                <i class="fas fa-futbol"></i> ${jugador.goles || 0} goles
                            </span>
                            <span class="stat-item text-warning">
                                <i class="fas fa-square"></i> ${jugador.tarjetas_amarillas || 0}
                            </span>
                            <span class="stat-item text-danger">
                                <i class="fas fa-square"></i> ${jugador.tarjetas_rojas || 0}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    container.html(html);
}

// ============================================
// EXPORTAR A EXCEL
// ============================================
function exportarExcel() {
    Swal.fire({
        icon: 'info',
        title: 'Exportando...',
        text: 'Preparando archivo Excel',
        timer: 1500,
        showConfirmButton: false
    });
    
    // Construir URL con filtros
    let url = '../controller/equipo_controller.php?action=exportar_excel';
    
    const division = $('#filtroDivision').val();
    const temporada = $('#filtroTemporada').val();
    const estado = $('#filtroEstado').val();
    
    if (division) url += `&liga_id=${division}`;
    if (temporada) url += `&temporada_id=${temporada}`;
    if (estado !== '') url += `&activo=${estado}`;
    
    // Abrir en nueva ventana
    window.open(url, '_blank');
}

// ============================================
// FUNCIÓN SIDEBAR TOGGLE (del dashboard)
// ============================================
function toggleSidebar() {
    $('#sidebar').toggleClass('collapsed');
    $('#mainContent').toggleClass('expanded');
}
