<?php
session_start();
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $pid = $_POST['pid'] ?? null;
    $patient_name = $_POST['patient_name'];
    $contact = $_POST['contact'];
    $ambulance_type = $_POST['ambulance_type'];
    $pickup_location = $_POST['pickup_location'];
    $booking_datetime = $_POST['booking_datetime'];

    if (empty($pid) || empty($patient_name) || empty($contact) || empty($ambulance_type) || empty($pickup_location) || empty($booking_datetime)) {
        echo "Missing required fields.";
        exit;
    }

    $stmt = $conn->prepare("
        INSERT INTO ambulance_bookings 
        (pid, patient_name, contact, ambulance_type, pickup_location, booking_datetime)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        echo "Database error: " . $conn->error;
        exit;
    }

    $stmt->bind_param("isssss", $pid, $patient_name, $contact, $ambulance_type, $pickup_location, $booking_datetime);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "<script>alert('Ambulance booked successfully!'); window.location.href='book-ambulance.php';</script>";
    } else {
        echo "Booking failed.";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request method.";
}
