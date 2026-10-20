<?php
session_start();
include("conexion.php");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultorio Odontológico Esperanza</title>
    <link rel="stylesheet" href="./estilospaginaDental.css">
    <link rel="icon" href="./assets/img/logo_simple.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
</head>
<body>
    <div id="preloader">
        <img src="./assets/img/preloader.gif" alt="Preloader">
    </div>

    <header>
        <nav class="navbar navbar-expand-md" style="background: linear-gradient(#023047, #219EBC)">
            <div class="container-fluid">
                <a class="navbar-brand" href="#">
                    <div class="logo">
                        <img src="./assets/img/logo.png" alt="Logo Consultorio" class="logo-nav">
                    </div>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link nav-pill" href="./paginaDental.php">Inicio</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-pill" href="#quienes-somos">Quién Soy</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-pill" href="#tratamientos">Tratamientos</a>
                        </li>
<li class="nav-item">
                            <a class="nav-link nav-pill" href="./cuidadoBucal.php">Cuidado Bucal</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-pill" href="#pacientes">Solicitar Cita</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-pill" href="#contacto">Contacto</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <div class="carousel-container">
        <div class="carousel">
            <img src="img/imagen1.gif" alt="Consultorio">
            <img src="img/imagen2.gif" alt="Sala de espera">
            <img src="img/imagen3.gif" alt="Entrada del consultorio">
        </div>
    </div>

    <section id="quienes-somos" class="seccion-scroll">
        <div class="quienes-somos-container">
            <h2>¿Quién Soy?</h2>
            <div class="intro-text">
                <p>Soy la Dra. Fabiana Nobs, y es un placer recibirlos en este espacio dedicado a cuidar su sonrisa y salud bucal. Con más de 15 años de experiencia como odontóloga en Puerto Esperanza, Misiones, mi pasión es ayudar a cada paciente a sentirse seguro y feliz con su sonrisa.<br><br>En la clínica odontológica, me dedico a ofrecer un <strong>trato cercano, profesional y personalizado</strong> a cada uno de mis pacientes, desde los más pequeños hasta los adultos. Comprendo que cada miembro de la familia tiene necesidades dentales únicas, por lo que proporciono un <strong>enfoque integral</strong> diseñado para garantizar una salud bucal óptima en todas las etapas de la vida.
<br>
Creo firmemente que una buena salud dental comienza con la <strong>educación y la prevención</strong>. Por eso, no solo ofrezco tratamientos de alta calidad, sino que también trabajo para empoderar a mis pacientes con el conocimiento y los hábitos necesarios para mantener sus sonrisas radiantes.<br>El consultorio odontológico está abierto al público en horario comercial, de 8:00hs a 12:00hs y de 16:00hs a 20hs. Para solicitudes de citas pueden comunicarse directamente al número de WhatsApp del consultorio a través del ícono que aparece en el costado de la pantalla, o bien, si ya consurrieron alguna vez al consultorio pueden solicitar una cita directamente desde la sección <a href="#pacientes">Solicitar Cita</a></p>
           <div class="valores">
             <h3>¿Por qué elegirme?</h3>
            <ul>
                <li>
                    <strong>Atención personalizada</strong>
                    <p>Cada tratamiento se adapta a las necesidades específicas de los pacientes.</p>
                </li>
                <li>
                    <strong>Ambiente cálido y acogedor</strong>
                    <p>Me esfuerzo por hacer que cada visita sea una experiencia cómoda y agradable.</p>
                </li>
                <li>
                    <strong>Profesionales altamente capacitados</strong>
                    <p>Estoy comprometida con la excelencia y la formación continua.</p>
                </li>
                <li>
                    <strong>Enfoque preventivo</strong>
                    <p>Priorizo la educación dental para evitar problemas futuros.</p>
                </li>
            </ul>
           </div>
