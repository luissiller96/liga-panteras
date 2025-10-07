# 📋 CONVENCIONES - LIGA PANTERAS

## 🗂️ ESTRUCTURA DE CARPETAS
```
liga-panteras/
├── config/
│   └── conexion.php
├── models/
│   ├── Liga.php
│   ├── Equipo.php
│   ├── Jugador.php
│   └── ...
├── controller/
│   ├── liga_controller.php
│   ├── equipo_controller.php
│   └── ...
├── pages/
├── js/
└── css/
```

---

## 🔷 MODELOS (models/)

### Conexión a BD
```php
$conectar = new Conectar();
$conexion = $conectar->Conexion();
```

### Métodos Estándar (TODOS los modelos)
```php
// 1. Listar todos
public function obtener_[entidad]s($filtro = null) {
    // Retorna: array de registros
}

// 2. Obtener uno por ID
public function obtener_[entidad]_por_id($id) {
    // Retorna: array asociativo o false
}

// 3. Crear
public function crear_[entidad]($datos) {
    // Retorna: lastInsertId() o false
}

// 4. Actualizar
public function actualizar_[entidad]($id, $datos) {
    // Retorna: true o false
}

// 5. Eliminar (con validaciones)
public function eliminar_[entidad]($id) {
    // Valida relaciones antes de eliminar
    // Retorna: true o false
}

// 6. Cambiar estatus (activar/desactivar)
public function cambiar_estatus($id, $estatus) {
    // Retorna: true o false
}

// 7. Verificar duplicados
public function verificar_nombre_existe($nombre, $excluir_id = null) {
    // Retorna: true si existe, false si no
}

// 8. Estadísticas (opcional)
public function obtener_estadisticas_[entidad]($id) {
    // Retorna: array con contadores
}
```

### Ejemplo Completo (Liga.php)
```php
class Liga {
    
    public function obtener_ligas($activas_solo = false) {
        $conectar = new Conectar();
        $conexion = $conectar->Conexion();
        
        $sql = "SELECT * FROM lp_ligas";
        if ($activas_solo) {
            $sql .= " WHERE liga_estatus = 1";
        }
        $sql .= " ORDER BY liga_id DESC";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function obtener_liga_por_id($liga_id) {
        // Similar...
    }
    
    public function crear_liga($datos) {
        // INSERT y retorna lastInsertId()
    }
    
    public function actualizar_liga($liga_id, $datos) {
        // UPDATE y retorna true
    }
    
    public function eliminar_liga($liga_id) {
        // Verifica dependencias y DELETE
    }
    
    public function cambiar_estatus($liga_id, $estatus) {
        // UPDATE solo el campo estatus
    }
    
    public function verificar_nombre_existe($liga_nombre, $excluir_id = null) {
        // SELECT COUNT(*) y retorna bool
    }
}
```

---

## 🔶 CONTROLLERS (controller/)

### Estructura Estándar
```php
<?php
require_once("../config/conexion.php");
require_once("../models/[Modelo].php");

$entidad = new [Modelo]();
$action = $_POST["action"] ?? $_GET["action"] ?? '';

if (!empty($action)) {
    switch ($action) {
        case "listar":
            // Código...
            break;
        case "obtener":
            // Código...
            break;
        case "crear":
            // Código...
            break;
        case "actualizar":
            // Código...
            break;
        case "cambiar_estatus":
            // Código...
            break;
        case "eliminar":
            // Código...
            break;
        default:
            echo json_encode([
                "status" => "error",
                "message" => "Acción no válida"
            ]);
    }
}
?>
```

### Respuestas JSON Estándar
```php
// Éxito
echo json_encode([
    "status" => "success",
    "message" => "Operación exitosa",
    "id" => $resultado,  // Opcional en crear
    "data" => $datos     // Opcional
]);

// Error
echo json_encode([
    "status" => "error",
    "message" => "Descripción del error"
]);
```

### Casos Comunes

#### LISTAR
```php
case "listar":
    $datos = $entidad->obtener_[entidad]s();
    echo json_encode([
        "status" => "success",
        "data" => $datos
    ]);
    break;
```

#### OBTENER
```php
case "obtener":
    $id = $_POST['id'] ?? $_GET['id'] ?? 0;
    $datos = $entidad->obtener_[entidad]_por_id($id);
    echo json_encode($datos);
    break;
```

#### CREAR
```php
case "crear":
    $datos = [
        'campo1' => $_POST['campo1'],
        'campo2' => $_POST['campo2']
    ];
    
    // Validación de duplicados
    if ($entidad->verificar_nombre_existe($datos['nombre'])) {
        echo json_encode([
            "status" => "error",
            "message" => "Ya existe un registro con ese nombre"
        ]);
        break;
    }
    
    $resultado = $entidad->crear_[entidad]($datos);
    
    if ($resultado) {
        echo json_encode([
            "status" => "success",
            "message" => "[Entidad] creada correctamente",
            "id" => $resultado
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Error al crear [entidad]"
        ]);
    }
    break;
```

