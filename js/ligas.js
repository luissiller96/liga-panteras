/**
 * JavaScript: Ligas
 * Descripción: Gestión de ligas/categorías
 */

let tablaLigas;

$(document).ready(function() {
    // Inicializar DataTable
    tablaLigas = $('#tabla-ligas').DataTable({
        ajax: {
            url: '../controller/liga_controller.php',
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
            { data: 7 }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-MX.json'
        },
        responsive: true,
        order: [[0, 'desc']]
    });
    
    // Submit formulario crear
    $('#form-crear-liga').on('submit', function(e) {
        e.preventDefault();
        crearLiga();
    });
    
    // Submit formulario editar
    $('#form-editar-liga').on('submit', function(e) {
        e.preventDefault();
        actualizarLiga();
    });
});

/**
 * Abrir modal para crear liga
 */
function abrirModalCrear() {
    $('#form-crear-liga')[0].reset();
    $('#modal-crear-liga').modal('show');
}

/**
 * Crear liga
 */
function crearLiga() {
    const formData = new FormData($('#form-crear-liga')[0]);
    formData.append('action', 'crear');
    
    $.ajax({
        url: '../controller/liga_controller.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                $('#modal-crear-liga').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: response.message,
                    timer: 2000
                });
                tablaLigas.ajax.reload();
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
 * Editar liga
 */
function editar(liga_id) {
    $.ajax({
        url: '../controller/liga_controller.php',
        type: 'POST',
        data: {
            action: 'obtener',
            liga_id: liga_id
        },
        dataType: 'json',
        success: function(data) {
            if (data) {
                $('#edit_liga_id').val(data.liga_id);
                $('#edit_liga_nombre').val(data.liga_nombre);
                $('#edit_liga_descripcion').val(data.liga_descripcion);
                $('#edit_liga_dia_juego').val(data.liga_dia_juego);
                $('#modal-editar-liga').modal('show');
            }
        }
    });
}

/**
 * Actualizar liga
 */
function actualizarLiga() {
    const formData = new FormData($('#form-editar-liga')[0]);
    formData.append('action', 'actualizar');
    
    $.ajax({
        url: '../controller/liga_controller.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                $('#modal-editar-liga').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: response.message,
                    timer: 2000
                });
                tablaLigas.ajax.reload();
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
 * Eliminar liga
 */
function eliminar(liga_id) {
    Swal.fire({
        title: '¿Eliminar liga?',
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#d33'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '../controller/liga_controller.php',
                type: 'POST',
                data: {
                    action: 'eliminar',
                    liga_id: liga_id
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
                        tablaLigas.ajax.reload();
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
 * Cargar ligas en select
 */
function cargarLigasSelect(selectId, callback) {
    $.ajax({
        url: '../controller/liga_controller.php',
        type: 'POST',
        data: { action: 'listar_select' },
        dataType: 'json',
        success: function(data) {
            const select = $(selectId);
            select.empty();
            select.append('<option value="">Seleccionar...</option>');
            
            data.forEach(function(liga) {
                select.append(`<option value="${liga.liga_id}">${liga.liga_nombre}</option>`);
            });
            
            if (callback) callback();
        }
    });
}