<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

class Conectar
{
  protected $dbh;
  private static $instance = null;

  protected function Conexion()
  {
    date_default_timezone_set('America/Mexico_City');

    if (self::$instance === null) {
      try {
        $host = '192.241.159.227';
        $dbname = 'db_ligapanteras';
        $username = 'remote_user';
        $password = 'k]K^l&Yw!J7';

        $pdo_options = [
          PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
          PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
          PDO::ATTR_EMULATE_PREPARES => false,
          PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8', time_zone = '-06:00'"  // ⬅️ CAMBIO AQUÍ
        ];

        self::$instance = new PDO(
          "mysql:host=$host;dbname=$dbname;charset=utf8",
          $username,
          $password,
          $pdo_options
        );

        // Forzar zona horaria inmediatamente después de conectar
        self::$instance->exec("SET time_zone = '-06:00'");
        
      } catch (PDOException $e) {
        error_log("Error de conexión a la BD: " . $e->getMessage());
        die("¡Error BD! Por favor, inténtelo de nuevo más tarde.");
      }
    }
    return self::$instance;
  }

  public function set_names()
  {
    return true;
  }

  public static function obtenerConexionUnica()
  {
    $connector = new Conectar();
    return $connector->Conexion();
  }

  static public function ruta()
  {
    return "/";
  }
}