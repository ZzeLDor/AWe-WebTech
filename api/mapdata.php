<?php
header('Content-Type: application/json');
require 'sqlvalidation.php';
$conn = require 'db.php';

$start_date = validateYear($_GET['from_year'] ?? 2010);
$end_date = validateYear($_GET['to_year'] ?? 2022);
$state = validateState($_GET['state'] ?? '');
if ($start_date === false || $end_date === false || $state === false) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input parameters']);
    exit;
}

$sql = "SELECT Start_Time, Start_Lat, Start_Lng, Severity, Description
        FROM accidents
        WHERE EXTRACT(YEAR FROM Start_Time) BETWEEN :start_date AND :end_date 
        AND Severity > 2";

if (!empty($state)) {
    $sql .= " AND State = :state";
}

$stmt = oci_parse($conn, $sql);

oci_bind_by_name($stmt, ':start_date', $start_date);
oci_bind_by_name($stmt, ':end_date', $end_date);
if (!empty($state)) {
    oci_bind_by_name($stmt, ':state', $state);
}

oci_execute($stmt);

$points = [];
while (($row = oci_fetch_assoc($stmt)) != false) {
    if ($row['START_LAT'] !== null && $row['START_LNG'] !== null) {
        $points[] = [
            'time' => $row['START_TIME'],
            'lat' => (float)$row['START_LAT'],
            'lng' => (float)$row['START_LNG'],
            'weight' => (int)$row['SEVERITY'],
            'description' => $row['DESCRIPTION'] ?? 'No description available'
        ];
    }
}

echo json_encode(['points' => $points]);

oci_free_statement($stmt);
oci_close($conn);
?>
