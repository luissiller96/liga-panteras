/**
 * JavaScript: Partidos
 * Descripción: Gestión de partidos, captura de resultados, goles y tarjetas
 */

let tablaPartidos;
let partidoActual = null;
let golesRegistrados = [];
let tarjetasRegistradas = [];

$(document).ready(function() {
    // Inicializar DataTable
    tablaPartidos = $('#tabla-partidos').DataTable({
        ajax: {
            url: '../controller/partido_controller.php',
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
        order: [[3, 'desc']]
    });
    
    // Submit formulario crear partido
    $('#form-crear-partido').on('submit', function(e) {
        e.preventDefault();
        crearPartido();
    });
    
    // Submit captura de resultado
    $('#form-capturar-resultado').on('submit', function(e) {
        e.preventDefault();
        guardarResultado();
    });
});

/**
 * Abrir modal para crear partido
 */
function abrirModalCrear() {
    $('#form-crear-partido')[0].reset();
    $('#modal-crear-partido').modal('show');
}

/**
 * Crear partido
 */
function crearPartido() {
    const formData = {
        action: 'crear',
        jornada_id: $('#jornada_id').val() || null,
        temporada_id: $('#crear_temporada_id').val(),
        tipo_partido: $('#tipo_partido').val(),
        equipo_local_id: $('#equipo_local_id').val(),
        equipo_visitante_id: $('#equipo_visitante_id').val(),
        fecha_partido: $('#fecha_partido').val(),
        hora_partido: $('#hora_partido').val(),
        duracion_partido: $('#duracion_partido').val() || '20 min x 2 tiempos'
    };
    
    // Validaciones
    if (!formData.temporada_id) {
        Swal.fire('Error', 'Selecciona una temporada', 'warning');
        return;
    }
    if (!formData.equipo_local_id || !formData.equipo_visitante_id) {
        Swal.fire('Error', 'Selecciona ambos equipos', 'warning');
        return;
    }
    if (formData.equipo_local_id === formData.equipo_visitante_id) {
        Swal.fire('Error', 'Un equipo no puede jugar contra sí mismo', 'warning');
        return;
    }
    
    $.ajax({
        url: '../controller/partido_controller.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                $('#modal-crear-partido').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: response.message,
                    timer: 2000
                });
                tablaPartidos.ajax.reload();
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
 * Capturar resultado del partido
 */
function capturarResultado(partido_id) {
    partidoActual = partido_id;
    golesRegistrados = [];
    tarjetasRegistradas = [];
    
    // Obtener información del partido
    $.ajax({
        url: '../controller/partido_controller.php',
        type: 'POST',
        data: {
            action: 'obtener',
            partido_id: partido_id
        },
        dataType: 'json',
        success: function(data) {
            if (data) {
                // Llenar información del partido
                $('#info-partido').html(`
                    <h5 class="text-center">
                        <strong>${data.equipo_local}</strong> vs <strong>${data.equipo_visitante}</strong>
                    </h5>
                    <p class="text-center text-muted">
                        ${data.liga_nombre} - ${data.temporada_nombre}<br>
                        ${new Date(data.fecha_partido).toLocaleDateString('es-MX')} - ${data.hora_partido}
                    </p>
                `);
                
                // Cargar jugadores de ambos equipos
                cargarJugadoresSelect('#gol_jugador_id', data.equipo_local_id);
                cargarJugadoresSelect('#tarjeta_jugador_id', data.equipo_local_id);
                
                // Guardar IDs de equipos
                $('#equipo_local_id_resultado').val(data.equipo_local_id);
                $('#equipo_visitante_id_resultado').val(data.equipo_visitante_id);
                
                // Configurar selector de equipo para goles y tarjetas
                $('#gol_equipo_id').html(`
                    <option value="${data.equipo_local_id}">${data.equipo_local}</option>
                    <option value="${data.equipo_visitante_id}">${data.equipo_visitante}</option>
                `);
                
                $('#tarjeta_equipo_id').html(`
                    <option value="${data.equipo_local_id}">${data.equipo_local}</option>
                    <option value="${data.equipo_visitante_id}">${data.equipo_visitante}</option>
                `);
                
                // Cargar goles y tarjetas existentes
                if (data.goles && data.goles.length > 0) {
                    golesRegistrados = data.goles;
                    actualizarListaGoles();
                }
                
                if (data.tarjetas && data.tarjetas.length > 0) {
                    tarjetasRegistradas = data.tarjetas;
                    actualizarListaTarjetas();
                }
                
                // Calcular marcador
                calcularMarcador();
                
                $('#modal-capturar-resultado').modal('show');
            }
        }
    });
}

/**
 * Cuando cambia el equipo en gol, cargar sus jugadores
 */
$(document).on('change', '#gol_equipo_id', function() {
    const equipoId = $(this).val();
    cargarJugadoresSelect('#gol_jugador_id', equipoId);
});

/**
 * Cuando cambia el equipo en tarjeta, cargar sus jugadores
 */
$(document).on('change', '#tarjeta_equipo_id', function() {
    const equipoId = $(this).val();
    cargarJugadoresSelect('#tarjeta_jugador_id', equipoId);
});

/**
 * Registrar gol
 */
function registrarGol() {
    const equipoId = $('#gol_equipo_id').val();
    const jugadorId = $('#gol_jugador_id').val();
    const tipoGol = $('#tipo_gol').val();
    
    if (!jugadorId) {
        Swal.fire('Error', 'Selecciona un jugador', 'warning');
        return;
    }
    
    const jugadorNombre = $('#gol_jugador_id option:selected').text();
    const equipoNombre = $('#gol_equipo_id option:selected').text();
    
    golesRegistrados.push({
        equipo_id: equipoId,
        jugador_id: jugadorId,
        jugador_nombre: jugadorNombre,
        equipo_nombre: equipoNombre,
        tipo_gol: tipoGol
    });
    
    actualizarListaGoles();
    calcularMarcador();
    
    // Limpiar formulario
    $('#gol_jugador_id').val('');
    $('#tipo_gol').val('normal');
}

/**
 * Actualizar lista de goles
 */
function actualizarListaGoles() {
    let html = '';
    golesRegistrados.forEach((gol, index) => {
        const badge = gol.tipo_gol === 'autogol' ? '<span class="badge bg-danger">Autogol</span>' : '';
        html += `
            <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                <div>
                    <strong>${gol.jugador_nombre}</strong> ${badge}<br>
                    <small class="text-muted">${gol.equipo_nombre}</small>
                </div>
                <button class="btn btn-sm btn-danger" onclick="eliminarGol(${index})">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
    });
    
    $('#lista-goles').html(html || '<p class="text-muted">No hay goles registrados</p>');
}

/**
 * Eliminar gol
 */
function eliminarGol(index) {
    golesRegistrados.splice(index, 1);
    actualizarListaGoles();
    calcularMarcador();
}

/**
 * Registrar tarjeta
 */
function registrarTarjeta() {
    const equipoId = $('#tarjeta_equipo_id').val();
    const jugadorId = $('#tarjeta_jugador_id').val();
    const tipoTarjeta = $('#tipo_tarjeta').val();
    const motivo = $('#motivo_tarjeta').val();
    
    if (!jugadorId) {
        Swal.fire('Error', 'Selecciona un jugador', 'warning');
        return;
    }
    
    const jugadorNombre = $('#tarjeta_jugador_id option:selected').text();
    const equipoNombre = $('#tarjeta_equipo_id option:selected').text();
    
    tarjetasRegistradas.push({
        equipo_id: equipoId,
        jugador_id: jugadorId,
        jugador_nombre: jugadorNombre,
        equipo_nombre: equipoNombre,
        tipo_tarjeta: tipoTarjeta,
        motivo: motivo
    });
    
    actualizarListaTarjetas();
    
    // Limpiar formulario
    $('#tarjeta_jugador_id').val('');
    $('#motivo_tarjeta').val('');
}

/**
 * Actualizar lista de tarjetas
 */
function actualizarListaTarjetas() {
    let html = '';
    tarjetasRegistradas.forEach((tarjeta, index) => {
        const badge = tarjeta.tipo_tarjeta === 'amarilla' ? 
            '<span class="badge bg-warning text-dark">Amarilla</span>' : 
            '<span class="badge bg-danger">Roja</span>';
        html += `
            <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                <div>
                    <strong>${tarjeta.jugador_nombre}</strong> ${badge}<br>
                    <small class="text-muted">${tarjeta.equipo_nombre}</small>
                    ${tarjeta.motivo ? `<br><small>${tarjeta.motivo}</small>` : ''}
                </div>
                <button class="btn btn-sm btn-danger" onclick="eliminarTarjeta(${index})">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
    });
    
    $('#lista-tarjetas').html(html || '<p class="text-muted">No hay tarjetas registradas</p>');
}

/**
 * Eliminar tarjeta
 */
function eliminarTarjeta(index) {
    tarjetasRegistradas.splice(index, 1);
    actualizarListaTarjetas();
}

/**
 * Calcular marcador
 */
function calcularMarcador() {
    const equipoLocalId = parseInt($('#equipo_local_id_resultado').val());
    const equipoVisitanteId = parseInt($('#equipo_visitante_id_resultado').val());
    
    let golesLocal = 0;
    let golesVisitante = 0;
    
    golesRegistrados.forEach(gol => {
        const equipoGol = parseInt(gol.equipo_id);
        
        if (gol.tipo_gol === 'normal') {
            if (equipoGol === equipoLocalId) {
                golesLocal++;
            } else if (equipoGol === equipoVisitanteId) {
                golesVisitante++;
            }
        } else { // autogol
            if (equipoGol === equipoLocalId) {
                golesVisitante++; // autogol del local suma al visitante
            } else if (equipoGol === equipoVisitanteId) {
                golesLocal++; // autogol del visitante suma al local
            }
        }
    });
    
    $('#goles_local').val(golesLocal);
    $('#goles_visitante').val(golesVisitante);
    
    $('#marcador-preview').html(`<h3 class="text-center">${golesLocal} - ${golesVisitante}</h3>`);
}

/**
 * Guardar resultado completo
 */
function guardarResultado() {
    const golesLocal = $('#goles_local').val();
    const golesVisitante = $('#goles_visitante').val();
    const observaciones = $('#observaciones_partido').val();
    
    Swal.fire({
        title: '¿Guardar resultado?',
        text: 'Se actualizará la tabla de posiciones',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, guardar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Primero actualizar el resultado del partido
            $.ajax({
                url: '../controller/partido_controller.php',
                type: 'POST',
                data: {
                    action: 'actualizar_resultado',
                    partido_id: partidoActual,
                    goles_local: golesLocal,
                    goles_visitante: golesVisitante,
                    observaciones: observaciones
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        // Registrar goles individuales
                        registrarGolesIndividuales().then(() => {
                            // Registrar tarjetas
                            registrarTarjetasIndividuales().then(() => {
                                $('#modal-capturar-resultado').modal('hide');
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Resultado guardado',
                                    text: 'La tabla de posiciones se ha actualizado',
                                    timer: 2000
                                });
                                tablaPartidos.ajax.reload();
                            });
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                }
            });
        }
    });
}

/**
 * Registrar goles individuales
 */
function registrarGolesIndividuales() {
    const promises = golesRegistrados.map(gol => {
        return $.ajax({
            url: '../controller/gol_controller.php',
            type: 'POST',
            data: {
                action: 'registrar',
                partido_id: partidoActual,
                jugador_id: gol.jugador_id,
                equipo_id: gol.equipo_id,
                tipo_gol: gol.tipo_gol
            },
            dataType: 'json'
        });
    });
    
    return Promise.all(promises);
}

/**
 * Registrar tarjetas individuales
 */
function registrarTarjetasIndividuales() {
    const promises = tarjetasRegistradas.map(tarjeta => {
        return $.ajax({
            url: '../controller/tarjeta_controller.php',
            type: 'POST',
            data: {
                action: 'registrar',
                partido_id: partidoActual,
                jugador_id: tarjeta.jugador_id,
                equipo_id: tarjeta.equipo_id,
                tipo_tarjeta: tarjeta.tipo_tarjeta,
                motivo: tarjeta.motivo
            },
            dataType: 'json'
        });
    });
    
    return Promise.all(promises);
}

/**
 * Ver detalles del partido
 */
function verDetalles(partido_id) {
    $.ajax({
        url: '../controller/partido_controller.php',
        type: 'POST',
        data: {
            action: 'obtener',
            partido_id: partido_id
        },
        dataType: 'json',
        success: function(data) {
            let html = `
                <h4 class="text-center mb-3">
                    <strong>${data.equipo_local}</strong> 
                    <span class="badge bg-dark fs-4">${data.goles_local} - ${data.goles_visitante}</span>
                    <strong>${data.equipo_visitante}</strong>
                </h4>
                <p class="text-center text-muted">
                    ${data.liga_nombre} - ${data.temporada_nombre}<br>
                    ${new Date(data.fecha_partido).toLocaleDateString('es-MX')} - ${data.hora_partido}
                </p>
            `;
            
            // Goles
            if (data.goles && data.goles.length > 0) {
                html += '<h5>Goleadores:</h5><ul class="list-group mb-3">';
                data.goles.forEach(gol => {
                    const badge = gol.tipo_gol === 'autogol' ? '<span class="badge bg-danger ms-2">Autogol</span>' : '';
                    html += `<li class="list-group-item">${gol.jugador_nombre} (${gol.equipo_nombre}) ${badge}</li>`;
                });
                html += '</ul>';
            }
            
            // Tarjetas
            if (data.tarjetas && data.tarjetas.length > 0) {
                html += '<h5>Amonestaciones:</h5><ul class="list-group">';
                data.tarjetas.forEach(tarjeta => {
                    const badge = tarjeta.tipo_tarjeta === 'amarilla' ? 
                        '<span class="badge bg-warning text-dark ms-2">Amarilla</span>' : 
                        '<span class="badge bg-danger ms-2">Roja</span>';
                    html += `<li class="list-group-item">${tarjeta.jugador_nombre} (${tarjeta.equipo_nombre}) ${badge}</li>`;
                });
                html += '</ul>';
            }
            
            Swal.fire({
                title: 'Detalles del Partido',
                html: html,
                width: 700
            });
        }
    });
}

/**
 * Eliminar partido
 */
function eliminar(partido_id) {
    Swal.fire({
        title: '¿Eliminar partido?',
        text: 'Solo se puede eliminar si no tiene goles o tarjetas registradas',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#d33'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '../controller/partido_controller.php',
                type: 'POST',
                data: {
                    action: 'eliminar',
                    partido_id: partido_id
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
                        tablaPartidos.ajax.reload();
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