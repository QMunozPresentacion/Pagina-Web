<?php
require_once "./database.php";

$queries = [

    "CREATE TABLE IF NOT EXISTS publication (
        idPublication VARCHAR(100) PRIMARY KEY,
        overallSeverity DECIMAL(10,6),
        publicationTime DATETIME,
        collectedAt DATETIME
    ) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS situation_record (
        idRecord VARCHAR(100) PRIMARY KEY,
        publication_id VARCHAR(100),
        source VARCHAR(255),
        causeType VARCHAR(255),
        severity INT,
        FOREIGN KEY (publication_id)
            REFERENCES publication(idPublication)
            ON DELETE CASCADE
    ) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS location (
        idLocation INT AUTO_INCREMENT PRIMARY KEY,
        latitude DECIMAL(10,6),
        longitude DECIMAL(10,6),
        autonomousCommunity VARCHAR(150),
        municipality VARCHAR(150),
        province VARCHAR(150),
        UNIQUE KEY unique_location (
            latitude, longitude,
            autonomousCommunity,
            municipality,
            province
        )
    ) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS situation_location (
        idRecord VARCHAR(100),
        idLocation INT,
        locationType ENUM('SINGLE','START','END'),
        PRIMARY KEY (idRecord, idLocation),
        FOREIGN KEY (idRecord)
            REFERENCES situation_record(idRecord)
            ON DELETE CASCADE,
        FOREIGN KEY (idLocation)
            REFERENCES location(idLocation)
            ON DELETE CASCADE
    ) ENGINE=InnoDB"
];

foreach ($queries as $sql) {
    if (!$conn->query($sql)) {
        die("Error creando tablas: " . $conn->error);
    }
}

echo "<h2>Tablas creadas correctamente</h2>";