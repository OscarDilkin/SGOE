<?php
include "conexion_db.php";

if (isset($_POST["btnlocalidad"])) {
    $nombre_localidad = $_POST["nombre_localidad"];
    
    // Insertar la nueva localidad en la base de datos
    $sql_localidad = $conexion->query("INSERT INTO localidades (nombre_localidad) VALUES ('$nombre_localidad')");
    
    if ($sql_localidad) {
        $_SESSION['mensaje'] = "Localidad agregada con éxito.";
        header("Location: localidades.php");
        exit();
    } else {
        echo "Error al agregar localidad: " . $conexion->error;
    }
}
?>