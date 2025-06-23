<?php
header('Content-Type: application/json');

$conn = require 'db.php';

$start_time = $_GET['Start_Time'] ?? '';
$start_lat = $_GET['Start_Lat'] ?? '';
$start_lng = $_GET['Start_Lng'] ?? '';
$severity = $_GET['Severity'] ?? '';
$description = $_GET['Description'] ?? '';
$state = $_GET['State'] ?? '';

$required = ['Start_Time', 'Start_Lat', 'Start_Lng', 'Severity', 'Description', 'State'];
$missing_fields = [];

foreach ($required as $field) {
    if (empty($_GET[$field])) {
        $missing_fields[] = $field;
    }
}

if (!empty($missing_fields)) {
    echo json_encode([
        'success' => false,
        'error' => 'Missing required fields: ' . implode(', ', $missing_fields)
    ]);
    exit;
}

if (!is_numeric($start_lat) || !is_numeric($start_lng)) {
    echo json_encode(['success' => false, 'error' => 'Latitude and Longitude must be numeric']);
    exit;
}

if (!in_array($severity, ['1', '2', '3', '4'])) {
    echo json_encode(['success' => false, 'error' => 'Severity must be 1, 2, 3, or 4']);
    exit;
}

try {
    $sql = "INSERT INTO accidents (Start_Time, Start_Lat, Start_Lng, Severity, Description, State) 
            VALUES (TO_DATE(:start_time, 'YYYY-MM-DD'), :start_lat, :start_lng, :severity, :description, :state)";

    $stmt = oci_parse($conn, $sql);

    oci_bind_by_name($stmt, ':start_time', $start_time);
    oci_bind_by_name($stmt, ':start_lat', $start_lat);
    oci_bind_by_name($stmt, ':start_lng', $start_lng);
    oci_bind_by_name($stmt, ':severity', $severity);
    oci_bind_by_name($stmt, ':description', $description);
    oci_bind_by_name($stmt, ':state', $state);

    $result = oci_execute($stmt);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Accident added successfully',
            'data' => [
                'start_time' => $start_time,
                'start_lat' => (float)$start_lat,
                'start_lng' => (float)$start_lng,
                'severity' => (int)$severity,
                'description' => $description,
                'state' => $state
            ]
        ]);
    } else {
        $error = oci_error($stmt);
        echo json_encode(['success' => false, 'error' => $error['message']]);
    }

    oci_free_statement($stmt);

} catch (Exception $ex) {
    echo json_encode(['success' => false, 'error' => $ex->getMessage()]);
}

oci_close($conn);
?>