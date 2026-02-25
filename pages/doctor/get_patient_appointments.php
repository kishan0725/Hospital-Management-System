<?php
session_start();
require_once('../../config.php');

header('Content-Type: application/json');

$doctor = $_SESSION['dname'] ?? null;

if (!$doctor) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if (!isset($_GET['patient_id']) || !isset($_GET['doctor_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

$patient_id = intval($_GET['patient_id']);
$doctor_id = intval($_GET['doctor_id']);

try {
    
    $stmt = $pdo->prepare("SELECT fullname FROM doctb WHERE id = :doctor_id");
    $stmt->execute([':doctor_id' => $doctor_id]);
    $doctor_info = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$doctor_info) {
        echo json_encode(['success' => false, 'message' => 'Doctor not found']);
        exit();
    }

    $doctor_fullname = $doctor_info['fullname'];

    
    $stmt = $pdo->prepare("
        SELECT ID, appdate, apptime, userStatus, doctorStatus
        FROM appointmenttb
        WHERE pid = :patient_id 
        AND TRIM(doctor) = TRIM(:doctor_name)
        ORDER BY appdate DESC, apptime DESC
    ");
    $stmt->execute([
        ':patient_id' => $patient_id,
        ':doctor_name' => $doctor_fullname
    ]);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    
    $formatted_appointments = [];
    foreach ($appointments as $apt) {
        $status = '';
        if ($apt['userStatus'] == 1 && $apt['doctorStatus'] == 1) {
            $status = ' (Hoạt động)';
        } else {
            $status = ' (Đã hủy)';
        }

        $formatted_appointments[] = [
            'ID' => $apt['ID'],
            'appdate' => date('d/m/Y', strtotime($apt['appdate'])),
            'apptime' => date('H:i', strtotime($apt['apptime'])),
            'status' => $status,
            'userStatus' => $apt['userStatus'],
            'doctorStatus' => $apt['doctorStatus']
        ];
    }

    echo json_encode([
        'success' => true,
        'appointments' => $formatted_appointments,
        'count' => count($formatted_appointments)
    ]);
} catch (PDOException $e) {
    error_log("Get patient appointments error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
