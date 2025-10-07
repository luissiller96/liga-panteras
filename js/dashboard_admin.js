// ============================================
// DASHBOARD ADMIN - LIGA PANTERAS
// ============================================

$(document).ready(function() {
    cargarDashboard();
});

// ============================================
// FUNCIÓN PRINCIPAL DE CARGA
// ============================================
function cargarDashboard() {
    mostrarLoader();
    
    // Cargar todos los datos del dashboard
    Promise.all([
        cargarKPIs(),
        cargarResumenDivisiones(),
        cargarProximosPartidos(),
        cargarUltimosResultados(),
        cargarTablaGoleo(),
        cargarPagosPendientes()
    ]).then(() => {
        ocultarLoader();
    }).catch((error) => {
        console.error('Error al cargar dashboard:', error);
        ocultarLoader();
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Hubo un problema al cargar el dashboard'
        });
    });
}

// ============================================
// LOADER
// ============================================
function mostrarLoader() {
    $('#dashboardLoader').show();
    $('#dashboardContent').hide();
}

function ocultarLoader() {
    $('#dashboardLoader').hide();
    $('#dashboardContent').fadeIn(400);
}

// ============================================
// CARGAR KPIs
// ============================================
function cargarKPIs() {
    return new Promise((resolve, reject) => {
        $.ajax({
            url: '../controller/dashboard_controller.php',
            type: 'POST',
            data: { action: 'obtener_kpis' },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    actualizarKPIs(response.data);
                    resolve();
                } else {
                    reject(response.message);
                }
            },
            error: function(xhr, status, error) {
                reject(error);
            }
        });
    });
}

function actualizarKPIs(data) {
    // Animar los números
    animarNumero('#kpi-total-equipos', data.total_equipos || 0);
    animarNumero('#kpi-total-jugadores', data.total_jugadores || 0);
    animarNumero('#kpi-partidos-jornada', data.partidos_jornada || 0);
    
    // Formatear el monto de pagos pendientes
    const pagosPendientes = parseFloat(data.pagos_pendientes || 0);
    $('#kpi-pagos-pendientes').text('$' + pagosPendientes.toLocaleString('es-MX', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }));
}

// ============================================
// CARGAR RESUMEN POR DIVISIÓN
// ============================================
function cargarResumenDivisiones() {
    return new Promise((resolve, reject) => {
        $.ajax({
            url: '../controller/dashboard_controller.php',
            type: 'POST',
            data: { action: 'obtener_resumen_divisiones' },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    renderizarResumenDivisiones(response.data);
                    resolve();
                } else {
                    reject(response.message);
                }
            },
            error: function(xhr, status, error) {
                reject(error);
            }
        });
    });
}

