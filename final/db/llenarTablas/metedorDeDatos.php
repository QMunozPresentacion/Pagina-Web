<?php

function guardarPublication($xml, $conn){
    // Hacemos que en vez de meter los datos 1 a 1 los meta todos
    // de golpe en la BD
    $conn->begin_transaction();

    $pubNode = $xml->xpath('//com:publicationTime');
    $publicationTime = isset($pubNode[0]) ? (string)$pubNode[0] : '';

    echo "<h3>DEBUG: Fecha de publicación encontrada: '$publicationTime'</h3>";
    if ($publicationTime === '') {
        return null;
    }
    $ns = $xml->getNamespaces(true);

    // idPublication es string (lo igualas a una fecha en texto)
    $idPublication = $publicationTime;

    $collectedAt = date('Y-m-d H:i:s');

    $rank = array('low' => 1, 'medium' => 2, 'high' => 3, 'highest' => 4);

    $overallSeverity = 0.0;

    $stmt_insert_pub = $conn->prepare("
        INSERT INTO publication (idPublication, publicationTime, collectedAt)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE
            publicationTime = VALUES(publicationTime),
            collectedAt = VALUES(collectedAt)
    ");
    if (!$stmt_insert_pub) {
        die("prepare publication: " . $conn->error);
    }

    // s = string, d = double, s = string, s = string
    $stmt_insert_pub->bind_param("sss", $idPublication, $publicationTime, $collectedAt);
    $stmt_insert_pub->execute();
    $stmt_insert_pub->close();


    $situations = $xml->xpath('//sit:situation');

    if (!empty($situations)) {
        $totalRank = 0;
        $countRank = 0;

        // Declaramos los stmts necesarios para hacer todas las cargas a la base de datos

        $stmt_insert_record = $conn->prepare("
            INSERT INTO record (idRecord, source, causeType, severity)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                source = VALUES(source),
                causeType = VALUES(causeType),
                severity = VALUES(severity)
        ");
        if (!$stmt_insert_record) {
            $conn->rollback();
            die("ERROR: stmt no ha sido creado correctamente" . $conn->error);
        }

        $stmt_find_location = $conn->prepare("
            SELECT idLocation
            FROM location
            WHERE latitude = ? AND longitude = ?
            AND autonomousCommunity = ? AND municipality = ? AND province = ?
            LIMIT 1
        ");
        if (!$stmt_find_location) {
            $conn->rollback();
            die("ERROR: stmt no ha sido creado correctamente" . $conn->error);
        }

        $stmt_find_record = $conn->prepare("
            SELECT idRecord
            FROM record
            WHERE idRecord = ?
            LIMIT 1
        ");
        if (!$stmt_find_record) {
            $conn->rollback();
            die("ERROR: stmt no ha sido creado correctamente" . $conn->error);
        }

        $stmt_insert_location = $conn->prepare("
            INSERT INTO location (latitude, longitude, autonomousCommunity, municipality, province)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
                latitude = latitude,
                longitude = longitude
        ");
        if (!$stmt_insert_location) {
            $conn->rollback();
            die("ERROR: stmt no ha sido creado correctamente" . $conn->error);
        }

        $stmt_insert_sitLoc = $conn->prepare("
            INSERT INTO situation_location (idRecord, idLocation, locationType)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE
                locationType = VALUES(locationType)
        ");
        if (!$stmt_insert_sitLoc) {
            $conn->rollback();
            die("ERROR: stmt no ha sido creado correctamente" . $conn->error);
        }

        $stmt_insert_pubRec = $conn->prepare("
            INSERT IGNORE INTO publication_record (idPublication, idRecord)
                VALUES (?, ?)
        ");
        if (!$stmt_insert_pubRec) {
            $conn->rollback();
            die("ERROR: stmt no ha sido creado correctamente" . $conn->error);
        }

        foreach ($situations as $sit) {
            guardarSituationRecords($sit, $conn, $ns, $idPublication, $stmt_find_record, $stmt_insert_record, $stmt_insert_pubRec, $stmt_find_location, $stmt_insert_location, $stmt_insert_sitLoc);
            
            $sevNode = $sit->xpath('sit:overallSeverity');

            $sev = isset($sevNode[0]) ? trim((string)$sevNode[0]) : ''; // Trim quita los espacios inecesarios

            if ($sev !== '' && isset($rank[$sev])) {
                $totalRank += $rank[$sev];
                $countRank++;
            }
        }

        if ($countRank > 0) {
            $overallSeverity = $totalRank / $countRank; // double
        }
        $stmt_insert_record->close();
        $stmt_find_location->close();
        $stmt_insert_location->close();
        $stmt_insert_sitLoc->close();
        
        $stmt_sev = $conn->prepare("
                UPDATE publication 
                SET overallSeverity = ?
                WHERE idPublication = ?
            ");

        $stmt_sev->bind_param("ds", $overallSeverity, $idPublication);
        $stmt_sev->execute();
        $stmt_sev->close();

        $conn->commit();
        echo "Datos actualizados correctamente";

    }
    else{
        $conn->rollback();
        echo "ERROR: situations vacio";
    }
}


function guardarSituationRecords($sit, $conn, $ns, $idPublication, $stmt_find_record, $stmt_insert_record, $stmt_insert_pubRec, $stmt_find_location, $stmt_insert_location, $stmt_insert_sitLoc){

    if ($idPublication === null || $idPublication === '') return;

    $rank = ['low'=>1,'medium'=>2,'high'=>3,'highest'=>4];

    if (empty($sit)) return;

    if (isset($ns['sit'])) $sit->registerXPathNamespace('sit', $ns['sit']);


    $sevNode = $sit->xpath('sit:overallSeverity');
    $severityText = isset($sevNode[0]) ? trim((string)$sevNode[0]) : '';
    $severity = $rank[$severityText] ?? 0;

    $records = $sit->xpath('.//sit:situationRecord');

    if (empty($records)) return;

    foreach ($records as $record) {
        if (isset($ns['sit'])) $record->registerXPathNamespace('sit', $ns['sit']);
        if (isset($ns['com'])) $record->registerXPathNamespace('com', $ns['com']);
        $idRecord = (string)$record['id'];

        //Comprobamos si existe el record
        $stmt_find_record->bind_param("s", $idRecord);

        $stmt_find_record->execute();

        $res = $stmt_find_record->get_result();

        if ($res->num_rows === 0) {
            $sourceNode = $record->xpath('sit:source/com:sourceIdentification');
            $source = isset($sourceNode[0]) ? trim((string)$sourceNode[0]) : '';

            $causeNode = $record->xpath('sit:cause/sit:causeType');
            $causeType = isset($causeNode[0]) ? trim((string)$causeNode[0]) : '';

            $stmt_insert_record->bind_param("sssi", $idRecord, $source, $causeType, $severity);
            $stmt_insert_record->execute();

            guardarLocationsYRelaciones($record, $conn, $ns, $stmt_find_location, $stmt_insert_location, $stmt_insert_sitLoc);
        }

        $stmt_insert_pubRec->bind_param("ss",$idPublication, $idRecord);
        $stmt_insert_pubRec->execute();
    }
}



function guardarLocationsYRelaciones($record, $conn, $ns, $stmt_find_location, $stmt_insert_location, $stmt_insert_sitLoc){

    if (empty($record)) {
        return;
    }

    if (isset($ns['sit'])) $record->registerXPathNamespace('sit', $ns['sit']);
    if (isset($ns['loc'])) $record->registerXPathNamespace('loc', $ns['loc']);
    if (isset($ns['lse'])) $record->registerXPathNamespace('lse', $ns['lse']);

    // IMPORTANTE: como antes lo tratabas string, aquí también
    $idRecord = (string)$record['id'];

    $locRef = $record->xpath('.//sit:locationReference');
    if (empty($locRef)) return;

    $locRef0 = $locRef[0];

    if (isset($ns['loc'])) $locRef0->registerXPathNamespace('loc', $ns['loc']);
    if (isset($ns['lse'])) $locRef0->registerXPathNamespace('lse', $ns['lse']);

    $acNodes   = $locRef0->xpath('.//lse:autonomousCommunity');
    $munNodes  = $locRef0->xpath('.//lse:municipality');
    $provNodes = $locRef0->xpath('.//lse:province');

    $autonomousCommunity = !empty($acNodes) ? trim((string)$acNodes[0]) : '';
    $municipality        = !empty($munNodes) ? trim((string)$munNodes[0]) : '';
    $province            = !empty($provNodes) ? trim((string)$provNodes[0]) : '';

    $pointNode  = $locRef0->xpath('.//loc:tpegPointLocation');
    $linearNode = $locRef0->xpath('.//loc:tpegLinearLocation');

    if (!empty($pointNode)) {

        $latNode = $locRef0->xpath('.//loc:tpegPointLocation//loc:pointCoordinates/loc:latitude');
        $lonNode = $locRef0->xpath('.//loc:tpegPointLocation//loc:pointCoordinates/loc:longitude');

        if (!isset($latNode[0]) || !isset($lonNode[0])) {
            return;
        }

        $latitude  = round((float)$latNode[0], 6);
        $longitude = round((float)$lonNode[0], 6);


        $idLocation = obtenerOInsertarLocation(
            $stmt_find_location, $stmt_insert_location, $conn,
            $latitude, $longitude,
            $autonomousCommunity, $municipality, $province
        );

        if ($idLocation !== null) {
            $type = "SINGLE";
            // AQUÍ ESTABA MAL: idRecord NO es int si lo llevas como string
            $stmt_insert_sitLoc->bind_param("sis", $idRecord, $idLocation, $type);
            $stmt_insert_sitLoc->execute();
        }

    } elseif (!empty($linearNode)) {

        $fromLat = $locRef0->xpath('.//loc:tpegLinearLocation//loc:from//loc:pointCoordinates/loc:latitude');
        $fromLon = $locRef0->xpath('.//loc:tpegLinearLocation//loc:from//loc:pointCoordinates/loc:longitude');
        $toLat   = $locRef0->xpath('.//loc:tpegLinearLocation//loc:to//loc:pointCoordinates/loc:latitude');
        $toLon   = $locRef0->xpath('.//loc:tpegLinearLocation//loc:to//loc:pointCoordinates/loc:longitude');

        if (isset($fromLat[0]) && isset($fromLon[0])) {
            $latitude  = round((float)$fromLat[0], 6);
            $longitude = round((float)$fromLon[0],6);

            $idLocation = obtenerOInsertarLocation(
                $stmt_find_location, $stmt_insert_location, $conn,
                $latitude, $longitude,
                $autonomousCommunity, $municipality, $province
            );

            if ($idLocation !== null) {
                $type = "START";
                $stmt_insert_sitLoc->bind_param("sis", $idRecord, $idLocation, $type);
                $stmt_insert_sitLoc->execute();
            }
        }

        if (isset($toLat[0]) && isset($toLon[0])) {
            $latitude  = round((float)$toLat[0],6);
            $longitude = round((float)$toLon[0],6);

            $idLocation = obtenerOInsertarLocation(
                $stmt_find_location, $stmt_insert_location, $conn,
                $latitude, $longitude,
                $autonomousCommunity, $municipality, $province
            );

            if ($idLocation !== null) {
                $type = "END";
                $stmt_insert_sitLoc->bind_param("sis", $idRecord, $idLocation, $type);
                $stmt_insert_sitLoc->execute();
            }
        }
    }
}



function obtenerOInsertarLocation($stmt_find_location, $stmt_insert_location, $conn, $latitude, $longitude, $autonomousCommunity, $municipality, $province){

    $stmt_find_location->bind_param("ddsss", $latitude, $longitude, $autonomousCommunity, $municipality, $province);
    $stmt_find_location->execute();

    $res = $stmt_find_location->get_result();

    if ($res && ($row = $res->fetch_assoc())) {
        return (int)$row['idLocation'];
    }

    $stmt_insert_location->bind_param("ddsss", $latitude, $longitude, $autonomousCommunity, $municipality, $province);
    if (!$stmt_insert_location->execute()) {
        return null;
    }

    return (int)$conn->insert_id;
}