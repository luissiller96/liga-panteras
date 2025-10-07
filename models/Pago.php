<?php
/**
 * Modelo: Pago
 * Descripción: Control de pagos de inscripción por equipo
 * Tabla: lp_pagos_inscripcion
 */

class Pago {
    
    /**
     * Obtener todos los pagos
     */
    public function obtener_pagos($temporada_id = null) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT p.*, 
                e.equipo_nombre,
                e.equipo_logo,
                e.capitan_nombre,
                e.capitan_telefono,
                t.temporada_nombre,
                l.liga_nombre
                FROM lp_pagos_inscripcion p
                INNER JOIN lp_equipos e ON p.equipo_id = e.equipo_id
                INNER JOIN lp_temporadas t ON p.temporada_id = t.temporada_id
                INNER JOIN lp_ligas l ON t.liga_id = l.liga_id";
        
        if ($temporada_id !== null) {
            $sql .= " WHERE p.temporada_id = ?";
        }
        
        $sql .= " ORDER BY p.estatus_pago, e.equipo_nombre";
        
        $stmt = $conexion->prepare($sql);
        
        if ($temporada_id !== null) {
            $stmt->bindValue(1, $temporada_id);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener pago por equipo
     */
    public function obtener_pago_por_equipo($equipo_id) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT p.*, 
                e.equipo_nombre,
                t.temporada_nombre,
                l.liga_nombre
                FROM lp_pagos_inscripcion p
                INNER JOIN lp_equipos e ON p.equipo_id = e.equipo_id
                INNER JOIN lp_temporadas t ON p.temporada_id = t.temporada_id
                INNER JOIN lp_ligas l ON t.liga_id = l.liga_id
                WHERE p.equipo_id = ?";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $equipo_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener pago por ID
     */
    public function obtener_pago_por_id($pago_id) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT p.*, 
                e.equipo_nombre,
                e.equipo_logo,
                e.capitan_nombre,
                e.capitan_telefono,
                t.temporada_nombre,
                l.liga_nombre
                FROM lp_pagos_inscripcion p
                INNER JOIN lp_equipos e ON p.equipo_id = e.equipo_id
                INNER JOIN lp_temporadas t ON p.temporada_id = t.temporada_id
                INNER JOIN lp_ligas l ON t.liga_id = l.liga_id
                WHERE p.pago_id = ?";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $pago_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Crear registro de pago
     */
    public function crear_pago($datos) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "INSERT INTO lp_pagos_inscripcion 
                (equipo_id, temporada_id, monto_total, monto_pagado, fecha_limite_pago, estatus_pago) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $datos['equipo_id']);
        $stmt->bindValue(2, $datos['temporada_id']);
        $stmt->bindValue(3, $datos['monto_total']);
        $stmt->bindValue(4, $datos['monto_pagado']);
        $stmt->bindValue(5, $datos['fecha_limite_pago']);
        $stmt->bindValue(6, $datos['estatus_pago']);
        $stmt->execute();
        
        return $conexion->lastInsertId();
    }
    
    /**
     * Actualizar monto pagado
     */
    public function actualizar_monto_pagado($pago_id) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        // Calcular total de abonos
        $sql = "SELECT COALESCE(SUM(monto_abono), 0) as total_abonos 
                FROM lp_abonos_inscripcion 
                WHERE pago_id = ?";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $pago_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $total_abonos = $result['total_abonos'];
        
        // Actualizar monto pagado
        $sql = "UPDATE lp_pagos_inscripcion SET monto_pagado = ? WHERE pago_id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $total_abonos);
        $stmt->bindValue(2, $pago_id);
        $stmt->execute();
        
        // Actualizar estatus
        $this->actualizar_estatus_pago($pago_id);
        
        return true;
    }
    
    /**
     * Actualizar estatus del pago
     */
    private function actualizar_estatus_pago($pago_id) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        // Obtener datos del pago
        $sql = "SELECT monto_total, monto_pagado, monto_pendiente FROM lp_pagos_inscripcion WHERE pago_id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $pago_id);
        $stmt->execute();
        $pago = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Determinar estatus
        $estatus = 'pendiente';
        if ($pago['monto_pendiente'] == 0) {
            $estatus = 'liquidado';
        } elseif ($pago['monto_pagado'] > 0) {
            $estatus = 'parcial';
        }
        
        // Actualizar estatus
        $sql = "UPDATE lp_pagos_inscripcion SET estatus_pago = ? WHERE pago_id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $estatus);
        $stmt->bindValue(2, $pago_id);
        $stmt->execute();
    }
    
    /**
     * Obtener pagos pendientes
     */
    public function obtener_pagos_pendientes($temporada_id = null) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT p.*, 
                e.equipo_nombre,
                e.capitan_nombre,
                e.capitan_telefono,
                t.temporada_nombre,
                l.liga_nombre
                FROM lp_pagos_inscripcion p
                INNER JOIN lp_equipos e ON p.equipo_id = e.equipo_id
                INNER JOIN lp_temporadas t ON p.temporada_id = t.temporada_id
                INNER JOIN lp_ligas l ON t.liga_id = l.liga_id
                WHERE p.estatus_pago IN ('pendiente', 'parcial')";
        
        if ($temporada_id !== null) {
            $sql .= " AND p.temporada_id = ?";
        }
        
        $sql .= " ORDER BY p.fecha_limite_pago, e.equipo_nombre";
        
        $stmt = $conexion->prepare($sql);
        
        if ($temporada_id !== null) {
            $stmt->bindValue(1, $temporada_id);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener estadísticas de pagos de una temporada
     */
    public function obtener_estadisticas_pagos($temporada_id) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT 
                COUNT(*) as total_equipos,
                SUM(monto_total) as ingresos_esperados,
                SUM(monto_pagado) as ingresos_reales,
                SUM(monto_pendiente) as monto_pendiente,
                COUNT(CASE WHEN estatus_pago = 'liquidado' THEN 1 END) as equipos_liquidados,
                COUNT(CASE WHEN estatus_pago = 'parcial' THEN 1 END) as equipos_parcial,
                COUNT(CASE WHEN estatus_pago = 'pendiente' THEN 1 END) as equipos_pendientes
                FROM lp_pagos_inscripcion
                WHERE temporada_id = ?";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $temporada_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener historial de abonos del pago
     */
    public function obtener_historial_abonos($pago_id) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT a.*, u.usu_nom, u.usu_ape
                FROM lp_abonos_inscripcion a
                LEFT JOIN tm_usuario u ON a.registrado_por = u.usu_id
                WHERE a.pago_id = ?
                ORDER BY a.fecha_abono DESC";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $pago_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Actualizar fecha límite de pago
     */
    public function actualizar_fecha_limite($pago_id, $fecha_limite) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "UPDATE lp_pagos_inscripcion SET fecha_limite_pago = ? WHERE pago_id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $fecha_limite);
        $stmt->bindValue(2, $pago_id);
        $stmt->execute();
        
        return true;
    }
}
?>