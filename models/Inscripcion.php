<?php
/**
 * Modelo para la tabla 'inscripciones'
 * Gestiona la relación entre equipos, ligas y temporadas.
 */
class Inscripcion
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
     * Inscribe un equipo en una liga para una temporada específica.
     * @param int $equipo_id
     * @param int $liga_id
     * @param int $temporada_id
     * @param string|null $grupo
     * @return int|false Retorna el ID de la inscripción o false si ya existe o hay un error.
     */
    public function inscribirEquipo($equipo_id, $liga_id, $temporada_id, $grupo = null)
    {
        // Primero, verificar que no exista ya esta inscripción
        if ($this->verificarInscripcion($equipo_id, $liga_id, $temporada_id)) {
            return false; // El equipo ya está inscrito
        }

        $query = "INSERT INTO inscripciones (equipo_id, liga_id, temporada_id, grupo) VALUES (?, ?, ?, ?)";
        
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param("iiis", $equipo_id, $liga_id, $temporada_id, $grupo);
        
        if ($stmt->execute()) {
            return $this->conexion->insert_id;
        } else {
            return false;
        }
    }

    /**
     * Verifica si un equipo ya está inscrito en una liga/temporada.
     * @param int $equipo_id
     * @param int $liga_id
     * @param int $temporada_id
     * @return bool Retorna true si ya existe, false si no.
     */
    public function verificarInscripcion($equipo_id, $liga_id, $temporada_id)
    {
        $query = "SELECT id FROM inscripciones WHERE equipo_id = ? AND liga_id = ? AND temporada_id = ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param("iii", $equipo_id, $liga_id, $temporada_id);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }

    /**
     * Obtiene todas las inscripciones de una liga en una temporada específica,
     * incluyendo los nombres de los equipos.
     * @param int $liga_id
     * @param int $temporada_id
     * @return array Un arreglo de equipos inscritos.
     */
    public function obtenerEquiposInscritos($liga_id, $temporada_id)
    {
        $query = "SELECT 
                    i.id AS inscripcion_id,
                    i.grupo,
                    e.id AS equipo_id,
                    e.nombre AS equipo_nombre,
                    e.logo AS equipo_logo
                  FROM inscripciones AS i
                  JOIN equipos AS e ON i.equipo_id = e.id
                  WHERE i.liga_id = ? AND i.temporada_id = ?
                  ORDER BY e.nombre ASC";
        
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param("ii", $liga_id, $temporada_id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Obtiene una inscripción específica por su ID.
     * @param int $id
     * @return array|null
     */
    public function obtenerPorId($id)
    {
        $query = "SELECT * FROM inscripciones WHERE id = ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_assoc();
    }


    /**
     * Elimina la inscripción de un equipo de un torneo.
     * @param int $inscripcion_id
     * @return bool
     */
    public function eliminar($inscripcion_id)
    {
        // Nota: Antes de eliminar, se deberían borrar los datos relacionados
        // (plantillas, goles, tarjetas, partidos) para evitar errores de integridad.
        $query = "DELETE FROM inscripciones WHERE id = ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param("i", $inscripcion_id);
        return $stmt->execute();
    }
}
?>