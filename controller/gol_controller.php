<?php
/**
 * Controlador: Gol
 * Descripción: Registro de goles en partidos
 */

require_once("../config/conexion.php");
require_once("../models/Gol.php");

$gol = new Gol();

$action = $_POST["action"] ?? $_GET["action"] ?? '';

if (!empty($action)) {
    
    switch ($action) {
        
        // ====================================
        // OBTENER GOLES DE UN PARTIDO
        // ====================================
        case "listar_partido":
            $partido_id = $_POST['partido_id'] ?? $_GET['partido_id'] ?? 0;
            $datos = $gol->obtener_goles_por_partido($partido_id);
            echo json_encode($datos);
            break;
        
        // ====================================
        // OBTENER GOLES DE UN JUGADOR
        // ====================================
        case "listar_jugador":
            $jugador_id = $_POST['jugador_id'] ?? $_GET['jugador_id'] ?? 0;
            $datos = $gol->obtener_goles_por_jugador($jugador_id);
            echo json_encode($datos);
            break;
        
        // ====================================
        // REGISTRAR GOL
        // ====================================
        case "registrar":
            $datos = [
                'partido_id' => $_POST['partido_id'],
                'jugador_id' => $_POST['jugador_id'],
                'equipo_id' => $_POST['equipo_id'],
                'tipo_gol' => $_POST['tipo_gol'] ?? 'normal'
            ];
            
            $resultado = $gol->registrar_gol($datos);
            
            if ($resultado) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Gol registrado correctamente",
                    "id" => $resultado
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Error al registrar gol"
                ]);
            }
            break;
        
        // ====================================
        // ELIMINAR GOL
        // ====================================
        case "eliminar":
            $gol_id = $_POST['gol_id'];
            $resultado = $gol->eliminar_gol($gol_id);
            
            if ($resultado) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Gol eliminado correctamente"
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Error al eliminar gol"
                ]);
            }
            break;
        
        // ====================================
        // TABLA DE GOLEO
        // ====================================
        case "tabla_goleo":
            $temporada_id = $_POST['temporada_id'] ?? $_GET['temporada_id'] ?? 0;
            $limite = $_POST['limite'] ?? $_GET['limite'] ?? 10;
            $datos = $gol->obtener_tabla_goleo($temporada_id, $limite);
            echo json_encode($datos);
            break;
        
        // ====================================
        // JUGADOR DESTACADO DE LA JORNADA
        // ====================================
        case "jugador_destacado":
            $jornada_id = $_POST['jornada_id'] ?? $_GET['jornada_id'] ?? 0;
            $datos = $gol->obtener_jugador_destacado_jornada($jornada_id);
            echo json_encode($datos);
            break;
        
        // ====================================
        // CONTAR GOLES DE UN JUGADOR
        // ====================================
        case "contar_goles":
            $jugador_id = $_POST['jugador_id'] ?? $_GET['jugador_id'] ?? 0;
            $total = $gol->contar_goles_jugador($jugador_id);
            echo json_encode([
                "total" => $total
            ]);
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