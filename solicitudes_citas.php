<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include("conexion_db.php");
include("conexion.php");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./styles/pacientes.css">
    <link rel="stylesheet" href="./styles/citas.css">
    <title>Solicitudes de Citas</title>
<style>
.tabla-containerr {
    overflow-y: auto;
    border: 1px solid #ccc;
}
.content-containerr{
height:auto;
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
    <a href="citas.php" style="background-color: #FFB703;">Citas</a>
    <a href="gestion.php">Gestión</a>
    <a href="logout.php">Cerrar sesión</a>
</nav>

<main>
    <div>
        <a href="citas.php">Volver</a>
    </div>

    <section class="content-containerr">
        
            <h3>Solicitudes de Citas</h3>
            <div class="tabla-containerr">

                <?php
                $conexion = new mysqli("localhost", "c2821234_consult", "Estebanquito2025ISTU", "c2821234_consult");
                if ($conexion->connect_error) {
                    die("Conexión fallida: " . $conexion->connect_error);
                }

                // ✅ JOIN para traer la hora real desde horarios
                $query = "SELECT sc.id_solicitud, sc.fecha_solicitud, h.hora AS hora_real,
                                 sc.motivo_solicitud, p.id_paciente, p.nombre, p.apellido, p.correo,
                                 sc.id_horario
                          FROM solicitudes_citas sc
                          JOIN pacientes p ON sc.id_paciente = p.id_paciente
                          JOIN horarios h ON sc.id_horario = h.id
                          ORDER BY sc.fecha_solicitud DESC, h.hora ASC";

                $result = $conexion->query($query);

                echo "<table class='registros'>";
                echo "<thead><tr><th>Fecha</th><th>Hora</th><th>Paciente</th><th>Motivo</th><th>Acciones</th></tr></thead>";
                echo "<tbody>";

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['fecha_solicitud']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['hora_real']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['nombre'] . " " . $row['apellido']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['motivo_solicitud']) . "</td>";
                        echo "<td>";

                        // ✅ Confirmar
                        echo "<a href='solicitudes_citas.php?confirmar_solicitud=" . $row['id_solicitud'] . "' class='confirm-link'
                                onclick='return confirm(\"¿Estás seguro de confirmar esta solicitud?\");'>Confirmar</a> ";

                        // ✅ Reprogramar
                        echo "<a href='solicitudes_citas.php?reprogramar_solicitud=" . $row['id_solicitud'] . "&confirmar=1'
        class='reprogramar-link'
        onclick='return confirm(\"¿Confirmar la reprogramación de esta cita? El horario quedará bloqueado.\");'>
        Reprogramar
      </a> ";



                        // ✅ Eliminar
                        echo "<a href='solicitudes_citas.php?borrar_solicitud=" . $row['id_solicitud'] . "' class='delete-link'
                                onclick='return confirm(\"¿Estás seguro de eliminar esta solicitud?\");'>Eliminar</a>";

                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No hay solicitudes de citas.</td></tr>";
                }

                echo "</tbody></table>";

                /* ==============================
                      CONFIRMAR SOLICITUD
                =============================== */
                if (isset($_GET['confirmar_solicitud'])) {

                    $id_solicitud = intval($_GET['confirmar_solicitud']);

                    $solicitud = $conexion->query("
                        SELECT sc.id_horario, sc.fecha_solicitud, sc.motivo_solicitud,
                               p.id_paciente, p.nombre, p.apellido, p.correo,
                               h.hora AS hora_real
                        FROM solicitudes_citas sc
                        JOIN pacientes p ON sc.id_paciente = p.id_paciente
                        JOIN horarios h ON sc.id_horario = h.id
                        WHERE sc.id_solicitud = '$id_solicitud'
                    ")->fetch_assoc();

                    if ($solicitud) {

                        $id_horario = $solicitud['id_horario'];
                        $id_paciente = $solicitud['id_paciente'];
                        $fecha = $solicitud['fecha_solicitud'];
                        $hora_real = $solicitud['hora_real'];
                        $notas = $conexion->real_escape_string($solicitud['motivo_solicitud']);

                        // Verificar si ya existe turno en ese horario
                        $check = $conexion->query("SELECT id FROM citas WHERE fecha = '$fecha' AND id_horario = '$id_horario'");
                        if ($check->num_rows > 0) {
                            echo "<script>alert('Error: ya existe un turno en ese horario.');</script>";
                        } else {

                            $conexion->query("
                                INSERT INTO citas (id_paciente, fecha, id_horario, notas, creado_en)
                                VALUES ('$id_paciente', '$fecha', '$id_horario', '$notas', NOW())
                            ");

                            $conexion->query("DELETE FROM solicitudes_citas WHERE id_solicitud = '$id_solicitud'");

                            // ✅ ENVÍO CORRECTO DE PARÁMETROS A enviar_correo.php
                            echo "<script>
                                    if (confirm('Turno agendado exitosamente. ¿Desea enviar un correo de confirmación?')) {
                                        window.location.href = 'enviar_correo.php?id_solicitud=$id_solicitud&nombre=" . urlencode($solicitud['nombre'] . " " . $solicitud['apellido']) . "&correo=" . urlencode($solicitud['correo']) . "&fecha=$fecha&hora=$hora_real&motivo=" . urlencode($notas) . "';
                                    } else {
                                        window.location.href = 'solicitudes_citas.php';
                                    }
                                  </script>";
                        }
                    }
                }

                /* ==============================
                      REPROGRAMAR SOLICITUD
                =============================== */
                if (isset($_GET['reprogramar_solicitud']) && isset($_GET['confirmar'])) {


                    $id_solicitud = intval($_GET['reprogramar_solicitud']);

                    $solicitud = $conexion->query("
                        SELECT sc.id_horario, sc.fecha_solicitud, sc.motivo_solicitud,
                               p.nombre, p.apellido, p.correo,
                               h.hora AS hora_real
                        FROM solicitudes_citas sc
                        JOIN pacientes p ON sc.id_paciente = p.id_paciente
                        JOIN horarios h ON sc.id_horario = h.id
                        WHERE sc.id_solicitud = '$id_solicitud'
                    ")->fetch_assoc();

                    if ($solicitud) {

                        $id_horario = $solicitud['id_horario'];
                        $fecha = $solicitud['fecha_solicitud'];
                        $hora_real = $solicitud['hora_real'];
                        $notas = $conexion->real_escape_string("Bloqueado por reprogramación: " . $solicitud['motivo_solicitud']);

                        // Bloqueo del horario
                        $conexion->query("
                            INSERT INTO citas (id_paciente, fecha, id_horario, notas, creado_en, reprogramado)
                            VALUES (NULL, '$fecha', '$id_horario', '$notas', NOW(), 1)
                        ");

                        $conexion->query("DELETE FROM solicitudes_citas WHERE id_solicitud = '$id_solicitud'");

                        // ✅ ENVÍA EMAIL DE REPROGRAMACIÓN CON DATOS COMPLETOS
                        echo "<script>
    if (confirm('La cita fue reprogramada correctamente. ¿Desea enviar el correo de reprogramación al paciente?')) {
        window.location.href = 'enviar_reprogramacion.php?nombre=" . urlencode($solicitud['nombre'] . " " . $solicitud['apellido']) . "&correo=" . urlencode($solicitud['correo']) . "&fecha=$fecha&hora=$hora_real';
    } else {
        window.location.href = 'solicitudes_citas.php';
    }
</script>";


                    }
                }

                /* ==============================
                      ELIMINAR SOLICITUD
                =============================== */
                if (isset($_GET['borrar_solicitud'])) {

                    $id_solicitud = intval($_GET['borrar_solicitud']);
                    $conexion->query("DELETE FROM solicitudes_citas WHERE id_solicitud = '$id_solicitud'");

                    echo "<script>
                            alert('Solicitud eliminada con éxito.');
                            window.location.href='solicitudes_citas.php';
                          </script>";
                }

                $conexion->close();
                ?>
            </div>
        
    </section>
</main>

<footer>
    <h4>SGOE - Sistema de Gestión Odontológico Esperanza - Creado por Dilkin, Oscar y De Matos Esteban</h4>
</footer>

<script>
(function () {
    const contenedor = document.querySelector('.tabla-containerr');
    if (!contenedor) return;

    const altoPantalla = window.screen.height;

    let altoTabla;

    if (altoPantalla >= 1080) {
        // Pantallas grandes
        altoTabla = 700;
    } else {
        // Pantallas chicas (720 aprox)
        altoTabla = 455;
    }

    contenedor.style.height = altoTabla + "px";
})();
</script>

</body>
</html>