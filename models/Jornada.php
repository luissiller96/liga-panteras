<?php
/**
 * Modelo: Jornada
 * Descripción: Manejo de jornadas de la temporada
 * Tabla: lp_jornadas
 */

class Jornada {
    
    /**
     * Obtener todas las jornadas por temporada
     */
    public function obtener_jornadas_por_temporada($temporada_id) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT j.*,
                COUNT(DISTINCT p.partido_id) as total_partidos,
                COUNT(DISTINCT CASE WHEN p.partido_estatus = 'finalizado' THEN p.partido_id END) as partidos_finalizados
                FROM lp_jornadas j
                LEFT JOIN lp_partidos p ON j.jornada_id = p.jornada_id
                WHERE j.temporada_id = ?
                GROUP BY j.jornada_id
                ORDER BY j.jornada_numero";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $temporada_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener jornada por ID
     */
    public function obtener_jornada_por_id($jornada_id) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT j.*, t.temporada_nombre, l.liga_nombre,
                COUNT(DISTINCT p.partido_id) as total_partidos,
                COUNT(DISTINCT CASE WHEN p.partido_estatus = 'finalizado' THEN p.partido_id END) as partidos_finalizados
                FROM lp_jornadas j
                INNER JOIN lp_temporadas t ON j.temporada_id = t.temporada_id
                INNER JOIN lp_ligas l ON t.liga_id = l.liga_id
                LEFT JOIN lp_partidos p ON j.jornada_id = p.jornada_id
                WHERE j.jornada_id = ?
                GROUP BY j.jornada_id";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $jornada_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener jornada actual (en curso)
     */
    public function obtener_jornada_actual($temporada_id) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT j.*
                FROM lp_jornadas j
                WHERE j.temporada_id = ? 
                AND j.jornada_estatus = 'en_curso'
                ORDER BY j.jornada_numero DESC
                LIMIT 1";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $temporada_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Crear nueva jornada
     */
    public function crear_jornada($datos) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "INSERT INTO lp_jornadas 
                (temporada_id, jornada_numero, jornada_fecha, jornada_estatus) 
                VALUES (?, ?, ?, ?)";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $datos['temporada_id']);
        $stmt->bindValue(2, $datos['jornada_numero']);
        $stmt->bindValue(3, $datos['jornada_fecha']);
        $stmt->bindValue(4, $datos['jornada_estatus']);
        $stmt->execute();
        
        return $conexion->lastInsertId();
    }
    
    /**
     * Actualizar jornada
     */
    public function actualizar_jornada($jornada_id, $datos) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "UPDATE lp_jornadas SET 
                jornada_fecha = ?,
                jornada_estatus = ?
                WHERE jornada_id = ?";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $datos['jornada_fecha']);
        $stmt->bindValue(2, $datos['jornada_estatus']);
        $stmt->bindValue(3, $jornada_id);
        $stmt->execute();
        
        return true;
    }
    
    /**
     * Cambiar estatus de jornada
     */
    public function cambiar_estatus($jornada_id, $estatus) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "UPDATE lp_jornadas SET jornada_estatus = ? WHERE jornada_id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $estatus);
        $stmt->bindValue(2, $jornada_id);
        $stmt->execute();
        
        return true;
    }
    
    /**
     * Generar todas las jornadas para una temporada
     */
    public function generar_jornadas_temporada($temporada_id, $num_jornadas = 8) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        // Obtener fecha de inicio de la temporada
        $sql = "SELECT fecha_inicio, liga_dia_juego 
                FROM lp_temporadas t
                INNER JOIN lp_ligas l ON t.liga_id = l.liga_id
                WHERE t.temporada_id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $temporada_id);
        $stmt->execute();
        $temporada = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$temporada) {
            return false;
        }
        
        $fecha_inicio = new DateTime($temporada['fecha_inicio']);
        
        // Crear jornadas
        for ($i = 1; $i <= $num_jornadas; $i++) {
            $datos = [
                'temporada_id' => $temporada_id,
                'jornada_numero' => $i,
                'jornada_fecha' => $fecha_inicio->format('Y-m-d'),
                'jornada_estatus' => ($i == 1) ? 'programada' : 'programada'
            ];
            
            $this->crear_jornada($datos);
            
            // Avanzar 7 días (una semana)
            $fecha_inicio->modify('+7 days');
        }
        
        return true;
    }
    
    /**
     * Eliminar jornada (solo si no tiene partidos)
     */
    public function eliminar_jornada($jornada_id) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        // Verificar que no tenga partidos
        $sql = "SELECT COUNT(*) as total FROM lp_partidos WHERE jornada_id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $jornada_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['total'] > 0) {
            return false;
        }
        
        // Eliminar jornada
        $sql = "DELETE FROM lp_jornadas WHERE jornada_id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $jornada_id);
        $stmt->execute();
        
        return true;
    }
    
    /**
     * Obtener estadísticas de la jornada
     */
    public function obtener_estadisticas_jornada($jornada_id) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT 
                COUNT(DISTINCT p.partido_id) as total_partidos,
                COUNT(DISTINCT CASE WHEN p.partido_estatus = 'finalizado' THEN p.partido_id END) as partidos_finalizados,
                SUM(CASE WHEN p.partido_estatus = 'finalizado' THEN p.goles_local + p.goles_visitante END) as total_goles,
                COUNT(DISTINCT g.gol_id) as goles_registrados,
                COUNT(DISTINCT CASE WHEN t.tipo_tarjeta = 'amarilla' THEN t.tarjeta_id END) as tarjetas_amarillas,
                COUNT(DISTINCT CASE WHEN t.tipo_tarjeta = 'roja' THEN t.tarjeta_id END) as tarjetas_rojas
                FROM lp_jornadas j
                LEFT JOIN lp_partidos p ON j.jornada_id = p.jornada_id
                LEFT JOIN lp_goles g ON p.partido_id = g.partido_id
                LEFT JOIN lp_tarjetas t ON p.partido_id = t.partido_id
                WHERE j.jornada_id = ?";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $jornada_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>