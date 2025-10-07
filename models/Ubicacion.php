<?php
/**
 * Modelo: Ubicacion
 * Descripción: Manejo de ubicaciones de canchas
 * Tabla: lp_ubicacion_canchas
 */

class Ubicacion {
    
    /**
     * Obtener todas las ubicaciones
     */
    public function obtener_ubicaciones($activas_solo = false) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT * FROM lp_ubicacion_canchas";
        
        if ($activas_solo) {
            $sql .= " WHERE ubicacion_estatus = 1";
        }
        
        $sql .= " ORDER BY nombre_lugar";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener ubicación por ID
     */
    public function obtener_ubicacion_por_id($ubicacion_id) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT * FROM lp_ubicacion_canchas WHERE ubicacion_id = ?";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $ubicacion_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Crear nueva ubicación
     */
    public function crear_ubicacion($datos) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "INSERT INTO lp_ubicacion_canchas 
                (nombre_lugar, direccion, coordenadas_maps, telefono_contacto, horarios_atencion, ubicacion_estatus) 
                VALUES (?, ?, ?, ?, ?, 1)";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $datos['nombre_lugar']);
        $stmt->bindValue(2, $datos['direccion']);
        $stmt->bindValue(3, $datos['coordenadas_maps']);
        $stmt->bindValue(4, $datos['telefono_contacto']);
        $stmt->bindValue(5, $datos['horarios_atencion']);
        $stmt->execute();
        
        return $conexion->lastInsertId();
    }
    
    /**
     * Actualizar ubicación
     */
    public function actualizar_ubicacion($ubicacion_id, $datos) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "UPDATE lp_ubicacion_canchas SET 
                nombre_lugar = ?,
                direccion = ?,
                coordenadas_maps = ?,
                telefono_contacto = ?,
                horarios_atencion = ?
                WHERE ubicacion_id = ?";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $datos['nombre_lugar']);
        $stmt->bindValue(2, $datos['direccion']);
        $stmt->bindValue(3, $datos['coordenadas_maps']);
        $stmt->bindValue(4, $datos['telefono_contacto']);
        $stmt->bindValue(5, $datos['horarios_atencion']);
        $stmt->bindValue(6, $ubicacion_id);
        $stmt->execute();
        
        return true;
    }
    
    /**
     * Cambiar estatus de la ubicación
     */
    public function cambiar_estatus($ubicacion_id, $estatus) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "UPDATE lp_ubicacion_canchas SET ubicacion_estatus = ? WHERE ubicacion_id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $estatus);
        $stmt->bindValue(2, $ubicacion_id);
        $stmt->execute();
        
        return true;
    }
    
    /**
     * Eliminar ubicación
     */
    public function eliminar_ubicacion($ubicacion_id) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "DELETE FROM lp_ubicacion_canchas WHERE ubicacion_id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $ubicacion_id);
        $stmt->execute();
        
        return true;
    }
    
    /**
     * Buscar ubicaciones por nombre o dirección
     */
    public function buscar_ubicaciones($termino) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT * FROM lp_ubicacion_canchas 
                WHERE ubicacion_estatus = 1 
                AND (nombre_lugar LIKE ? OR direccion LIKE ?)
                ORDER BY nombre_lugar";
        
        $stmt = $conexion->prepare($sql);
        $termino_busqueda = "%{$termino}%";
        $stmt->bindValue(1, $termino_busqueda);
        $stmt->bindValue(2, $termino_busqueda);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Contar ubicaciones activas
     */
    public function contar_ubicaciones_activas() {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT COUNT(*) as total FROM lp_ubicacion_canchas WHERE ubicacion_estatus = 1";
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
    
    /**
     * Obtener ubicación principal (la primera activa)
     */
    public function obtener_ubicacion_principal() {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT * FROM lp_ubicacion_canchas 
                WHERE ubicacion_estatus = 1 
                ORDER BY ubicacion_id 
                LIMIT 1";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>