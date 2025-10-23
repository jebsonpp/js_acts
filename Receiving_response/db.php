<?php
// db.php
// DB connection via PDO. Update credentials below.

$DB_HOST = '127.0.0.1';
$DB_NAME = 'receiving_response';
$DB_USER = 'root';
$DB_PASS = '';
$DSN = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($DSN, $DB_USER, $DB_PASS, $options);
} catch (PDOException $e) {
    http_response_code(500);
    echo "DB connection failed: " . $e->getMessage();
    exit;
}
