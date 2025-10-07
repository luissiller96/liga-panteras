<?php
/**
 * Controlador: Jugador
 * Descripción: Manejo de jugadores
 */

require_once("../config/conexion.php");
require_once("../models/Jugador.php");

$jugador = new Jugador();

$action = $_POST["action"] ?? $_GET["action"] ?? '';

if (!empty($action)) {
    
    switch ($action) {
        
        // ====================================
        // LISTAR JUGADORES
        // ====================================
        case "listar":
            $equipo_id = $_POST['equipo_id'] ?? $_GET['equipo_id'] ?? null;
            
            if ($equipo_id) {
                $datos = $jugador->obtener_jugadores($equipo_id);
            } else {
                $datos = $jugador->obtener_jugadores();
            }
            
            $output = array();
            
            foreach ($datos as $row) {
                $sub_array = array();
                $sub_array[] = $row["jugador_id"];
                
                // Foto
                if (!empty($row["jugador_foto"])) {
                    $sub_array[] = '<img src="../assets/jugadores/' . $row["jugador_foto"] . '" width="40" height="40" class="rounded-circle">';
                } else {
                    $sub_array[] = '<i class="fas fa-user-circle fa-2x text-secondary"></i>';
                }
                
                $sub_array[] = htmlspecialchars($row["jugador_nombre"]);
                $sub_array[] = '<span class="badge bg-dark">#' . ($row["jugador_numero"] ?? 'S/N') . '</span>';
                $sub_array[] = htmlspecialchars($row["jugador_posicion"] ?? 'N/A');
                
                // Edad
                if (!empty($row["fecha_nacimiento"])) {
                    $fecha_nac = new DateTime($row["fecha_nacimiento"]);
                    $hoy = new DateTime();
                    $edad = $hoy->diff($fecha_nac)->y;
                    $sub_array[] = $edad . ' años';
                } else {
                    $sub_array[] = 'N/A';
                }
                
                $sub_array[] = htmlspecialchars($row["equipo_nombre"] ?? '');
                $sub_array[] = $row["total_goles"] ?? 0;
                $sub_array[] = '<span class="badge bg-warning text-dark">' . ($row["tarjetas_amarillas"] ?? 0) . '</span>';
                $sub_array[] = '<span class="badge bg-danger">' . ($row["tarjetas_rojas"] ?? 0) . '</span>';
                
                $sub_array[] = '<button class="btn btn-info btn-sm" onclick="verEstadisticas(' . $row["jugador_id"] . ')" title="Ver Estadísticas"><i class="fas fa-chart-line"></i></button>
                                <button class="btn btn-warning btn-sm" onclick="editar(' . $row["jugador_id"] . ')" title="Editar"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-danger btn-sm" onclick="eliminar(' . $row["jugador_id"] . ')" title="Eliminar"><i class="fas fa-trash"></i></button>';
                $output[] = $sub_array;
            }
            
            echo json_encode([
                "data" => $output
            ]);
            break;
        
        // ====================================
        // LISTAR JUGADORES PARA SELECT
        // ====================================
        case "listar_select":
            $equipo_id = $_POST['equipo_id'] ?? $_GET['equipo_id'] ?? 0;
            $datos = $jugador->obtener_jugadores($equipo_id);
            echo json_encode($datos);
            break;
        
        // ====================================
        // OBTENER JUGADOR POR ID
        // ====================================
        case "obtener":
            $jugador_id = $_POST['jugador_id'] ?? $_GET['jugador_id'] ?? 0;
            $datos = $jugador->obtener_jugador_por_id($jugador_id);
            echo json_encode($datos);
            break;
        
        // ====================================
        // CREAR JUGADOR
        // ====================================
        case "crear":
            // Manejar upload de foto
            $foto_nombre = null;
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
                $directorio = "../assets/jugadores/";
                if (!file_exists($directorio)) {
                    mkdir($directorio, 0777, true);
                }

                $extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
                $foto_nombre = 'jugador_' . time() . '_' . uniqid() . '.' . $extension;
                $ruta_destino = $directorio . $foto_nombre;

                if (!move_uploaded_file($_FILES['foto']['tmp_name'], $ruta_destino)) {
                    $foto_nombre = null;
                }
            }
            
            $datos = [
                'equipo_id' => $_POST['equipo_id'],
                'jugador_nombre' => $_POST['jugador_nombre'],
                'jugador_numero' => $_POST['jugador_numero'] ?? null,
                'jugador_posicion' => $_POST['jugador_posicion'] ?? null,
                'jugador_foto' => $foto_nombre,
                'fecha_nacimiento' => $_POST['fecha_nacimiento'] ?? null
            ];
            
            // Verificar número de playera disponible
            if (!empty($datos['jugador_numero'])) {
                if ($jugador->verificar_numero_existe($datos['jugador_numero'], $datos['equipo_id'])) {
                    echo json_encode([
                        "status" => "error",
                        "message" => "El número de playera ya está asignado a otro jugador"
                    ]);
                    break;
                }
            }
            
            $resultado = $jugador->crear_jugador($datos);
            
            if ($resultado) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Jugador registrado correctamente",
                    "id" => $resultado
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Error al registrar jugador"
                ]);
            }
            break;
        
        // ====================================
        // ACTUALIZAR JUGADOR
        // ====================================
        case "actualizar":
            $jugador_id = $_POST['jugador_id'];
            
            // Manejar upload de nueva foto
            $foto_nombre = null;
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
                $directorio = "../assets/jugadores/";
                if (!file_exists($directorio)) {
                    mkdir($directorio, 0777, true);
                }

                $extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
                $foto_nombre = 'jugador_' . time() . '_' . uniqid() . '.' . $extension;
                $ruta_destino = $directorio . $foto_nombre;

                if (move_uploaded_file($_FILES['foto']['tmp_name'], $ruta_destino)) {
                    // Eliminar foto anterior si existe
                    $jugador_anterior = $jugador->obtener_jugador_por_id($jugador_id);
                    if ($jugador_anterior && !empty($jugador_anterior['jugador_foto'])) {
                        $foto_anterior = "../assets/jugadores/" . $jugador_anterior['jugador_foto'];
                        if (file_exists($foto_anterior)) {
                            unlink($foto_anterior);
                        }
                    }
                } else {
                    $foto_nombre = null;
                }
            }
            
            // Obtener jugador actual para preservar la foto si no se sube una nueva
            if (!$foto_nombre) {
                $jugador_actual = $jugador->obtener_jugador_por_id($jugador_id);
                $foto_nombre = $jugador_actual['jugador_foto'] ?? null;
            }
            
            $datos = [
                'jugador_nombre' => $_POST['jugador_nombre'],
                'jugador_numero' => $_POST['jugador_numero'] ?? null,
                'jugador_posicion' => $_POST['jugador_posicion'] ?? null,
                'jugador_foto' => $foto_nombre,
                'fecha_nacimiento' => $_POST['fecha_nacimiento'] ?? null
            ];
            
            // Verificar número de playera disponible (excluyendo el actual)
            if (!empty($datos['jugador_numero'])) {
                $jugador_actual = $jugador->obtener_jugador_por_id($jugador_id);
                if ($jugador->verificar_numero_existe($datos['jugador_numero'], $jugador_actual['equipo_id'], $jugador_id)) {
                    echo json_encode([
                        "status" => "error",
                        "message" => "El número de playera ya está asignado a otro jugador"
                    ]);
                    break;
                }
            }
            
            $resultado = $jugador->actualizar_jugador($jugador_id, $datos);
            
            if ($resultado) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Jugador actualizado correctamente"
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Error al actualizar jugador"
                ]);
            }
            break;
        
        // ====================================
        // CAMBIAR ESTATUS
        // ====================================
        case "cambiar_estatus":
            $jugador_id = $_POST['jugador_id'];
            $estatus = $_POST['estatus'];
            
            $resultado = $jugador->cambiar_estatus($jugador_id, $estatus);
            
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
        // OBTENER ESTADÍSTICAS DEL JUGADOR
        // ====================================
        case "estadisticas":
            $jugador_id = $_POST['jugador_id'] ?? $_GET['jugador_id'] ?? 0;
            $datos = $jugador->obtener_estadisticas_jugador($jugador_id);
            echo json_encode($datos);
            break;
        
        // ====================================
        // TABLA DE GOLEO
        // ====================================
        case "tabla_goleo":
            $temporada_id = $_POST['temporada_id'] ?? $_GET['temporada_id'] ?? 0;
            $limite = $_POST['limite'] ?? $_GET['limite'] ?? 10;
            $datos = $jugador->obtener_tabla_goleo($temporada_id, $limite);
            echo json_encode($datos);
            break;
        
        // ====================================
        // ELIMINAR JUGADOR
        // ====================================
        case "eliminar":
            $jugador_id = $_POST['jugador_id'];
            
            // CORREGIDO: usar eliminar_jugador en lugar de desactivar_jugador
            $resultado = $jugador->eliminar_jugador($jugador_id);
            
            if ($resultado) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Jugador eliminado correctamente"
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "No se puede eliminar un jugador con goles o tarjetas registradas"
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