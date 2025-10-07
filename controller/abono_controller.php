<?php
/**
 * Controlador: Abono
 * Descripción: Registro de abonos a inscripciones
 */

require_once("../config/conexion.php");
require_once("../models/Abono.php");

$abono = new Abono();

$action = $_POST["action"] ?? $_GET["action"] ?? '';

if (!empty($action)) {
    
    switch ($action) {
        
        // ====================================
        // LISTAR ABONOS
        // ====================================
        case "listar":
            $pago_id = $_POST['pago_id'] ?? $_GET['pago_id'] ?? null;
            $datos = $abono->obtener_abonos($pago_id);
            $output = array();
            
            foreach ($datos as $row) {
                $sub_array = array();
                $sub_array[] = $row["abono_id"];
                $sub_array[] = htmlspecialchars($row["equipo_nombre"]);
                $sub_array[] = htmlspecialchars($row["liga_nombre"]);
                $sub_array[] = '$' . number_format($row["monto_abono"], 2);
                $sub_array[] = date('d/m/Y H:i', strtotime($row["fecha_abono"]));
                
                // Método de pago
                $metodo_badge = '';
                switch ($row["metodo_pago"]) {
                    case 'efectivo':
                        $metodo_badge = '<span class="badge bg-success">Efectivo</span>';
                        break;
                    case 'transferencia':
                        $metodo_badge = '<span class="badge bg-info">Transferencia</span>';
                        break;
                    case 'tarjeta':
                        $metodo_badge = '<span class="badge bg-primary">Tarjeta</span>';
                        break;
                }
                $sub_array[] = $metodo_badge;
                
                $sub_array[] = htmlspecialchars($row["referencia_pago"] ?? 'N/A');
                $sub_array[] = htmlspecialchars($row["usu_nom"] ?? 'Sistema');
                $sub_array[] = '<button class="btn btn-info btn-sm" onclick="verDetalles(' . $row["abono_id"] . ')" title="Ver Detalles"><i class="fas fa-eye"></i></button>
                                <button class="btn btn-danger btn-sm" onclick="eliminar(' . $row["abono_id"] . ')" title="Eliminar"><i class="fas fa-trash"></i></button>';
                $output[] = $sub_array;
            }
            
            echo json_encode([
                "data" => $output
            ]);
            break;
        
        // ====================================
        // OBTENER ABONOS RECIENTES
        // ====================================
        case "recientes":
            $limite = $_POST['limite'] ?? $_GET['limite'] ?? 10;
            $datos = $abono->obtener_abonos_recientes($limite);
            echo json_encode($datos);
            break;
        
        // ====================================
        // OBTENER ABONO POR ID
        // ====================================
        case "obtener":
            $abono_id = $_POST['abono_id'] ?? $_GET['abono_id'] ?? 0;
            $datos = $abono->obtener_abono_por_id($abono_id);
            echo json_encode($datos);
            break;
        
        // ====================================
        // REGISTRAR ABONO
        // ====================================
        case "registrar":
            $datos = [
                'pago_id' => $_POST['pago_id'],
                'monto_abono' => $_POST['monto_abono'],
                'fecha_abono' => $_POST['fecha_abono'],
                'metodo_pago' => $_POST['metodo_pago'],
                'referencia_pago' => $_POST['referencia_pago'] ?? null,
                'comentarios' => $_POST['comentarios'] ?? '',
                'registrado_por' => $_SESSION['usu_id'] ?? 1
            ];
            
            // Validar que el monto sea positivo
            if ($datos['monto_abono'] <= 0) {
                echo json_encode([
                    "status" => "error",
                    "message" => "El monto del abono debe ser mayor a cero"
                ]);
                break;
            }
            
            $resultado = $abono->registrar_abono($datos);
            
            if (is_array($resultado) && isset($resultado['error'])) {
                echo json_encode([
                    "status" => "error",
                    "message" => $resultado['error']
                ]);
            } else if ($resultado) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Abono registrado correctamente",
                    "id" => $resultado
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Error al registrar abono"
                ]);
            }
            break;
        
        // ====================================
        // ELIMINAR ABONO
        // ====================================
        case "eliminar":
            $abono_id = $_POST['abono_id'];
            $resultado = $abono->eliminar_abono($abono_id);
            
            if ($resultado) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Abono eliminado correctamente"
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Error al eliminar abono"
                ]);
            }
            break;
        
        // ====================================
        // OBTENER ESTADÍSTICAS DE ABONOS
        // ====================================
        case "estadisticas":
            $temporada_id = $_POST['temporada_id'] ?? $_GET['temporada_id'] ?? 0;
            $datos = $abono->obtener_estadisticas_abonos($temporada_id);
            echo json_encode($datos);
            break;
        
        // ====================================
        // OBTENER ABONOS POR TEMPORADA
        // ====================================
        case "listar_por_temporada":
            $temporada_id = $_POST['temporada_id'] ?? $_GET['temporada_id'] ?? 0;
            $datos = $abono->obtener_abonos_por_temporada($temporada_id);
            echo json_encode($datos);
            break;
        
        // ====================================
        // OBTENER ABONOS POR MÉTODO DE PAGO
        // ====================================
        case "por_metodo":
            $temporada_id = $_POST['temporada_id'] ?? $_GET['temporada_id'] ?? 0;
            $metodo_pago = $_POST['metodo_pago'] ?? $_GET['metodo_pago'] ?? '';
            $datos = $abono->obtener_abonos_por_metodo($temporada_id, $metodo_pago);
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