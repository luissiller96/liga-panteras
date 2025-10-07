/**
 * LIGA.JS - Funcionalidad para módulo de Ligas
 * Liga Panteras
 */

$(document).ready(function() {
    // Inicializar DataTable
    let tabla = $('#tablaLigas').DataTable({
        ajax: {
            url: '../controllers/LigaController.php?action=listar',
            dataSrc: 'data'
        },
        columns: [
            { data: 'id' },
            { data: 'nombre' },
            { data: 'descripcion' },
            { 
                data: 'color',
                render: function(data) {
                    return `<div class="badge-color" style="background-color: ${data}"></div>`;
                }
            },
            { data: 'temporada_actual' },
            { 
                data: 'estado',
                render: function(data) {
                    if (data === 'activo') {
                        return '<span class="badge bg-success">Activo</span>';
                    } else {
                        return '<span class="badge bg-danger">Inactivo</span>';
                    }
                }
            },
            {
                data: null,
                orderable: false,
                render: function(data) {
                    return `
                        <button class="btn btn-warning btn-action btn-editar" data-id="${data.id}" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-danger btn-action btn-eliminar" data-id="${data.id}" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    `;
                }
            }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
        },
        responsive: true,
        pageLength: 10,
        order: [[0, 'desc']]
    });

    // Sidebar Toggle
    $('#sidebarToggle').on('click', function() {
        $('.wrapper').toggleClass('sidebar-collapsed');
    });

    // Sincronizar color picker con input text
    $('#color').on('change', function() {
        $('#color_hex').val($(this).val());
    });

    $('#color_hex').on('input', function() {
        let color = $(this).val();
        if (/^#[0-9A-F]{6}$/i.test(color)) {
            $('#color').val(color);
        }
    });

    // Limpiar formulario al abrir modal
    $('#modalLiga').on('show.bs.modal', function() {
        if (!$(this).hasClass('editar')) {
            $('#formLiga')[0].reset();
            $('#liga_id').val('');
            $('#modalLigaTitle').html('<i class="fas fa-trophy"></i> Nueva Liga');
            $('#color').val('#1a73e8');
            $('#color_hex').val('#1a73e8');
        }
    });

    // Guardar/Actualizar Liga
    $('#formLiga').on('submit', function(e) {
        e.preventDefault();
        
        let formData = new FormData(this);
        let action = $('#liga_id').val() ? 'actualizar' : 'crear';
        formData.append('action', action);

        // Deshabilitar botón
        $('#btnGuardar').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

        $.ajax({
            url: '../controllers/LigaController.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                $('#btnGuardar').prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');
                
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    $('#modalLiga').modal('hide');
                    tabla.ajax.reload();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });
                }
            },
            error: function(xhr) {
                $('#btnGuardar').prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un error al procesar la solicitud'
                });
                console.error('Error:', xhr);
            }
        });
    });

    // Editar Liga
    $(document).on('click', '.btn-editar', function() {
        let id = $(this).data('id');
        
        $.ajax({
            url: '../controllers/LigaController.php?action=obtener&id=' + id,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    let data = response.data;
                    
                    // Llenar formulario
                    $('#liga_id').val(data.id);
                    $('#nombre').val(data.nombre);
                    $('#descripcion').val(data.descripcion);
                    $('#color').val(data.color);
                    $('#color_hex').val(data.color);
                    $('#temporada_actual').val(data.temporada_actual);
                    $('#numero_equipos').val(data.numero_equipos);
                    $('#jornadas_totales').val(data.jornadas_totales);
                    $('#equipos_clasifican').val(data.equipos_clasifican);
                    $('#estado').val(data.estado);
                    
                    // Cambiar título modal
                    $('#modalLigaTitle').html('<i class="fas fa-edit"></i> Editar Liga');
                    
                    // Abrir modal
                    $('#modalLiga').addClass('editar').modal('show');
                    
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo cargar la información de la liga'
                });
                console.error('Error:', xhr);
            }
        });
    });

    // Eliminar Liga
    $(document).on('click', '.btn-eliminar', function() {
        let id = $(this).data('id');
        
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción no se puede revertir",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '../controllers/LigaController.php',
                    type: 'POST',
                    data: {
                        action: 'eliminar',
                        id: id
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Eliminado!',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                            tabla.ajax.reload();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Ocurrió un error al eliminar la liga'
                        });
                        console.error('Error:', xhr);
                    }
                });
            }
        });
    });

    // Remover clase editar al cerrar modal
    $('#modalLiga').on('hidden.bs.modal', function() {
        $(this).removeClass('editar');
    });
});

// Función para recargar tabla (si se necesita desde fuera)
function recargarTablaLigas() {
    $('#tablaLigas').DataTable().ajax.reload();
}