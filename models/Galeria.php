<?php
/**
 * Modelo: Galeria
 * Descripción: Manejo de galería de fotos
 * Tabla: lp_galeria
 */

class Galeria {
    
    /**
     * Obtener todas las fotos
     */
    public function obtener_fotos($activas_solo = false) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT g.*, 
                p.fecha_partido,
                el.equipo_nombre AS equipo_local,
                ev.equipo_nombre AS equipo_visitante,
                j.jornada_numero,
                t.temporada_nombre,
                l.liga_nombre
                FROM lp_galeria g
                LEFT JOIN lp_partidos p ON g.partido_id = p.partido_id
                LEFT JOIN lp_jornadas j ON p.jornada_id = j.jornada_id
                LEFT JOIN lp_equipos el ON p.equipo_local_id = el.equipo_id
                LEFT JOIN lp_equipos ev ON p.equipo_visitante_id = ev.equipo_id
                LEFT JOIN lp_temporadas t ON p.temporada_id = t.temporada_id
                LEFT JOIN lp_ligas l ON t.liga_id = l.liga_id";
        
        if ($activas_solo) {
            $sql .= " WHERE g.foto_estatus = 1";
        }
        
        $sql .= " ORDER BY g.fecha_foto DESC, g.fecha_subida DESC";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener fotos por partido
     */
    public function obtener_fotos_por_partido($partido_id) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT * FROM lp_galeria 
                WHERE partido_id = ? AND foto_estatus = 1
                ORDER BY fecha_foto DESC";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $partido_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener foto por ID
     */
    public function obtener_foto_por_id($foto_id) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT g.*, 
                p.fecha_partido,
                el.equipo_nombre AS equipo_local,
                ev.equipo_nombre AS equipo_visitante,
                j.jornada_numero
                FROM lp_galeria g
                LEFT JOIN lp_partidos p ON g.partido_id = p.partido_id
                LEFT JOIN lp_jornadas j ON p.jornada_id = j.jornada_id
                LEFT JOIN lp_equipos el ON p.equipo_local_id = el.equipo_id
                LEFT JOIN lp_equipos ev ON p.equipo_visitante_id = ev.equipo_id
                WHERE g.foto_id = ?";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $foto_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Subir nueva foto
     */
    public function subir_foto($datos) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "INSERT INTO lp_galeria 
                (foto_titulo, foto_imagen, foto_descripcion, partido_id, fecha_foto, foto_estatus) 
                VALUES (?, ?, ?, ?, ?, 1)";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $datos['foto_titulo']);
        $stmt->bindValue(2, $datos['foto_imagen']);
        $stmt->bindValue(3, $datos['foto_descripcion']);
        $stmt->bindValue(4, $datos['partido_id']);
        $stmt->bindValue(5, $datos['fecha_foto']);
        $stmt->execute();
        
        return $conexion->lastInsertId();
    }
    
    /**
     * Actualizar foto
     */
    public function actualizar_foto($foto_id, $datos) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "UPDATE lp_galeria SET 
                foto_titulo = ?,
                foto_descripcion = ?,
                partido_id = ?,
                fecha_foto = ?
                WHERE foto_id = ?";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $datos['foto_titulo']);
        $stmt->bindValue(2, $datos['foto_descripcion']);
        $stmt->bindValue(3, $datos['partido_id']);
        $stmt->bindValue(4, $datos['fecha_foto']);
        $stmt->bindValue(5, $foto_id);
        $stmt->execute();
        
        return true;
    }
    
    /**
     * Cambiar estatus de la foto
     */
    public function cambiar_estatus($foto_id, $estatus) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "UPDATE lp_galeria SET foto_estatus = ? WHERE foto_id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $estatus);
        $stmt->bindValue(2, $foto_id);
        $stmt->execute();
        
        return true;
    }
    
    /**
     * Eliminar foto
     */
    public function eliminar_foto($foto_id) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "DELETE FROM lp_galeria WHERE foto_id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $foto_id);
        $stmt->execute();
        
        return true;
    }
    
    /**
     * Obtener fotos recientes
     */
    public function obtener_fotos_recientes($limite = 12) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT g.*, 
                p.fecha_partido,
                el.equipo_nombre AS equipo_local,
                ev.equipo_nombre AS equipo_visitante
                FROM lp_galeria g
                LEFT JOIN lp_partidos p ON g.partido_id = p.partido_id
                LEFT JOIN lp_equipos el ON p.equipo_local_id = el.equipo_id
                LEFT JOIN lp_equipos ev ON p.equipo_visitante_id = ev.equipo_id
                WHERE g.foto_estatus = 1
                ORDER BY g.fecha_subida DESC
                LIMIT ?";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $limite, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener fotos por fecha
     */
    public function obtener_fotos_por_fecha($fecha_inicio, $fecha_fin) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT g.*, 
                p.fecha_partido,
                el.equipo_nombre AS equipo_local,
                ev.equipo_nombre AS equipo_visitante
                FROM lp_galeria g
                LEFT JOIN lp_partidos p ON g.partido_id = p.partido_id
                LEFT JOIN lp_equipos el ON p.equipo_local_id = el.equipo_id
                LEFT JOIN lp_equipos ev ON p.equipo_visitante_id = ev.equipo_id
                WHERE g.foto_estatus = 1 
                AND g.fecha_foto BETWEEN ? AND ?
                ORDER BY g.fecha_foto DESC";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $fecha_inicio);
        $stmt->bindValue(2, $fecha_fin);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener fotos por temporada
     */
    public function obtener_fotos_por_temporada($temporada_id) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT g.*, 
                p.fecha_partido,
                el.equipo_nombre AS equipo_local,
                ev.equipo_nombre AS equipo_visitante,
                j.jornada_numero
                FROM lp_galeria g
                INNER JOIN lp_partidos p ON g.partido_id = p.partido_id
                LEFT JOIN lp_jornadas j ON p.jornada_id = j.jornada_id
                LEFT JOIN lp_equipos el ON p.equipo_local_id = el.equipo_id
                LEFT JOIN lp_equipos ev ON p.equipo_visitante_id = ev.equipo_id
                WHERE g.foto_estatus = 1 
                AND p.temporada_id = ?
                ORDER BY g.fecha_foto DESC";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $temporada_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Contar fotos en la galería
     */
    public function contar_fotos($activas_solo = true) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT COUNT(*) as total FROM lp_galeria";
        
        if ($activas_solo) {
            $sql .= " WHERE foto_estatus = 1";
        }
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
    
    /**
     * Buscar fotos por título o descripción
     */
    public function buscar_fotos($termino) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT g.*, 
                p.fecha_partido,
                el.equipo_nombre AS equipo_local,
                ev.equipo_nombre AS equipo_visitante
                FROM lp_galeria g
                LEFT JOIN lp_partidos p ON g.partido_id = p.partido_id
                LEFT JOIN lp_equipos el ON p.equipo_local_id = el.equipo_id
                LEFT JOIN lp_equipos ev ON p.equipo_visitante_id = ev.equipo_id
                WHERE g.foto_estatus = 1 
                AND (g.foto_titulo LIKE ? OR g.foto_descripcion LIKE ?)
                ORDER BY g.fecha_foto DESC";
        
        $stmt = $conexion->prepare($sql);
        $termino_busqueda = "%{$termino}%";
        $stmt->bindValue(1, $termino_busqueda);
        $stmt->bindValue(2, $termino_busqueda);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>