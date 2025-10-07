/**
 * JavaScript: Helpers
 * Descripción: Funciones auxiliares globales del sistema
 */

/**
 * Formatear fecha en español
 */
function formatearFecha(fecha) {
    const opciones = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(fecha).toLocaleDateString('es-MX', opciones);
}

/**
 * Formatear fecha corta
 */
function formatearFechaCorta(fecha) {
    const opciones = { year: 'numeric', month: '2-digit', day: '2-digit' };
    return new Date(fecha).toLocaleDateString('es-MX', opciones);
}

/**
 * Formatear hora
 */
function formatearHora(hora) {
    return hora.slice(0, 5);
}

/**
 * Formatear moneda
 */
function formatearMoneda(monto) {
    return '$' + parseFloat(monto).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

/**
 * Validar correo electrónico
 */
function validarEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

/**
 * Validar teléfono (10 dígitos)
 */
function validarTelefono(telefono) {
    const re = /^\d{10}$/;
    return re.test(telefono);
}

/**
 * Calcular edad desde fecha de nacimiento
 */
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

/**
 * Mostrar loading en un contenedor
 */
function mostrarLoading(contenedor) {
    $(contenedor).html(`
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-2 text-muted">Cargando...</p>
        </div>
    `);
}

/**
 * Mostrar mensaje de error en contenedor
 */
function mostrarError(contenedor, mensaje) {
    $(contenedor).html(`
        <div class="alert alert-danger text-center">
            <i class="fas fa-exclamation-triangle"></i>
            <p class="mb-0">${mensaje}</p>
        </div>
    `);
}

/**
 * Mostrar mensaje cuando no hay datos
 */
function mostrarSinDatos(contenedor, mensaje = 'No hay datos disponibles') {
    $(contenedor).html(`
        <div class="text-center py-5 text-muted">
            <i class="fas fa-inbox fa-3x mb-3"></i>
            <p>${mensaje}</p>
        </div>
    `);
}

/**
 * Confirmar acción con SweetAlert
 */
function confirmarAccion(titulo, texto, callback) {
    Swal.fire({
        title: titulo,
        text: texto,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, continuar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#d33'
    }).then((result) => {
        if (result.isConfirmed && callback) {
            callback();
        }
    });
}

/**
 * Toast notification
 */
function mostrarToast(tipo, mensaje) {
    const iconos = {
        success: 'success',
        error: 'error',
        warning: 'warning',
        info: 'info'
    };
    
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
    
    Toast.fire({
        icon: iconos[tipo] || 'info',
        title: mensaje
    });
}

/**
 * Copiar texto al portapapeles
 */
function copiarAlPortapapeles(texto) {
    navigator.clipboard.writeText(texto).then(() => {
        mostrarToast('success', 'Copiado al portapapeles');
    }).catch(() => {
        mostrarToast('error', 'No se pudo copiar');
    });
}

/**
 * Descargar JSON como archivo
 */
function descargarJSON(data, nombreArchivo) {
    const dataStr = JSON.stringify(data, null, 2);
    const dataBlob = new Blob([dataStr], { type: 'application/json' });
    const url = URL.createObjectURL(dataBlob);
    const link = document.createElement('a');
    link.href = url;
    link.download = nombreArchivo + '.json';
    link.click();
    URL.revokeObjectURL(url);
}

/**
 * Exportar tabla a CSV
 */
function exportarTablaCSV(tableId, nombreArchivo) {
    const table = document.getElementById(tableId);
    let csv = [];
    
    // Headers
    const headers = [];
    table.querySelectorAll('thead th').forEach(th => {
        headers.push(th.textContent.trim());
    });
    csv.push(headers.join(','));
    
    // Rows
    table.querySelectorAll('tbody tr').forEach(tr => {
        const row = [];
        tr.querySelectorAll('td').forEach(td => {
            row.push('"' + td.textContent.trim().replace(/"/g, '""') + '"');
        });
        csv.push(row.join(','));
    });
    
    // Download
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = nombreArchivo + '.csv';
    link.click();
    URL.revokeObjectURL(url);
}

/**
 * Generar color aleatorio para uniformes
 */
function generarColorAleatorio() {
    const colores = [
        '#FF0000', '#0000FF', '#00FF00', '#FFFF00', '#FF00FF',
        '#00FFFF', '#FFA500', '#800080', '#000000', '#FFFFFF',
        '#008000', '#800000', '#000080', '#808000', '#008080'
    ];
    return colores[Math.floor(Math.random() * colores.length)];
}

/**
 * Validar formato de imagen
 */
function validarImagen(archivo) {
    const formatosPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    const tamañoMaximo = 5 * 1024 * 1024; // 5MB
    
    if (!formatosPermitidos.includes(archivo.type)) {
        Swal.fire('Error', 'Solo se permiten imágenes JPG, PNG o GIF', 'error');
        return false;
    }
    
    if (archivo.size > tamañoMaximo) {
        Swal.fire('Error', 'La imagen no debe superar los 5MB', 'error');
        return false;
    }
    
    return true;
}

/**
 * Preview de imagen antes de subir
 */
function previewImagen(input, targetImg) {
    if (input.files && input.files[0]) {
        if (validarImagen(input.files[0])) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $(targetImg).attr('src', e.target.result).show();
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
}

/**
 * Limpiar formulario
 */
function limpiarFormulario(formId) {
    $(formId)[0].reset();
    $(formId).find('.is-invalid').removeClass('is-invalid');
    $(formId).find('.invalid-feedback').remove();
}

/**
 * Agregar validación de Bootstrap a formulario
 */
function validarFormulario(formId) {
    const form = document.getElementById(formId);
    
    if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
    }
    
    form.classList.add('was-validated');
    return form.checkValidity();
}

/**
 * Inicializar tooltips de Bootstrap
 */
function inicializarTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

/**
 * Scroll suave a elemento
 */
function scrollSuave(elementId) {
    document.getElementById(elementId).scrollIntoView({
        behavior: 'smooth',
        block: 'start'
    });
}

/**
 * Obtener parámetro de URL
 */
function getURLParameter(name) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(name);
}

/**
 * Actualizar parámetro de URL sin recargar
 */
function actualizarURL(parametro, valor) {
    const url = new URL(window.location);
    url.searchParams.set(parametro, valor);
    window.history.pushState({}, '', url);
}

/**
 * Debounce para búsquedas
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Inicializar al cargar el documento
 */
$(document).ready(function() {
    // Inicializar tooltips
    inicializarTooltips();
    
    // Configurar AJAX global
    $.ajaxSetup({
        error: function(xhr, status, error) {
            console.error('Error AJAX:', error);
            if (xhr.status === 401) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sesión expirada',
                    text: 'Por favor, inicia sesión nuevamente',
                    confirmButtonText: 'Ir al login'
                }).then(() => {
                    window.location.href = '../pages/login.php';
                });
            }
        }
    });
});