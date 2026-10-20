<?php
$conexion = new mysqli("localhost", "c2821234_consult", "Estebanquito2025ISTU", "c2821234_consult");

if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}

// Obtener el término de búsqueda del input
$query = isset($_GET['query']) ? $conexion->real_escape_string($_GET['query']) : '';

// Consulta SQL que busca en nombre, apellido, dni y telefono
$sql = "SELECT id_paciente, nombre, apellido 
        FROM pacientes 
        WHERE nombre LIKE '%$query%' 
           OR apellido LIKE '%$query%' 
           OR dni LIKE '%$query%' 
           OR telefono LIKE '%$query%'";
$result = $conexion->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<div onclick='seleccionarPaciente(" . $row['id_paciente'] . ", \"" . $row['nombre'] . " " . $row['apellido'] . "\")'>" . $row['nombre'] . " " . $row['apellido'] . "</div>";
    }
} else {
    echo "<div>No se encontraron resultados.</div>";
}

$conexion->close();
?>