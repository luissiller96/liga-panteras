<?php
/**
 * Modelo para la tabla 'equipos'
 */
class Equipo
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
     * Crea un nuevo equipo en la base de datos.
     * @param string $nombre El nombre del equipo.
     * @param string|null $logo La ruta a la imagen del escudo del equipo.
     * @param string|null $dt El nombre del Director Técnico.
     * @return int|false Retorna el ID del equipo creado o false en caso de error.
     */
    public function crear($nombre, $logo = null, $dt = null)
    {
        $query = "INSERT INTO equipos (nombre, logo, dt) VALUES (?, ?, ?)";
        
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param("sss", $nombre, $logo, $dt);
        
        if ($stmt->execute()) {
            return $this->conexion->insert_id;
        } else {
            return false;
        }
    }

    /**
     * Obtiene todos los equipos registrados, ordenados alfabéticamente.
     * @return array Un arreglo con todos los equipos.
     */
    public function obtenerTodos()
    {
        $query = "SELECT * FROM equipos ORDER BY nombre ASC";
        $resultado = $this->conexion->query($query);
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtiene los datos de un equipo específico por su ID.
     * @param int $id El ID del equipo.
     * @return array|null Retorna un arreglo con los datos del equipo o null si no se encuentra.
     */
    public function obtenerPorId($id)
    {
        $query = "SELECT * FROM equipos WHERE id = ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_assoc();
    }

    /**
     * Actualiza los datos de un equipo.
     * @param int $id El ID del equipo a actualizar.
     * @param string $nombre El nuevo nombre del equipo.
     * @param string|null $logo La nueva ruta del logo.
     * @param string|null $dt El nuevo nombre del DT.
     * @return bool Retorna true si la actualización fue exitosa, false en caso contrario.
     */
    public function actualizar($id, $nombre, $logo = null, $dt = null)
    {
        $query = "UPDATE equipos SET nombre = ?, logo = ?, dt = ? WHERE id = ?";
        
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param("sssi", $nombre, $logo, $dt, $id);
        
        return $stmt->execute();
    }

    /**
     * Elimina un equipo de la base de datos.
     * @param int $id El ID del equipo a eliminar.
     * @return bool Retorna true si se eliminó, false en caso contrario.
     */
    public function eliminar($id)
    {
        $query = "DELETE FROM equipos WHERE id = ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
    
    /**
     * Busca equipos por nombre. Útil para un buscador en el panel de admin.
     * @param string $termino El término de búsqueda.
     * @return array Un arreglo con los equipos que coinciden con la búsqueda.
     */
    public function buscarPorNombre($termino)
    {
        $query = "SELECT * FROM equipos WHERE nombre LIKE ?";
        $param = "%{$termino}%";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param("s", $param);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }
}
?>