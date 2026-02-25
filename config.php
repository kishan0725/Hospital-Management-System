<?php
define('DB_HOST', 'host-244160.nvme.io.vn');
define('DB_USER', 'chuduyit_k73');
define('DB_PASS', 'chuduyisme@123');
define('DB_NAME', 'chuduyit_medical_k73');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $pdo->exec("SET NAMES 'utf8mb4'");
} catch (PDOException $e) {
    error_log("Database Connection Error: " . $e->getMessage());
    die("Database connection failed. Please contact the administrator.");
}

function getDB()
{
    global $pdo;
    return $pdo;
}
$con = $pdo;
