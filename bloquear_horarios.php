<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include("conexion_db.php");
include("conexion.php");

if (!isset($conexion) || !$conexion instanceof mysqli) {
    $conexion = new mysqli("localhost", "c2821234_consult", "Estebanquito2025ISTU", "c2821234_consult");
    if ($conexion->connect_error) {
        die("Conexión fallida: " . $conexion->connect_error);
    }
}

/* ================= ELIMINAR BLOQUEO ================= */
if (isset($_GET['eliminar'])) {
    $fecha = $_GET['fecha'];
    $desde = $_GET['desde'] ?? '';
    $hasta = $_GET['hasta'] ?? '';
    $id    = $_GET['id'] ?? '';

    if ($desde && $hasta) {
        $conexion->query("
            DELETE b FROM bloqueos_horarios b
            INNER JOIN horarios h ON b.id_horario = h.id
            WHERE b.fecha = '$fecha'
            AND h.hora BETWEEN '$desde' AND '$hasta'
        ");
    } elseif ($id) {
        $conexion->query("DELETE FROM bloqueos_horarios WHERE id_bloqueo = '$id'");
    } else {
        $conexion->query("DELETE FROM bloqueos_horarios WHERE fecha = '$fecha' AND id_horario IS NULL");
    }

    echo "<script>alert('Bloqueo eliminado correctamente'); window.location='bloquear_horarios.php';</script>";
    exit();
}

/* ================= NUEVO BLOQUEO ================= */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['deshabilitar'])) {

    $opcion = trim($_POST['opcion']);
    $fecha  = trim($_POST['fecha']);
    $motivo = trim($_POST['motivo']);

    if ($opcion == "todo_dia") {
        $conexion->query("INSERT INTO bloqueos_horarios (fecha, motivo) VALUES ('$fecha', '$motivo')");
    }

    elseif ($opcion == "rango_horario") {
        $desde = $_POST['desde'];
        $hasta = $_POST['hasta'];

        $result = $conexion->query("
            SELECT id FROM horarios
            WHERE hora BETWEEN '$desde' AND '$hasta'
        ");

        while ($row = $result->fetch_assoc()) {
            $conexion->query("
                INSERT INTO bloqueos_horarios (fecha, id_horario, motivo)
                VALUES ('$fecha', '{$row['id']}', '$motivo')
            ");
        }
    }

    elseif ($opcion == "horario_especifico") {
        $id_h = $_POST['id_horario'];
        $conexion->query("
            INSERT INTO bloqueos_horarios (fecha, id_horario, motivo)
            VALUES ('$fecha', '$id_h', '$motivo')
        ");
    }

    echo "<script>alert('Bloqueo aplicado'); window.location='bloquear_horarios.php';</script>";
}

/* ================= PAGINACIÓN ================= */
$porPagina = 5;

$resCount = $conexion->query("
    SELECT COUNT(*) AS total FROM (
        SELECT fecha
        FROM bloqueos_horarios
        GROUP BY fecha, motivo, (CASE WHEN id_horario IS NULL THEN 1 ELSE 0 END)
    ) t
");

$totalRegistros = (int)$resCount->fetch_assoc()['total'];
$totalPaginas = max(1, ceil($totalRegistros / $porPagina));

$pagina = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($pagina < 1) $pagina = 1;
if ($pagina > $totalPaginas) $pagina = $totalPaginas;

$offset = ($pagina - 1) * $porPagina;
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="./styles/pacientes.css">
<link rel="stylesheet" href="./styles/citas.css">
<title>Bloquear Horarios</title>

<style>
.columna-izquierda{
    max-width:600px;
    margin:40px auto;
    background:white;
    padding:20px;
    border-radius:12px;
    box-shadow:0 0 10px rgba(0,0,0,0.08);
}
.btn-volver, .btn-bloquear {
    background: #6c757d;
    color: white;
    border: none;
    padding: 10px 22px;
    border-radius: 22px;
    font-size: 15px;
    cursor: pointer;
    transition: background 0.2s ease;
}

.btn-volver:hover, .btn-bloquear:hover {
    background: #5a6268;
}
.form-select,.form-input{
    width:100%;
    padding:12px;
    border:1px solid #ccc;
    border-radius:6px;
    margin-bottom:12px;
text-transform: uppercase;
}
.rango-flex{
    display:flex;
    gap:10px;
}
.tabla-container{max-height:400px;overflow-y:auto;margin-top:20px}
.tabla-bloqueos{width:100%;border-collapse:collapse}
.tabla-bloqueos th{
    background:#9ca3af;
    padding:10px;
    color:white;
    border: 1px solid #6b7280;
    position: sticky;
    top: 0;
    z-index: 5;
}
.tabla-bloqueos td{padding:8px;border:1px solid #ddd;text-align:center}
.btn-eliminar{background:#c1121f;color:white;padding:6px 12px;border-radius:4px;text-decoration:none}

.pagination {
    margin-top: 16px;
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    justify-content: center;
}

.pagination a, .pagination span {
    padding: 4px 8px;
    border-radius: 4px;
    border: 1px solid #9ca3af;
    background:#9ca3af;
    color:white;
    text-decoration:none;
    font-size:13px;
}

.active-page {
    background:#0d6efd;
}

</style>
</head>

<body>

<header>
<div class="logo">
<img src="./assets/img/logo.png">
<h1>Sistema Esperanza</h1>
</div>
</header>

<nav>
<a href="main.php">Inicio</a>
<a href="pacientes.php">Pacientes</a>
<a href="reportes.php">Evoluciones</a>
<a href="citas.php">Citas</a>
<a href="gestion.php" style="background:#FFB703">Gestión</a>
<a href="logout.php">Cerrar sesión</a>
</nav>

<main>

<div><a href="gestion.php">Volver</a></div>

<div class="columna-izquierda" style="margin-top:-20px">

<h3 style="text-align:center;margin-top:-10px">Bloquear Horarios/Días</h3>

<form method="POST">

<select name="opcion" id="opcion" class="form-select" onchange="mostrarCampos()" required>
<option value="">Seleccione una opción</option>
<option value="todo_dia">Todo el día</option>
<option value="rango_horario">Rango horario</option>
<option value="horario_especifico">Horario específico</option>
</select>

<input type="date" name="fecha" class="form-input" required>
<input type="text" name="motivo" class="form-input" placeholder="Motivo del bloqueo (opcional)" oninput="convertirMayusculas(this)">

<div id="rango-container" style="display:none;">
<div class="rango-flex">
<select name="desde" class="form-select">
<?php $r=$conexion->query("SELECT hora FROM horarios where disponible = 1 ORDER BY hora");
while($h=$r->fetch_assoc()) echo "<option>{$h['hora']}</option>"; ?>
</select>

<select name="hasta" class="form-select">
<?php $r=$conexion->query("SELECT hora FROM horarios where disponible = 1 ORDER BY hora");
while($h=$r->fetch_assoc()) echo "<option>{$h['hora']}</option>"; ?>
</select>
</div>
</div>

<div id="horario-container" style="display:none;">
<select name="id_horario" class="form-select">
<?php
$r=$conexion->query("SELECT id,hora FROM horarios where disponible = 1 ORDER BY hora");
while($h=$r->fetch_assoc())
echo "<option value='{$h['id']}'>{$h['hora']}</option>";
?>
</select>
</div>

<button type="submit" name="deshabilitar" class="btn-bloquear">Bloquear</button>

</form>

</div>

<h3 style="text-align:center;margin-top:-20px">Bloqueos registrados</h3>

<div class="tabla-container">
<table class="tabla-bloqueos">
<tr>
<th>Fecha</th>
<th>Horario/Rango</th>
<th>Motivo</th>
<th>Acción</th>
</tr>

<?php
$result=$conexion->query("
SELECT fecha,motivo,
MIN(h.hora) AS desde,
MAX(h.hora) AS hasta,
COUNT(b.id_horario) AS cant,
MAX(b.id_bloqueo) AS id
FROM bloqueos_horarios b
LEFT JOIN horarios h ON h.id=b.id_horario
GROUP BY fecha,motivo,(CASE WHEN b.id_horario IS NULL THEN 1 ELSE 0 END)
ORDER BY fecha DESC
LIMIT $offset, $porPagina
");

while($row=$result->fetch_assoc()){
if($row['desde']==null) $texto="Día completo";
elseif($row['cant']==1) $texto=$row['desde'];
else $texto=$row['desde']." – ".$row['hasta'];

$fechaFormateada = date("d-m-Y", strtotime($row['fecha']));

echo "
<tr>
<td>$fechaFormateada</td>
<td>$texto</td>
<td>{$row['motivo']}</td>
<td>
<a class='btn-eliminar'
href='bloquear_horarios.php?eliminar=1&fecha={$row['fecha']}&desde={$row['desde']}&hasta={$row['hasta']}&id={$row['id']}'
onclick='return confirm(\"¿Eliminar bloqueo?\")'>
Eliminar
</a>
</td>
</tr>";
}
?>
</table>
</div>

<!-- PAGINACIÓN -->
<div class="pagination">
<?php if ($pagina > 1): ?>
    <a href="bloquear_horarios.php?page=<?= $pagina-1 ?>">&laquo; Anterior</a>
<?php endif; ?>

<?php for ($i=1; $i<=$totalPaginas; $i++): ?>
    <?php if ($i == $pagina): ?>
        <span class="active-page"><?= $i ?></span>
    <?php else: ?>
        <a href="bloquear_horarios.php?page=<?= $i ?>"><?= $i ?></a>
    <?php endif; ?>
<?php endfor; ?>

<?php if ($pagina < $totalPaginas): ?>
    <a href="bloquear_horarios.php?page=<?= $pagina+1 ?>">Siguiente &raquo;</a>
<?php endif; ?>
</div>

</main>

<script>
function mostrarCampos(){
    let op=document.getElementById("opcion").value;
    document.getElementById("rango-container").style.display="none";
    document.getElementById("horario-container").style.display="none";

    if(op==="rango_horario"){
        document.getElementById("rango-container").style.display="block";
    }
    if(op==="horario_especifico"){
        document.getElementById("horario-container").style.display="block";
    }
}
function convertirMayusculas(input) {
    input.value = input.value.toUpperCase();
}
</script>

<footer>
<h4>SGOE - Sistema de Gestión Odontológico Esperanza - Creado por Dilkin, Oscar y De Matos Esteban</h4>
</footer>

<script>
(function () {
    const contenedor = document.querySelector('.tabla-container');
    if (!contenedor) return;

    const altoPantalla = window.screen.height;
    let altoTabla;

    if (altoPantalla >= 1080) {
        altoTabla = 400;   // pantallas grandes
    } else {
        altoTabla = 200;   // pantallas chicas (720 aprox)
    }

    contenedor.style.height = altoTabla + "px";
})();
</script>

</body>
</html>