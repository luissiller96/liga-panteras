<?php
/**
 * Modelo: Partido
 * Descripción: Manejo de partidos (jornada regular y playoffs)
 * Tabla: lp_partidos
 */

class Partido {
    
    /**
     * Obtener todos los partidos
     */
    public function obtener_partidos($temporada_id = null) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT p.*, 
                j.jornada_numero,
                el.equipo_nombre AS equipo_local,
                el.equipo_logo AS logo_local,
                ev.equipo_nombre AS equipo_visitante,
                ev.equipo_logo AS logo_visitante,
                eg.equipo_nombre AS equipo_ganador,
                t.temporada_nombre,
                l.liga_nombre
                FROM lp_partidos p
                LEFT JOIN lp_jornadas j ON p.jornada_id = j.jornada_id
                INNER JOIN lp_equipos el ON p.equipo_local_id = el.equipo_id
                INNER JOIN lp_equipos ev ON p.equipo_visitante_id = ev.equipo_id
                LEFT JOIN lp_equipos eg ON p.equipo_ganador_id = eg.equipo_id
                INNER JOIN lp_temporadas t ON p.temporada_id = t.temporada_id
                INNER JOIN lp_ligas l ON t.liga_id = l.liga_id";
        
        if ($temporada_id !== null) {
            $sql .= " WHERE p.temporada_id = ?";
        }
        
        $sql .= " ORDER BY p.fecha_partido DESC, p.hora_partido DESC";
        
        $stmt = $conexion->prepare($sql);
        
