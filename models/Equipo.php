<?php
/**
 * Modelo: Equipo
 * Descripción: Equipos registrados por temporada
 * Tabla: lp_equipos
 */

class Equipo {
    
    /**
     * Obtener todos los equipos
     */
    public function obtener_equipos($temporada_id = null) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT e.*, 
                t.temporada_nombre,
                t.temporada_id,
                l.liga_nombre,
                l.liga_id
                FROM lp_equipos e
                INNER JOIN lp_temporadas t ON e.temporada_id = t.temporada_id
                INNER JOIN lp_ligas l ON t.liga_id = l.liga_id";
        
        if ($temporada_id !== null) {
            $sql .= " WHERE e.temporada_id = ?";
        }
        
        $sql .= " ORDER BY e.equipo_id DESC";
        
        $stmt = $conexion->prepare($sql);
        
        if ($temporada_id !== null) {
            $stmt->bindValue(1, $temporada_id);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener equipos activos
     */
    public function obtener_equipos_activos($temporada_id = null) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT e.*, 
                t.temporada_nombre,
                l.liga_nombre
                FROM lp_equipos e
                INNER JOIN lp_temporadas t ON e.temporada_id = t.temporada_id
                INNER JOIN lp_ligas l ON t.liga_id = l.liga_id
                WHERE e.equipo_estatus = 1";
        
        if ($temporada_id !== null) {
            $sql .= " AND e.temporada_id = ?";
        }
        
        $sql .= " ORDER BY e.equipo_nombre";
        
        $stmt = $conexion->prepare($sql);
        
        if ($temporada_id !== null) {
            $stmt->bindValue(1, $temporada_id);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener equipo por ID
     */
    public function obtener_equipo_por_id($equipo_id) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT e.*, 
                t.temporada_nombre,
                t.temporada_id,
                l.liga_nombre,
                l.liga_id
                FROM lp_equipos e
                INNER JOIN lp_temporadas t ON e.temporada_id = t.temporada_id
                INNER JOIN lp_ligas l ON t.liga_id = l.liga_id
                WHERE e.equipo_id = ?";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $equipo_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Crear nuevo equipo
     */
    public function crear_equipo($datos) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "INSERT INTO lp_equipos 
                (temporada_id, equipo_nombre, equipo_logo, equipo_color_uniforme, 
                 capitan_nombre, capitan_telefono, capitan_correo, equipo_estatus) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 1)";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $datos['temporada_id']);
        $stmt->bindValue(2, $datos['equipo_nombre']);
        $stmt->bindValue(3, $datos['equipo_logo']);
        $stmt->bindValue(4, $datos['equipo_color_uniforme']);
        $stmt->bindValue(5, $datos['capitan_nombre']);
        $stmt->bindValue(6, $datos['capitan_telefono']);
        $stmt->bindValue(7, $datos['capitan_correo']);
        $stmt->execute();
        
        return $conexion->lastInsertId();
    }
    
    /**
     * Actualizar equipo
     */
    public function actualizar_equipo($equipo_id, $datos) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "UPDATE lp_equipos SET 
                temporada_id = ?,
                equipo_nombre = ?,
                equipo_logo = ?,
                equipo_color_uniforme = ?,
                capitan_nombre = ?,
                capitan_telefono = ?,
                capitan_correo = ?
                WHERE equipo_id = ?";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $datos['temporada_id']);
        $stmt->bindValue(2, $datos['equipo_nombre']);
        $stmt->bindValue(3, $datos['equipo_logo']);
        $stmt->bindValue(4, $datos['equipo_color_uniforme']);
        $stmt->bindValue(5, $datos['capitan_nombre']);
        $stmt->bindValue(6, $datos['capitan_telefono']);
        $stmt->bindValue(7, $datos['capitan_correo']);
        $stmt->bindValue(8, $equipo_id);
        $stmt->execute();
        
