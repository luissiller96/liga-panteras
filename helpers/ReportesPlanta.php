<?php
// helpers/ReportesPlanta.php

require_once __DIR__ . "/../libs/fpdf186/fpdf.php";
require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/MovimientosPlanta.php";
require_once __DIR__ . "/../models/ProduccionPlanta.php";
require_once __DIR__ . "/../models/InventarioPlanta.php";


date_default_timezone_set('America/Mexico_City');


/**
 * Clase PDF personalizada para reportes de Solupatch Planta
 * SOLUCIÓN PARA TILDES: Usar iconv en lugar de utf8_decode
 */
class PDF_Planta extends FPDF 
{
    private $tituloReporte = '';
    private $periodo = '';

    public function setTitulo($titulo, $periodo)
    {
        $this->tituloReporte = $titulo;
        $this->periodo = $periodo;
    }

    // Función helper para convertir texto con tildes correctamente
    protected function convertirTexto($texto)
    {
        if (empty($texto))
            return '';
        // Convertir de UTF-8 a ISO-8859-1 para FPDF
        return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $texto);
    }

    function Header()
    {
        // Logo (si existe)
        if (file_exists('../assets/images/solupatch_logo.png')) {
            $this->Image('../assets/images/solupatch_logo.png', 10, 8, 40);
        }

        // Título
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, $this->convertirTexto($this->tituloReporte), 0, 1, 'C');

        // Período
        $this->SetFont('Arial', '', 11);
        $this->Cell(0, 5, $this->convertirTexto($this->periodo), 0, 1, 'C');

        // Fecha de generación
        $this->SetFont('Arial', 'I', 9);
        $this->Cell(0, 5, 'Generado: ' . date('d/m/Y H:i'), 0, 1, 'C');

        // Línea separadora
        $this->Ln(5);
        $this->SetDrawColor(233, 189, 64);
        $this->SetLineWidth(0.5);
        $this->Line(10, $this->GetY(), $this->GetPageWidth() - 10, $this->GetY());
        $this->Ln(10);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(128);
        $this->Cell(0, 10, $this->convertirTexto('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

class ReportesPlanta extends Conectar {

    private $pdf;
    private $movimientos;
    private $produccion;
    private $inventario;

    public function __construct()
    {
        $this->movimientos = new MovimientosPlanta();
        $this->produccion = new ProduccionPlanta();
        $this->inventario = new InventarioPlanta();
    }

    /**
     * Helper para convertir texto
     */
    private function convertir($texto)
    {
        if (empty($texto))
            return '';
        return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $texto);
    }

    public function generarReporteMovimientos($tipo, $fecha_inicio, $fecha_fin)
    {
        $this->pdf = new PDF_Planta();
        $this->pdf->AliasNbPages();

        $titulo = $tipo ? 'REPORTE DE ' . strtoupper($tipo) . 'S' : 'REPORTE DE MOVIMIENTOS';
        $periodo = "Período: " . date('d/m/Y', strtotime($fecha_inicio)) .
            " al " . date('d/m/Y', strtotime($fecha_fin));

        $this->pdf->setTitulo($titulo, $periodo);
        $this->pdf->AddPage('L', 'Letter');

        $datos = $this->movimientos->listar_movimientos($tipo, $fecha_inicio, $fecha_fin);

        if ($tipo == 'entrada' || !$tipo) {
            $this->generarTablaEntradas($datos);
        }

        if ($tipo == 'salida' || !$tipo) {
            if (!$tipo)
                $this->pdf->AddPage('L', 'Letter');
            $this->generarTablaSalidas($datos);
        }

        $this->agregarResumenMovimientos($datos);

        $this->pdf->Output('I', 'Reporte_Movimientos_' . date('Ymd') . '.pdf');
    }

    private function generarTablaEntradas($datos)
    {
        $this->pdf->SetFont('Arial', 'B', 12);
        $this->pdf->Cell(0, 8, 'ENTRADAS DE MATERIAL', 0, 1, 'L');

        $this->pdf->SetFillColor(233, 189, 64);
        $this->pdf->SetTextColor(255);
        $this->pdf->SetFont('Arial', 'B', 9);

        $this->pdf->Cell(25, 7, 'Fecha', 1, 0, 'C', true);
        $this->pdf->Cell(25, 7, 'Folio', 1, 0, 'C', true);
        $this->pdf->Cell(45, 7, 'Empresa', 1, 0, 'C', true);
        $this->pdf->Cell(35, 7, 'Producto', 1, 0, 'C', true);
        $this->pdf->Cell(25, 7, 'Peso (Ton)', 1, 0, 'C', true);
        $this->pdf->Cell(35, 7, 'Origen', 1, 0, 'C', true);
        $this->pdf->Cell(25, 7, 'Placas', 1, 0, 'C', true);
        $this->pdf->Cell(40, 7, 'Operador', 1, 1, 'C', true);

        $this->pdf->SetTextColor(0);
        $this->pdf->SetFont('Arial', '', 9);
        $totalKg = 0;

        foreach ($datos as $mov) {
            if ($mov['tipo_movimiento'] == 'entrada') {
                $this->pdf->Cell(25, 6, date('d/m/Y', strtotime($mov['fecha'])), 1);
                $this->pdf->Cell(25, 6, $this->convertir($mov['folio']), 1);
                $this->pdf->Cell(45, 6, $this->convertir(substr($mov['empresa'], 0, 25)), 1);
                $this->pdf->Cell(35, 6, $this->convertir($mov['producto']), 1);
                $this->pdf->Cell(25, 6, number_format($mov['peso_toneladas'], 2), 1, 0, 'R');
                $this->pdf->Cell(35, 6, $this->convertir(substr($mov['origen'], 0, 20)), 1);
                $this->pdf->Cell(25, 6, $this->convertir($mov['placas_camion']), 1);
                $this->pdf->Cell(40, 6, $this->convertir(substr($mov['operador'], 0, 25)), 1, 1);

                $totalKg += $mov['peso_kg'];
            }
        }

        $this->pdf->SetFont('Arial', 'B', 9);
        $this->pdf->Cell(130, 8, 'TOTAL ENTRADAS:', 1, 0, 'R');
        $this->pdf->Cell(25, 8, number_format($totalKg / 1000, 2) . ' Ton', 1, 1, 'R');
    }

    private function generarTablaSalidas($datos)
    {
        $this->pdf->SetFont('Arial', 'B', 12);
        $this->pdf->Cell(0, 8, 'SALIDAS DE MATERIAL', 0, 1, 'L');

        $this->pdf->SetFillColor(239, 68, 68);
        $this->pdf->SetTextColor(255);
        $this->pdf->SetFont('Arial', 'B', 9);

        $this->pdf->Cell(25, 7, 'Fecha', 1, 0, 'C', true);
        $this->pdf->Cell(45, 7, 'Cliente', 1, 0, 'C', true);
        $this->pdf->Cell(35, 7, 'Producto', 1, 0, 'C', true);
        $this->pdf->Cell(20, 7, 'Bultos', 1, 0, 'C', true);
        $this->pdf->Cell(25, 7, 'Peso (Ton)', 1, 0, 'C', true);
        $this->pdf->Cell(40, 7, 'Vendedor', 1, 0, 'C', true);
        $this->pdf->Cell(35, 7, 'Destino', 1, 0, 'C', true);
        $this->pdf->Cell(30, 7, 'Estatus', 1, 1, 'C', true);

        $this->pdf->SetTextColor(0);
        $this->pdf->SetFont('Arial', '', 9);
        $totalKg = 0;
        $pendientes = 0;

        foreach ($datos as $mov) {
            if ($mov['tipo_movimiento'] == 'salida') {
                $this->pdf->Cell(25, 6, date('d/m/Y', strtotime($mov['fecha'])), 1);
                $this->pdf->Cell(45, 6, $this->convertir(substr($mov['empresa'], 0, 25)), 1);
                $this->pdf->Cell(35, 6, $this->convertir($mov['producto']), 1);
                $this->pdf->Cell(20, 6, $mov['bultos'], 1, 0, 'C');
                $this->pdf->Cell(25, 6, number_format($mov['peso_toneladas'], 2), 1, 0, 'R');
                $this->pdf->Cell(40, 6, $this->convertir(substr($mov['vendedor_nombre'], 0, 25)), 1);
                $this->pdf->Cell(35, 6, $this->convertir(substr($mov['destino'], 0, 20)), 1);
                $this->pdf->Cell(30, 6, strtoupper($mov['estatus_pago']), 1, 1, 'C');

                $totalKg += $mov['peso_kg'];
                if ($mov['estatus_pago'] == 'pendiente' || $mov['estatus_pago'] == 'credito') {
                    $pendientes++;
                }
            }
        }

        $this->pdf->SetFont('Arial', 'B', 9);
        $this->pdf->Cell(125, 8, 'TOTAL SALIDAS:', 1, 0, 'R');
        $this->pdf->Cell(25, 8, number_format($totalKg / 1000, 2) . ' Ton', 1, 1, 'R');

        if ($pendientes > 0) {
            $this->pdf->SetTextColor(239, 68, 68);
            $this->pdf->Cell(0, 8, "* Salidas con pago pendiente: $pendientes", 0, 1);
        }
    }
    private function agregarResumenMovimientos($datos)
    {
        $this->pdf->AddPage('P', 'Letter');
        $this->pdf->SetFont('Arial', 'B', 14);
        $this->pdf->Cell(0, 10, 'RESUMEN EJECUTIVO', 0, 1, 'C');
        $this->pdf->Ln(5);

        $totalEntradas = 0;
        $totalSalidas = 0;
        $productos = [];

        foreach ($datos as $mov) {
            if ($mov['tipo_movimiento'] == 'entrada') {
                $totalEntradas += $mov['peso_kg'];
            } else {
                $totalSalidas += $mov['peso_kg'];
            }
            if (!isset($productos[$mov['producto']])) {
                $productos[$mov['producto']] = ['entrada' => 0, 'salida' => 0];
            }
            if ($mov['tipo_movimiento'] == 'entrada') {
                $productos[$mov['producto']]['entrada'] += $mov['peso_kg'];
            } else {
                $productos[$mov['producto']]['salida'] += $mov['peso_kg'];
            }
        }

        $this->pdf->SetFillColor(248, 250, 252);
        $this->pdf->SetFont('Arial', 'B', 11);
        $this->pdf->Cell(95, 10, 'Total Entradas:', 1, 0, 'L', true);
        $this->pdf->SetFont('Arial', '', 11);
        $this->pdf->Cell(95, 10, number_format($totalEntradas / 1000, 2) . ' Toneladas', 1, 1, 'R');

        $this->pdf->SetFont('Arial', 'B', 11);
        $this->pdf->Cell(95, 10, 'Total Salidas:', 1, 0, 'L', true);
        $this->pdf->SetFont('Arial', '', 11);
        $this->pdf->Cell(95, 10, number_format($totalSalidas / 1000, 2) . ' Toneladas', 1, 1, 'R');

        $balance = ($totalEntradas - $totalSalidas) / 1000;
        $this->pdf->SetFont('Arial', 'B', 11);
        $this->pdf->Cell(95, 10, 'Balance:', 1, 0, 'L', true);
        $this->pdf->SetTextColor($balance >= 0 ? 0 : 255, $balance >= 0 ? 128 : 0, 0);
        $this->pdf->Cell(95, 10, number_format($balance, 2) . ' Toneladas', 1, 1, 'R');

        if (count($productos) > 0) {
            $this->pdf->Ln(10);
            $this->pdf->SetTextColor(0);
            $this->pdf->SetFont('Arial', 'B', 12);
            $this->pdf->Cell(0, 8, 'DESGLOSE POR PRODUCTO', 0, 1);

            $this->pdf->SetFont('Arial', 'B', 10);
            $this->pdf->Cell(60, 8, 'Producto', 1, 0, 'C', true);
            $this->pdf->Cell(45, 8, 'Entradas (Ton)', 1, 0, 'C', true);
            $this->pdf->Cell(45, 8, 'Salidas (Ton)', 1, 0, 'C', true);
            $this->pdf->Cell(40, 8, 'Balance (Ton)', 1, 1, 'C', true);

            $this->pdf->SetFont('Arial', '', 10);
            foreach ($productos as $nombre => $valores) {
                $balanceProd = ($valores['entrada'] - $valores['salida']) / 1000;
                $this->pdf->Cell(60, 7, $this->convertir($nombre), 1);
                $this->pdf->Cell(45, 7, number_format($valores['entrada'] / 1000, 2), 1, 0, 'R');
                $this->pdf->Cell(45, 7, number_format($valores['salida'] / 1000, 2), 1, 0, 'R');
                $this->pdf->Cell(40, 7, number_format($balanceProd, 2), 1, 1, 'R');
            }
        }
    }
    public function generarReporteProduccion($fecha_inicio, $fecha_fin, $producto_id = null)
    {
        $this->pdf = new PDF_Planta();
        $this->pdf->AliasNbPages();

        $titulo = 'REPORTE DE PRODUCCIÓN';
        $periodo = "Período: " . date('d/m/Y', strtotime($fecha_inicio)) .
            " al " . date('d/m/Y', strtotime($fecha_fin));

        $this->pdf->setTitulo($titulo, $periodo);
        $this->pdf->AddPage('L', 'Letter');

        $datos = $this->produccion->listar_produccion($fecha_inicio, $fecha_fin, $producto_id);
        $estadisticas = $this->produccion->obtener_estadisticas_produccion(30);

        $this->pdf->SetFont('Arial', 'B', 12);
        $this->pdf->Cell(0, 8, $this->convertir('REGISTRO DE PRODUCCIÓN DIARIA'), 0, 1);

        $this->pdf->SetFillColor(245, 158, 11);
        $this->pdf->SetTextColor(255);
        $this->pdf->SetFont('Arial', 'B', 10);

        $this->pdf->Cell(30, 8, 'Fecha', 1, 0, 'C', true);
        $this->pdf->Cell(25, 8, 'Hora', 1, 0, 'C', true);
        $this->pdf->Cell(50, 8, 'Producto', 1, 0, 'C', true);
        $this->pdf->Cell(35, 8, 'Cantidad (Ton)', 1, 0, 'C', true);
        $this->pdf->Cell(115, 8, 'Observaciones', 1, 1, 'C', true);

        $this->pdf->SetTextColor(0);
        $this->pdf->SetFont('Arial', '', 9);

        $totalProducido = 0;
        $productosTotales = [];

        foreach ($datos as $registro) {
            $this->pdf->Cell(30, 7, date('d/m/Y', strtotime($registro['fecha'])), 1);
            $this->pdf->Cell(25, 7, $registro['hora_registro'], 1, 0, 'C');
            $this->pdf->Cell(50, 7, $this->convertir($registro['producto']), 1);
            $this->pdf->Cell(35, 7, number_format($registro['cantidad_toneladas'], 2), 1, 0, 'R');

            $observaciones = substr($registro['observaciones'] ?? '-', 0, 60);
            $this->pdf->Cell(115, 7, $this->convertir($observaciones), 1, 1);

            $totalProducido += $registro['cantidad_producida_kg'];

            if (!isset($productosTotales[$registro['producto']])) {
                $productosTotales[$registro['producto']] = 0;
            }
            $productosTotales[$registro['producto']] += $registro['cantidad_producida_kg'];
        }

        $this->pdf->SetFont('Arial', 'B', 10);
        $this->pdf->Cell(105, 8, 'TOTAL PRODUCIDO:', 1, 0, 'R');
        $this->pdf->Cell(35, 8, number_format($totalProducido / 1000, 2) . ' Ton', 1, 1, 'R');

        $this->pdf->Ln(10);
        $this->pdf->SetFont('Arial', 'B', 12);
        $this->pdf->Cell(0, 8, 'RESUMEN POR PRODUCTO', 0, 1);

        $this->pdf->SetFont('Arial', 'B', 10);
        $this->pdf->SetFillColor(248, 250, 252);

        if ($totalProducido > 0) {
            foreach ($productosTotales as $producto => $total) {
                $porcentaje = ($total / $totalProducido) * 100;
                $this->pdf->Cell(80, 8, $this->convertir($producto), 1, 0, 'L', true);
                $this->pdf->Cell(50, 8, number_format($total / 1000, 2) . ' Ton', 1, 0, 'R');
                $this->pdf->Cell(40, 8, number_format($porcentaje, 1) . '%', 1, 1, 'R');
            }
        }

        if (!empty($estadisticas['estadisticas'])) {
            $this->pdf->AddPage('P', 'Letter');
            $this->pdf->SetFont('Arial', 'B', 14);
            $this->pdf->Cell(0, 10, $this->convertir('ANÁLISIS ESTADÍSTICO'), 0, 1, 'C');
            $this->pdf->Ln(5);

            $this->pdf->SetFont('Arial', 'B', 11);
            foreach ($estadisticas['estadisticas'] as $stat) {
                if ($stat['total_toneladas'] > 0) {
                    $this->pdf->SetFillColor(240, 240, 240);
                    $this->pdf->Cell(0, 8, $this->convertir($stat['producto']), 0, 1, 'L', true);

                    $this->pdf->SetFont('Arial', '', 10);
                    $this->pdf->Cell(95, 7, 'Total producido:', 1, 0, 'L');
                    $this->pdf->Cell(95, 7, number_format($stat['total_toneladas'], 2) . ' Ton', 1, 1, 'R');

                    $this->pdf->Cell(95, 7, 'Promedio diario:', 1, 0, 'L');
                    $this->pdf->Cell(95, 7, number_format($stat['promedio_toneladas'], 2) . ' Ton', 1, 1, 'R');

                    $this->pdf->Cell(95, 7, 'Registros:', 1, 0, 'L');
                    $this->pdf->Cell(95, 7, $stat['total_registros'], 1, 1, 'R');

                    $this->pdf->Ln(5);
                    $this->pdf->SetFont('Arial', 'B', 11);
                }
            }
        }

        $this->pdf->Output('I', 'Reporte_Produccion_' . date('Ymd') . '.pdf');
    }

    /**
     * NUEVO REPORTE DE INVENTARIO - Formato de calendario mensual
     */
    public function generarReporteInventario($fecha_inicio, $fecha_fin)
    {
        $this->pdf = new PDF_Planta();
        $this->pdf->AliasNbPages();

        $titulo = 'REPORTE DE INVENTARIO MENSUAL';
        $periodo = "Período: " . date('d/m/Y', strtotime($fecha_inicio)) .
            " al " . date('d/m/Y', strtotime($fecha_fin));

        $this->pdf->setTitulo($titulo, $periodo);
        $this->pdf->AddPage('L', 'Letter'); // Horizontal

        // Obtener productos activos
        $productos = $this->obtenerProductosActivos();

        // Obtener datos de inventario diario
        $inventarioDiario = $this->obtenerInventarioDiario($fecha_inicio, $fecha_fin);

        // Generar tabla de inventario por día
        $this->generarTablaInventarioDiario($fecha_inicio, $fecha_fin, $productos, $inventarioDiario);

        $this->pdf->Output('I', 'Reporte_Inventario_' . date('Ymd') . '.pdf');
    }

private function obtenerProductosActivos()
{
    // Usar el modelo de inventario que ya extiende Conectar
    $inventario = new InventarioPlanta();
    $todosLosInventarios = $inventario->obtener_inventario_completo();

    $productos = [];
    foreach ($todosLosInventarios as $item) {
        $productos[] = [
            'producto_id' => $item['producto_id'],
            'nombre' => $item['producto']
        ];
    }
    
    // Ordenar productos según el orden deseado
    $ordenDeseado = ['Solupatch', 'Cemex', 'Bacherrey', 'Permamix'];
    
    usort($productos, function($a, $b) use ($ordenDeseado) {
        $posA = array_search($a['nombre'], $ordenDeseado);
        $posB = array_search($b['nombre'], $ordenDeseado);
        
        // Si no se encuentra, poner al final
        if ($posA === false) $posA = 9999;
        if ($posB === false) $posB = 9999;
        
        return $posA - $posB;
    });
    
    return $productos;
}
    /**
     * Obtener inventario diario calculado
     */
private function obtenerInventarioDiario($fecha_inicio, $fecha_fin)
{
    $conn = parent::conexion();
    parent::set_names();

    $sql = "
    SELECT 
        fecha,
        producto_id,
        inventario_inicial_kg,
        produccion_kg,
        ajustes_kg,
        inventario_final_kg
    FROM (
        SELECT 
            d.fecha,
            p.producto_id,
            
            -- Inventario inicial del día
            COALESCE((
                SELECT SUM(COALESCE(prod.cantidad_producida_kg, 0))
                FROM pl_produccion prod
                WHERE prod.producto_id = p.producto_id
                AND prod.fecha < d.fecha
                AND prod.activo = 1
            ), 0) + COALESCE((
                SELECT SUM(diferencia_kg)
                FROM pl_ajustes_inventario
                WHERE producto_id = p.producto_id
                AND fecha < d.fecha
            ), 0) as inventario_inicial_kg,
            
            -- Producción del día
            COALESCE((
                SELECT SUM(cantidad_producida_kg)
                FROM pl_produccion
                WHERE producto_id = p.producto_id
                AND fecha = d.fecha
                AND activo = 1
            ), 0) as produccion_kg,
            
            -- Ajustes del día
            COALESCE((
                SELECT SUM(diferencia_kg)
                FROM pl_ajustes_inventario
                WHERE producto_id = p.producto_id
                AND fecha = d.fecha
            ), 0) as ajustes_kg,
            
            -- Inventario final del día (inicial + producción + ajustes)
            COALESCE((
                SELECT SUM(COALESCE(prod.cantidad_producida_kg, 0))
                FROM pl_produccion prod
                WHERE prod.producto_id = p.producto_id
                AND prod.fecha <= d.fecha
                AND prod.activo = 1
            ), 0) + COALESCE((
                SELECT SUM(diferencia_kg)
                FROM pl_ajustes_inventario
                WHERE producto_id = p.producto_id
                AND fecha <= d.fecha
            ), 0) as inventario_final_kg
            
        FROM (
            -- Fechas del rango
            SELECT DISTINCT DATE(fecha) as fecha
            FROM pl_produccion
            WHERE fecha BETWEEN ? AND ?
            AND activo = 1
            
            UNION
            
            SELECT DISTINCT DATE(fecha) as fecha
            FROM pl_ajustes_inventario
            WHERE fecha BETWEEN ? AND ?
            
            UNION
            
            SELECT DATE(?) as fecha
            UNION
            SELECT DATE(?) as fecha
        ) d
        CROSS JOIN pl_productos p
        WHERE p.activo = 1
    ) resumen
    ORDER BY fecha, producto_id
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$fecha_inicio, $fecha_fin, $fecha_inicio, $fecha_fin, $fecha_inicio, $fecha_fin]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


    /**
     * Generar tabla de inventario diario
     */
    private function generarTablaInventarioDiario($fecha_inicio, $fecha_fin, $productos, $datos)
    {
        // Título
        $this->pdf->SetFont('Arial', 'B', 12);
        $this->pdf->Cell(0, 8, $this->convertir('INVENTARIO DIARIO POR PRODUCTO (Toneladas)'), 0, 1, 'C');
        $this->pdf->Ln(3);

        // Calcular ancho de columnas
        $anchoFecha = 22;
        $numProductos = count($productos);
        $anchoColumnaProducto = ($this->pdf->GetPageWidth() - 20 - $anchoFecha) / $numProductos;

        // Encabezados
        $this->pdf->SetFillColor(233, 189, 64);
        $this->pdf->SetTextColor(255);
        $this->pdf->SetFont('Arial', 'B', 8);

        // Columna de fecha
        $this->pdf->Cell($anchoFecha, 10, 'Fecha', 1, 0, 'C', true);

        // Columnas de productos
        foreach ($productos as $prod) {
            $nombreCorto = substr($prod['nombre'], 0, 10);
            $this->pdf->Cell($anchoColumnaProducto, 10, $this->convertir($nombreCorto), 1, 0, 'C', true);
        }
        $this->pdf->Ln();

        // Organizar datos por fecha
        $datosPorFecha = [];
        foreach ($datos as $row) {
            $fecha = $row['fecha'];
            $prod_id = $row['producto_id'];

            if (!isset($datosPorFecha[$fecha])) {
                $datosPorFecha[$fecha] = [];
            }
            $datosPorFecha[$fecha][$prod_id] = $row;
        }

        // Generar fechas del rango
        $fechaActual = new DateTime($fecha_inicio);
        $fechaFinal = new DateTime($fecha_fin);

        $this->pdf->SetTextColor(0);
        $this->pdf->SetFont('Arial', '', 7);

        $alternar = false;

        while ($fechaActual <= $fechaFinal) {
            $fechaStr = $fechaActual->format('Y-m-d');
            $fechaDisplay = $fechaActual->format('d M');

            // Fondo alternado para mejor lectura
            if ($alternar) {
                $this->pdf->SetFillColor(248, 250, 252);
            } else {
                $this->pdf->SetFillColor(255, 255, 255);
            }
            $alternar = !$alternar;

            // Fecha
            $this->pdf->Cell($anchoFecha, 6, $fechaDisplay, 1, 0, 'C', true);

            // Valores de inventario para cada producto
            foreach ($productos as $prod) {
                $prod_id = $prod['producto_id'];

                if (isset($datosPorFecha[$fechaStr][$prod_id])) {
                    $inventarioFinal = $datosPorFecha[$fechaStr][$prod_id]['inventario_final_kg'] / 1000;
                    $valor = number_format($inventarioFinal, 2);
                } else {
                    $valor = '-';
                }

                $this->pdf->Cell($anchoColumnaProducto, 6, $valor, 1, 0, 'C', true);
            }

            $this->pdf->Ln();
            $fechaActual->modify('+1 day');
        }

        $this->pdf->Ln(5);

        // SECCIÓN: INVENTARIO INICIAL Y FINAL
        $this->pdf->SetFont('Arial', 'B', 11);
        $this->pdf->Cell(0, 8, $this->convertir('RESUMEN DEL PERÍODO'), 0, 1, 'L');
        $this->pdf->Ln(2);

        // Calcular inventarios iniciales y finales
        $inventarioInicial = $this->calcularInventarioInicial($fecha_inicio, $productos);
        $inventarioFinal = $this->calcularInventarioFinal($fecha_fin, $productos);

        // Tabla de resumen
        $this->pdf->SetFillColor(240, 240, 240);
        $this->pdf->SetFont('Arial', 'B', 9);

        $this->pdf->Cell(50, 8, 'Producto', 1, 0, 'C', true);
        $this->pdf->Cell(35, 8, 'Inventario Inicial', 1, 0, 'C', true);
        $this->pdf->Cell(35, 8, $this->convertir('Producción Total'), 1, 0, 'C', true);
        $this->pdf->Cell(35, 8, 'Inventario Final', 1, 0, 'C', true);
        $this->pdf->Cell(35, 8, $this->convertir('Variación'), 1, 1, 'C', true);

        $this->pdf->SetFont('Arial', '', 9);

        foreach ($productos as $prod) {
            $prod_id = $prod['producto_id'];
            $nombre = $prod['nombre'];

            $inicial = isset($inventarioInicial[$prod_id]) ? $inventarioInicial[$prod_id] : 0;
            $final = isset($inventarioFinal[$prod_id]) ? $inventarioFinal[$prod_id] : 0;
            $produccion = $this->calcularProduccionTotal($fecha_inicio, $fecha_fin, $prod_id);
            $variacion = $final - $inicial;

            $this->pdf->Cell(50, 7, $this->convertir($nombre), 1, 0, 'L');
            $this->pdf->Cell(35, 7, number_format($inicial, 2) . ' Ton', 1, 0, 'R');
            $this->pdf->Cell(35, 7, number_format($produccion, 2) . ' Ton', 1, 0, 'R');
            $this->pdf->Cell(35, 7, number_format($final, 2) . ' Ton', 1, 0, 'R');

            // Color según variación
            if ($variacion > 0) {
                $this->pdf->SetTextColor(0, 128, 0); // Verde
                $signo = '+';
            } elseif ($variacion < 0) {
                $this->pdf->SetTextColor(255, 0, 0); // Rojo
                $signo = '';
            } else {
                $signo = '';
            }

            $this->pdf->Cell(35, 7, $signo . number_format($variacion, 2) . ' Ton', 1, 1, 'R');
            $this->pdf->SetTextColor(0);
        }

        // Totales
        $this->pdf->SetFont('Arial', 'B', 9);
        $totalInicial = array_sum($inventarioInicial);
        $totalFinal = array_sum($inventarioFinal);
        $totalProduccion = $this->calcularProduccionTotal($fecha_inicio, $fecha_fin, null);
        $totalVariacion = $totalFinal - $totalInicial;

        $this->pdf->Cell(50, 8, 'TOTALES:', 1, 0, 'R');
        $this->pdf->Cell(35, 8, number_format($totalInicial, 2) . ' Ton', 1, 0, 'R');
        $this->pdf->Cell(35, 8, number_format($totalProduccion, 2) . ' Ton', 1, 0, 'R');
        $this->pdf->Cell(35, 8, number_format($totalFinal, 2) . ' Ton', 1, 0, 'R');

        if ($totalVariacion > 0) {
            $this->pdf->SetTextColor(0, 128, 0);
            $signo = '+';
        } elseif ($totalVariacion < 0) {
            $this->pdf->SetTextColor(255, 0, 0);
            $signo = '';
        } else {
            $signo = '';
        }

        $this->pdf->Cell(35, 8, $signo . number_format($totalVariacion, 2) . ' Ton', 1, 1, 'R');
        $this->pdf->SetTextColor(0);
    }
    /**
     * Calcular inventario inicial (antes de la fecha de inicio)
     */

/**
 * Calcular inventario inicial (leer de pl_inventario al inicio del período)
 */
private function calcularInventarioInicial($fecha_inicio, $productos)
{
    $conn = parent::conexion();
    parent::set_names();
    $inventarios = [];

    foreach ($productos as $prod) {
        // Obtener inventario actual y restarle movimientos después de fecha_inicio
        $sql = "
            SELECT 
                i.cantidad_kg - 
                COALESCE((
                    SELECT SUM(cantidad_producida_kg)
                    FROM pl_produccion
                    WHERE producto_id = ?
                    AND fecha >= ?
                    AND activo = 1
                ), 0) as inventario_inicial
            FROM pl_inventario i
            WHERE i.producto_id = ?
        ";

        $stmt = $conn->prepare($sql);
        $stmt->execute([$prod['producto_id'], $fecha_inicio, $prod['producto_id']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $inventarios[$prod['producto_id']] = ($result['inventario_inicial'] ?? 0) / 1000;
    }

    return $inventarios;
}

/**
 * Calcular inventario final (leer de pl_inventario actual)
 */
private function calcularInventarioFinal($fecha_fin, $productos)
{
    $conn = parent::conexion();
    parent::set_names();
    $inventarios = [];

    foreach ($productos as $prod) {
        $sql = "
            SELECT cantidad_kg / 1000 as inventario
            FROM pl_inventario
            WHERE producto_id = ?
        ";

        $stmt = $conn->prepare($sql);
        $stmt->execute([$prod['producto_id']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $inventarios[$prod['producto_id']] = $result['inventario'] ?? 0;
    }

    return $inventarios;
}

/**
 * Calcular producción total en el período
 */
private function calcularProduccionTotal($fecha_inicio, $fecha_fin, $producto_id = null)
{
    $conn = parent::conexion();
    parent::set_names();

    if ($producto_id) {
        $sql = "
            SELECT COALESCE(SUM(cantidad_producida_kg), 0) / 1000 as total
            FROM pl_produccion
            WHERE producto_id = ?
            AND fecha BETWEEN ? AND ?
            AND activo = 1
        ";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$producto_id, $fecha_inicio, $fecha_fin]);
    } else {
        $sql = "
            SELECT COALESCE(SUM(cantidad_producida_kg), 0) / 1000 as total
            FROM pl_produccion
            WHERE fecha BETWEEN ? AND ?
            AND activo = 1
        ";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$fecha_inicio, $fecha_fin]);
    }

    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'] ?? 0;
}
}

// Manejo de solicitudes GET para generar reportes
if (isset($_GET['tipo'])) {
    $reportes = new ReportesPlanta();
    $tipo = $_GET['tipo'];
    $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
    $fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');

    switch ($tipo) {
        case 'movimientos':
            $subtipo = $_GET['subtipo'] ?? null;
            $reportes->generarReporteMovimientos($subtipo, $fecha_inicio, $fecha_fin);
            break;

        case 'produccion':
            $producto_id = $_GET['producto_id'] ?? null;
            $reportes->generarReporteProduccion($fecha_inicio, $fecha_fin, $producto_id);
            break;

        case 'inventario':
            $reportes->generarReporteInventario($fecha_inicio, $fecha_fin);
            break;

        default:
            echo "Tipo de reporte no válido";
    }
}
?>