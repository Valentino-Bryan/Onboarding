<?php
// includes/db.php
// PDO connection - no output, no BOM, no echo

$host = '127.0.0.1';
$db   = 'onboarding';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (Throwable $e) {
    // In a dev environment you might want to show the message.
    // But never echo it here. Throw so callers can handle.
    throw $e;
}
