<?php
/**
 * Modelo para la tabla 'ligas'
 */
class Liga
{
    private $conexion;

    /**
     * Constructor de la clase
     * @param object $conexion Objeto de conexión a la base de datos
     */
    public function __construct($conexion)
    {
        $this->conexion = $conexion;
    }

    /**
     * Crea una nueva liga en la base de datos.
     * @param string $nombre El nombre de la liga (ej. "Panteras Champions League").
     * @param string|null $descripcion Una descripción opcional para la liga.
     * @param string|null $logo La ruta opcional a la imagen del logo.
     * @return int|false Retorna el ID de la liga creada o false en caso de error.
     */
    public function crear($nombre, $descripcion = null, $logo = null)
    {
        $query = "INSERT INTO ligas (nombre, descripcion, logo) VALUES (?, ?, ?)";
        
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param("sss", $nombre, $descripcion, $logo);
        
        if ($stmt->execute()) {
            return $this->conexion->insert_id; // Retorna el ID del nuevo registro
        } else {
            return false;
        }
    }

    /**
     * Obtiene todas las ligas registradas.
     * @return array Un arreglo con todas las ligas.
     */
    public function obtenerTodas()
    {
        $query = "SELECT * FROM ligas ORDER BY nombre ASC";
        $resultado = $this->conexion->query($query);
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtiene los datos de una liga específica por su ID.
     * @param int $id El ID de la liga.
     * @return array|null Retorna un arreglo con los datos de la liga o null si no se encuentra.
     */
    public function obtenerPorId($id)
    {
        $query = "SELECT * FROM ligas WHERE id = ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_assoc();
    }

    /**
     * Actualiza los datos de una liga.
     * @param int $id El ID de la liga a actualizar.
     * @param string $nombre El nuevo nombre de la liga.
     * @param string|null $descripcion La nueva descripción.
     * @param string|null $logo La nueva ruta del logo.
     * @return bool Retorna true si la actualización fue exitosa, false en caso contrario.
     */
    public function actualizar($id, $nombre, $descripcion = null, $logo = null)
    {
        $query = "UPDATE ligas SET nombre = ?, descripcion = ?, logo = ? WHERE id = ?";
        
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param("sssi", $nombre, $descripcion, $logo, $id);
        
        return $stmt->execute();
    }

    /**
     * Elimina una liga de la base de datos.
     * @param int $id El ID de la liga a eliminar.
     * @return bool Retorna true si se eliminó, false en caso contrario.
     */
    public function eliminar($id)
    {
        $query = "DELETE FROM ligas WHERE id = ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>  