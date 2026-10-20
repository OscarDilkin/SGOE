<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es" style="height:100%;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./styles/pacientes.css">
    <title>Pacientes</title>

    <style>
        /* Contenedor superior */
        .barra-superior {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .barra-superior form {
            display: flex;
            gap: 8px;
        }

        .barra-superior input[type="text"] {
            padding: 6px 10px;
            width: 300px;
        }

        .btn-buscar {
            padding: 8px 14px;
            font-size: 15px;
            font-weight: 600;
            background: #6b7280;
            color: #fff;
            border-radius: 6px;
            border: none;
            cursor: pointer;
        }

        .btn-buscar:hover {
            background: #4b5563;
        }

        .boton-gris {
            padding: 8px 14px;
            font-size: 15px;
            font-weight: 600;
            background: #6b7280;
            color: #fff;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            text-decoration: none;
        }

        .boton-gris:hover {
            background: #4b5563;
        }

        .boton-agregar {
            padding: 8px 14px;
            background: #0d6efd;
            color: #fff;
            font-weight: 600;
            border-radius: 6px;
            text-decoration: none;
        }

        .boton-agregar:hover {
            background: #0b5ed7;
        }

        /* Paginación */
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
background-color:#9ca3af;
            color: white;
        }

        .pagination a:hover {
            background: #e5e7eb;
        }

        .active-page {
            background: #0d6efd;
            color: #fff;
            border-color: #0d6efd;
        }

        .pagination-info {
            margin-left: auto;
            color: #475569;
        }
        /* Links de los encabezados de la tabla */
        .th{
            text-decoration: none;
background-color:#9ca3af;
margin:-5px
        }
    </style>
</head>
<body>

<script>
function eliminarPaciente(id, nombre, apellido, dni) {
    const mensaje = `ATENCIÓN\nVas a eliminar al paciente:\n${apellido}, ${nombre}\nDNI: ${dni}\nEsta acción NO se puede deshacer.\nPara confirmar, ingresá nuevamente el DNI del paciente:`;

    const dniIngresado = prompt(mensaje);

    if (dniIngresado === null) {
        return false; // Canceló
    }

    if (dniIngresado.trim() !== dni.toString()) {
        alert("❌ El DNI ingresado no coincide. Eliminación cancelada.");
        return false;
    }

    return confirm("✅ DNI verificado correctamente.\n¿Confirmás definitivamente la eliminación?");
}
</script>

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

<nav style="min-height: calc(100% - 140px);">
    <a href="main.php">Inicio</a>
    <a href="pacientes.php" style="background-color: #FFB703;">Pacientes</a>
    <a href="reportes.php">Evoluciones</a>
    <a href="citas.php">Citas</a>
    <a href="gestion.php">Gestión</a>
    <a href="logout.php">Cerrar sesión</a>
</nav>

<main style="flex:1;">
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include("conexion_db.php");
include("conexion.php");
include("eliminarpacientes.php");

/* =======================================
   BUSCADOR
======================================= */

$valorBuscado = isset($_GET['buscar']) ? trim($_GET['buscar']) : "";
$buscarSQL = $conexion->real_escape_string($valorBuscado);

/* =======================================
   ORDENAMIENTO (sort + dir)
======================================= */

$sort = $_GET['sort'] ?? '';
$dir  = $_GET['dir'] ?? 'asc';

$allowedSort = [
    'nombre'      => 'p.nombre',
    'apellido'    => 'p.apellido',
    'dni'         => 'p.dni',
    'telefono'    => 'p.telefono',
    'direccion'   => 'p.direccion',
    'obra_social' => 'o.nombre_obrasocial'
];

if (isset($allowedSort[$sort])) {
    $orderBy  = $allowedSort[$sort];
    $orderDir = ($dir === 'desc') ? 'DESC' : 'ASC';
} else {
    // Orden por defecto
    $sort     = '';
    $orderBy  = 'p.id_paciente';
    $orderDir = 'DESC';
}

/* =======================================
   WHERE (filtro por búsqueda)
======================================= */

