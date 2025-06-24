<?php
header('Content-Type: image/webp');
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

$width = 600;
$height = 400;
$image = imagecreatetruecolor($width, $height);

$white = imagecolorallocate($image, 255, 255, 255);
$black = imagecolorallocate($image, 0, 0, 0);
$red = imagecolorallocate($image, 220, 20, 60);
$orange = imagecolorallocate($image, 255, 140, 0);
$yellow = imagecolorallocate($image, 255, 215, 0);
$green = imagecolorallocate($image, 34, 139, 34);

imagefill($image, 0, 0, $white);

$title = "Accident Severity Report - State: $state ($start_date - $end_date)";
imagestring($image, 5, 20, 20, $title, $black);
imagestring($image, 4, 20, 50, "Total accidents: $accidents", $black);

$max_value = max($s1, $s2, $s3, $s4, 1);
$bar_width = 80;
$spacing = 40;
$base_y = 350;

$bars = [
    ['label' => 'Severity 1', 'value' => $s1, 'color' => $green],
    ['label' => 'Severity 2', 'value' => $s2, 'color' => $yellow],
    ['label' => 'Severity 3', 'value' => $s3, 'color' => $orange],
    ['label' => 'Severity 4', 'value' => $s4, 'color' => $red],
];

$x = 60;
foreach ($bars as $bar) {
    $bar_height = (int)(250 * $bar['value'] / $max_value);
    imagefilledrectangle($image, $x, $base_y - $bar_height, $x + $bar_width, $base_y, $bar['color']);
    imagestring($image, 3, $x, $base_y + 5, $bar['label'], $black);
    imagestring($image, 3, $x, $base_y - $bar_height - 15, $bar['value'], $black);
    $x += $bar_width + $spacing;
}

imagewebp($image);

imagedestroy($image);
oci_free_statement($stmt);
oci_close($conn);
?>
