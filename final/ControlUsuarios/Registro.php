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
        $check = $conn->prepare("SELECT id FROM usuario WHERE nombre_usuario = ?");
        $check->bind_param("s", $nombre_usuario);
        $check->execute();
        $res = $check->get_result();

        if ($res->num_rows > 0) {
            $mensaje = "Error: El nombre de usuario '<strong>$nombre_usuario</strong>' ya está pillado. Elige otro.";
            $tipoMensaje = "mensaje_error";
        } else {
            $contrasenhaHash = password_hash($contrasenha, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO usuario (nombre_usuario, nombre, apellidos, contrasenha) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $nombre_usuario, $nombre, $apellidos, $contrasenhaHash);

            if ($stmt->execute()) {
                $mensaje = "¡Listo! Usuario registrado correctamente.";
                $tipoMensaje = "mensaje_ok";
            } else {
                $mensaje = "Hubo un problema técnico al registrar.";
                $tipoMensaje = "mensaje_error";
            }
            $stmt->close();
        }
        $check->close();
    } else {
        $mensaje = "No te dejes campos vacíos, anda.";
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
        <div id="MegaTelmo">
            <div id="logo">
                <img src="../images/logo.png" alt="Logo">
            </div>
            <a href="../index.php" id="telmo"><h1>Puta DGT</h1></a>
        </div>
        <div id="zona_usuarios">
            <a href="./Login.php">Iniciar Sesión</a>
        </div>
    </header>

    <main class="contenedor_formulario">
        <div class="form_card">
            <div class="form_card_header">
                <h2>Registro de usuario</h2>
                <p>Únete para ver dónde están los radares (o quejarte)</p>
            </div>

            <form class="formulario_usuario" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <?php if (!empty($mensaje)) { ?>
                    <div class="<?php echo $tipoMensaje; ?>" style="margin-bottom: 15px; padding: 10px; border-radius: 5px;">
                        <?php echo $mensaje; ?>
                    </div>
                <?php } ?>

                <div class="campo_form">
                    <label for="nombre_usuario">Nombre de usuario</label>
                    <input type="text" id="nombre_usuario" name="nombre_usuario" value="<?php echo htmlspecialchars($nombre_usuario); ?>" required>
                </div>

                <div class="campo_form">
                    <label for="nombre">Nombre</label>
                    <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($nombre); ?>" required>
                </div>

                <div class="campo_form">
                    <label for="apellidos">Apellidos</label>
                    <input type="text" id="apellidos" name="apellidos" value="<?php echo htmlspecialchars($apellidos); ?>" required>
                </div>

                <div class="campo_form">
                    <label for="contrasenha">Contraseña</label>
                    <input type="password" id="contrasenha" name="contrasenha" required>
                </div>

                <button type="submit" class="boton_form">Registrar</button>
            </form>
        </div>
    </main>

</body>
</html>