</div>
            <div class="mision-vision">
                <div class="mision">
                    <h3>Misión</h3>
                    <p>Brindar atención odontológica de excelencia, promoviendo la salud bucal y el bienestar de los pacientes con tratamientos de calidad, tecnología innovadora y un trato humano que genera confianza.</p>
                </div>
                <div class="vision">
                    <h3>Visión</h3>
                    <p>Ser el consultorio odontológico de referencia en Puerto Esperanza, reconocido por la atención personalizada, eficiencia administrativa y el uso de herramientas digitales que faciliten la experiencia de los pacientes.</p>
                </div>
            </div>
            <div class="valores">
                <h3>Valores</h3>
                <ul>
                    <li>
                        <strong>Calidad</strong>
                        <p>Ofrezco tratamientos precisos y confiables para garantizar tu salud bucal.</p>
                    </li>
                    <li>
                        <strong>Cercanía</strong>
                        <p>Trato a cada paciente con empatía, como parte de nuestra familia.</p>
                    </li>
                    <li>
                        <strong>Innovación</strong>
                        <p>Integro tecnología para optimizar la gestión y mejorar tu experiencia.</p>
                    </li>
                    <li>
                        <strong>Confianza</strong>
                        <p>Protejo tus datos y priorizo tu tranquilidad en cada visita.</p>
                    </li>
                </ul>
            </div>
        </div>
    </section>

    <section id="tratamientos" class="seccion-scroll">
        <h2>Tratamientos</h2>
        <div class="servicios-container">
            <p class="texto-servicios">La clínica ofrece un amplio abanico de tratamientos odontológicos, diseñados para atender todas las necesidades de los pacientes con tecnología avanzada y personal altamente cualificado. Entre los tratamientos se encuentran:</p>
            <a href="/tratamientos.php#implantes-dentales" class="servicio">Implantes Dentales<br>
                <img src="./assets/img/servicios/implantes.jpg" alt="Implantes Dentales">
            </a>
            <a href="/tratamientos.php#blanqueamiento-dental" class="servicio">Blanqueamiento Dental<br>
                <img src="./assets/img/servicios/blanqueamiento.jpg" alt="Blanqueamiento Dental">
            </a>
            <a href="/tratamientos.php#ortodoncia" class="servicio">Ortodoncia<br>
                <img src="./assets/img/servicios/ortodoncia.jpg" alt="Ortodoncia">
            </a>
            <a href="/tratamientos.php#extraccion-caries" class="servicio">Extracción de Caries<br>
                <img src="./assets/img/servicios/caries.jpg" alt="Extracción de Caries">
            </a>
            <a href="/tratamientos.php#extracciones-dentales" class="servicio">Extracciones Dentales<br>
                <img src="./assets/img/servicios/extracciones.jpg" alt="Extracciones Dentales">
            </a>
            <a href="/tratamientos.php#tratamiento-periodontal" class="servicio">Tratamiento Periodontal<br>
                <img src="./assets/img/servicios/periodontal.jpg" alt="Tratamiento Periodontal">
            </a>
            <a href="/tratamientos.php#limpiezas-sarro" class="servicio">Limpiezas de Sarro<br>
                <img src="./assets/img/servicios/sarro.jpg" alt="Limpieza de Sarro">
            </a>
            <a href="/tratamientos.php#tratamientos-conducto" class="servicio">Tratamientos de Conducto<br>
                <img src="./assets/img/servicios/conducto.jpg" alt="Tratamientos de Conducto">
            </a>
            <a href="/tratamientos.php#paladares-protesis" class="servicio">Paladares Prótesis<br>
                <img src="./assets/img/servicios/paladar.jpg" alt="Paladares">
            </a>
            <a href="/tratamientos.php#cirugias-dentales" class="servicio">Cirugías Dentales<br>
                <img src="./assets/img/servicios/cirugia.jpg" alt="Cirugías">
            </a>
        </div>
<a class="button" href="./tratamientos.php">Más información de tratamientos</a>
    </section>

    <section id="odontologa">
        <h2>Nuestra Odontóloga</h2>
        <div class="bio-container">
            <div class="bio-image">
                <img src="img/odontologa.jpeg" alt="Dra. Nobs">
            </div>
<div class="bio-text">
                <h3>Dra. Nobs Fabiana</h3>
                <p>Me formé en la <strong>Universidad Nacional del Nordeste</strong>, donde aprendí no solo las técnicas más avanzadas de la odontología, sino también la importancia de tratar a cada persona con cercanía y respeto. Desde entonces, me mantengo en constante formación para ofrecerles tratamientos modernos y efectivos, desde limpiezas y blanqueamientos hasta implantes y cirugías dentales. Mi objetivo es que cada visita sea una experiencia positiva, con soluciones a medida para lo que necesiten.<br><br>

Lo que más me motiva es ver el cambio en mis pacientes: no solo en su sonrisa, sino en su confianza y bienestar. Por eso, además de mi trabajo en el consultorio, me involucro en la comunidad, llevando charlas sobre cuidado bucal a escuelas locales y promoviendo la prevención como base de una boca sana.<br><br>

Cuando no estoy en el consultorio, disfruto de la belleza de Misiones, paseando por sus paisajes y compartiendo tiempo con mis dos hijas. Esa energía y calidez es la que traigo cada día al trabajo, para que se sientan como en casa.<br><br>

