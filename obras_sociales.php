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

// ===============================
// AGREGAR OBRA SOCIAL (SIN DUPLICADOS)
// ===============================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['agregar_obra_social'])) {
    $conexion = new mysqli("localhost", "c2821234_consult", "Estebanquito2025ISTU", "c2821234_consult");
    if ($conexion->connect_error) die("Conexión fallida: " . $conexion->connect_error);

    $nombre_obrasocial = trim($conexion->real_escape_string($_POST['nombre_obrasocial']));

    if (!empty($nombre_obrasocial)) {

        // 🔎 Verificar si ya existe la obra social
        $verificar = $conexion->query("
            SELECT id_obrasocial 
            FROM obras_sociales 
            WHERE nombre_obrasocial = '$nombre_obrasocial'
            LIMIT 1
        ");

        if ($verificar->num_rows > 0) {
            // ❌ Ya existe
            echo "<script>
                alert('La obra social ya existe y no se puede duplicar.');
                window.location.replace('obras_sociales.php');
            </script>";
        } else {
            // ✅ No existe, se inserta
            $sql = "INSERT INTO obras_sociales (nombre_obrasocial) VALUES ('$nombre_obrasocial')";
            if ($conexion->query($sql) === TRUE) {
                echo "<script>alert('Obra social agregada con éxito.');</script>";
            } else {
                echo "<script>alert('Error al agregar obra social.');</script>";
            }
        }

    } else {
        echo "<script>alert('El nombre de la obra social no puede estar vacío.');</script>";
    }

    $conexion->close();
}

// ===============================
// ELIMINAR OBRA SOCIAL (VALIDADO)
// ===============================
if (isset($_GET['borrar_obra_social'])) {
    $conexion = new mysqli("localhost", "c2821234_consult", "Estebanquito2025ISTU", "c2821234_consult");
    if ($conexion->connect_error) die("Conexión fallida: " . $conexion->connect_error);

    $id_obrasocial = $conexion->real_escape_string($_GET['borrar_obra_social']);

    // Verificar si hay pacientes asociados
    $consulta = $conexion->query("
        SELECT COUNT(*) AS total 
        FROM pacientes 
        WHERE obra_social = '$id_obrasocial'
    ");

    $fila = $consulta->fetch_assoc();

    if ($fila['total'] > 0) {
        echo "<script>
            alert('No se puede eliminar la obra social porque hay pacientes asociados.');
            window.location.replace('obras_sociales.php');
        </script>";
    } else {
        $delete_query = "DELETE FROM obras_sociales WHERE id_obrasocial = '$id_obrasocial'";
        if ($conexion->query($delete_query) === TRUE) {
            echo "<script>
                alert('Obra social eliminada con éxito.');
                window.location.replace('obras_sociales.php');
            </script>";
        } else {
            echo "<script>alert('Error al eliminar la obra social.');</script>";
        }
    }

    $conexion->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./styles/pacientes.css">
    <link rel="stylesheet" href="./styles/citas.css">
    <link rel="stylesheet" href="./styles/gestion.css">
    <title>Gestión de Obras Sociales</title>
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
    <div><a href="gestion.php">Volver</a></div>

    <section class="gestion-container">
        <h3 class="gestion-titulo">Obras Sociales</h3>

        <div class="gestion-tabla-container">
            <?php
            $conexion = new mysqli("localhost", "c2821234_consult", "Estebanquito2025ISTU", "c2821234_consult");
            if ($conexion->connect_error) die("Conexión fallida: " . $conexion->connect_error);

            $query = "SELECT id_obrasocial, nombre_obrasocial FROM obras_sociales ORDER BY nombre_obrasocial";
            $result = $conexion->query($query);

            echo "<table class='gestion-tabla'>";
            echo "<thead>
                    <tr>
                        <th>Obra Social</th>
                        <th>Acción</th>
                    </tr>
                  </thead>
                  <tbody>";

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['nombre_obrasocial']) . "</td>";
                    echo "<td>
                        <a href='obras_sociales.php?borrar_obra_social={$row['id_obrasocial']}'
                           class='gestion-eliminar'
                           onclick='return confirm(\"¿Estás seguro de eliminar esta obra social?\");'>
                           Eliminar
                        </a>
                    </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='2'>No hay obras sociales registradas.</td></tr>";
            }

            echo "</tbody></table>";
            $conexion->close();
            ?>
        </div>

        <h3 class="gestion-subtitulo">Agregar Obra Social</h3>

        <form method="POST" action="obras_sociales.php" class="gestion-form">
            <input type="text"
                   id="nombre_obrasocial"
                   name="nombre_obrasocial"
                   placeholder="Nombre de la obra social"
                   required
                   oninput="convertirMayusculas(this)"
                   style="text-transform: uppercase;">
            <input type="submit" name="agregar_obra_social" value="Agregar">
        </form>
    </section>
</main>

<script>
function convertirMayusculas(input) {
    input.value = input.value.toUpperCase();
}
</script>

<footer>
    <h4>SGOE - Sistema de Gestión Odontológico Esperanza - Creado por Dilkin, Oscar y De Matos Esteban</h4>
</footer>

</body>
</html>