<?php

$database = "project1";
$dbUser   = "robert";
$dbPass   = "12345";

$pdo       = new PDO("mysql:host=localhost;dbname={$database}", $dbUser, $dbPass);
$batch     = [];
$batchSize = 500;

foreach (parseCsvGenerator('US_Accidents_March23.csv') as $row) {
    $batch[] = $row;

    if (count($batch) >= $batchSize) {
        insertBatch($pdo, $batch);
        $batch = [];
    }
}

if ($batch) {
    insertBatch($pdo, $batch);
}

function parseCsvGenerator(string $filePath): Generator {
    $handle = fopen($filePath, 'r');
    if (!$handle) return;

    $headerLine = trim(fgets($handle));
    if ($headerLine === '') return;

    $headers = str_getcsv($headerLine, ',', '"', '\\');
    $map = array_flip($headers);

    while (($line = fgets($handle)) !== false) {
        $line = trim($line);
        if ($line === '') continue;

        $values = str_getcsv($line, ',', '"', '\\');
        if (count($values) !== count($headers)) continue;

        yield [
            'severity'   => $values[$map['Severity']] ?? null,
            'city'       => $values[$map['City']] ?? null,
            'county'     => $values[$map['County']] ?? null,
            'state'      => $values[$map['State']] ?? null,
            'zipcode'    => $values[$map['Zipcode']] ?? null,
            'country'    => $values[$map['Country']] ?? null,
            'start_time' => $values[$map['Start_Time']] ?? null,
        ];
    }

    fclose($handle);
}


function insertBatch(PDO $pdo, array $batch): void {
    $placeholders = [];
    $params       = [];

    foreach ($batch as $row) {
        $placeholders[] = '(?, ?, ?, ?, ?, ?, ?)';
        $params[] = $row['severity'];
        $params[] = $row['city'];
        $params[] = $row['county'];
        $params[] = $row['state'];
        $params[] = $row['zipcode'];
        $params[] = $row['country'];
        $params[] = $row['start_time'];
    }

    $sql = 'INSERT INTO accidente (severity, city, county, state, zipcode, country, date) VALUES ' . implode(', ', $placeholders);
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
}