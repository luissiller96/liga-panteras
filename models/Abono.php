<?php
/**
 * Modelo: Abono
 * Descripción: Registro de abonos a inscripciones
 * Tabla: lp_abonos_inscripcion
 */

class Abono {
    
    /**
     * Obtener todos los abonos
     */
    public function obtener_abonos($pago_id = null) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT a.*, 
                p.equipo_id,
                e.equipo_nombre,
                t.temporada_nombre,
                l.liga_nombre,
                u.usu_nom,
                u.usu_ape
                FROM lp_abonos_inscripcion a
                INNER JOIN lp_pagos_inscripcion p ON a.pago_id = p.pago_id
                INNER JOIN lp_equipos e ON p.equipo_id = e.equipo_id
                INNER JOIN lp_temporadas t ON p.temporada_id = t.temporada_id
                INNER JOIN lp_ligas l ON t.liga_id = l.liga_id
                LEFT JOIN tm_usuario u ON a.registrado_por = u.usu_id";
        
        if ($pago_id !== null) {
            $sql .= " WHERE a.pago_id = ?";
        }
        
        $sql .= " ORDER BY a.fecha_abono DESC";
        
        $stmt = $conexion->prepare($sql);
        
        if ($pago_id !== null) {
            $stmt->bindValue(1, $pago_id);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener abono por ID
     */
    public function obtener_abono_por_id($abono_id) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT a.*, 
                p.equipo_id,
                e.equipo_nombre,
                t.temporada_nombre,
                u.usu_nom,
                u.usu_ape
                FROM lp_abonos_inscripcion a
                INNER JOIN lp_pagos_inscripcion p ON a.pago_id = p.pago_id
                INNER JOIN lp_equipos e ON p.equipo_id = e.equipo_id
                INNER JOIN lp_temporadas t ON p.temporada_id = t.temporada_id
                LEFT JOIN tm_usuario u ON a.registrado_por = u.usu_id
                WHERE a.abono_id = ?";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $abono_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Registrar abono
     */
    public function registrar_abono($datos) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        // Verificar que no se exceda el monto pendiente
        $sql = "SELECT monto_pendiente FROM lp_pagos_inscripcion WHERE pago_id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $datos['pago_id']);
        $stmt->execute();
        $pago = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($datos['monto_abono'] > $pago['monto_pendiente']) {
            return ['error' => 'El monto del abono excede el monto pendiente'];
        }
        
        // Registrar abono
        $sql = "INSERT INTO lp_abonos_inscripcion 
                (pago_id, monto_abono, fecha_abono, metodo_pago, referencia_pago, comentarios, registrado_por) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $datos['pago_id']);
        $stmt->bindValue(2, $datos['monto_abono']);
        $stmt->bindValue(3, $datos['fecha_abono']);
        $stmt->bindValue(4, $datos['metodo_pago']);
        $stmt->bindValue(5, $datos['referencia_pago']);
        $stmt->bindValue(6, $datos['comentarios']);
        $stmt->bindValue(7, $datos['registrado_por']);
        $stmt->execute();
        
        $abono_id = $conexion->lastInsertId();
        
        // Actualizar monto pagado en la tabla de pagos
        require_once("Pago.php");
        $pago_model = new Pago();
        $pago_model->actualizar_monto_pagado($datos['pago_id']);
        
        return $abono_id;
    }
    
    /**
     * Eliminar abono
     */
    public function eliminar_abono($abono_id) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        // Obtener pago_id antes de eliminar
        $sql = "SELECT pago_id FROM lp_abonos_inscripcion WHERE abono_id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $abono_id);
        $stmt->execute();
        $abono = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$abono) {
            return false;
        }
        
        // Eliminar abono
        $sql = "DELETE FROM lp_abonos_inscripcion WHERE abono_id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $abono_id);
        $stmt->execute();
        
        // Actualizar monto pagado en la tabla de pagos
        require_once("Pago.php");
        $pago_model = new Pago();
        $pago_model->actualizar_monto_pagado($abono['pago_id']);
        
        return true;
    }
    
    /**
     * Obtener abonos recientes
     */
    public function obtener_abonos_recientes($limite = 10) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT a.*, 
                e.equipo_nombre,
                t.temporada_nombre,
                l.liga_nombre,
                u.usu_nom
                FROM lp_abonos_inscripcion a
                INNER JOIN lp_pagos_inscripcion p ON a.pago_id = p.pago_id
                INNER JOIN lp_equipos e ON p.equipo_id = e.equipo_id
                INNER JOIN lp_temporadas t ON p.temporada_id = t.temporada_id
                INNER JOIN lp_ligas l ON t.liga_id = l.liga_id
                LEFT JOIN tm_usuario u ON a.registrado_por = u.usu_id
                ORDER BY a.fecha_abono DESC
                LIMIT ?";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $limite, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener abonos por temporada
     */
    public function obtener_abonos_por_temporada($temporada_id) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT a.*, 
                e.equipo_nombre,
                u.usu_nom,
                u.usu_ape
                FROM lp_abonos_inscripcion a
                INNER JOIN lp_pagos_inscripcion p ON a.pago_id = p.pago_id
                INNER JOIN lp_equipos e ON p.equipo_id = e.equipo_id
                LEFT JOIN tm_usuario u ON a.registrado_por = u.usu_id
                WHERE p.temporada_id = ?
                ORDER BY a.fecha_abono DESC";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $temporada_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener estadísticas de abonos por temporada
     */
    public function obtener_estadisticas_abonos($temporada_id) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT 
                COUNT(a.abono_id) as total_abonos,
                SUM(a.monto_abono) as monto_total_abonos,
                AVG(a.monto_abono) as promedio_abono,
                COUNT(CASE WHEN a.metodo_pago = 'efectivo' THEN 1 END) as abonos_efectivo,
                COUNT(CASE WHEN a.metodo_pago = 'transferencia' THEN 1 END) as abonos_transferencia,
                COUNT(CASE WHEN a.metodo_pago = 'tarjeta' THEN 1 END) as abonos_tarjeta
                FROM lp_abonos_inscripcion a
                INNER JOIN lp_pagos_inscripcion p ON a.pago_id = p.pago_id
                WHERE p.temporada_id = ?";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $temporada_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener abonos por método de pago
     */
    public function obtener_abonos_por_metodo($temporada_id, $metodo_pago) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT a.*, 
                e.equipo_nombre,
                u.usu_nom
                FROM lp_abonos_inscripcion a
                INNER JOIN lp_pagos_inscripcion p ON a.pago_id = p.pago_id
                INNER JOIN lp_equipos e ON p.equipo_id = e.equipo_id
                LEFT JOIN tm_usuario u ON a.registrado_por = u.usu_id
                WHERE p.temporada_id = ? AND a.metodo_pago = ?
                ORDER BY a.fecha_abono DESC";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $temporada_id);
        $stmt->bindValue(2, $metodo_pago);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener total de abonos de un pago
     */
    public function obtener_total_abonos($pago_id) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT COALESCE(SUM(monto_abono), 0) as total 
                FROM lp_abonos_inscripcion 
                WHERE pago_id = ?";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $pago_id);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
}
?>