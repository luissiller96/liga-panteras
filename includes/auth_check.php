<?php
/**
 * Auth Check
 * Middleware de autenticación para páginas protegidas
 * Liga Panteras
 */

// Incluir el gestor de sesiones
require_once(__DIR__ . '/session_manager.php');

// Verificar si hay sesión activa
if (!verificar_sesion()) {
    header("Location: login.php");
    exit();
}

// Verificar timeout de sesión
if (!verificar_timeout()) {
    header("Location: login.php?timeout=1");
    exit();
}

// Obtener la página actual
$pagina_actual = basename($_SERVER['PHP_SELF']);
$tipo_usuario = obtener_tipo_usuario();

// Definir páginas permitidas por tipo de usuario
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
    'usuarios.php',
    'perfil.php',
    'configuracion.php'
];

$paginas_equipo = [
    'dashboard_equipo.php',
    'mi_equipo.php',
    'mis_jugadores.php',
    'mis_partidos.php',
    'mis_pagos.php',
    'perfil.php'
];

// Verificar permisos según tipo de usuario
$tiene_acceso = false;

if ($tipo_usuario === 'admin') {
    // Los administradores tienen acceso a todo
    $tiene_acceso = true;
} elseif ($tipo_usuario === 'equipo') {
    // Los equipos solo pueden acceder a sus páginas
    $tiene_acceso = in_array($pagina_actual, $paginas_equipo);
} else {
    // Tipo de usuario desconocido, no tiene acceso
    $tiene_acceso = false;
}

// Si no tiene acceso, redirigir a su dashboard correspondiente
if (!$tiene_acceso) {
    if ($tipo_usuario === 'admin') {
        // Si es admin y no está en una página permitida (caso raro), redirigir a dashboard
        if ($pagina_actual !== 'dashboard_admin.php') {
            header("Location: dashboard_admin.php");
            exit();
        }
    } elseif ($tipo_usuario === 'equipo') {
        // Si es equipo y no tiene acceso, redirigir a dashboard de equipo
        if ($pagina_actual !== 'dashboard_equipo.php') {
            header("Location: dashboard_equipo.php");
            exit();
        }
    } else {
        // Usuario sin tipo válido, cerrar sesión y redirigir a login
        destruir_sesion();
        header("Location: login.php");
        exit();
    }
}

// Obtener datos del usuario para usar en la página
$usuario_actual = obtener_usuario_actual();
$usuario_id = obtener_usuario_id();
$usuario_nombre = obtener_usuario_nombre();
$usuario_tipo = obtener_tipo_usuario();
$equipo_id = obtener_equipo_id();
?>
