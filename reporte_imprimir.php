<?php
include("conexion_db.php");

/* ===============================
   LECTURA DE FILTROS
=============================== */
$campos = [
    "nombre", "apellido", "dni", "telefono",
    "nro_afiliado", "obra_social",
    "motivo", "tratamiento",
    "from_date", "to_date"
];
foreach ($campos as $c) {
    $$c = trim($_GET[$c] ?? "");
}

/* ===============================
   WHERE DINÁMICO
=============================== */
$where = [];
function like($campo, $valor, $conexion) {
    if ($valor === "") return "";
    $v = $conexion->real_escape_string($valor);
    return "$campo LIKE '%$v%'";
}

if ($nombre)        $where[] = like("p.nombre", $nombre, $conexion);
if ($apellido)      $where[] = like("p.apellido", $apellido, $conexion);
if ($dni)           $where[] = like("p.dni", $dni, $conexion);
if ($telefono)      $where[] = like("p.telefono", $telefono, $conexion);
if ($nro_afiliado)  $where[] = like("p.nro_afiliado", $nro_afiliado, $conexion);
if ($obra_social)   $where[] = "p.obra_social = ".intval($obra_social);
if ($motivo)        $where[] = like("h.motivo", $motivo, $conexion);
if ($tratamiento)   $where[] = like("h.tratamiento", $tratamiento, $conexion);

if ($from_date && $to_date)
    $where[] = "h.fecha BETWEEN '$from_date' AND '$to_date'";
else if ($from_date)
    $where[] = "h.fecha >= '$from_date'";
else if ($to_date)
    $where[] = "h.fecha <= '$to_date'";

$whereSQL = $where ? " WHERE ".implode(" AND ", $where) : "";

/* ===============================
   CONSULTA
=============================== */
$sql = "
    SELECT h.fecha, p.apellido, p.nombre, p.dni,
           o.nombre_obrasocial AS obra_social,
           p.nro_afiliado, h.motivo, h.tratamiento
    FROM historia h
    INNER JOIN pacientes p ON h.id_paciente = p.id_paciente
    LEFT JOIN obras_sociales o ON p.obra_social = o.id_obrasocial
    $whereSQL
    ORDER BY h.fecha DESC
";
$result = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reporte de Evoluciones</title>

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
   TABLAS (MISMO ESTILO QUE reporte_paciente.php)
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

<h2>Reporte de Evoluciones</h2>

<table>
<thead>
<tr>
    <th style="width:10%">Fecha</th>
    <th style="width:15%">Apellido</th>
    <th style="width:15%">Nombre</th>
    <th style="width:10%">DNI</th>
    <th style="width:15%">Obra Social</th>
    <th style="width:10%">Nº Afiliado</th>
    <th style="width:12%">Motivo</th>
    <th style="width:13%">Tratamiento</th>
</tr>
</thead>
<tbody>

<?php if ($result->num_rows > 0): ?>
    <?php while ($r = $result->fetch_assoc()): ?>
    <tr>
        <td><?= date("d/m/Y", strtotime($r["fecha"])) ?></td>
        <td><?= htmlspecialchars($r["apellido"]) ?></td>
        <td><?= htmlspecialchars($r["nombre"]) ?></td>
        <td><?= htmlspecialchars($r["dni"]) ?></td>
        <td><?= htmlspecialchars($r["obra_social"]) ?></td>
        <td><?= htmlspecialchars($r["nro_afiliado"]) ?></td>
        <td><?= htmlspecialchars($r["motivo"]) ?></td>
        <td><?= htmlspecialchars($r["tratamiento"]) ?></td>
    </tr>
    <?php endwhile; ?>
<?php else: ?>
    <tr>
        <td colspan="8">No se encontraron registros.</td>
    </tr>
<?php endif; ?>

</tbody>
</table>

</main>

</body>
</html>