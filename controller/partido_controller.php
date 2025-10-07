<?php
/**
 * Controlador: Partido
 * Descripción: Manejo de partidos (jornada regular y playoffs)
 */

require_once("../config/conexion.php");
require_once("../models/Partido.php");
require_once("../models/Gol.php");
require_once("../models/Tarjeta.php");

$partido = new Partido();
$gol = new Gol();
$tarjeta = new Tarjeta();

$action = $_POST["action"] ?? $_GET["action"] ?? '';

if (!empty($action)) {
    
    switch ($action) {
        
        // ====================================
        // LISTAR PARTIDOS
        // ====================================
        case "listar":
            $temporada_id = $_POST['temporada_id'] ?? $_GET['temporada_id'] ?? null;
            $datos = $partido->obtener_partidos($temporada_id);
            $output = array();
            
            foreach ($datos as $row) {
                $sub_array = array();
                $sub_array[] = $row["partido_id"];
                
                // Tipo de partido
                $tipo_badge = '';
                switch ($row["tipo_partido"]) {
                    case 'jornada_regular':
                        $tipo_badge = '<span class="badge bg-primary">Jornada ' . ($row["jornada_numero"] ?? '') . '</span>';
                        break;
                    case 'cuartos':
                        $tipo_badge = '<span class="badge bg-warning">Cuartos</span>';
                        break;
                    case 'semifinal':
                        $tipo_badge = '<span class="badge bg-info">Semifinal</span>';
                        break;
                    case 'final':
                        $tipo_badge = '<span class="badge bg-success">Final</span>';
                        break;
                }
                $sub_array[] = $tipo_badge;
                
                $sub_array[] = htmlspecialchars($row["liga_nombre"]);
                $sub_array[] = date('d/m/Y', strtotime($row["fecha_partido"]));
                $sub_array[] = date('H:i', strtotime($row["hora_partido"]));
                
                // Enfrentamiento
                $enfrentamiento = '<div class="text-center">';
                $enfrentamiento .= '<strong>' . htmlspecialchars($row["equipo_local"]) . '</strong>';
                
                if ($row["partido_estatus"] == 'finalizado') {
                    $enfrentamiento .= '<br><span class="badge bg-dark fs-6">' . $row["goles_local"] . ' - ' . $row["goles_visitante"] . '</span><br>';
                } else {
                    $enfrentamiento .= '<br><span class="text-muted">vs</span><br>';
                }
                
                $enfrentamiento .= '<strong>' . htmlspecialchars($row["equipo_visitante"]) . '</strong>';
                $enfrentamiento .= '</div>';
                $sub_array[] = $enfrentamiento;
                
                // Estatus
                $estatus_badge = '';
                switch ($row["partido_estatus"]) {
                    case 'programado':
                        $estatus_badge = '<span class="badge bg-secondary">Programado</span>';
                        break;
                    case 'en_curso':
                        $estatus_badge = '<span class="badge bg-warning">En Curso</span>';
                        break;
                    case 'finalizado':
                        $estatus_badge = '<span class="badge bg-success">Finalizado</span>';
                        break;
                    case 'suspendido':
                        $estatus_badge = '<span class="badge bg-danger">Suspendido</span>';
                        break;
                }
                $sub_array[] = $estatus_badge;
                
                // Acciones
                $acciones = '';
                if ($row["partido_estatus"] == 'programado') {
                    $acciones .= '<button class="btn btn-success btn-sm" onclick="capturarResultado(' . $row["partido_id"] . ')" title="Capturar Resultado"><i class="fas fa-futbol"></i></button> ';
                } else if ($row["partido_estatus"] == 'finalizado') {
                    $acciones .= '<button class="btn btn-info btn-sm" onclick="verDetalles(' . $row["partido_id"] . ')" title="Ver Detalles"><i class="fas fa-eye"></i></button> ';
                    $acciones .= '<button class="btn btn-warning btn-sm" onclick="editarResultado(' . $row["partido_id"] . ')" title="Editar Resultado"><i class="fas fa-edit"></i></button> ';
                }
                $acciones .= '<button class="btn btn-danger btn-sm" onclick="eliminar(' . $row["partido_id"] . ')" title="Eliminar"><i class="fas fa-trash"></i></button>';
                
                $sub_array[] = $acciones;
                $output[] = $sub_array;
            }
            
            echo json_encode([
                "data" => $output
            ]);
            break;
        
        // ====================================
        // LISTAR PARTIDOS DE JORNADA
        // ====================================
        case "listar_jornada":
            $jornada_id = $_POST['jornada_id'] ?? $_GET['jornada_id'] ?? 0;
            $datos = $partido->obtener_partidos_por_jornada($jornada_id);
            echo json_encode($datos);
            break;
        
        // ====================================
        // LISTAR PLAYOFFS
        // ====================================
        case "listar_playoffs":
            $temporada_id = $_POST['temporada_id'] ?? $_GET['temporada_id'] ?? 0;
            $tipo = $_POST['tipo'] ?? $_GET['tipo'] ?? null;
            $datos = $partido->obtener_partidos_playoffs($temporada_id, $tipo);
            echo json_encode($datos);
            break;
        
        // ====================================
        // OBTENER PRÓXIMOS PARTIDOS
        // ====================================
        case "proximos":
            $limite = $_POST['limite'] ?? $_GET['limite'] ?? 10;
            $temporada_id = $_POST['temporada_id'] ?? $_GET['temporada_id'] ?? null;
            $datos = $partido->obtener_proximos_partidos($limite, $temporada_id);
            echo json_encode($datos);
            break;
        
        // ====================================
        // OBTENER RESULTADOS RECIENTES
        // ====================================
        case "resultados_recientes":
            $limite = $_POST['limite'] ?? $_GET['limite'] ?? 10;
            $temporada_id = $_POST['temporada_id'] ?? $_GET['temporada_id'] ?? null;
            $datos = $partido->obtener_resultados_recientes($limite, $temporada_id);
            echo json_encode($datos);
            break;
        
        // ====================================
        // OBTENER PARTIDO POR ID
        // ====================================
        case "obtener":
            $partido_id = $_POST['partido_id'] ?? $_GET['partido_id'] ?? 0;
            $datos = $partido->obtener_partido_por_id($partido_id);
            
            // Agregar goles y tarjetas
            if ($datos) {
                $datos['goles'] = $gol->obtener_goles_por_partido($partido_id);
                $datos['tarjetas'] = $tarjeta->obtener_tarjetas_por_partido($partido_id);
            }
            
            echo json_encode($datos);
            break;
        
        // ====================================
        // CREAR PARTIDO
        // ====================================
        case "crear":
            $datos = [
                'jornada_id' => $_POST['jornada_id'] ?? null,
                'temporada_id' => $_POST['temporada_id'],
                'tipo_partido' => $_POST['tipo_partido'] ?? 'jornada_regular',
                'equipo_local_id' => $_POST['equipo_local_id'],
                'equipo_visitante_id' => $_POST['equipo_visitante_id'],
                'fecha_partido' => $_POST['fecha_partido'],
                'hora_partido' => $_POST['hora_partido'],
                'duracion_partido' => $_POST['duracion_partido'] ?? '20 min x 2 tiempos'
            ];
            
            // Validar que no sea el mismo equipo
            if ($datos['equipo_local_id'] == $datos['equipo_visitante_id']) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Un equipo no puede jugar contra sí mismo"
                ]);
                break;
            }
            
            $resultado = $partido->crear_partido($datos);
            
            if ($resultado) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Partido creado correctamente",
                    "id" => $resultado
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Error al crear partido"
                ]);
            }
            break;
        
        // ====================================
        // ACTUALIZAR RESULTADO
        // ====================================
        case "actualizar_resultado":
            $partido_id = $_POST['partido_id'];
            $goles_local = $_POST['goles_local'];
            $goles_visitante = $_POST['goles_visitante'];
            $observaciones = $_POST['observaciones'] ?? '';
            
            $resultado = $partido->actualizar_resultado($partido_id, $goles_local, $goles_visitante, $observaciones);
            
            if ($resultado) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Resultado actualizado correctamente"
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Error al actualizar resultado"
                ]);
            }
            break;
        
        // ====================================
        // CAMBIAR ESTATUS
        // ====================================
        case "cambiar_estatus":
            $partido_id = $_POST['partido_id'];
            $estatus = $_POST['estatus'];
            
            $resultado = $partido->cambiar_estatus($partido_id, $estatus);
            
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
        // OBTENER PARTIDOS DE UN EQUIPO
        // ====================================
        case "partidos_equipo":
            $equipo_id = $_POST['equipo_id'] ?? $_GET['equipo_id'] ?? 0;
            $datos = $partido->obtener_partidos_equipo($equipo_id);
            echo json_encode($datos);
            break;
        
        // ====================================
        // ELIMINAR PARTIDO
        // ====================================
        case "eliminar":
            $partido_id = $_POST['partido_id'];
            $resultado = $partido->eliminar_partido($partido_id);
            
            if ($resultado) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Partido eliminado correctamente"
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "No se puede eliminar un partido con goles o tarjetas registradas"
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