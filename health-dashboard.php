<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['pid'])) {
    echo "User not logged in";
    exit;
}
$pid = $_SESSION['pid'];
date_default_timezone_set("Asia/Kolkata");

$data = [
    'labels' => [],
    'weight' => [],
    'sugar' => [],
    'bp' => [],
    'weight_status' => ['Underweight' => 0, 'Normal' => 0, 'Overweight' => 0]
];

$res = $conn->query("SELECT date, weight, sugar, bp FROM health_logs WHERE pid=$pid ORDER BY date ASC");
while ($row = $res->fetch_assoc()) {
    $data['labels'][] = $row['date'];
    $data['weight'][] = $row['weight'];
    $data['sugar'][] = $row['sugar'];
    $data['bp'][] = $row['bp'];

    $weight = $row['weight'];
    if ($weight < 50) $data['weight_status']['Underweight']++;
    elseif ($weight >= 50 && $weight <= 70) $data['weight_status']['Normal']++;
    else $data['weight_status']['Overweight']++;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Health Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background: #f4f7fa;
            font-family: 'Segoe UI', sans-serif;
        }
        .container {
            margin-top: 30px;
            max-width: 900px;
        }
        h2 {
            font-weight: 600;
            color: #007bff;
            margin-bottom: 20px;
        }
        .chart-container {
            background: #fff;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 3px 20px rgba(0,0,0,0.08);
            margin-bottom: 25px;
        }
        canvas {
            max-height: 260px !important;
        }
        h5 {
            margin-bottom: 15px;
            color: #343a40;
            font-size: 1rem;
        }
    </style>
</head>
<body>
<div class="container">
    <h2 class="text-center">Health Analytics Dashboard</h2>

    <div class="chart-container">
        <h5>📈 Weight & Sugar Over Time</h5>
        <canvas id="lineChart"></canvas>
    </div>

    <div class="chart-container">
        <h5>📊 Blood Sugar Levels</h5>
        <canvas id="barChart"></canvas>
    </div>

    <div class="chart-container">
        <h5>🥧 Weight Category Distribution</h5>
        <canvas id="pieChart"></canvas>
    </div>
</div>

<script>
    const labels = <?= json_encode($data['labels']) ?>;
    const weightData = <?= json_encode($data['weight']) ?>;
    const sugarData = <?= json_encode($data['sugar']) ?>;
    const weightStatus = <?= json_encode(array_values($data['weight_status'])) ?>;

    // Line Chart
    new Chart(document.getElementById('lineChart'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Weight (kg)',
                    data: weightData,
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0,123,255,0.15)',
                    fill: true,
                    tension: 0.3
                },
                {
                    label: 'Sugar (mg/dL)',
                    data: sugarData,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40,167,69,0.15)',
                    fill: true,
                    tension: 0.3
                }
            ]
        },
        options: {
            responsive: true,
            scales: { y: { beginAtZero: true } },
            plugins: {
                legend: { position: 'top' }
            }
        }
    });

    // Bar Chart
    new Chart(document.getElementById('barChart'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Blood Sugar (mg/dL)',
                data: sugarData,
                backgroundColor: '#ffc107'
            }]
        },
        options: {
            responsive: true,
            scales: { y: { beginAtZero: true } }
        }
    });

    // Pie Chart
    new Chart(document.getElementById('pieChart'), {
        type: 'pie',
        data: {
            labels: ['Underweight', 'Normal', 'Overweight'],
            datasets: [{
                data: weightStatus,
                backgroundColor: ['#17a2b8', '#28a745', '#dc3545']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
</script>
</body>
</html>