function renderizarResumenDivisiones(divisiones) {
    const container = $('#resumen-divisiones');
    container.empty();
    
    if (!divisiones || divisiones.length === 0) {
        container.html('<div class="col-12"><div class="alert alert-info">No hay divisiones disponibles</div></div>');
        return;
    }
    
    // Iconos personalizados por división
    const iconos = {
        'Panteras Champions League': 'fa-crown',
        'Panteras Europa League': 'fa-star',
        'Panteras Conference League': 'fa-medal',
        'MLS': 'fa-trophy'
    };
    
    // Colores por división
    const colores = {
        'Panteras Champions League': 'primary',
        'Panteras Europa League': 'info',
        'Panteras Conference League': 'success',
        'MLS': 'warning'
    };
    
    divisiones.forEach(division => {
        const icono = iconos[division.liga_nombre] || 'fa-trophy';
        const color = colores[division.liga_nombre] || 'primary';
        
        const html = `
            <div class="col-lg-3 col-md-6">
                <div class="division-card ${color}">
                    <div class="division-icon">
                        <i class="fas ${icono}"></i>
                    </div>
                    <div class="division-info">
                        <h3>${division.liga_nombre}</h3>
                        <div class="division-stats">
                            <div class="stat-item">
                                <span class="stat-value">${division.total_equipos || 0}</span>
                                <span class="stat-label">Equipos</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-value">${division.jornada_actual || 0}</span>
                                <span class="stat-label">Jornada</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-value">${division.partidos_pendientes || 0}</span>
                                <span class="stat-label">Partidos</span>
                            </div>
                        </div>
                        <div class="division-actions">
                            <a href="tabla_posiciones.php?liga_id=${division.liga_id}" class="btn-ver-mas">
                                Ver Tabla <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        container.append(html);
    });
}

// ============================================
// CARGAR PRÓXIMOS PARTIDOS
// ============================================
function cargarProximosPartidos() {
    return new Promise((resolve, reject) => {
        $.ajax({
            url: '../controller/partido_controller.php',
            type: 'POST',
            data: { action: 'obtener_proximos_partidos', limit: 5 },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    renderizarProximosPartidos(response.data);
                    resolve();
                } else {
                    reject(response.message);
                }
            },
            error: function(xhr, status, error) {
                reject(error);
            }
        });
    });
}

function renderizarProximosPartidos(partidos) {
    const container = $('#proximos-partidos');
    container.empty();
    
    if (!partidos || partidos.length === 0) {
        container.html('<div class="alert alert-info">No hay partidos próximos</div>');
        return;
    }
    
    partidos.forEach(partido => {
        const fecha = new Date(partido.fecha_partido);
        const fechaFormateada = fecha.toLocaleDateString('es-MX', { 
            weekday: 'short', 
            day: 'numeric', 
            month: 'short' 
        });
        const horaFormateada = partido.hora_partido;
        
        const html = `
            <div class="partido-item">
                <div class="partido-fecha">
                    <i class="fas fa-calendar"></i>
                    ${fechaFormateada} - ${horaFormateada}
                </div>
                <div class="partido-equipos">
                    <div class="equipo">
                        ${partido.logo_local ? `<img src="${partido.logo_local}" alt="${partido.equipo_local}">` : '<i class="fas fa-shield-alt"></i>'}
                        <span>${partido.equipo_local}</span>
                    </div>
                    <div class="vs">VS</div>
                    <div class="equipo">
                        ${partido.logo_visitante ? `<img src="${partido.logo_visitante}" alt="${partido.equipo_visitante}">` : '<i class="fas fa-shield-alt"></i>'}
                        <span>${partido.equipo_visitante}</span>
                    </div>
                </div>
                <div class="partido-liga">
                    <span class="badge badge-division">${partido.liga_nombre}</span>
                </div>
            </div>
        `;
        
        container.append(html);
    });
}

// ============================================
// CARGAR ÚLTIMOS RESULTADOS
// ============================================
function cargarUltimosResultados() {
    return new Promise((resolve, reject) => {
        $.ajax({
            url: '../controller/partido_controller.php',
            type: 'POST',
            data: { action: 'obtener_ultimos_resultados', limit: 5 },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    renderizarUltimosResultados(response.data);
                    resolve();
                } else {
                    reject(response.message);
                }
            },
            error: function(xhr, status, error) {
                reject(error);
            }
        });
    });
}

function renderizarUltimosResultados(resultados) {
    const container = $('#ultimos-resultados');
    container.empty();
    
    if (!resultados || resultados.length === 0) {
        container.html('<div class="alert alert-info">No hay resultados disponibles</div>');
        return;
    }
    
    resultados.forEach(resultado => {
        const fecha = new Date(resultado.fecha_partido);
        const fechaFormateada = fecha.toLocaleDateString('es-MX', { 
            day: 'numeric', 
            month: 'short' 
        });
        
        // Determinar ganador
        let claseLocal = '';
        let claseVisitante = '';
        
        if (resultado.goles_local > resultado.goles_visitante) {
            claseLocal = 'ganador';
        } else if (resultado.goles_local < resultado.goles_visitante) {
            claseVisitante = 'ganador';
        }
        
        const html = `
            <div class="resultado-item">
                <div class="resultado-fecha">
                    <i class="fas fa-calendar-check"></i>
                    ${fechaFormateada}
                </div>
                <div class="resultado-marcador">
                    <div class="equipo ${claseLocal}">
                        <span class="equipo-nombre">${resultado.equipo_local}</span>
                        <span class="equipo-goles">${resultado.goles_local}</span>
                    </div>
                    <div class="separador">-</div>
                    <div class="equipo ${claseVisitante}">
                        <span class="equipo-goles">${resultado.goles_visitante}</span>
                        <span class="equipo-nombre">${resultado.equipo_visitante}</span>
                    </div>
                </div>
                <div class="resultado-liga">
                    <span class="badge badge-division">${resultado.liga_nombre}</span>
                </div>
            </div>
        `;
        
        container.append(html);
    });
}

// ============================================
// CARGAR TABLA DE GOLEO
// ============================================
function cargarTablaGoleo() {
    return new Promise((resolve, reject) => {
        $.ajax({
            url: '../controller/estadistica_controller.php',
            type: 'POST',
            data: { action: 'obtener_goleadores', limit: 10 },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    renderizarTablaGoleo(response.data);
                    resolve();
                } else {
                    reject(response.message);
                }
            },
            error: function(xhr, status, error) {
                reject(error);
            }
        });
    });
}

function renderizarTablaGoleo(goleadores) {
    const tbody = $('#tabla-goleo tbody');
    tbody.empty();
    
    if (!goleadores || goleadores.length === 0) {
        tbody.html('<tr><td colspan="5" class="text-center">No hay goleadores registrados</td></tr>');
        return;
    }
    
    goleadores.forEach((jugador, index) => {
        // Agregar medalla para top 3
        let medalla = '';
        if (index === 0) medalla = '<i class="fas fa-medal text-warning"></i> ';
        else if (index === 1) medalla = '<i class="fas fa-medal text-secondary"></i> ';
        else if (index === 2) medalla = '<i class="fas fa-medal text-bronze"></i> ';
        
        const html = `
            <tr>
                <td>${medalla}${index + 1}</td>
                <td>
                    <div class="jugador-info">
                        ${jugador.foto ? `<img src="${jugador.foto}" alt="${jugador.jugador_nombre}" class="jugador-foto">` : '<i class="fas fa-user-circle"></i>'}
                        <span>${jugador.jugador_nombre}</span>
                    </div>
                </td>
                <td>${jugador.equipo_nombre}</td>
                <td>${jugador.liga_nombre}</td>
                <td class="text-center"><strong>${jugador.total_goles}</strong></td>
            </tr>
        `;
        
        tbody.append(html);
    });
}

// ============================================
// CARGAR PAGOS PENDIENTES
// ============================================
function cargarPagosPendientes() {
    return new Promise((resolve, reject) => {
        $.ajax({
            url: '../controller/pago_controller.php',
            type: 'POST',
            data: { action: 'listar_pagos_pendientes' },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    renderizarPagosPendientes(response.data);
                    resolve();
                } else {
                    reject(response.message);
                }
            },
            error: function(xhr, status, error) {
                reject(error);
            }
        });
    });
}

function renderizarPagosPendientes(pagos) {
    const tbody = $('#tabla-pagos-pendientes tbody');
    tbody.empty();
    
    if (!pagos || pagos.length === 0) {
        tbody.html('<tr><td colspan="7" class="text-center">No hay pagos pendientes</td></tr>');
        return;
    }
    
    pagos.forEach(pago => {
        const total = parseFloat(pago.monto_inscripcion || 0);
        const pagado = parseFloat(pago.monto_pagado || 0);
        const pendiente = total - pagado;
        
        // Calcular porcentaje pagado
        const porcentaje = total > 0 ? (pagado / total * 100) : 0;
        
        // Determinar clase de estado
        let estadoClass = 'badge-danger';
        let estadoTexto = 'Pendiente';
        
        if (porcentaje >= 100) {
            estadoClass = 'badge-success';
            estadoTexto = 'Pagado';
        } else if (porcentaje >= 50) {
            estadoClass = 'badge-warning';
            estadoTexto = 'Parcial';
        }
        
        const html = `
            <tr>
                <td>${pago.equipo_nombre}</td>
                <td>${pago.liga_nombre}</td>
                <td>${pago.temporada_nombre}</td>
                <td class="text-end">$${total.toFixed(2)}</td>
                <td class="text-end">$${pagado.toFixed(2)}</td>
                <td class="text-end text-danger"><strong>$${pendiente.toFixed(2)}</strong></td>
                <td class="text-center">
                    <span class="badge ${estadoClass}">${estadoTexto}</span>
                    <div class="progress mt-2" style="height: 5px;">
                        <div class="progress-bar ${estadoClass.replace('badge', 'bg')}" 
                             style="width: ${porcentaje}%"></div>
                    </div>
                </td>
            </tr>
        `;
        
        tbody.append(html);
    });
}

// ============================================
// FUNCIONES AUXILIARES
// ============================================

// Animar números con efecto de contador
function animarNumero(selector, valorFinal) {
    const elemento = $(selector);
    const duracion = 1000; // 1 segundo
    const incremento = Math.ceil(valorFinal / 50);
    let valorActual = 0;
    
    const intervalo = setInterval(function() {
        valorActual += incremento;
        if (valorActual >= valorFinal) {
            valorActual = valorFinal;
            clearInterval(intervalo);
        }
        elemento.text(valorActual);
    }, duracion / 50);
}

// ============================================
// SIDEBAR TOGGLE
// ============================================
function toggleSidebar() {
    $('#sidebar').toggleClass('collapsed');
    $('#mainContent').toggleClass('expanded');
}

// Cerrar sidebar en móvil al hacer clic en un enlace
$('.sidebar-menu .menu-item').on('click', function() {
    if ($(window).width() <= 768) {
        $('#sidebar').addClass('collapsed');
        $('#mainContent').addClass('expanded');
    }
});

// ============================================
// RESPONSIVE
// ============================================
$(window).on('resize', function() {
    if ($(window).width() <= 768) {
        $('#sidebar').addClass('collapsed');
        $('#mainContent').addClass('expanded');
    } else {
        $('#sidebar').removeClass('collapsed');
        $('#mainContent').removeClass('expanded');
    }
});
