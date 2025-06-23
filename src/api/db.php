<?php
$user = 'USR';
$pass = 'Qwertyuiop1.';
$service_name = 'e7n1g0no6mbnvbp5_low';

putenv("TNS_ADMIN=C:\\oraclexe\\wallet");

putenv("PATH=C:\\oraclexe\\instantclient_19_26;" . getenv("PATH"));

try {
    $pdo = new PDO(
        "oci:dbname=$service_name;charset=UTF8",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

return $pdo;
