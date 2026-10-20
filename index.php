<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include("conexion_db.php");
include("controlado.php"); // Procesar el login antes de cualquier salida HTML
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="./styles/login.css">
    <link rel="stylesheet" href="./styles/pacientes.css">
</head>
<body>

    <header>
        <div class="logo">
            <img src="./assets/img/logo.png" alt="logo">
            <h1>Sistema Esperanza</h1>
        </div>
    </header>

    <br><br><br><br><br><br><br><br>
    <div class="login-container">
        <div class="login-box">
            <h2>Iniciar sesión</h2>
            <form action="index.php" method="POST">
                <div class="textbox">
                    <input type="text" placeholder="Usuario" name="usuario" required>
                </div>
                <div class="textbox">
                    <input type="password" placeholder="Contraseña" name="password" required>
                </div>
                <input type="submit" value="Iniciar sesión" class="btn" name="btnIngresar">
            </form>
        </div>
    </div>
</body>
</html>