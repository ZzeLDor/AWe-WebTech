<?php
header('Content-Type: image/svg+xml');
$conn = require 'db.php';

$start_date = $_GET['from_year'] ?? 2010;
$end_date = $_GET['to_year'] ?? 2022;
$state = $_GET['state'] ?? '';

$sql = "SELECT 
            COUNT(*) AS accidents,
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
$s1 = $row['S1'] ?? 0;
$s2 = $row['S2'] ?? 0;
$s3 = $row['S3'] ?? 0;
$s4 = $row['S4'] ?? 0;

$bars = [
    ['label' => 'Severity 1', 'value' => $s1, 'color' => '#228B22'],
    ['label' => 'Severity 2', 'value' => $s2, 'color' => '#FFD700'],
    ['label' => 'Severity 3', 'value' => $s3, 'color' => '#FF8C00'],
    ['label' => 'Severity 4', 'value' => $s4, 'color' => '#DC143C'],
];

$max_value = max($s1, $s2, $s3, $s4, 1);
$width = 600;
$height = 400;
$bar_width = 80;
$spacing = 40;
$base_y = 350;
$x = 60;

echo "<?xml version='1.0' encoding='UTF-8'?>";
echo "<svg width='$width' height='$height' xmlns='http://www.w3.org/2000/svg'>";
echo "<rect width='100%' height='100%' fill='white' />";
echo "<text x='20' y='30' font-size='20' fill='black'>Accident Severity Report - State: $state ($start_date - $end_date)</text>";
echo "<text x='20' y='60' font-size='16' fill='black'>Total accidents: $accidents</text>";

foreach ($bars as $bar) {
    $bar_height = (int)(250 * $bar['value'] / $max_value);
    $y = $base_y - $bar_height;
    echo "<rect x='$x' y='$y' width='$bar_width' height='$bar_height' fill='{$bar['color']}' />";
    echo "<text x='$x' y='" . ($base_y + 20) . "' font-size='12' fill='black'>{$bar['label']}</text>";
    echo "<text x='$x' y='" . ($y - 5) . "' font-size='12' fill='black'>{$bar['value']}</text>";
    $x += $bar_width + $spacing;
}

echo "</svg>";

oci_free_statement($stmt);
oci_close($conn);
?>
