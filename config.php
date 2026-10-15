<?php
// Mirza Bot (NOVA-X-PANEL Edition) Configuration
$request_exec_timeout = null;
$dbhost = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: 'mirzabot';
$usernamedb = getenv('DB_USER') ?: 'mirzauser';
$passworddb = getenv('DB_PASS') ?: 'MirzaDbSecure_Nova!';
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
];
$dsn = "mysql:host=$dbhost;dbname=$dbname;charset=utf8mb4";
try {
    $pdo = new PDO($dsn, $usernamedb, $passworddb, $options);
} catch (\PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
}
$APIKEY = '{API_KEY}';
$adminnumber = '{ADMIN_NUMBER}';
$domainhosts = '{DOMAIN_HOSTS}';
$usernamebot = '{BOT_USERNAME}';
?>
