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
require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php'; // Ajusta la ruta según tu estructura

// ✅ Obtener parámetros GET (hora viene directamente desde solicitudes_citas.php)
$nombre_paciente = isset($_GET['nombre']) ? htmlspecialchars($_GET['nombre']) : '';
$email_paciente  = isset($_GET['correo']) ? htmlspecialchars($_GET['correo']) : '';
$fecha           = isset($_GET['fecha']) ? htmlspecialchars($_GET['fecha']) : '';
$hora            = isset($_GET['hora']) ? htmlspecialchars($_GET['hora']) : '';

// ✅ Validación mínima
if (empty($email_paciente) || empty($nombre_paciente) || empty($fecha) || empty($hora)) {
    echo "<script>
        alert('Error: Faltan datos para enviar el correo.');
        window.location.href = 'solicitudes_citas.php';
    </script>";
    exit();
}

// ✅ Validar email
if (!filter_var($email_paciente, FILTER_VALIDATE_EMAIL)) {
    echo "<script>
        alert('Error: El correo del paciente no es válido.');
        window.location.href = 'solicitudes_citas.php';
    </script>";
    exit();
}

// ✅ Configurar y enviar correo
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

    $mail->Subject = 'Aviso de Reprogramación de Cita Odontológica';
    $mail->Body = "Hola $nombre_paciente,\n\n"
                . "Lamentamos informarle que no pudimos confirmar su cita en la siguiente fecha:\n"
                . "📅 Fecha: $fecha\n"
                . "🕒 Hora: $hora\n\n"
                . "Por favor, ingrese al siguiente enlace para solicitar un nuevo turno:\n"
                . "👉 https://dilkin-pcell.site/paginaDental.php#pacientes\n\n"
                . "Gracias por su comprensión.\n"
                . "Atentamente,\n"
                . "Consultorio Odontológico Esperanza";

    $mail->send();

    echo "<script>
        alert('Correo de reprogramación enviado con éxito.');
        window.location.href = 'solicitudes_citas.php';
    </script>";

} catch (Exception $e) {
    echo "<script>
        alert('Error al enviar el correo: " . addslashes($mail->ErrorInfo) . "');
        window.location.href = 'solicitudes_citas.php';
    </script>";
}
?>