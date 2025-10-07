<?php
/**
 * Session Manager
 * Gestión centralizada de sesiones para Liga Panteras
 */

// Iniciar sesión si no está activa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Verificar si hay una sesión activa
 */
function verificar_sesion() {
    return isset($_SESSION['usu_id']) && !empty($_SESSION['usu_id']);
}

/**
 * Verificar si el usuario es administrador
 */
function es_admin() {
    return verificar_sesion() && isset($_SESSION['usu_tipo']) && $_SESSION['usu_tipo'] === 'admin';
}

/**
 * Verificar si el usuario es de tipo equipo
 */
function es_equipo() {
    return verificar_sesion() && isset($_SESSION['usu_tipo']) && $_SESSION['usu_tipo'] === 'equipo';
}

/**
 * Obtener ID del usuario actual
 */
function obtener_usuario_id() {
    return verificar_sesion() ? $_SESSION['usu_id'] : null;
}

/**
 * Obtener nombre completo del usuario actual
 */
function obtener_usuario_nombre() {
    if (verificar_sesion()) {
        $nombre = $_SESSION['usu_nom'] ?? '';
        $apellido = $_SESSION['usu_ape'] ?? '';
        return trim($nombre . ' ' . $apellido);
    }
    return '';
}

/**
 * Obtener solo el nombre del usuario actual
 */
function obtener_nombre() {
    return verificar_sesion() ? ($_SESSION['usu_nom'] ?? '') : '';
}

/**
 * Obtener tipo de usuario actual
 */
function obtener_tipo_usuario() {
    return verificar_sesion() ? ($_SESSION['usu_tipo'] ?? '') : '';
}

/**
 * Obtener equipo_id del usuario actual (si aplica)
 */
function obtener_equipo_id() {
    return verificar_sesion() ? ($_SESSION['equipo_id'] ?? null) : null;
}

/**
 * Obtener nombre del equipo del usuario (si aplica)
 */
function obtener_equipo_nombre() {
    return verificar_sesion() ? ($_SESSION['equipo_nombre'] ?? '') : '';
}

/**
 * Obtener correo del usuario actual
 */
function obtener_usuario_correo() {
    return verificar_sesion() ? ($_SESSION['usu_correo'] ?? '') : '';
}

/**
 * Obtener foto de perfil del usuario
 */
function obtener_foto_perfil() {
    if (verificar_sesion() && isset($_SESSION['usu_photoprofile']) && !empty($_SESSION['usu_photoprofile'])) {
        return '../assets/usuarios/' . $_SESSION['usu_photoprofile'];
    }
    return '../assets/images/default-avatar.png';
}

/**
 * Obtener todos los datos del usuario actual
 */
function obtener_usuario_actual() {
    if (!verificar_sesion()) {
        return null;
    }

    return [
        'usu_id' => $_SESSION['usu_id'] ?? null,
        'usu_nom' => $_SESSION['usu_nom'] ?? '',
        'usu_ape' => $_SESSION['usu_ape'] ?? '',
        'usu_correo' => $_SESSION['usu_correo'] ?? '',
        'usu_tipo' => $_SESSION['usu_tipo'] ?? '',
        'equipo_id' => $_SESSION['equipo_id'] ?? null,
        'equipo_nombre' => $_SESSION['equipo_nombre'] ?? '',
        'usu_photoprofile' => $_SESSION['usu_photoprofile'] ?? ''
    ];
}

/**
 * Iniciar sesión de usuario
 */
function iniciar_sesion($usuario_data) {
    // Regenerar ID de sesión por seguridad
    session_regenerate_id(true);

    $_SESSION['usu_id'] = $usuario_data['usu_id'];
    $_SESSION['usu_nom'] = $usuario_data['usu_nom'];
    $_SESSION['usu_ape'] = $usuario_data['usu_ape'] ?? '';
    $_SESSION['usu_correo'] = $usuario_data['usu_correo'];
    $_SESSION['usu_tipo'] = $usuario_data['usu_tipo'];
    $_SESSION['equipo_id'] = $usuario_data['equipo_id'] ?? null;
    $_SESSION['equipo_nombre'] = $usuario_data['equipo_nombre'] ?? '';
    $_SESSION['usu_photoprofile'] = $usuario_data['usu_photoprofile'] ?? '';

    return true;
}

/**
 * Destruir sesión de usuario
 */
function destruir_sesion() {
    // Eliminar todas las variables de sesión
    $_SESSION = array();

    // Destruir la cookie de sesión si existe
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 42000, '/');
    }

    // Destruir la sesión
    session_destroy();

    return true;
}

/**
 * Verificar permisos para una página específica
 */
function tiene_permiso($pagina) {
    if (!verificar_sesion()) {
        return false;
    }

    $tipo_usuario = $_SESSION['usu_tipo'] ?? '';

    // Páginas que solo pueden ver los administradores
    $paginas_admin = [
        'dashboard_admin.php',
        'ligas.php',
        'liga.php',
        'temporadas.php',
        'equipos.php',
        'jugadores.php',
        'partidos.php',
        'resultados.php',
        'estadisticas.php',
        'pagos.php',
        'banners.php',
        'usuarios.php'
    ];

    // Páginas que pueden ver los equipos
    $paginas_equipo = [
        'dashboard_equipo.php',
        'mi_equipo.php',
        'mis_jugadores.php',
        'mis_partidos.php',
        'mis_pagos.php'
    ];

    if ($tipo_usuario === 'admin') {
        return true; // Admin tiene acceso a todo
    } elseif ($tipo_usuario === 'equipo') {
        return in_array($pagina, $paginas_equipo);
    }

    return false;
}

/**
 * Redirigir según tipo de usuario
 */
function redirigir_dashboard() {
    if (!verificar_sesion()) {
        header("Location: login.php");
        exit();
    }

    if (es_admin()) {
        header("Location: dashboard_admin.php");
    } elseif (es_equipo()) {
        header("Location: dashboard_equipo.php");
    } else {
        header("Location: login.php");
    }
    exit();
}

/**
 * Actualizar datos de sesión
 */
function actualizar_sesion($campo, $valor) {
    if (verificar_sesion()) {
        $_SESSION[$campo] = $valor;
        return true;
    }
    return false;
}

/**
 * Obtener tiempo restante de sesión (en segundos)
 */
function tiempo_sesion_restante() {
    if (verificar_sesion() && isset($_SESSION['ultima_actividad'])) {
        $tiempo_inactivo = time() - $_SESSION['ultima_actividad'];
        $tiempo_maximo = 3600; // 1 hora
        return max(0, $tiempo_maximo - $tiempo_inactivo);
    }
    return 0;
}

/**
 * Actualizar última actividad
 */
function actualizar_actividad() {
    if (verificar_sesion()) {
        $_SESSION['ultima_actividad'] = time();
    }
}

/**
 * Verificar timeout de sesión
 */
function verificar_timeout() {
    if (verificar_sesion()) {
        if (isset($_SESSION['ultima_actividad'])) {
            $tiempo_inactivo = time() - $_SESSION['ultima_actividad'];

            // Timeout de 1 hora (3600 segundos)
            if ($tiempo_inactivo > 3600) {
                destruir_sesion();
                return false;
            }
        }

        actualizar_actividad();
        return true;
    }
    return false;
}
?>
