<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./styles/pacientes.css">
    <title>Evoluciones</title>
<style>
    /* ==============================
       CONTENEDOR GENERAL DE FILTROS
    =============================== */
    .filters-container {
        margin-bottom: 20px;
        display: flex;
        flex-direction: column;
        gap: 18px;
        width: 100%;
    }
    /* El formulario ahora es vertical */
    .filters-form {
        display: flex;
        flex-direction: column;
        gap: 15px;
        width: 100%;
    }
    /* ==============================
       FILA 1 y FILA 2
    =============================== */
    .filters-row {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        width: 100%;
        justify-content: space-between; /* <<< ESTA LÍNEA NUEVA */
    }
    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 3px;
    }
    .filter-group label {
        font-size: 0.75rem;
        color: #374151;
    }
    /* Inputs */
    .filter-group input[type="text"],
    .filter-group select {
        padding: 5px 7px;
        width: 100px;
        font-size: 0.85rem;
    }
    /* Inputs de fecha */
    .filter-group input[type="date"] {
        width: 120px;
        padding: 5px 6px;
        font-size: 0.85rem;
    }
    /* Botones */
    .btn-gris {
        padding: 6px 12px;
        background: #9ca3af;
        color: #fff;
        font-size: 0.9rem;
        border-radius: 6px;
        border: none;
        cursor: pointer;
        text-decoration: none;
    }
    .btn-gris:hover {
        background: #4b5563;
    }
    /* PAGINACIÓN */
    .pagination {
        margin-top: 14px;
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        align-items: center;
        font-size: 0.9rem;
    }
    .pagination a,
    .pagination span {
        padding: 4px 8px;
        border-radius: 4px;
        text-decoration: none;
        border: 1px solid #d1d5db;
        color: #111827;
    }
    .pagination a:hover {
        background: #e5e7eb;
    }
    .pagination .active-page {
        background: #0d6efd;
        color: #fff;
        border-color: #0d6efd;
    }
    .pagination-info {
        margin-left: auto;
        font-size: 0.9rem;
        color: #4b5563;
    }
    /* Botón imprimir */
    .print-container {
        display: flex;
        justify-content: flex-end;
        margin-top: 12px;
        margin-bottom: 20px;
    }
    /* Estilo para los encabezados ordenables */
    .sortable {
        background-color: #9ca3af;
color:white;
        text-decoration: none;
        display: block;
    }
    .sortable:hover {
background-color: #6b7280;
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
    <a href="pacientes.php">Pacientes</a>
    <a href="reportes.php" style="background-color:#FFB703;">Evoluciones</a>
    <a href="citas.php">Citas</a>
    <a href="gestion.php">Gestión</a>
    <a href="logout.php">Cerrar sesión</a>
</nav>
<main>
<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
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

/* =======================================
   ORDENAMIENTO (sort + dir) - NUEVO
======================================= */
$sort = $_GET['sort'] ?? '';
$dir  = $_GET['dir'] ?? 'asc';

$allowedSort = [
    'fecha'        => 'h.fecha',
    'apellido'     => 'p.apellido',
    'nombre'       => 'p.nombre',
    'dni'          => 'p.dni',
    'obra_social'  => 'o.nombre_obrasocial',
    'nro_afiliado' => 'p.nro_afiliado',
    'motivo'       => 'h.motivo',
    'tratamiento'  => 'h.tratamiento'
];

if (isset($allowedSort[$sort])) {
    $orderBy   = $allowedSort[$sort];
    $orderDir  = ($dir === 'desc') ? 'DESC' : 'ASC';
} else {
    $sort = $orderBy = '';
    $orderDir = '';
}

/* Construcción del ORDER BY final */
$orderSQL = "";
if ($orderBy !== '') {
    $orderSQL = "ORDER BY $orderBy $orderDir, h.id DESC";
} else {
    $orderSQL = "ORDER BY h.fecha DESC, h.id DESC";
}

/* Función para construir URL de ordenamiento */
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
    $qs = http_build_query($p);
    return $baseURL . ($qs ? "?$qs" : "");
}

/* Flechita de orden */
function sortArrow($campo, $currentSort, $currentDir) {
    if ($campo !== $currentSort) return '';
    return $currentDir === 'asc' ? ' ▲' : ' ▼';
}

/* ==================================== */

$where = [];
function like($campo, $valor, $conexion) {
    if ($valor === "") return "";
    $v = $conexion->real_escape_string($valor);
    return "$campo LIKE '%$v%'";
}
if ($nombre) $where[] = like("p.nombre", $nombre, $conexion);
if ($apellido) $where[] = like("p.apellido", $apellido, $conexion);
if ($dni) $where[] = like("p.dni", $dni, $conexion);
if ($telefono) $where[] = like("p.telefono", $telefono, $conexion);
if ($nro_afiliado) $where[] = like("p.nro_afiliado", $nro_afiliado, $conexion);
if ($obra_social) $where[] = "p.obra_social = ".intval($obra_social);
if ($motivo) $where[] = like("h.motivo", $motivo, $conexion);
if ($tratamiento) $where[] = like("h.tratamiento", $tratamiento, $conexion);
if ($from_date && $to_date)
    $where[] = "h.fecha BETWEEN '$from_date' AND '$to_date'";