$where = "";
if ($valorBuscado !== "") {
    $where = "
        WHERE p.nombre   LIKE '%$buscarSQL%'
           OR p.apellido LIKE '%$buscarSQL%'
           OR p.dni      LIKE '%$buscarSQL%'
           OR p.telefono LIKE '%$buscarSQL%'
    ";
}

/* =======================================
   PAGINACIÓN
======================================= */

$porPagina = isset($_GET['rows']) && in_array((int)$_GET['rows'], [8, 12])
    ? (int)$_GET['rows']
    : 8;

$sqlCount = "
    SELECT COUNT(*) AS total
    FROM pacientes p
    LEFT JOIN obras_sociales o ON p.obra_social = o.id_obrasocial
    $where
";
$resCount = $conexion->query($sqlCount);
$totalRegistros = $resCount ? (int)$resCount->fetch_assoc()['total'] : 0;

$totalPaginas = max(1, ceil($totalRegistros / $porPagina));

$pagina = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($pagina < 1) $pagina = 1;
if ($pagina > $totalPaginas) $pagina = $totalPaginas;

$offset = ($pagina - 1) * $porPagina;

/* =======================================
   CONSULTA PRINCIPAL
======================================= */

$sql = "
    SELECT p.*, o.nombre_obrasocial
    FROM pacientes p
    LEFT JOIN obras_sociales o ON p.obra_social = o.id_obrasocial
    $where
    ORDER BY $orderBy $orderDir
    LIMIT $offset, $porPagina
";

$result = $conexion->query($sql);

/* =======================================
   PARAMS PARA LINKS (paginación y orden)
======================================= */

$params = $_GET;
unset($params['page']); // page se maneja aparte
$qsBase = http_build_query($params);
$baseURL = "pacientes.php";

/**
 * Construye URL para ordenar por una columna.
 */
function buildSortUrl($campo, $currentSort, $currentDir, $params, $baseURL) {
    // Duplicamos parámetros actuales
    $p = $params;

    // Al hacer clic en otra columna, empezamos en ascendente
    if ($currentSort === $campo) {
        // Toggle asc/desc
        $newDir = ($currentDir === 'asc') ? 'desc' : 'asc';
    } else {
        $newDir = 'asc';
    }

    $p['sort'] = $campo;
    $p['dir']  = $newDir;

    // Al cambiar orden, volvemos a la primera página
    unset($p['page']);

    $qs = http_build_query($p);

    return $baseURL . '?' . $qs;
}

/**
 * Devuelve la flechita ▲▼ según columna actual y dirección.
 */
function sortArrow($campo, $currentSort, $currentDir) {
    if ($campo !== $currentSort) return '';
    return $currentDir === 'asc' ? ' ▲' : ' ▼';
}
?>

