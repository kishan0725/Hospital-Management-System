<?php
// config.php - Database connection config
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');    // Set your MySQL password here
define('DB_NAME', 'myhmsdb');  // Change to your DB name
date_default_timezone_set('Asia/Kolkata');


$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
