<?php
session_start();
include "conexion_db.php";

$id = $_GET["id"] ?? '';
$sql = $conexion->query("SELECT * FROM pacientes WHERE id_paciente = '$id'");

if (isset($_POST["enviar"])) {

    $id_original = intval($_POST["id_original"]);
    $nombre = $conexion->real_escape_string($_POST["nombre"]);
    $apellido = $conexion->real_escape_string($_POST["apellido"]);
    $genero = $conexion->real_escape_string($_POST["genero"]);
    $dni_nuevo = $conexion->real_escape_string($_POST["dni"]);
    $fecha_nac = $conexion->real_escape_string($_POST["fecha_nac"]);
    $telefono = $conexion->real_escape_string($_POST["telefono"]);
    $correo = $conexion->real_escape_string($_POST["correo"]);
    $observaciones = $conexion->real_escape_string($_POST["observaciones"]);
    $localidad = intval($_POST["localidad"]);
    $direccion = $conexion->real_escape_string($_POST["direccion"]);
    $tipo_sangre = $conexion->real_escape_string($_POST["tipo_sangre"]);
    $obra_social = intval($_POST["obra_social"]);
    $nro_afiliado = $conexion->real_escape_string($_POST["nro_afiliado"]);

    $update_sql = "UPDATE pacientes SET 
        nombre = '$nombre',
        apellido = '$apellido',
        genero = '$genero',
        dni = '$dni_nuevo',
        fecha_nac = '$fecha_nac',
        telefono = '$telefono',
        correo = '$correo',
        observaciones = '$observaciones',
        localidad = '$localidad',
        direccion = '$direccion',
        tipo_sangre = '$tipo_sangre',
        obra_social = '$obra_social',
        nro_afiliado = '$nro_afiliado'
        WHERE id_paciente = '$id_original'";

    if ($conexion->query($update_sql) === TRUE) {
        echo "<script>
            alert('Cambios guardados exitosamente.');
            window.location.href = 'pacientes.php';
        </script>";
    } else {
        echo "<script>
            alert('Error al guardar los cambios: " . addslashes($conexion->error) . "');
        </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link rel="stylesheet" href="./styles/pacientes.css">
<link rel="stylesheet" href="./styles/agregar_paciente.css">

<title>Modificar Paciente</title>

<style>
.input-group {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 15px 25px;
    width: 100%;
}

.form-field {
    display: flex;
    flex-direction: column;
    width: 100%;
}

.form-field label {
    font-weight: bold;
    margin-bottom: 5px;
}

.form-field input,
.form-field select {
    width: 100%;
height:40px;
}

form {
    display: flex;
    flex-direction: column;
}

.button-group {
    width: 100%;
    text-align: center;
    margin-top: 40px;
}
input, select {
    text-transform: uppercase;
}
</style>

<script>
function validarDocumento(input) {
    input.value = input.value.replace(/[^0-9]/g, '');
    if (input.value.length > 8) {
        input.value = input.value.slice(0, 8);
    }
}

function validarTelefono(input) {
    input.value = input.value.replace(/[^0-9]/g, '');
    if (input.value.length > 10) {
        input.value = input.value.slice(0, 10);
    }
}

function validarAfiliado(input) {
    input.value = input.value.replace(/[^0-9]/g, '');
}
function convertirMayusculas(input) {
    input.value = input.value.toUpperCase();
}
</script>

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
</div>

<div>
    <h2 style="text-align:center;">Modificar Paciente</h2>
</div>

<form method="post" autocomplete="off">

<input type="hidden" name="id_original" value="<?= htmlspecialchars($id) ?>">

<?php while ($datos = $sql->fetch_object()) { ?>

<div class="input-group">

    <div class="form-field">
        <label>Nombre</label>
        <input type="text" name="nombre" value="<?= htmlspecialchars($datos->nombre) ?>" required oninput="convertirMayusculas(this)">
    </div>

    <div class="form-field">
        <label>Apellido</label>
        <input type="text" name="apellido" value="<?= htmlspecialchars($datos->apellido) ?>" required oninput="convertirMayusculas(this)">
    </div>

    <div class="form-field">
        <label>Género</label>
        <select name="genero" required>
            <option value="" disabled>Seleccione</option>
            <option value="Masculino" <?= $datos->genero == "Masculino" ? "selected" : "" ?>>Masculino</option>
            <option value="Femenino" <?= $datos->genero == "Femenino" ? "selected" : "" ?>>Femenino</option>
        </select>
    </div>

    <div class="form-field">
        <label>Documento</label>
        <input type="text" name="dni" value="<?= htmlspecialchars($datos->dni) ?>" oninput="validarDocumento(this)" required>
    </div>

    <div class="form-field">
        <label>Fecha Nacimiento</label>
        <input type="date" name="fecha_nac" value="<?= htmlspecialchars($datos->fecha_nac) ?>" required>
    </div>

    <div class="form-field">
        <label>Teléfono</label>
        <input type="text" name="telefono" value="<?= htmlspecialchars($datos->telefono) ?>" oninput="validarTelefono(this)" required>
    </div>

    <div class="form-field">
        <label>Correo</label>
        <input type="email" name="correo" value="<?= htmlspecialchars($datos->correo) ?>" oninput="convertirMayusculas(this)">
    </div>

    <div class="form-field">
        <label>Observaciones</label>
        <input type="text" name="observaciones" value="<?= htmlspecialchars($datos->observaciones) ?>" oninput="convertirMayusculas(this)">
    </div>

    <div class="form-field">
        <label>Localidad</label>
        <select name="localidad" required>
            <option value="" disabled>Seleccione</option>
            <?php
            $query_localidades = "SELECT id_localidad, nombre_localidad FROM localidades";
            $result_localidades = $conexion->query($query_localidades);
            while ($row = $result_localidades->fetch_object()) {
                $selected = ($datos->localidad == $row->id_localidad) ? "selected" : "";
                echo "<option value='$row->id_localidad' $selected>$row->nombre_localidad</option>";
            }
            ?>
        </select>
    </div>

    <div class="form-field">
        <label>Dirección</label>
        <input type="text" name="direccion" value="<?= htmlspecialchars($datos->direccion) ?>" required oninput="convertirMayusculas(this)">
    </div>

    <div class="form-field">
        <label>Tipo de Sangre</label>
        <select name="tipo_sangre" required>
            <option value="" disabled>Seleccione</option>
            <?php
            $tipos = ["A+","A-","B+","B-","AB+","AB-","O+","O-"];
            foreach ($tipos as $tipo) {
                $sel = ($datos->tipo_sangre == $tipo) ? "selected" : "";
                echo "<option $sel>$tipo</option>";
            }
            ?>
        </select>
    </div>

    <div class="form-field">
        <label>Obra Social</label>
        <select name="obra_social" required>
            <option value="" disabled>Seleccione</option>
            <?php
            $query_obras_sociales = "SELECT id_obrasocial, nombre_obrasocial FROM obras_sociales";
            $result_obras_sociales = $conexion->query($query_obras_sociales);
            while ($row = $result_obras_sociales->fetch_object()) {
                $selected = ($datos->obra_social == $row->id_obrasocial) ? "selected" : "";
                echo "<option value='$row->id_obrasocial' $selected>$row->nombre_obrasocial</option>";
            }
            ?>
        </select>
    </div>

    <div class="form-field">
        <label>Nº Afiliado</label>
        <input type="text" name="nro_afiliado" value="<?= htmlspecialchars($datos->nro_afiliado) ?>" oninput="validarAfiliado(this)">
    </div>

</div>

<?php } ?>

<div class="button-group">
    <button type="submit" name="enviar" value="ok" class="form-container-button">Guardar</button>
    <a href="pacientes.php" class="form-container-button">Cancelar</a>
</div>

</form>

</section>
</main>

<footer>
    <h4>SGOE - Sistema de Gestión Odontológico Esperanza - Creado por Dilkin, Oscar y De Matos Esteban</h4>
</footer>

</body>
</html>