<?php
/**
 * Modelo: Banner
 * Descripción: Manejo de banners informativos del dashboard
 * Tabla: lp_banners
 */

class Banner {
    
    /**
     * Obtener todos los banners
     */
    public function obtener_banners($activos_solo = false) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT * FROM lp_banners";
        
        if ($activos_solo) {
            $sql .= " WHERE banner_estatus = 1";
        }
        
        $sql .= " ORDER BY banner_orden ASC, banner_id DESC";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener banner por ID
     */
    public function obtener_banner_por_id($banner_id) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT * FROM lp_banners WHERE banner_id = ?";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $banner_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Crear nuevo banner
     */
    public function crear_banner($datos) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        // Obtener el siguiente orden
        $sql = "SELECT COALESCE(MAX(banner_orden), 0) + 1 as siguiente_orden FROM lp_banners";
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $orden = $result['siguiente_orden'];
        
        $sql = "INSERT INTO lp_banners 
                (banner_titulo, banner_imagen, banner_link, banner_orden, banner_estatus) 
                VALUES (?, ?, ?, ?, 1)";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $datos['banner_titulo']);
        $stmt->bindValue(2, $datos['banner_imagen']);
        $stmt->bindValue(3, $datos['banner_link']);
        $stmt->bindValue(4, $orden);
        $stmt->execute();
        
        return $conexion->lastInsertId();
    }
    
    /**
     * Actualizar banner
     */
    public function actualizar_banner($banner_id, $datos) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "UPDATE lp_banners SET 
                banner_titulo = ?,
                banner_imagen = ?,
                banner_link = ?
                WHERE banner_id = ?";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $datos['banner_titulo']);
        $stmt->bindValue(2, $datos['banner_imagen']);
        $stmt->bindValue(3, $datos['banner_link']);
        $stmt->bindValue(4, $banner_id);
        $stmt->execute();
        
        return true;
    }
    
    /**
     * Cambiar estatus del banner
     */
    public function cambiar_estatus($banner_id, $estatus) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "UPDATE lp_banners SET banner_estatus = ? WHERE banner_id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $estatus);
        $stmt->bindValue(2, $banner_id);
        $stmt->execute();
        
        return true;
    }
    
    /**
     * Eliminar banner
     */
    public function eliminar_banner($banner_id) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "DELETE FROM lp_banners WHERE banner_id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $banner_id);
        $stmt->execute();
        
        return true;
    }
    
    /**
     * Cambiar orden de los banners
     */
    public function cambiar_orden($banner_id, $nuevo_orden) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "UPDATE lp_banners SET banner_orden = ? WHERE banner_id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $nuevo_orden);
        $stmt->bindValue(2, $banner_id);
        $stmt->execute();
        
        return true;
    }
    
    /**
     * Reordenar banners automáticamente
     */
    public function reordenar_banners() {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        // Obtener todos los banners ordenados
        $sql = "SELECT banner_id FROM lp_banners ORDER BY banner_orden, banner_id";
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        $banners = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Reordenar
        $orden = 1;
        foreach ($banners as $banner) {
            $this->cambiar_orden($banner['banner_id'], $orden);
            $orden++;
        }
        
        return true;
    }
    
    /**
     * Mover banner hacia arriba en el orden
     */
    public function mover_arriba($banner_id) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        // Obtener orden actual
        $sql = "SELECT banner_orden FROM lp_banners WHERE banner_id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $banner_id);
        $stmt->execute();
        $banner = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$banner || $banner['banner_orden'] <= 1) {
            return false;
        }
        
        $orden_actual = $banner['banner_orden'];
        $orden_nuevo = $orden_actual - 1;
        
        // Intercambiar órdenes
        $sql = "UPDATE lp_banners SET banner_orden = ? WHERE banner_orden = ? AND banner_id != ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $orden_actual);
        $stmt->bindValue(2, $orden_nuevo);
        $stmt->bindValue(3, $banner_id);
        $stmt->execute();
        
        $sql = "UPDATE lp_banners SET banner_orden = ? WHERE banner_id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $orden_nuevo);
        $stmt->bindValue(2, $banner_id);
        $stmt->execute();
        
        return true;
    }
    
    /**
     * Mover banner hacia abajo en el orden
     */
    public function mover_abajo($banner_id) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        // Obtener orden actual y máximo
        $sql = "SELECT banner_orden FROM lp_banners WHERE banner_id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $banner_id);
        $stmt->execute();
        $banner = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $sql = "SELECT MAX(banner_orden) as max_orden FROM lp_banners";
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        $max = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$banner || $banner['banner_orden'] >= $max['max_orden']) {
            return false;
        }
        
        $orden_actual = $banner['banner_orden'];
        $orden_nuevo = $orden_actual + 1;
        
        // Intercambiar órdenes
        $sql = "UPDATE lp_banners SET banner_orden = ? WHERE banner_orden = ? AND banner_id != ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $orden_actual);
        $stmt->bindValue(2, $orden_nuevo);
        $stmt->bindValue(3, $banner_id);
        $stmt->execute();
        
        $sql = "UPDATE lp_banners SET banner_orden = ? WHERE banner_id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $orden_nuevo);
        $stmt->bindValue(2, $banner_id);
        $stmt->execute();
        
        return true;
    }
    
    /**
     * Contar banners activos
     */
    public function contar_banners_activos() {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT COUNT(*) as total FROM lp_banners WHERE banner_estatus = 1";
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
}
?>