<?php
/**
 * Modelo para la tabla 'jugadores'
 */
class Jugador
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
     * Crea un nuevo jugador en la base de datos.
     * @param string $nombre El nombre del jugador.
     * @param string $apellidos Los apellidos del jugador.
     * @param string|null $fecha_nacimiento La fecha de nacimiento (YYYY-MM-DD).
     * @param string|null $posicion La posición en la que juega.
     * @param string|null $foto La ruta a la foto del jugador.
     * @return int|false Retorna el ID del jugador creado o false en caso de error.
     */
    public function crear($nombre, $apellidos, $fecha_nacimiento = null, $posicion = null, $foto = null)
    {
        $query = "INSERT INTO jugadores (nombre, apellidos, fecha_nacimiento, posicion, foto) VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param("sssss", $nombre, $apellidos, $fecha_nacimiento, $posicion, $foto);
        
        if ($stmt->execute()) {
            return $this->conexion->insert_id;
        } else {
            return false;
        }
    }

    /**
     * Obtiene todos los jugadores registrados, ordenados por apellidos y nombre.
     * @return array Un arreglo con todos los jugadores.
     */
    public function obtenerTodos()
    {
        $query = "SELECT * FROM jugadores ORDER BY apellidos ASC, nombre ASC";
        $resultado = $this->conexion->query($query);
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtiene los datos de un jugador específico por su ID.
     * @param int $id El ID del jugador.
     * @return array|null Retorna un arreglo con los datos del jugador o null si no se encuentra.
     */
    public function obtenerPorId($id)
    {
        $query = "SELECT * FROM jugadores WHERE id = ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_assoc();
    }

    /**
     * Actualiza los datos de un jugador.
     * @param int $id El ID del jugador a actualizar.
     * @param string $nombre El nuevo nombre.
     * @param string $apellidos Los nuevos apellidos.
     * @param string|null $fecha_nacimiento La nueva fecha de nacimiento.
     * @param string|null $posicion La nueva posición.
     * @param string|null $foto La nueva ruta de la foto.
     * @return bool Retorna true si la actualización fue exitosa, false en caso contrario.
     */
    public function actualizar($id, $nombre, $apellidos, $fecha_nacimiento = null, $posicion = null, $foto = null)
    {
        $query = "UPDATE jugadores SET nombre = ?, apellidos = ?, fecha_nacimiento = ?, posicion = ?, foto = ? WHERE id = ?";
        
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param("sssssi", $nombre, $apellidos, $fecha_nacimiento, $posicion, $foto, $id);
        
        return $stmt->execute();
    }

    /**
     * Elimina un jugador de la base de datos.
     * @param int $id El ID del jugador a eliminar.
     * @return bool Retorna true si se eliminó, false en caso contrario.
     */
    public function eliminar($id)
    {
        $query = "DELETE FROM jugadores WHERE id = ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
    
    /**
     * Busca jugadores por nombre o apellidos.
     * @param string $termino El término de búsqueda.
     * @return array Un arreglo con los jugadores que coinciden.
     */
    public function buscarPorNombre($termino)
    {
        $query = "SELECT * FROM jugadores WHERE CONCAT(nombre, ' ', apellidos) LIKE ?";
        $param = "%{$termino}%";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param("s", $param);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }
}
?>