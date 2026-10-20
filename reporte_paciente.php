<?php
include("conexion_db.php");

$id_paciente = $_GET['id_paciente'] ?? '';

if ($id_paciente == '') {
    die("Paciente no especificado.");
}

/* =========================
   DATOS DEL PACIENTE
========================= */
$sqlPaciente = $conexion->query("
    SELECT p.*, o.nombre_obrasocial
    FROM pacientes p
    LEFT JOIN obras_sociales o ON p.obra_social = o.id_obrasocial
    WHERE p.id_paciente = '$id_paciente'
");

$paciente = $sqlPaciente->fetch_assoc();

if (!$paciente) {
    die("Paciente no encontrado.");
}

/* =========================
   EVOLUCIONES DEL PACIENTE
========================= */
$sqlHistoria = $conexion->query("
    SELECT fecha, motivo, tratamiento
    FROM historia
    WHERE id_paciente = '$id_paciente'
    ORDER BY fecha DESC
");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reporte del Paciente</title>

<style>
body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
}

/* =========================
   HEADER
========================= */
header {
    display: flex;
    align-items: center;
    padding: 15px 25px;
    border-bottom: 2px solid #9ca3af;
}

header img {
    height: 70px;
    margin-right: 15px;
}

header h1 {
    font-size: 22px;
    margin: 0;
}

/* =========================
   CONTENIDO
========================= */
main {
    padding: 25px;
}

h2 {
    text-align: center;
    margin-top: 0;
}

/* =========================
   TABLAS
========================= */
table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 25px;
}

th, td {
    border: 1px solid #ccc;
    padding: 8px;
    font-size: 13px;
}

th {
    background-color: #9ca3af;
    color: white;
    text-align: left;
}

tr:nth-child(even) {
    background-color: #f3f4f6;
}

/* =========================
   IMPRESIÓN
========================= */
@media print {
    body {
        margin: 0;
    }
}
</style>
</head>

<body onload="window.print()">

<!-- =========================
     HEADER
========================= -->
<header>
    <img src="./assets/img/logo.png" alt="Logo Consultorio">
    <h1>Sistema de Gestión Odontológico Esperanza</h1>
</header>

<main>

<h2>Reporte del Paciente</h2>

<!-- =========================
     DATOS DEL PACIENTE
========================= -->
<table>
    <tr>
        <th>Nombre</th>
        <td><?= htmlspecialchars($paciente['nombre']) ?></td>
        <th>Apellido</th>
        <td><?= htmlspecialchars($paciente['apellido']) ?></td>
    </tr>
    <tr>
        <th>DNI</th>
        <td><?= htmlspecialchars($paciente['dni']) ?></td>
        <th>Teléfono</th>
        <td><?= htmlspecialchars($paciente['telefono']) ?></td>
    </tr>
    <tr>
        <th>Dirección</th>
        <td><?= htmlspecialchars($paciente['direccion']) ?></td>
        <th>Obra Social</th>
        <td><?= htmlspecialchars($paciente['nombre_obrasocial']) ?></td>
    </tr>
    <tr>
        <th>Nº Afiliado</th>
        <td colspan="3"><?= htmlspecialchars($paciente['nro_afiliado']) ?></td>
    </tr>
</table>

<!-- =========================
     EVOLUCIONES
========================= -->
<h2>Evoluciones</h2>

<table>
    <thead>
        <tr>
            <th style="width:15%">Fecha</th>
            <th style="width:35%">Motivo</th>
            <th style="width:50%">Tratamiento</th>
        </tr>
    </thead>
    <tbody>
    <?php if ($sqlHistoria->num_rows > 0): ?>
        <?php while ($e = $sqlHistoria->fetch_assoc()): ?>
        <tr>
            <td><?= date("d/m/Y", strtotime($e['fecha'])) ?></td>
            <td><?= htmlspecialchars($e['motivo']) ?></td>
            <td><?= htmlspecialchars($e['tratamiento']) ?></td>
        </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="3">No hay evoluciones registradas.</td>
        </tr>
    <?php endif; ?>
    </tbody>
</table>

</main>

</body>
</html>