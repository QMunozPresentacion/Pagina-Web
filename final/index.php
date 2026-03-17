<?php
    session_start();

    $logueado = isset($_SESSION["id_usuario"]);
    $admin = isset($_SESSION["esAdmin"]) && $_SESSION["esAdmin"] == 1;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Puta DGT: pagina principal</title>
    <link rel="icon" type="image/x-icon" href="./images/favicon.ico">
    <link rel="stylesheet" href="./styles/estilo.css">
</head>
<body>

    <?php
        require_once "./db/modTablas/database.php";
    ?>

    <header>
        <div id="logo">
            <img src="./images/logo.png" alt="Logo">
        </div>

        <h1>Puta DGT</h1>

        <div id="zona_usuarios">
            <?php if(!$logueado): ?>
                <a href="./ControlUsuarios/Registro.php">Registrarse</a>
                <a href="./ControlUsuarios/Login.php">Iniciar Sesión</a>
            <?php else: ?>
                <?php if ($admin): ?>
                    <a id="panel_administracion" href="./paneles/admin.php">Panel de administrador</a>
                <?php endif; ?>
                
                <a id="cerrar" href="./ControlUsuarios/logout.php">Cerrar Sesión</a>
            <?php endif; ?>
        </div>
    </header>

    <?php if($logueado): ?>
        <div class="bienvenida">
            <h1>Hola <?= htmlspecialchars($_SESSION["nombre"]) ?></h1>
        </div>
    <?php endif; ?>
    
    <?php 

        $comando_fecha = "SELECT MAX(collectedAt) fecha_max
                                     FROM publication";

        $resultado_fecha = $conn->query($comando_fecha);
        
        $ultima_fecha = $resultado_fecha->fetch_assoc()['fecha_max'] ?? null;
        ?>


    <br><h2>Las tablas:</h2><br><br>

    <h3>Ultima edición: <?php echo  $ultima_fecha ?></h3> 

    <section id="tablas">

        <h3>Tabla de toda España</h3>
        <div class="tabla">
            <table id="table_situation_record" border="1">
                <tr>
                    <th>Fecha y Hora</th>
                    <th>Numero de Incidencias</th>
                    <th>Gravedad</th>
                </tr>
                <?php
                    $comando = "SELECT p.collectedAt AS fecha,
                                       COUNT(pr.idRecord) AS conteo,
                                       p.overallSeverity AS severidad
                                FROM publication p
                                    JOIN publication_record pr USING(idPublication)
                                GROUP BY p.idPublication, p.collectedAt, p.overallSeverity
                                ORDER BY fecha DESC
                                LIMIT 24";

                    $result = $conn->query($comando);

                    if ($result) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row["fecha"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["conteo"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["severidad"]) . "</td>";
                            echo "</tr>";
                        }
                    }
                ?>
            </table>
        </div>

        <h3>Estadisticas de tipos de accidente</h3>
        <div class="tabla">
            <table id="table_situation_location" border="1">
                <tr>
                    <th>Causas</th>
                    <th>Gravedad media</th>
                    <th>Numero incidencias</th>
                </tr>
                <?php
                    $comando = "SELECT causeType, 
                        round(AVG(severity),2) AS severidad_media, 
                        COUNT(*) AS conteo 
                    FROM record r
                        JOIN publication_record pr ON r.idRecord = pr.idRecord
                        JOIN publication p ON pr.idPublication = p.idPublication
                    WHERE p.collectedAt = '" . $ultima_fecha . "'
                    GROUP BY causeType
                    ORDER BY conteo DESC";

                    $result = $conn->query($comando);

                    if ($result) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row["causeType"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["severidad_media"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["conteo"]) . "</td>";
                            echo "</tr>";
                        }
                    }
                ?>
            </table>
        </div>

        <h3>Estadisticas por Comunidad Autonoma</h3>
        <div class="tabla">
            <table id="table_location" border="1">
                <tr>
                    <th>Comunidad Autonoma</th>
                    <th>Media de incidencias</th>
                    <th>Maximo de incidencias concurrentes</th>
                    <th>Causa principal</th>
                    <th>Gravedad Media</th>
                </tr>
                <?php

                    $comando_total = "
                        WITH Estadisticas AS (
                            SELECT ccaa,
                                AVG(conteo) AS media_incidencias,
                                MAX(conteo) AS max_incidencias,
                                AVG(severidad) AS severidad_media
                            FROM vista_incidencias
                            GROUP BY ccaa
                        ),
                        RankingCausas AS (
                            SELECT ccaa, 
                                causa,
                                ROW_NUMBER() OVER(PARTITION BY ccaa ORDER BY conteo DESC) as ranking
                            FROM vista_incidencias_causas
                        )
                        
                        -- Juntamos las estadísticas con la causa que sacó el número 1 en el ranking
                        SELECT e.ccaa, 
                            e.media_incidencias, 
                            e.max_incidencias, 
                            r.causa, 
                            e.severidad_media
                        FROM Estadisticas e
                        LEFT JOIN RankingCausas r ON e.ccaa = r.ccaa AND r.ranking = 1
                        ORDER BY e.ccaa;
                    ";

                    $resultado = $conn->query($comando_total);

                    // 2. Un único while limpio y seguro
                    if ($resultado) {
                        while ($row = $resultado->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row["ccaa"]) . "</td>";
                            
                            // El number_format es un pequeño toque extra para redondear la media a 2 decimales ;)
                            echo "<td>" . number_format($row["media_incidencias"], 2) . "</td>"; 
                            
                            echo "<td>" . htmlspecialchars($row["max_incidencias"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["causa"]) . "</td>";
                            echo "<td>" . number_format($row["severidad_media"], 2) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        // Si la consulta falla, mostramos el error para poder depurarlo
                        echo "<tr><td colspan='5'>Error en la consulta: " . $conn->error . "</td></tr>";
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