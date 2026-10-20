<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

include("conexion_db.php");

$id_paciente = $_GET['id_paciente'] ?? '';

$fecha = date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Evolución</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="./styles/pacientes.css">
    <link rel="stylesheet" href="./styles/agregar_paciente.css">

    <style>
        .input-group {
            display: grid;
align-items: center;
            grid-template-columns: 1fr;
            gap: 15px 25px;
            width: 50%;
 text-transform: uppercase;
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

        .form-field input {
            width: 100%;
height:50px;
        }

form{
    display: flex;
    flex-direction: column;
    min-height: 350px; /* ajustá si querés más alto */
}

.button-group{
    margin-top: auto;     /* empuja los botones hacia abajo */
    text-align: center;  /* los centra */
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
    <a href="reportes.php">Reportes</a>
    <a href="citas.php">Citas</a>
    <a href="gestion.php">Gestión</a>
    <a href="logout.php">Cerrar sesión</a>
</nav>

<main>
<section>

    <div>
        <a href="historiaclinica.php?id_paciente=<?= urlencode($id_paciente) ?>">Volver</a>
    </div>

    <div>
        <h2 style="text-align:center;">Agregar Evolución</h2>
    </div>

    <form method="POST" autocomplete="off">

        <input type="hidden" name="fecha" value="<?= $fecha ?>">
        <input type="hidden" name="id_paciente" value="<?= $id_paciente ?>">

        <div class="input-group">

            <div class="form-field">
                <label for="motivo">Motivo:</label>
                <input type="text" name="motivo" placeholder="Motivo de la consulta" class="form-input" required oninput="convertirMayusculas(this)">
            </div>

            <div class="form-field">
                <label for="tratamiento">Tratamiento:</label>
                <input type="text" name="tratamiento" placeholder="Tratamiento realizado" class="form-input" required oninput="convertirMayusculas(this)">
            </div>

        </div>

        <div class="button-group">
            <button type="submit" name="btnhistoria" value="ok" class="form-container-button">
                Guardar Evolución
            </button>

            <a href="historiaclinica.php?id_paciente=<?= urlencode($id_paciente) ?>" class="form-container-button">
                Cancelar
            </a>
        </div>

        <?php include("sendhistoria.php"); ?>

    </form>

</section>
</main>

<footer>
    <h4>SGOE - Sistema de Gestión Odontológico Esperanza - Creado por Dilkin, Oscar y De Matos Esteban</h4>
</footer>

<script>
<?php if (isset($_SESSION['mensaje'])) { ?>
    alert("<?= $_SESSION['mensaje'] ?>");
<?php unset($_SESSION['mensaje']); } ?>

function convertirMayusculas(input) {
    input.value = input.value.toUpperCase();
}

</script>

</body>
</html>