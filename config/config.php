
<?php
// =========================================================================
// DATABASE CONFIGURATION - PRODUCTION READY (RAILWAY + LOCAL)
// =========================================================================

// 1. Load Environment Variables (Prioritizes Railway, falls back to Local)
$db_host = getenv('mysql.railway.internal')     ?: 'localhost';
$db_port = getenv('3306')     ?: 3306;
$db_user = getenv('root')     ?: 'root';
$db_pass = getenv('FLExurmFuEiXUJmMiCFwStcBurNCHFXb') ?: '';
$db_name = getenv('academic_system') ?: 'academic_system';

// 2. Build Data Source Name (DSN)
$dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4";

// 3. Establish Secure Connection
try {
    $pdo = new PDO($dsn, $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); 
} catch(PDOException $e) {
    // Graceful error masking for production security
    error_log("Database connection error: " . $e->getMessage());
    die("Database connection failed. Please try again later.");
}
?>