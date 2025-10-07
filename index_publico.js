// ============================================
// DASHBOARD PÚBLICO - LIGA PANTERAS
// ============================================

let bannerSwiper = null;
let ligaSeleccionada = 'todas';

$(document).ready(function() {
    inicializarDashboard();
    inicializarEventos();
});

// ============================================
// INICIALIZACIÓN
// ============================================
function inicializarDashboard() {
    cargarBanners();
    cargarDivisiones();
    cargarPartidosSemana();
    cargarUltimosResultados();
    cargarProximaJornada();
    cargarTablasGlobales();
    cargarTablaGoleadores();
}

function inicializarEventos() {
    // Smooth scroll para navegación
    $('a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        const target = $(this.getAttribute('href'));
        if (target.length) {
            $('html, body').stop().animate({
                scrollTop: target.offset().top - 80
            }, 800);
        }
    });
    
    // Filtros de división en partidos
    $('.btn-filtro').on('click', function() {
        $('.btn-filtro').removeClass('active');
        $(this).addClass('active');
        
        ligaSeleccionada = $(this).data('liga');
        cargarPartidosSemana();
    });
    
    // Cambio de navbar en scroll
    $(window).on('scroll', function() {
        if ($(this).scrollTop() > 100) {
            $('.navbar').addClass('scrolled');
        } else {
            $('.navbar').removeClass('scrolled');
        }
    });
}

// ============================================
// CARGAR BANNERS
// ============================================
function cargarBanners() {
    $.ajax({
        url: 'controller/banner_controller.php',
        type: 'POST',
        data: { action: 'listar_activos' },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success' && response.data.length > 0) {
                renderizarBanners(response.data);
            } else {
                // Banner por defecto si no hay banners
                $('#bannersContainer').html(`
                    <div class="swiper-slide">
                        <div class="banner-default">
                            <h2>Bienvenido a Liga Panteras</h2>
                            <p>La liga de fútbol 7 más competitiva</p>
                        </div>
                    </div>
                `);
                inicializarSwiper();
            }
        },
        error: function() {
            console.error('Error al cargar banners');
        }
    });
}

function renderizarBanners(banners) {
    const container = $('#bannersContainer');
    container.empty();
    
    banners.forEach(banner => {
        const html = `
            <div class="swiper-slide">
                <img src="${banner.imagen_url}" alt="${banner.titulo || 'Banner'}">
                ${banner.titulo || banner.descripcion ? `
                    <div class="banner-caption">
                        ${banner.titulo ? `<h2>${banner.titulo}</h2>` : ''}
                        ${banner.descripcion ? `<p>${banner.descripcion}</p>` : ''}
                    </div>
                ` : ''}
            </div>
        `;
        container.append(html);
    });
    
    inicializarSwiper();
}

function inicializarSwiper() {
    bannerSwiper = new Swiper('.bannerSwiper', {
        loop: true,
        autoplay: {
            delay: 5000,
            disableOnInteraction: false,
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        effect: 'fade',
        fadeEffect: {
            crossFade: true
        }
    });
}

// ============================================
// CARGAR DIVISIONES
// ============================================
function cargarDivisiones() {
    $.ajax({
        url: 'controller/liga_controller.php',
        type: 'POST',
        data: { action: 'listar' },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                renderizarDivisiones(response.data);
            }
        },
        error: function() {
            console.error('Error al cargar divisiones');
        }
    });
}

function renderizarDivisiones(divisiones) {
    const container = $('#divisionesCards');
    container.empty();
    
    const iconos = {
        'Panteras Champions League': 'fa-crown',
        'Panteras Europa League': 'fa-star',
        'Panteras Conference League': 'fa-medal',
        'MLS': 'fa-trophy'
    };
    
    const colores = ['primary', 'info', 'success', 'warning'];
    
    divisiones.forEach((division, index) => {
        const icono = iconos[division.liga_nombre] || 'fa-trophy';
        const color = colores[index % colores.length];
        
        const html = `
            <div class="col-lg-3 col-md-6">
                <div class="division-card-public ${color}">
                    <div class="division-icon-public">
                        <i class="fas ${icono}"></i>
                    </div>
                    <h3>${division.liga_nombre}</h3>
                    <p>${division.liga_descripcion || ''}</p>
                    <div class="division-info-public">
                        <span><i class="fas fa-calendar"></i> ${division.liga_dia_juego || 'Por definir'}</span>
                    </div>
                    <a href="#posiciones" class="btn-ver-tabla" data-tab="${index}">
                        Ver Tabla <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        `;
        
        container.append(html);
    });
    
    // Event para ver tabla al hacer clic
    $('.btn-ver-tabla').on('click', function(e) {
        e.preventDefault();
        const tabIndex = $(this).data('tab');
        const tabButtons = $('#tablasTab button');
        
        if (tabButtons[tabIndex]) {
            $(tabButtons[tabIndex]).tab('show');
        }
        
        // Scroll suave a la sección de posiciones
        $('html, body').animate({
            scrollTop: $('#posiciones').offset().top - 80
        }, 800);
    });
}

