<?php
require_once "./database.php";

$vista_ccaa = "CREATE OR REPLACE VIEW vista_incidencias AS
    SELECT l.autonomousCommunity AS ccaa,
            p.collectedAt AS fecha,
            COUNT(r.idRecord) AS conteo,
            AVG(r.severity) AS severidad
    FROM publication p
        JOIN publication_record pr USING(idPublication)
        JOIN record r USING(idRecord)
        JOIN situation_location sl USING(idRecord) 
        JOIN location l USING(idLocation)
    GROUP BY ccaa, fecha";

if (!$conn->query($vista_ccaa)) {
    die("Error creando una vista: " . $conn->error);
}

$vista_causas_por_ccaa= "CREATE OR REPLACE VIEW vista_incidencias_causas AS
    SELECT l.autonomousCommunity AS ccaa,
            r.causeType AS causa,
            COUNT(r.idRecord) AS conteo
    FROM publication p
        JOIN publication_record pr USING(idPublication)
        JOIN record r USING(idRecord)
        JOIN situation_location sl USING(idRecord) 
        JOIN location l USING(idLocation)
    GROUP BY ccaa, causa";

if (!$conn->query($vista_causas_por_ccaa)) {
    die("Error creando una vista: " . $conn->error);
}



$conn->close();
