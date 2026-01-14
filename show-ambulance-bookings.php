<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['pid'])) {
    echo "You must be logged in to view your bookings.";
    exit;
}

$pid = $_SESSION['pid'];

// Fetch bookings
$stmt = $conn->prepare("SELECT patient_name, contact, ambulance_type, pickup_location, booking_datetime, request_time FROM ambulance_bookings WHERE pid = ? ORDER BY request_time DESC");
$stmt->bind_param("i", $pid);
$stmt->execute();
$result = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Ambulance Bookings</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #eef2f5;
            padding: 40px;
        }

        .container {
            max-width: 1000px;
            margin: auto;
            background: #fff;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            padding: 12px 15px;
            border: 1px solid #ccc;
            text-align: left;
        }

        th {
            background-color: #007BFF;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .no-data {
            text-align: center;
            color: #777;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Your Ambulance Bookings</h2>

    <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Patient Name</th>
                    <th>Contact</th>
                    <th>Ambulance Type</th>
                    <th>Pickup Location</th>
                    <th>Booking Date & Time</th>
                    <th>Request Submitted At</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['patient_name']) ?></td>
                        <td><?= htmlspecialchars($row['contact']) ?></td>
                        <td><?= htmlspecialchars($row['ambulance_type']) ?></td>
                        <td><?= htmlspecialchars($row['pickup_location']) ?></td>
                        <td><?= htmlspecialchars($row['booking_datetime']) ?></td>
                        <td><?= htmlspecialchars($row['request_time']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="no-data">No bookings found.</p>
    <?php endif; ?>

</div>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
