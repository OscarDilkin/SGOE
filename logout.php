<?php
session_start(); // Iniciar la sesión
session_destroy(); // Destruir todas las variables de sesión
header("Location: index.php"); // Redirigir al login
exit();
?>