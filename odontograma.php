<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}
include "conexion_db.php"; // Debe dejar disponible la variable $conexion (mysqli)

// Obtener ID_PACIENTE desde GET
$id_paciente = $_GET['id_paciente'] ?? '';
if (empty($id_paciente)) {
    header("Location: pacientes.php");
    exit();
}

// Obtener paciente
$stmt = $conexion->prepare("SELECT id_paciente, nombre, apellido FROM pacientes WHERE id_paciente = ?");
$stmt->bind_param("i", $id_paciente);
$stmt->execute();
$pac = $stmt->get_result()->fetch_object();
$stmt->close();

if (!$pac) {
    echo "Paciente no encontrado.";
    exit();
}

// Verificar existencia de piezas_dentales
$res = $conexion->query("SELECT COUNT(*) as cnt FROM piezas_dentales");
$row = $res->fetch_object();
if ($row->cnt == 0) {
    $codes = array_merge(range(11,18), range(21,28), range(31,38), range(41,48));
    $insert = $conexion->prepare("INSERT INTO piezas_dentales (codigo_pieza, nombre_pieza, cuadrante, tipo_denticion) VALUES (?, ?, ?, 'permanente')");
    foreach ($codes as $c) {
        $nombre = "Pieza " . $c;
        if ($c >= 11 && $c <= 18) $cuad = 1;
        if ($c >= 21 && $c <= 28) $cuad = 2;
        if ($c >= 31 && $c <= 38) $cuad = 3;
        if ($c >= 41 && $c <= 48) $cuad = 4;
        $insert->bind_param("ssi", $c, $nombre, $cuad);
        $insert->execute();
    }
    $insert->close();
}

// Cargar piezas_dentales ordenadas
$piezas_q = $conexion->query("SELECT id_pieza, codigo_pieza, nombre_pieza, cuadrante FROM piezas_dentales ORDER BY cuadrante ASC, codigo_pieza ASC");
$piezas = [];
while ($p = $piezas_q->fetch_object()) {
    $piezas[] = $p;
}

// Obtener odontogramas del paciente
$stmt = $conexion->prepare("SELECT id_odontograma, fecha_creacion, observaciones_generales, activo FROM odontogramas WHERE id_paciente = ? ORDER BY fecha_creacion DESC");
$stmt->bind_param("i", $id_paciente);
$stmt->execute();
$odontogramas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Odontograma seleccionado (activo o elegido)
$odontograma_actual_id = null;
if (isset($_GET['odontograma_id']) && is_numeric($_GET['odontograma_id'])) {
    $odontograma_actual_id = (int) $_GET['odontograma_id'];
} else {
    foreach ($odontogramas as $o) {
        if ($o['activo']) { $odontograma_actual_id = $o['id_odontograma']; break; }
    }
    if (!$odontograma_actual_id && count($odontogramas) > 0) {
        $odontograma_actual_id = $odontogramas[0]['id_odontograma'];
    }
}

