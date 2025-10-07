/**
 * Dashboard Admin JS
 * Funcionalidad para el panel de administración
 * Liga Panteras - VERSIÓN DINÁMICA CON AJAX
 */

// ============================================
// SIDEBAR TOGGLE
// ============================================
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    
    if (sidebar) {
        sidebar.classList.toggle('open');
    }
    
    if (overlay) {
        overlay.classList.toggle('active');
    }
}

// Cerrar sidebar al hacer click en el overlay
document.addEventListener('DOMContentLoaded', function() {
    const overlay = document.getElementById('sidebar-overlay');
    
    if (overlay) {
        overlay.addEventListener('click', function() {
            toggleSidebar();
        });
    }
    
    // Cerrar sidebar con tecla ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const sidebar = document.getElementById('sidebar');
            if (sidebar && sidebar.classList.contains('open')) {
                toggleSidebar();
            }
        }
    });
});

// ============================================
// NOTIFICACIONES
// ============================================
function mostrarNotificacion(mensaje, tipo = 'success') {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: tipo,
            title: tipo === 'success' ? '¡Éxito!' : tipo === 'error' ? 'Error' : 'Información',
            text: mensaje,
            timer: 3000,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        });
    } else {
        alert(mensaje);
    }
}

// ============================================
// CONFIRMACIÓN DE ELIMINACIÓN
// ============================================
function confirmarEliminacion(mensaje, callback) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¿Estás seguro?',
            text: mensaje || "Esta acción no se puede deshacer",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e94560',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed && typeof callback === 'function') {
                callback();
            }
        });
    } else {
        if (confirm(mensaje || "¿Estás seguro de eliminar este registro?")) {
            if (typeof callback === 'function') {
                callback();
            }
        }
    }
}

// ============================================
// LOADER / SPINNER
// ============================================
function mostrarLoader() {
    let loader = document.getElementById('loader-overlay');
    if (loader) {
        loader.style.display = 'flex';
    } else {
        const loaderHTML = `
            <div id="loader-overlay" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;z-index:9999;">
                <div class="spinner-border text-light" role="status" style="width:3rem;height:3rem;">
                    <span class="visually-hidden">Cargando...</span>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', loaderHTML);
        loader = document.getElementById('loader-overlay');
    }
}

function ocultarLoader() {
    const loader = document.getElementById('loader-overlay');
    if (loader) {
        loader.style.display = 'none';
    }
}

// ============================================
// BÚSQUEDA EN TIEMPO REAL (TOPBAR)
// ============================================
function initBusquedaTopbar() {
    const searchInput = document.querySelector('.topbar-search input');
    
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase();
            
            console.log('Buscando:', query);
            
            // Ejemplo: buscar en items del menú
            const menuItems = document.querySelectorAll('.menu-item');
            menuItems.forEach(item => {
                const text = item.textContent.toLowerCase();
                if (text.includes(query)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = query ? 'none' : 'flex';
                }
            });
        });
    }
}

// ============================================
// CONTADOR ANIMADO PARA KPIs
// ============================================
function animarContador(elemento, valorFinal, duracion = 1000) {
    if (typeof elemento === 'string') {
        elemento = document.querySelector(elemento);
    }
    
    if (!elemento) return;
    
    const valorInicial = 0;
    const incremento = valorFinal / (duracion / 16);
    let valorActual = valorInicial;
    
    const timer = setInterval(() => {
        valorActual += incremento;
        if (valorActual >= valorFinal) {
            elemento.textContent = Math.round(valorFinal);
            clearInterval(timer);
        } else {
            elemento.textContent = Math.round(valorActual);
        }
    }, 16);
}

// ============================================
// FORMATO DE NÚMEROS
// ============================================
function formatearNumero(numero) {
    return new Intl.NumberFormat('es-MX').format(numero);
}

// ============================================
// FORMATO DE MONEDA
// ============================================
function formatearMoneda(monto) {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN'
    }).format(monto);
}

// ============================================
// FORMATO DE FECHA
// ============================================
function formatearFecha(fecha, opciones = {}) {
    const opcionesDefault = {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    };
    
    const opcionesFinal = { ...opcionesDefault, ...opciones };
    const date = typeof fecha === 'string' ? new Date(fecha) : fecha;
    
    return date.toLocaleDateString('es-MX', opcionesFinal);
}

// ============================================
// ACTUALIZACIÓN AUTOMÁTICA DE FECHA/HORA
// ============================================
function actualizarFechaHora() {
    const elementoFecha = document.getElementById('fecha-actual');
    if (elementoFecha) {
        const ahora = new Date();
        const opciones = {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        };
        elementoFecha.textContent = ahora.toLocaleDateString('es-MX', opciones);
    }
}

// Actualizar cada minuto
setInterval(actualizarFechaHora, 60000);

// ============================================
// RESPONSIVE: AJUSTAR GRÁFICOS
// ============================================
function ajustarGraficos() {
    if (typeof Chart !== 'undefined') {
        Chart.helpers.each(Chart.instances, function(instance) {
            instance.resize();
        });
    }
}

// Debounce para resize
let resizeTimer;
window.addEventListener('resize', function() {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(ajustarGraficos, 250);
});

// ============================================
// MANEJO DE ERRORES AJAX
// ============================================
function manejarErrorAjax(xhr, status, error) {
    console.error('Error AJAX:', {
        status: status,
        error: error,
        response: xhr.responseText
    });
    
    let mensaje = 'Ocurrió un error al procesar la solicitud';
    
    if (xhr.status === 404) {
        mensaje = 'Recurso no encontrado';
    } else if (xhr.status === 500) {
        mensaje = 'Error en el servidor';
    } else if (xhr.status === 0) {
        mensaje = 'No se pudo conectar con el servidor';
    }
    
    mostrarNotificacion(mensaje, 'error');
}

// ============================================
// REFRESH DE DATOS
// ============================================
function refrescarDashboard() {
    mostrarLoader();
    
    // Recargar todas las secciones
    if (typeof cargarEstadisticas === 'function') {
        cargarEstadisticas();
    }
    
    if (typeof cargarPartidosSemana === 'function') {
        cargarPartidosSemana();
    }
    
    if (typeof cargarDatosGraficos === 'function') {
        cargarDatosGraficos();
    }
    
    setTimeout(() => {
        ocultarLoader();
        mostrarNotificacion('Dashboard actualizado', 'success');
    }, 1000);
}

// ============================================
// INICIALIZACIÓN GLOBAL
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    console.log('Dashboard Admin JS cargado correctamente');
    
    // Inicializar funcionalidades
    initBusquedaTopbar();
    actualizarFechaHora();
    
    // Ocultar loader inicial si existe
    setTimeout(ocultarLoader, 500);
});

// ============================================
// UTILIDADES GLOBALES
// ============================================
window.dashboardUtils = {
    toggleSidebar,
    mostrarNotificacion,
    confirmarEliminacion,
    mostrarLoader,
    ocultarLoader,
    animarContador,
    formatearNumero,
    formatearMoneda,
    formatearFecha,
    manejarErrorAjax,
    refrescarDashboard
};