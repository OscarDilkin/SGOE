<?php
if (!empty($_POST["btnIngresar"])) {
    if (empty($_POST["usuario"]) || empty($_POST["password"])) {
        echo '<div class="alert alert-danger">LOS CAMPOS ESTAN VACIOS</div>';
    } else {
        $usuario = $_POST["usuario"];
        $password = $_POST["password"];

        $stmt = $conexion->prepare("SELECT * FROM login WHERE usuario = ? AND password = ?");
        $stmt->bind_param("ss", $usuario, $password);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 1) {
            // No need for session_start() here, it's already started in index.php
            $_SESSION['usuario'] = $usuario;
            header("Location: main.php");
            exit();
        } else {
            echo '<div style="color: white; font-size: 14px; font-weight: 800; text-align: center; background-color:red">USUARIO O CONTRASEÑA INCORRECTOS</div><br>';
        }
        $stmt->close();
    }
}
?>