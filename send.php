<?php
include("conexion.php");
if(isset($_POST['enviar'])){
    $documento = $_POST['documento'];

    // Verificar si el documento ya existe
    $verificar_doc = "SELECT dni FROM pacientes WHERE dni = '$documento'";
    $resultado_verificacion = mysqli_query($conexion, $verificar_doc);

    if (!$resultado_verificacion) {
        die("Error en la consulta: " . mysqli_error($conexion));
    }

    if (mysqli_num_rows($resultado_verificacion) > 0) {
        echo '<h3 style="color: black; font-size: 14px; font-weight: 800;text-align: center;background-color:yellow">Usuario existente</h3>';
    } else {
        if(
            strlen($_POST['nombre'])>0 &&
            strlen($_POST['apellido'])>0 &&
            strlen($_POST['documento'])>0 &&
            strlen($_POST['fecha_nacimiento'])>0 &&
            strlen($_POST['telefono'])>0 &&
            strlen($_POST['direccion'])>0        
        ){
            $nombre=$_POST['nombre'];
            $apellido=$_POST['apellido'];
            $genero=$_POST['genero'];
            $documento=$_POST['documento'];
            $fecha_nacimiento=$_POST['fecha_nacimiento'];
            $telefono=$_POST['telefono'];
            $correo=$_POST['correo'];
            $localidad=$_POST['localidad'];
            $direccion=$_POST['direccion'];
            $observaciones=$_POST['observaciones'];
            $tipo_sangre=$_POST['tipo_sangre'];
            $obra_social=$_POST['obra_social'];
            $nro_afiliado=$_POST['nro_afiliado'];

            // ✅ Insertando IDs de localidad y obra social
            $consulta="INSERT INTO pacientes VALUES ('','$documento','$nombre','$apellido','$tipo_sangre','$fecha_nacimiento','$correo','$telefono','$genero','$localidad','$direccion','$observaciones','$obra_social','$nro_afiliado')";     

            $resultado=mysqli_query($conexion ,$consulta);

            if($resultado){
                echo "<script>
                        alert('Cambios guardados exitosamente.');
                        window.location.href = 'pacientes.php';
                    </script>";
            }else{
                echo "<script>alert('Error al guardar los cambios: " . addslashes($conexion->error) . "');</script>";
            }
        }else{
            echo '<h3 style="color: black; font-size: 14px; font-weight: 800;text-align: center;background-color:yellow">Llena todos los campos</h3>';
        }
    }
}
?>