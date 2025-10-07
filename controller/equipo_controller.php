<?php
/**
 * Controlador: Equipo
 * Descripción: Manejo de equipos
 */

require_once("../config/conexion.php");
require_once("../models/Equipo.php");

$equipo = new Equipo();

$action = $_POST["action"] ?? $_GET["action"] ?? '';

if (!empty($action)) {
    
    switch ($action) {
        
        // ====================================
        // LISTAR EQUIPOS
        // ====================================
        case "listar":
            $temporada_id = $_POST['temporada_id'] ?? $_GET['temporada_id'] ?? null;
            $liga_id = $_POST['liga_id'] ?? $_GET['liga_id'] ?? null;
            $activo = $_POST['activo'] ?? $_GET['activo'] ?? null;

            // CORREGIDO: obtener_equipos (antes era obtener_equipos_por_temporada)
            if ($temporada_id) {
                $datos = $equipo->obtener_equipos($temporada_id);
            } else {
                $datos = $equipo->obtener_equipos();
            }

            // Aplicar filtros adicionales
            if ($liga_id || $activo !== null) {
                $datos = array_filter($datos, function($row) use ($liga_id, $activo) {
                    $cumple = true;
                    if ($liga_id && $row['liga_id'] != $liga_id) {
                        $cumple = false;
                    }
                    if ($activo !== null && $row['equipo_estatus'] != $activo) {
                        $cumple = false;
                    }
                    return $cumple;
                });
            }

            // Formatear datos para el JS
            $output = array();
            foreach ($datos as $row) {
                $equipo_data = [
                    'equipo_id' => $row['equipo_id'],
                    'equipo_nombre' => $row['equipo_nombre'],
                    'liga_id' => $row['liga_id'] ?? null,
                    'liga_nombre' => $row['liga_nombre'] ?? '',
                    'temporada_id' => $row['temporada_id'],
                    'temporada_nombre' => $row['temporada_nombre'] ?? '',
                    'capitan' => $row['capitan_nombre'] ?? '',
                    'capitan_telefono' => $row['capitan_telefono'] ?? '',
                    'capitan_correo' => $row['capitan_correo'] ?? '',
                    'color_uniforme' => $row['equipo_color_uniforme'] ?? '',
                    'observaciones' => $row['equipo_observaciones'] ?? '',
                    'activo' => $row['equipo_estatus'],
                    'total_jugadores' => $row['total_jugadores'] ?? 0,
                    'logo' => !empty($row['equipo_logo']) ? '../assets/equipos/' . $row['equipo_logo'] : null
                ];
                $output[] = $equipo_data;
            }

            echo json_encode([
                "status" => "success",
                "data" => $output
            ]);
            break;
        
        // ====================================
        // LISTAR EQUIPOS PARA SELECT
        // ====================================
        case "listar_select":
            $temporada_id = $_POST['temporada_id'] ?? $_GET['temporada_id'] ?? 0;
            
            // CORREGIDO: obtener_equipos (antes era obtener_equipos_por_temporada)
            $datos = $equipo->obtener_equipos($temporada_id);
            echo json_encode($datos);
            break;
        
        // ====================================
        // OBTENER EQUIPO POR ID
        // ====================================
        case "obtener":
            $equipo_id = $_POST['equipo_id'] ?? $_GET['equipo_id'] ?? 0;
            $datos = $equipo->obtener_equipo_por_id($equipo_id);

            if ($datos) {
                // Formatear datos para el formulario
                $equipo_data = [
                    'equipo_id' => $datos['equipo_id'],
                    'equipo_nombre' => $datos['equipo_nombre'],
                    'liga_id' => $datos['liga_id'] ?? null,
                    'temporada_id' => $datos['temporada_id'],
                    'capitan' => $datos['capitan_nombre'] ?? '',
                    'capitan_telefono' => $datos['capitan_telefono'] ?? '',
                    'capitan_correo' => $datos['capitan_correo'] ?? '',
                    'color_uniforme' => $datos['equipo_color_uniforme'] ?? '#1a1a2e',
                    'observaciones' => $datos['equipo_observaciones'] ?? '',
                    'activo' => $datos['equipo_estatus'],
                    'logo' => !empty($datos['equipo_logo']) ? '../assets/equipos/' . $datos['equipo_logo'] : null
                ];

                echo json_encode([
                    "status" => "success",
                    "data" => $equipo_data
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Equipo no encontrado"
                ]);
            }
            break;
        
        // ====================================
        // CREAR/INSERTAR EQUIPO
        // ====================================
        case "crear":
        case "insertar":
            // Manejar upload de logo
            $logo_nombre = null;
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
                $directorio = "../assets/equipos/";
                if (!file_exists($directorio)) {
                    mkdir($directorio, 0777, true);
                }

                $extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
                $logo_nombre = 'equipo_' . time() . '_' . uniqid() . '.' . $extension;
                $ruta_destino = $directorio . $logo_nombre;

                if (!move_uploaded_file($_FILES['logo']['tmp_name'], $ruta_destino)) {
                    $logo_nombre = null;
                }
            }

            $datos = [
                'temporada_id' => $_POST['temporada_id'],
                'equipo_nombre' => $_POST['equipo_nombre'],
                'equipo_logo' => $logo_nombre,
                'equipo_color_uniforme' => $_POST['color_uniforme'] ?? '#1a1a2e',
                'capitan_nombre' => $_POST['capitan'] ?? '',
                'capitan_telefono' => $_POST['capitan_telefono'] ?? '',
                'capitan_correo' => $_POST['capitan_correo'] ?? ''
            ];

            // CORREGIDO: verificar_nombre_existe (antes era verificar_nombre_existente)
            if ($equipo->verificar_nombre_existe($datos['equipo_nombre'], $datos['temporada_id'])) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Ya existe un equipo con ese nombre en esta temporada"
                ]);
                break;
            }

            $resultado = $equipo->crear_equipo($datos);

            if ($resultado) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Equipo creado correctamente",
                    "id" => $resultado
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Error al crear equipo"
                ]);
            }
            break;
        
        // ====================================
        // ACTUALIZAR EQUIPO
        // ====================================
        case "actualizar":
            $equipo_id = $_POST['equipo_id'];

            // Manejar upload de nuevo logo
            $logo_nombre = null;
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
                $directorio = "../assets/equipos/";
                if (!file_exists($directorio)) {
                    mkdir($directorio, 0777, true);
                }

                $extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
                $logo_nombre = 'equipo_' . time() . '_' . uniqid() . '.' . $extension;
                $ruta_destino = $directorio . $logo_nombre;

                if (move_uploaded_file($_FILES['logo']['tmp_name'], $ruta_destino)) {
                    // Eliminar logo anterior si existe
                    $equipo_anterior = $equipo->obtener_equipo_por_id($equipo_id);
                    if ($equipo_anterior && !empty($equipo_anterior['equipo_logo'])) {
                        $logo_anterior = "../assets/equipos/" . $equipo_anterior['equipo_logo'];
                        if (file_exists($logo_anterior)) {
                            unlink($logo_anterior);
                        }
                    }
                } else {
                    $logo_nombre = null;
                }
            }

            // Obtener equipo actual para preservar el logo si no se sube uno nuevo
            if (!$logo_nombre) {
                $equipo_actual = $equipo->obtener_equipo_por_id($equipo_id);
                $logo_nombre = $equipo_actual['equipo_logo'] ?? null;
            }

            $datos = [
                'temporada_id' => $_POST['temporada_id'],
                'equipo_nombre' => $_POST['equipo_nombre'],
                'equipo_logo' => $logo_nombre,
                'equipo_color_uniforme' => $_POST['color_uniforme'] ?? '#1a1a2e',
                'capitan_nombre' => $_POST['capitan'] ?? '',
                'capitan_telefono' => $_POST['capitan_telefono'] ?? '',
                'capitan_correo' => $_POST['capitan_correo'] ?? ''
            ];

            // CORREGIDO: verificar_nombre_existe con excluir_id
            if ($equipo->verificar_nombre_existe($datos['equipo_nombre'], $datos['temporada_id'], $equipo_id)) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Ya existe otro equipo con ese nombre en esta temporada"
                ]);
                break;
            }

            $resultado = $equipo->actualizar_equipo($equipo_id, $datos);

            if ($resultado) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Equipo actualizado correctamente"
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Error al actualizar equipo"
                ]);
            }
            break;
        
        // ====================================
        // CAMBIAR ESTATUS
        // ====================================
        case "cambiar_estatus":
            $equipo_id = $_POST['equipo_id'];
            $estatus = $_POST['estatus'];
            
            $resultado = $equipo->cambiar_estatus($equipo_id, $estatus);
            
            if ($resultado) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Estatus actualizado correctamente"
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Error al actualizar estatus"
                ]);
            }
            break;
        
        // ====================================
        // OBTENER ESTADÍSTICAS DEL EQUIPO
        // ====================================
        case "estadisticas":
            $equipo_id = $_POST['equipo_id'] ?? $_GET['equipo_id'] ?? 0;
            $datos = $equipo->obtener_estadisticas_equipo($equipo_id);
            echo json_encode($datos);
            break;
        
        // ====================================
        // OBTENER JUGADORES DEL EQUIPO
        // ====================================
        case "obtener_jugadores":
            $equipo_id = $_POST['equipo_id'] ?? $_GET['equipo_id'] ?? 0;
            $datos = $equipo->obtener_jugadores_equipo($equipo_id);
            echo json_encode($datos);
            break;
        
        // ====================================
        // ELIMINAR EQUIPO
        // ====================================
        case "eliminar":
            $equipo_id = $_POST['equipo_id'];
            
            // CORREGIDO: usar eliminar_equipo en lugar de desactivar_equipo
            $resultado = $equipo->eliminar_equipo($equipo_id);
            
            if ($resultado) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Equipo eliminado correctamente"
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "No se puede eliminar un equipo con jugadores o partidos registrados"
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