<?php
// ============================================
// DATABASE CONFIGURATION - COPY THIS FILE
// ============================================
// 1. Copy this file to config.php
// 2. Edit config.php with your database credentials
// 3. DO NOT commit config.php to version control
// ============================================

$db_host = 'localhost';
$db_name = 'academic_system';
$db_user = 'root';
$db_pass = '';

// Create PDO connection
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>