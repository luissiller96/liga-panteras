<?php
/**
 * Modelo: Liga
 * Descripción: Catálogo de ligas/categorías disponibles
 * Tabla: lp_ligas
 */

class Liga {
    
    /**
     * Obtener todas las ligas
     */
    public function obtener_ligas($activas_solo = false) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT * FROM lp_ligas";
        
        if ($activas_solo) {
            $sql .= " WHERE liga_estatus = 1";
        }
        
        $sql .= " ORDER BY liga_id DESC";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener liga por ID
     */
    public function obtener_liga_por_id($liga_id) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT * FROM lp_ligas WHERE liga_id = ?";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $liga_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Crear nueva liga
     */
    public function crear_liga($datos) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "INSERT INTO lp_ligas 
                (liga_nombre, liga_descripcion, liga_dia_juego, liga_logo, liga_estatus) 
                VALUES (?, ?, ?, ?, 1)";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $datos['liga_nombre']);
        $stmt->bindValue(2, $datos['liga_descripcion']);
        $stmt->bindValue(3, $datos['liga_dia_juego']);
        $stmt->bindValue(4, $datos['liga_logo']);
        $stmt->execute();
        
        return $conexion->lastInsertId();
    }
    
    /**
     * Actualizar liga
     */
    public function actualizar_liga($liga_id, $datos) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "UPDATE lp_ligas SET 
                liga_nombre = ?,
                liga_descripcion = ?,
                liga_dia_juego = ?,
                liga_logo = ?
                WHERE liga_id = ?";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $datos['liga_nombre']);
        $stmt->bindValue(2, $datos['liga_descripcion']);
        $stmt->bindValue(3, $datos['liga_dia_juego']);
        $stmt->bindValue(4, $datos['liga_logo']);
        $stmt->bindValue(5, $liga_id);
        $stmt->execute();
        
        return true;
    }
    
    /**
     * Cambiar estatus de la liga
     */
    public function cambiar_estatus($liga_id, $estatus) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "UPDATE lp_ligas SET liga_estatus = ? WHERE liga_id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $estatus);
        $stmt->bindValue(2, $liga_id);
        $stmt->execute();
        
        return true;
    }
    
    /**
     * Eliminar liga (solo si no tiene temporadas)
     */
    public function eliminar_liga($liga_id) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        // Verificar que no tenga temporadas
        $sql = "SELECT COUNT(*) as total FROM lp_temporadas WHERE liga_id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $liga_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['total'] > 0) {
            return false;
        }
        
        // Eliminar liga
        $sql = "DELETE FROM lp_ligas WHERE liga_id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $liga_id);
        $stmt->execute();
        
        return true;
    }
    
    /**
     * Verificar si existe una liga con el mismo nombre
     */
    public function verificar_nombre_existe($liga_nombre, $excluir_id = null) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT COUNT(*) as total FROM lp_ligas WHERE liga_nombre = ?";
        
        if ($excluir_id) {
            $sql .= " AND liga_id != ?";
        }
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $liga_nombre);
        
        if ($excluir_id) {
            $stmt->bindValue(2, $excluir_id);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['total'] > 0;
    }
    
    /**
     * Obtener estadísticas de una liga
     */
    public function obtener_estadisticas_liga($liga_id) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT 
                l.liga_id,
                l.liga_nombre,
                COUNT(DISTINCT t.temporada_id) as total_temporadas,
                COUNT(DISTINCT e.equipo_id) as total_equipos,
                COUNT(DISTINCT j.jugador_id) as total_jugadores,
                COUNT(DISTINCT p.partido_id) as total_partidos
                FROM lp_ligas l
                LEFT JOIN lp_temporadas t ON l.liga_id = t.liga_id
                LEFT JOIN lp_equipos e ON t.temporada_id = e.temporada_id
                LEFT JOIN lp_jugadores j ON e.equipo_id = j.equipo_id
                LEFT JOIN lp_partidos p ON t.temporada_id = p.temporada_id
                WHERE l.liga_id = ?
                GROUP BY l.liga_id";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $liga_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener ligas con sus temporadas activas
     */
    public function obtener_ligas_con_temporadas() {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT 
                l.*,
                COUNT(t.temporada_id) as total_temporadas,
                MAX(CASE WHEN t.temporada_estatus = 'en_curso' THEN t.temporada_nombre END) as temporada_actual
                FROM lp_ligas l
                LEFT JOIN lp_temporadas t ON l.liga_id = t.liga_id
                WHERE l.liga_estatus = 1
                GROUP BY l.liga_id
                ORDER BY l.liga_nombre";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Contar ligas activas
     */
    public function contar_ligas_activas() {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT COUNT(*) as total FROM lp_ligas WHERE liga_estatus = 1";
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
    
    /**
     * Obtener días de juego disponibles
     */
    public function obtener_dias_juego() {
        return [
            'lunes' => 'Lunes',
            'martes' => 'Martes',
            'miercoles' => 'Miércoles',
            'jueves' => 'Jueves',
            'viernes' => 'Viernes',
            'sabado' => 'Sábado',
            'domingo' => 'Domingo'
        ];
    }
}
?>