// ============================================
// CARGAR PARTIDOS DE LA SEMANA
// ============================================
function cargarPartidosSemana() {
    const data = { action: 'obtener_partidos_semana' };
    
    if (ligaSeleccionada !== 'todas') {
        data.liga_id = ligaSeleccionada;
    }
    
    $.ajax({
        url: 'controller/partido_controller.php',
        type: 'POST',
        data: data,
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                renderizarPartidosSemana(response.data);
            } else {
                $('#partidosContainer').html('<div class="alert alert-info">No hay partidos programados para esta semana</div>');
            }
        },
        error: function() {
            console.error('Error al cargar partidos');
            $('#partidosContainer').html('<div class="alert alert-danger">Error al cargar partidos</div>');
        }
    });
}

function renderizarPartidosSemana(partidos) {
    const container = $('#partidosContainer');
    container.empty();
    
    if (!partidos || partidos.length === 0) {
        container.html('<div class="alert alert-info">No hay partidos programados</div>');
        return;
    }
    
    // Agrupar por fecha
    const partidosPorFecha = {};
    partidos.forEach(partido => {
        const fecha = partido.fecha_partido;
        if (!partidosPorFecha[fecha]) {
            partidosPorFecha[fecha] = [];
        }
        partidosPorFecha[fecha].push(partido);
    });
    
    // Renderizar por fecha
    Object.keys(partidosPorFecha).sort().forEach(fecha => {
        const fechaObj = new Date(fecha + 'T00:00:00');
        const fechaFormateada = fechaObj.toLocaleDateString('es-MX', { 
            weekday: 'long', 
            day: 'numeric', 
            month: 'long' 
        });
        
        let html = `
            <div class="fecha-grupo">
                <h3 class="fecha-titulo"><i class="fas fa-calendar-day"></i> ${fechaFormateada}</h3>
                <div class="row g-3">
        `;
        
        partidosPorFecha[fecha].forEach(partido => {
            html += `
                <div class="col-lg-6">
                    <div class="partido-card-public">
                        <div class="partido-header-public">
                            <span class="badge badge-liga">${partido.liga_nombre}</span>
                            <span class="partido-hora"><i class="fas fa-clock"></i> ${partido.hora_partido}</span>
                        </div>
                        <div class="partido-vs">
                            <div class="equipo-publico">
                                ${partido.logo_local ? `<img src="${partido.logo_local}" alt="${partido.equipo_local}">` : '<i class="fas fa-shield-alt"></i>'}
                                <span>${partido.equipo_local}</span>
                            </div>
                            <div class="vs-text">VS</div>
                            <div class="equipo-publico">
                                ${partido.logo_visitante ? `<img src="${partido.logo_visitante}" alt="${partido.equipo_visitante}">` : '<i class="fas fa-shield-alt"></i>'}
                                <span>${partido.equipo_visitante}</span>
                            </div>
                        </div>
                        <div class="partido-footer-public">
                            <span><i class="fas fa-map-marker-alt"></i> Cancha Principal</span>
                        </div>
                    </div>
                </div>
            `;
        });
        
        html += `
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
    $.ajax({
        url: 'controller/partido_controller.php',
        type: 'POST',
        data: { action: 'obtener_ultimos_resultados', limit: 6 },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                renderizarUltimosResultados(response.data);
            }
        },
        error: function() {
            console.error('Error al cargar resultados');
        }
    });
}

function renderizarUltimosResultados(resultados) {
    const container = $('#resultadosContainer');
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
        
        let claseLocal = 'empate';
        let claseVisitante = 'empate';
        
        if (resultado.goles_local > resultado.goles_visitante) {
            claseLocal = 'ganador';
            claseVisitante = 'perdedor';
        } else if (resultado.goles_local < resultado.goles_visitante) {
            claseLocal = 'perdedor';
            claseVisitante = 'ganador';
        }
        
        const html = `
            <div class="resultado-card-public">
                <div class="resultado-liga-fecha">
                    <span class="badge badge-liga">${resultado.liga_nombre}</span>
                    <span class="resultado-fecha-text">${fechaFormateada}</span>
                </div>
                <div class="resultado-marcador-public">
                    <div class="equipo-resultado ${claseLocal}">
                        <span class="equipo-nombre-res">${resultado.equipo_local}</span>
                        <span class="equipo-goles-res">${resultado.goles_local}</span>
                    </div>
                    <div class="separador-res">-</div>
                    <div class="equipo-resultado ${claseVisitante}">
                        <span class="equipo-goles-res">${resultado.goles_visitante}</span>
                        <span class="equipo-nombre-res">${resultado.equipo_visitante}</span>
                    </div>
                </div>
            </div>
        `;
        
        container.append(html);
    });
}

// ============================================
// CARGAR PRÓXIMA JORNADA
// ============================================
function cargarProximaJornada() {
    $.ajax({
        url: 'controller/jornada_controller.php',
        type: 'POST',
        data: { action: 'obtener_proxima_jornada' },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                renderizarProximaJornada(response.data);
            }
        },
        error: function() {
            console.error('Error al cargar próxima jornada');
        }
    });
}

function renderizarProximaJornada(jornadas) {
    const container = $('#proximaJornada');
    container.empty();
    
    if (!jornadas || jornadas.length === 0) {
        container.html('<p class="text-muted">No hay jornadas próximas</p>');
        return;
    }
    
    jornadas.forEach(jornada => {
        const html = `
            <div class="jornada-item">
                <div class="jornada-titulo">
                    <strong>${jornada.liga_nombre}</strong>
                </div>
                <div class="jornada-numero">
                    Jornada ${jornada.numero_jornada}
                </div>
                <div class="jornada-fecha">
                    <i class="fas fa-calendar"></i> ${jornada.fecha_inicio}
                </div>
            </div>
        `;
        container.append(html);
    });
}

// ============================================
// CARGAR TABLAS DE POSICIONES
// ============================================
function cargarTablasGlobales() {
    // Cargar cada tabla por división
    cargarTablaPosiciones(1, 'champions');
    cargarTablaPosiciones(2, 'europa');
    cargarTablaPosiciones(3, 'conference');
    cargarTablaPosiciones(4, 'mls');
}

function cargarTablaPosiciones(ligaId, nombreTabla) {
    $.ajax({
        url: 'controller/posicion_controller.php',
        type: 'POST',
        data: { action: 'obtener_tabla_posiciones', liga_id: ligaId },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                renderizarTablaPosiciones(response.data, nombreTabla);
            }
        },
        error: function() {
            console.error('Error al cargar tabla de posiciones');
        }
    });
}

function renderizarTablaPosiciones(equipos, nombreTabla) {
    const tbody = $(`#tbody-${nombreTabla}`);
    tbody.empty();
    
    if (!equipos || equipos.length === 0) {
        tbody.html('<tr><td colspan="10" class="text-center">No hay equipos registrados</td></tr>');
        return;
    }
    
    equipos.forEach((equipo, index) => {
        // Clasificación para playoffs (top 8)
        let claseClasificacion = '';
        if (index < 8) {
            claseClasificacion = 'clasificado';
        }
        
        const html = `
            <tr class="${claseClasificacion}">
                <td><strong>${index + 1}</strong></td>
                <td>
                    <div class="equipo-nombre-tabla">
                        ${equipo.logo ? `<img src="${equipo.logo}" alt="${equipo.equipo_nombre}">` : ''}
                        <a href="equipo_detalle.php?id=${equipo.equipo_id}">${equipo.equipo_nombre}</a>
                    </div>
                </td>
                <td class="text-center">${equipo.partidos_jugados}</td>
                <td class="text-center">${equipo.partidos_ganados}</td>
                <td class="text-center">${equipo.partidos_empatados}</td>
                <td class="text-center">${equipo.partidos_perdidos}</td>
                <td class="text-center">${equipo.goles_favor}</td>
                <td class="text-center">${equipo.goles_contra}</td>
                <td class="text-center">${equipo.diferencia_goles}</td>
                <td class="text-center"><strong>${equipo.puntos}</strong></td>
            </tr>
        `;
        
        tbody.append(html);
    });
}

// ============================================
// CARGAR TABLA DE GOLEADORES
// ============================================
function cargarTablaGoleadores() {
    $.ajax({
        url: 'controller/estadistica_controller.php',
        type: 'POST',
        data: { action: 'obtener_goleadores', limit: 20 },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                renderizarTablaGoleadores(response.data);
            }
        },
        error: function() {
            console.error('Error al cargar goleadores');
        }
    });
}

