<?php
header('Content-Type: application/json');
$pdo = require 'db.php';


$from = $_GET['from_year'] ?? 2010;
$to = $_GET['to_year'] ?? 2022;
$state = $_GET['state'] ?? '';

$sql = "SELECT Start_Lat, Start_Lng, Severity
        FROM accidents
        WHERE EXTRACT(YEAR FROM Start_Time) BETWEEN :from AND :to";

if (!empty($state)) {
    $sql .= " AND State = :state";
}


$stmt = $pdo->prepare($sql);
$stmt->bindValue(':from', $from);
$stmt->bindValue(':to', $to);
if (!empty($state)) {
    $stmt->bindValue(':state', $state);
}
$stmt->execute();

$points = [];
while ($row = $stmt->fetch()) {
    if ($row['START_LAT'] && $row['START_LNG']) {
        $points[] = [
            'lat' => (float)$row['START_LAT'],
            'lng' => (float)$row['START_LNG'],
            'weight' => (int)$row['SEVERITY']
        ];
    }
}

echo json_encode(['points' => $points]);
?>
