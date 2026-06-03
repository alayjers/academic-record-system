<?php
// ============================================
// DATABASE CONFIGURATION - RAILWAY DEPLOYMENT
// ============================================

// Get database URL from Railway environment variable
$db_url = getenv('DATABASE_URL');

if ($db_url) {
    // Parse Railway's DATABASE_URL (postgresql://...)
    // For MySQL, Railway provides MYSQL_URL
    $db_host = getenv('MYSQL_HOST') ?: 'localhost';
    $db_name = getenv('MYSQL_DATABASE') ?: 'academic_system';
    $db_user = getenv('MYSQL_USER') ?: 'root';
    $db_pass = getenv('MYSQL_PASSWORD') ?: '';
} else {
    // Local development fallback
    $db_host = 'localhost';
    $db_name = 'academic_system';
    $db_user = 'root';
    $db_pass = '';
}

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>