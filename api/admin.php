<?php
header('Content-Type: application/json');

$conn = require 'db.php';
require 'sqlvalidation.php';
$start_time = validateDate($_GET['Start_Time'] ?? '');
$start_lat = validateCoordinate($_GET['Start_Lat'] ?? '');
$start_lng = validateCoordinate($_GET['Start_Lng'] ?? '');
$severity = validateSeverity($_GET['Severity'] ?? '');
$description = $_GET['Description'] ?? '';
$state = validateState($_GET['State'] ?? '');

$errors = [];
if ($start_time === false) $errors[] = 'Invalid Start_Time format (use YYYY-MM-DD)';
if ($start_lat === false) $errors[] = 'Invalid Start_Lat (must be numeric)';
if ($start_lng === false) $errors[] = 'Invalid Start_Lng (must be numeric)';
if ($severity === false) $errors[] = 'Invalid Severity (must be 1-4)';
if (empty($description)) $errors[] = 'Description is required';
if ($state === false) $errors[] = 'Invalid State code';

if (!empty($errors)) {
    echo json_encode([
        'success' => false,
        'error' => 'Validation errors: ' . implode(', ', $errors)
    ]);
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