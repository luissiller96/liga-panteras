<?php
/**
 * Controlador: Jornada
 * Descripción: Manejo de jornadas
 */

require_once("../config/conexion.php");
require_once("../models/Jornada.php");

$jornada = new Jornada();

$action = $_POST["action"] ?? $_GET["action"] ?? '';

if (!empty($action)) {
    
    switch ($action) {
        
        // ====================================
        // LISTAR JORNADAS
        // ====================================
        case "listar":
            $temporada_id = $_POST['temporada_id'] ?? $_GET['temporada_id'] ?? 0;
            $datos = $jornada->obtener_jornadas_por_temporada($temporada_id);
            $output = array();
            
            foreach ($datos as $row) {
                $sub_array = array();
                $sub_array[] = $row["jornada_id"];
                $sub_array[] = '<span class="badge bg-primary fs-6">Jornada ' . $row["jornada_numero"] . '</span>';
                $sub_array[] = date('d/m/Y', strtotime($row["jornada_fecha"]));
                $sub_array[] = $row["total_partidos"];
                $sub_array[] = $row["partidos_finalizados"];
                
                // Progreso
                if ($row["total_partidos"] > 0) {
                    $porcentaje = ($row["partidos_finalizados"] / $row["total_partidos"]) * 100;
                    $sub_array[] = '<div class="progress" style="height: 25px;">
                                      <div class="progress-bar" role="progressbar" style="width: ' . $porcentaje . '%;" aria-valuenow="' . $porcentaje . '" aria-valuemin="0" aria-valuemax="100">' . round($porcentaje) . '%</div>
                                    </div>';
                } else {
                    $sub_array[] = '<span class="badge bg-secondary">Sin partidos</span>';
                }
                
                // Estatus
                $estatus_badge = '';
                switch ($row["jornada_estatus"]) {
                    case 'programada':
                        $estatus_badge = '<span class="badge bg-secondary">Programada</span>';
                        break;
                    case 'en_curso':
                        $estatus_badge = '<span class="badge bg-warning">En Curso</span>';
                        break;
                    case 'finalizada':
                        $estatus_badge = '<span class="badge bg-success">Finalizada</span>';
                        break;
                }
                $sub_array[] = $estatus_badge;
                
                $sub_array[] = '<button class="btn btn-info btn-sm" onclick="verPartidos(' . $row["jornada_id"] . ')" title="Ver Partidos"><i class="fas fa-futbol"></i></button>
                                <button class="btn btn-primary btn-sm" onclick="verEstadisticas(' . $row["jornada_id"] . ')" title="Ver Estadísticas"><i class="fas fa-chart-bar"></i></button>
                                <button class="btn btn-warning btn-sm" onclick="editar(' . $row["jornada_id"] . ')" title="Editar"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-danger btn-sm" onclick="eliminar(' . $row["jornada_id"] . ')" title="Eliminar"><i class="fas fa-trash"></i></button>';
                $output[] = $sub_array;
            }
            
            echo json_encode([
                "data" => $output
            ]);
            break;
        
        // ====================================
        // LISTAR JORNADAS PARA SELECT
        // ====================================
        case "listar_select":
            $temporada_id = $_POST['temporada_id'] ?? $_GET['temporada_id'] ?? 0;
            $datos = $jornada->obtener_jornadas_por_temporada($temporada_id);
            echo json_encode($datos);
            break;
        
        // ====================================
        // OBTENER JORNADA POR ID
        // ====================================
        case "obtener":
            $jornada_id = $_POST['jornada_id'] ?? $_GET['jornada_id'] ?? 0;
            $datos = $jornada->obtener_jornada_por_id($jornada_id);
            echo json_encode($datos);
            break;
        
        // ====================================
        // OBTENER JORNADA ACTUAL
        // ====================================
        case "obtener_actual":
            $temporada_id = $_POST['temporada_id'] ?? $_GET['temporada_id'] ?? 0;
            $datos = $jornada->obtener_jornada_actual($temporada_id);
            echo json_encode($datos);
            break;
        
        // ====================================
        // CREAR JORNADA
        // ====================================
        case "crear":
            $datos = [
                'temporada_id' => $_POST['temporada_id'],
                'jornada_numero' => $_POST['jornada_numero'],
                'jornada_fecha' => $_POST['jornada_fecha'],
                'jornada_estatus' => $_POST['jornada_estatus'] ?? 'programada'
            ];
            
            $resultado = $jornada->crear_jornada($datos);
            
            if ($resultado) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Jornada creada correctamente",
                    "id" => $resultado
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Error al crear jornada"
                ]);
            }
            break;
        
        // ====================================
        // ACTUALIZAR JORNADA
        // ====================================
        case "actualizar":
            $jornada_id = $_POST['jornada_id'];
            $datos = [
                'jornada_fecha' => $_POST['jornada_fecha'],
                'jornada_estatus' => $_POST['jornada_estatus']
            ];
            
            $resultado = $jornada->actualizar_jornada($jornada_id, $datos);
            
            if ($resultado) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Jornada actualizada correctamente"
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Error al actualizar jornada"
                ]);
            }
            break;
        
        // ====================================
        // CAMBIAR ESTATUS
        // ====================================
        case "cambiar_estatus":
            $jornada_id = $_POST['jornada_id'];
            $estatus = $_POST['estatus'];
            
            $resultado = $jornada->cambiar_estatus($jornada_id, $estatus);
            
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
        // GENERAR JORNADAS AUTOMÁTICAMENTE
        // ====================================
        case "generar_jornadas":
            $temporada_id = $_POST['temporada_id'];
            $num_jornadas = $_POST['num_jornadas'] ?? 8;
            
            $resultado = $jornada->generar_jornadas_temporada($temporada_id, $num_jornadas);
            
            if ($resultado) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Jornadas generadas correctamente"
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Error al generar jornadas"
                ]);
            }
            break;
        
        // ====================================
        // OBTENER ESTADÍSTICAS DE LA JORNADA
        // ====================================
        case "estadisticas":
            $jornada_id = $_POST['jornada_id'] ?? $_GET['jornada_id'] ?? 0;
            $datos = $jornada->obtener_estadisticas_jornada($jornada_id);
            echo json_encode($datos);
            break;
        
        // ====================================
        // ELIMINAR JORNADA
        // ====================================
        case "eliminar":
            $jornada_id = $_POST['jornada_id'];
            $resultado = $jornada->eliminar_jornada($jornada_id);
            
            if ($resultado) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Jornada eliminada correctamente"
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "No se puede eliminar una jornada con partidos registrados"
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