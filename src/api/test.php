<?php
putenv("TNS_ADMIN=C:/oraclexe/wallet");

try {
    $conn = new PDO(
        'oci:dbname=e7n1g0no6mbnvbp5_high;charset=UTF8',
        'USR',
        'Qwertyuiop1.',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    echo "Connected!";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

?>
