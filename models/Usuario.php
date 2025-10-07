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
     * CORREGIDO: Acepta tanto hash como texto plano (temporal para migración)
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
                    FROM tm_usuario u
                    LEFT JOIN lp_equipos e ON u.equipo_id = e.equipo_id
                    WHERE u.usu_correo = :correo
                    AND u.est = 1";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':correo', $correo);
            $stmt->execute();

            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario) {
                // Verificar si la contraseña es hash o texto plano
                $password_valida = false;
                
                // Intentar verificar como hash
                if (password_verify($password, $usuario['usu_pass'])) {
                    $password_valida = true;
                } 
                // Si no es hash, comparar como texto plano (temporal)
                elseif ($password === $usuario['usu_pass']) {
                    $password_valida = true;
                    
                    // OPCIONAL: Actualizar a hash automáticamente
                    $this->actualizar_password_hash($usuario['usu_id'], $password);
                }
                
                if ($password_valida) {
                    // Eliminar la contraseña del resultado
                    unset($usuario['usu_pass']);
                    return [$usuario];
                }
            }

            return [];
        } catch (Exception $e) {
            error_log("Error en login: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Actualizar contraseña a hash (migración automática)
     */
    private function actualizar_password_hash($usu_id, $password) {
        try {
            $password_hash = password_hash($password, PASSWORD_BCRYPT);
            $sql = "UPDATE tm_usuario SET usu_pass = :usu_pass WHERE usu_id = :usu_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':usu_pass', $password_hash);
            $stmt->bindParam(':usu_id', $usu_id, PDO::PARAM_INT);
            $stmt->execute();
        } catch (Exception $e) {
            error_log("Error al actualizar hash: " . $e->getMessage());
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
                        u.est as usu_estatus,
                        u.fecha_registro as fecha_creacion,
                        e.equipo_nombre
                    FROM tm_usuario u
                    LEFT JOIN lp_equipos e ON u.equipo_id = e.equipo_id
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
                    FROM tm_usuario u
                    LEFT JOIN lp_equipos e ON u.equipo_id = e.equipo_id
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
            $sql = "INSERT INTO tm_usuario (
                        usu_nom,
                        usu_ape,
                        usu_correo,
                        usu_pass,
                        usu_tipo,
                        equipo_id,
                        usu_photoprofile,
                        est
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
            $sql = "UPDATE tm_usuario SET
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
     * RENOMBRADO: verificar_correo_existente -> verificar_correo_existe (convención)
     */
    public function verificar_correo_existe($correo, $usu_id = null) {
        try {
            $sql = "SELECT COUNT(*) as total
                    FROM tm_usuario
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
     * Alias para compatibilidad con código antiguo
     * @deprecated Usar verificar_correo_existe()
     */
    public function verificar_correo_existente($correo, $usu_id = null) {
        return $this->verificar_correo_existe($correo, $usu_id);
    }

    /**
     * Cambiar contraseña de usuario
     */
    public function cambiar_password($usu_id, $password_actual, $password_nuevo) {
        try {
            // Verificar contraseña actual
            $sql = "SELECT usu_pass FROM tm_usuario WHERE usu_id = :usu_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':usu_id', $usu_id, PDO::PARAM_INT);
            $stmt->execute();

            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$usuario) {
                return false;
            }

            // Verificar contraseña actual (hash o texto plano)
            $password_valida = false;
            if (password_verify($password_actual, $usuario['usu_pass'])) {
                $password_valida = true;
            } elseif ($password_actual === $usuario['usu_pass']) {
                $password_valida = true;
            }

            if (!$password_valida) {
                return false;
            }

            // Actualizar con nueva contraseña
            $password_hash = password_hash($password_nuevo, PASSWORD_BCRYPT);

            $sql = "UPDATE tm_usuario SET usu_pass = :usu_pass WHERE usu_id = :usu_id";
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
     * Cambiar estatus del usuario
     * AGREGADO: Método según convenciones
     */
    public function cambiar_estatus($usu_id, $estatus) {
        try {
            $sql = "UPDATE tm_usuario SET est = :estatus WHERE usu_id = :usu_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':estatus', $estatus, PDO::PARAM_INT);
            $stmt->bindParam(':usu_id', $usu_id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error al cambiar estatus: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar usuario (borrado lógico)
     * RENOMBRADO: desactivar_usuario -> eliminar_usuario (convención)
     */
    public function eliminar_usuario($usu_id) {
        try {
            $sql = "UPDATE tm_usuario SET est = 0 WHERE usu_id = :usu_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':usu_id', $usu_id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Alias para compatibilidad con código antiguo
     * @deprecated Usar eliminar_usuario()
     */
    public function desactivar_usuario($usu_id) {
        return $this->eliminar_usuario($usu_id);
    }

    /**
     * Activar usuario
     */
    public function activar_usuario($usu_id) {
        try {
            $sql = "UPDATE tm_usuario SET est = 1 WHERE usu_id = :usu_id";
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
            $sql = "SELECT COUNT(*) as total FROM tm_usuario WHERE est = 1";
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
            $sql = "SELECT COUNT(*) as total FROM tm_usuario WHERE usu_tipo = 'admin' AND est = 1";
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