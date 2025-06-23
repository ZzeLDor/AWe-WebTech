<?php
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="accidents_export.csv"');

$conn = require 'db.php';

$start_date = $_GET['from_year'] ?? 2010;
$end_date = $_GET['to_year'] ?? 2022;
$state = $_GET['state'] ?? '';

$sql = "SELECT ID, Source, Severity, TO_CHAR(Start_Time, 'DD-MON-YYYY') AS Start_Time,
               TO_CHAR(End_Time, 'DD-MON-YYYY') AS End_Time,
               Start_Lat, Start_Lng, End_Lat, End_Lng, Distance,
               Description, Street, City, County, State, Zipcode, Country, Timezone,
               Airport_Code, Weather_Condition, Sunrise_Sunset
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

$columns = [
    'ID', 'Source', 'Severity', 'Start_Time', 'End_Time', 'Start_Lat', 'Start_Lng', 'End_Lat', 'End_Lng', 'Distance',
    'Description', 'Street', 'City', 'County', 'State', 'Zipcode', 'Country', 'Timezone',
    'Airport_Code', 'Weather_Condition', 'Sunrise_Sunset'
];

$fp = fopen('php://output', 'w');
fputcsv($fp, $columns);

while (($row = oci_fetch_assoc($stmt)) != false) {
    $csvRow = [];
    foreach ($columns as $col) {
        $csvRow[] = isset($row[strtoupper($col)]) ? $row[strtoupper($col)] : '';
    }
    fputcsv($fp, $csvRow);
}

fclose($fp);

oci_free_statement($stmt);
oci_close($conn);
?>
