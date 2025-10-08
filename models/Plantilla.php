<?php
/**
 * Modelo para la tabla 'plantillas'
 * Gestiona la asignación de jugadores a equipos inscritos en un torneo.
 */
class Plantilla
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
     * Agrega un jugador a la plantilla de un equipo inscrito.
     * @param int $jugador_id El ID del jugador a agregar.
     * @param int $inscripcion_id El ID de la inscripción del equipo.
     * @param int|null $numero_camiseta El número de camiseta del jugador.
     * @return bool Retorna true si se agregó correctamente, false si ya existía o hubo un error.
     */
    public function agregarJugador($jugador_id, $inscripcion_id, $numero_camiseta = null)
    {
        // Verificar que el jugador no esté ya en la plantilla
        if ($this->verificarJugador($jugador_id, $inscripcion_id)) {
            return false;
        }

        $query = "INSERT INTO plantillas (jugador_id, inscripcion_id, numero_camiseta) VALUES (?, ?, ?)";
        
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param("iii", $jugador_id, $inscripcion_id, $numero_camiseta);
        
        return $stmt->execute();
    }
    
    /**
     * Verifica si un jugador ya pertenece a una plantilla.
     * @param int $jugador_id
     * @param int $inscripcion_id
     * @return bool
     */
    public function verificarJugador($jugador_id, $inscripcion_id)
    {
        $query = "SELECT id FROM plantillas WHERE jugador_id = ? AND inscripcion_id = ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param("ii", $jugador_id, $inscripcion_id);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }

    /**
     * Obtiene todos los jugadores de una plantilla específica (equipo en un torneo).
     * @param int $inscripcion_id El ID de la inscripción del equipo.
     * @return array Un arreglo con los datos de los jugadores de la plantilla.
     */
    public function obtenerJugadoresDePlantilla($inscripcion_id)
    {
        $query = "SELECT 
                    p.id AS plantilla_id,
                    p.numero_camiseta,
                    j.id AS jugador_id,
                    j.nombre,
                    j.apellidos,
                    j.posicion,
                    j.foto
                  FROM plantillas AS p
                  JOIN jugadores AS j ON p.jugador_id = j.id
                  WHERE p.inscripcion_id = ?
                  ORDER BY j.apellidos ASC, j.nombre ASC";
        
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param("i", $inscripcion_id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Quita un jugador de una plantilla.
     * @param int $plantilla_id El ID del registro en la tabla 'plantillas'.
     * @return bool
     */
    public function quitarJugador($plantilla_id)
    {
        $query = "DELETE FROM plantillas WHERE id = ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param("i", $plantilla_id);
        return $stmt->execute();
    }
    
    /**
     * Actualiza el número de camiseta de un jugador en una plantilla.
     * @param int $plantilla_id El ID del registro en la tabla 'plantillas'.
     * @param int|null $numero_camiseta El nuevo número.
     * @return bool
     */
    public function actualizarNumero($plantilla_id, $numero_camiseta)
    {
        $query = "UPDATE plantillas SET numero_camiseta = ? WHERE id = ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param("ii", $numero_camiseta, $plantilla_id);
        return $stmt->execute();
    }
}
?>