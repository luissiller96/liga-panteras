<?php
/**
 * Controlador: Usuario
 * Descripción: Manejo de usuarios del sistema
 */

require_once("../config/conexion.php");
require_once("../models/Usuario.php");
require_once("../includes/session_manager.php");

$usuario = new Usuario();

// Determinar la acción
$action = $_POST["action"] ?? $_GET["action"] ?? '';

if (!empty($action)) {
    
    switch ($action) {
        
        // ====================================
        // LOGIN
        // ====================================
        case "login":
            $correo = $_POST['correo'] ?? '';
            $password = $_POST['password'] ?? '';
            
            if (empty($correo) || empty($password)) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Correo y contraseña son requeridos"
                ]);
                break;
            }
            
            $resultado = $usuario->login($correo, $password);

            if (count($resultado) > 0) {
                // Usar session_manager para iniciar la sesión
                iniciar_sesion($resultado[0]);

                echo json_encode([
                    "status" => "success",
                    "message" => "Login exitoso",
                    "data" => $resultado[0]
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Credenciales incorrectas"
                ]);
            }
            break;
        
        // ====================================
        // LOGOUT / SALIR
        // ====================================
        case "logout":
        case "salir":
            // Incluir gestor de sesiones si no está incluido
            if (!function_exists('destruir_sesion')) {
                require_once("../includes/session_manager.php");
            }

            // Destruir la sesión
            destruir_sesion();

            // Si es una petición AJAX, devolver JSON
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                echo json_encode([
                    "status" => "success",
                    "message" => "Sesión cerrada correctamente"
                ]);
            } else {
                // Si es una redirección directa, ir al login
                header("Location: ../pages/login.php");
                exit();
            }
            break;
        
        // ====================================
        // LISTAR USUARIOS
        // ====================================
        case "listar":
            $datos = $usuario->obtener_usuarios();
            $output = array();
            
            foreach ($datos as $row) {
                $sub_array = array();
                $sub_array[] = $row["usu_id"];
                $sub_array[] = htmlspecialchars($row["usu_nom"] . ' ' . ($row["usu_ape"] ?? ''));
                $sub_array[] = htmlspecialchars($row["usu_correo"] ?? '');
                $sub_array[] = ($row["usu_tipo"] == 'admin') ? 
                    '<span class="badge bg-danger">Administrador</span>' : 
                    '<span class="badge bg-info">Equipo</span>';
                $sub_array[] = htmlspecialchars($row["equipo_nombre"] ?? 'N/A');
                $sub_array[] = '<button class="btn btn-warning btn-sm" onclick="editar(' . $row["usu_id"] . ')"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-danger btn-sm" onclick="eliminar(' . $row["usu_id"] . ')"><i class="fas fa-trash"></i></button>';
                $output[] = $sub_array;
            }
            
            echo json_encode([
                "data" => $output
            ]);
            break;
        
        // ====================================
        // OBTENER USUARIO POR ID
        // ====================================
        case "obtener":
            $usu_id = $_POST['usu_id'] ?? $_GET['usu_id'] ?? 0;
            $datos = $usuario->obtener_usuario_por_id($usu_id);
            echo json_encode($datos);
            break;
        
        // ====================================
        // CREAR USUARIO
        // ====================================
        case "crear":
            $datos = [
                'usu_nom' => $_POST['usu_nom'],
                'usu_ape' => $_POST['usu_ape'] ?? '',
                'usu_correo' => $_POST['usu_correo'],
                'usu_pass' => $_POST['usu_pass'],
                'usu_tipo' => $_POST['usu_tipo'],
                'equipo_id' => $_POST['equipo_id'] ?? null,
                'usu_photoprofile' => $_POST['usu_photoprofile'] ?? null
            ];
            
            // Verificar si el correo ya existe
            if ($usuario->verificar_correo_existente($datos['usu_correo'])) {
                echo json_encode([
                    "status" => "error",
                    "message" => "El correo electrónico ya está registrado"
                ]);
                break;
            }
            
            $resultado = $usuario->crear_usuario($datos);
            
            if ($resultado) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Usuario creado correctamente",
                    "id" => $resultado
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Error al crear usuario"
                ]);
            }
            break;
        
        // ====================================
        // ACTUALIZAR USUARIO
        // ====================================
        case "actualizar":
            $usu_id = $_POST['usu_id'];
            $datos = [
                'usu_nom' => $_POST['usu_nom'],
                'usu_ape' => $_POST['usu_ape'] ?? '',
                'usu_correo' => $_POST['usu_correo'],
                'usu_tipo' => $_POST['usu_tipo'],
                'equipo_id' => $_POST['equipo_id'] ?? null,
                'usu_photoprofile' => $_POST['usu_photoprofile'] ?? null
            ];
            
            // Verificar si el correo ya existe (excluyendo el actual)
            if ($usuario->verificar_correo_existente($datos['usu_correo'], $usu_id)) {
                echo json_encode([
                    "status" => "error",
                    "message" => "El correo electrónico ya está registrado"
                ]);
                break;
            }
            
            $resultado = $usuario->actualizar_usuario($usu_id, $datos);
            
            if ($resultado) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Usuario actualizado correctamente"
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Error al actualizar usuario"
                ]);
            }
            break;
        
        // ====================================
        // CAMBIAR CONTRASEÑA
        // ====================================
        case "cambiar_password":
            $usu_id = $_POST['usu_id'];
            $password_actual = $_POST['password_actual'];
            $password_nuevo = $_POST['password_nuevo'];
            
            $resultado = $usuario->cambiar_password($usu_id, $password_actual, $password_nuevo);
            
            if ($resultado) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Contraseña actualizada correctamente"
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "La contraseña actual es incorrecta"
                ]);
            }
            break;
        
        // ====================================
        // ELIMINAR USUARIO
        // ====================================
        case "eliminar":
            $usu_id = $_POST['usu_id'];
            $resultado = $usuario->desactivar_usuario($usu_id);
            
            if ($resultado) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Usuario eliminado correctamente"
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Error al eliminar usuario"
                ]);
            }
            break;
        
        default:
            echo json_encode([
                "status" => "error",
                "message" => "Acción no válida"
            ]);
            break;
    }
} else {
    echo json_encode([
        "status" => "error",
        "message" => "No se especificó ninguna acción"
    ]);
}
?>