/**
 * JavaScript: Temporadas
 * Descripción: Gestión de temporadas
 */

let tablaTemporadas;

$(document).ready(function() {
    // Cargar ligas en select
    cargarLigasSelect('#liga_id');
    cargarLigasSelect('#edit_liga_id');
    
    // Inicializar DataTable
    tablaTemporadas = $('#tabla-temporadas').DataTable({
        ajax: {
            url: '../controller/temporada_controller.php',
            type: 'POST',
            data: { action: 'listar' },
            dataSrc: 'data'
        },
        columns: [
            { data: 0 },
            { data: 1 },
            { data: 2 },
            { data: 3 },
            { data: 4 },
            { data: 5 },
            { data: 6 },
            { data: 7 },
            { data: 8 }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-MX.json'
        },
        responsive: true,
        order: [[0, 'desc']]
    });
    
    // Submit formulario crear
    $('#form-crear-temporada').on('submit', function(e) {
        e.preventDefault();
        crearTemporada();
    });
    
    // Submit formulario editar
    $('#form-editar-temporada').on('submit', function(e) {
        e.preventDefault();
        actualizarTemporada();
    });
});

/**
 * Abrir modal para crear temporada
 */
function abrirModalCrear() {
    $('#form-crear-temporada')[0].reset();
    $('#modal-crear-temporada').modal('show');
}

/**
 * Crear temporada
 */
function crearTemporada() {
    const formData = {
        action: 'crear',
        liga_id: $('#liga_id').val(),
        temporada_nombre: $('#temporada_nombre').val(),
        fecha_inicio: $('#fecha_inicio').val(),
        fecha_fin: $('#fecha_fin').val(),
        num_jornadas: $('#num_jornadas').val() || 8,
        costo_inscripcion: $('#costo_inscripcion').val(),
        temporada_estatus: $('#temporada_estatus').val()
    };
    
    // Validaciones
    if (!formData.liga_id) {
        Swal.fire('Error', 'Selecciona una liga', 'warning');
        return;
    }
    if (!formData.temporada_nombre) {
        Swal.fire('Error', 'Ingresa el nombre de la temporada', 'warning');
        return;
    }
    if (!formData.fecha_inicio || !formData.fecha_fin) {
        Swal.fire('Error', 'Selecciona las fechas de inicio y fin', 'warning');
        return;
    }
    
    // Mostrar loading
    Swal.fire({
        title: 'Creando temporada...',
        text: 'Se generarán automáticamente las jornadas',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    $.ajax({
        url: '../controller/temporada_controller.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                $('#modal-crear-temporada').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: response.message,
                    timer: 2000
                });
                tablaTemporadas.ajax.reload();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message
                });
            }
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo conectar con el servidor'
            });
        }
    });
}

/**
 * Editar temporada
 */
function editar(temporada_id) {
    $.ajax({
        url: '../controller/temporada_controller.php',
        type: 'POST',
        data: {
            action: 'obtener',
            temporada_id: temporada_id
        },
        dataType: 'json',
        success: function(data) {
            if (data) {
                $('#edit_temporada_id').val(data.temporada_id);
                $('#edit_liga_id').val(data.liga_id);
                $('#edit_temporada_nombre').val(data.temporada_nombre);
                $('#edit_fecha_inicio').val(data.fecha_inicio);
                $('#edit_fecha_fin').val(data.fecha_fin);
                $('#edit_num_jornadas').val(data.num_jornadas);
                $('#edit_costo_inscripcion').val(data.costo_inscripcion);
                $('#edit_temporada_estatus').val(data.temporada_estatus);
                $('#modal-editar-temporada').modal('show');
            }
        }
    });
}

/**
 * Actualizar temporada
 */
function actualizarTemporada() {
    const formData = {
        action: 'actualizar',
        temporada_id: $('#edit_temporada_id').val(),
        liga_id: $('#edit_liga_id').val(),
        temporada_nombre: $('#edit_temporada_nombre').val(),
        fecha_inicio: $('#edit_fecha_inicio').val(),
        fecha_fin: $('#edit_fecha_fin').val(),
        num_jornadas: $('#edit_num_jornadas').val(),
        costo_inscripcion: $('#edit_costo_inscripcion').val(),
        temporada_estatus: $('#edit_temporada_estatus').val()
    };
    
    $.ajax({
        url: '../controller/temporada_controller.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                $('#modal-editar-temporada').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: response.message,
                    timer: 2000
                });
                tablaTemporadas.ajax.reload();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message
                });
            }
        }
    });
}

/**
 * Ver estadísticas de la temporada
 */
function verEstadisticas(temporada_id) {
    $.ajax({
        url: '../controller/temporada_controller.php',
        type: 'POST',
        data: {
            action: 'estadisticas',
            temporada_id: temporada_id
        },
        dataType: 'json',
        success: function(data) {
            const html = `
                <div class="row text-center">
                    <div class="col-md-4">
                        <h3>${data.total_equipos || 0}</h3>
                        <p>Equipos</p>
                    </div>
                    <div class="col-md-4">
                        <h3>${data.total_jugadores || 0}</h3>
                        <p>Jugadores</p>
                    </div>
                    <div class="col-md-4">
                        <h3>${data.total_partidos || 0}</h3>
                        <p>Partidos</p>
                    </div>
                    <div class="col-md-4">
                        <h3>${data.partidos_finalizados || 0}</h3>
                        <p>Finalizados</p>
                    </div>
                    <div class="col-md-4">
                        <h3>${data.total_jornadas || 0}</h3>
                        <p>Jornadas</p>
                    </div>
                    <div class="col-md-4">
                        <h3>${data.total_goles || 0}</h3>
                        <p>Goles</p>
                    </div>
                </div>
            `;
            
            Swal.fire({
                title: 'Estadísticas de la Temporada',
                html: html,
                width: 600
            });
        }
    });
}

/**
 * Eliminar temporada
 */
function eliminar(temporada_id) {
    Swal.fire({
        title: '¿Eliminar temporada?',
        text: 'Solo se puede eliminar si no tiene equipos registrados',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#d33'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '../controller/temporada_controller.php',
                type: 'POST',
                data: {
                    action: 'eliminar',
                    temporada_id: temporada_id
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Eliminado',
                            text: response.message,
                            timer: 2000
                        });
                        tablaTemporadas.ajax.reload();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message
                        });
                    }
                }
            });
        }
    });
}

/**
 * Cargar temporadas en select
 */
function cargarTemporadasSelect(selectId, ligaId, callback) {
    $.ajax({
        url: '../controller/temporada_controller.php',
        type: 'POST',
        data: {
            action: 'listar_por_liga',
            liga_id: ligaId
        },
        dataType: 'json',
        success: function(data) {
            const select = $(selectId);
            select.empty();
            select.append('<option value="">Seleccionar...</option>');
            
            data.forEach(function(temporada) {
                select.append(`<option value="${temporada.temporada_id}">${temporada.temporada_nombre}</option>`);
            });
            
            if (callback) callback();
        }
    });
}