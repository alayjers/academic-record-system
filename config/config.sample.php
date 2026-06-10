<?php
// ============================================
// DATABASE CONFIGURATION - SAMPLE FILE
// ============================================
// 1. Copy this file to config.php
// 2. Edit config.php with your local database credentials
// 3. For Railway deployment, no changes needed (uses env variables)
// 4. DO NOT commit config.php to version control
// ============================================

// Local development credentials (fallback)
$db_host = 'localhost';
$db_name = 'academic_system';
$db_user = 'root';
$db_pass = '';

// ============================================
// DATABASE CONNECTION (Works for Local + Railway)
// ============================================

// Check if running on Railway (has DATABASE_URL)
$railway_db_url = getenv('DATABASE_URL');

if ($railway_db_url) {
    // Parse Railway's DATABASE_URL (mysql://user:pass@host:port/database)
    $parsed = parse_url($railway_db_url);
    
    $db_host = $parsed['host'];
    $db_port = $parsed['port'] ?? 3306;
    $db_user = $parsed['user'];
    $db_pass = $parsed['pass'] ?? '';
    $db_name = ltrim($parsed['path'], '/');
    
    // Use host:port format for Railway (avoids socket error)
    $dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name";
} else {
    // Check for individual Railway MySQL variables (fallback)
    $railway_host = getenv('MYSQL_HOST');
    if ($railway_host) {
        $db_host = $railway_host;
        $db_port = getenv('MYSQL_PORT') ?: 3306;
        $db_name = getenv('MYSQL_DATABASE') ?: $db_name;
        $db_user = getenv('MYSQL_USER') ?: $db_user;
        $db_pass = getenv('MYSQL_PASSWORD') ?: $db_pass;
        $dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name";
    } else {
        // Local development
        $dsn = "mysql:host=$db_host;dbname=$db_name";
    }
}

try {
    $pdo = new PDO($dsn, $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>