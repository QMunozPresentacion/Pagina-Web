<?php
$mensaje = "";
$tipoMensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    require_once "../db/modTablas/database.php";

    $nombre_usuario = trim($_POST["nombre_usuario"] ?? "");
    $nombre = trim($_POST["nombre"] ?? "");
    $apellidos = trim($_POST["apellidos"] ?? "");
    $contrasenha = trim($_POST["contrasenha"] ?? "");

    if (!empty($nombre) && !empty($apellidos) && !empty($contrasenha) && !empty($nombre_usuario)) {
        $contrasenhaHash = password_hash($contrasenha, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO usuarios (nombre_usuario,nombre, apellidos, contrasenha) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss",$nombre_usuario, $nombre, $apellidos, $contrasenhaHash);

        if ($stmt->execute()) {
            $mensaje = "Usuario registrado correctamente";
            $tipoMensaje = "mensaje_ok";
        } else {
            if ($conn->errno == 1062) {
                $mensaje = "Ese nombre de usuario ya existe";
            } else {
                $mensaje = "Error al registrar el usuario: ";
            }

            $tipoMensaje = "mensaje_error";
        }

        $stmt->close();
    } else {
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
            <a href = "./Login.php">Iniciar Sesion</a>
        </div>
    </header>

    <main class="contenedor_formulario">
        <div class="form_card">
            <div class="form_card_header">
                <h2>Registro de usuario</h2>
                <p>Crea tu cuenta para acceder al panel</p>
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
                    <label for="nombre">Nombre</label>
                    <input type="text" id="nombre" name="nombre" placeholder="Introduce tu nombre" required>
                </div>

                <div class="campo_form">
                    <label for="apellidos">Apellidos</label>
                    <input type="text" id="apellidos" name="apellidos" placeholder="Introduce tus apellidos" required>
                </div>

                <div class="campo_form">
                    <label for="contrasenha">Contraseña</label>
                    <input type="password" id="contrasenha" name="contrasenha" placeholder="Crea una contraseña" required>
                </div>

                <button type="submit" class="boton_form">Registrar</button>
            </form>
        </div>
    </main>

</body>
</html>