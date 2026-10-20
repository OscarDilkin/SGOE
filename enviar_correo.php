<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php'; 

include("conexion_db.php"); // ⚠️ Debe existir $conexion aquí

// Obtener parámetros GET
$id_solicitud  = isset($_GET['id_solicitud']) ? htmlspecialchars($_GET['id_solicitud']) : '';
$fecha_citas   = isset($_GET['fecha_citas']) ? htmlspecialchars($_GET['fecha_citas']) : '';
$nombre_paciente = isset($_GET['nombre']) ? htmlspecialchars($_GET['nombre']) : '';
$email_paciente  = isset($_GET['correo']) ? htmlspecialchars($_GET['correo']) : '';
$fecha           = isset($_GET['fecha']) ? htmlspecialchars($_GET['fecha']) : '';
$hora            = isset($_GET['hora']) ? htmlspecialchars($_GET['hora']) : '';   // ✅ AHORA TOMAMOS LA HORA DIRECTAMENTE
$motivo          = isset($_GET['motivo']) ? htmlspecialchars($_GET['motivo']) : '';

// 🔎 VALIDACIÓN MÍNIMA
if (empty($email_paciente) || empty($nombre_paciente) || empty($fecha) || empty($hora)) {
    echo "<script>
        alert('Error: Faltan datos para enviar el correo.');
        window.location.href = 'solicitudes_citas.php?fecha_citas=" . urlencode($fecha_citas) . "';
    </script>";
    exit();
}

// Validar email
if (!filter_var($email_paciente, FILTER_VALIDATE_EMAIL)) {
    echo "<script>
        alert('Error: El correo del paciente no es válido.');
        window.location.href = 'solicitudes_citas.php?fecha_citas=" . urlencode($fecha_citas) . "';
    </script>";
    exit();
}

// Configurar PHPMailer
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'c2821234.ferozo.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'clinica-esperanza@dilkin-pcell.site';
    $mail->Password = 'Estebanquito2025ISTU*';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;
    $mail->CharSet = 'UTF-8';

    $mail->setFrom('clinica-esperanza@dilkin-pcell.site', 'Consultorio Esperanza');
    $mail->addAddress($email_paciente, $nombre_paciente);
    $mail->addReplyTo('clinica-esperanza@dilkin-pcell.site', 'Información');
    $mail->isHTML(false);

    $mail->Subject = 'Confirmación de Cita Odontológica';
    $mail->Body = "Hola $nombre_paciente,\n\nTu cita odontológica ha sido confirmada.\n\nDetalles:\nFecha: $fecha\nHora: $hora\nMotivo: $motivo\n\nMuchas gracias.\nConsultorio Esperanza";

    $mail->send();

    echo "<script>
        alert('Correo de confirmación enviado con éxito.');
        window.location.href = 'solicitudes_citas.php?fecha_citas=" . urlencode($fecha_citas) . "';
    </script>";

} catch (Exception $e) {
    echo "<script>
        alert('Error al enviar el correo: " . addslashes($mail->ErrorInfo) . "');
        window.location.href = 'solicitudes_citas.php?fecha_citas=" . urlencode($fecha_citas) . "';
    </script>";
}
?>