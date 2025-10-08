<?php
/**
 * Modelo para la tabla 'canchas'
 */
class Cancha
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
     * Crea una nueva cancha en la base de datos.
     * @param string $nombre El nombre de la cancha.
     * @param string|null $direccion La dirección de la cancha.
     * @return int|false Retorna el ID de la cancha creada o false en caso de error.
     */
    public function crear($nombre, $direccion = null)
    {
        $query = "INSERT INTO canchas (nombre, direccion) VALUES (?, ?)";
        
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param("ss", $nombre, $direccion);
        
        if ($stmt->execute()) {
            return $this->conexion->insert_id;
        } else {
            return false;
        }
    }

    /**
     * Obtiene todas las canchas registradas.
     * @return array Un arreglo con todas las canchas.
     */
    public function obtenerTodas()
    {
        $query = "SELECT * FROM canchas ORDER BY nombre ASC";
        $resultado = $this->conexion->query($query);
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtiene los datos de una cancha específica por su ID.
     * @param int $id El ID de la cancha.
     * @return array|null Retorna un arreglo con los datos de la cancha o null si no se encuentra.
     */
    public function obtenerPorId($id)
    {
        $query = "SELECT * FROM canchas WHERE id = ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_assoc();
    }

    /**
     * Actualiza los datos de una cancha.
     * @param int $id El ID de la cancha a actualizar.
     * @param string $nombre El nuevo nombre.
     * @param string|null $direccion La nueva dirección.
     * @return bool Retorna true si la actualización fue exitosa, false en caso contrario.
     */
    public function actualizar($id, $nombre, $direccion = null)
    {
        $query = "UPDATE canchas SET nombre = ?, direccion = ? WHERE id = ?";
        
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param("ssi", $nombre, $direccion, $id);
        
        return $stmt->execute();
    }

    /**
     * Elimina una cancha de la base de datos.
     * @param int $id El ID de la cancha a eliminar.
     * @return bool Retorna true si se eliminó, false en caso contrario.
     */
    public function eliminar($id)
    {
        $query = "DELETE FROM canchas WHERE id = ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>