#### ACTUALIZAR
```php
case "actualizar":
    $id = $_POST['id'];
    $datos = [
        'campo1' => $_POST['campo1'],
        'campo2' => $_POST['campo2']
    ];
    
    // Validación excluyendo el actual
    if ($entidad->verificar_nombre_existe($datos['nombre'], $id)) {
        echo json_encode([
            "status" => "error",
            "message" => "Ya existe otro registro con ese nombre"
        ]);
        break;
    }
    
    $resultado = $entidad->actualizar_[entidad]($id, $datos);
    
    if ($resultado) {
        echo json_encode([
            "status" => "success",
            "message" => "[Entidad] actualizada correctamente"
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Error al actualizar [entidad]"
        ]);
    }
    break;
```

#### CAMBIAR ESTATUS
```php
case "cambiar_estatus":
    $id = $_POST['id'];
    $estatus = $_POST['estatus'];
    
    $resultado = $entidad->cambiar_estatus($id, $estatus);
    
    if ($resultado) {
        echo json_encode([
            "status" => "success",
            "message" => "Estatus actualizado correctamente"
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Error al actualizar estatus"
        ]);
    }
    break;
```

#### ELIMINAR
```php
case "eliminar":
    $id = $_POST['id'];
    $resultado = $entidad->eliminar_[entidad]($id);
    
    if ($resultado) {
        echo json_encode([
            "status" => "success",
            "message" => "[Entidad] eliminada correctamente"
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "No se puede eliminar porque tiene registros asociados"
        ]);
    }
    break;
```

---

## 🗄️ BASE DE DATOS

### Prefijo de Tablas
```
lp_[nombre]
```

### Tablas Principales
```
lp_ligas
lp_temporadas
lp_equipos
lp_jugadores
lp_partidos
lp_jornadas
lp_goles
lp_tarjetas
lp_posiciones
lp_pagos
lp_abonos
lp_banners
lp_galeria
lp_usuarios
```

### Campos Estándar
- **ID:** `[tabla]_id` (INT, PK, AUTO_INCREMENT)
- **Nombre:** `[tabla]_nombre` (VARCHAR)
- **Estatus:** `[tabla]_estatus` (TINYINT, 1=activo, 0=inactivo)
- **Fechas:** `fecha_creacion`, `fecha_actualizacion` (DATETIME)

---

## ❌ NO USAR (Métodos obsoletos)

### ❌ En Modelos:
```php
// NO:
desactivar_[entidad]()
verificar_nombre_existente()

// SÍ:
eliminar_[entidad]()  // o cambiar_estatus()
verificar_nombre_existe()
```

### ❌ En Controllers:
```php
// NO:
$resultado = $entidad->desactivar_liga($id);
if ($entidad->verificar_nombre_existente($nombre)) { }

// SÍ:
$resultado = $entidad->eliminar_liga($id);
if ($entidad->verificar_nombre_existe($nombre)) { }
```

---

## 📝 CONVENCIONES DE NOMBRES

### Archivos
- Modelos: `PascalCase.php` (Liga.php, Equipo.php)
- Controllers: `snake_case_controller.php` (liga_controller.php)
- JavaScript: `snake_case.js` (liga.js)
- CSS: `snake_case.css` (liga.css)
- Páginas: `snake_case.php` (liga.php)

### Variables
- PHP: `$snake_case`
- JavaScript: `camelCase`
- SQL: `snake_case`

### Funciones
- Modelos: `obtener_`, `crear_`, `actualizar_`, `eliminar_`
- Controllers: Mismas acciones que modelos
- JavaScript: `camelCase`

---

## 🔧 MANEJO DE ARCHIVOS

### Upload de Imágenes
```php
if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] == 0) {
    $directorio = "../assets/[carpeta]/";
    if (!file_exists($directorio)) {
        mkdir($directorio, 0777, true);
    }
    
    $extension = pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION);
    $nombre_archivo = '[prefijo]_' . time() . '_' . uniqid() . '.' . $extension;
    $ruta_destino = $directorio . $nombre_archivo;
    
    if (move_uploaded_file($_FILES['archivo']['tmp_name'], $ruta_destino)) {
        // Éxito
    }
}
```

### Carpetas de Assets
```
assets/
├── logos/       (ligas)
├── equipos/     (logos de equipos)
├── jugadores/   (fotos de jugadores)
├── banners/     (banners informativos)
└── galeria/     (fotos de partidos)
```

---

## ✅ RESUMEN RÁPIDO

**Modelos:**
- Conexión: `new Conectar()`
- Métodos: `obtener_`, `crear_`, `actualizar_`, `eliminar_`
- Validaciones: `verificar_nombre_existe()`

**Controllers:**
- Action via: `$_POST["action"] ?? $_GET["action"]`
- Respuestas: JSON con `status` y `message`
- Validar antes de crear/actualizar

**Base de Datos:**
- Prefijo: `lp_`
- IDs: `[tabla]_id`
- Estatus: `[tabla]_estatus` (1/0)

**NO usar:**
- `desactivar_[entidad]()`
- `verificar_nombre_existente()`