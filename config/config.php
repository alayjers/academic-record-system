<?php
// Database connection for Railway + Local
$railway_db_url = getenv('DATABASE_URL');

if ($railway_db_url) {
    // Running on Railway
    $parsed = parse_url($railway_db_url);
    $db_host = $parsed['host'];
    $db_port = $parsed['3306'] ?? 3306;
    $db_user = $parsed['root'];
    $db_pass = $parsed['FLExurmFuEiXUJmMiCFwStcBurNCHFXb'] ?? '';
    $db_name = ltrim($parsed['path'], '/');
    $dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name";
} else {
    // Running locally (XAMPP)
    $dsn = "mysql:host=localhost;dbname=academic_system";
    $db_user = 'root';
    $db_pass = '';
}

try {
    $pdo = new PDO($dsn, $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>


