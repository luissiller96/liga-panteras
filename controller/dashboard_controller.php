<?php
/**
 * Controlador: Dashboard
 * Descripción: Dashboard público y estadísticas generales
 */

// Headers para JSON
header('Content-Type: application/json; charset=utf-8');

require_once("../config/conexion.php");
require_once("../models/Liga.php");
require_once("../models/Temporada.php");
require_once("../models/Partido.php");
require_once("../models/Gol.php");
require_once("../models/Posicion.php");
require_once("../models/Banner.php");
require_once("../models/Galeria.php");

$action = $_POST["action"] ?? $_GET["action"] ?? '';

if (!empty($action)) {
    
    try {
        switch ($action) {
            
            // ====================================
            // ESTADÍSTICAS GENERALES (KPIs)
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
                
                echo json_encode([
                    'status' => 'success',
                    'total_ligas' => (int)$total_ligas,
                    'total_equipos' => (int)$total_equipos,
                    'total_jugadores' => (int)$total_jugadores,
                    'total_partidos' => (int)$total_partidos,
                    'total_goles' => (int)$total_goles
                ]);
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
                        ORDER BY p.fecha_partido, p.hora_partido
                        LIMIT 10";
                
                $stmt = $conexion->prepare($sql);
                $stmt->bindValue(1, $fecha_inicio);
                $stmt->bindValue(2, $fecha_fin);
                $stmt->execute();
                $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode($datos);
                break;
            
            // ====================================
            // TABLA DE POSICIONES DE UNA TEMPORADA
            // ====================================
            case "tabla_posiciones":
                $temporada_id = $_POST['temporada_id'] ?? $_GET['temporada_id'] ?? 0;
                
                $conectar = new Conectar();
                $conexion = $conectar->Conexion();
                
                $sql = "SELECT tp.*, e.equipo_nombre, e.equipo_logo,
                        t.temporada_nombre, l.liga_nombre
                        FROM lp_tabla_posiciones tp
                        INNER JOIN lp_equipos e ON tp.equipo_id = e.equipo_id
                        INNER JOIN lp_temporadas t ON tp.temporada_id = t.temporada_id
                        INNER JOIN lp_ligas l ON t.liga_id = l.liga_id
                        WHERE tp.temporada_id = ?
                        ORDER BY tp.puntos DESC, tp.diferencia_goles DESC, tp.goles_favor DESC";
                
                $stmt = $conexion->prepare($sql);
                $stmt->bindValue(1, $temporada_id, PDO::PARAM_INT);
                $stmt->execute();
                $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Agregar posición numérica
                $posicion_num = 1;
                foreach ($datos as &$row) {
                    $row['posicion'] = $posicion_num;
                    $row['clasifica_playoffs'] = ($posicion_num <= 8);
                    $posicion_num++;
                }
                
                echo json_encode($datos);
                break;
            
            // ====================================
            // TABLA DE GOLEO
            // ====================================
            case "tabla_goleo":
                $temporada_id = $_POST['temporada_id'] ?? $_GET['temporada_id'] ?? null;
                $limite = $_POST['limite'] ?? $_GET['limite'] ?? 10;
                
                $conectar = new Conectar();
                $conexion = $conectar->Conexion();
                
                $sql = "SELECT 
                        j.jugador_id,
                        j.jugador_nombre,
                        j.jugador_numero,
                        j.jugador_foto,
                        e.equipo_nombre,
                        e.equipo_logo,
                        t.temporada_id,
                        t.temporada_nombre,
                        l.liga_nombre,
                        COUNT(g.gol_id) as total_goles
                        FROM lp_jugadores j
                        INNER JOIN lp_equipos e ON j.equipo_id = e.equipo_id
                        INNER JOIN lp_temporadas t ON e.temporada_id = t.temporada_id
                        INNER JOIN lp_ligas l ON t.liga_id = l.liga_id
                        INNER JOIN lp_goles g ON j.jugador_id = g.jugador_id
                        WHERE g.tipo_gol = 'normal'
                        AND j.jugador_estatus = 1";
                
                if ($temporada_id) {
                    $sql .= " AND t.temporada_id = :temporada_id";
                }
                
                $sql .= " GROUP BY j.jugador_id, j.jugador_nombre, j.jugador_numero, j.jugador_foto,
                          e.equipo_nombre, e.equipo_logo, t.temporada_id, t.temporada_nombre, l.liga_nombre
                          ORDER BY total_goles DESC
                          LIMIT :limite";
                
                $stmt = $conexion->prepare($sql);
                
                if ($temporada_id) {
                    $stmt->bindValue(':temporada_id', $temporada_id, PDO::PARAM_INT);
                }
                
                $stmt->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
                $stmt->execute();
                $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Agregar posición
                $posicion_num = 1;
                foreach ($datos as &$row) {
                    $row['posicion'] = $posicion_num++;
                }
                
                echo json_encode($datos);
                break;
            
            // ====================================
            // DATOS DASHBOARD PRINCIPAL
            // ====================================
            case "dashboard_principal":
                $liga = new Liga();
                $temporada = new Temporada();
                $partido = new Partido();
                $banner = new Banner();
                $galeria = new Galeria();
                
                $datos = [
                    'banners' => $banner->obtener_banners(true) ?? [],
                    'ligas' => $liga->obtener_ligas() ?? [],
                    'proximos_partidos' => $partido->obtener_proximos_partidos(6) ?? [],
                    'resultados_recientes' => $partido->obtener_resultados_recientes(6) ?? []
                ];
                
                echo json_encode($datos);
                break;
            
            // ====================================
            // CALENDARIO DE PARTIDOS
            // ====================================
            case "calendario":
                $temporada_id = $_POST['temporada_id'] ?? $_GET['temporada_id'] ?? 0;
                $partido = new Partido();
                $datos = $partido->obtener_partidos($temporada_id);
                echo json_encode($datos ?? []);
                break;
            
            // ====================================
            // PLAYOFFS DE UNA TEMPORADA
            // ====================================
            case "playoffs":
                $temporada_id = $_POST['temporada_id'] ?? $_GET['temporada_id'] ?? 0;
                $partido = new Partido();
                
                $datos = [
                    'cuartos' => $partido->obtener_partidos_playoffs($temporada_id, 'cuartos') ?? [],
                    'semifinales' => $partido->obtener_partidos_playoffs($temporada_id, 'semifinal') ?? [],
                    'final' => $partido->obtener_partidos_playoffs($temporada_id, 'final') ?? []
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
        
    } catch (Exception $e) {
        echo json_encode([
            "status" => "error",
            "message" => "Error al procesar la solicitud: " . $e->getMessage()
        ]);
    }
    
} else {
    echo json_encode([
        "status" => "error",
        "message" => "No se especificó ninguna acción"
    ]);
}
?>