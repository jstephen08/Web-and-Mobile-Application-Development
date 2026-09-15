<?php
// --- Database connection (edit these if your XAMPP setup differs) ---
$DB_HOST = 'localhost';
$DB_NAME = 'nsbm_eventhub';
$DB_USER = 'root';
$DB_PASS = ''; // default XAMPP MySQL root password is blank

try {
    $pdo = new PDO(
        "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database connection failed. Did you import schema.sql?']);
    exit;
}
