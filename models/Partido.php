<?php
/**
 * Modelo para la tabla 'partidos'
 */
class Partido
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
     * Crea un nuevo partido en la base de datos.
     * Los IDs de equipo local y visitante se refieren al ID de la tabla 'inscripciones'.
     * @param int $jornada_id
     * @param string $fecha_hora (Formato YYYY-MM-DD HH:MM:SS)
     * @param int $equipo_local_id (ID de inscripciones)
     * @param int $equipo_visitante_id (ID de inscripciones)
     * @param int $cancha_id
     * @param int|null $arbitro_id
     * @return int|false Retorna el ID del partido creado o false en caso de error.
     */
    public function crear($jornada_id, $fecha_hora, $equipo_local_id, $equipo_visitante_id, $cancha_id, $arbitro_id = null)
    {
        $query = "INSERT INTO partidos (jornada_id, fecha_hora, equipo_local_id, equipo_visitante_id, cancha_id, arbitro_id, estado) 
                  VALUES (?, ?, ?, ?, ?, ?, 'Programado')";
        
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param("isiiii", $jornada_id, $fecha_hora, $equipo_local_id, $equipo_visitante_id, $cancha_id, $arbitro_id);
        
        if ($stmt->execute()) {
            return $this->conexion->insert_id;
        } else {
            return false;
        }
    }

    /**
     * Obtiene todos los partidos de una jornada con información detallada de los equipos.
     * @param int $jornada_id
     * @return array
     */
    public function obtenerPorJornada($jornada_id)
    {
        $query = "SELECT 
                    p.id, p.fecha_hora, p.goles_local, p.goles_visitante, p.estado,
                    -- Información del equipo local
                    el.nombre AS local_nombre,
                    el.logo AS local_logo,
                    il.id AS local_inscripcion_id,
                    -- Información del equipo visitante
                    ev.nombre AS visitante_nombre,
                    ev.logo AS visitante_logo,
                    iv.id AS visitante_inscripcion_id,
                    -- Información adicional
                    c.nombre AS cancha_nombre,
                    CONCAT(a.nombre, ' ', a.apellidos) AS arbitro_nombre
                  FROM partidos AS p
                  -- Joins para el equipo local
                  JOIN inscripciones AS il ON p.equipo_local_id = il.id
                  JOIN equipos AS el ON il.equipo_id = el.id
                  -- Joins para el equipo visitante
                  JOIN inscripciones AS iv ON p.equipo_visitante_id = iv.id
                  JOIN equipos AS ev ON iv.equipo_id = ev.id
                  -- Joins para cancha y árbitro
                  JOIN canchas AS c ON p.cancha_id = c.id
                  LEFT JOIN arbitros AS a ON p.arbitro_id = a.id
                  WHERE p.jornada_id = ?
                  ORDER BY p.fecha_hora ASC";
        
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param("i", $jornada_id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Obtiene un partido específico por su ID con toda la información.
     * @param int $partido_id
     * @return array|null
     */
    public function obtenerPorId($partido_id) {
        // La consulta es idéntica a la anterior, solo cambia el WHERE
        $query = "SELECT p.id, p.fecha_hora, p.goles_local, p.goles_visitante, p.estado, el.nombre AS local_nombre, el.logo AS local_logo, il.id AS local_inscripcion_id, ev.nombre AS visitante_nombre, ev.logo AS visitante_logo, iv.id AS visitante_inscripcion_id, c.nombre AS cancha_nombre, CONCAT(a.nombre, ' ', a.apellidos) AS arbitro_nombre FROM partidos AS p JOIN inscripciones AS il ON p.equipo_local_id = il.id JOIN equipos AS el ON il.equipo_id = el.id JOIN inscripciones AS iv ON p.equipo_visitante_id = iv.id JOIN equipos AS ev ON iv.equipo_id = ev.id JOIN canchas AS c ON p.cancha_id = c.id LEFT JOIN arbitros AS a ON p.arbitro_id = a.id WHERE p.id = ? LIMIT 1";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param("i", $partido_id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_assoc();
    }

    /**
     * Actualiza el resultado de un partido y cambia su estado a 'Finalizado'.
     * @param int $partido_id
     * @param int $goles_local
     * @param int $goles_visitante
     * @return bool
     */
    public function actualizarResultado($partido_id, $goles_local, $goles_visitante)
    {
        $query = "UPDATE partidos SET goles_local = ?, goles_visitante = ?, estado = 'Finalizado' WHERE id = ?";
        
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param("iii", $goles_local, $goles_visitante, $partido_id);
        
        return $stmt->execute();
    }

    /**
     * Elimina un partido de la base de datos.
     * @param int $partido_id
     * @return bool
     */
    public function eliminar($partido_id)
    {
        // Antes de eliminar un partido, se deberían eliminar los goles y tarjetas asociados.
        $query = "DELETE FROM partidos WHERE id = ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param("i", $partido_id);
        return $stmt->execute();
    }
}
?>