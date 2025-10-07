<?php
/**
 * Controlador: Temporada
 * Descripción: Manejo de temporadas
 */

require_once("../config/conexion.php");
require_once("../models/Temporada.php");
require_once("../models/Jornada.php");

$temporada = new Temporada();
$jornada = new Jornada();

$action = $_POST["action"] ?? $_GET["action"] ?? '';

if (!empty($action)) {
    
    switch ($action) {
        
        // ====================================
        // LISTAR TEMPORADAS
        // ====================================
        case "listar":
            $liga_id = $_POST['liga_id'] ?? $_GET['liga_id'] ?? null;
            
            if ($liga_id) {
                $datos = $temporada->obtener_temporadas_por_liga($liga_id);
            } else {
                $datos = $temporada->obtener_temporadas();
            }
            
            $output = array();
            
            foreach ($datos as $row) {
                $sub_array = array();
                $sub_array[] = $row["temporada_id"];
                $sub_array[] = htmlspecialchars($row["liga_nombre"]);
                $sub_array[] = htmlspecialchars($row["temporada_nombre"]);
                $sub_array[] = date('d/m/Y', strtotime($row["fecha_inicio"]));
                $sub_array[] = date('d/m/Y', strtotime($row["fecha_fin"]));
                $sub_array[] = $row["num_jornadas"];
                $sub_array[] = '$' . number_format($row["costo_inscripcion"], 2);
                
                // Estatus
                $estatus_badge = '';
                switch ($row["temporada_estatus"]) {
                    case 'proxima':
                        $estatus_badge = '<span class="badge bg-info">Próxima</span>';
                        break;
                    case 'en_curso':
                        $estatus_badge = '<span class="badge bg-success">En Curso</span>';
                        break;
                    case 'finalizada':
                        $estatus_badge = '<span class="badge bg-secondary">Finalizada</span>';
                        break;
                }
                $sub_array[] = $estatus_badge;
                
                $sub_array[] = '<button class="btn btn-info btn-sm" onclick="verEstadisticas(' . $row["temporada_id"] . ')" title="Ver Estadísticas"><i class="fas fa-chart-bar"></i></button>
                                <button class="btn btn-warning btn-sm" onclick="editar(' . $row["temporada_id"] . ')" title="Editar"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-danger btn-sm" onclick="eliminar(' . $row["temporada_id"] . ')" title="Eliminar"><i class="fas fa-trash"></i></button>';
                $output[] = $sub_array;
            }
            
            echo json_encode([
                "data" => $output
            ]);
            break;
        
        // ====================================
        // LISTAR TEMPORADAS ACTIVAS (SELECT)
        // ====================================
        case "listar_activas":
            $datos = $temporada->obtener_temporadas_activas();
            echo json_encode($datos);
            break;
        
        // ====================================
        // LISTAR TEMPORADAS POR LIGA (SELECT)
        // ====================================
        case "listar_por_liga":
            $liga_id = $_POST['liga_id'] ?? $_GET['liga_id'] ?? 0;
            $datos = $temporada->obtener_temporadas_por_liga($liga_id);
            echo json_encode($datos);
            break;
        
        // ====================================
        // OBTENER TEMPORADA POR ID
        // ====================================
        case "obtener":
            $temporada_id = $_POST['temporada_id'] ?? $_GET['temporada_id'] ?? 0;
            $datos = $temporada->obtener_temporada_por_id($temporada_id);
            echo json_encode($datos);
            break;
        
        // ====================================
        // CREAR TEMPORADA
        // ====================================
        case "crear":
            $datos = [
                'liga_id' => $_POST['liga_id'],
                'temporada_nombre' => $_POST['temporada_nombre'],
                'fecha_inicio' => $_POST['fecha_inicio'],
                'fecha_fin' => $_POST['fecha_fin'],
                'num_jornadas' => $_POST['num_jornadas'] ?? 8,
                'costo_inscripcion' => $_POST['costo_inscripcion'],
                'temporada_estatus' => $_POST['temporada_estatus'] ?? 'proxima'
            ];
            
            $resultado = $temporada->crear_temporada($datos);
            
            if ($resultado) {
                // Generar jornadas automáticamente
                $jornada->generar_jornadas_temporada($resultado, $datos['num_jornadas']);
                
                echo json_encode([
                    "status" => "success",
                    "message" => "Temporada creada correctamente",
                    "id" => $resultado
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Error al crear temporada"
                ]);
            }
            break;
        
        // ====================================
        // ACTUALIZAR TEMPORADA
        // ====================================
        case "actualizar":
            $temporada_id = $_POST['temporada_id'];
            $datos = [
                'liga_id' => $_POST['liga_id'],
                'temporada_nombre' => $_POST['temporada_nombre'],
                'fecha_inicio' => $_POST['fecha_inicio'],
                'fecha_fin' => $_POST['fecha_fin'],
                'num_jornadas' => $_POST['num_jornadas'] ?? 8,
                'costo_inscripcion' => $_POST['costo_inscripcion'],
                'temporada_estatus' => $_POST['temporada_estatus']
            ];
            
            $resultado = $temporada->actualizar_temporada($temporada_id, $datos);
            
            if ($resultado) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Temporada actualizada correctamente"
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Error al actualizar temporada"
                ]);
            }
            break;
        
        // ====================================
        // CAMBIAR ESTATUS TEMPORADA
        // ====================================
        case "cambiar_estatus":
            $temporada_id = $_POST['temporada_id'];
            $estatus = $_POST['estatus'];
            
            $resultado = $temporada->cambiar_estatus($temporada_id, $estatus);
            
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
        // OBTENER ESTADÍSTICAS
        // ====================================
        case "estadisticas":
            $temporada_id = $_POST['temporada_id'] ?? $_GET['temporada_id'] ?? 0;
            $datos = $temporada->obtener_estadisticas_temporada($temporada_id);
            echo json_encode($datos);
            break;
        
        // ====================================
        // ELIMINAR TEMPORADA
        // ====================================
        case "eliminar":
            $temporada_id = $_POST['temporada_id'];
            $resultado = $temporada->eliminar_temporada($temporada_id);
            
            if ($resultado) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Temporada eliminada correctamente"
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "No se puede eliminar una temporada con equipos registrados"
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