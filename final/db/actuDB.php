<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/modTablas/database.php";
require_once __DIR__ . "/llenarTablas/procesadorDeXML.php";
require_once __DIR__ . "/llenarTablas/metedorDeDatos.php";
require_once __DIR__ . "/vaciarTablas/vaciarViejos.php";

$url = "https://nap.dgt.es/datex2/v3/dgt/SituationPublication/datex2_v36.xml";

$xmlString = descargarXML($url);
$xml = prepararXML($xmlString);

guardarPublication($xml, $conn);

vaciarViejos($conn);

echo "Datos borrados correctamente";

close_db($conn);
