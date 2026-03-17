<?php

require_once __DIR__ . "/database.php";

$conn->query("SET FOREIGN_KEY_CHECKS = 0");

$conn->query("DROP TABLE IF EXISTS situation_location");
$conn->query("DROP TABLE IF EXISTS publication_record");
$conn->query("DROP TABLE IF EXISTS location");
$conn->query("DROP TABLE IF EXISTS record");
$conn->query("DROP TABLE IF EXISTS publication");

$conn->query("DROP TABLE IF EXISTS usuario");

$conn->query("SET FOREIGN_KEY_CHECKS = 1");

echo "Todas las tablas han sido eliminadas correctamente.";

$conn->close();