<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}
include("conexion.php");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./styles/pacientes.css">
    <link rel="stylesheet" href="./styles/agregar_paciente.css">
    <title>Agregar Paciente</title>

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
    flex-direction: column; /* FUERZA que los hijos vayan verticalmente */
}

.button-group {
    width: 100%;
    text-align: center;
    margin-top: 40px;  /* separa los botones de los inputs */
}
input, select {
    text-transform: uppercase;
}
    </style>
    
<script>
function validarDocumento(input) {
    // Solo números
    input.value = input.value.replace(/[^0-9]/g, '');

    // Máximo 8 caracteres
    if (input.value.length > 8) {
        input.value = input.value.slice(0, 8);
    }
}
function validarTelefono(input) {
    // Solo números
    input.value = input.value.replace(/[^0-9]/g, '');

    // Máximo 10 caracteres
    if (input.value.length > 10) {
        input.value = input.value.slice(0, 10);
    }
}
function validarAfiliado(input) {
    // Solo números (sin límite por ahora)
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
    <h2 style="text-align:center;">Agregar Paciente</h2>
</div>

<form method="post" action="#" autocomplete="off">

<div class="input-group">

    <div class="form-field">
        <label>Nombre</label>
        <input type="text" name="nombre" required oninput="convertirMayusculas(this)">
    </div>

    <div class="form-field">
        <label>Apellido</label>
        <input type="text" name="apellido" required oninput="convertirMayusculas(this)">
    </div>

    <div class="form-field">
        <label>Género</label>
        <select name="genero" required>
            <option value="" disabled selected>Seleccione</option>
            <option value="Masculino">Masculino</option>
            <option value="Femenino">Femenino</option>
        </select>
    </div>

    <div class="form-field">
        <label>Documento</label>
        <input type="text" name="documento" oninput="validarDocumento(this)" required>
    </div>

    <div class="form-field">
        <label>Fecha Nacimiento</label>
        <input type="date" name="fecha_nacimiento" required>
    </div>

    <div class="form-field">
        <label>Teléfono</label>
        <input type="text" name="telefono" oninput="validarTelefono(this)" required>
    </div>

    <div class="form-field">
        <label>Correo</label>
        <input type="email" name="correo" oninput="convertirMayusculas(this)">
    </div>

    <div class="form-field">
        <label>Observaciones</label>
        <input type="text" name="observaciones" oninput="convertirMayusculas(this)">
    </div>

    <div class="form-field">
        <label>Localidad</label>
        <select name="localidad" required>
            <option value="" disabled selected>Seleccione</option>
            <?php
            $result = $conexion->query("SELECT id_localidad, nombre_localidad FROM localidades");
            while ($row = $result->fetch_object()) {
                echo "<option value='$row->id_localidad'>$row->nombre_localidad</option>";
            }
            ?>
        </select>
    </div>

    <div class="form-field">
        <label>Dirección</label>
        <input type="text" name="direccion" required oninput="convertirMayusculas(this)">
    </div>

    <div class="form-field">
        <label>Tipo de Sangre</label>
        <select name="tipo_sangre" required>
            <option value="" disabled selected>Seleccione</option>
            <option>A+</option><option>A-</option><option>B+</option><option>B-</option>
            <option>AB+</option><option>AB-</option><option>O+</option><option>O-</option>
        </select>
    </div>

    <div class="form-field">
        <label>Obra Social</label>
        <select name="obra_social" required>
            <option value="" disabled selected>Seleccione</option>
            <?php
            $result = $conexion->query("SELECT id_obrasocial, nombre_obrasocial FROM obras_sociales");
            while ($row = $result->fetch_object()) {
                echo "<option value='$row->id_obrasocial'>$row->nombre_obrasocial</option>";
            }
            ?>
        </select>
    </div>

    <div class="form-field">
        <label>Nº Afiliado</label>
        <input type="text" name="nro_afiliado" oninput="validarAfiliado(this)">
    </div>

</div>

<div class="button-group">
    <button type="submit" name="enviar" value="ok" class="form-container-button">Guardar</button>
    <a href="pacientes.php" class="form-container-button">Cancelar</a>
</div>

<?php include("send.php"); ?>

</form>
</section>
</main>

<footer>
    <h4>SGOE - Sistema de Gestión Odontológico Esperanza - Creado por Dilkin, Oscar y De Matos Esteban</h4>
</footer>
</body>
</html>