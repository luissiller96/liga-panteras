<?php
/**
 * Controlador: Tarjeta
 * Descripción: Registro de tarjetas (amarillas y rojas)
 */

require_once("../config/conexion.php");
require_once("../models/Tarjeta.php");

$tarjeta = new Tarjeta();

$action = $_POST["action"] ?? $_GET["action"] ?? '';

if (!empty($action)) {
    
    switch ($action) {
        
        // ====================================
        // OBTENER TARJETAS DE UN PARTIDO
        // ====================================
        case "listar_partido":
            $partido_id = $_POST['partido_id'] ?? $_GET['partido_id'] ?? 0;
            $datos = $tarjeta->obtener_tarjetas_por_partido($partido_id);
            echo json_encode($datos);
            break;
        
        // ====================================
        // OBTENER TARJETAS DE UN JUGADOR
        // ====================================
        case "listar_jugador":
            $jugador_id = $_POST['jugador_id'] ?? $_GET['jugador_id'] ?? 0;
            $datos = $tarjeta->obtener_tarjetas_por_jugador($jugador_id);
            echo json_encode($datos);
            break;
        
        // ====================================
        // REGISTRAR TARJETA
        // ====================================
        case "registrar":
            $datos = [
                'partido_id' => $_POST['partido_id'],
                'jugador_id' => $_POST['jugador_id'],
                'equipo_id' => $_POST['equipo_id'],
                'tipo_tarjeta' => $_POST['tipo_tarjeta'],
                'motivo' => $_POST['motivo'] ?? ''
            ];
            
            $resultado = $tarjeta->registrar_tarjeta($datos);
            
            if ($resultado) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Tarjeta registrada correctamente",
                    "id" => $resultado
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Error al registrar tarjeta"
                ]);
            }
            break;
        
        // ====================================
        // ELIMINAR TARJETA
        // ====================================
        case "eliminar":
            $tarjeta_id = $_POST['tarjeta_id'];
            $resultado = $tarjeta->eliminar_tarjeta($tarjeta_id);
            
            if ($resultado) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Tarjeta eliminada correctamente"
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Error al eliminar tarjeta"
                ]);
            }
            break;
        
        // ====================================
        // TABLA DE TARJETAS
        // ====================================
        case "tabla_tarjetas":
            $temporada_id = $_POST['temporada_id'] ?? $_GET['temporada_id'] ?? 0;
            $tipo_tarjeta = $_POST['tipo_tarjeta'] ?? $_GET['tipo_tarjeta'] ?? null;
            $limite = $_POST['limite'] ?? $_GET['limite'] ?? 10;
            $datos = $tarjeta->obtener_tabla_tarjetas($temporada_id, $tipo_tarjeta, $limite);
            echo json_encode($datos);
            break;
        
        // ====================================
        // CONTAR TARJETAS DE UN JUGADOR
        // ====================================
        case "contar_tarjetas":
            $jugador_id = $_POST['jugador_id'] ?? $_GET['jugador_id'] ?? 0;
            $tipo = $_POST['tipo'] ?? $_GET['tipo'] ?? null;
            $total = $tarjeta->contar_tarjetas_jugador($jugador_id, $tipo);
            echo json_encode([
                "total" => $total
            ]);
            break;
        
        // ====================================
        // VERIFICAR TARJETA ROJA EN PARTIDO
        // ====================================
        case "verificar_roja":
            $jugador_id = $_POST['jugador_id'] ?? $_GET['jugador_id'] ?? 0;
            $partido_id = $_POST['partido_id'] ?? $_GET['partido_id'] ?? 0;
            $tiene_roja = $tarjeta->tiene_tarjeta_roja_en_partido($jugador_id, $partido_id);
            echo json_encode([
                "tiene_roja" => $tiene_roja
            ]);
            break;
        
        // ====================================
        // ESTADÍSTICAS DE TARJETAS POR EQUIPO
        // ====================================
        case "estadisticas_equipo":
            $equipo_id = $_POST['equipo_id'] ?? $_GET['equipo_id'] ?? 0;
            $datos = $tarjeta->obtener_estadisticas_equipo($equipo_id);
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