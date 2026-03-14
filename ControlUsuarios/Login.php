<?php
session_start();

$mensaje = "";
$tipoMensaje = "";

if($_SERVER["REQUEST_METHOD"] === "POST"){
    require_once "../db/modTablas/database.php";

    $nombre_usuario = trim($_POST["nombre_usuario"] ?? "");
    $contrasenha = trim($_POST["contrasenha"] ?? "");

    if(!empty($nombre_usuario) && !empty($contrasenha)){
        $stmt = $conn->prepare("SELECT id, nombre_usuario, nombre, apellidos, contrasenha FROM usuarios WHERE nombre_usuario = ?");
        $stmt->bind_param("s",$nombre_usuario);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if($resultado->num_rows === 1){
            $user = $resultado->fetch_assoc();

            if(password_verify($contrasenha, $user["contrasenha"])){
                $_SESSION["id_usuario"] = $user["id"];
                $_SESSION["nombre_usuario"] = $user["nombre_usuario"];
                $_SESSION["nombre"] = $user["nombre"];
                $_SESSION["apellidos"] = $user["apellidos"];

                header("Location: ../index.php");
                exit;
            }
            else{
                $mensaje = "Contraseña incorrecta";
                $tipoMensaje = "mensaje_error";
            }
        }
        else{
            $mensaje = "El usuario no existe";
            $tipoMensaje = "mensaje_error";
        }
        $stmt->close();
    }else{
        $mensaje = "Todos los campos son obligatorios";
        $tipoMensaje = "mensaje_error";
    }
    close_db($conn);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Puta DGT: Panel de Registro</title>
    <link rel="icon" type="image/x-icon" href="../images/favicon.ico">
    <link rel="stylesheet" href="../styles/estilo.css">
    <link rel="stylesheet" href="../styles/estiloPaneles.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

    <header>
        <div id = "MegaTelmo">
            <div id="logo">
                <img src="../images/logo.png" alt="Logo">
            </div>
            <a href = "../index.php" id = "telmo"><h1>Puta DGT</h1></a>
        </div>
        <div id = "zona_usuarios">
            <a href = "./Registro.php">Registrarse</a>
        </div>
    </header>

    <main class="contenedor_formulario">
        <div class="form_card">
            <div class="form_card_header">
                <h2>Incio de sesion</h2>
                <p>Accede a tu cuenta</p>
            </div>

            <form class="formulario_usuario" method="POST">
                <?php if (!empty($mensaje)) { ?>
                    <div class="<?php echo $tipoMensaje; ?>">
                        <?php echo $mensaje; ?>
                    </div>
                <?php } ?>

                <div class="campo_form">
                    <label for="nombre_usuario">Nombre de usuario</label>
                    <input type="text" id="nombre_usuario" name="nombre_usuario" placeholder="Introduce tu nombre de usuario" required>
                </div>

                <div class="campo_form">
                    <label for="contrasenha">Contraseña</label>
                    <input type="password" id="contrasenha" name="contrasenha" placeholder="Crea una contraseña" required>
                </div>

                <button type="submit" class="boton_form">Iniciar Sesion</button>
            </form>
        </div>
    </main>

</body>
</html>