/**
 * EQUIPO.JS - Funcionalidad para módulo de Equipos
 * Liga Panteras
 */

$(document).ready(function() {
    // Variables globales
    let tabla;

    // Cargar ligas en los selectores
    cargarLigas();

    // Inicializar DataTable
    tabla = $('#tablaEquipos').DataTable({
        ajax: {
            url: '../controllers/EquipoController.php?action=listar',
            dataSrc: 'data'
        },
        columns: [
            { data: 'id' },
            { 
                data: 'logo',
                orderable: false,
                render: function(data, type, row) {
                    if (data && data !== '') {
                        return `<img src="${data}" class="equipo-logo" alt="${row.nombre}">`;
                    } else {
                        return `<div class="sin-logo"><i class="fas fa-shield-alt"></i></div>`;
                    }
                }
            },
            { 
                data: 'nombre',
                render: function(data, type, row) {
                    return `<span class="equipo-nombre">${data}</span>`;
                }
            },
            { 
                data: 'liga_nombre',
                render: function(data, type, row) {
                    return `<span class="badge badge-liga" style="background-color: ${row.liga_color}; color: white;">${data}</span>`;
                }
            },
            { 
                data: 'representante',
                render: function(data, type, row) {
                    return `
                        <div class="representante-info">
                            <span class="representante-nombre">${data}</span>
                            ${row.telefono ? `<span class="representante-contacto"><i class="fas fa-phone"></i> ${row.telefono}</span>` : ''}
                        </div>
                    `;
                }
            },
            { data: 'telefono' },
            { 
                data: 'estado_pago',
                render: function(data) {
                    if (data === 'pagado') {
                        return '<span class="badge badge-pago-pagado"><i class="fas fa-check-circle"></i> Pagado</span>';
                    } else {
                        return '<span class="badge badge-pago-pendiente"><i class="fas fa-clock"></i> Pendiente</span>';
                    }
                }
            },
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
                        <button class="btn btn-info btn-action btn-ver" data-id="${data.id}" title="Ver Detalle">
                            <i class="fas fa-eye"></i>
                        </button>
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
        columnDefs: [
            { targets: 5, visible: false } // Ocultar columna de teléfono (ya se muestra en representante)
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

    // Filtros
    $('#filtroLiga, #filtroPago, #filtroEstado').on('change', function() {
        aplicarFiltros();
    });

    // Sincronizar color pickers
    $('#color_principal').on('change', function() {
        $('#color_principal_hex').val($(this).val());
    });

    $('#color_principal_hex').on('input', function() {
        let color = $(this).val();
        if (/^#[0-9A-F]{6}$/i.test(color)) {
            $('#color_principal').val(color);
        }
    });

    $('#color_secundario').on('change', function() {
        $('#color_secundario_hex').val($(this).val());
    });

    $('#color_secundario_hex').on('input', function() {
        let color = $(this).val();
        if (/^#[0-9A-F]{6}$/i.test(color)) {
            $('#color_secundario').val(color);
        }
    });

    // Preview de logo
    $('#logo').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validar tamaño (2MB max)
            if (file.size > 2097152) {
                Swal.fire({
                    icon: 'error',
                    title: 'Archivo muy grande',
                    text: 'El logo no debe superar los 2MB'
                });
                $(this).val('');
                $('#previewLogo').html('');
                return;
            }

            // Validar tipo
            if (!file.type.match('image.*')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Formato inválido',
                    text: 'Por favor selecciona una imagen válida'
                });
                $(this).val('');
                $('#previewLogo').html('');
                return;
            }

            // Mostrar preview
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#previewLogo').html(`<img src="${e.target.result}" alt="Preview">`);
            };
            reader.readAsDataURL(file);
        } else {
            $('#previewLogo').html('');
        }
    });

    // Limpiar formulario al abrir modal
    $('#modalEquipo').on('show.bs.modal', function() {
        if (!$(this).hasClass('editar')) {
            $('#formEquipo')[0].reset();
            $('#equipo_id').val('');
            $('#logo_actual').val('');
            $('#previewLogo').html('');
            $('#modalEquipoTitle').html('<i class="fas fa-shield-alt"></i> Nuevo Equipo');
            $('#color_principal').val('#000000');
            $('#color_principal_hex').val('#000000');
            $('#color_secundario').val('#FFFFFF');
            $('#color_secundario_hex').val('#FFFFFF');
        }
    });

    // Guardar/Actualizar Equipo
    $('#formEquipo').on('submit', function(e) {
        e.preventDefault();
        
        let formData = new FormData(this);
        let action = $('#equipo_id').val() ? 'actualizar' : 'crear';
        formData.append('action', action);

        // Deshabilitar botón
        $('#btnGuardar').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

        $.ajax({
            url: '../controllers/EquipoController.php',
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
                    $('#modalEquipo').modal('hide');
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

    // Editar Equipo
    $(document).on('click', '.btn-editar', function() {
        let id = $(this).data('id');
        
        $.ajax({
            url: '../controllers/EquipoController.php?action=obtener&id=' + id,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    let data = response.data;
                    
                    // Llenar formulario
                    $('#equipo_id').val(data.id);
                    $('#liga_id').val(data.liga_id);
                    $('#nombre').val(data.nombre);
                    $('#color_principal').val(data.color_principal);
                    $('#color_principal_hex').val(data.color_principal);
                    $('#color_secundario').val(data.color_secundario);
                    $('#color_secundario_hex').val(data.color_secundario);
                    $('#representante').val(data.representante);
                    $('#telefono').val(data.telefono);
                    $('#email').val(data.email);
                    $('#monto_inscripcion').val(data.monto_inscripcion);
                    $('#estado_pago').val(data.estado_pago);
                    $('#estado').val(data.estado);
                    $('#notas').val(data.notas);
                    $('#logo_actual').val(data.logo);
                    
                    // Mostrar logo actual si existe
                    if (data.logo) {
                        $('#previewLogo').html(`<img src="${data.logo}" alt="Logo actual">`);
                    }
                    
                    // Cambiar título modal
                    $('#modalEquipoTitle').html('<i class="fas fa-edit"></i> Editar Equipo');
                    
                    // Abrir modal
                    $('#modalEquipo').addClass('editar').modal('show');
                    
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
                    text: 'No se pudo cargar la información del equipo'
                });
                console.error('Error:', xhr);
            }
        });
    });

    // Eliminar Equipo
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
                    url: '../controllers/EquipoController.php',
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
                            text: 'Ocurrió un error al eliminar el equipo'
                        });
                        console.error('Error:', xhr);
                    }
                });
            }
        });
    });

    // Ver Detalle Equipo (placeholder para futura implementación)
    $(document).on('click', '.btn-ver', function() {
        let id = $(this).data('id');
        Swal.fire({
            icon: 'info',
            title: 'Vista de Detalle',
            text: 'Esta funcionalidad se implementará próximamente'
        });
    });

    // Remover clase editar al cerrar modal
    $('#modalEquipo').on('hidden.bs.modal', function() {
        $(this).removeClass('editar');
    });

    /**
     * Cargar ligas en los selectores
     */
    function cargarLigas() {
        $.ajax({
            url: '../controllers/LigaController.php?action=listar_activas',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Cargar en modal
                    let options = '<option value="">Seleccione una liga</option>';
                    response.data.forEach(function(liga) {
                        options += `<option value="${liga.id}">${liga.nombre}</option>`;
                    });
                    $('#liga_id').html(options);

                    // Cargar en filtro
                    let filtroOptions = '<option value="">Todas las ligas</option>';
                    response.data.forEach(function(liga) {
                        filtroOptions += `<option value="${liga.id}">${liga.nombre}</option>`;
                    });
                    $('#filtroLiga').html(filtroOptions);
                }
            },
            error: function(xhr) {
                console.error('Error al cargar ligas:', xhr);
            }
        });
    }

    /**
     * Aplicar filtros a la tabla
     */
    function aplicarFiltros() {
        let ligaId = $('#filtroLiga').val();
        let estadoPago = $('#filtroPago').val();
        let estado = $('#filtroEstado').val();

        let params = '?action=listar';
        if (ligaId) params += '&liga_id=' + ligaId;
        if (estadoPago) params += '&estado_pago=' + estadoPago;
        if (estado) params += '&estado=' + estado;

        tabla.ajax.url('../controllers/EquipoController.php' + params).load();
    }
});

// Función para recargar tabla (si se necesita desde fuera)
function recargarTablaEquipos() {
    $('#tablaEquipos').DataTable().ajax.reload();
}