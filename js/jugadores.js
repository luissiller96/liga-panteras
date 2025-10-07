// ============================================
// GESTIÓN DE JUGADORES - LIGA PANTERAS
// ============================================

let tablaJugadores;
let modalJugador;

$(document).ready(function() {
    inicializarModulo();
});

// ============================================
// INICIALIZACIÓN
// ============================================
function inicializarModulo() {
    // Inicializar modal
    modalJugador = new bootstrap.Modal(document.getElementById('modalJugador'));
    
    // Cargar combos
    cargarEquipos();
    cargarDivisiones();
    
    // Inicializar DataTable
    inicializarDataTable();
    
    // Cargar KPIs
    cargarKPIs();
    
    // Cargar datos
    cargarJugadores();
    
    // Eventos
    inicializarEventos();
}

function inicializarEventos() {
    // Cambio en filtros
    $('#filtroEquipo, #filtroDivision, #filtroPosicion, #filtroEstado').on('change', function() {
        cargarJugadores();
    });
    
    // Preview de foto
    $('#foto').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            if (file.size > 2 * 1024 * 1024) {
                Swal.fire({
                    icon: 'error',
                    title: 'Archivo muy grande',
                    text: 'La foto no debe superar los 2MB'
                });
                $(this).val('');
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#fotoPreview').attr('src', e.target.result);
                $('#fotoPreviewContainer').show();
            };
            reader.readAsDataURL(file);
        } else {
            $('#fotoPreviewContainer').hide();
        }
    });
    
    // Validar CURP en mayúsculas
    $('#curp').on('input', function() {
        $(this).val($(this).val().toUpperCase());
    });
    
    // Validar teléfono (solo números)
    $('#telefono').on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '').substring(0, 10);
    });
}

// ============================================
// DATATABLES
// ============================================
function inicializarDataTable() {
    tablaJugadores = $('#tablaJugadores').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
        },
        responsive: true,
        order: [[1, 'asc']],
        pageLength: 25,
        columnDefs: [
            { orderable: false, targets: [0, 9] }
        ]
    });
}

// ============================================
// CARGAR KPIs
// ============================================
function cargarKPIs() {
    $.ajax({
        url: '../controller/jugador_controller.php',
        type: 'POST',
        data: { action: 'obtener_kpis' },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                const kpis = response.data;
                $('#kpi-total-jugadores').text(kpis.total_jugadores || 0);
                $('#kpi-total-goles').text(kpis.total_goles || 0);
                $('#kpi-tarjetas-amarillas').text(kpis.total_amarillas || 0);
                $('#kpi-tarjetas-rojas').text(kpis.total_rojas || 0);
            }
        }
    });
}

// ============================================
// CARGAR COMBOS
// ============================================
function cargarEquipos() {
    $.ajax({
        url: '../controller/equipo_controller.php',
        type: 'POST',
        data: { action: 'listar', activo: 1 },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                let optionsFiltro = '<option value="">Todos los equipos</option>';
                let optionsModal = '<option value="">Seleccione un equipo</option>';
                
                response.data.forEach(equipo => {
                    optionsFiltro += `<option value="${equipo.equipo_id}">${equipo.equipo_nombre}</option>`;
                    optionsModal += `<option value="${equipo.equipo_id}">${equipo.equipo_nombre} - ${equipo.liga_nombre}</option>`;
                });
                
                $('#filtroEquipo').html(optionsFiltro);
                $('#equipo_id').html(optionsModal);
            }
        }
    });
}

function cargarDivisiones() {
    $.ajax({
        url: '../controller/liga_controller.php',
        type: 'POST',
        data: { action: 'listar' },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                let options = '<option value="">Todas las divisiones</option>';
                
                response.data.forEach(liga => {
                    options += `<option value="${liga.liga_id}">${liga.liga_nombre}</option>`;
                });
                
                $('#filtroDivision').html(options);
            }
        }
    });
}