else if ($from_date)
    $where[] = "h.fecha >= '$from_date'";
else if ($to_date)
    $where[] = "h.fecha <= '$to_date'";
$whereSQL = $where ? " WHERE ".implode(" AND ", $where) : "";

/* OBRAS SOCIALES */
$obras = [];
$res = $conexion->query("SELECT id_obrasocial, nombre_obrasocial FROM obras_sociales ORDER BY nombre_obrasocial");
while ($r = $res->fetch_assoc()) $obras[] = $r;

/* PAGINACION (50 por página) */

$porPagina = isset($_GET['rows']) && in_array((int)$_GET['rows'], [7, 12])
    ? (int)$_GET['rows']
    : 7;

$sqlCount = "
    SELECT COUNT(*) AS total
    FROM historia h
    INNER JOIN pacientes p ON h.id_paciente = p.id_paciente
    LEFT JOIN obras_sociales o ON p.obra_social = o.id_obrasocial
    $whereSQL
";
$totalRegistros = $conexion->query($sqlCount)->fetch_assoc()["total"];
$totalPaginas = max(1, ceil($totalRegistros / $porPagina));
$pagina = max(1, min(intval($_GET["page"] ?? 1), $totalPaginas));
$offset = ($pagina - 1) * $porPagina;

/* CONSULTA FINAL */
$sql = "
    SELECT h.fecha, p.apellido, p.nombre, p.dni,
           o.nombre_obrasocial AS obra_social,
           p.nro_afiliado, h.motivo, h.tratamiento
    FROM historia h
    INNER JOIN pacientes p ON h.id_paciente = p.id_paciente
    LEFT JOIN obras_sociales o ON p.obra_social = o.id_obrasocial
    $whereSQL
    $orderSQL
    LIMIT $offset, $porPagina
";
$result = $conexion->query($sql);

