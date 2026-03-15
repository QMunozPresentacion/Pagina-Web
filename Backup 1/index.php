<?php
    session_start();

    $logueado = isset($_SESSION["id_usuario"]);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Puta DGT: pagina principal</title>
    <link rel="icon" type="image/x-icon" href="./images/favicon.ico">
    <link rel="stylesheet" href = "./styles/estilo.css">
</head>
<body>

    <?php
        require_once "./db/modTablas/database.php";
    ?>

    <header>
        <div id = "logo">
            <img src = "./images/logo.png">
        </div>

        <h1>Puta DGT</h1>
        <?php if(!$logueado): ?>
            <div id = "zona_usuarios">
                <a href = "./ControlUsuarios/Registro.php">Registrarse</a>
                <a href = "./ControlUsuarios/Login.php">Iniciar Sesion</a>
            </div>
        <?php else: ?>
            <div id = "zona_usuarios">
                <a id ="cerrar" href = "./ControlUsuarios/logout.php">Cerrar Sesion</a>
            </div>
        <?php endif; ?>
    </header> 

    <?php if($logueado): ?>
        <div class="bienvenida">
            <h1>Hola <?= htmlspecialchars($_SESSION["nombre"]) ?></h1>
        </div>
    <?php endif; ?>

    <br><h2>Las tablas</h2><br><br>
    <h3>Tabla de toda España</h3>
    <section id = "tablas">
        <div class="tabla">
            <table id="table_situation_record" border="1">
                <tr>
                    <th>Fecha y Hora</th>
                    <th>Numero de Incidencias</th>
                    <th>Gravedad</th>
                </tr>
                <?php
                    $comando = "SELECT p.collectedAt fecha, count(r.idRecord) conteo, p.overallSeverity severidad
                                FROM situation_record r JOIN publication p ON(r.publication_id = p.idPublication)
                                GROUP BY r.publication_id
                                ORDER BY fecha desc";
                    $result = $conn->query($comando);

                    $count = 0;
                    while(($row = $result->fetch_assoc()) && ($count < 48)){
                        echo "<tr>";
                        echo "<td>".$row["fecha"]."</td>";
                        echo "<td>".$row["conteo"]."</td>";
                        echo "<td>".$row["severidad"]."</td>";
                        echo "</tr>";
                        $count ++;
                    }
                ?>
            </table>
        </div>
        <h3>Tabla de Situation Location</h3>
        <div class="tabla">
            <table id="table_situation_location" border="1">
                <tr>
                    <th>idRecord</th>
                    <th>idLocation</th>
                    <th>locationType</th>
                </tr>
                <?php 
                    $comando = "SELECT * FROM situation_location;";
                    $result = $conn->query($comando);
                    while($row = $result->fetch_assoc()){
                        echo "<tr>";
                        echo "<td>".$row["idRecord"]."</td>";
                        echo "<td>".$row["idLocation"]."</td>";
                        echo "<td>".$row["locationType"]."</td>";
                        echo "</tr>";
                    }
                ?>
            </table>
        </div>
        <h3>Tabla de Location</h3>
        <div class = "tabla">
            <table id = "table_location" border="1">
                <tr>
                    <th>idLocation</th>
                    <th>latitude</th>
                    <th>longitude</th>
                    <th>autonomousCommunity</th>
                    <th>municipality</th>
                    <th>province</th>
                </tr>

                <?php 
                    $comando = "SELECT * FROM location";
                    $result = $conn->query($comando);

                    while($row = $result->fetch_assoc()){
                        echo "<tr>";
                        echo "<td>".$row["idLocation"]."</td>";
                        echo "<td>".$row["latitude"]."</td>";
                        echo "<td>".$row["longitude"]."</td>";
                        echo "<td>".$row["autonomousCommunity"]."</td>";
                        echo "<td>".$row["municipality"]."</td>";
                        echo "<td>".$row["province"]."</td>";
                        echo "</tr>";
                    }
                ?>
            </table>
        </div>

    </section>


    <?php
        close_db($conn);
    ?>

</body>
</html>
