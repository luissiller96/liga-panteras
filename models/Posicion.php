<?php
/**
 * Modelo: Posicion
 * Descripción: Manejo de tabla de posiciones
 * Tabla: lp_tabla_posiciones
 */

class Posicion {
    
    /**
     * Obtener tabla de posiciones de una temporada
     */
    public function obtener_tabla_posiciones($temporada_id) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT p.*, 
                e.equipo_nombre,
                e.equipo_logo,
                e.equipo_color_uniforme
                FROM lp_tabla_posiciones p
                INNER JOIN lp_equipos e ON p.equipo_id = e.equipo_id
                WHERE p.temporada_id = ?
                ORDER BY p.puntos DESC, 
                         p.diferencia_goles DESC, 
                         p.goles_favor DESC,
                         e.equipo_nombre ASC";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $temporada_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener posición de un equipo
     */
    public function obtener_posicion_equipo($equipo_id) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT p.*, 
                e.equipo_nombre,
                e.equipo_logo,
                e.temporada_id
                FROM lp_tabla_posiciones p
                INNER JOIN lp_equipos e ON p.equipo_id = e.equipo_id
                WHERE p.equipo_id = ?";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $equipo_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Actualizar posiciones desde un partido finalizado
     */
    public function actualizar_posiciones_desde_partido($partido_id) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        // Obtener datos del partido
        $sql = "SELECT * FROM lp_partidos WHERE partido_id = ? AND partido_estatus = 'finalizado'";
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $partido_id);
        $stmt->execute();
        $partido = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$partido) {
            return false;
        }
        
        // Solo actualizar si es jornada regular
        if ($partido['tipo_partido'] != 'jornada_regular') {
            return false;
        }
        
        $equipo_local_id = $partido['equipo_local_id'];
        $equipo_visitante_id = $partido['equipo_visitante_id'];
        $goles_local = $partido['goles_local'];
        $goles_visitante = $partido['goles_visitante'];
        
        // Determinar resultado
        if ($goles_local > $goles_visitante) {
            // Victoria local
            $this->actualizar_equipo($equipo_local_id, 'victoria', $goles_local, $goles_visitante);
            $this->actualizar_equipo($equipo_visitante_id, 'derrota', $goles_visitante, $goles_local);
        } elseif ($goles_visitante > $goles_local) {
            // Victoria visitante
            $this->actualizar_equipo($equipo_visitante_id, 'victoria', $goles_visitante, $goles_local);
            $this->actualizar_equipo($equipo_local_id, 'derrota', $goles_local, $goles_visitante);
        } else {
            // Empate
            $this->actualizar_equipo($equipo_local_id, 'empate', $goles_local, $goles_visitante);
            $this->actualizar_equipo($equipo_visitante_id, 'empate', $goles_visitante, $goles_local);
        }
        
        return true;
    }
    
    /**
     * Actualizar estadísticas de un equipo
     */
    private function actualizar_equipo($equipo_id, $resultado, $goles_favor, $goles_contra) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        // Calcular puntos
        $puntos = 0;
        if ($resultado == 'victoria') {
            $puntos = 3;
        } elseif ($resultado == 'empate') {
            $puntos = 1;
        }
        
        // Actualizar tabla de posiciones
        $sql = "UPDATE lp_tabla_posiciones SET 
                partidos_jugados = partidos_jugados + 1,
                partidos_ganados = partidos_ganados + ?,
                partidos_empatados = partidos_empatados + ?,
                partidos_perdidos = partidos_perdidos + ?,
                goles_favor = goles_favor + ?,
                goles_contra = goles_contra + ?,
                puntos = puntos + ?
                WHERE equipo_id = ?";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, ($resultado == 'victoria') ? 1 : 0);
        $stmt->bindValue(2, ($resultado == 'empate') ? 1 : 0);
        $stmt->bindValue(3, ($resultado == 'derrota') ? 1 : 0);
        $stmt->bindValue(4, $goles_favor);
        $stmt->bindValue(5, $goles_contra);
        $stmt->bindValue(6, $puntos);
        $stmt->bindValue(7, $equipo_id);
        $stmt->execute();
    }
    
    /**
     * Recalcular toda la tabla de posiciones desde cero
     */
    public function recalcular_tabla_completa($temporada_id) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        // Reiniciar tabla de posiciones
        $sql = "UPDATE lp_tabla_posiciones SET 
                partidos_jugados = 0,
                partidos_ganados = 0,
                partidos_empatados = 0,
                partidos_perdidos = 0,
                goles_favor = 0,
                goles_contra = 0,
                puntos = 0
                WHERE temporada_id = ?";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $temporada_id);
        $stmt->execute();
        
        // Obtener todos los partidos finalizados de la temporada (solo jornada regular)
        $sql = "SELECT partido_id FROM lp_partidos 
                WHERE temporada_id = ? 
                AND partido_estatus = 'finalizado'
                AND tipo_partido = 'jornada_regular'
                ORDER BY fecha_partido, hora_partido";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $temporada_id);
        $stmt->execute();
        $partidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Procesar cada partido
        foreach ($partidos as $partido) {
            $this->actualizar_posiciones_desde_partido($partido['partido_id']);
        }
        
        return true;
    }
    
    /**
     * Obtener top 8 equipos para playoffs
     */
    public function obtener_clasificados_playoffs($temporada_id) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT p.*, 
                e.equipo_nombre,
                e.equipo_logo
                FROM lp_tabla_posiciones p
                INNER JOIN lp_equipos e ON p.equipo_id = e.equipo_id
                WHERE p.temporada_id = ?
                ORDER BY p.puntos DESC, 
                         p.diferencia_goles DESC, 
                         p.goles_favor DESC
                LIMIT 8";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $temporada_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener posición numérica de un equipo en la tabla
     */
    public function obtener_posicion_numerica($equipo_id) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        // Obtener temporada del equipo
        $sql = "SELECT temporada_id FROM lp_equipos WHERE equipo_id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $equipo_id);
        $stmt->execute();
        $equipo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$equipo) {
            return null;
        }
        
        // Obtener tabla ordenada
        $tabla = $this->obtener_tabla_posiciones($equipo['temporada_id']);
        
        // Buscar posición
        $posicion = 1;
        foreach ($tabla as $registro) {
            if ($registro['equipo_id'] == $equipo_id) {
                return $posicion;
            }
            $posicion++;
        }
        
        return null;
    }
    
    /**
     * Obtener estadísticas resumidas de la temporada
     */
    public function obtener_estadisticas_temporada($temporada_id) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT 
                COUNT(DISTINCT p.equipo_id) as total_equipos,
                SUM(p.partidos_jugados) / 2 as total_partidos,
                SUM(p.goles_favor) as total_goles,
                AVG(p.puntos) as promedio_puntos,
                MAX(p.puntos) as max_puntos,
                MIN(p.puntos) as min_puntos
                FROM lp_tabla_posiciones p
                WHERE p.temporada_id = ?";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $temporada_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>