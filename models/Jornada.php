<?php
/**
 * Modelo para la tabla 'jornadas'
 */
class Jornada
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
     * Crea una nueva jornada en la base de datos.
     * @param int $numero_jornada El número consecutivo de la jornada.
     * @param int $liga_id El ID de la liga a la que pertenece.
     * @param int $temporada_id El ID de la temporada a la que pertenece.
     * @param string|null $nombre Un nombre descriptivo (ej. "Cuartos de Final").
     * @param string|null $fecha_inicio La fecha de inicio de la jornada.
     * @param string|null $fecha_fin La fecha de fin de la jornada.
     * @return int|false Retorna el ID de la jornada creada o false en caso de error.
     */
    public function crear($numero_jornada, $liga_id, $temporada_id, $nombre = null, $fecha_inicio = null, $fecha_fin = null)
    {
        $query = "INSERT INTO jornadas (numero_jornada, liga_id, temporada_id, nombre, fecha_inicio, fecha_fin) VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param("iiisss", $numero_jornada, $liga_id, $temporada_id, $nombre, $fecha_inicio, $fecha_fin);
        
        if ($stmt->execute()) {
            return $this->conexion->insert_id;
        } else {
            return false;
        }
    }

    /**
     * Obtiene todas las jornadas de una liga y temporada específicas.
     * @param int $liga_id
     * @param int $temporada_id
     * @return array Un arreglo con todas las jornadas del torneo.
     */
    public function obtenerPorLigaTemporada($liga_id, $temporada_id)
    {
        $query = "SELECT * FROM jornadas WHERE liga_id = ? AND temporada_id = ? ORDER BY numero_jornada ASC";
        
        $stmt = $this.conexion->prepare($query);
        $stmt->bind_param("ii", $liga_id, $temporada_id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Obtiene los datos de una jornada específica por su ID.
     * @param int $id El ID de la jornada.
     * @return array|null
     */
    public function obtenerPorId($id)
    {
        $query = "SELECT * FROM jornadas WHERE id = ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_assoc();
    }

    /**
     * Actualiza los datos de una jornada.
     * @param int $id El ID de la jornada a actualizar.
     * @param int $numero_jornada
     * @param string|null $nombre
     * @param string|null $fecha_inicio
     * @param string|null $fecha_fin
     * @return bool
     */
    public function actualizar($id, $numero_jornada, $nombre, $fecha_inicio, $fecha_fin)
    {
        $query = "UPDATE jornadas SET numero_jornada = ?, nombre = ?, fecha_inicio = ?, fecha_fin = ? WHERE id = ?";
        
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param("isssi", $numero_jornada, $nombre, $fecha_inicio, $fecha_fin, $id);
        
        return $stmt->execute();
    }

    /**
     * Elimina una jornada. (¡Precaución!)
     * Solo debería ser posible si no tiene partidos asociados.
     * @param int $id El ID de la jornada a eliminar.
     * @return bool
     */
    public function eliminar($id)
    {
        $query = "DELETE FROM jornadas WHERE id = ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>