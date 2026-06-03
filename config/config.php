<?php
// Database connection for Railway + Local
$railway_db_url = getenv('DATABASE_URL');

$db_host = getenv('DB_HOST') ?: 'localhost';
$db_name = getenv('DB_NAME') ?: 'academic_system';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASS') ?: '';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // In production, avoid echoing the actual error message if it contains credentials!
    die("Database connection failed.");
}
?>


