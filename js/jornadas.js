/**
 * JavaScript: Jornadas
 * Descripción: Gestión de jornadas
 */

let tablaJornadas;
let temporadaActual = null;

$(document).ready(function() {
    // Obtener temporada_id de la URL si existe
    const urlParams = new URLSearchParams(window.location.search);
    temporadaActual = urlParams.get('temporada_id');
    
    // Inicializar DataTable
    const ajaxData = { action: 'listar' };
    if (temporadaActual) {
        ajaxData.temporada_id = temporadaActual;
    }
    
    tablaJornadas = $('#tabla-jornadas').DataTable({
        ajax: {
            url: '../controller/jornada_controller.php',
            type: 'POST',
            data: ajaxData,
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
            { data: 7 }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-MX.json'
        },
        responsive: true,
        order: [[1, 'asc']]
    });
    
    // Submit formulario crear
    $('#form-crear-jornada').on('submit', function(e) {
        e.preventDefault();
        crearJornada();
    });
    
    // Submit formulario editar
    $('#form-editar-jornada').on('submit', function(e) {
        e.preventDefault();
        actualizarJornada();
    });
});

/**
 * Abrir modal para crear jornada
 */
function abrirModalCrear() {
    $('#form-crear-jornada')[0].reset();
    
    if (temporadaActual) {
        $('#crear_temporada_id').val(temporadaActual);
    }
    
    $('#modal-crear-jornada').modal('show');
}

/**
 * Crear jornada
 */
function crearJornada() {
    const formData = {
        action: 'crear',
        temporada_id: $('#crear_temporada_id').val() || temporadaActual,
        jornada_numero: $('#jornada_numero').val(),
        jornada_fecha: $('#jornada_fecha').val(),
        jornada_estatus: $('#jornada_estatus').val()
    };
    
    // Validaciones
    if (!formData.temporada_id) {
        Swal.fire('Error', 'Selecciona una temporada', 'warning');
        return;
    }
    if (!formData.jornada_numero) {
        Swal.fire('Error', 'Ingresa el número de jornada', 'warning');
        return;
    }
    if (!formData.jornada_fecha) {
        Swal.fire('Error', 'Selecciona la fecha', 'warning');
        return;
    }
    
    $.ajax({
        url: '../controller/jornada_controller.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                $('#modal-crear-jornada').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: response.message,
                    timer: 2000
                });
                tablaJornadas.ajax.reload();
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
 * Editar jornada
 */
function editar(jornada_id) {
    $.ajax({
        url: '../controller/jornada_controller.php',
        type: 'POST',
        data: {
            action: 'obtener',
            jornada_id: jornada_id
        },
        dataType: 'json',
        success: function(data) {
            if (data) {
                $('#edit_jornada_id').val(data.jornada_id);
                $('#edit_jornada_fecha').val(data.jornada_fecha);
                $('#edit_jornada_estatus').val(data.jornada_estatus);
                $('#modal-editar-jornada').modal('show');
            }
        }
    });
}

/**
 * Actualizar jornada
 */
function actualizarJornada() {
    const formData = {
        action: 'actualizar',
        jornada_id: $('#edit_jornada_id').val(),
        jornada_fecha: $('#edit_jornada_fecha').val(),
        jornada_estatus: $('#edit_jornada_estatus').val()
    };
    
    $.ajax({
        url: '../controller/jornada_controller.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                $('#modal-editar-jornada').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: response.message,
                    timer: 2000
                });
                tablaJornadas.ajax.reload();
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
 * Ver partidos de la jornada
 */
function verPartidos(jornada_id) {
    window.location.href = `partidos.php?jornada_id=${jornada_id}`;
}

/**
 * Ver estadísticas de la jornada
 */
function verEstadisticas(jornada_id) {
    $.ajax({
        url: '../controller/jornada_controller.php',
        type: 'POST',
        data: {
            action: 'estadisticas',
            jornada_id: jornada_id
        },
        dataType: 'json',
        success: function(data) {
            const html = `
                <div class="row text-center">
                    <div class="col-md-6">
                        <h3>${data.total_partidos || 0}</h3>
                        <p>Partidos Programados</p>
                    </div>
                    <div class="col-md-6">
                        <h3 class="text-success">${data.partidos_finalizados || 0}</h3>
                        <p>Partidos Finalizados</p>
                    </div>
                    <div class="col-md-4">
                        <h4>${data.total_goles || 0}</h4>
                        <small>Total Goles</small>
                    </div>
                    <div class="col-md-4">
                        <h4 class="text-warning">${data.tarjetas_amarillas || 0}</h4>
                        <small>Amarillas</small>
                    </div>
                    <div class="col-md-4">
                        <h4 class="text-danger">${data.tarjetas_rojas || 0}</h4>
                        <small>Rojas</small>
                    </div>
                </div>
            `;
            
            Swal.fire({
                title: 'Estadísticas de la Jornada',
                html: html,
                width: 600
            });
        }
    });
}

/**
 * Generar jornadas automáticamente
 */
function generarJornadas() {
    if (!temporadaActual) {
        Swal.fire('Error', 'Selecciona una temporada primero', 'warning');
        return;
    }
    
    Swal.fire({
        title: '¿Generar jornadas?',
        text: 'Se crearán 8 jornadas automáticamente',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, generar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '../controller/jornada_controller.php',
                type: 'POST',
                data: {
                    action: 'generar_jornadas',
                    temporada_id: temporadaActual,
                    num_jornadas: 8
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Éxito',
                            text: response.message,
                            timer: 2000
                        });
                        tablaJornadas.ajax.reload();
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
 * Eliminar jornada
 */
function eliminar(jornada_id) {
    Swal.fire({
        title: '¿Eliminar jornada?',
        text: 'Solo se puede eliminar si no tiene partidos registrados',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#d33'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '../controller/jornada_controller.php',
                type: 'POST',
                data: {
                    action: 'eliminar',
                    jornada_id: jornada_id
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
                        tablaJornadas.ajax.reload();
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