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
    <title>Citas</title>
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
        <a href="pacientes.php">Volver</a>
        <a href="solicitudes_citas.php">Solicitudes de citas</a>
    </div>

    <section class="content-container">

        <!-- ==================== COLUMNAS ==================== -->

        <!-- COLUMNA IZQUIERDA -->
        <div class="columna-izquierda">
            <h3>Citas Agendadas</h3>
            <form method="GET" action="citas.php" id="form-citas-agendadas" class="form-container">
                <input type="date" id="fecha_citas" name="fecha_citas"
                       value="<?php echo isset($_GET['fecha_citas']) ? htmlspecialchars($_GET['fecha_citas']) : date('Y-m-d'); ?>"
                       onchange="this.form.submit()" class="form-input">
            </form>

            <div class="tabla-container">
                <?php
                $fecha_citas = isset($_GET['fecha_citas']) ? $_GET['fecha_citas'] : date('Y-m-d');

                $conexion = new mysqli("localhost", "c2821234_consult", "Estebanquito2025ISTU", "c2821234_consult");
                if ($conexion->connect_error) { die("Conexión fallida: " . $conexion->connect_error); }

                // ✅ ahora se hace JOIN con horarios para obtener la hora desde id_horario
                $query = "SELECT t.id, t.fecha, h.hora AS hora_real, p.id_paciente, p.nombre, p.apellido, p.dni, t.notas, sc.motivo_solicitud
                          FROM citas t
                          JOIN pacientes p ON t.id_paciente = p.id_paciente
                          JOIN horarios h ON t.id_horario = h.id
                          LEFT JOIN solicitudes_citas sc ON t.id_paciente = sc.id_paciente
                          AND t.fecha = sc.fecha_solicitud
                          AND t.id_horario = sc.id_horario
                          WHERE t.fecha = '$fecha_citas'
                          ORDER BY h.hora";

                $result = $conexion->query($query);

                echo "<table class='registros'>";
                echo "<thead><tr><th>Hora</th><th>Paciente</th><th>Motivo</th><th>Acción</th></tr></thead>";
                echo "<tbody>";

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['hora_real']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['nombre'] . " " . $row['apellido']) . "</td>";

                        $motivo = !empty($row['notas']) ? htmlspecialchars($row['notas']) :
                                  (!empty($row['motivo_solicitud']) ? htmlspecialchars($row['motivo_solicitud']) : '-');

                        echo "<td>" . $motivo . "</td>";

                        echo "<td>
                                <a href='citas.php?borrar_turno=" . $row['id'] . "&fecha_citas=" . urlencode($fecha_citas) . "' class='delete-link'
                                   onclick='return confirm(\"¿Estás seguro de eliminar este turno?\");'>Eliminar</a>
                                <a href='historiaclinica.php?id_paciente=" . urlencode($row['id_paciente']) . "' class='history-link'>Historia</a>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No hay citas para esta fecha.</td></tr>";
                }

                echo "</tbody></table>";

                // ELIMINAR TURNO
                if (isset($_GET['borrar_turno'])) {
                    $id_turno = $conexion->real_escape_string($_GET['borrar_turno']);
                    $fecha_citas = $conexion->real_escape_string($_GET['fecha_citas']);
                    if ($conexion->query("DELETE FROM citas WHERE id = '$id_turno'") === TRUE) {
                        echo "<script>
                                alert('Turno borrado con éxito.');
                                window.location.replace('citas.php?fecha_citas=" . urlencode($fecha_citas) . "');
                              </script>";
                    }
                }

                $conexion->close();
                ?>
            </div>
        </div>


        <!-- COLUMNA DERECHA -->
        <div class="columna-derecha">
            <h3>Agendar Cita</h3>
            <form method="POST" action="citas.php" id="form-agendar" class="form-container">
                <?php
                $paciente_nombre = '';
                $id_paciente = '';
                $fecha_seleccionada = '';
                $notas = '';

                if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['confirmar_turno'])) {
                    $id_paciente = $_POST['id_paciente'];
                    $fecha_seleccionada = $_POST['fecha'];
                    $notas = $_POST['notas'];

                    if ($id_paciente) {
                        $conexion = new mysqli("localhost", "c2821234_consult", "Estebanquito2025ISTU", "c2821234_consult");
                        $q = $conexion->query("SELECT nombre, apellido FROM pacientes WHERE id_paciente = '$id_paciente'");
                        if ($q->num_rows > 0) {
                            $row = $q->fetch_assoc();
                            $paciente_nombre = htmlspecialchars($row['nombre'] . ' ' . $row['apellido']);
                        }
                        $conexion->close();
                    }
                }
                ?>

                <input type="text" id="paciente" name="paciente" oninput="buscarPaciente()"
                       placeholder="Nombre, apellido, DNI o teléfono..."
                       value="<?php echo $paciente_nombre; ?>" required class="form-input">

                <input type="hidden" id="id_paciente_hidden" name="id_paciente" value="<?php echo $id_paciente; ?>">
                <div id="resultados" class="resultados"></div>

                <input type="text" id="notas" name="notas" class="form-input"
                       placeholder="Notas o motivo de la cita..."
                       value="<?php echo htmlspecialchars($notas); ?>" style="text-transform: uppercase;" oninput="convertirMayusculas(this)">

                <input type="date" id="fecha" name="fecha" onchange="mostrarHorarios()"
                       value="<?php echo $fecha_seleccionada; ?>" required class="form-input">

                <div class="horarios-disponibles" id="horarios-disponibles">
                    <?php
                    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['fecha']) && !isset($_POST['confirmar_turno'])) {

                        $conexion = new mysqli("localhost", "c2821234_consult", "Estebanquito2025ISTU", "c2821234_consult");

                        $fecha = $conexion->real_escape_string($_POST['fecha']);
                        $reservados = $conexion->query("SELECT id_horario FROM citas WHERE fecha = '$fecha'");

                        $bloqueados = $conexion->query("SELECT id_horario FROM bloqueos_horarios WHERE fecha = '$fecha'");

                        $ocupados = [];
                        while ($r = $reservados->fetch_assoc()) { $ocupados[] = $r['id_horario']; }
                        while ($b = $bloqueados->fetch_assoc()) { $ocupados[] = $b['id_horario']; }

                        $result = $conexion->query("SELECT id, hora FROM horarios WHERE disponible = 1 ORDER BY hora");

                        $horarios_manana = [];
                        $horarios_tarde = [];

                        while ($row = $result->fetch_assoc()) {
                            if (!in_array($row['id'], $ocupados)) {
                                if (strtotime($row['hora']) < strtotime('13:00')) {
                                    $horarios_manana[] = $row;
                                } else {
                                    $horarios_tarde[] = $row;
                                }
                            }
                        }

                        echo "<input type='hidden' name='id_paciente' value='$id_paciente'>";
                        echo "<input type='hidden' name='fecha' value='$fecha'>";
                        echo "<input type='hidden' name='notas' value='" . htmlspecialchars($notas) . "'>";

                        if (!empty($horarios_manana) || !empty($horarios_tarde)) {
                            echo "<div class='columna-horarios'><strong>Mañana</strong>";
                            foreach ($horarios_manana as $h) {
                                echo "<label class='horario-label'>
                                        <input type='radio' name='id_horario' value='{$h['id']}' required class='horario-radio'>
                                        {$h['hora']}
                                      </label>";
                            }
                            echo "</div>";

                            echo "<div class='columna-horarios'><strong>Tarde</strong>";
                            foreach ($horarios_tarde as $h) {
                                echo "<label class='horario-label'>
                                        <input type='radio' name='id_horario' value='{$h['id']}' required class='horario-radio'>
                                        {$h['hora']}
                                      </label>";
                            }
                            echo "</div>";
                        } else {
                            echo "<p>No hay horarios disponibles para esta fecha.</p>";
                        }

                        $conexion->close();
                    }
                    ?>
                </div>

                <?php
                if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['fecha']) && !isset($_POST['confirmar_turno'])) {
                    echo "<input type='submit' class='button' name='confirmar_turno' value='Confirmar turno'>";
                }
                ?>
            </form>

            <?php
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirmar_turno'])) {

                $conexion = new mysqli("localhost", "c2821234_consult", "Estebanquito2025ISTU", "c2821234_consult");

                $fecha = $_POST['fecha'];
                $id_horario = $_POST['id_horario'];
                $id_paciente = $_POST['id_paciente'];
                $notas = $_POST['notas'];

                // Verificar disponibilidad
                $consulta = $conexion->query("SELECT * FROM citas WHERE fecha = '$fecha' AND id_horario = '$id_horario'");
                if ($consulta->num_rows > 0) {
                    echo "<script>alert('Error: Ya existe un turno reservado para esa fecha y horario.');</script>";
                } else {
                    $sql = "INSERT INTO citas (id_paciente, fecha, id_horario, notas, creado_en)
                            VALUES ('$id_paciente', '$fecha', '$id_horario', '" . $conexion->real_escape_string($notas) . "', NOW())";

                    if ($conexion->query($sql) === TRUE) {
                        echo "<script>
                                alert('Turno agendado con éxito para el $fecha.');
                                window.location.replace('citas.php?fecha_citas=$fecha');
                              </script>";
                    }
                }
                $conexion->close();
            }
            ?>
        </div>
    </section>
</main>

<footer>
    <h4>SGOE - Sistema de Gestión Odontológico Esperanza - Creado por Dilkin, Oscar y De Matos Esteban</h4>
</footer>

<script>
    function buscarPaciente() {
        var input = document.getElementById("paciente").value;
        if (input.length > 2) {
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "buscar_paciente.php?query=" + input, true);
            xhr.onload = function () {
                if (xhr.status == 200) {
                    document.getElementById("resultados").innerHTML = xhr.responseText;
                }
            };
            xhr.send();
        } else {
            document.getElementById("resultados").innerHTML = '';
        }
    }

    function seleccionarPaciente(id, nombre) {
        document.getElementById("paciente").value = nombre;
        document.getElementById("id_paciente_hidden").value = id;
        document.getElementById("resultados").innerHTML = '';
        var fecha = document.getElementById("fecha").value;
        if (fecha) { mostrarHorarios(); }
    }

    function mostrarHorarios() {
        document.getElementById("form-agendar").submit();
    }
function convertirMayusculas(input) {
    input.value = input.value.toUpperCase();
}
</script>

</body>
</html>