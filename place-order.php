<?php

require_once 'config.php';
include('func.php');
include('newfunc.php');

if (!isset($_SESSION['email']) || !isset($_SESSION['pid'])) {
    echo "User not logged in";
    exit;
}

$pid     = $_SESSION['pid'];
$username= $_SESSION['username'] ?? '';
$email   = $_SESSION['email'];
$fname   = $_SESSION['fname'];
$lname   = $_SESSION['lname'];
$gender  = $_SESSION['gender'];
$contact = $_SESSION['contact'];

$patient_name = $fname . ' ' . $lname;
$order_date = date('Y-m-d H:i:s');
$payment_method = "Cash on Delivery";

if (empty($_POST['items'])) {
    echo "Cart is empty";
    exit;
}

// Database insert
$insert_stmt = $conn->prepare("
    INSERT INTO medicine_orders (pid, patient_name, gender, email, contact, medicine_name, quantity, order_date, payment_method)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");

if (!$insert_stmt) {
    echo "Prepare failed: " . $conn->error;
    exit;
}

foreach ($_POST['items'] as $item) {
    $medicine_name = $item['name'];
    $quantity = $item['quantity'];

    $insert_stmt->bind_param("isssssiss", $pid, $patient_name, $gender, $email, $contact, $medicine_name, $quantity, $order_date, $payment_method);

    if (!$insert_stmt->execute()) {
        echo "Insert failed: " . $insert_stmt->error;
        exit;
    }
}

$insert_stmt->close();
$conn->close();

echo "success";
?>
