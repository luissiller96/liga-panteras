<?php
/**
 * Modelo: Tarjeta
 * Descripción: Registro de tarjetas amarillas y rojas
 * Tabla: lp_tarjetas
 */

class Tarjeta {
    
    /**
     * Obtener tarjetas de un partido
     */
    public function obtener_tarjetas_por_partido($partido_id) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT t.*, 
                j.jugador_nombre,
                j.jugador_numero,
                j.jugador_foto,
                e.equipo_nombre,
                e.equipo_logo
                FROM lp_tarjetas t
                INNER JOIN lp_jugadores j ON t.jugador_id = j.jugador_id
                INNER JOIN lp_equipos e ON t.equipo_id = e.equipo_id
                WHERE t.partido_id = ?
                ORDER BY t.fecha_registro";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $partido_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener tarjetas de un jugador
     */
    public function obtener_tarjetas_por_jugador($jugador_id) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT t.*, 
                p.fecha_partido,
                el.equipo_nombre AS equipo_local,
                ev.equipo_nombre AS equipo_visitante,
                j.jornada_numero
                FROM lp_tarjetas t
                INNER JOIN lp_partidos p ON t.partido_id = p.partido_id
                LEFT JOIN lp_jornadas j ON p.jornada_id = j.jornada_id
                INNER JOIN lp_equipos el ON p.equipo_local_id = el.equipo_id
                INNER JOIN lp_equipos ev ON p.equipo_visitante_id = ev.equipo_id
                WHERE t.jugador_id = ?
                ORDER BY p.fecha_partido DESC";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $jugador_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Registrar tarjeta
     */
    public function registrar_tarjeta($datos) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "INSERT INTO lp_tarjetas 
                (partido_id, jugador_id, equipo_id, tipo_tarjeta, motivo) 
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $datos['partido_id']);
        $stmt->bindValue(2, $datos['jugador_id']);
        $stmt->bindValue(3, $datos['equipo_id']);
        $stmt->bindValue(4, $datos['tipo_tarjeta']);
        $stmt->bindValue(5, $datos['motivo']);
        $stmt->execute();
        
        return $conexion->lastInsertId();
    }
    
    /**
     * Eliminar tarjeta
     */
    public function eliminar_tarjeta($tarjeta_id) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "DELETE FROM lp_tarjetas WHERE tarjeta_id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $tarjeta_id);
        $stmt->execute();
        
        return true;
    }
    
    /**
     * Contar tarjetas de un jugador
     */
    public function contar_tarjetas_jugador($jugador_id, $tipo = null) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT COUNT(*) as total FROM lp_tarjetas WHERE jugador_id = ?";
        
        if ($tipo !== null) {
            $sql .= " AND tipo_tarjeta = ?";
        }
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $jugador_id);
        
        if ($tipo !== null) {
            $stmt->bindValue(2, $tipo);
        }
        
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
    
    /**
     * Obtener jugadores con más tarjetas en una temporada
     */
    public function obtener_tabla_tarjetas($temporada_id, $tipo_tarjeta = null, $limite = 10) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT 
                j.jugador_id,
                j.jugador_nombre,
                j.jugador_numero,
                j.jugador_foto,
                e.equipo_nombre,
                e.equipo_logo,
                COUNT(CASE WHEN t.tipo_tarjeta = 'amarilla' THEN 1 END) as tarjetas_amarillas,
                COUNT(CASE WHEN t.tipo_tarjeta = 'roja' THEN 1 END) as tarjetas_rojas,
                COUNT(t.tarjeta_id) as total_tarjetas
                FROM lp_jugadores j
                INNER JOIN lp_equipos e ON j.equipo_id = e.equipo_id
                INNER JOIN lp_tarjetas t ON j.jugador_id = t.jugador_id
                INNER JOIN lp_partidos p ON t.partido_id = p.partido_id
                WHERE e.temporada_id = ? 
                AND j.jugador_estatus = 1";
        
        if ($tipo_tarjeta !== null) {
            $sql .= " AND t.tipo_tarjeta = ?";
        }
        
        $sql .= " GROUP BY j.jugador_id
                ORDER BY total_tarjetas DESC, j.jugador_nombre
                LIMIT ?";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $temporada_id);
        
        if ($tipo_tarjeta !== null) {
            $stmt->bindValue(2, $tipo_tarjeta);
            $stmt->bindValue(3, $limite, PDO::PARAM_INT);
        } else {
            $stmt->bindValue(2, $limite, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Verificar si jugador tiene tarjeta roja en partido
     */
    public function tiene_tarjeta_roja_en_partido($jugador_id, $partido_id) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT COUNT(*) as total 
                FROM lp_tarjetas 
                WHERE jugador_id = ? 
                AND partido_id = ? 
                AND tipo_tarjeta = 'roja'";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $jugador_id);
        $stmt->bindValue(2, $partido_id);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] > 0;
    }
    
    /**
     * Obtener estadísticas de tarjetas por equipo en una temporada
     */
    public function obtener_estadisticas_equipo($equipo_id) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT 
                COUNT(CASE WHEN tipo_tarjeta = 'amarilla' THEN 1 END) as tarjetas_amarillas,
                COUNT(CASE WHEN tipo_tarjeta = 'roja' THEN 1 END) as tarjetas_rojas,
                COUNT(tarjeta_id) as total_tarjetas
                FROM lp_tarjetas 
                WHERE equipo_id = ?";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $equipo_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>