<section>

    <!-- BARRA SUPERIOR -->
    <div class="barra-superior">

        <!-- FORMULARIO BUSCADOR -->
        <form method="GET" action="pacientes.php">

            <input 
                type="text" 
                name="buscar"
                placeholder="Ingrese nombre, apellido, DNI o teléfono"
                value="<?= htmlspecialchars($valorBuscado) ?>"
            >

            <button type="submit" class="btn-buscar">Buscar</button>

            <!-- BOTÓN LIMPIAR -->
            <a class="boton-gris" href="pacientes.php">Limpiar</a>

        </form>

        <!-- BOTÓN AGREGAR PACIENTE (derecha) -->
        <a href="agregar_paciente.php" class="boton-agregar">Agregar paciente</a>

    </div>


    <div>
        <table>
            <thead>
                <tr>
                    <th>
                        <a class="th" href="<?= htmlspecialchars(buildSortUrl('nombre', $sort, $dir, $params, $baseURL)) ?>">
                            Nombre<?= sortArrow('nombre', $sort, $dir) ?>
                        </a>
                    </th>
                    <th>
                        <a class="th" href="<?= htmlspecialchars(buildSortUrl('apellido', $sort, $dir, $params, $baseURL)) ?>">
                            Apellido<?= sortArrow('apellido', $sort, $dir) ?>
                        </a>
                    </th>
                    <th>
                        <a class="th" href="<?= htmlspecialchars(buildSortUrl('dni', $sort, $dir, $params, $baseURL)) ?>">
                            Documento<?= sortArrow('dni', $sort, $dir) ?>
                        </a>
                    </th>
                    <th>
                        <a class="th" href="<?= htmlspecialchars(buildSortUrl('telefono', $sort, $dir, $params, $baseURL)) ?>">
                            Teléfono<?= sortArrow('telefono', $sort, $dir) ?>
                        </a>
                    </th>
                    <th>
                        <a class="th" href="<?= htmlspecialchars(buildSortUrl('direccion', $sort, $dir, $params, $baseURL)) ?>">
                            Dirección<?= sortArrow('direccion', $sort, $dir) ?>
                        </a>
                    </th>
                    <th>
                        <a class="th" href="<?= htmlspecialchars(buildSortUrl('obra_social', $sort, $dir, $params, $baseURL)) ?>">
                            Obra Social<?= sortArrow('obra_social', $sort, $dir) ?>
                        </a>
                    </th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['nombre']) ?></td>
                        <td><?= htmlspecialchars($row['apellido']) ?></td>
                        <td><?= htmlspecialchars($row['dni']) ?></td>
                        <td><?= htmlspecialchars($row['telefono']) ?></td>
                        <td><?= htmlspecialchars($row['direccion']) ?></td>
                        <td><?= htmlspecialchars($row['nombre_obrasocial']) ?></td>
                        <td style="display:flex; justify-content: space-evenly;">
                            <a href="modificarpacientes.php?id=<?= $row['id_paciente'] ?>" style="background:#0d6efd;color:white;padding:4px 7px;border-radius:4px;">Modificar</a>
                           <a 
   href="pacientes.php?id=<?= $row['id_paciente'] ?>"
   onclick="return eliminarPaciente(
       <?= $row['id_paciente'] ?>,
       '<?= htmlspecialchars($row['nombre'], ENT_QUOTES) ?>',
       '<?= htmlspecialchars($row['apellido'], ENT_QUOTES) ?>',
       '<?= $row['dni'] ?>'
   )"
   style="background:#dc3545;color:white;padding:4px 7px;border-radius:4px;">
   Eliminar
</a>
                            <a href="historiaclinica.php?id_paciente=<?= $row['id_paciente'] ?>" style="background:#ffc107;color:black;padding:4px 7px;border-radius:4px;">Evoluciones</a>
                            <a href="odontograma.php?id_paciente=<?= $row['id_paciente'] ?>" style="background:#28a745;color:white;padding:4px 7px;border-radius:4px;">Odontograma</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="7">No se encontraron resultados.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- PAGINACIÓN -->
    <div class="pagination">
        <?php
        // Reconstruir params sin page para los links de paginación
        $paramsNoPage = $_GET;
        unset($paramsNoPage['page']);
        $qsNoPage = http_build_query($paramsNoPage);

        // Link anterior
        if ($pagina > 1):
            $p = $pagina - 1;
            $qsPrev = $qsNoPage ? "$qsNoPage&page=$p" : "page=$p";
        ?>
            <a href="<?= htmlspecialchars("$baseURL?$qsPrev") ?>">&laquo; Anterior</a>
        <?php endif; ?>

        <?php
        // Rango de páginas
        $inicio = max(1, $pagina - 3);
        $fin    = min($totalPaginas, $pagina + 3);

        for ($i = $inicio; $i <= $fin; $i++):
            $qPage = $qsNoPage ? "$qsNoPage&page=$i" : "page=$i";
        ?>
            <?php if ($i == $pagina): ?>
                <span class="active-page"><?= $i ?></span>
            <?php else: ?>
                <a href="<?= htmlspecialchars("$baseURL?$qPage") ?>"><?= $i ?></a>
            <?php endif; ?>
        <?php endfor; ?>

        <?php
        // Link siguiente
        if ($pagina < $totalPaginas):
            $p = $pagina + 1;
            $qsNext = $qsNoPage ? "$qsNoPage&page=$p" : "page=$p";
        ?>
            <a href="<?= htmlspecialchars("$baseURL?$qsNext") ?>">Siguiente &raquo;</a>
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
(function () {
    const alto = window.screen.height;
    let filas = 8;

    if (alto >= 1080) {
        filas = 12;
    } else if (alto >= 720) {
        filas = 8;
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