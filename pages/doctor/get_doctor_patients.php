<?php
session_start();
require_once('../../config.php');

header('Content-Type: application/json');

$doctor = $_SESSION['dname'] ?? null;

if (!$doctor) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if (!isset($_GET['doctor_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

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
        SELECT DISTINCT p.pid, p.fname, p.lname, p.contact, p.email
        FROM patreg p
        INNER JOIN appointmenttb a ON p.pid = a.pid
        WHERE TRIM(a.doctor) = TRIM(:doctor_name)
        ORDER BY p.fname, p.lname
    ");
    $stmt->execute([':doctor_name' => $doctor_fullname]);
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'patients' => $patients,
        'count' => count($patients)
    ]);
} catch (PDOException $e) {
    error_log("Get doctor patients error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