        return true;
    }
    
    /**
     * Cambiar estatus del equipo
     */
    public function cambiar_estatus($equipo_id, $estatus) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "UPDATE lp_equipos SET equipo_estatus = ? WHERE equipo_id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $estatus);
        $stmt->bindValue(2, $equipo_id);
        $stmt->execute();
        
        return true;
    }
    
    /**
     * Eliminar equipo (solo si no tiene jugadores ni partidos)
     */
    public function eliminar_equipo($equipo_id) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        // Verificar que no tenga jugadores
        $sql = "SELECT COUNT(*) as total FROM lp_jugadores WHERE equipo_id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $equipo_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['total'] > 0) {
            return false;
        }
        
        // Verificar que no tenga partidos
        $sql = "SELECT COUNT(*) as total FROM lp_partidos 
                WHERE equipo_local_id = ? OR equipo_visitante_id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $equipo_id);
        $stmt->bindValue(2, $equipo_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['total'] > 0) {
            return false;
        }
        
        // Eliminar equipo
        $sql = "DELETE FROM lp_equipos WHERE equipo_id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $equipo_id);
        $stmt->execute();
        
        return true;
    }
    
    /**
     * Verificar si existe un equipo con el mismo nombre en la misma temporada
     */
    public function verificar_nombre_existe($equipo_nombre, $temporada_id, $excluir_id = null) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT COUNT(*) as total FROM lp_equipos 
                WHERE equipo_nombre = ? AND temporada_id = ?";
        
        if ($excluir_id) {
            $sql .= " AND equipo_id != ?";
        }
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $equipo_nombre);
        $stmt->bindValue(2, $temporada_id);
        
        if ($excluir_id) {
            $stmt->bindValue(3, $excluir_id);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['total'] > 0;
    }
    
    /**
     * Obtener estadísticas de un equipo
     */
    public function obtener_estadisticas_equipo($equipo_id) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT 
                e.equipo_id,
                e.equipo_nombre,
                COUNT(DISTINCT j.jugador_id) as total_jugadores,
                COUNT(DISTINCT p.partido_id) as total_partidos,
                COUNT(DISTINCT g.gol_id) as total_goles,
                COUNT(DISTINCT t.tarjeta_id) as total_tarjetas
                FROM lp_equipos e
                LEFT JOIN lp_jugadores j ON e.equipo_id = j.equipo_id
                LEFT JOIN lp_partidos p ON (e.equipo_id = p.equipo_local_id OR e.equipo_id = p.equipo_visitante_id)
                LEFT JOIN lp_goles g ON e.equipo_id = g.equipo_id
                LEFT JOIN lp_tarjetas t ON e.equipo_id = t.equipo_id
                WHERE e.equipo_id = ?
                GROUP BY e.equipo_id";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $equipo_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener roster de jugadores de un equipo
     */
    public function obtener_jugadores_equipo($equipo_id) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT j.*,
                COUNT(DISTINCT g.gol_id) as total_goles,
                COUNT(DISTINCT CASE WHEN ta.tipo_tarjeta = 'amarilla' THEN ta.tarjeta_id END) as tarjetas_amarillas,
                COUNT(DISTINCT CASE WHEN ta.tipo_tarjeta = 'roja' THEN ta.tarjeta_id END) as tarjetas_rojas
                FROM lp_jugadores j
                LEFT JOIN lp_goles g ON j.jugador_id = g.jugador_id AND g.tipo_gol = 'normal'
                LEFT JOIN lp_tarjetas ta ON j.jugador_id = ta.jugador_id
                WHERE j.equipo_id = ? AND j.jugador_estatus = 1
                GROUP BY j.jugador_id
                ORDER BY j.jugador_numero, j.jugador_nombre";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $equipo_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Contar equipos activos por temporada
     */
    public function contar_equipos_activos($temporada_id) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT COUNT(*) as total FROM lp_equipos 
                WHERE temporada_id = ? AND equipo_estatus = 1";
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $temporada_id);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
    
    /**
     * Obtener equipos por liga (a través de temporadas activas)
     */
    public function obtener_equipos_por_liga($liga_id) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT e.*, 
                t.temporada_nombre
                FROM lp_equipos e
                INNER JOIN lp_temporadas t ON e.temporada_id = t.temporada_id
                WHERE t.liga_id = ? 
                AND e.equipo_estatus = 1
                AND t.temporada_estatus = 'en_curso'
                ORDER BY e.equipo_nombre";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $liga_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>