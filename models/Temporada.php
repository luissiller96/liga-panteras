<?php
/**
 * Modelo para la tabla 'temporadas'
 */
class Temporada
{
    private $conexion; // Almacenará el objeto de conexión a la BD

    /**
     * Constructor de la clase
     * @param object $conexion Objeto de conexión a la base de datos
     */
    public function __construct($conexion)
    {
        $this->conexion = $conexion;
    }

    /**
     * Crea una nueva temporada en la base de datos.
     * @param string $nombre El nombre de la temporada (ej. "Temporada 2025-2026").
     * @param string $fecha_inicio La fecha de inicio en formato YYYY-MM-DD.
     * @param string $fecha_fin La fecha de finalización en formato YYYY-MM-DD.
     * @return bool Retorna true si se creó correctamente, false en caso contrario.
     */
    public function crear($nombre, $fecha_inicio, $fecha_fin)
    {
        $query = "INSERT INTO temporadas (nombre, fecha_inicio, fecha_fin, activa) VALUES (?, ?, ?, 1)";
        
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param("sss", $nombre, $fecha_inicio, $fecha_fin);
        
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Obtiene todas las temporadas registradas.
     * @return array Un arreglo con todas las temporadas.
     */
    public function obtenerTodas()
    {
        $query = "SELECT * FROM temporadas ORDER BY fecha_inicio DESC";
        $resultado = $this->conexion->query($query);
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtiene los datos de una temporada específica por su ID.
     * @param int $id El ID de la temporada.
     * @return array|null Retorna un arreglo con los datos de la temporada o null si no se encuentra.
     */
    public function obtenerPorId($id)
    {
        $query = "SELECT * FROM temporadas WHERE id = ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_assoc();
    }
    
    /**
     * Obtiene la temporada que está marcada como activa.
     * @return array|null Retorna un arreglo con los datos de la temporada activa o null si no hay ninguna.
     */
    public function obtenerActiva()
    {
        $query = "SELECT * FROM temporadas WHERE activa = 1 LIMIT 1";
        $resultado = $this->conexion->query($query);
        return $resultado->fetch_assoc();
    }

    /**
     * Actualiza los datos de una temporada.
     * @param int $id El ID de la temporada a actualizar.
     * @param string $nombre El nuevo nombre.
     * @param string $fecha_inicio La nueva fecha de inicio.
     * @param string $fecha_fin La nueva fecha de fin.
     * @param int $activa El nuevo estado (1 para activa, 0 para inactiva).
     * @return bool Retorna true si la actualización fue exitosa, false en caso contrario.
     */
    public function actualizar($id, $nombre, $fecha_inicio, $fecha_fin, $activa)
    {
        $query = "UPDATE temporadas SET nombre = ?, fecha_inicio = ?, fecha_fin = ?, activa = ? WHERE id = ?";
        
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param("sssii", $nombre, $fecha_inicio, $fecha_fin, $activa, $id);
        
        return $stmt->execute();
    }

    /**
     * Elimina una temporada de la base de datos.
     * ¡Cuidado! Esto podría fallar si hay ligas o equipos asociados a ella.
     * @param int $id El ID de la temporada a eliminar.
     * @return bool Retorna true si se eliminó, false en caso contrario.
     */
    public function eliminar($id)
    {
        $query = "DELETE FROM temporadas WHERE id = ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>