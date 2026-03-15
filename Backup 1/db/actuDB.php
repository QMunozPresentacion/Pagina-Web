<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "./modTablas/database.php";
require_once "./llenarTablas/procesadorDeXML.php";
require_once "./llenarTablas/metedorDeDatos.php";
require_once "./vaciarTablas/vaciarViejos.php";

$url = "https://nap.dgt.es/datex2/v3/dgt/SituationPublication/datex2_v36.xml";

$xmlString = descargarXML($url);
$xml = prepararXML($xmlString);

guardarPublication($xml, $conn);

echo "Datos actualizados correctamente";

vaciarViejos($conn);

echo "Datos borrados correctamente";

close_db($conn);
