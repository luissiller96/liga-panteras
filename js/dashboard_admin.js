/**
 * Dashboard Admin JS
 * Funcionalidad para el panel de administración
 * Liga Panteras
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
// ANIMACIONES DE ENTRADA
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    // Animar KPI cards
    const kpiCards = document.querySelectorAll('.kpi-card');
    kpiCards.forEach((card, index) => {
        setTimeout(() => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'all 0.5s ease';
            
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 50);
        }, index * 100);
    });
    
    // Animar módulos
    const moduleCards = document.querySelectorAll('.module-card');
    moduleCards.forEach((card, index) => {
        setTimeout(() => {
            card.style.opacity = '0';
            card.style.transform = 'scale(0.9)';
            card.style.transition = 'all 0.5s ease';
            
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'scale(1)';
            }, 50);
        }, index * 80);
    });
});

// ============================================
// TOOLTIPS (opcional)
// ============================================
function initTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    if (typeof bootstrap !== 'undefined') {
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
}

// Inicializar tooltips cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', initTooltips);

// ============================================
// NOTIFICACIONES
// ============================================
function mostrarNotificacion(mensaje, tipo = 'success') {
    // Si usas SweetAlert2
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
        // Fallback a alert
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
    const loader = document.getElementById('loader-overlay');
    if (loader) {
        loader.style.display = 'flex';
    } else {
        // Crear loader si no existe
        const loaderHTML = `
            <div id="loader-overlay" class="loader-overlay">
                <div class="spinner-custom"></div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', loaderHTML);
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
            
            // Aquí puedes implementar la lógica de búsqueda
            // Por ahora solo un ejemplo básico
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
    const valorInicial = 0;
    const incremento = valorFinal / (duracion / 16); // 60 FPS
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

// Inicializar contadores cuando sea visible
function initContadoresKPI() {
    const kpiValues = document.querySelectorAll('.kpi-value');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && !entry.target.dataset.animated) {
                const valorFinal = parseInt(entry.target.textContent);
                if (!isNaN(valorFinal)) {
                    entry.target.textContent = '0';
                    animarContador(entry.target, valorFinal);
                    entry.target.dataset.animated = 'true';
                }
            }
        });
    }, { threshold: 0.5 });
    
    kpiValues.forEach(value => observer.observe(value));
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
    // Esta función se llamará cuando se redimensione la ventana
    // para ajustar el tamaño de los gráficos (Chart.js, etc.)
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
// INICIALIZACIÓN GLOBAL
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    console.log('Dashboard Admin JS cargado correctamente');
    
    // Inicializar funcionalidades
    initBusquedaTopbar();
    initContadoresKPI();
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
    animarContador
};