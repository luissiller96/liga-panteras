/**
 * JavaScript: Dashboard
 * Descripción: Dashboard público de la Liga Panteras
 */

$(document).ready(function() {
    cargarDashboardPrincipal();
    
    // Click en liga para ver detalles
    $(document).on('click', '.btn-ver-liga', function() {
        const ligaId = $(this).data('liga-id');
        cargarDatosLiga(ligaId);
    });
});

/**
 * Cargar dashboard principal
 */
function cargarDashboardPrincipal() {
    $.ajax({
        url: '../controller/dashboard_controller.php',
        type: 'POST',
        data: { action: 'dashboard_principal' },
        dataType: 'json',
        success: function(data) {
            // Cargar banners
            if (data.banners && data.banners.length > 0) {
                cargarBanners(data.banners);
            }
            
            // Cargar ligas
            if (data.ligas && data.ligas.length > 0) {
                cargarLigas(data.ligas);
            }
            
            // Cargar próximos partidos
            if (data.proximos_partidos && data.proximos_partidos.length > 0) {
                cargarProximosPartidos(data.proximos_partidos);
            }
            
            // Cargar resultados recientes
            if (data.resultados_recientes && data.resultados_recientes.length > 0) {
                cargarResultadosRecientes(data.resultados_recientes);
            }
            
            // Cargar galería
            if (data.fotos_recientes && data.fotos_recientes.length > 0) {
                cargarGaleria(data.fotos_recientes);
            }
            
            // Cargar estadísticas generales
            cargarEstadisticasGenerales();
        }
    });
}

/**
 * Cargar banners en carousel
 */
function cargarBanners(banners) {
    let html = '';
    banners.forEach((banner, index) => {
        const active = index === 0 ? 'active' : '';
        html += `
            <div class="carousel-item ${active}">
                <img src="../assets/banners/${banner.banner_imagen}" class="d-block w-100" alt="${banner.banner_titulo || ''}">
                ${banner.banner_titulo ? `<div class="carousel-caption"><h5>${banner.banner_titulo}</h5></div>` : ''}
            </div>
        `;
    });
    $('#carousel-banners .carousel-inner').html(html);
}

/**
 * Cargar ligas disponibles
 */