$params = $_GET;
unset($params["page"]);
$qs = http_build_query($params);
$baseURL = "reportes.php";
?>
<section>
    <!-- ================================
         FILTROS (FILA 1 + FILA 2)
    ================================ -->
    <div class="filters-container">
        <form method="GET" class="filters-form">
            <!-- FILA 1 -->
            <div class="filters-row">
                <div class="filter-group">
                    <label>Nombre</label>
                    <input type="text" name="nombre" value="<?= htmlspecialchars($nombre) ?>">
                </div>
                <div class="filter-group">
                    <label>Apellido</label>
                    <input type="text" name="apellido" value="<?= htmlspecialchars($apellido) ?>">
                </div>
                <div class="filter-group">
                    <label>Documento</label>
                    <input type="text" name="dni" value="<?= htmlspecialchars($dni) ?>">
                </div>
                <div class="filter-group">
                    <label>Teléfono</label>
                    <input type="text" name="telefono" value="<?= htmlspecialchars($telefono) ?>">
                </div>
                <div class="filter-group">
                    <label>Nº Afiliado</label>
                    <input type="text" name="nro_afiliado" value="<?= htmlspecialchars($nro_afiliado) ?>">
                </div>
                <div class="filter-group">
                    <label>Obra Social</label>
                    <select name="obra_social">
                        <option value="">Todas</option>
                        <?php foreach ($obras as $o): ?>
                            <option value="<?= $o["id_obrasocial"] ?>"
                                <?= $obra_social == $o["id_obrasocial"] ? "selected" : "" ?>>
                                <?= htmlspecialchars($o["nombre_obrasocial"]) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Motivo</label>
                    <input type="text" name="motivo" value="<?= htmlspecialchars($motivo) ?>">
                </div>
                <div class="filter-group">
                    <label>Tratamiento</label>
                    <input type="text" name="tratamiento" value="<?= htmlspecialchars($tratamiento) ?>">
                </div>
            </div> <!-- FIN FILA 1 -->
            <!-- FILA 2 -->
            <div class="filters-row">
                <div class="filter-group">
                    <label>Desde</label>
                    <input type="date" name="from_date" value="<?= htmlspecialchars($from_date) ?>">
                </div>
                <div class="filter-group">
                    <label>Hasta</label>
                    <input type="date" name="to_date" value="<?= htmlspecialchars($to_date) ?>">
                </div>
                <!-- Botones -->
                <div class="filter-group" style="margin-top:18px;">
                    <button type="submit" class="btn-gris">Buscar</button>
                    <a href="reportes.php" class="btn-gris">Limpiar</a>
                </div>
            </div> <!-- FIN FILA 2 -->
        </form>
    </div> <!-- FIN CONTENEDOR FILTROS -->

    <!-- ================================
         TABLA DE RESULTADOS         ================================ -->
    <table id="reportesTable">
        <thead>
            <tr>
                <th>
                    <a class="sortable" href="<?= htmlspecialchars(buildSortUrl('fecha', $sort, $dir, $params, $baseURL)) ?>">
                        Fecha<?= sortArrow('fecha', $sort, $dir) ?>
                    </a>
                </th>
                <th>
                    <a class="sortable" href="<?= htmlspecialchars(buildSortUrl('apellido', $sort, $dir, $params, $baseURL)) ?>">
                        Apellido<?= sortArrow('apellido', $sort, $dir) ?>
                    </a>
                </th>
                <th>
                    <a class="sortable" href="<?= htmlspecialchars(buildSortUrl('nombre', $sort, $dir, $params, $baseURL)) ?>">
                        Nombre<?= sortArrow('nombre', $sort, $dir) ?>
                    </a>
                </th>
                <th>
                    <a class="sortable" href="<?= htmlspecialchars(buildSortUrl('dni', $sort, $dir, $params, $baseURL)) ?>">
                        DNI<?= sortArrow('dni', $sort, $dir) ?>
                    </a>
                </th>
                <th>
                    <a class="sortable" href="<?= htmlspecialchars(buildSortUrl('obra_social', $sort, $dir, $params, $baseURL)) ?>">
                        Obra Social<?= sortArrow('obra_social', $sort, $dir) ?>
                    </a>
                </th>
                <th>
                    <a class="sortable" href="<?= htmlspecialchars(buildSortUrl('nro_afiliado', $sort, $dir, $params, $baseURL)) ?>">
                        Nº Afiliado<?= sortArrow('nro_afiliado', $sort, $dir) ?>
                    </a>
                </th>
                <th>
                    <a class="sortable" href="<?= htmlspecialchars(buildSortUrl('motivo', $sort, $dir, $params, $baseURL)) ?>">
                        Motivo<?= sortArrow('motivo', $sort, $dir) ?>
                    </a>
                </th>
                <th>
                    <a class="sortable" href="<?= htmlspecialchars(buildSortUrl('tratamiento', $sort, $dir, $params, $baseURL)) ?>">
                        Tratamiento<?= sortArrow('tratamiento', $sort, $dir) ?>
                    </a>
                </th>
            </tr>
        </thead>
        <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($r = $result->fetch_assoc()): ?>
            <tr>
                <td><?= date("d-m-Y", strtotime($r["fecha"])) ?></td>
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
            <tr><td colspan="8">No se encontraron resultados.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

    <!-- ================================
         PAGINACIÓN
    ================================ -->
    <div class="pagination">
        <!-- PREVIOUS -->
        <?php if ($pagina > 1):
            $p = $pagina - 1;
            $qPrev = $qs ? "$qs&page=$p" : "page=$p";
        ?>
            <a href="<?= "$baseURL?$qPrev" ?>">&laquo; Anterior</a>
        <?php endif; ?>

        <?php
        $inicio = max(1, $pagina - 3);
        $fin = min($totalPaginas, $pagina + 3);
        for ($i = $inicio; $i <= $fin; $i++):
            $q = $qs ? "$qs&page=$i" : "page=$i";
        ?>
            <?php if ($i == $pagina): ?>
                <span class="active-page"><?= $i ?></span>
            <?php else: ?>
                <a href="<?= "$baseURL?$q" ?>"><?= $i ?></a>
            <?php endif; ?>
        <?php endfor; ?>

        <!-- NEXT -->
        <?php if ($pagina < $totalPaginas):
            $p = $pagina + 1;
            $qNext = $qs ? "$qs&page=$p" : "page=$p";
        ?>
            <a href="<?= "$baseURL?$qNext" ?>">Siguiente &raquo;</a>
        <?php endif; ?>

        <!-- INFO -->
        <span class="pagination-info">
            Página <?= $pagina ?> de <?= $totalPaginas ?> — Registros: <?= $totalRegistros ?>
        </span>
<div >
        <button class="btn-gris" onclick="imprimirTodo()">Generar reporte</button>
    </div>
    </div>    
</section>

<!-- =====================================
     SCRIPT IMPRIMIR
====================================== -->
<script>
function imprimirTodo() {
    const params = new URLSearchParams(window.location.search);
    window.open("reporte_imprimir.php?" + params.toString(), "_blank");
}
</script>
</main>
<footer>
    <h4>SGOE - Sistema de Gestión Odontológico Esperanza - Creado por Dilkin, Oscar y De Matos Esteban</h4>
</footer>

<script>
(function () {
    const alto = window.screen.height;
    let filas = 7;

    if (alto >= 1080) {
        filas = 12;
    } else if (alto >= 720) {
        filas = 7;
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