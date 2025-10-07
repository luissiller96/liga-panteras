<?php
/**
 * Modelo: Gol
 * Descripción: Registro de goles en partidos
 * Tabla: lp_goles
 */

class Gol {
    
    /**
     * Obtener goles de un partido
     */
    public function obtener_goles_por_partido($partido_id) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT g.*, 
                j.jugador_nombre,
                j.jugador_numero,
                j.jugador_foto,
                e.equipo_nombre,
                e.equipo_logo
                FROM lp_goles g
                INNER JOIN lp_jugadores j ON g.jugador_id = j.jugador_id
                INNER JOIN lp_equipos e ON g.equipo_id = e.equipo_id
                WHERE g.partido_id = ?
                ORDER BY g.fecha_registro";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $partido_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener goles de un jugador
     */
    public function obtener_goles_por_jugador($jugador_id) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT g.*, 
                p.fecha_partido,
                p.goles_local,
                p.goles_visitante,
                el.equipo_nombre AS equipo_local,
                ev.equipo_nombre AS equipo_visitante,
                j.jornada_numero
                FROM lp_goles g
                INNER JOIN lp_partidos p ON g.partido_id = p.partido_id
                LEFT JOIN lp_jornadas j ON p.jornada_id = j.jornada_id
                INNER JOIN lp_equipos el ON p.equipo_local_id = el.equipo_id
                INNER JOIN lp_equipos ev ON p.equipo_visitante_id = ev.equipo_id
                WHERE g.jugador_id = ?
                ORDER BY p.fecha_partido DESC";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $jugador_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Registrar gol
     */
    public function registrar_gol($datos) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "INSERT INTO lp_goles 
                (partido_id, jugador_id, equipo_id, tipo_gol) 
                VALUES (?, ?, ?, ?)";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $datos['partido_id']);
        $stmt->bindValue(2, $datos['jugador_id']);
        $stmt->bindValue(3, $datos['equipo_id']);
        $stmt->bindValue(4, $datos['tipo_gol']);
        $stmt->execute();
        
        // Actualizar contador en tabla de partidos
        $this->actualizar_marcador_partido($datos['partido_id']);
        
        return $conexion->lastInsertId();
    }
    
    /**
     * Eliminar gol
     */
    public function eliminar_gol($gol_id) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        // Obtener partido_id antes de eliminar
        $sql = "SELECT partido_id FROM lp_goles WHERE gol_id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $gol_id);
        $stmt->execute();
        $gol = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$gol) {
            return false;
        }
        
        // Eliminar gol
        $sql = "DELETE FROM lp_goles WHERE gol_id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $gol_id);
        $stmt->execute();
        
        // Actualizar marcador del partido
        $this->actualizar_marcador_partido($gol['partido_id']);
        
        return true;
    }
    
    /**
     * Actualizar marcador del partido basado en goles registrados
     */
    private function actualizar_marcador_partido($partido_id) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        // Obtener información del partido
        $sql = "SELECT equipo_local_id, equipo_visitante_id FROM lp_partidos WHERE partido_id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $partido_id);
        $stmt->execute();
        $partido = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$partido) {
            return false;
        }
        
        // Contar goles del equipo local (goles normales - autogoles del visitante)
        $sql = "SELECT 
                COUNT(CASE WHEN equipo_id = ? AND tipo_gol = 'normal' THEN 1 END) +
                COUNT(CASE WHEN equipo_id = ? AND tipo_gol = 'autogol' THEN 1 END) as goles_local,
                COUNT(CASE WHEN equipo_id = ? AND tipo_gol = 'normal' THEN 1 END) +
                COUNT(CASE WHEN equipo_id = ? AND tipo_gol = 'autogol' THEN 1 END) as goles_visitante
                FROM lp_goles 
                WHERE partido_id = ?";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $partido['equipo_local_id']);
        $stmt->bindValue(2, $partido['equipo_visitante_id']);
        $stmt->bindValue(3, $partido['equipo_visitante_id']);
        $stmt->bindValue(4, $partido['equipo_local_id']);
        $stmt->bindValue(5, $partido_id);
        $stmt->execute();
        $marcador = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Actualizar partido
        $sql = "UPDATE lp_partidos SET 
                goles_local = ?,
                goles_visitante = ?
                WHERE partido_id = ?";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $marcador['goles_local']);
        $stmt->bindValue(2, $marcador['goles_visitante']);
        $stmt->bindValue(3, $partido_id);
        $stmt->execute();
        
        return true;
    }
    
    /**
     * Obtener tabla de goleo de una temporada
     */
    public function obtener_tabla_goleo($temporada_id, $limite = 10) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT 
                j.jugador_id,
                j.jugador_nombre,
                j.jugador_numero,
                j.jugador_foto,
                e.equipo_nombre,
                e.equipo_logo,
                COUNT(g.gol_id) as total_goles
                FROM lp_jugadores j
                INNER JOIN lp_equipos e ON j.equipo_id = e.equipo_id
                INNER JOIN lp_goles g ON j.jugador_id = g.jugador_id
                INNER JOIN lp_partidos p ON g.partido_id = p.partido_id
                WHERE e.temporada_id = ? 
                AND g.tipo_gol = 'normal'
                AND j.jugador_estatus = 1
                GROUP BY j.jugador_id
                ORDER BY total_goles DESC, j.jugador_nombre
                LIMIT ?";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $temporada_id);
        $stmt->bindValue(2, $limite, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener jugador destacado de la jornada (más goles)
     */
    public function obtener_jugador_destacado_jornada($jornada_id) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT 
                j.jugador_id,
                j.jugador_nombre,
                j.jugador_numero,
                j.jugador_foto,
                e.equipo_nombre,
                e.equipo_logo,
                COUNT(g.gol_id) as goles_jornada
                FROM lp_jugadores j
                INNER JOIN lp_equipos e ON j.equipo_id = e.equipo_id
                INNER JOIN lp_goles g ON j.jugador_id = g.jugador_id
                INNER JOIN lp_partidos p ON g.partido_id = p.partido_id
                WHERE p.jornada_id = ? 
                AND g.tipo_gol = 'normal'
                GROUP BY j.jugador_id
                ORDER BY goles_jornada DESC, j.jugador_nombre
                LIMIT 1";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $jornada_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Contar goles de un jugador en la temporada
     */
    public function contar_goles_jugador($jugador_id) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT COUNT(*) as total 
                FROM lp_goles 
                WHERE jugador_id = ? AND tipo_gol = 'normal'";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $jugador_id);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
}
?>