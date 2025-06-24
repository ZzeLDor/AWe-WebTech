<?php
header('Content-Type: application/json');

$conn = require 'db.php';
require 'sqlvalidation.php';
$start_date = validateYear($_GET['from_year'] ?? 2010);
$end_date = validateYear($_GET['to_year'] ?? 2022);
$state = validateState($_GET['state'] ?? '');

if ($start_date === false || $end_date === false || $state === false) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input parameters']);
    exit;
}

$sql = "SELECT COUNT(*) AS accidents, SUM(CASE WHEN SUNRISE_SUNSET = 'Day' THEN 1 ELSE 0 END) AS tod,
        SUM(CASE WHEN SEVERITY = 1 THEN 1 ELSE 0 END) AS s1,
        SUM(CASE WHEN SEVERITY = 2 THEN 1 ELSE 0 END) AS s2,
        SUM(CASE WHEN SEVERITY = 3 THEN 1 ELSE 0 END) AS s3,
        SUM(CASE WHEN SEVERITY = 4 THEN 1 ELSE 0 END) AS s4
        FROM accidents
        WHERE EXTRACT(YEAR FROM Start_Time) BETWEEN :start_date AND :end_date";

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

$row = oci_fetch_assoc($stmt);

$accidents = $row['ACCIDENTS'] ?? 0;
$tod = $row['TOD'] ?? 0;
$tod2 = ($accidents > 0) ? $accidents - $tod : 0;
$s1 = $row['S1'] ?? 0;
$s2 = $row['S2'] ?? 0;
$s3 = $row['S3'] ?? 0;
$s4 = $row['S4'] ?? 0;



echo json_encode([
    'accidents' => (int)$accidents,
    'tod' => (int)$tod, 'tod2' => $tod2,
    's1' => (int)$s1,
    's2' => (int)$s2,
    's3' => (int)$s3,
    's4' => (int)$s4,
]);

oci_free_statement($stmt);
oci_close($conn);
?>
