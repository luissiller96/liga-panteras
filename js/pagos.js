/**
 * JavaScript: Pagos y Abonos
 * Descripción: Control de pagos de inscripción y registro de abonos
 */

let tablaPagos;
let tablaAbonos;

$(document).ready(function() {
    // Inicializar DataTable de pagos
    if ($('#tabla-pagos').length) {
        tablaPagos = $('#tabla-pagos').DataTable({
            ajax: {
                url: '../controller/pago_controller.php',
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
                { data: 8 },
                { data: 9 },
                { data: 10 },
                { data: 11 }
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-MX.json'
            },
            responsive: true,
            order: [[9, 'asc']]
        });
    }
    
    // Inicializar DataTable de abonos
    if ($('#tabla-abonos').length) {
        tablaAbonos = $('#tabla-abonos').DataTable({
            ajax: {
                url: '../controller/abono_controller.php',
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
            order: [[4, 'desc']]
        });
    }
    
    // Submit formulario registrar abono
    $('#form-registrar-abono').on('submit', function(e) {
        e.preventDefault();
        registrarAbono();
    });
});

/**
 * Registrar abono
 */
function registrarAbono(pago_id = null) {
    if (pago_id) {
        // Si viene desde el botón de la tabla
        $('#pago_id_abono').val(pago_id);
        
        // Obtener información del pago
        $.ajax({
            url: '../controller/pago_controller.php',
            type: 'POST',
            data: {
                action: 'obtener',
                pago_id: pago_id
            },
            dataType: 'json',
            success: function(data) {
                if (data) {
                    $('#info-pago').html(`
                        <div class="alert alert-info">
                            <strong>Equipo:</strong> ${data.equipo_nombre}<br>
                            <strong>Temporada:</strong> ${data.temporada_nombre}<br>
                            <strong>Monto Total:</strong> $${parseFloat(data.monto_total).toFixed(2)}<br>
                            <strong>Pagado:</strong> $${parseFloat(data.monto_pagado).toFixed(2)}<br>
                            <strong>Pendiente:</strong> <span class="text-danger">$${parseFloat(data.monto_pendiente).toFixed(2)}</span>
                        </div>
                    `);
                    
                    // Establecer máximo del input
                    $('#monto_abono').attr('max', data.monto_pendiente);
                }
            }
        });
    }
    
    $('#form-registrar-abono')[0].reset();
    $('#modal-registrar-abono').modal('show');
}

/**
 * Guardar abono
 */
function guardarAbono() {
    const formData = {
        action: 'registrar',
        pago_id: $('#pago_id_abono').val(),
        monto_abono: $('#monto_abono').val(),
        fecha_abono: $('#fecha_abono').val() || new Date().toISOString().slice(0, 16),
        metodo_pago: $('#metodo_pago').val(),
        referencia_pago: $('#referencia_pago').val(),
        comentarios: $('#comentarios_abono').val()
    };
    
    // Validaciones
    if (!formData.monto_abono || parseFloat(formData.monto_abono) <= 0) {
        Swal.fire('Error', 'Ingresa un monto válido', 'warning');
        return;
    }
    
    $.ajax({
        url: '../controller/abono_controller.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                $('#modal-registrar-abono').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: response.message,
                    timer: 2000
                });
                if (tablaPagos) tablaPagos.ajax.reload();
                if (tablaAbonos) tablaAbonos.ajax.reload();
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
 * Ver historial de abonos
 */
function verHistorial(pago_id) {
    $.ajax({
        url: '../controller/pago_controller.php',
        type: 'POST',
        data: {
            action: 'historial_abonos',
            pago_id: pago_id
        },
        dataType: 'json',
        success: function(data) {
            let html = '<div class="table-responsive"><table class="table table-sm">';
            html += '<thead><tr><th>Fecha</th><th>Monto</th><th>Método</th><th>Registrado por</th></tr></thead><tbody>';
            
            if (data && data.length > 0) {
                data.forEach(abono => {
                    const fecha = new Date(abono.fecha_abono).toLocaleString('es-MX');
                    const monto = parseFloat(abono.monto_abono).toFixed(2);
                    const metodo = abono.metodo_pago.charAt(0).toUpperCase() + abono.metodo_pago.slice(1);
                    const usuario = abono.usu_nom || 'Sistema';
                    
                    html += `<tr>
                        <td>${fecha}</td>
                        <td class="text-success fw-bold">$${monto}</td>
                        <td>${metodo}</td>
                        <td>${usuario}</td>
                    </tr>`;
                });
            } else {
                html += '<tr><td colspan="4" class="text-center text-muted">No hay abonos registrados</td></tr>';
            }
            
            html += '</tbody></table></div>';
            
            Swal.fire({
                title: 'Historial de Abonos',
                html: html,
                width: 700
            });
        }
    });
}

/**
 * Editar fecha límite de pago
 */
function editarFechaLimite(pago_id) {
    Swal.fire({
        title: 'Actualizar Fecha Límite',
        html: '<input type="date" id="nueva_fecha_limite" class="form-control">',
        showCancelButton: true,
        confirmButtonText: 'Guardar',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            const fecha = $('#nueva_fecha_limite').val();
            if (!fecha) {
                Swal.showValidationMessage('Selecciona una fecha');
                return false;
            }
            return fecha;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '../controller/pago_controller.php',
                type: 'POST',
                data: {
                    action: 'actualizar_fecha_limite',
                    pago_id: pago_id,
                    fecha_limite_pago: result.value
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Actualizado',
                            text: response.message,
                            timer: 2000
                        });
                        tablaPagos.ajax.reload();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                }
            });
        }
    });
}

/**
 * Cargar estadísticas de pagos
 */
function cargarEstadisticasPagos(temporada_id) {
    $.ajax({
        url: '../controller/pago_controller.php',
        type: 'POST',
        data: {
            action: 'estadisticas',
            temporada_id: temporada_id
        },
        dataType: 'json',
        success: function(data) {
            $('#total_equipos').text(data.total_equipos || 0);
            $('#ingresos_esperados').text('$' + parseFloat(data.ingresos_esperados || 0).toFixed(2));
            $('#ingresos_reales').text('$' + parseFloat(data.ingresos_reales || 0).toFixed(2));
            $('#monto_pendiente').text('$' + parseFloat(data.monto_pendiente || 0).toFixed(2));
            $('#equipos_liquidados').text(data.equipos_liquidados || 0);
            $('#equipos_pendientes').text(data.equipos_pendientes || 0);
        }
    });
}