<?php
/**
 * Modelo Usuario
 * Gestiona las operaciones de base de datos para usuarios del sistema Liga Panteras
 */

class Usuario {
    private $conn;

    public function __construct() {
        $this->conn = Conectar::obtenerConexionUnica();
    }

    /**
     * Login de usuario
     */
    public function login($correo, $password) {
        try {
            $sql = "SELECT
                        u.usu_id,
                        u.usu_nom,
                        u.usu_ape,
                        u.usu_correo,
                        u.usu_pass,
                        u.usu_tipo,
                        u.equipo_id,
                        u.usu_photoprofile,
                        e.equipo_nombre
                    FROM usuarios u
                    LEFT JOIN equipos e ON u.equipo_id = e.equipo_id
                    WHERE u.usu_correo = :correo
                    AND u.usu_estatus = 1";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':correo', $correo);
            $stmt->execute();

            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario && password_verify($password, $usuario['usu_pass'])) {
                // Eliminar la contraseña del resultado
                unset($usuario['usu_pass']);
                return [$usuario];
            }

            return [];
        } catch (Exception $e) {
            error_log("Error en login: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener todos los usuarios
     */
    public function obtener_usuarios() {
        try {
            $sql = "SELECT
                        u.usu_id,
                        u.usu_nom,
                        u.usu_ape,
                        u.usu_correo,
                        u.usu_tipo,
                        u.equipo_id,
                        u.usu_photoprofile,
                        u.usu_estatus,
                        u.fecha_creacion,
                        e.equipo_nombre
                    FROM usuarios u
                    LEFT JOIN equipos e ON u.equipo_id = e.equipo_id
                    ORDER BY u.usu_id DESC";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Obtener usuario por ID
     */
    public function obtener_usuario_por_id($usu_id) {
        try {
            $sql = "SELECT
                        u.*,
                        e.equipo_nombre
                    FROM usuarios u
                    LEFT JOIN equipos e ON u.equipo_id = e.equipo_id
                    WHERE u.usu_id = :usu_id";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':usu_id', $usu_id, PDO::PARAM_INT);
            $stmt->execute();

            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            // Eliminar la contraseña del resultado
            if ($usuario) {
                unset($usuario['usu_pass']);
            }

            return $usuario;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Crear nuevo usuario
     */
    public function crear_usuario($datos) {
        try {
            $sql = "INSERT INTO usuarios (
                        usu_nom,
                        usu_ape,
                        usu_correo,
                        usu_pass,
                        usu_tipo,
                        equipo_id,
                        usu_photoprofile,
                        usu_estatus
                    ) VALUES (
                        :usu_nom,
                        :usu_ape,
                        :usu_correo,
                        :usu_pass,
                        :usu_tipo,
                        :equipo_id,
                        :usu_photoprofile,
                        1
                    )";

            $stmt = $this->conn->prepare($sql);

            // Hash de la contraseña
            $password_hash = password_hash($datos['usu_pass'], PASSWORD_BCRYPT);

            $stmt->bindParam(':usu_nom', $datos['usu_nom']);
            $stmt->bindParam(':usu_ape', $datos['usu_ape']);
            $stmt->bindParam(':usu_correo', $datos['usu_correo']);
            $stmt->bindParam(':usu_pass', $password_hash);
            $stmt->bindParam(':usu_tipo', $datos['usu_tipo']);
            $stmt->bindParam(':equipo_id', $datos['equipo_id'], PDO::PARAM_INT);
            $stmt->bindParam(':usu_photoprofile', $datos['usu_photoprofile']);

            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            return false;
        } catch (Exception $e) {
            error_log("Error al crear usuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar usuario
     */
    public function actualizar_usuario($usu_id, $datos) {
        try {
            $sql = "UPDATE usuarios SET
                        usu_nom = :usu_nom,
                        usu_ape = :usu_ape,
                        usu_correo = :usu_correo,
                        usu_tipo = :usu_tipo,
                        equipo_id = :equipo_id";

            // Si se proporciona una nueva foto
            if (isset($datos['usu_photoprofile']) && !empty($datos['usu_photoprofile'])) {
                $sql .= ", usu_photoprofile = :usu_photoprofile";
            }

            $sql .= " WHERE usu_id = :usu_id";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':usu_nom', $datos['usu_nom']);
            $stmt->bindParam(':usu_ape', $datos['usu_ape']);
            $stmt->bindParam(':usu_correo', $datos['usu_correo']);
            $stmt->bindParam(':usu_tipo', $datos['usu_tipo']);
            $stmt->bindParam(':equipo_id', $datos['equipo_id'], PDO::PARAM_INT);
            $stmt->bindParam(':usu_id', $usu_id, PDO::PARAM_INT);

            if (isset($datos['usu_photoprofile']) && !empty($datos['usu_photoprofile'])) {
                $stmt->bindParam(':usu_photoprofile', $datos['usu_photoprofile']);
            }

            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error al actualizar usuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar si un correo ya existe
     */
    public function verificar_correo_existente($correo, $usu_id = null) {
        try {
            $sql = "SELECT COUNT(*) as total
                    FROM usuarios
                    WHERE usu_correo = :correo";

            if ($usu_id) {
                $sql .= " AND usu_id != :usu_id";
            }

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':correo', $correo);

            if ($usu_id) {
                $stmt->bindParam(':usu_id', $usu_id, PDO::PARAM_INT);
            }

            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result['total'] > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Cambiar contraseña de usuario
     */
    public function cambiar_password($usu_id, $password_actual, $password_nuevo) {
        try {
            // Verificar contraseña actual
            $sql = "SELECT usu_pass FROM usuarios WHERE usu_id = :usu_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':usu_id', $usu_id, PDO::PARAM_INT);
            $stmt->execute();

            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$usuario || !password_verify($password_actual, $usuario['usu_pass'])) {
                return false;
            }

            // Actualizar con nueva contraseña
            $password_hash = password_hash($password_nuevo, PASSWORD_BCRYPT);

            $sql = "UPDATE usuarios SET usu_pass = :usu_pass WHERE usu_id = :usu_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':usu_pass', $password_hash);
            $stmt->bindParam(':usu_id', $usu_id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error al cambiar contraseña: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Desactivar usuario (borrado lógico)
     */
    public function desactivar_usuario($usu_id) {
        try {
            $sql = "UPDATE usuarios SET usu_estatus = 0 WHERE usu_id = :usu_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':usu_id', $usu_id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Activar usuario
     */
    public function activar_usuario($usu_id) {
        try {
            $sql = "UPDATE usuarios SET usu_estatus = 1 WHERE usu_id = :usu_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':usu_id', $usu_id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Contar usuarios activos
     */
    public function contar_usuarios_activos() {
        try {
            $sql = "SELECT COUNT(*) as total FROM usuarios WHERE usu_estatus = 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'];
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Contar administradores
     */
    public function contar_administradores() {
        try {
            $sql = "SELECT COUNT(*) as total FROM usuarios WHERE usu_tipo = 'admin' AND usu_estatus = 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'];
        } catch (Exception $e) {
            return 0;
        }
    }
}
?>
