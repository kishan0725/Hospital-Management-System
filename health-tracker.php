<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['email']) || !isset($_SESSION['pid'])) {
    echo "User not logged in";
    exit;
}

$pid = $_SESSION['pid'];
$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Health Tracker</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f4f7fa;
            font-family: 'Segoe UI', sans-serif;
        }
        .container {
            background: #fff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 6px 30px rgba(0,0,0,0.15);
            margin-top: 50px;
        }
        h2 {
            font-weight: 700;
            color: #007bff;
            margin-bottom: 25px;
        }
        .form-control {
            border-radius: 8px;
            margin-bottom: 15px;
            border: 1px solid #ced4da;
        }
        .btn-primary {
            background-color: #007bff;
            border-radius: 8px;
        }
        .btn-outline-secondary {
            border-radius: 8px;
        }
        .gap-2 > * {
            margin-right: 12px;
        }
        .table th {
            background-color: #f1f5f9;
            color: #333;
        }
        .table-bordered {
            border-radius: 10px;
            overflow: hidden;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>My Health Tracker</h2>
    <form method="POST" action="save-health-data.php">
        <input type="hidden" name="pid" value="<?= $pid ?>">
        <div class="row">
            <div class="col-md-4">
                <input class="form-control" name="weight" placeholder="Weight (kg)" required>
            </div>
            <div class="col-md-4">
                <input class="form-control" name="bp" placeholder="Blood Pressure (e.g. 120/80)" required>
            </div>
            <div class="col-md-4">
                <input class="form-control" name="sugar" placeholder="Blood Sugar (mg/dL)" required>
            </div>
        </div>
        <div class="form-group">
            <textarea class="form-control" name="notes" rows="3" placeholder="Additional Notes (symptoms, mood, pain level, etc.)..."></textarea>
        </div>
        <div class="mt-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary">Save Record</button>
            <a href="health-dashboard.php" class="btn btn-outline-secondary">Health Log</a>
        </div>
    </form>

    <hr>

    <h4 class="mb-3">Health History</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Date</th>
                <th>Weight</th>
                <th>Blood Pressure</th>
                <th>Blood Sugar</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $result = $conn->query("SELECT * FROM health_logs WHERE pid = '$pid' ORDER BY time DESC");
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                <td>{$row['time']}</td>
                <td>{$row['weight']} kg</td>
                <td>{$row['bp']}</td>
                <td>{$row['sugar']} mg/dL</td>
                <td>" . nl2br(htmlspecialchars($row['notes'])) . "</td>
            </tr>";
        }
        ?>
        </tbody>
    </table>
</div>
</body>
</html>
