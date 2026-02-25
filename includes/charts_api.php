<?php



header('Content-Type: application/json');
require_once '../config.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'patients_by_month':
        echo json_encode(getPatientsByMonth($pdo));
        break;
    
    case 'revenue_stats':
        echo json_encode(getRevenueStats($pdo));
        break;
    
    case 'appointment_ratios':
        echo json_encode(getAppointmentRatios($pdo));
        break;
    
    case 'top_doctors':
        echo json_encode(getTopDoctors($pdo));
        break;
    
    case 'doctor_personal_stats':
        $doctor_id = intval($_GET['doctor_id'] ?? 0);
        echo json_encode(getDoctorPersonalStats($pdo, $doctor_id));
        break;
    
    default:
        echo json_encode(['error' => 'Invalid action']);
}


function getPatientsByMonth($pdo) {
    $sql = "
        SELECT 
            DATE_FORMAT(updated_at, '%Y-%m') as month,
            DATE_FORMAT(updated_at, '%m/%Y') as month_label,
            COUNT(*) as count
        FROM patreg
        WHERE updated_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY month
        ORDER BY month ASC
    ";
    
    $stmt = $pdo->query($sql);
    $data = $stmt->fetchAll();
    
    return [
        'labels' => array_column($data, 'month_label'),
        'data' => array_column($data, 'count')
    ];
}


function getRevenueStats($pdo) {
    $sql = "
        SELECT 
            DATE_FORMAT(appdate, '%Y-%m') as month,
            DATE_FORMAT(appdate, '%m/%Y') as month_label,
            SUM(docFees) as revenue
        FROM appointmenttb
        WHERE appdate >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            AND (userStatus = 1 OR doctorStatus = 1)
        GROUP BY month
        ORDER BY month ASC
    ";
    
    $stmt = $pdo->query($sql);
    $data = $stmt->fetchAll();
    
    return [
        'labels' => array_column($data, 'month_label'),
        'data' => array_column($data, 'revenue')
    ];
}


function getAppointmentRatios($pdo) {
    $sql = "
        SELECT 
            SUM(CASE WHEN userStatus = 1 AND doctorStatus = 1 THEN 1 ELSE 0 END) as confirmed,
            SUM(CASE WHEN userStatus = 0 OR doctorStatus = 0 THEN 1 ELSE 0 END) as cancelled
        FROM appointmenttb
        WHERE appdate >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    ";
    
    $stmt = $pdo->query($sql);
    $data = $stmt->fetch();
    
    return [
        'labels' => ['Đã xác nhận', 'Đã hủy'],
        'data' => [$data['confirmed'], $data['cancelled']]
    ];
}


function getTopDoctors($pdo) {
    $sql = "
        SELECT 
            d.fullname,
            COUNT(a.ID) as appointment_count
        FROM doctb d
        LEFT JOIN appointmenttb a ON d.username = a.doctor
        WHERE a.appdate >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY d.id, d.fullname
        ORDER BY appointment_count DESC
        LIMIT 10
    ";
    
    $stmt = $pdo->query($sql);
    $data = $stmt->fetchAll();
    
    return [
        'labels' => array_column($data, 'fullname'),
        'data' => array_column($data, 'appointment_count')
    ];
}


function getDoctorPersonalStats($pdo, $doctor_id) {
    
    $doctorStmt = $pdo->prepare("SELECT username FROM doctb WHERE id = ?");
    $doctorStmt->execute([$doctor_id]);
    $doctor = $doctorStmt->fetch();
    
    if (!$doctor) {
        return ['error' => 'Doctor not found'];
    }
    
    
    $sql = "
        SELECT 
            DATE_FORMAT(appdate, '%Y-%m') as month,
            DATE_FORMAT(appdate, '%m/%Y') as month_label,
            COUNT(*) as count
        FROM appointmenttb
        WHERE doctor = ? AND appdate >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY month
        ORDER BY month ASC
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$doctor['username']]);
    $monthly = $stmt->fetchAll();
    
    
    $revenueSql = "
        SELECT 
            DATE_FORMAT(appdate, '%Y-%m') as month,
            DATE_FORMAT(appdate, '%m/%Y') as month_label,
            SUM(docFees) as revenue
        FROM appointmenttb
        WHERE doctor = ? AND appdate >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            AND (userStatus = 1 OR doctorStatus = 1)
        GROUP BY month
        ORDER BY month ASC
    ";
    
    $revenueStmt = $pdo->prepare($revenueSql);
    $revenueStmt->execute([$doctor['username']]);
    $revenue = $revenueStmt->fetchAll();
    
    return [
        'monthly_appointments' => [
            'labels' => array_column($monthly, 'month_label'),
            'data' => array_column($monthly, 'count')
        ],
        'monthly_revenue' => [
            'labels' => array_column($revenue, 'month_label'),
            'data' => array_column($revenue, 'revenue')
        ]
    ];
}
