<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

include "conexion_db.php";

$id_paciente = $_GET["id_paciente"] ?? '';

/* =========================
   ELIMINAR EVOLUCIÓN
========================= */
if (isset($_GET['eliminar_id'])) {
    $id = intval($_GET['eliminar_id']);

    $sql_eliminar = $conexion->query("DELETE FROM historia WHERE id = '$id'");
    if ($sql_eliminar) {
        $_SESSION['mensaje'] = "Evolución eliminada con éxito.";
        header("Location: historiaclinica.php?id_paciente=$id_paciente");
        exit();
    } else {
        echo "Error al eliminar: " . $conexion->error;
    }
}

/* =========================
   DATOS DEL PACIENTE
========================= */
$sqlPaciente = $conexion->query("
    SELECT p.*, o.nombre_obrasocial
    FROM pacientes p
    LEFT JOIN obras_sociales o ON p.obra_social = o.id_obrasocial
    WHERE id_paciente = '$id_paciente'
");

/* =========================
   ORDENAMIENTO EVOLUCIÓN
========================= */
$sort = $_GET['sort'] ?? 'fecha';
$dir  = $_GET['dir'] ?? 'desc';

$allowedSort = [
    'fecha'       => 'fecha',
    'motivo'      => 'motivo',
    'tratamiento' => 'tratamiento'
];

if (isset($allowedSort[$sort])) {
    $orderBy  = $allowedSort[$sort];
    $orderDir = ($dir === 'asc') ? 'ASC' : 'DESC';
} else {
    $orderBy  = 'fecha';
    $orderDir = 'DESC';
}

/* =========================
   PAGINACIÓN EVOLUCIÓN
========================= */
$porPagina = isset($_GET['rows']) && in_array((int)$_GET['rows'], [4, 9])
    ? (int)$_GET['rows']
    : 4;

$sqlCount = "SELECT COUNT(*) AS total FROM historia WHERE id_paciente = '$id_paciente'";
$resCount = $conexion->query($sqlCount);
$totalRegistros = $resCount->fetch_assoc()['total'];

$totalPaginas = max(1, ceil($totalRegistros / $porPagina));

$pagina = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($pagina < 1) $pagina = 1;
if ($pagina > $totalPaginas) $pagina = $totalPaginas;

$offset = ($pagina - 1) * $porPagina;

/* =========================
   EVOLUCIÓN CON PAGINACIÓN
========================= */
$sqlHistoria = $conexion->query("
    SELECT *
    FROM historia
    WHERE id_paciente = '$id_paciente'
    ORDER BY $orderBy $orderDir
    LIMIT $offset, $porPagina
");

/* =========================
   URL BASE
========================= */
$params = $_GET;
unset($params['page']);
$baseURL = "historiaclinica.php?id_paciente=$id_paciente";

/* =========================
   FUNCIONES
========================= */
function buildSortUrl($campo, $currentSort, $currentDir, $params, $baseURL) {
    $p = $params;

    if ($currentSort === $campo) {
        $newDir = ($currentDir === 'asc') ? 'desc' : 'asc';
    } else {
        $newDir = 'asc';
    }

    $p['sort'] = $campo;
    $p['dir']  = $newDir;
    unset($p['page']);

    return $baseURL . '&' . http_build_query($p);
}

function sortArrow($campo, $currentSort, $currentDir) {
    if ($campo !== $currentSort) return '';
    return $currentDir === 'asc' ? ' ▲' : ' ▼';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Evoluciones</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./styles/pacientes.css">
    <link rel="stylesheet" href="./styles/historiaclinica.css">
<style>
.th{
background-color:#9ca3af;
}

.evolution-table thead th{
    position: sticky;
    top: 0;
    background-color: #9ca3af;
    z-index: 2;
}
</style>
</head>
<body>

<header>
    <div class="logo">
        <img src="./assets/img/logo.png" alt="logo">
        <h1>Sistema Esperanza</h1>
    </div>
    <div class="radio">
        <h2 class="radio-title">Radio online InD'Mix</h2>
        <audio src="https://server.laradio.online:58919/live" controls preload></audio>
    </div>
</header>

<nav>
    <a href="main.php">Inicio</a>
    <a href="pacientes.php" style="background-color: #FFB703;">Pacientes</a>
    <a href="reportes.php">Evoluciones</a>
    <a href="citas.php">Citas</a>
    <a href="gestion.php">Gestión</a>
    <a href="logout.php">Cerrar sesión</a>
</nav>

<main>
<section>
<div>
    <a href="pacientes.php">Volver</a>
<h2 style="text-align: center">Paciente</h2>
    <a href="reporte_paciente.php?id_paciente=<?= urlencode($id_paciente) ?>" target="_blank">Generar reporte</a>
</div>





<?php while ($datos = $sqlPaciente->fetch_object()) { ?>
<table class="patient-table">
    <tr><th>Nombre</th><td><?= htmlspecialchars($datos->nombre) ?></td></tr>
    <tr><th>Apellido</th><td><?= htmlspecialchars($datos->apellido) ?></td></tr>
    <tr><th>DNI</th><td><?= htmlspecialchars($datos->dni) ?></td></tr>
    <tr><th>Teléfono</th><td><?= htmlspecialchars($datos->telefono) ?></td></tr>
    <tr><th>Dirección</th><td><?= htmlspecialchars($datos->direccion) ?></td></tr>
    <tr><th>Obra Social</th><td><?= htmlspecialchars($datos->nombre_obrasocial) ?></td></tr>
</table>
<?php } ?>
<div style="display:flex;justify-content: space-between;">
<h2></h2>
<h3 style="text-align:center;">Evoluciones</h3>
<a href="agregar_evolucion.php?id_paciente=<?= urlencode($id_paciente) ?>">Agregar evolución</a>
</div>
<div class="tabla-scroll">
<table class="evolution-table">
<thead>
<tr>
    <th><a class="th" href="<?= buildSortUrl('fecha', $sort, $dir, $_GET, $baseURL) ?>">Fecha<?= sortArrow('fecha', $sort, $dir) ?></a></th>
    <th><a class="th" href="<?= buildSortUrl('motivo', $sort, $dir, $_GET, $baseURL) ?>">Motivo<?= sortArrow('motivo', $sort, $dir) ?></a></th>
    <th><a class="th" href="<?= buildSortUrl('tratamiento', $sort, $dir, $_GET, $baseURL) ?>">Tratamiento<?= sortArrow('tratamiento', $sort, $dir) ?></a></th>
    <th>Acción</th>
</tr>
</thead>
<tbody>

<?php if ($sqlHistoria->num_rows > 0): ?>
<?php while ($historia_clinica = $sqlHistoria->fetch_object()): ?>
<tr>
    <td><?= htmlspecialchars($historia_clinica->fecha) ?></td>
    <td><?= htmlspecialchars($historia_clinica->motivo) ?></td>
    <td><?= htmlspecialchars($historia_clinica->tratamiento) ?></td>
    <td>
        <a 
        href="historiaclinica.php?id_paciente=<?= urlencode($id_paciente) ?>&eliminar_id=<?= $historia_clinica->id ?>"
        class="eliminar-btn"
        onclick="return confirmarEliminacionEvolucion(
            '<?= htmlspecialchars($historia_clinica->fecha, ENT_QUOTES) ?>',
            '<?= htmlspecialchars($historia_clinica->motivo, ENT_QUOTES) ?>'
        )">
        Eliminar
        </a>
    </td>
</tr>
<?php endwhile; ?>
<?php else: ?>
<tr><td colspan="4">No hay registros de evolución.</td></tr>
<?php endif; ?>

</tbody>
</table>
</div>

<!-- PAGINACIÓN -->
<div class="pagination">
<?php
// Reconstruir params sin page para los links
$paramsNoPage = $_GET;
unset($paramsNoPage['page']);
$qsNoPage = http_build_query($paramsNoPage);

// Link anterior
if ($pagina > 1):
    $p = $pagina - 1;
    $qsPrev = $qsNoPage ? "$qsNoPage&page=$p" : "page=$p";
?>
    <a href="<?= htmlspecialchars("$baseURL&$qsPrev") ?>">&laquo; Anterior</a>
<?php endif; ?>

<?php
// Rango dinámico de páginas (igual a pacientes.php)
$inicio = max(1, $pagina - 3);
$fin    = min($totalPaginas, $pagina + 3);

for ($i = $inicio; $i <= $fin; $i++):
    $qPage = $qsNoPage ? "$qsNoPage&page=$i" : "page=$i";
?>
    <?php if ($i == $pagina): ?>
        <span class="active-page"></span>
    <?php else: ?>
        <a href="<?= htmlspecialchars("$baseURL&$qPage") ?>"><?= $i ?></a>
    <?php endif; ?>
<?php endfor; ?>

<?php
// Link siguiente
if ($pagina < $totalPaginas):
    $p = $pagina + 1;
    $qsNext = $qsNoPage ? "$qsNoPage&page=$p" : "page=$p";
?>
    <a href="<?= htmlspecialchars("$baseURL&$qsNext") ?>">Siguiente &raquo;</a>
<?php endif; ?>

<span class="pagination-info">
    Página <?= $pagina ?> de <?= $totalPaginas ?> — Registros: <?= $totalRegistros ?>
</span>

</div>


</section>
</main>

<footer>
    <h4>SGOE - Sistema de Gestión Odontológico Esperanza - Creado por Dilkin, Oscar y De Matos Esteban</h4>
</footer>

<script>
<?php if (isset($_SESSION['mensaje'])) { ?>
alert("<?= $_SESSION['mensaje'] ?>");
<?php unset($_SESSION['mensaje']); } ?>

function confirmarEliminacionEvolucion(fecha, motivo) {
    return confirm(
        "ATENCIÓN\n\nFecha: " + fecha +
        "\nMotivo: " + motivo +
        "\n\n¿Eliminar este registro definitivamente?"
    );
}
</script>

<script>
(function () {
    const alto = window.screen.height;
    let filas = 4;

    if (alto >= 1080) {
        filas = 9;
    } else if (alto >= 720) {
        filas = 4;
    }

    const params = new URLSearchParams(window.location.search);

    if (params.get("rows") != filas) {
        params.set("rows", filas);
        window.location.search = params.toString();
    }
})();
</script>



</body>
</html>