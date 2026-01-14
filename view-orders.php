<?php

require_once 'config.php';
include('func.php');
include('newfunc.php');

// Redirect if not logged in
if (!isset($_SESSION['email']) || !isset($_SESSION['pid'])) {
    echo "You must be logged in to view your orders.";
    exit;
}

$pid = $_SESSION['pid'];

// Fetch orders from database
$query = $conn->prepare("SELECT medicine_name, quantity, order_date, payment_method FROM medicine_orders WHERE pid = ? ORDER BY order_date DESC");
$query->bind_param("i", $pid);
$query->execute();
$result = $query->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}

$query->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Medicine Orders</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 20px;
        }

        h2 {
            text-align: center;
            color: #333;
        }

        table {
            margin: 0 auto;
            width: 90%;
            border-collapse: collapse;
            background: #fff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            border-radius: 10px;
            overflow: hidden;
        }

        th, td {
            padding: 15px 20px;
            text-align: center;
        }

        th {
            background: #007BFF;
            color: white;
            text-transform: uppercase;
            font-size: 14px;
        }

        tr:nth-child(even) {
            background: #f2f2f2;
        }

        tr:hover {
            background: #d6e9f9;
        }

        .back-btn {
            display: block;
            width: 200px;
            margin: 20px auto;
            padding: 10px 20px;
            text-align: center;
            background: #007BFF;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
        }

        .back-btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>

    <h2>Your Medicine Orders</h2>

    <?php if (count($orders) === 0): ?>
        <p style="text-align:center;">You have not placed any orders yet.</p>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Medicine Name</th>
                <th>Quantity</th>
                <th>Order Date</th>
                <th>Payment Method</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
            <tr>
                <td><?= htmlspecialchars($order['medicine_name']) ?></td>
                <td><?= htmlspecialchars($order['quantity']) ?></td>
                <td><?= date("d-m-Y h:i A", strtotime($order['order_date'])) ?></td>
                <td><?= htmlspecialchars($order['payment_method']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <a href="order-medicine.html" class="back-btn">Back to Order Page</a>

</body>
</html>
