<?php
session_start();

if (!isset($_SESSION["esAdmin"]) || $_SESSION["esAdmin"] != 1) {
    header("Location: ../index.php");
    exit(); 
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Puta DGT: Panel de administración</title>
</head>
<body>
    <h1>Bienvenido, Jefe</h1>
    <img src = "https://ih1.redbubble.net/image.6000092779.6675/raf,360x360,075,t,fafafa:ca443f4786.jpg">
</body>
</html>