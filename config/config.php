<?php
// Try Railway-standard keys first, then fallback to local
$db_host = getenv('MYSQLHOST') ?: getenv('DB_HOST') ?: 'localhost';
$db_name = getenv('MYSQLDATABASE') ?: getenv('DB_NAME') ?: 'academic_system';
$db_user = getenv('MYSQLUSER') ?: getenv('DB_USER') ?: 'root';
$db_pass = getenv('MYSQLPASSWORD') ?: getenv('DB_PASS') ?: '';
$db_port = getenv('MYSQLPORT') ?: '3306';

try {
    // Include port if provided by Railway
    $dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4";
    $pdo = new PDO($dsn, $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    // If it fails, output the error for debugging ONLY during setup
    die("Connection failed: " . $e->getMessage()); 
}
?>