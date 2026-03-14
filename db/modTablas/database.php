<?php
$conn = mysqli_connect("dbserver", "grupo26", "gooChifoo2", "db_grupo26");
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}

function close_db($conn) {
    if ($conn != null)
        $conn->close();
}