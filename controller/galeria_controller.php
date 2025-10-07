<?php
/**
 * Controlador: Galeria
 * Descripción: Gestión de galería de fotos
 */

require_once("../config/conexion.php");
require_once("../models/Galeria.php");

$galeria = new Galeria();

$action = $_POST["action"] ?? $_GET["action"] ?? '';

if (!empty($action)) {
    
    switch ($action) {
        
        // ====================================
        // LISTAR FOTOS
        // ====================================
        case "listar":
            $activas_solo = $_POST['activas_solo'] ?? $_GET['activas_solo'] ?? false;
            $datos = $galeria->obtener_fotos($activas_solo);
            $output = array();
            
            foreach ($datos as $row) {
                $sub_array = array();
                $sub_array[] = $row["foto_id"];
                
                // Foto
                if (!empty($row["foto_imagen"])) {
                    $sub_array[] = '<img src="../assets/galeria/' . $row["foto_imagen"] . '" width="100" height="70" class="img-thumbnail" onclick="verFoto(' . $row["foto_id"] . ')" style="cursor: pointer;">';
                } else {
                    $sub_array[] = '<span class="text-muted">Sin imagen</span>';
                }
                
                $sub_array[] = htmlspecialchars($row["foto_titulo"] ?? 'Sin título');
                
                // Partido asociado
                if (!empty($row["equipo_local"]) && !empty($row["equipo_visitante"])) {
                    $sub_array[] = htmlspecialchars($row["equipo_local"]) . ' vs ' . htmlspecialchars($row["equipo_visitante"]);
                } else {
                    $sub_array[] = 'N/A';
                }
                
                $sub_array[] = !empty($row["fecha_foto"]) ? date('d/m/Y', strtotime($row["fecha_foto"])) : 'N/A';
                $sub_array[] = date('d/m/Y', strtotime($row["fecha_subida"]));
                $sub_array[] = ($row["foto_estatus"] == 1) ? 
                    '<span class="badge bg-success">Visible</span>' : 
                    '<span class="badge bg-secondary">Oculta</span>';
                
                $sub_array[] = '<button class="btn btn-info btn-sm" onclick="verFoto(' . $row["foto_id"] . ')" title="Ver Foto"><i class="fas fa-eye"></i></button>
                                <button class="btn btn-warning btn-sm" onclick="editar(' . $row["foto_id"] . ')" title="Editar"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-danger btn-sm" onclick="eliminar(' . $row["foto_id"] . ')" title="Eliminar"><i class="fas fa-trash"></i></button>';
                $output[] = $sub_array;
            }
            
            echo json_encode([
                "data" => $output
            ]);
            break;
        
        // ====================================
        // LISTAR FOTOS RECIENTES (PÚBLICO)
        // ====================================
        case "recientes":
            $limite = $_POST['limite'] ?? $_GET['limite'] ?? 12;
            $datos = $galeria->obtener_fotos_recientes($limite);
            echo json_encode($datos);
            break;
        
        // ====================================
        // LISTAR FOTOS POR PARTIDO
        // ====================================
        case "listar_partido":
            $partido_id = $_POST['partido_id'] ?? $_GET['partido_id'] ?? 0;
            $datos = $galeria->obtener_fotos_por_partido($partido_id);
            echo json_encode($datos);
            break;
        
        // ====================================
        // LISTAR FOTOS POR TEMPORADA
        // ====================================
        case "listar_temporada":
            $temporada_id = $_POST['temporada_id'] ?? $_GET['temporada_id'] ?? 0;
            $datos = $galeria->obtener_fotos_por_temporada($temporada_id);
            echo json_encode($datos);
            break;
        
        // ====================================
        // OBTENER FOTO POR ID
        // ====================================
        case "obtener":
            $foto_id = $_POST['foto_id'] ?? $_GET['foto_id'] ?? 0;
            $datos = $galeria->obtener_foto_por_id($foto_id);
            echo json_encode($datos);
            break;
        
        // ====================================
        // SUBIR FOTO
        // ====================================
        case "subir":
            $datos = [
                'foto_titulo' => $_POST['foto_titulo'] ?? '',
                'foto_imagen' => $_POST['foto_imagen'],
                'foto_descripcion' => $_POST['foto_descripcion'] ?? '',
                'partido_id' => $_POST['partido_id'] ?? null,
                'fecha_foto' => $_POST['fecha_foto'] ?? date('Y-m-d')
            ];
            
            $resultado = $galeria->subir_foto($datos);
            
            if ($resultado) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Foto subida correctamente",
                    "id" => $resultado
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Error al subir foto"
                ]);
            }
            break;
        
        // ====================================
        // ACTUALIZAR FOTO
        // ====================================
        case "actualizar":
            $foto_id = $_POST['foto_id'];
            $datos = [
                'foto_titulo' => $_POST['foto_titulo'] ?? '',
                'foto_descripcion' => $_POST['foto_descripcion'] ?? '',
                'partido_id' => $_POST['partido_id'] ?? null,
                'fecha_foto' => $_POST['fecha_foto'] ?? date('Y-m-d')
            ];
            
            $resultado = $galeria->actualizar_foto($foto_id, $datos);
            
            if ($resultado) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Foto actualizada correctamente"
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Error al actualizar foto"
                ]);
            }
            break;
        
        // ====================================
        // CAMBIAR ESTATUS
        // ====================================
        case "cambiar_estatus":
            $foto_id = $_POST['foto_id'];
            $estatus = $_POST['estatus'];
            
            $resultado = $galeria->cambiar_estatus($foto_id, $estatus);
            
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
        
        // ====================================
        // ELIMINAR FOTO
        // ====================================
        case "eliminar":
            $foto_id = $_POST['foto_id'];
            $resultado = $galeria->eliminar_foto($foto_id);
            
            if ($resultado) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Foto eliminada correctamente"
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Error al eliminar foto"
                ]);
            }
            break;
        
        // ====================================
        // BUSCAR FOTOS
        // ====================================
        case "buscar":
            $termino = $_POST['termino'] ?? $_GET['termino'] ?? '';
            $datos = $galeria->buscar_fotos($termino);
            echo json_encode($datos);
            break;
        
        // ====================================
        // CONTAR FOTOS
        // ====================================
        case "contar":
            $activas_solo = $_POST['activas_solo'] ?? $_GET['activas_solo'] ?? true;
            $total = $galeria->contar_fotos($activas_solo);
            echo json_encode([
                "total" => $total
            ]);
            break;
        
        default:
            echo json_encode([
                "status" => "error",
                "message" => "Acción no válida"
            ]);
            break;
    }
} else {
    echo json_encode([
        "status" => "error",
        "message" => "No se especificó ninguna acción"
    ]);
}
?>