// Cargar detalles del odontograma seleccionado
$detalles = [];
if ($odontograma_actual_id) {
    $stmt = $conexion->prepare("SELECT id_detalle, id_pieza, cara_superior, cara_inferior, cara_izquierda, cara_derecha, cara_central, observacion_pieza FROM detalle_odontograma WHERE id_odontograma = ?");
    $stmt->bind_param("i", $odontograma_actual_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($d = $res->fetch_object()) {
        $detalles[$d->id_pieza] = $d;
    }
    $stmt->close();
}

$fecha_hoy = date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="es" style="height:100%">
<head>
<meta charset="utf-8">
<title>Odontograma - <?= htmlspecialchars($pac->nombre . ' ' . $pac->apellido) ?></title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="./styles/pacientes.css">

<style>
*{ font-family: Arial, sans-serif;}
body {margin:0; background:#f5f5f5;}

/* Layout: container + history a la derecha */
.main-layout {
    display: flex;
    gap: 10px;
    padding: 8px;
    box-sizing: border-box;
width: calc(100% - 200px);
    height: calc(100vh - 160px); /* deja espacio para header/nav/footer */
}

/* container (odontograma) */
.container {
    width: 100%;
    
    height: 100%;
    background:white;
    padding:8px;
    border-radius:6px;
    box-shadow:0 2px 8px rgba(0,0,0,0.08);
    overflow-y:auto;
    overflow-x:hidden;
}

/* history a la derecha */
.history-list {
    width: 320px;
    max-width: 320px;
    height: 100%;
    background: #fff;
    padding: 8px;
    border-radius:6px;
    box-shadow:0 2px 8px rgba(0,0,0,0.08);
    overflow-y:auto; /* solo el historial hace scroll vertical */
}

/* resto de estilos (sin cambios en funcionalidad) */
.top-actions, .controls { display:flex; flex-wrap: wrap; gap:6px; align-items:center; margin-bottom:8px; }

.tooth-grid { display:grid; grid-template-columns: repeat(8, 1fr); gap:4px; margin-bottom:8px; }

.tooth { background:#fff; border:1px solid #ddd; border-radius:4px; padding:2px; text-align:center; position:relative; cursor:pointer; user-select:none; font-size:10px; }
.tooth .code { font-weight:bold; margin-bottom:2px; font-size:14px; }

.faces { display:grid; 
grid-template-columns: 1fr 1fr 1fr;
grid-template-areas:
    "superior superior superior"
    "izquierda central derecha"
    "inferior inferior inferior";
    gap:1px;
}

.face { border:1px solid #eee; padding:2px; font-size:12px; background:#fafafa; text-align:center; border-radius:3px; 
height:30px; display:flex; align-items: center; justify-content: center; overflow-x:hidden;
}
.face-superior { grid-area: superior; }
.face-inferior { grid-area: inferior; }
.face-izquierda { grid-area: izquierda; }
.face-central { grid-area: central; }
.face-derecha { grid-area: derecha; }
.face.marked { background:#007bff; color:white; font-weight:bold; }

.note { font-size:12px; display:block; margin-top:2px; }

table { width:100%; border-collapse:collapse; margin-top:6px; font-size:11px; }
th, td { border:1px solid #eee; padding:2px; text-align:left; }

input, textarea, select, button { font-size:10px; padding:2px 4px; border-radius:3px; }
input{ text-transform: uppercase;}
.btn { padding:6px 10px; border:none; border-radius:6px; cursor:pointer; font-size:12px; }
.btn-primary { background:rgb(40,167,69); color:#fff; }
.btn-primary:hover { background:rgb(31,129,55); color:#fff; }
.btn-secondary { background:rgb(107,114,128); color:#fff; text-decoration: none; margin-top: 5px}
.btn-secondary:hover { background:rgb(75,85,99); color:#fff; }
.btn-danger { background:#e63946; color:#fff; }

.modal { position: fixed; left:0; right:0; top:0; bottom:0; display:none; align-items:center; justify-content:center; background: rgba(0,0,0,0.45); z-index: 9999; }
.modal.open { display:flex;}
.modal-content { background:#fff; padding:10px; border-radius:6px; width:700px; max-width:94%; box-shadow:0 6px 18px rgba(0,0,0,0.2); }
.modal-content input, textarea{height:30px; border-radius:5px;font-size:12px}

header, nav, footer { box-sizing:border-box; }
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

<nav style="min-height: calc(100% - 140px);">
    <a href="main.php">Inicio</a>
    <a href="pacientes.php" style="background-color:#FFB703;">Pacientes</a>
    <a href="reportes.php">Evoluciones</a>
    <a href="citas.php">Citas</a>
    <a href="gestion.php">Gestión</a>
    <a href="logout.php">Cerrar sesión</a>
</nav>

<div class="main-layout">

    <div class="container">
<div><a href="pacientes.php" class="btn btn-secondary">Volver</a></div>  
        <div style="display:flex; justify-content:center; align-items:center;">
          
<h2 style="margin:0;">Odontograma - <?= htmlspecialchars($pac->nombre . ' ' . $pac->apellido) ?></h2>
            
        </div>

        <div class="top-actions">
            







            <div style="margin-left:auto;">
                <label style="margin-right:6px;">Odontogramas guardados:</label>
                <select id="selectHistoria" style="padding:6px; border-radius:6px; border:1px solid #ccc; width:85%;">
                    <option value="">-- Seleccionar/Ver historial --</option>
                    <?php foreach ($odontogramas as $o): ?>
                        <option value="<?= $o['id_odontograma'] ?>"
        data-activo="<?= $o['activo'] ? '1' : '0' ?>"
        <?php if($o['id_odontograma'] == $odontograma_actual_id) echo 'selected'; ?>>

    <?= htmlspecialchars(
        $o['fecha_creacion']
        . ' - '
        . ($o['observaciones_generales'] ?: 'Sin observaciones')
        . ($o['activo'] ? ' (activo)' : '')
    ) ?>

</option>
                    <?php endforeach; ?>
                </select>
                <button id="btnCargar" class="btn btn-secondary">Cargar</button>
            </div>
        </div>

        <div class="controls">
            <label for="obs" style="margin-right:8px;">Observaciones generales:</label>
            <input id="obs"
       type="text"
       value="<?= htmlspecialchars($odontogramas ? $odontogramas[0]['observaciones_generales'] ?? '' : '') ?>"
       data-original="<?= htmlspecialchars($odontogramas ? $odontogramas[0]['observaciones_generales'] ?? '' : '') ?>"
       placeholder="Observaciones breves..."
       style="flex:1; padding:6px; border-radius:6px; border:1px solid #ccc;">
        </div>

        <div>
            <small>Hacé click en una pieza para editar el estado de sus caras y la observación de la pieza.</small>
        </div>

        <hr style="margin:8px 0;">

        <div id="grid" class="tooth-grid">
            <?php foreach ($piezas as $p):
                $detalle = $detalles[$p->id_pieza] ?? null;
                $cs = $detalle ? $detalle->cara_superior : '';
                $ci = $detalle ? $detalle->cara_inferior : '';
                $cl = $detalle ? $detalle->cara_izquierda : '';
                $cd = $detalle ? $detalle->cara_derecha : '';
                $cc = $detalle ? $detalle->cara_central : '';
            ?>
                <div class="tooth" data-id="<?= $p->id_pieza ?>" data-codigo="<?= htmlspecialchars($p->codigo_pieza) ?>">
                    <div class="code"><?= htmlspecialchars($p->codigo_pieza) ?></div>
                    <div class="faces">
                        <div class="face face-superior <?= $cs ? 'marked' : '' ?>"><?= $cs ?: 'Sup' ?></div>
                        <div class="face face-izquierda <?= $cl ? 'marked' : '' ?>"><?= $cl ?: 'Izq' ?></div>
                        <div class="face face-central <?= $cc ? 'marked' : '' ?>"><?= $cc ?: 'Cen' ?></div>
                        <div class="face face-derecha <?= $cd ? 'marked' : '' ?>"><?= $cd ?: 'Der' ?></div>
                        <div class="face face-inferior <?= $ci ? 'marked' : '' ?>"><?= $ci ?: 'Inf' ?></div>
                    </div>
                    <div class="note"><?= htmlspecialchars($detalle ? $detalle->observacion_pieza : '') ?></div>
                </div>
            <?php endforeach; ?>
        </div>

<form id="newForm" method="POST" action="save_odontograma.php" style="display:flex; gap:8px; align-items:center;justify-content: right;">
                <input type="hidden" name="id_paciente" value="<?= intval($id_paciente) ?>">
                <input type="hidden" name="datos_json" id="datos_json">
                <input type="hidden" name="observaciones_generales" id="observaciones_generales" oninput="convertirMayusculas(this)">
                <button type="button" id="btnCrear" class="btn btn-primary">Guardar Odontograma</button>
            </form>

    </div>
</div>

<!-- Modal para editar pieza (funcionalidad completa) -->
<div id="modal" class="modal" aria-hidden="true">
    <div class="modal-content" role="dialog" aria-modal="true">
        <h3 id="modalTitle" style="margin:0 0 6px 0; text-align:center;">EDITAR PIEZA</h3>
        <input type="hidden" id="modal_pieza_id">
        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:8px;">
            <div style="grid-column:2;">
                <label for="m_superior">Superior</label>
                <input id="m_superior" type="text" maxlength="20" placeholder="estado corto" oninput="convertirMayusculas(this)">
<br>
            </div>
            <div style="grid-column:1;">
                <label for="m_izquierda">Izquierda</label>
                <input id="m_izquierda" type="text" maxlength="20" oninput="convertirMayusculas(this)">
            </div>
<div style="grid-column:2;">
                <label for="m_central">Central</label><br>
                <input id="m_central" type="text" maxlength="20" oninput="convertirMayusculas(this)">
            </div>
            <div style="grid-column:3;">
                <label for="m_derecha">Derecha</label>
                <input id="m_derecha" type="text" maxlength="20" oninput="convertirMayusculas(this)">
            </div>

<div style="grid-column:2;">
                <label for="m_inferior">Inferior</label><br>
                <input id="m_inferior" type="text" maxlength="20" oninput="convertirMayusculas(this)">
            </div>
            <br>
</div>
            <div>
                <label for="m_observacion">Observación de la pieza</label><br>
                <textarea id="m_observacion" rows="1" style="width:100%;" maxlength="25" oninput="convertirMayusculas(this)"></textarea>
            </div>
        

        <div class="modal-footer" style="margin-left:35%">
            <button id="btnEliminarP" class="btn btn-danger">Borrar pieza</button>
            <button id="btnCerrar" class="btn btn-secondary">Cerrar</button>
            <button id="btnGuardarP" class="btn btn-primary">Guardar</button>
        </div>
    </div>
</div>



<footer>
    <h4 style="margin:0; text-align:center;">SGOE - Sistema de Gestión Odontológico Esperanza - Creado por Dilkin, Oscar y De Matos Esteban</h4>
</footer>

<script>
let estado = {}; // id_pieza -> { superior, inferior, izquierda, derecha, central, observacion }
let estadoOriginal = {};


// Inicializar estado leyendo la vista actual
function inicializarEstado() {
    estado = {};
    estadoOriginal = {};

    document.querySelectorAll('.tooth').forEach(t => {
        const id = t.getAttribute('data-id');
        const getText = (sel, placeholder) => {
            const el = t.querySelector(sel);
            if (!el) return '';
            const txt = el.textContent.trim();
            if (!txt) return '';
            if (txt === placeholder) return '';
            return txt;
        };

        const data = {
            superior: getText('.face-superior', 'Sup'),
            inferior: getText('.face-inferior', 'Inf'),
            izquierda: getText('.face-izquierda', 'Izq'),
            derecha: getText('.face-derecha', 'Der'),
            central: getText('.face-central', 'Cen'),
            observacion: t.querySelector('.note') ? t.querySelector('.note').textContent.trim() : ''
        };

        estado[id] = {...data};
        estadoOriginal[id] = {...data};
    });
}
function huboCambios() {
    for (const id in estado) {
        const a = estado[id];
        const b = estadoOriginal[id];

        if (
            a.superior !== b.superior ||
            a.inferior !== b.inferior ||
            a.izquierda !== b.izquierda ||
            a.derecha !== b.derecha ||
            a.central !== b.central ||
            a.observacion !== b.observacion
        ) {
            return true; // hubo cambios
        }
    }

    // También comparamos observación general
    const obsActual = document.getElementById('obs').value.trim();
    const obsOriginal = document.getElementById('obs').dataset.original || '';

    if (obsActual !== obsOriginal) return true;

    return false;
}

// Mostrar modal para editar pieza
const modal = document.getElementById('modal');
const modalTitle = document.getElementById('modalTitle');
const modal_pieza_id = document.getElementById('modal_pieza_id');
const m_superior = document.getElementById('m_superior');
const m_inferior = document.getElementById('m_inferior');
const m_izquierda = document.getElementById('m_izquierda');
const m_derecha = document.getElementById('m_derecha');
const m_central = document.getElementById('m_central');
const m_observacion = document.getElementById('m_observacion');

function abrirModalParaPieza(elementoTooth) {
    const id = elementoTooth.getAttribute('data-id');
    const codigo = elementoTooth.getAttribute('data-codigo');
    modalTitle.textContent = 'EDITAR PIEZA ' + codigo;
    modal_pieza_id.value = id;
    const s = estado[id] || { superior:'', inferior:'', izquierda:'', derecha:'', central:'', observacion:'' };
    m_superior.value = s.superior || '';
    m_inferior.value = s.inferior || '';
    m_izquierda.value = s.izquierda || '';
    m_derecha.value = s.derecha || '';
    m_central.value = s.central || '';
    m_observacion.value = s.observacion || '';
    modal.classList.add('open');
    modal.setAttribute('aria-hidden', 'false');
}

// Cerrar modal
document.getElementById('btnCerrar').addEventListener('click', () => {
    modal.classList.remove('open');
    modal.setAttribute('aria-hidden', 'true');
});

// Guardar pieza desde modal -> actualiza estado y DOM
document.getElementById('btnGuardarP').addEventListener('click', () => {
    const id = modal_pieza_id.value;
    estado[id] = {
        superior: m_superior.value.trim(),
        inferior: m_inferior.value.trim(),
        izquierda: m_izquierda.value.trim(),
        derecha: m_derecha.value.trim(),
        central: m_central.value.trim(),
        observacion: m_observacion.value.trim()
    };
    // actualizar DOM
    const t = document.querySelector(`.tooth[data-id="${id}"]`);
    if (t) {
        const setFace = (sel, value, placeholder) => {
            const el = t.querySelector(sel);
            if (!el) return;
            if (value) {
                el.textContent = value;
                el.classList.add('marked');
            } else {
                el.textContent = placeholder;
                el.classList.remove('marked');
            }
        };
        setFace('.face-superior', estado[id].superior, 'S');
        setFace('.face-inferior', estado[id].inferior, 'I');
        setFace('.face-izquierda', estado[id].izquierda, 'L');
        setFace('.face-derecha', estado[id].derecha, 'D');
        setFace('.face-central', estado[id].central, 'C');
        const note = t.querySelector('.note');
        if (note) note.textContent = estado[id].observacion;
    }
    modal.classList.remove('open');
    modal.setAttribute('aria-hidden', 'true');
});

// Borrar los datos de una pieza desde modal
document.getElementById('btnEliminarP').addEventListener('click', () => {
    const id = modal_pieza_id.value;
    if (!confirm('¿Borrar los datos de esta pieza?')) return;
    estado[id] = { superior:'', inferior:'', izquierda:'', derecha:'', central:'', observacion:'' };
    const t = document.querySelector(`.tooth[data-id="${id}"]`);
    if (t) {
        ['.face-superior','.face-inferior','.face-izquierda','.face-derecha','.face-central'].forEach((sel) => {
            const el = t.querySelector(sel);
            if (el) {
                const placeholder = sel.includes('superior') ? 'S' : sel.includes('inferior') ? 'I' : sel.includes('izquierda') ? 'L' : sel.includes('derecha') ? 'D' : 'C';
                el.textContent = placeholder;
                el.classList.remove('marked');
            }
        });
        const note = t.querySelector('.note'); if (note) note.textContent = '';
    }
    modal.classList.remove('open');
    modal.setAttribute('aria-hidden', 'true');
});

// Abrir modal al click en cualquier diente
document.querySelectorAll('.tooth').forEach(t => {
    t.addEventListener('click', () => {
        abrirModalParaPieza(t);
    });
});

// Inicializar estado al cargar
inicializarEstado();


    
document.getElementById('btnCrear').addEventListener('click', () => {

    if (!huboCambios()) {
        alert('No se realizaron cambios en el odontograma. No se guardó.');
        return;
    }

    const datos = [];
    for (const id in estado) {
        const s = estado[id];
        datos.push({
            id_pieza: id,
            cara_superior: s.superior || '',
            cara_inferior: s.inferior || '',
            cara_izquierda: s.izquierda || '',
            cara_derecha: s.derecha || '',
            cara_central: s.central || '',
            observacion_pieza: s.observacion || ''
        });
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'save_odontograma.php';

    const add = (name, val) => {
        const i = document.createElement('input');
        i.type = 'hidden';
        i.name = name;
        i.value = val;
        form.appendChild(i);
    };

    add('id_paciente', '<?= intval($id_paciente) ?>');
    add('datos_json', JSON.stringify(datos));
    add('observaciones_generales', document.getElementById('obs').value.trim());

    document.body.appendChild(form);
    form.submit();
});

document.getElementById('btnCargar').addEventListener('click', () => {
    const sel = document.getElementById('selectHistoria');
    const id = sel.value;
    if (!id) {
        alert('Seleccioná un odontograma');
        return;
    }
    window.location.href = `odontograma.php?id_paciente=<?= urlencode($id_paciente) ?>&odontograma_id=${id}`;
});
// Evitar que el modal se cierre al hacer click dentro del contenido
document.getElementById('modal').addEventListener('click', (e) => {
    if (e.target === document.getElementById('modal')) {
        // si clic fuera del modal-content, cerrarlo
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');
    }
});
function convertirMayusculas(input) {
    input.value = input.value.toUpperCase();
}
</script>

</body>
</html>