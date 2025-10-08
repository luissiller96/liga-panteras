<?php
/**
 * Modelo para la tabla 'arbitros'
 */
class Arbitro
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
     * Crea un nuevo árbitro en la base de datos.
     * @param string $nombre El nombre del árbitro.
     * @param string $apellidos Los apellidos del árbitro.
     * @return int|false Retorna el ID del árbitro creado o false en caso de error.
     */
    public function crear($nombre, $apellidos)
    {
        $query = "INSERT INTO arbitros (nombre, apellidos) VALUES (?, ?)";
        
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param("ss", $nombre, $apellidos);
        
        if ($stmt->execute()) {
            return $this->conexion->insert_id;
        } else {
            return false;
        }
    }

    /**
     * Obtiene todos los árbitros registrados, ordenados por apellidos.
     * @return array Un arreglo con todos los árbitros.
     */
    public function obtenerTodos()
    {
        $query = "SELECT * FROM arbitros ORDER BY apellidos ASC, nombre ASC";
        $resultado = $this->conexion->query($query);
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtiene los datos de un árbitro específico por su ID.
     * @param int $id El ID del árbitro.
     * @return array|null Retorna un arreglo con los datos del árbitro o null si no se encuentra.
     */
    public function obtenerPorId($id)
    {
        $query = "SELECT * FROM arbitros WHERE id = ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_assoc();
    }

    /**
     * Actualiza los datos de un árbitro.
     * @param int $id El ID del árbitro a actualizar.
     * @param string $nombre El nuevo nombre.
     * @param string $apellidos Los nuevos apellidos.
     * @return bool Retorna true si la actualización fue exitosa, false en caso contrario.
     */
    public function actualizar($id, $nombre, $apellidos)
    {
        $query = "UPDATE arbitros SET nombre = ?, apellidos = ? WHERE id = ?";
        
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param("ssi", $nombre, $apellidos, $id);
        
        return $stmt->execute();
    }

    /**
     * Elimina un árbitro de la base de datos.
     * @param int $id El ID del árbitro a eliminar.
     * @return bool Retorna true si se eliminó, false en caso contrario.
     */
    public function eliminar($id)
    {
        $query = "DELETE FROM arbitros WHERE id = ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>