<?php
/**
 * Controlador: Posicion
 * Descripción: Tabla de posiciones y estadísticas
 */

require_once("../config/conexion.php");
require_once("../models/Posicion.php");

$posicion = new Posicion();

$action = $_POST["action"] ?? $_GET["action"] ?? '';

if (!empty($action)) {
    
    switch ($action) {
        
        // ====================================
        // OBTENER TABLA DE POSICIONES
        // ====================================
        case "tabla":
            $temporada_id = $_POST['temporada_id'] ?? $_GET['temporada_id'] ?? 0;
            $datos = $posicion->obtener_tabla_posiciones($temporada_id);
            
            // Agregar posición numérica
            $posicion_num = 1;
            foreach ($datos as &$row) {
                $row['posicion'] = $posicion_num++;
            }
            
            echo json_encode($datos);
            break;
        
        // ====================================
        // TABLA DE POSICIONES PARA DATATABLE
        // ====================================
        case "listar":
            $temporada_id = $_POST['temporada_id'] ?? $_GET['temporada_id'] ?? 0;
            $datos = $posicion->obtener_tabla_posiciones($temporada_id);
            $output = array();
            
            $posicion_num = 1;
            foreach ($datos as $row) {
                $sub_array = array();
                
                // Posición con color según clasificación
                $pos_class = '';
                if ($posicion_num <= 8) {
                    $pos_class = 'bg-success text-white'; // Clasifican a playoffs
                }
                $sub_array[] = '<span class="badge ' . $pos_class . ' fs-6">' . $posicion_num . '</span>';
                
                // Logo y nombre del equipo
                $equipo_html = '<div class="d-flex align-items-center">';
                if (!empty($row["equipo_logo"])) {
                    $equipo_html .= '<img src="../assets/equipos/' . $row["equipo_logo"] . '" width="30" height="30" class="rounded-circle me-2">';
                }
                $equipo_html .= '<strong>' . htmlspecialchars($row["equipo_nombre"]) . '</strong>';
                $equipo_html .= '</div>';
                $sub_array[] = $equipo_html;
                
                $sub_array[] = $row["partidos_jugados"];
                $sub_array[] = $row["partidos_ganados"];
                $sub_array[] = $row["partidos_empatados"];
                $sub_array[] = $row["partidos_perdidos"];
                $sub_array[] = $row["goles_favor"];
                $sub_array[] = $row["goles_contra"];
                $sub_array[] = '<span class="badge bg-' . ($row["diferencia_goles"] >= 0 ? 'success' : 'danger') . '">' . ($row["diferencia_goles"] >= 0 ? '+' : '') . $row["diferencia_goles"] . '</span>';
                $sub_array[] = '<strong>' . $row["puntos"] . '</strong>';
                
                $sub_array[] = '<button class="btn btn-info btn-sm" onclick="verEquipo(' . $row["equipo_id"] . ')" title="Ver Equipo"><i class="fas fa-eye"></i></button>';
                
                $output[] = $sub_array;
                $posicion_num++;
            }
            
            echo json_encode([
                "data" => $output
            ]);
            break;
        
        // ====================================
        // OBTENER POSICIÓN DE UN EQUIPO
        // ====================================
        case "obtener_equipo":
            $equipo_id = $_POST['equipo_id'] ?? $_GET['equipo_id'] ?? 0;
            $datos = $posicion->obtener_posicion_equipo($equipo_id);
            
            if ($datos) {
                // Agregar posición numérica
                $datos['posicion_numerica'] = $posicion->obtener_posicion_numerica($equipo_id);
            }
            
            echo json_encode($datos);
            break;
        
        // ====================================
        // RECALCULAR TABLA COMPLETA
        // ====================================
        case "recalcular":
            $temporada_id = $_POST['temporada_id'];
            $resultado = $posicion->recalcular_tabla_completa($temporada_id);
            
            if ($resultado) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Tabla de posiciones recalculada correctamente"
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Error al recalcular tabla"
                ]);
            }
            break;
        
        // ====================================
        // OBTENER TOP 8 CLASIFICADOS A PLAYOFFS
        // ====================================
        case "clasificados_playoffs":
            $temporada_id = $_POST['temporada_id'] ?? $_GET['temporada_id'] ?? 0;
            $datos = $posicion->obtener_clasificados_playoffs($temporada_id);
            echo json_encode($datos);
            break;
        
        // ====================================
        // OBTENER ESTADÍSTICAS DE LA TEMPORADA
        // ====================================
        case "estadisticas_temporada":
            $temporada_id = $_POST['temporada_id'] ?? $_GET['temporada_id'] ?? 0;
            $datos = $posicion->obtener_estadisticas_temporada($temporada_id);
            echo json_encode($datos);
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