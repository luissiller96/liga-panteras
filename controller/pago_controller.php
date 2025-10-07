<?php
/**
 * Controlador: Pago
 * Descripción: Control de pagos de inscripción
 */

require_once("../config/conexion.php");
require_once("../models/Pago.php");

$pago = new Pago();

$action = $_POST["action"] ?? $_GET["action"] ?? '';

if (!empty($action)) {
    
    switch ($action) {
        
        // ====================================
        // LISTAR PAGOS
        // ====================================
        case "listar":
            $temporada_id = $_POST['temporada_id'] ?? $_GET['temporada_id'] ?? null;
            $datos = $pago->obtener_pagos($temporada_id);
            $output = array();
            
            foreach ($datos as $row) {
                $sub_array = array();
                $sub_array[] = $row["pago_id"];
                $sub_array[] = htmlspecialchars($row["liga_nombre"]);
                $sub_array[] = htmlspecialchars($row["temporada_nombre"]);
                $sub_array[] = htmlspecialchars($row["equipo_nombre"]);
                $sub_array[] = htmlspecialchars($row["capitan_nombre"] ?? '');
                $sub_array[] = htmlspecialchars($row["capitan_telefono"] ?? '');
                $sub_array[] = '$' . number_format($row["monto_total"], 2);
                $sub_array[] = '$' . number_format($row["monto_pagado"], 2);
                $sub_array[] = '$' . number_format($row["monto_pendiente"], 2);
                
                // Estatus
                $estatus_badge = '';
                switch ($row["estatus_pago"]) {
                    case 'pendiente':
                        $estatus_badge = '<span class="badge bg-danger">Pendiente</span>';
                        break;
                    case 'parcial':
                        $estatus_badge = '<span class="badge bg-warning">Parcial</span>';
                        break;
                    case 'liquidado':
                        $estatus_badge = '<span class="badge bg-success">Liquidado</span>';
                        break;
                }
                $sub_array[] = $estatus_badge;
                
                // Fecha límite
                if (!empty($row["fecha_limite_pago"])) {
                    $fecha_limite = new DateTime($row["fecha_limite_pago"]);
                    $hoy = new DateTime();
                    if ($fecha_limite < $hoy && $row["estatus_pago"] != 'liquidado') {
                        $sub_array[] = '<span class="badge bg-danger">' . date('d/m/Y', strtotime($row["fecha_limite_pago"])) . '</span>';
                    } else {
                        $sub_array[] = date('d/m/Y', strtotime($row["fecha_limite_pago"]));
                    }
                } else {
                    $sub_array[] = 'N/A';
                }
                
                $sub_array[] = '<button class="btn btn-success btn-sm" onclick="registrarAbono(' . $row["pago_id"] . ')" title="Registrar Abono"><i class="fas fa-dollar-sign"></i></button>
                                <button class="btn btn-info btn-sm" onclick="verHistorial(' . $row["pago_id"] . ')" title="Ver Historial"><i class="fas fa-history"></i></button>
                                <button class="btn btn-warning btn-sm" onclick="editarFechaLimite(' . $row["pago_id"] . ')" title="Editar Fecha Límite"><i class="fas fa-calendar"></i></button>';
                $output[] = $sub_array;
            }
            
            echo json_encode([
                "data" => $output
            ]);
            break;
        
        // ====================================
        // LISTAR PAGOS PENDIENTES
        // ====================================
        case "listar_pendientes":
            $temporada_id = $_POST['temporada_id'] ?? $_GET['temporada_id'] ?? null;
            $datos = $pago->obtener_pagos_pendientes($temporada_id);
            echo json_encode($datos);
            break;
        
        // ====================================
        // OBTENER PAGO POR ID
        // ====================================
        case "obtener":
            $pago_id = $_POST['pago_id'] ?? $_GET['pago_id'] ?? 0;
            $datos = $pago->obtener_pago_por_id($pago_id);
            echo json_encode($datos);
            break;
        
        // ====================================
        // OBTENER PAGO POR EQUIPO
        // ====================================
        case "obtener_por_equipo":
            $equipo_id = $_POST['equipo_id'] ?? $_GET['equipo_id'] ?? 0;
            $datos = $pago->obtener_pago_por_equipo($equipo_id);
            echo json_encode($datos);
            break;
        
        // ====================================
        // OBTENER HISTORIAL DE ABONOS
        // ====================================
        case "historial_abonos":
            $pago_id = $_POST['pago_id'] ?? $_GET['pago_id'] ?? 0;
            $datos = $pago->obtener_historial_abonos($pago_id);
            echo json_encode($datos);
            break;
        
        // ====================================
        // OBTENER ESTADÍSTICAS DE PAGOS
        // ====================================
        case "estadisticas":
            $temporada_id = $_POST['temporada_id'] ?? $_GET['temporada_id'] ?? 0;
            $datos = $pago->obtener_estadisticas_pagos($temporada_id);
            echo json_encode($datos);
            break;
        
        // ====================================
        // ACTUALIZAR FECHA LÍMITE
        // ====================================
        case "actualizar_fecha_limite":
            $pago_id = $_POST['pago_id'];
            $fecha_limite = $_POST['fecha_limite_pago'];
            
            $resultado = $pago->actualizar_fecha_limite($pago_id, $fecha_limite);
            
            if ($resultado) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Fecha límite actualizada correctamente"
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Error al actualizar fecha límite"
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