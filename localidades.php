<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

include("conexion_db.php");
include("conexion.php");

// ===============================
// AGREGAR LOCALIDAD (SIN DUPLICADOS)
// ===============================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['agregar_localidad'])) {
    $conexion = new mysqli("localhost", "c2821234_consult", "Estebanquito2025ISTU", "c2821234_consult");
    if ($conexion->connect_error) die("Conexión fallida: " . $conexion->connect_error);

    $nombre_localidad = trim($conexion->real_escape_string($_POST['nombre_localidad']));

    if (!empty($nombre_localidad)) {

        // 🔎 Verificar si la localidad ya existe
        $verificar = $conexion->query("
            SELECT id_localidad 
            FROM localidades 
            WHERE nombre_localidad = '$nombre_localidad'
            LIMIT 1
        ");

        if ($verificar->num_rows > 0) {
            // ❌ Ya existe
            echo "<script>
                alert('La localidad ya existe y no se puede duplicar.');
                window.location.replace('localidades.php');
            </script>";
        } else {
            // ✅ No existe, se inserta
            $sql = "INSERT INTO localidades (nombre_localidad) VALUES ('$nombre_localidad')";
            if ($conexion->query($sql) === TRUE) {
                echo "<script>alert('Localidad agregada con éxito.');</script>";
            } else {
                echo "<script>alert('Error al agregar localidad.');</script>";
            }
        }

    } else {
        echo "<script>alert('El nombre de la localidad no puede estar vacío.');</script>";
    }

    $conexion->close();
}

// ===============================
// ELIMINAR LOCALIDAD (VALIDADO)
// ===============================
if (isset($_GET['borrar_localidad'])) {
    $conexion = new mysqli("localhost", "c2821234_consult", "Estebanquito2025ISTU", "c2821234_consult");
    if ($conexion->connect_error) die("Conexión fallida: " . $conexion->connect_error);

    $id_localidad = $conexion->real_escape_string($_GET['borrar_localidad']);

    // Verificar si hay pacientes asociados
    $consulta = $conexion->query("
        SELECT COUNT(*) AS total
        FROM pacientes
        WHERE localidad = '$id_localidad'
    ");

    $fila = $consulta->fetch_assoc();

    if ($fila['total'] > 0) {
        echo "<script>
            alert('No se puede eliminar la localidad porque hay pacientes asociados.');
            window.location.replace('localidades.php');
        </script>";
    } else {
        $delete_query = "DELETE FROM localidades WHERE id_localidad = '$id_localidad'";
        if ($conexion->query($delete_query) === TRUE) {
            echo "<script>
                alert('Localidad eliminada con éxito.');
                window.location.replace('localidades.php');
            </script>";
        } else {
            echo "<script>alert('Error al eliminar la localidad.');</script>";
        }
    }

    $conexion->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Localidades</title>
    <link rel="stylesheet" href="./styles/pacientes.css">
    <link rel="stylesheet" href="./styles/citas.css">
    <link rel="stylesheet" href="./styles/gestion.css">
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
        <h3 class="gestion-titulo">Localidades</h3>

        <div class="gestion-tabla-container">
            <?php
            $conexion = new mysqli("localhost", "c2821234_consult", "Estebanquito2025ISTU", "c2821234_consult");
            if ($conexion->connect_error) die("Conexión fallida: " . $conexion->connect_error);

            $query = "SELECT id_localidad, nombre_localidad FROM localidades ORDER BY nombre_localidad";
            $result = $conexion->query($query);

            echo "<table class='gestion-tabla'>";
            echo "<thead><tr><th>Localidad</th><th>Acción</th></tr></thead><tbody>";

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['nombre_localidad']) . "</td>";
                    echo "<td>
                        <a href='localidades.php?borrar_localidad={$row['id_localidad']}'
                           class='gestion-eliminar'
                           onclick='return confirm(\"¿Estás seguro?\");'>
                           Eliminar
                        </a>
                    </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='2'>No hay localidades registradas.</td></tr>";
            }

            echo "</tbody></table>";
            $conexion->close();
            ?>
        </div>

        <h3 class="gestion-subtitulo">Agregar Localidad</h3>
        <form method="POST" action="localidades.php" class="gestion-form">
            <input type="text"
                   id="nombre_localidad"
                   name="nombre_localidad"
                   placeholder="Nombre de la localidad"
                   required
                   oninput="convertirMayusculas(this)"
                   style="text-transform: uppercase;">
            <input type="submit" name="agregar_localidad" value="Agregar">
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