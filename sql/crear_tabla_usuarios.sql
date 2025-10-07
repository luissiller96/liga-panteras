-- =============================================
-- TABLA DE USUARIOS - LIGA PANTERAS
-- =============================================

-- Crear tabla usuarios si no existe
CREATE TABLE IF NOT EXISTS usuarios (
    usu_id INT AUTO_INCREMENT PRIMARY KEY,
    usu_nom VARCHAR(100) NOT NULL COMMENT 'Nombre del usuario',
    usu_ape VARCHAR(100) DEFAULT NULL COMMENT 'Apellido del usuario',
    usu_correo VARCHAR(150) UNIQUE NOT NULL COMMENT 'Correo electrónico (único)',
    usu_pass VARCHAR(255) NOT NULL COMMENT 'Contraseña hasheada (bcrypt)',
    usu_tipo ENUM('admin', 'equipo') DEFAULT 'equipo' COMMENT 'Tipo de usuario',
    equipo_id INT NULL COMMENT 'ID del equipo (si aplica)',
    usu_photoprofile VARCHAR(255) DEFAULT NULL COMMENT 'Nombre de archivo de foto de perfil',
    usu_estatus TINYINT(1) DEFAULT 1 COMMENT '1 = Activo, 0 = Inactivo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación del usuario',
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Fecha de última actualización',
    FOREIGN KEY (equipo_id) REFERENCES equipos(equipo_id) ON DELETE SET NULL,
    INDEX idx_usu_correo (usu_correo),
    INDEX idx_usu_tipo (usu_tipo),
    INDEX idx_usu_estatus (usu_estatus),
    INDEX idx_equipo_id (equipo_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla de usuarios del sistema';

-- =============================================
-- INSERTAR USUARIO ADMINISTRADOR POR DEFECTO
-- =============================================

-- Verificar si ya existe un usuario admin
-- Contraseña por defecto: admin123
-- IMPORTANTE: Cambiar esta contraseña después del primer login

INSERT IGNORE INTO usuarios (usu_nom, usu_ape, usu_correo, usu_pass, usu_tipo, equipo_id, usu_estatus)
VALUES (
    'Administrador',
    'Sistema',
    'admin@ligapanteras.com',
    '$2y$12$J.uogH3.wQUpZnCOVMap6./wrZN363BSI6jqtls7yt6zWVNmOaZYu', -- Contraseña: admin123
    'admin',
    NULL,
    1
);

-- =============================================
-- USUARIO DE EJEMPLO PARA EQUIPO
-- =============================================

-- Descomentar y ajustar equipo_id según corresponda
/*
INSERT INTO usuarios (usu_nom, usu_ape, usu_correo, usu_pass, usu_tipo, equipo_id, usu_estatus)
VALUES (
    'Capitán',
    'Ejemplo',
    'capitan@ejemplo.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- Contraseña: admin123
    'equipo',
    1, -- Cambiar por el ID del equipo correspondiente
    1
);
*/

-- =============================================
-- NOTAS IMPORTANTES
-- =============================================

/*
1. La contraseña por defecto para el usuario admin es: admin123
   CAMBIARLA INMEDIATAMENTE después del primer login.

2. Las contraseñas están hasheadas con bcrypt (PASSWORD_BCRYPT en PHP).
   Para generar una nueva contraseña hasheada en PHP:

   echo password_hash('tu_contraseña_aquí', PASSWORD_BCRYPT);

3. El campo equipo_id debe tener una clave foránea válida hacia la tabla equipos.
   Si un usuario es 'admin', equipo_id debe ser NULL.

4. Los tipos de usuario son:
   - 'admin': Acceso completo al sistema
   - 'equipo': Acceso limitado a gestión de su propio equipo

5. El campo usu_estatus:
   - 1: Usuario activo
   - 0: Usuario desactivado (borrado lógico)
*/