// ============================================
// CARGAR JUGADORES
// ============================================
function cargarJugadores() {
    const data = {
        action: 'listar'
    };
    
    // Aplicar filtros
    const equipo = $('#filtroEquipo').val();
    const division = $('#filtroDivision').val();
    const posicion = $('#filtroPosicion').val();
    const estado = $('#filtroEstado').val();
    
    if (equipo) data.equipo_id = equipo;
    if (division) data.liga_id = division;
    if (posicion) data.posicion = posicion;
    if (estado !== '') data.activo = estado;
    
    $.ajax({
        url: '../controller/jugador_controller.php',
        type: 'POST',
        data: data,
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                renderizarTabla(response.data);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message || 'Error al cargar jugadores'
                });
            }
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al comunicarse con el servidor'
            });
        }
    });
}

function renderizarTabla(jugadores) {
    tablaJugadores.clear();
    
    if (!jugadores || jugadores.length === 0) {
        tablaJugadores.draw();
        return;
    }
    
    jugadores.forEach(jugador => {
        // Foto
        const foto = jugador.foto 
            ? `<img src="${jugador.foto}" alt="${jugador.nombre_completo}" class="jugador-foto-tabla">` 
            : `<div class="jugador-foto-placeholder"><i class="fas fa-user-circle"></i></div>`;
        
        // Nombre completo
        const nombreCompleto = jugador.nombre_completo || '-';
        
        // Equipo con logo
        const equipoHtml = jugador.equipo_nombre 
            ? `<div class="equipo-info-tabla">
                ${jugador.equipo_logo ? `<img src="${jugador.equipo_logo}" alt="${jugador.equipo_nombre}">` : '<i class="fas fa-shield-alt"></i>'}
                <span>${jugador.equipo_nombre}</span>
               </div>`
            : '-';
        
        // Número de playera
        const numero = jugador.numero_playera 
            ? `<span class="numero-playera">${jugador.numero_playera}</span>`
            : '-';
        
        // Posición con icono
        const posicionIconos = {
            'Portero': 'fa-hands',
            'Defensa': 'fa-shield-alt',
            'Medio': 'fa-circle-dot',
            'Delantero': 'fa-running'
        };
        const iconoPosicion = posicionIconos[jugador.posicion] || 'fa-user';
        const posicion = jugador.posicion 
            ? `<span class="posicion-badge"><i class="fas ${iconoPosicion}"></i> ${jugador.posicion}</span>`
            : '-';
        
        // Estadísticas
        const goles = jugador.goles || 0;
        const amarillas = jugador.tarjetas_amarillas || 0;
        const rojas = jugador.tarjetas_rojas || 0;
        
        // Estado
        const estadoBadge = jugador.activo == 1
            ? '<span class="badge bg-success">Activo</span>'
            : '<span class="badge bg-danger">Inactivo</span>';
        
        // Acciones
        const acciones = `
            <div class="btn-group" role="group">
                <button class="btn btn-sm btn-info" onclick="verDetalleJugador(${jugador.jugador_id})" title="Ver detalle">
                    <i class="fas fa-eye"></i>
                </button>
                <button class="btn btn-sm btn-warning" onclick="editarJugador(${jugador.jugador_id})" title="Editar">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-danger" onclick="eliminarJugador(${jugador.jugador_id})" title="Eliminar">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        
        tablaJugadores.row.add([
            foto,
            nombreCompleto,
            equipoHtml,
            numero,
            posicion,
            `<span class="stat-goles">${goles}</span>`,
            `<span class="stat-amarilla">${amarillas}</span>`,
            `<span class="stat-roja">${rojas}</span>`,
            estadoBadge,
            acciones
        ]);
    });
    
    tablaJugadores.draw();
}

// ============================================
// MODAL NUEVO JUGADOR
// ============================================
function abrirModalNuevo() {
    $('#formJugador')[0].reset();
    $('#jugador_id').val('');
    $('#tituloModal').html('<i class="fas fa-user-plus"></i> Nuevo Jugador');
    $('#fotoPreviewContainer').hide();
    $('#activo').prop('checked', true);
    
    // Resetear estadísticas a 0
    $('#goles').val(0);
    $('#tarjetas_amarillas').val(0);
    $('#tarjetas_rojas').val(0);
    
    // Activar primera pestaña
    $('#tab-datos-generales').tab('show');
    
    modalJugador.show();
}

// ============================================
// EDITAR JUGADOR
// ============================================
function editarJugador(jugadorId) {
    $.ajax({
        url: '../controller/jugador_controller.php',
        type: 'POST',
        data: { 
            action: 'obtener', 
            jugador_id: jugadorId 
        },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                const jugador = response.data;
                
                // Datos generales
                $('#jugador_id').val(jugador.jugador_id);
                $('#nombre').val(jugador.nombre);
                $('#apellido_paterno').val(jugador.apellido_paterno);
                $('#apellido_materno').val(jugador.apellido_materno);
                $('#fecha_nacimiento').val(jugador.fecha_nacimiento);
                $('#genero').val(jugador.genero);
                $('#telefono').val(jugador.telefono);
                $('#curp').val(jugador.curp);
                
                // Equipo y posición
                $('#equipo_id').val(jugador.equipo_id);
                $('#numero_playera').val(jugador.numero_playera);
                $('#posicion').val(jugador.posicion);
                $('#observaciones').val(jugador.observaciones);
                
                // Estadísticas
                $('#goles').val(jugador.goles || 0);
                $('#tarjetas_amarillas').val(jugador.tarjetas_amarillas || 0);
                $('#tarjetas_rojas').val(jugador.tarjetas_rojas || 0);
                $('#activo').prop('checked', jugador.activo == 1);
                
                // Mostrar foto actual si existe
                if (jugador.foto) {
                    $('#fotoPreview').attr('src', jugador.foto);
                    $('#fotoPreviewContainer').show();
                } else {
                    $('#fotoPreviewContainer').hide();
                }
                
                $('#tituloModal').html('<i class="fas fa-user-edit"></i> Editar Jugador');
                modalJugador.show();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message || 'Error al obtener datos del jugador'
                });
            }
        }
    });
}

// ============================================
// GUARDAR JUGADOR
// ============================================
function guardarJugador() {
    // Validar formulario
    if (!$('#formJugador')[0].checkValidity()) {
        $('#formJugador')[0].reportValidity();
        return;
    }
    
    const formData = new FormData($('#formJugador')[0]);
    const jugadorId = $('#jugador_id').val();
    
    formData.append('action', jugadorId ? 'actualizar' : 'insertar');
    formData.append('activo', $('#activo').is(':checked') ? 1 : 0);
    
    // Mostrar loading
    Swal.fire({
        title: 'Guardando...',
        text: 'Por favor espere',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    $.ajax({
        url: '../controller/jugador_controller.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: response.message,
                    timer: 1500,
                    showConfirmButton: false
                });
                
                modalJugador.hide();
                cargarKPIs();
                cargarJugadores();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message || 'Error al guardar el jugador'
                });
            }
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al comunicarse con el servidor'
            });
        }
    });
}

// ============================================
// ELIMINAR JUGADOR
// ============================================
function eliminarJugador(jugadorId) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "Esta acción no se puede deshacer",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e94560',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '../controller/jugador_controller.php',
                type: 'POST',
                data: {
                    action: 'eliminar',
                    jugador_id: jugadorId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Eliminado!',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                        
                        cargarKPIs();
                        cargarJugadores();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'No se pudo eliminar el jugador'
                        });
                    }
                }
            });
        }
    });
}

// ============================================
// VER DETALLE DEL JUGADOR
// ============================================
function verDetalleJugador(jugadorId) {
    $.ajax({
        url: '../controller/jugador_controller.php',
        type: 'POST',
        data: { 
            action: 'obtener', 
            jugador_id: jugadorId 
        },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                const jugador = response.data;
                mostrarDetalleModal(jugador);
            }
        }
    });
}

function mostrarDetalleModal(jugador) {
    const foto = jugador.foto 
        ? `<img src="${jugador.foto}" alt="${jugador.nombre_completo}" style="max-width: 200px; border-radius: 10px;">` 
        : '<i class="fas fa-user-circle" style="font-size: 150px; color: #6c757d;"></i>';
    
    const edad = jugador.fecha_nacimiento 
        ? calcularEdad(jugador.fecha_nacimiento) + ' años'
        : 'No disponible';
    
    Swal.fire({
        title: jugador.nombre_completo,
        html: `
            <div style="text-align: center;">
                ${foto}
                <h4 style="margin-top: 20px;">${jugador.equipo_nombre || 'Sin equipo'}</h4>
                <p style="color: #6c757d;">${jugador.liga_nombre || ''}</p>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 20px; text-align: left;">
                    <div><strong>Número:</strong> ${jugador.numero_playera || 'S/N'}</div>
                    <div><strong>Posición:</strong> ${jugador.posicion || '-'}</div>
                    <div><strong>Edad:</strong> ${edad}</div>
                    <div><strong>Género:</strong> ${jugador.genero === 'M' ? 'Masculino' : jugador.genero === 'F' ? 'Femenino' : '-'}</div>
                    <div><strong>Teléfono:</strong> ${jugador.telefono || '-'}</div>
                    <div><strong>CURP:</strong> ${jugador.curp || '-'}</div>
                </div>
                
                <h5 style="margin-top: 25px; border-top: 2px solid #e94560; padding-top: 15px;">Estadísticas</h5>
                <div style="display: flex; justify-content: space-around; margin-top: 15px;">
                    <div style="text-align: center;">
                        <i class="fas fa-futbol" style="font-size: 30px; color: #06d6a0;"></i>
                        <div style="font-size: 24px; font-weight: bold; margin-top: 5px;">${jugador.goles || 0}</div>
                        <div style="font-size: 12px; color: #6c757d;">Goles</div>
                    </div>
                    <div style="text-align: center;">
                        <i class="fas fa-square" style="font-size: 30px; color: #ffd166;"></i>
                        <div style="font-size: 24px; font-weight: bold; margin-top: 5px;">${jugador.tarjetas_amarillas || 0}</div>
                        <div style="font-size: 12px; color: #6c757d;">T. Amarillas</div>
                    </div>
                    <div style="text-align: center;">
                        <i class="fas fa-square" style="font-size: 30px; color: #ef476f;"></i>
                        <div style="font-size: 24px; font-weight: bold; margin-top: 5px;">${jugador.tarjetas_rojas || 0}</div>
                        <div style="font-size: 12px; color: #6c757d;">T. Rojas</div>
                    </div>
                </div>
            </div>
        `,
        width: 600,
        showCloseButton: true,
        showConfirmButton: false
    });
}

function calcularEdad(fechaNacimiento) {
    const hoy = new Date();
    const nacimiento = new Date(fechaNacimiento);
    let edad = hoy.getFullYear() - nacimiento.getFullYear();
    const mes = hoy.getMonth() - nacimiento.getMonth();
    
    if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) {
        edad--;
    }
    
    return edad;
}

// ============================================
// EXPORTAR A EXCEL
// ============================================
function exportarExcel() {
    Swal.fire({
        icon: 'info',
        title: 'Exportando...',
        text: 'Preparando archivo Excel',
        timer: 1500,
        showConfirmButton: false
    });
    
    // Construir URL con filtros
    let url = '../controller/jugador_controller.php?action=exportar_excel';
    
    const equipo = $('#filtroEquipo').val();
    const division = $('#filtroDivision').val();
    const posicion = $('#filtroPosicion').val();
    const estado = $('#filtroEstado').val();
    
    if (equipo) url += `&equipo_id=${equipo}`;
    if (division) url += `&liga_id=${division}`;
    if (posicion) url += `&posicion=${posicion}`;
    if (estado !== '') url += `&activo=${estado}`;
    
    // Abrir en nueva ventana
    window.open(url, '_blank');
}

// ============================================
// FUNCIÓN SIDEBAR TOGGLE (del dashboard)
// ============================================
function toggleSidebar() {
    $('#sidebar').toggleClass('collapsed');
    $('#mainContent').toggleClass('expanded');
}
