<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión General</title>
    <link rel="stylesheet" href="./styles/pacientes.css">
    <link rel="stylesheet" href="./styles/citas.css">
    <link rel="stylesheet" href="./styles/gestion.css">
    <style>
        /* Panel de botones centrado */
        .gestion-panel {
            background-color: #f4f9fc;
            border-radius: 10px;
            padding: 30px;
            width: 90%;
            max-width: 600px;
            margin: 50px auto;
            text-align: center;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .gestion-panel h2 {
            margin-bottom: 25px;
            color: #333;
        }

        .gestion-boton {
            display: block;
            width: 70%;
            padding: 15px;
            margin: 10px auto;
            background-color: rgb(107,114,128);
            color: white;
            text-decoration: none;
            font-size: 1.1em;
            font-weight: bold;
            border-radius: 20px;
            transition: 0.3s;
        }

        .gestion-boton:hover {
            background-color: rgb(75,85,99);
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
<a href="./assets/manual.pdf" target="_blank">Manual de usuario</a>    
<section class="gestion-panel">
        <h2>Panel de Gestión</h2>
        <a href="obras_sociales.php" class="gestion-boton">Obras Sociales</a>
        <a href="localidades.php" class="gestion-boton">Localidades</a>
        <a href="bloquear_horarios.php" class="gestion-boton">Bloquear Horarios</a>
<a href="gestionar_horarios.php" class="gestion-boton">Gestionar Horarios</a>
    </section>
</main>

<footer>
    <h4>SGOE - Sistema de Gestión Odontológico Esperanza - Creado por Dilkin, Oscar y De Matos Esteban</h4>
</footer>
</body>
</html>