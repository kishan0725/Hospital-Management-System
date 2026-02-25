<?php
session_start();
require_once('../../config.php');

header('Content-Type: application/json');

$doctor = $_SESSION['dname'] ?? null;

if (!$doctor) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}


$stmt = $pdo->prepare("SELECT id FROM doctb WHERE username = :username");
$stmt->execute([':username' => $doctor]);
$doctor_result = $stmt->fetch(PDO::FETCH_ASSOC);
$doctor_id = $doctor_result['id'] ?? null;

if (!$doctor_id || !isset($_POST['record_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

$record_id = intval($_POST['record_id']);
$symptoms = $_POST['symptoms'] ?? '';
$diagnosis = $_POST['diagnosis'] ?? '';
$treatment_plan = $_POST['treatment_plan'] ?? '';
$height = isset($_POST['height']) && $_POST['height'] !== '' ? floatval($_POST['height']) : null;
$weight = isset($_POST['weight']) && $_POST['weight'] !== '' ? floatval($_POST['weight']) : null;
$blood_pressure = $_POST['blood_pressure'] ?? null;
$heart_rate = isset($_POST['heart_rate']) && $_POST['heart_rate'] !== '' ? intval($_POST['heart_rate']) : null;
$temperature = isset($_POST['temperature']) && $_POST['temperature'] !== '' ? floatval($_POST['temperature']) : null;
$notes = $_POST['notes'] ?? null;

try {
    
    $stmt = $pdo->prepare("
        UPDATE medical_records 
        SET symptoms = :symptoms,
            diagnosis = :diagnosis,
            treatment_plan = :treatment_plan,
            height = :height,
            weight = :weight,
            blood_pressure = :blood_pressure,
            heart_rate = :heart_rate,
            temperature = :temperature,
            notes = :notes,
            updated_at = NOW()
        WHERE id = :id AND doctor_id = :doctor_id
    ");

    $stmt->execute([
        ':symptoms' => $symptoms,
        ':diagnosis' => $diagnosis,
        ':treatment_plan' => $treatment_plan,
        ':height' => $height,
        ':weight' => $weight,
        ':blood_pressure' => $blood_pressure,
        ':heart_rate' => $heart_rate,
        ':temperature' => $temperature,
        ':notes' => $notes,
        ':id' => $record_id,
        ':doctor_id' => $doctor_id
    ]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Cập nhật thành công']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Không có thay đổi hoặc không có quyền chỉnh sửa']);
    }
} catch (PDOException $e) {
    error_log("Update record error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
