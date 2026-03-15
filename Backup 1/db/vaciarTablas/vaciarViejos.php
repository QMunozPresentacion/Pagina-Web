<?php

function vaciarViejos($conn){
    $query = "DELETE FROM publication WHERE collectedAt < NOW() - INTERVAL 7 DAY";

    if (!$conn->query($query)) {
        die("Error borrando las tablas: " . $conn->error);
    }

    echo "<h2>Datos viejos borrados correctamente</h2>";
}