        if ($temporada_id !== null) {
            $stmt->bindValue(1, $temporada_id);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener partidos por jornada
     */
    public function obtener_partidos_por_jornada($jornada_id) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT p.*, 
                el.equipo_nombre AS equipo_local,
                el.equipo_logo AS logo_local,
                ev.equipo_nombre AS equipo_visitante,
                ev.equipo_logo AS logo_visitante
                FROM lp_partidos p
                INNER JOIN lp_equipos el ON p.equipo_local_id = el.equipo_id
                INNER JOIN lp_equipos ev ON p.equipo_visitante_id = ev.equipo_id
                WHERE p.jornada_id = ?
                ORDER BY p.hora_partido";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $jornada_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener partidos de playoffs por tipo
     */
    public function obtener_partidos_playoffs($temporada_id, $tipo = null) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT p.*, 
                el.equipo_nombre AS equipo_local,
                el.equipo_logo AS logo_local,
                ev.equipo_nombre AS equipo_visitante,
                ev.equipo_logo AS logo_visitante,
                eg.equipo_nombre AS equipo_ganador
                FROM lp_partidos p
                INNER JOIN lp_equipos el ON p.equipo_local_id = el.equipo_id
                INNER JOIN lp_equipos ev ON p.equipo_visitante_id = ev.equipo_id
                LEFT JOIN lp_equipos eg ON p.equipo_ganador_id = eg.equipo_id
                WHERE p.temporada_id = ? 
                AND p.tipo_partido != 'jornada_regular'";
        
        if ($tipo !== null) {
            $sql .= " AND p.tipo_partido = ?";
        }
        
        $sql .= " ORDER BY 
                CASE p.tipo_partido 
                    WHEN 'cuartos' THEN 1
                    WHEN 'semifinal' THEN 2
                    WHEN 'final' THEN 3
                END, p.fecha_partido, p.hora_partido";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $temporada_id);
        
        if ($tipo !== null) {
            $stmt->bindValue(2, $tipo);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener partido por ID
     */
    public function obtener_partido_por_id($partido_id) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT p.*, 
                j.jornada_numero,
                el.equipo_nombre AS equipo_local,
                el.equipo_logo AS logo_local,
                ev.equipo_nombre AS equipo_visitante,
                ev.equipo_logo AS logo_visitante,
                eg.equipo_nombre AS equipo_ganador,
                t.temporada_nombre,
                l.liga_nombre
                FROM lp_partidos p
                LEFT JOIN lp_jornadas j ON p.jornada_id = j.jornada_id
                INNER JOIN lp_equipos el ON p.equipo_local_id = el.equipo_id
                INNER JOIN lp_equipos ev ON p.equipo_visitante_id = ev.equipo_id
                LEFT JOIN lp_equipos eg ON p.equipo_ganador_id = eg.equipo_id
                INNER JOIN lp_temporadas t ON p.temporada_id = t.temporada_id
                INNER JOIN lp_ligas l ON t.liga_id = l.liga_id
                WHERE p.partido_id = ?";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $partido_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener próximos partidos (programados)
     */
    public function obtener_proximos_partidos($limite = 10, $temporada_id = null) {
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
                WHERE p.partido_estatus IN ('programado', 'en_curso')";
        
        if ($temporada_id !== null) {
            $sql .= " AND p.temporada_id = ?";
        }
        
        $sql .= " ORDER BY p.fecha_partido ASC, p.hora_partido ASC
                LIMIT ?";
        
        $stmt = $conexion->prepare($sql);
        
        if ($temporada_id !== null) {
            $stmt->bindValue(1, $temporada_id);
            $stmt->bindValue(2, $limite, PDO::PARAM_INT);
        } else {
            $stmt->bindValue(1, $limite, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener resultados recientes
     */
    public function obtener_resultados_recientes($limite = 10, $temporada_id = null) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT p.*, 
                j.jornada_numero,
                el.equipo_nombre AS equipo_local,
                el.equipo_logo AS logo_local,
                ev.equipo_nombre AS equipo_visitante,
                ev.equipo_logo AS logo_visitante,
                eg.equipo_nombre AS equipo_ganador,
                t.temporada_nombre,
                l.liga_nombre
                FROM lp_partidos p
                LEFT JOIN lp_jornadas j ON p.jornada_id = j.jornada_id
                INNER JOIN lp_equipos el ON p.equipo_local_id = el.equipo_id
                INNER JOIN lp_equipos ev ON p.equipo_visitante_id = ev.equipo_id
                LEFT JOIN lp_equipos eg ON p.equipo_ganador_id = eg.equipo_id
                INNER JOIN lp_temporadas t ON p.temporada_id = t.temporada_id
                INNER JOIN lp_ligas l ON t.liga_id = l.liga_id
                WHERE p.partido_estatus = 'finalizado'";
        
        if ($temporada_id !== null) {
            $sql .= " AND p.temporada_id = ?";
        }
        
        $sql .= " ORDER BY p.fecha_partido DESC, p.hora_partido DESC
                LIMIT ?";
        
        $stmt = $conexion->prepare($sql);
        
        if ($temporada_id !== null) {
            $stmt->bindValue(1, $temporada_id);
            $stmt->bindValue(2, $limite, PDO::PARAM_INT);
        } else {
            $stmt->bindValue(1, $limite, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Crear nuevo partido
     */
    public function crear_partido($datos) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "INSERT INTO lp_partidos 
                (jornada_id, temporada_id, tipo_partido, equipo_local_id, equipo_visitante_id, fecha_partido, hora_partido, duracion_partido, partido_estatus) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'programado')";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $datos['jornada_id']);
        $stmt->bindValue(2, $datos['temporada_id']);
        $stmt->bindValue(3, $datos['tipo_partido']);
        $stmt->bindValue(4, $datos['equipo_local_id']);
        $stmt->bindValue(5, $datos['equipo_visitante_id']);
        $stmt->bindValue(6, $datos['fecha_partido']);
        $stmt->bindValue(7, $datos['hora_partido']);
        $stmt->bindValue(8, $datos['duracion_partido']);
        $stmt->execute();
        
        return $conexion->lastInsertId();
    }
    
    /**
     * Actualizar resultado del partido
     */
    public function actualizar_resultado($partido_id, $goles_local, $goles_visitante, $observaciones = '') {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        // Determinar ganador
        $equipo_ganador_id = null;
        $partido = $this->obtener_partido_por_id($partido_id);
        
        if ($goles_local > $goles_visitante) {
            $equipo_ganador_id = $partido['equipo_local_id'];
        } elseif ($goles_visitante > $goles_local) {
            $equipo_ganador_id = $partido['equipo_visitante_id'];
        }
        
        $sql = "UPDATE lp_partidos SET 
                goles_local = ?,
                goles_visitante = ?,
                equipo_ganador_id = ?,
                observaciones = ?,
                partido_estatus = 'finalizado',
                fecha_captura = NOW()
                WHERE partido_id = ?";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $goles_local);
        $stmt->bindValue(2, $goles_visitante);
        $stmt->bindValue(3, $equipo_ganador_id);
        $stmt->bindValue(4, $observaciones);
        $stmt->bindValue(5, $partido_id);
        $stmt->execute();
        
        // Si es jornada regular, actualizar tabla de posiciones
        if ($partido['tipo_partido'] == 'jornada_regular') {
            $this->actualizar_tabla_posiciones($partido_id);
        }
        
        return true;
    }
    
    /**
     * Actualizar tabla de posiciones después de un partido
     */
    private function actualizar_tabla_posiciones($partido_id) {
        require_once("Posicion.php");
        $posicion = new Posicion();
        $posicion->actualizar_posiciones_desde_partido($partido_id);
    }
    
    /**
     * Cambiar estatus del partido
     */
    public function cambiar_estatus($partido_id, $estatus) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "UPDATE lp_partidos SET partido_estatus = ? WHERE partido_id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $estatus);
        $stmt->bindValue(2, $partido_id);
        $stmt->execute();
        
        return true;
    }
    
    /**
     * Eliminar partido (solo si no tiene goles/tarjetas registradas)
     */
    public function eliminar_partido($partido_id) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        // Verificar que no tenga goles
        $sql = "SELECT COUNT(*) as total FROM lp_goles WHERE partido_id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $partido_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['total'] > 0) {
            return false;
        }
        
        // Verificar que no tenga tarjetas
        $sql = "SELECT COUNT(*) as total FROM lp_tarjetas WHERE partido_id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $partido_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['total'] > 0) {
            return false;
        }
        
        // Eliminar partido
        $sql = "DELETE FROM lp_partidos WHERE partido_id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $partido_id);
        $stmt->execute();
        
        return true;
    }
    
    /**
     * Obtener partidos de un equipo
     */
    public function obtener_partidos_equipo($equipo_id) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT p.*, 
                j.jornada_numero,
                el.equipo_nombre AS equipo_local,
                el.equipo_logo AS logo_local,
                ev.equipo_nombre AS equipo_visitante,
                ev.equipo_logo AS logo_visitante,
                CASE 
                    WHEN p.equipo_local_id = ? THEN 'local'
                    ELSE 'visitante'
                END as tipo_encuentro
                FROM lp_partidos p
                LEFT JOIN lp_jornadas j ON p.jornada_id = j.jornada_id
                INNER JOIN lp_equipos el ON p.equipo_local_id = el.equipo_id
                INNER JOIN lp_equipos ev ON p.equipo_visitante_id = ev.equipo_id
                WHERE (p.equipo_local_id = ? OR p.equipo_visitante_id = ?)
                ORDER BY p.fecha_partido DESC, p.hora_partido DESC";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $equipo_id);
        $stmt->bindValue(2, $equipo_id);
        $stmt->bindValue(3, $equipo_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>