Estoy aquí para cuidar de ustedes con profesionalismo y cariño. ¡Los invito a conocerme y a dar el primer paso hacia una sonrisa que los haga brillar!</p>
            </div>
        </div>
    </section>

    <section id="testimonios">
        <h2>Experiencias de Nuestros Pacientes</h2>
        <div class="testimonios">
            <div class="paciente">
                <img src="./assets/img/personas/mujer3.jpg" alt="María" class="foto-circular">
                <p>"La atención es excelente, me sentí muy cómoda durante mi blanqueamiento."</p>
            </div>
            <div class="paciente">
                <img src="./assets/img/personas/hombre2.jpg" alt="Juan" class="foto-circular">
                <p>"Mi implante quedó perfecto, muy profesional."</p>
            </div>
            <div class="paciente">
                <img src="./assets/img/personas/mujer4.jpg" alt="Sofía" class="foto-circular">
                <p>"Llevé a mi hija y la experiencia fue increíble, muy recomendable."</p>
            </div>
<div class="paciente">
                <img src="./assets/img/personas/hombre5.jpg" alt="Sofía" class="foto-circular">
                <p>"Excelente servicio. Muy buena atención."</p>
            </div>
<div class="paciente">
                <img src="./assets/img/personas/mujer6.jpg" alt="Sofía" class="foto-circular">
                <p>"Quedé fascinado con mis dientes extra blancos. Muchas gracias."</p>
            </div>
        </div>
    </section>


    <section id="pacientes" class="seccion-scroll">
        <div class="form-container">
            <h2>Solicitar Cita</h2>
            <h4>(Solo pacientes registrados)</h4>
            <form method="POST" action="#pacientes" autocomplete="off" name="solicitar_turno" class="form-container">
                <div class="input-group">
                    <input type="text" name="documento" placeholder="Documento" class="form-input" value="<?php echo isset($_POST['documento']) ? htmlspecialchars($_POST['documento']) : ''; ?>" required>
                    <textarea name="motivo" placeholder="Motivo de la cita" class="form-input" required oninput="convertirMayusculas(this)" style="text-transform: uppercase;"><?php echo isset($_POST['motivo']) ? htmlspecialchars($_POST['motivo']) : ''; ?></textarea>

                    <input type="date" name="fecha" id="fecha" class="form-input"
                        value="<?php echo isset($_POST['fecha']) ? htmlspecialchars($_POST['fecha']) : ''; ?>"
                        min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                        max="<?php echo date('Y-m-d', strtotime('+20 days')); ?>" required>

                    <div class="horarios-disponibles" id="horarios-disponibles">

                    <?php
                    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['fecha']) && !isset($_POST['solicitar'])) {

                        $fecha = $conexion->real_escape_string($_POST['fecha']);

                        $dia_bloqueado = $conexion->query("SELECT id_horario FROM bloqueos_horarios WHERE fecha = '$fecha' AND id_horario IS NULL");

                        if ($dia_bloqueado->num_rows > 0) {
                            echo "<p class='texto-alerta-roja'>No hay citas disponibles para esta fecha.</p>";
                        } else {

                            $result = $conexion->query("SELECT id, hora, disponible FROM horarios where disponible = 1");

                            $reservados = $conexion->query("SELECT id_horario FROM citas WHERE fecha = '$fecha'");
                            $horas_reservadas = [];
                            while ($row = $reservados->fetch_assoc()) {
                                $horas_reservadas[] = $row['id_horario'];
                            }

                            $bloqueados = $conexion->query("SELECT id_horario FROM bloqueos_horarios WHERE fecha = '$fecha' AND id_horario IS NOT NULL");
                            $horas_bloqueadas = [];
                            while ($row = $bloqueados->fetch_assoc()) {
                                $horas_bloqueadas[] = $row['id_horario'];
                            }

                            $horarios_manana = [];
                            $horarios_tarde = [];

                            while ($row = $result->fetch_assoc()) {
    $id = $row['id'];
    $hora = $row['hora'];

    if (!in_array($id, $horas_reservadas) && !in_array($id, $horas_bloqueadas)) {
        if (strtotime($hora) < strtotime('12:00')) {
            $horarios_manana[] = ['id' => $id, 'hora' => $hora];
        } else {
            $horarios_tarde[] = ['id' => $id, 'hora' => $hora];
        }
    }
}

                            if (!empty($horarios_manana) || !empty($horarios_tarde)) {
                                echo "<div class='columna-horarios'><strong>Mañana</strong>";
                                foreach ($horarios_manana as $h) {
    echo "<label class='horario-label'><input type='radio' name='id_horario' value='{$h['id']}' required class='horario-radio'> {$h['hora']}</label>";
}
                                echo "</div>";

                                echo "<div class='columna-horarios'><strong>Tarde</strong>";
                                foreach ($horarios_tarde as $h) {
    echo "<label class='horario-label'><input type='radio' name='id_horario' value='{$h['id']}' required class='horario-radio'> {$h['hora']}</label>";
}
                                echo "</div>";
                            } else {
                                echo "<p>No hay horarios disponibles para esta fecha.</p>";
                            }
                        }
                    }
                    ?>

                    </div>
                </div>

                <div class="button-group">
                    <?php
                    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['fecha']) && !isset($_POST['solicitar']) && (!empty($horarios_manana) || !empty($horarios_tarde))) {
                        echo "<button type='submit' name='solicitar' value='ok'>Solicitar Cita</button>";
                    }
                    ?>
                </div>

                <?php
                if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['solicitar'])) {
                    $documento = $conexion->real_escape_string($_POST['documento']);
                    $motivo = $conexion->real_escape_string($_POST['motivo']);
                    $fecha = $conexion->real_escape_string($_POST['fecha']);
                    $id_horario = intval($_POST['id_horario']);


























                    if (!empty($_POST['documento']) && !empty($_POST['motivo']) && !empty($fecha) && !empty($id_horario)) {
                        $verificar_doc = "SELECT id_paciente FROM pacientes WHERE dni = '$documento'";
                        $resultado_verificacion = $conexion->query($verificar_doc);

                        if ($resultado_verificacion->num_rows > 0) {
                            $row = $resultado_verificacion->fetch_assoc();
                            $id_paciente = $row['id_paciente'];

                            $current_date = date('Y-m-d'); 
                            $check_solicitud = "SELECT id_solicitud FROM solicitudes_citas
                                                WHERE id_paciente= '$id_paciente' 
                                                AND fecha_solicitud >= '$current_date'";
                            $resultado_solicitud = $conexion->query($check_solicitud);

                            if ($resultado_solicitud->num_rows > 0) {
                                echo "<script>alert('Ya tienes una solicitud pendiente.');</script>";
                            } else {
                                $sql_turno = "INSERT INTO solicitudes_citas (id_paciente, fecha_solicitud, id_horario, motivo_solicitud)
              VALUES ('$id_paciente', '$fecha', '$id_horario', '$motivo')";
                                if ($conexion->query($sql_turno) === TRUE) {
                                    echo '<h3 class="alerta-verde">Turno solicitado con éxito.</h3>';
                                }
                            }
                        } else {
                            echo '<h3 class="alerta-roja">Paciente no registrado.</h3>';
                        }
                    } else {
                        echo '<h3 class="alerta-amarilla">Completa todos los campos.</h3>';
                    }
                }
                ?>
            </form>
        </div>
    </section>

    <section id="mapa">
        <h2>Ubicación</h2>
        <iframe class="iframe-mapa" src="https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d378.48775492974664!2d-54.614347546441394!3d-26.022892594369406!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e0!3m2!1ses!2sar!4v1748264983594!5m2!1ses!2sar" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    </section>

    <div class="social-icons">
        <a href="https://wa.me/543757404174" target="_blank">
            <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="WhatsApp">
        </a>
        <a href="https://instagram.com/istumisiones" target="_blank">
            <img src="https://upload.wikimedia.org/wikipedia/commons/a/a5/Instagram_icon.png" alt="Instagram">
        </a>
        <a href="https://www.facebook.com/fabiananobs?locale=es_ES" target="_blank">
            <img src="https://upload.wikimedia.org/wikipedia/commons/5/51/Facebook_f_logo_%282019%29.svg" alt="Facebook">
        </a>
    </div>

    <footer id="contacto">
        <h2>Contacto</h2>
        <p>Teléfono: +54 3757-404174<br>
           Dirección: Av. 25 de Mayo 170, Puerto Esperanza, Misiones</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        window.addEventListener('load', function() {
            const preloader = document.getElementById('preloader');
            const minimumDisplayTime = 1000;
            const startTime = Date.now();

            function hidePreloader() {
                const elapsedTime = Date.now() - startTime;
                const remainingTime = minimumDisplayTime - elapsedTime;

                if (remainingTime > 0) {
                    setTimeout(() => {
                        preloader.classList.add('hidden');
                        setTimeout(() => {
                            preloader.style.display = 'none';
                        }, 500);
                    }, remainingTime);
                } else {
                    preloader.classList.add('hidden');
                    setTimeout(() => {
                        preloader.style.display = 'none';
                    }, 500);
                }
            }
            hidePreloader();
        });

        document.addEventListener('DOMContentLoaded', function() {
            const fechaInput = document.getElementById('fecha');
            fechaInput.addEventListener('change', function() {
                const fecha = new Date(this.value);
                const diaSemana = fecha.getDay();

                if (diaSemana === 6) {
                    this.value = '';
                    document.getElementById('horarios-disponibles').innerHTML = '';
                    return false;
                }
                this.form.submit();
            });
        });
    </script>

</body>
</html>