function renderizarTablaGoleadores(goleadores) {
    const tbody = $('#tbody-goleadores');
    tbody.empty();
    
    if (!goleadores || goleadores.length === 0) {
        tbody.html('<tr><td colspan="5" class="text-center">No hay goleadores registrados</td></tr>');
        return;
    }
    
    goleadores.forEach((jugador, index) => {
        let medalla = '';
        let claseEspecial = '';
        
        if (index === 0) {
            medalla = '<i class="fas fa-medal" style="color: #FFD700;"></i> ';
            claseEspecial = 'top-1';
        } else if (index === 1) {
            medalla = '<i class="fas fa-medal" style="color: #C0C0C0;"></i> ';
            claseEspecial = 'top-2';
        } else if (index === 2) {
            medalla = '<i class="fas fa-medal" style="color: #CD7F32;"></i> ';
            claseEspecial = 'top-3';
        }
        
        const html = `
            <tr class="${claseEspecial}">
                <td>${medalla}${index + 1}</td>
                <td>
                    <div class="jugador-info-tabla">
                        ${jugador.foto ? `<img src="${jugador.foto}" alt="${jugador.jugador_nombre}">` : '<i class="fas fa-user-circle"></i>'}
                        <span>${jugador.jugador_nombre}</span>
                    </div>
                </td>
                <td>${jugador.equipo_nombre}</td>
                <td><span class="badge badge-division">${jugador.liga_nombre}</span></td>
                <td class="text-center"><strong class="goles-num">${jugador.total_goles}</strong></td>
            </tr>
        `;
        
        tbody.append(html);
    });
}
