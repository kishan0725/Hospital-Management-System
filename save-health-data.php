<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pid = $_POST['pid'];
    $weight = $_POST['weight'];
    $bp = $_POST['bp'];
    $sugar = $_POST['sugar'];
    $notes = $_POST['notes'];
    
    date_default_timezone_set('Asia/Kolkata');
    $timestamp = date("Y-m-d H:i:s");

    $stmt = $conn->prepare("INSERT INTO health_logs (pid, weight, bp, sugar, notes, time) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $pid, $weight, $bp, $sugar, $notes, $timestamp);

    if ($stmt->execute()) {
        header("Location: health-tracker.php");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
