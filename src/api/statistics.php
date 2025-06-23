<?php
header('Content-Type: application/json');
$pdo = require 'db.php';


$from = $_GET['from_year'] ?? 2010;
$to = $_GET['to_year'] ?? 2022;
$state = $_GET['state'] ?? '';

$sql = "SELECT COUNT(*) AS ACCIDENTS, SUM(Severity) AS CASUALTIES
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
$row = $stmt->fetch();

$accidents = (int)($row['ACCIDENTS'] ?? 0);
$casualties = (int)($row['CASUALTIES'] ?? 0);
$avg = $accidents > 0 ? $casualties / $accidents : 0;

echo json_encode([
    'accidents' => $accidents,
    'casualties' => $casualties,
    'avg_casualties' => round($avg, 2)
]);
?>
