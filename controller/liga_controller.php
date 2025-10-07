<?php
/**
 * Controlador: Liga
 * Descripción: Manejo de ligas/categorías
 */

require_once("../config/conexion.php");
require_once("../models/Liga.php");

$liga = new Liga();

$action = $_POST["action"] ?? $_GET["action"] ?? '';

if (!empty($action)) {
    
    switch ($action) {
        
        // ====================================
        // LISTAR LIGAS PARA SELECT
        // ====================================
        case "listar_select":
            $datos = $liga->obtener_ligas();
            echo json_encode($datos);
            break;
        
        // ====================================
        // LISTAR LIGAS PARA DATATABLE
        // ====================================
        case "listar":
            $datos = $liga->obtener_ligas_con_temporadas();
            $output = array();
            
            foreach ($datos as $row) {
                $sub_array = array();
                $sub_array[] = $row["liga_id"];
                
                // Logo
                if (!empty($row["liga_logo"])) {
                    $sub_array[] = '<img src="../assets/logos/' . $row["liga_logo"] . '" width="50" height="50" class="rounded">';
                } else {
                    $sub_array[] = '<i class="fas fa-trophy fa-2x text-secondary"></i>';
                }
                
                $sub_array[] = htmlspecialchars($row["liga_nombre"]);
                $sub_array[] = '<span class="badge bg-primary">' . ucfirst($row["liga_dia_juego"]) . '</span>';
                $sub_array[] = $row["total_temporadas"] ?? 0;
                $sub_array[] = $row["temporada_actual"] ?? 'N/A';
                $sub_array[] = ($row["liga_estatus"] == 1) ? 
                    '<span class="badge bg-success">Activa</span>' : 
                    '<span class="badge bg-secondary">Inactiva</span>';
                $sub_array[] = '<button class="btn btn-warning btn-sm" onclick="editar(' . $row["liga_id"] . ')"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-danger btn-sm" onclick="eliminar(' . $row["liga_id"] . ')"><i class="fas fa-trash"></i></button>';
                $output[] = $sub_array;
            }
            
            echo json_encode([
                "data" => $output
            ]);
            break;
        
        // ====================================
        // OBTENER LIGA POR ID
        // ====================================
        case "obtener":
            $liga_id = $_POST['liga_id'] ?? $_GET['liga_id'] ?? 0;
            $datos = $liga->obtener_liga_por_id($liga_id);
            echo json_encode($datos);
            break;
        
        // ====================================
        // CREAR LIGA
        // ====================================
        case "crear":
            $datos = [
                'liga_nombre' => $_POST['liga_nombre'],
                'liga_descripcion' => $_POST['liga_descripcion'] ?? '',
                'liga_dia_juego' => $_POST['liga_dia_juego'],
                'liga_logo' => $_POST['liga_logo'] ?? null
            ];
            
            // CORREGIDO: verificar_nombre_existe (antes era verificar_nombre_existente)
            if ($liga->verificar_nombre_existe($datos['liga_nombre'])) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Ya existe una liga con ese nombre"
                ]);
                break;
            }
            
            $resultado = $liga->crear_liga($datos);
            
            if ($resultado) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Liga creada correctamente",
                    "id" => $resultado
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Error al crear liga"
                ]);
            }
            break;
        
        // ====================================
        // ACTUALIZAR LIGA
        // ====================================
        case "actualizar":
            $liga_id = $_POST['liga_id'];
            $datos = [
                'liga_nombre' => $_POST['liga_nombre'],
                'liga_descripcion' => $_POST['liga_descripcion'] ?? '',
                'liga_dia_juego' => $_POST['liga_dia_juego'],
                'liga_logo' => $_POST['liga_logo'] ?? null
            ];
            
            // CORREGIDO: verificar_nombre_existe (antes era verificar_nombre_existente)
            if ($liga->verificar_nombre_existe($datos['liga_nombre'], $liga_id)) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Ya existe una liga con ese nombre"
                ]);
                break;
            }
            
            $resultado = $liga->actualizar_liga($liga_id, $datos);
            
            if ($resultado) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Liga actualizada correctamente"
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Error al actualizar liga"
                ]);
            }
            break;
        
        // ====================================
        // CAMBIAR ESTATUS LIGA
        // ====================================
        case "cambiar_estatus":
            $liga_id = $_POST['liga_id'];
            $estatus = $_POST['estatus'];
            
            $resultado = $liga->cambiar_estatus($liga_id, $estatus);
            
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
        // ELIMINAR LIGA
        // ====================================
        case "eliminar":
            $liga_id = $_POST['liga_id'];
            
            // CORREGIDO: usar eliminar_liga en lugar de desactivar_liga
            $resultado = $liga->eliminar_liga($liga_id);
            
            if ($resultado) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Liga eliminada correctamente"
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "No se puede eliminar una liga con temporadas registradas"
                ]);
            }
            break;
        
        // ====================================
        // OBTENER ESTADÍSTICAS
        // ====================================
        case "estadisticas":
            $liga_id = $_POST['liga_id'] ?? $_GET['liga_id'] ?? 0;
            $datos = $liga->obtener_estadisticas_liga($liga_id);
            echo json_encode($datos);
            break;
        
        // ====================================
        // OBTENER DÍAS DE JUEGO
        // ====================================
        case "obtener_dias_juego":
            $dias = $liga->obtener_dias_juego();
            echo json_encode($dias);
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