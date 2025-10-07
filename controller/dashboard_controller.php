<?php
/**
 * Controlador: Dashboard
 * Descripción: Dashboard público y estadísticas generales
 */

require_once("../config/conexion.php");
require_once("../models/Liga.php");
require_once("../models/Temporada.php");
require_once("../models/Partido.php");
require_once("../models/Gol.php");
require_once("../models/Posicion.php");
require_once("../models/Banner.php");
require_once("../models/Galeria.php");

$liga = new Liga();
$temporada = new Temporada();
$partido = new Partido();
$gol = new Gol();
$posicion = new Posicion();
$banner = new Banner();
$galeria = new Galeria();

$action = $_POST["action"] ?? $_GET["action"] ?? '';

if (!empty($action)) {
    
    switch ($action) {
        
        // ====================================
        // DATOS DASHBOARD PRINCIPAL
        // ====================================
        case "dashboard_principal":
            $datos = [
                'banners' => $banner->obtener_banners(true),
                'ligas' => $liga->obtener_ligas_con_temporadas(),
                'proximos_partidos' => $partido->obtener_proximos_partidos(6),
                'resultados_recientes' => $partido->obtener_resultados_recientes(6),
                'fotos_recientes' => $galeria->obtener_fotos_recientes(6)
            ];
            
            echo json_encode($datos);
            break;
        
        // ====================================
        // DATOS DE UNA LIGA ESPECÍFICA
        // ====================================
        case "datos_liga":
            $liga_id = $_POST['liga_id'] ?? $_GET['liga_id'] ?? 0;
            
            // Obtener temporada activa de la liga
            $temporadas = $temporada->obtener_temporadas_por_liga($liga_id);
            $temporada_activa = null;
            foreach ($temporadas as $t) {
                if ($t['temporada_estatus'] == 'en_curso') {
                    $temporada_activa = $t;
                    break;
                }
            }
            
            $datos = [
                'liga' => $liga->obtener_liga_por_id($liga_id),
                'temporadas' => $temporadas,
                'temporada_activa' => $temporada_activa
            ];
            
            if ($temporada_activa) {
                $datos['tabla_posiciones'] = $posicion->obtener_tabla_posiciones($temporada_activa['temporada_id']);
                $datos['tabla_goleo'] = $gol->obtener_tabla_goleo($temporada_activa['temporada_id'], 10);
                $datos['proximos_partidos'] = $partido->obtener_proximos_partidos(5, $temporada_activa['temporada_id']);
                $datos['resultados_recientes'] = $partido->obtener_resultados_recientes(5, $temporada_activa['temporada_id']);
            }
            
            echo json_encode($datos);
            break;
        
        // ====================================
        // TABLA DE POSICIONES DE UNA TEMPORADA
        // ====================================
        case "tabla_posiciones":
            $temporada_id = $_POST['temporada_id'] ?? $_GET['temporada_id'] ?? 0;
            $datos = $posicion->obtener_tabla_posiciones($temporada_id);
            
            // Agregar posición numérica y clasificación a playoffs
            $posicion_num = 1;
            foreach ($datos as &$row) {
                $row['posicion'] = $posicion_num;
                $row['clasifica_playoffs'] = ($posicion_num <= 8) ? true : false;
                $posicion_num++;
            }
            
            echo json_encode($datos);
            break;
        
        // ====================================
        // TABLA DE GOLEO
        // ====================================
        case "tabla_goleo":
            $temporada_id = $_POST['temporada_id'] ?? $_GET['temporada_id'] ?? 0;
            $limite = $_POST['limite'] ?? $_GET['limite'] ?? 10;
            $datos = $gol->obtener_tabla_goleo($temporada_id, $limite);
            
            // Agregar posición
            $posicion_num = 1;
            foreach ($datos as &$row) {
                $row['posicion'] = $posicion_num++;
            }
            
            echo json_encode($datos);
            break;
        
        // ====================================
        // CALENDARIO DE PARTIDOS
        // ====================================
        case "calendario":
            $temporada_id = $_POST['temporada_id'] ?? $_GET['temporada_id'] ?? 0;
            $datos = $partido->obtener_partidos($temporada_id);
            echo json_encode($datos);
            break;
        
        // ====================================
        // RESULTADOS DE UNA JORNADA
        // ====================================
        case "resultados_jornada":
            $jornada_id = $_POST['jornada_id'] ?? $_GET['jornada_id'] ?? 0;
            $datos = $partido->obtener_partidos_por_jornada($jornada_id);
            echo json_encode($datos);
            break;
        
        // ====================================
        // ESTADÍSTICAS GENERALES
        // ====================================
        case "estadisticas_generales":
            $conectar = new Conectar();
            $conexion = $conectar->Conexion();
            
            // Total de ligas activas
            $sql = "SELECT COUNT(*) as total FROM lp_ligas WHERE liga_estatus = 1";
            $stmt = $conexion->prepare($sql);
            $stmt->execute();
            $total_ligas = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Total de equipos activos
            $sql = "SELECT COUNT(*) as total FROM lp_equipos WHERE equipo_estatus = 1";
            $stmt = $conexion->prepare($sql);
            $stmt->execute();
            $total_equipos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Total de jugadores activos
            $sql = "SELECT COUNT(*) as total FROM lp_jugadores WHERE jugador_estatus = 1";
            $stmt = $conexion->prepare($sql);
            $stmt->execute();
            $total_jugadores = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Total de partidos jugados
            $sql = "SELECT COUNT(*) as total FROM lp_partidos WHERE partido_estatus = 'finalizado'";
            $stmt = $conexion->prepare($sql);
            $stmt->execute();
            $total_partidos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Total de goles
            $sql = "SELECT COUNT(*) as total FROM lp_goles WHERE tipo_gol = 'normal'";
            $stmt = $conexion->prepare($sql);
            $stmt->execute();
            $total_goles = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            $datos = [
                'total_ligas' => $total_ligas,
                'total_equipos' => $total_equipos,
                'total_jugadores' => $total_jugadores,
                'total_partidos' => $total_partidos,
                'total_goles' => $total_goles
            ];
            
            echo json_encode($datos);
            break;
        
        // ====================================
        // PARTIDOS DE LA SEMANA
        // ====================================
        case "partidos_semana":
            $fecha_inicio = $_POST['fecha_inicio'] ?? $_GET['fecha_inicio'] ?? date('Y-m-d');
            $fecha_fin = date('Y-m-d', strtotime($fecha_inicio . ' +7 days'));
            
            $conectar = new Conectar();
            $conexion = $conectar->Conexion();
            
            $sql = "SELECT p.*, 
                    j.jornada_numero,
                    el.equipo_nombre AS equipo_local,
                    el.equipo_logo AS logo_local,
                    ev.equipo_nombre AS equipo_visitante,
                    ev.equipo_logo AS logo_visitante,
                    t.temporada_nombre,
                    l.liga_nombre
                    FROM lp_partidos p
                    LEFT JOIN lp_jornadas j ON p.jornada_id = j.jornada_id
                    INNER JOIN lp_equipos el ON p.equipo_local_id = el.equipo_id
                    INNER JOIN lp_equipos ev ON p.equipo_visitante_id = ev.equipo_id
                    INNER JOIN lp_temporadas t ON p.temporada_id = t.temporada_id
                    INNER JOIN lp_ligas l ON t.liga_id = l.liga_id
                    WHERE p.fecha_partido BETWEEN ? AND ?
                    AND p.partido_estatus IN ('programado', 'en_curso')
                    ORDER BY p.fecha_partido, p.hora_partido";
            
            $stmt = $conexion->prepare($sql);
            $stmt->bindValue(1, $fecha_inicio);
            $stmt->bindValue(2, $fecha_fin);
            $stmt->execute();
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode($datos);
            break;
        
        // ====================================
        // PLAYOFFS DE UNA TEMPORADA
        // ====================================
        case "playoffs":
            $temporada_id = $_POST['temporada_id'] ?? $_GET['temporada_id'] ?? 0;
            $datos = [
                'cuartos' => $partido->obtener_partidos_playoffs($temporada_id, 'cuartos'),
                'semifinales' => $partido->obtener_partidos_playoffs($temporada_id, 'semifinal'),
                'final' => $partido->obtener_partidos_playoffs($temporada_id, 'final')
            ];
            
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