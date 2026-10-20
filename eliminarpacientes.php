<?php

if (!empty($_GET["id"])) {
    $id_paciente = intval($_GET["id"]);
    $sql = $conexion->query("DELETE FROM pacientes WHERE id_paciente = $id_paciente");

    if ($sql == 1) {
        echo "Paciente eliminado";
    } else {
        echo "error al eliminar";
    }
}

?>