function cargarLigas(ligas) {
    let html = '';
    ligas.forEach(liga => {
        html += `
            <div class="col-md-3 mb-3">
                <div class="card h-100 text-center">
                    ${liga.liga_logo ? `<img src="../assets/logos/${liga.liga_logo}" class="card-img-top p-3" alt="${liga.liga_nombre}">` : ''}
                    <div class="card-body">
                        <h5 class="card-title">${liga.liga_nombre}</h5>
                        <p class="card-text"><small class="text-muted">${liga.liga_descripcion || ''}</small></p>
                        <p><span class="badge bg-primary">${liga.liga_dia_juego.charAt(0).toUpperCase() + liga.liga_dia_juego.slice(1)}</span></p>
                        <p class="mb-0"><strong>${liga.temporadas_activas || 0}</strong> temporadas activas</p>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-primary btn-sm btn-ver-liga" data-liga-id="${liga.liga_id}">
                            Ver Liga <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
    });
    $('#ligas-container').html(html);
}

/**
 * Cargar próximos partidos
 */
function cargarProximosPartidos(partidos) {
    let html = '';
    partidos.forEach(partido => {
        const fecha = new Date(partido.fecha_partido).toLocaleDateString('es-MX', { weekday: 'short', day: '2-digit', month: 'short' });
        html += `
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-header bg-primary text-white text-center">
                        <small>${partido.liga_nombre}</small><br>
                        <strong>${fecha} - ${partido.hora_partido}</strong>
                    </div>
                    <div class="card-body text-center">
                        <div class="row align-items-center">
                            <div class="col-5">
                                ${partido.logo_local ? `<img src="../assets/equipos/${partido.logo_local}" width="50" class="mb-2">` : ''}
                                <p class="mb-0"><strong>${partido.equipo_local}</strong></p>
                            </div>
                            <div class="col-2">
                                <h4 class="text-muted">VS</h4>
                            </div>
                            <div class="col-5">
                                ${partido.logo_visitante ? `<img src="../assets/equipos/${partido.logo_visitante}" width="50" class="mb-2">` : ''}
                                <p class="mb-0"><strong>${partido.equipo_visitante}</strong></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    $('#proximos-partidos-container').html(html || '<p class="text-center text-muted">No hay partidos programados</p>');
}

/**
 * Cargar resultados recientes
 */
function cargarResultadosRecientes(resultados) {
    let html = '';
    resultados.forEach(partido => {
        const fecha = new Date(partido.fecha_partido).toLocaleDateString('es-MX', { day: '2-digit', month: 'short' });
        html += `
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-header bg-success text-white text-center">
                        <small>${partido.liga_nombre}</small><br>
                        <strong>${fecha}</strong>
                    </div>
                    <div class="card-body text-center">
                        <div class="row align-items-center">
                            <div class="col-5">
                                ${partido.logo_local ? `<img src="../assets/equipos/${partido.logo_local}" width="40" class="mb-2">` : ''}
                                <p class="mb-0"><small><strong>${partido.equipo_local}</strong></small></p>
                            </div>
                            <div class="col-2">
                                <h3 class="mb-0">${partido.goles_local} - ${partido.goles_visitante}</h3>
                            </div>
                            <div class="col-5">
                                ${partido.logo_visitante ? `<img src="../assets/equipos/${partido.logo_visitante}" width="40" class="mb-2">` : ''}
                                <p class="mb-0"><small><strong>${partido.equipo_visitante}</strong></small></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    $('#resultados-recientes-container').html(html || '<p class="text-center text-muted">No hay resultados disponibles</p>');
}

/**
 * Cargar galería de fotos
 */
function cargarGaleria(fotos) {
    let html = '';
    fotos.forEach(foto => {
        html += `
            <div class="col-md-4 mb-3">
                <div class="card">
                    <img src="../assets/galeria/${foto.foto_imagen}" class="card-img-top" alt="${foto.foto_titulo || ''}" style="height: 200px; object-fit: cover;">
                    ${foto.foto_titulo ? `<div class="card-body"><p class="card-text text-center"><small>${foto.foto_titulo}</small></p></div>` : ''}
                </div>
            </div>
        `;
    });
    $('#galeria-container').html(html);
}

/**
 * Cargar estadísticas generales
 */
function cargarEstadisticasGenerales() {
    $.ajax({
        url: '../controller/dashboard_controller.php',
        type: 'POST',
        data: { action: 'estadisticas_generales' },
        dataType: 'json',
        success: function(data) {
            $('#stat-ligas').text(data.total_ligas || 0);
            $('#stat-equipos').text(data.total_equipos || 0);
            $('#stat-jugadores').text(data.total_jugadores || 0);
            $('#stat-partidos').text(data.total_partidos || 0);
            $('#stat-goles').text(data.total_goles || 0);
        }
    });
}

/**
 * Cargar datos de una liga específica
 */
function cargarDatosLiga(liga_id) {
    $.ajax({
        url: '../controller/dashboard_controller.php',
        type: 'POST',
        data: {
            action: 'datos_liga',
            liga_id: liga_id
        },
        dataType: 'json',
        success: function(data) {
            if (data.liga) {
                $('#liga-titulo').text(data.liga.liga_nombre);
                
                // Cargar tabla de posiciones si hay temporada activa
                if (data.temporada_activa && data.tabla_posiciones) {
                    cargarTablaPosiciones(data.tabla_posiciones);
                }
                
                // Cargar tabla de goleo
                if (data.tabla_goleo) {
                    cargarTablaGoleo(data.tabla_goleo);
                }
                
                // Mostrar sección de liga
                $('#seccion-principal').hide();
                $('#seccion-liga').show();
            }
        }
    });
}

/**
 * Cargar tabla de posiciones
 */
function cargarTablaPosiciones(tabla) {
    let html = '<table class="table table-striped"><thead><tr><th>#</th><th>Equipo</th><th>PJ</th><th>PG</th><th>PE</th><th>PP</th><th>GF</th><th>GC</th><th>DIF</th><th>PTS</th></tr></thead><tbody>';
    
    tabla.forEach((equipo, index) => {
        const posicion = index + 1;
        const rowClass = posicion <= 8 ? 'table-success' : '';
        html += `
            <tr class="${rowClass}">
                <td><strong>${posicion}</strong></td>
                <td>
                    ${equipo.equipo_logo ? `<img src="../assets/equipos/${equipo.equipo_logo}" width="25" class="me-2">` : ''}
                    ${equipo.equipo_nombre}
                </td>
                <td>${equipo.partidos_jugados}</td>
                <td>${equipo.partidos_ganados}</td>
                <td>${equipo.partidos_empatados}</td>
                <td>${equipo.partidos_perdidos}</td>
                <td>${equipo.goles_favor}</td>
                <td>${equipo.goles_contra}</td>
                <td class="${equipo.diferencia_goles >= 0 ? 'text-success' : 'text-danger'}">${equipo.diferencia_goles >= 0 ? '+' : ''}${equipo.diferencia_goles}</td>
                <td><strong>${equipo.puntos}</strong></td>
            </tr>
        `;
    });
    
    html += '</tbody></table>';
    $('#tabla-posiciones-container').html(html);
}

/**
 * Cargar tabla de goleo
 */
function cargarTablaGoleo(tabla) {
    let html = '<table class="table"><thead><tr><th>#</th><th>Jugador</th><th>Equipo</th><th>Goles</th></tr></thead><tbody>';
    
    tabla.forEach((jugador, index) => {
        html += `
            <tr>
                <td><strong>${index + 1}</strong></td>
                <td>
                    ${jugador.jugador_foto ? `<img src="../assets/jugadores/${jugador.jugador_foto}" width="30" class="rounded-circle me-2">` : ''}
                    ${jugador.jugador_nombre}
                </td>
                <td>
                    ${jugador.equipo_logo ? `<img src="../assets/equipos/${jugador.equipo_logo}" width="25" class="me-2">` : ''}
                    ${jugador.equipo_nombre}
                </td>
                <td><strong class="text-success">${jugador.total_goles}</strong></td>
            </tr>
        `;
    });
    
    html += '</tbody></table>';
    $('#tabla-goleo-container').html(html);
}

/**
 * Volver al dashboard principal
 */
function volverDashboard() {
    $('#seccion-liga').hide();
    $('#seccion-principal').show();
}