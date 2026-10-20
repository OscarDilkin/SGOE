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

$conexion = new mysqli("localhost", "c2821234_consult", "Estebanquito2025ISTU", "c2821234_consult");
if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}

/* ==========================
   HABILITAR / DESHABILITAR
========================== */
if (isset($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    $estado = $conexion->query("SELECT disponible FROM horarios WHERE id = $id")->fetch_assoc();
    $nuevo_estado = $estado['disponible'] == 1 ? 0 : 1;
    $conexion->query("UPDATE horarios SET disponible = $nuevo_estado WHERE id = $id");
    header("Location: gestionar_horarios.php");
    exit();
}

/* ==========================
   AGREGAR NUEVO HORARIO
========================== */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["nueva_hora"])) {

    $hora = $_POST["nueva_hora"];

    if (preg_match("/^(?:[01]\d|2[0-3]):(00|30)$/", $hora)) {

        $check = $conexion->query("SELECT id FROM horarios WHERE hora = '$hora'");
        if ($check->num_rows == 0) {
            $conexion->query("INSERT INTO horarios (hora, disponible) VALUES ('$hora', 1)");
        } else {
            echo "<script>alert('Ese horario ya existe');</script>";
        }

    } else {
        echo "<script>alert('Solo se permiten horarios cada 30 minutos entre 00:00 y 23:30');</script>";
    }

    header("Location: gestionar_horarios.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Horarios</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./styles/pacientes.css">
    <link rel="stylesheet" href="./styles/citas.css">

<style>
.content-container {
    display: flex !important;
    justify-content: center;
}

.columna-izquierda {
    width: 100% !important;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.form-horario {
    margin-bottom: 20px;
}

.tabla-container {
    width: auto !important;
    display: flex;
    justify-content: center;
    max-height: 500px;       /* ✅ ALTURA MÁXIMA */
    overflow-y: auto;       /* ✅ SCROLL VERTICAL */
}

table.registros {
    width: 600px !important;
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
    <a href="reportes.php">Evoluciones</a>
    <a href="citas.php">Citas</a>
    <a href="gestion.php" style="background-color: #FFB703;">Gestión</a>
    <a href="logout.php">Cerrar sesión</a>
</nav>

<main>
    <div>
        <a href="gestion.php">Volver</a>
    </div>

    <div class="columna-izquierda" style="width: 100%;">
        <h3 style="text-align:center;">Gestión de Horarios</h3>

        <!-- ✅ TABLA CON SCROLL -->
        <div class="tabla-container">
            <?php
            $result = $conexion->query("SELECT * FROM horarios ORDER BY hora ASC");

            echo "<table class='registros'>";
            echo "<thead>
                    <tr>
                        <th>Horario</th>
                        <th>Acción</th>
                    </tr>
                  </thead>";
            echo "<tbody>";

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {

                    $boton = $row["disponible"] == 1 ? "Deshabilitar" : "Habilitar";
                    $clase = $row["disponible"] == 1 ? "delete-link" : "confirm-link";

                    echo "<tr>";
                    echo "<td>{$row['hora']}</td>";
                    echo "<td>
                            <a href='gestionar_horarios.php?toggle={$row['id']}' class='{$clase}'
                               onclick='return confirm(\"¿Confirmar acción sobre este horario?\");'>
                               {$boton}
                            </a>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='2'>No hay horarios cargados.</td></tr>";
            }

            echo "</tbody></table>";
            ?>
        </div>
    </div>
</main>

<footer>
    <h4>SGOE - Sistema de Gestión Odontológico Esperanza - Creado por Dilkin, Oscar y De Matos Esteban</h4>
</footer>

</body>
</html>