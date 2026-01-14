<?php
session_start();
$pid = $_SESSION['pid'] ?? '';
$fname = $_SESSION['fname'] ?? '';
$lname = $_SESSION['lname'] ?? '';
$contact = $_SESSION['contact'] ?? '';
$patient_name = $fname . ' ' . $lname;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ambulance Booking</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f0f4f8;
            padding: 40px;
        }

        .container {
            max-width: 600px;
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

        label {
            display: block;
            margin-top: 15px;
            font-weight: 500;
            color: #333;
        }

        input, select, textarea {
            width: 100%;
            padding: 12px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
        }

        button {
            margin-top: 25px;
            width: 100%;
            padding: 12px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        .success-msg {
            color: green;
            text-align: center;
            margin-top: 15px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Ambulance Booking Form</h2>
    <form action="ambulancebooking.php" method="POST">
        <input type="hidden" name="pid" value="<?= $pid ?>">

        <label>Patient Name:</label>
        <input type="text" name="patient_name" value="<?= $patient_name ?>" required>

        <label>Contact Number:</label>
        <input type="text" name="contact" value="<?= $contact ?>" required>

        <label>Ambulance Type:</label>
        <select name="ambulance_type" required>
            <option value="">-- Select Type --</option>
            <option value="Basic Life Support (BLS)">Basic Life Support (BLS)</option>
            <option value="Advanced Life Support (ALS)">Advanced Life Support (ALS)</option>
            <option value="Patient Transport Ambulance">Patient Transport Ambulance</option>
        </select>

        <label>Pickup Location:</label>
        <textarea name="pickup_location" rows="3" required></textarea>

        <label>Booking Date & Time:</label>
        <input type="datetime-local" name="booking_datetime" required>

        <button type="submit">Book Ambulance</button>
    </form>
</div>

</body>
</html>
