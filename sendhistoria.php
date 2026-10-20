<?php
include("conexion.php");

if (!empty($_POST['btnhistoria'])) {
    if (strlen($_POST['motivo']) > 0 && strlen($_POST['tratamiento']) > 0) {

        $fecha = $_POST['fecha'];
        $id_paciente = intval($_POST['id_paciente']);
        $motivo = $_POST['motivo'];
        $tratamiento = $_POST['tratamiento'];

        $consulta = "INSERT INTO historia (id_paciente, fecha, motivo, tratamiento) VALUES ('$id_paciente', '$fecha', '$motivo', '$tratamiento')";
        $resultado = mysqli_query($conexion, $consulta);

        if ($resultado) {
            echo '<h3 style="color: white; font-size: 14px; font-weight: 800; text-align: center; background-color: green">Se han guardado los datos</h3>';
            echo '<script>
                    setTimeout(function() {
                        window.location.href = "historiaclinica.php?id_paciente=' . urlencode($id_paciente) . '";
                    }, 2000);
                  </script>';
        } else {
            echo '<h3 style="color: white; font-size: 14px; font-weight: 800; text-align: center; background-color: red">Hubo un error al guardar los datos</h3>';
            echo '<script>
                    setTimeout(function() {
                        window.location.href = "historiaclinica.php?id_paciente=' . urlencode($id_paciente) . '";
                    }, 500);
                  </script>';
        }

    } else {
        echo '<h3 style="color: white; font-size: 14px; font-weight: 800; text-align: center; background-color: red">Por favor, completa todos los campos</h3>';
    }
}
?>