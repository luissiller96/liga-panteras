<?php
/**
 * Controlador: Banner
 * Descripción: Gestión de banners informativos
 */

require_once("../config/conexion.php");
require_once("../models/Banner.php");

$banner = new Banner();

$action = $_POST["action"] ?? $_GET["action"] ?? '';

if (!empty($action)) {
    
    switch ($action) {
        
        // ====================================
        // LISTAR BANNERS
        // ====================================
        case "listar":
            $activos_solo = $_POST['activos_solo'] ?? $_GET['activos_solo'] ?? false;
            $datos = $banner->obtener_banners($activos_solo);
            $output = array();
            
            foreach ($datos as $row) {
                $sub_array = array();
                $sub_array[] = $row["banner_id"];
                $sub_array[] = $row["banner_orden"];
                
                // Imagen
                if (!empty($row["banner_imagen"])) {
                    $sub_array[] = '<img src="../assets/banners/' . $row["banner_imagen"] . '" width="150" height="80" class="img-thumbnail">';
                } else {
                    $sub_array[] = '<span class="text-muted">Sin imagen</span>';
                }
                
                $sub_array[] = htmlspecialchars($row["banner_titulo"] ?? 'Sin título');
                $sub_array[] = htmlspecialchars($row["banner_link"] ?? 'N/A');
                $sub_array[] = ($row["banner_estatus"] == 1) ? 
                    '<span class="badge bg-success">Activo</span>' : 
                    '<span class="badge bg-secondary">Inactivo</span>';
                
                $sub_array[] = '<button class="btn btn-secondary btn-sm" onclick="moverArriba(' . $row["banner_id"] . ')" title="Mover Arriba"><i class="fas fa-arrow-up"></i></button>
                                <button class="btn btn-secondary btn-sm" onclick="moverAbajo(' . $row["banner_id"] . ')" title="Mover Abajo"><i class="fas fa-arrow-down"></i></button>
                                <button class="btn btn-warning btn-sm" onclick="editar(' . $row["banner_id"] . ')" title="Editar"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-danger btn-sm" onclick="eliminar(' . $row["banner_id"] . ')" title="Eliminar"><i class="fas fa-trash"></i></button>';
                $output[] = $sub_array;
            }
            
            echo json_encode([
                "data" => $output
            ]);
            break;
        
        // ====================================
        // LISTAR BANNERS ACTIVOS (PÚBLICO)
        // ====================================
        case "listar_activos":
            $datos = $banner->obtener_banners(true);
            echo json_encode($datos);
            break;
        
        // ====================================
        // OBTENER BANNER POR ID
        // ====================================
        case "obtener":
            $banner_id = $_POST['banner_id'] ?? $_GET['banner_id'] ?? 0;
            $datos = $banner->obtener_banner_por_id($banner_id);
            echo json_encode($datos);
            break;
        
        // ====================================
        // CREAR BANNER
        // ====================================
        case "crear":
            $datos = [
                'banner_titulo' => $_POST['banner_titulo'] ?? '',
                'banner_imagen' => $_POST['banner_imagen'],
                'banner_link' => $_POST['banner_link'] ?? null
            ];
            
            $resultado = $banner->crear_banner($datos);
            
            if ($resultado) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Banner creado correctamente",
                    "id" => $resultado
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Error al crear banner"
                ]);
            }
            break;
        
        // ====================================
        // ACTUALIZAR BANNER
        // ====================================
        case "actualizar":
            $banner_id = $_POST['banner_id'];
            $datos = [
                'banner_titulo' => $_POST['banner_titulo'] ?? '',
                'banner_imagen' => $_POST['banner_imagen'],
                'banner_link' => $_POST['banner_link'] ?? null
            ];
            
            $resultado = $banner->actualizar_banner($banner_id, $datos);
            
            if ($resultado) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Banner actualizado correctamente"
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Error al actualizar banner"
                ]);
            }
            break;
        
        // ====================================
        // CAMBIAR ESTATUS
        // ====================================
        case "cambiar_estatus":
            $banner_id = $_POST['banner_id'];
            $estatus = $_POST['estatus'];
            
            $resultado = $banner->cambiar_estatus($banner_id, $estatus);
            
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
        // MOVER ARRIBA
        // ====================================
        case "mover_arriba":
            $banner_id = $_POST['banner_id'];
            $resultado = $banner->mover_arriba($banner_id);
            
            if ($resultado) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Banner movido correctamente"
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "No se puede mover más arriba"
                ]);
            }
            break;
        
        // ====================================
        // MOVER ABAJO
        // ====================================
        case "mover_abajo":
            $banner_id = $_POST['banner_id'];
            $resultado = $banner->mover_abajo($banner_id);
            
            if ($resultado) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Banner movido correctamente"
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "No se puede mover más abajo"
                ]);
            }
            break;
        
        // ====================================
        // ELIMINAR BANNER
        // ====================================
        case "eliminar":
            $banner_id = $_POST['banner_id'];
            $resultado = $banner->eliminar_banner($banner_id);
            
            if ($resultado) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Banner eliminado correctamente"
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Error al eliminar banner"
                ]);
            }
            break;
        
        // ====================================
        // REORDENAR BANNERS
        // ====================================
        case "reordenar":
            $resultado = $banner->reordenar_banners();
            
            if ($resultado) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Banners reordenados correctamente"
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Error al reordenar banners"
                ]);
            }
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