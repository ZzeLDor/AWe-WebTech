<?php
$conn = oci_connect('webtech', '1234', 'localhost/XE');

if (!$conn) {
    $e = oci_error();
    http_response_code(500);
    echo json_encode(['error' => $e['message']]);
    exit;
}
return $conn;
?>