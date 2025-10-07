<?php
// utils/generar_reporte.php

// Cargar PHPMailer y nuestro nuevo Helper de PDF
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../helpers/FinanzasReportSender.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// --- VALIDACIÓN DE PARÁMETROS ---
$tipo = $_GET['tipo'] ?? null;
$accion = $_GET['accion'] ?? 'descargar';
$fecha_inicio = $_GET['fecha_inicio'] ?? null;
$fecha_fin = $_GET['fecha_fin'] ?? null;
$email_destino = $_GET['email'] ?? null;

if (!$tipo || !in_array($tipo, ['gasto', 'ingreso'])) {
    die("Tipo de reporte no válido.");
}

// --- INSTANCIAR EL HELPER ---
$reportSender = new FinanzasReportSender();

// --- EJECUTAR ACCIÓN ---
if ($accion === 'descargar') {
    // El 'D' en FPDF fuerza la descarga en el navegador.
    $reportSender->generarReportePDF($tipo, $fecha_inicio, $fecha_fin, 'D');

} elseif ($accion === 'enviar') {
    if (empty($email_destino)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'El correo es requerido.']);
        exit();
    }
    
    // El 'S' devuelve el contenido del PDF como un string.
    $pdfContent = $reportSender->generarReportePDF($tipo, $fecha_inicio, $fecha_fin, 'S');
    
    // El resto del código es la lógica de envío de correo que ya conoces.
    $tituloReporte = ($tipo === 'gasto' ? 'Reporte de Gastos' : 'Reporte de Ingresos');
    $nombreArchivo = str_replace(' ', '_', $tituloReporte) . '_' . date('Ymd') . '.pdf';
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.hostinger.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'reportes@solupatchtienda.smartouch.me';
        $mail->Password = '@2GJ2jHlA'; // <-- TU CONTRASEÑA
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        $mail->setFrom('reportes@solupatchtienda.smartouch.me', 'Sistema Automático Solupatch Finanzas');
        $mail->addAddress($email_destino);
        $mail->addStringAttachment($pdfContent, $nombreArchivo);

        $mail->isHTML(true);
        $mail->Subject = $tituloReporte . ' - ' . date('d/m/Y');
        $mail->Body    = "Hola,<br><br>Se ha generado el reporte que solicitaste.<br><br>Saludos,<br><b>Sistema de Finanzas Solupatch</b>";
        
        $mail->send();
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Correo enviado correctamente']);

    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => "El correo no pudo ser enviado. Mailer Error: {$mail->ErrorInfo}"]);
    }
}