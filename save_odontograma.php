<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}
include "conexion_db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: pacientes.php");
    exit();
}

$id_paciente = intval($_POST['id_paciente'] ?? 0);
$datos_json = $_POST['datos_json'] ?? '';
$observaciones = $_POST['observaciones_generales'] ?? '';

if (!$id_paciente || $datos_json === '') {
    $_SESSION['mensaje'] = "Datos incompletos para guardar odontograma.";
    header("Location: odontograma.php?id_paciente=" . urlencode($id_paciente));
    exit();
}

$parsed = json_decode($datos_json, true);
if (!is_array($parsed)) {
    $_SESSION['mensaje'] = "Formato inválido de datos.";
    header("Location: odontograma.php?id_paciente=" . urlencode($id_paciente));
    exit();
}

$conexion->begin_transaction();

try {
    $stmt = $conexion->prepare("UPDATE odontogramas SET activo = 0 WHERE id_paciente = ?");
    $stmt->bind_param("i", $id_paciente);
    $stmt->execute();
    $stmt->close();

    $fecha = date('Y-m-d H:i:s');
    $stmt = $conexion->prepare("INSERT INTO odontogramas (id_paciente, fecha_creacion, observaciones_generales, activo) VALUES (?, ?, ?, 1)");
    $stmt->bind_param("iss", $id_paciente, $fecha, $observaciones);
    $stmt->execute();
    $id_odontograma = $stmt->insert_id;
    $stmt->close();

    $stmt = $conexion->prepare("INSERT INTO detalle_odontograma (id_odontograma, id_pieza, cara_superior, cara_inferior, cara_izquierda, cara_derecha, cara_central, observacion_pieza, fecha_modificacion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    foreach ($parsed as $row) {
        $id_pieza = intval($row['id_pieza'] ?? 0);
        $cs = $row['cara_superior'] ?? '';
        $ci = $row['cara_inferior'] ?? '';
        $cl = $row['cara_izquierda'] ?? '';
        $cd = $row['cara_derecha'] ?? '';
        $cc = $row['cara_central'] ?? '';
        $obs = $row['observacion_pieza'] ?? '';
        $fecha_mod = date('Y-m-d H:i:s');

        $stmt->bind_param("iisssssss", $id_odontograma, $id_pieza, $cs, $ci, $cl, $cd, $cc, $obs, $fecha_mod);
        $stmt->execute();
    }
    $stmt->close();

    $conexion->commit();
    $_SESSION['mensaje'] = "Odontograma guardado correctamente.";
    header("Location: odontograma.php?id_paciente=" . urlencode($id_paciente));
    exit();

} catch (Exception $e) {
    $conexion->rollback();
    $_SESSION['mensaje'] = "Error al guardar odontograma: " . $e->getMessage();
    header("Location: odontograma.php?id_paciente=" . urlencode($id_paciente));
    exit();
}