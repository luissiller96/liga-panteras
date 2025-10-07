/**
 * JavaScript: Login
 * Descripción: Manejo del login del sistema
 */

$(document).ready(function() {
    
    // Manejar submit del formulario
    $('#form-login').on('submit', function(e) {
        e.preventDefault();
        login();
    });
    
    // Enter en los campos
    $('#correo, #password').on('keypress', function(e) {
        if (e.which === 13) {
            login();
        }
    });
});

/**
 * Realizar login
 */
function login() {
    const correo = $('#correo').val().trim();
    const password = $('#password').val().trim();
    
    // Validaciones
    if (correo === '') {
        Swal.fire({
            icon: 'warning',
            title: 'Campo requerido',
            text: 'Ingresa tu correo electrónico'
        });
        $('#correo').focus();
        return;
    }
    
    if (password === '') {
        Swal.fire({
            icon: 'warning',
            title: 'Campo requerido',
            text: 'Ingresa tu contraseña'
        });
        $('#password').focus();
        return;
    }
    
    // Mostrar loading
    Swal.fire({
        title: 'Iniciando sesión...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Petición AJAX
    $.ajax({
        url: '../controller/usuario_controller.php',
        type: 'POST',
        data: {
            action: 'login',
            correo: correo,
            password: password
        },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: '¡Bienvenido!',
                    text: response.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    // Redirigir según el tipo de usuario
                    if (response.data.usu_tipo === 'admin') {
                        window.location.href = 'dashboard_admin.php';
                    } else {
                        window.location.href = 'dashboard_equipo.php';
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message
                });
            }
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo conectar con el servidor'
            });
        }
    });
}

/**
 * Cerrar sesión
 */
function logout() {
    Swal.fire({
        title: '¿Cerrar sesión?',
        text: '¿Estás seguro de que deseas salir?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, salir',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#d33'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '../controller/usuario_controller.php',
                type: 'POST',
                data: { action: 'logout' },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        window.location.href = 'login.php';
                    }
                }
            });
        }
    });
}