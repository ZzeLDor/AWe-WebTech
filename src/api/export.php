<?php
// export.php

$pdo = require 'db.php';

$from = $_GET['from_year'] ?? 2010;
$to = $_GET['to_year'] ?? 2022;
$state = $_GET['state'] ?? '';
$format = $_GET['format'] ?? 'csv';
$mode = $_GET['mode'] ?? 'data'; 


$from = (int)$from;
$to = (int)$to;
$format = strtolower($format);
$allowedFormats = ['csv', 'webp'];

if (!in_array($format, $allowedFormats)) {
    http_response_code(400);
    echo json_encode(['error' => 'Unsupported format']);
    exit;
}

if ($mode === 'count') {
    $sql = "SELECT COUNT(*) as cnt FROM accidente WHERE YEAR(date) BETWEEN :from AND :to";
    if (!empty($state)) {
        $sql .= " AND State = :state";
    }
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':from', $from, PDO::PARAM_INT);
    $stmt->bindValue(':to', $to, PDO::PARAM_INT);
    if (!empty($state)) {
        $stmt->bindValue(':state', $state, PDO::PARAM_STR);
    }
    $stmt->execute();
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
    header('Content-Type: application/json');
    echo json_encode(['count' => (int)$count]);
    exit;
}


$sql = "SELECT * FROM accidente WHERE YEAR(date) BETWEEN :from AND :to";
if (!empty($state)) {
    $sql .= " AND State = :state";
}
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':from', $from, PDO::PARAM_INT);
$stmt->bindValue(':to', $to, PDO::PARAM_INT);
if (!empty($state)) {
    $stmt->bindValue(':state', $state, PDO::PARAM_STR);
}
$stmt->execute();

if ($format === 'csv') {
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="accidents_export.csv"');

    $output = fopen('php://output', 'w');
    if (!empty($rows)) {
        fputcsv($output, array_keys($rows[0]));
        foreach ($rows as $row) {
            fputcsv($output, $row);
        }
    }
    fclose($output);
    exit;
}

if ($format === 'webp') {


    $background = imagecreatefrompng('us_map1.png');

    if (!$background) {
        http_response_code(500);
        echo json_encode(['error' => 'Background map not found']);
        exit;
    }

    $width = imagesx($background);
    $height = imagesy($background);

    // Create new image based on background
    $img = imagecreatetruecolor($width, $height);
    imagecopy($img, $background, 0, 0, 0, 0, $width, $height);

    $red = imagecolorallocatealpha($img, 255, 0, 0, 80);


    $minLat = 24.396308;
    $maxLat = 49.384358;
    $minLng = -125.0;
    $maxLng = -66.93457;

    // Draw points (fetch row by row to reduce memory)
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (!empty($row['Start_Lat']) && !empty($row['Start_Lng'])) {
            $x = intval(($row['Start_Lng'] - $minLng) / ($maxLng - $minLng) * $width);
            $y = intval(($maxLat - $row['Start_Lat']) / ($maxLat - $minLat) * $height);

            $radius = max(2, intval($row['severity'] ?? 1));
            imagefilledellipse($img, $x, $y, $radius * 2, $radius * 2, $red);
        }
    }

    header('Content-Type: image/webp');
    header('Content-Disposition: attachment; filename="accidents_heatmap.webp"');
    imagewebp($img);
    imagedestroy($img);
    imagedestroy($background);
    exit;
}
?>
