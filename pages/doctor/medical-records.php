<?php
ob_start();
session_start();

set_exception_handler(function ($e) {
    error_log("Doctor medical-records uncaught: " . $e->getMessage());
    while (ob_get_level()) ob_end_clean();
    http_response_code(500);
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Lỗi</title><link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"></head><body class="bg-light"><div class="container mt-5"><div class="alert alert-danger"><h4>Lỗi</h4><p>' . htmlspecialchars($e->getMessage()) . '</p><a href="dashboard.php" class="btn btn-sm btn-outline-danger">Quay lại</a></div></div></body></html>';
    exit;
});
register_shutdown_function(function () {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        while (ob_get_level()) ob_end_clean();
        http_response_code(500);
        echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Lỗi Server</title><link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"></head><body class="bg-light"><div class="container mt-5"><div class="alert alert-danger"><h4>Lỗi Server</h4><p>' . htmlspecialchars($err['message']) . '</p><a href="dashboard.php" class="btn btn-sm btn-outline-danger">Quay lại</a></div></div></body></html>';
    }
});

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/messages.php';
require_once __DIR__ . '/../../includes/functions.php';

$doctor = $_SESSION['dname'] ?? null;

if (!$doctor) {
    header("Location: ../../pages/auth/login.php");
    exit();
}


$page = isset($_GET['page']) ? $_GET['page'] : 'view';
$allowed_pages = array('view', 'add', 'search');
if (!in_array($page, $allowed_pages)) {
    $page = 'view';
}


if (isset($_POST['add_medical_record'])) {
    try {
        $patient_id = $_POST['patient_id'];
        $appointment_id = isset($_POST['appointment_id']) && $_POST['appointment_id'] !== '' ? $_POST['appointment_id'] : null;
        $symptoms = $_POST['symptoms'];
        $diagnosis = $_POST['diagnosis'];
        $treatment = $_POST['treatment'];
        $notes = isset($_POST['notes']) && $_POST['notes'] !== '' ? $_POST['notes'] : null;
        $height = isset($_POST['height']) && $_POST['height'] !== '' ? floatval($_POST['height']) : null;
        $weight = isset($_POST['weight']) && $_POST['weight'] !== '' ? floatval($_POST['weight']) : null;
        $blood_pressure = isset($_POST['blood_pressure']) && $_POST['blood_pressure'] !== '' ? $_POST['blood_pressure'] : null;
        $heart_rate = isset($_POST['heart_rate']) && $_POST['heart_rate'] !== '' ? intval($_POST['heart_rate']) : null;
        $temperature = isset($_POST['temperature']) && $_POST['temperature'] !== '' ? floatval($_POST['temperature']) : null;

        
        $stmt = $pdo->prepare("SELECT id FROM doctb WHERE username = :username");
        $stmt->execute([':username' => $doctor]);
        $doctor_result = $stmt->fetch(PDO::FETCH_ASSOC);
        $doctor_id = $doctor_result['id'] ?? null;

        $stmt = $pdo->prepare("
            INSERT INTO medical_records (
                patient_id, doctor_id, appointment_id, record_date, symptoms, diagnosis, treatment_plan,
                notes, height, weight, blood_pressure, heart_rate, temperature, status, created_by, created_at, updated_at
            ) VALUES (
                :patient_id, :doctor_id, :appointment_id, CURDATE(), :symptoms, :diagnosis, :treatment,
                :notes, :height, :weight, :blood_pressure, :heart_rate, :temperature, 'completed', " . intval($doctor_id) . ", NOW(), NOW()
            )
        ");
        $stmt->execute([
            ':patient_id' => $patient_id,
            ':doctor_id' => $doctor_id,
            ':appointment_id' => $appointment_id,
            ':symptoms' => $symptoms,
            ':diagnosis' => $diagnosis,
            ':treatment' => $treatment,
            ':notes' => $notes,
            ':height' => $height,
            ':weight' => $weight,
            ':blood_pressure' => $blood_pressure,
            ':heart_rate' => $heart_rate,
            ':temperature' => $temperature
        ]);
        redirectWithMessage($_SERVER['PHP_SELF'], 'success', 'Hồ sơ bệnh án đã được thêm thành công!');
    } catch (PDOException $e) {
        error_log("Add medical record error: " . $e->getMessage());
        redirectWithMessage($_SERVER['PHP_SELF'], 'error', 'Lỗi khi thêm hồ sơ bệnh án: ' . $e->getMessage());
    }
}


if (isset($_POST['delete_record_id'])) {
    try {
        $record_id = intval($_POST['delete_record_id']);

        
        $stmt = $pdo->prepare("SELECT id FROM doctb WHERE username = :username");
        $stmt->execute([':username' => $doctor]);
        $doctor_result = $stmt->fetch(PDO::FETCH_ASSOC);
        $current_doctor_id = $doctor_result['id'] ?? null;

        
        $stmt = $pdo->prepare("DELETE FROM medical_records WHERE id = :id AND doctor_id = :doctor_id");
        $stmt->execute([':id' => $record_id, ':doctor_id' => $current_doctor_id]);

        if ($stmt->rowCount() > 0) {
            redirectWithMessage($_SERVER['PHP_SELF'], 'success', 'Đã xóa bệnh án thành công!');
        } else {
            redirectWithMessage($_SERVER['PHP_SELF'], 'error', 'Không thể xóa bệnh án này. Vui lòng thử lại.');
        }
    } catch (PDOException $e) {
        error_log("Delete medical record error: " . $e->getMessage());
        redirectWithMessage($_SERVER['PHP_SELF'], 'error', 'Lỗi khi xóa bệnh án: ' . $e->getMessage());
    }
}


$doctor_id = null;
$doctor_fullname = null;
try {
    $stmt = $pdo->prepare("SELECT id, fullname FROM doctb WHERE username = :username");
    $stmt->execute([':username' => $doctor]);
    $doctor_result = $stmt->fetch(PDO::FETCH_ASSOC);
    $doctor_id = $doctor_result['id'] ?? null;
    $doctor_fullname = $doctor_result['fullname'] ?? $doctor;
} catch (PDOException $e) {
    error_log("Get doctor info error: " . $e->getMessage());
}


$doctor_patients = [];
if ($doctor_id && $doctor_fullname) {
    try {
        $stmt = $pdo->prepare("
            SELECT DISTINCT p.pid, p.fname, p.lname, p.contact, p.email
            FROM patreg p
            INNER JOIN appointmenttb a ON p.pid = a.pid
            WHERE TRIM(a.doctor) = TRIM(:doctor_name)
            ORDER BY p.fname, p.lname
        ");
        $stmt->execute([':doctor_name' => $doctor_fullname]);
        $doctor_patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Get doctor patients error: " . $e->getMessage());
    }
}


$all_medical_records = [];
$grouped_records = []; 
if ($doctor_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT mr.id, mr.patient_id, mr.doctor_id, mr.created_at, mr.symptoms, mr.diagnosis, mr.treatment_plan, mr.notes,
                   mr.height, mr.weight, mr.blood_pressure, mr.heart_rate, mr.temperature, mr.record_date,
                   p.fname, p.lname, p.contact, p.email, p.blood_group, p.pid,
                   d.fullname as doctor_name
            FROM medical_records mr
            JOIN patreg p ON mr.patient_id = p.pid
            LEFT JOIN doctb d ON mr.doctor_id = d.id
            WHERE mr.doctor_id = :doctor_id
            ORDER BY p.fname, p.lname, mr.created_at DESC
        ");
        $stmt->execute([':doctor_id' => $doctor_id]);
        $all_medical_records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        
        foreach ($all_medical_records as $record) {
            $patient_id = $record['patient_id'];
            if (!isset($grouped_records[$patient_id])) {
                $grouped_records[$patient_id] = [
                    'patient_info' => [
                        'pid' => $record['pid'],
                        'fname' => $record['fname'],
                        'lname' => $record['lname'],
                        'contact' => $record['contact'],
                        'email' => $record['email'],
                        'blood_group' => $record['blood_group']
                    ],
                    'records' => []
                ];
            }
            $grouped_records[$patient_id]['records'][] = $record;
        }
    } catch (PDOException $e) {
        error_log("Fetch all medical records error: " . $e->getMessage());
    }
}


$selected_patient_id = isset($_GET['patient_id']) ? intval($_GET['patient_id']) : null;
$patient_medical_records = [];
$selected_patient_info = null;

if ($selected_patient_id && $doctor_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM patreg WHERE pid = :pid");
        $stmt->execute([':pid' => $selected_patient_id]);
        $selected_patient_info = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("
            SELECT mr.*,
                   d.fullname as doctor_name,
                   a.appdate as appointmentDate, a.apptime as appointmentTime
            FROM medical_records mr
            LEFT JOIN doctb d ON mr.doctor_id = d.id
            LEFT JOIN appointmenttb a ON mr.appointment_id = a.ID
            WHERE mr.patient_id = :patient_id AND mr.doctor_id = :doctor_id
            ORDER BY mr.created_at DESC
        ");
        $stmt->execute([':patient_id' => $selected_patient_id, ':doctor_id' => $doctor_id]);
        $patient_medical_records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Fetch patient records error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="shortcut icon" type="image/x-icon" href="../../images/favicon.png" />
    <title>Quản lý Hồ sơ bệnh án - Bệnh viện Global</title>

    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/custom/medical-theme.css">
    <style>
        body {
            background-image:
                linear-gradient(135deg, rgba(254, 243, 199, 0.85) 0%, rgba(254, 215, 170, 0.85) 25%, rgba(253, 186, 116, 0.85) 50%, rgba(251, 146, 60, 0.85) 75%, rgba(249, 115, 22, 0.85) 100%),
                url('../../images/ngua.png');
            background-size: cover, contain;
            background-position: center, center;
            background-repeat: no-repeat, no-repeat;
            background-attachment: fixed, fixed;
            font-family: 'Inter', sans-serif;
        }

        .nav-tabs .nav-link {
            color: #6b7280;
            border: none;
            border-bottom: 3px solid transparent;
            font-weight: 600;
            padding: 12px 20px;
            transition: all 0.3s;
        }

        .nav-tabs .nav-link.active {
            color: #0369a1;
            background-color: transparent;
            border-bottom-color: #0369a1;
        }

        .nav-tabs .nav-link:hover {
            color: #0369a1;
        }

        .tab-content {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08), 0 0 0 1px rgba(0, 0, 0, 0.02);
        }

        .patient-card {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border: 2px solid #bae6fd;
            border-radius: 14px;
            padding: 12px;
            margin-bottom: 8px;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .patient-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(180deg, #0284c7, #0ea5e9);
            transition: width 0.3s ease;
        }

        .patient-card:hover {
            box-shadow: 0 12px 48px rgba(2, 132, 199, 0.25);
            transform: translateY(-4px) scale(1.01);
            border-color: #7dd3fc;
        }

        .patient-card:hover::before {
            width: 100%;
            opacity: 0.03;
        }

        .patient-card.active {
            background: linear-gradient(135deg, #dbeafe 0%, #bae6fd 100%);
            border-color: #0ea5e9;
            box-shadow: 0 8px 32px rgba(2, 132, 199, 0.2);
        }

        .patient-name {
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 5px;
        }

        .patient-meta {
            font-size: 11px;
            color: #6b7280;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .medical-record-card {
            background: linear-gradient(135deg, #dbeafe 0%, #bae6fd 100%);
            border: 2px solid #7dd3fc;
            border-radius: 14px;
            padding: 16px;
            margin-bottom: 16px;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .medical-record-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(180deg, #0284c7, #0ea5e9);
        }

        .form-section {
            background: white;
            border-radius: 14px;
            padding: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08), 0 0 0 1px rgba(0, 0, 0, 0.02);
            margin-bottom: 20px;
            border-left: 6px solid #0ea5e9;
        }

        .form-group-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .form-label {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 6px;
            font-size: 13px;
        }

        .form-control {
            border-radius: 8px;
            border: 2px solid #e5e7eb;
            padding: 8px 14px;
        }

        .form-control:focus {
            border-color: #0ea5e9;
            box-shadow: 0 0 0 0.2rem rgba(14, 165, 233, 0.25);
        }

        .textarea-control {
            min-height: 100px;
            font-family: 'Inter', sans-serif;
        }

        .btn-primary {
            background: linear-gradient(135deg, #0369a1 0%, #0284c7 50%, #0ea5e9 100%);
            border: none;
            font-weight: 700;
            padding: 8px 20px;
            border-radius: 8px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 16px rgba(3, 105, 161, 0.3);
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #075985 0%, #0369a1 50%, #0284c7 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(3, 105, 161, 0.4);
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #0c4a6e;
            background: white;
            padding: 10px 20px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 700;
            margin-bottom: 20px;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .back-link:hover {
            color: #0c4a6e;
            text-decoration: none;
            transform: translateX(-8px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .page-header {
            background: linear-gradient(135deg, #0369a1 0%, #0284c7 50%, #0ea5e9 100%);
            padding: 32px;
            border-radius: 20px;
            color: white;
            margin-bottom: 24px;
            box-shadow: 0 20px 60px rgba(3, 105, 161, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            animation: pulse 8s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
                opacity: 0.5;
            }

            50% {
                transform: scale(1.1);
                opacity: 0.8;
            }
        }

        .page-header h1 {
            margin: 0;
            font-size: 26px;
            font-weight: 800;
            position: relative;
            z-index: 1;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .page-header p {
            position: relative;
            z-index: 1;
            opacity: 0.95;
        }

        .vitals-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 15px;
        }

        .vital-input-card {
            background: linear-gradient(135deg, #ecfeff 0%, #cffafe 100%);
            padding: 12px;
            border-radius: 10px;
            border: 2px solid #a5f3fc;
            transition: all 0.3s;
        }

        .vital-input-card:hover {
            border-color: #67e8f9;
            box-shadow: 0 4px 12px rgba(6, 182, 212, 0.15);
        }

        .vital-input-card .form-label {
            color: #0e7490;
            font-size: 11px;
            font-weight: 600;
        }

        .patient-list-container {
            max-height: 600px;
            overflow-y: auto;
        }

        @media (max-width: 768px) {
            .form-group-row {
                grid-template-columns: 1fr;
            }

            .vitals-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .tab-content {
                padding: 15px;
            }
        }

        .petals-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            pointer-events: none;
            z-index: 9999;
        }

        .petal {
            position: absolute;
            top: -10px;
            width: 15px;
            height: 15px;
            background: radial-gradient(ellipse at center, #ffb7d5 0%, #ff69b4 40%, #ff1493 100%);
            border-radius: 50% 0 50% 0;
            opacity: 0.8;
            animation: fall linear infinite;
        }

        .petal::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background: radial-gradient(ellipse at center, rgba(255, 255, 255, 0.5) 0%, transparent 50%);
            border-radius: 50% 0 50% 0;
            transform: rotate(90deg);
        }

        @keyframes fall {
            0% {
                transform: translateY(0) rotateZ(0deg) rotateY(0deg);
                opacity: 0.8;
            }

            50% {
                transform: translateY(50vh) rotateZ(180deg) rotateY(180deg);
                opacity: 0.6;
            }

            100% {
                transform: translateY(100vh) rotateZ(360deg) rotateY(360deg);
                opacity: 0;
            }
        }

        .petal:nth-child(odd) {
            animation-duration: 8s;
        }

        .petal:nth-child(even) {
            animation-duration: 12s;
        }

        .petal:nth-child(3n) {
            animation-duration: 10s;
            width: 12px;
            height: 12px;
        }

        .petal:nth-child(5n) {
            animation-duration: 15s;
            width: 18px;
            height: 18px;
            opacity: 0.6;
        }
    </style>
</head>

<body>
    <div class="petals-container" id="petals"></div>
    <script>
        function createPetals() {
            const c = document.getElementById('petals');
            for (let i = 0; i < 25; i++) {
                const p = document.createElement('div');
                p.className = 'petal';
                p.style.left = Math.random() * 100 + '%';
                p.style.animationDelay = Math.random() * 10 + 's';
                p.style.animationDuration = (8 + Math.random() * 10) + 's';
                c.appendChild(p);
            }
        }
        window.addEventListener('load', createPetals);
    </script>
    <?php displayMessage(); ?>

    <div class="container-lg py-4">
        <a href="dashboard.php" class="back-link">
            <i class="fas fa-arrow-left"></i>
            Quay lại bảng điều khiển
        </a>

        
        <div class="page-header">
            <h1><i class="fas fa-file-medical"></i> Quản lý Hồ sơ bệnh án</h1>
            <p>Xem, thêm và quản lý hồ sơ bệnh án của bệnh nhân</p>
        </div>

        
        <ul class="nav nav-tabs" id="medicalTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="view-tab" data-toggle="tab" href="#view-content" role="tab">
                    <i class="fas fa-list"></i> Xem bệnh án
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="add-tab" data-toggle="tab" href="#add-content" role="tab">
                    <i class="fas fa-plus"></i> Thêm bệnh án
                </a>
            </li>
        </ul>

        
        <div class="tab-content" id="medicalTabsContent">
            
            <div class="tab-pane fade show active" id="view-content" role="tabpanel">
                <div>
                    <h5 class="mb-4" style="color: #1f2937; font-weight: 700;">
                        <i class="fas fa-list"></i> Danh sách bệnh án
                    </h5>

                    <?php if (empty($grouped_records)) { ?>
                        <div style="text-align: center; padding: 60px 20px; background: white; border-radius: 8px;">
                            <i class="fas fa-inbox" style="font-size: 48px; color: #d1d5db; margin-bottom: 15px;"></i>
                            <h5 style="color: #6b7280;">Không có bệnh án</h5>
                            <p style="color: #9ca3af;">Bạn chưa thêm bệnh án nào. Hãy vào tab "Thêm bệnh án" để tạo mới.</p>
                        </div>
                    <?php } else { ?>
                        <div style="display: grid; gap: 12px;">
                            <?php foreach ($grouped_records as $patient_id => $group) { ?>
                                <div class="patient-group" data-patient-id="<?php echo $patient_id; ?>" style="background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: all 0.3s ease;">
                                    
                                    <div class="patient-header" style="padding: 18px 20px; background: linear-gradient(135deg, #48bb78 0%, #38a169 100%); color: white; cursor: pointer; display: flex; justify-content: space-between; align-items: center; user-select: none;">
                                        <div style="flex: 1;">
                                            <h6 style="margin: 0 0 8px 0; font-weight: 700; font-size: 16px;">
                                                <i class="fas fa-user-circle" style="margin-right: 8px;"></i><?php echo $group['patient_info']['fname'] . ' ' . $group['patient_info']['lname']; ?>
                                            </h6>
                                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 12px; font-size: 12px; opacity: 0.9;">
                                                <div><i class="fas fa-phone" style="margin-right: 4px;"></i><?php echo $group['patient_info']['contact']; ?></div>
                                                <div><i class="fas fa-envelope" style="margin-right: 4px;"></i><?php echo $group['patient_info']['email']; ?></div>
                                                <div><i class="fas fa-file-medical" style="margin-right: 4px;"></i><?php echo count($group['records']); ?> bệnh án</div>
                                            </div>
                                        </div>
                                        <div style="text-align: right;">
                                            <div style="font-size: 24px; transition: transform 0.3s ease;" class="expand-icon">
                                                <i class="fas fa-chevron-down"></i>
                                            </div>
                                        </div>
                                    </div>

                                    
                                    <div class="patient-records" style="max-height: 0; overflow: hidden; transition: max-height 0.3s ease; background: #f8fafc;">
                                        <div style="padding: 0;">
                                            <?php foreach ($group['records'] as $index => $record) { ?>
                                                <div style="padding: 15px 20px; border-top: 1px solid #e5e7eb; background: white; margin: 0;">
                                                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px;">
                                                        <div>
                                                            <div style="font-weight: 600; color: #1f2937; margin-bottom: 4px;">Bệnh án #<?php echo count($group['records']) - $index; ?></div>
                                                            <div style="font-size: 12px; color: #6b7280;">
                                                                <i class="fas fa-calendar"></i> <?php echo date('d/m/Y H:i', strtotime($record['created_at'])); ?>
                                                            </div>
                                                        </div>
                                                        <div style="display: flex; gap: 6px;">
                                                            <button type="button" class="btn btn-sm btn-info view-record" data-record-id="<?php echo $record['id']; ?>" style="padding: 4px 10px; font-size: 11px;">
                                                                <i class="fas fa-eye"></i> Xem
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-warning edit-record" data-record-id="<?php echo $record['id']; ?>" style="padding: 4px 10px; font-size: 11px;">
                                                                <i class="fas fa-edit"></i> Sửa
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-danger delete-record" data-record-id="<?php echo $record['id']; ?>" style="padding: 4px 10px; font-size: 11px;">
                                                                <i class="fas fa-trash"></i> Xóa
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <?php if ($record['symptoms']) { ?>
                                                        <div style="margin-bottom: 8px;">
                                                            <span style="font-weight: 600; color: #6b7280; font-size: 12px;">Triệu chứng:</span>
                                                            <div style="color: #374151; font-size: 13px; margin-top: 2px;"><?php echo substr($record['symptoms'], 0, 120); ?><?php echo strlen($record['symptoms']) > 120 ? '...' : ''; ?></div>
                                                        </div>
                                                    <?php } ?>

                                                    <?php if ($record['diagnosis']) { ?>
                                                        <div style="margin-bottom: 8px;">
                                                            <span style="font-weight: 600; color: #6b7280; font-size: 12px;">Chẩn đoán:</span>
                                                            <div style="color: #374151; font-size: 13px; margin-top: 2px;"><?php echo substr($record['diagnosis'], 0, 120); ?><?php echo strlen($record['diagnosis']) > 120 ? '...' : ''; ?></div>
                                                        </div>
                                                    <?php } ?>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
            </div>

            
            <div class="tab-pane fade" id="add-content" role="tabpanel">
                <h5 style="color: #1f2937; font-weight: 700; margin-bottom: 25px;">
                    <i class="fas fa-plus-circle"></i> Thêm hồ sơ bệnh án mới
                </h5>

                <form method="POST" action="">
                    <div class="form-section">
                        <h6 style="color: #1f2937; font-weight: 700; margin-bottom: 20px;">
                            <i class="fas fa-user"></i> Chọn bệnh nhân
                        </h6>

                        <div class="form-group">
                            <label class="form-label">Bác sĩ <span style="color: #ef4444;">*</span></label>
                            <select id="doctor_select" class="form-control" required>
                                <option value="">-- Chọn bác sĩ --</option>
                                <?php
                                try {
                                    $doctors = $pdo->query("SELECT MIN(id) as id, fullname FROM doctb GROUP BY fullname ORDER BY fullname")->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($doctors as $doc) {
                                        $selected = ($doc['fullname'] === $doctor) ? 'selected' : '';
                                        echo "<option value='" . $doc['id'] . "' " . $selected . ">" . $doc['fullname'] . "</option>";
                                    }
                                } catch (PDOException $e) {
                                    error_log("Get doctors error: " . $e->getMessage());
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Bệnh nhân <span style="color: #ef4444;">*</span></label>
                            <select name="patient_id" id="patient_select" class="form-control" required>
                                <option value="">-- Chọn bệnh nhân --</option>
                                <?php foreach ($doctor_patients as $patient) { ?>
                                    <option value="<?php echo $patient['pid']; ?>">
                                        <?php echo $patient['fname'] . ' ' . $patient['lname'] . ' - ' . $patient['contact']; ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Lịch hẹn (tùy chọn)</label>
                            <select name="appointment_id" id="appointment_select" class="form-control">
                                <option value="">-- Chọn bệnh nhân trước --</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-section">
                        <h6 style="color: #1f2937; font-weight: 700; margin-bottom: 20px;">
                            <i class="fas fa-stethoscope"></i> Triệu chứng và Chẩn đoán
                        </h6>

                        <div class="form-group">
                            <label class="form-label">Triệu chứng <span style="color: #ef4444;">*</span></label>
                            <textarea name="symptoms" class="form-control textarea-control" placeholder="Mô tả triệu chứng của bệnh nhân..." required></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Chẩn đoán <span style="color: #ef4444;">*</span></label>
                            <textarea name="diagnosis" class="form-control textarea-control" placeholder="Kết quả chẩn đoán..." required></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Điều trị <span style="color: #ef4444;">*</span></label>
                            <textarea name="treatment" class="form-control textarea-control" placeholder="Kế hoạch điều trị..." required></textarea>
                        </div>
                    </div>

                    <div class="form-section">
                        <h6 style="color: #1f2937; font-weight: 700; margin-bottom: 20px;">
                            <i class="fas fa-heartbeat"></i> Chỉ số sức khỏe
                        </h6>

                        <div class="vitals-grid">
                            <div class="vital-input-card">
                                <label class="form-label">Chiều cao (cm)</label>
                                <input type="number" name="height" class="form-control" placeholder="cm" step="0.1">
                            </div>

                            <div class="vital-input-card">
                                <label class="form-label">Cân nặng (kg)</label>
                                <input type="number" name="weight" class="form-control" placeholder="kg" step="0.1">
                            </div>

                            <div class="vital-input-card">
                                <label class="form-label">Huyết áp</label>
                                <input type="text" name="blood_pressure" class="form-control" placeholder="e.g., 120/80">
                            </div>

                            <div class="vital-input-card">
                                <label class="form-label">Nhịp tim (bpm)</label>
                                <input type="number" name="heart_rate" class="form-control" placeholder="bpm" step="1">
                            </div>

                            <div class="vital-input-card">
                                <label class="form-label">Nhiệt độ (°C)</label>
                                <input type="number" name="temperature" class="form-control" placeholder="°C" step="0.1">
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h6 style="color: #1f2937; font-weight: 700; margin-bottom: 20px;">
                            <i class="fas fa-sticky-note"></i> Ghi chú bổ sung
                        </h6>

                        <div class="form-group">
                            <label class="form-label">Ghi chú</label>
                            <textarea name="notes" class="form-control textarea-control" placeholder="Ghi chú thêm về bệnh nhân..."></textarea>
                        </div>
                    </div>

                    <div class="form-section">
                        <button type="submit" name="add_medical_record" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> Lưu hồ sơ bệnh án
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    
    <div class="modal fade" id="viewDetailModal" tabindex="-1" role="dialog" aria-labelledby="viewDetailLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #d2302c 0%, #8b0000 100%); color: white;">
                    <h5 class="modal-title" id="viewDetailLabel"><i class="fas fa-file-medical"></i> Chi tiết hồ sơ bệnh án</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="viewDetailContent">
                    <p>Đang tải...</p>
                </div>
            </div>
        </div>
    </div>

    
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white;">
                    <h5 class="modal-title" id="editLabel"><i class="fas fa-edit"></i> Chỉnh sửa hồ sơ bệnh án</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="editForm">
                    <input type="hidden" id="editRecordId" name="record_id">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Bác sĩ <span style="color: red;">*</span> (không thể chỉnh sửa)</label>
                            <input type="text" class="form-control" id="editDoctorName" disabled>
                        </div>

                        <div class="form-group">
                            <label>Triệu chứng</label>
                            <textarea class="form-control" name="symptoms" id="editSymptoms" rows="3"></textarea>
                        </div>

                        <div class="form-group">
                            <label>Chẩn đoán</label>
                            <textarea class="form-control" name="diagnosis" id="editDiagnosis" rows="3"></textarea>
                        </div>

                        <div class="form-group">
                            <label>Điều trị</label>
                            <textarea class="form-control" name="treatment_plan" id="editTreatment" rows="3"></textarea>
                        </div>

                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 15px;">
                            <div class="form-group">
                                <label>Chiều cao (cm)</label>
                                <input type="number" class="form-control" name="height" id="editHeight" step="0.1">
                            </div>
                            <div class="form-group">
                                <label>Cân nặng (kg)</label>
                                <input type="number" class="form-control" name="weight" id="editWeight" step="0.1">
                            </div>
                            <div class="form-group">
                                <label>Huyết áp</label>
                                <input type="text" class="form-control" name="blood_pressure" id="editBloodPressure" placeholder="e.g., 120/80">
                            </div>
                            <div class="form-group">
                                <label>Nhịp tim (bpm)</label>
                                <input type="number" class="form-control" name="heart_rate" id="editHeartRate" step="1">
                            </div>
                            <div class="form-group">
                                <label>Nhiệt độ (°C)</label>
                                <input type="number" class="form-control" name="temperature" id="editTemperature" step="0.1">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Ghi chú</label>
                            <textarea class="form-control" name="notes" id="editNotes" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu thay đổi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Initialize doctor select on page load
        document.addEventListener('DOMContentLoaded', function() {
            const doctorSelect = document.getElementById('doctor_select');
            const patientSelect = document.getElementById('patient_select');
            const appointmentSelect = document.getElementById('appointment_select');

            // Khi chọn bác sĩ → load bệnh nhân đã đặt lịch của bác sĩ đó
            if (doctorSelect) {
                doctorSelect.addEventListener('change', function() {
                    const doctorId = this.value;

                    // Reset patient và appointment
                    patientSelect.innerHTML = '<option value="">-- Đang tải... --</option>';
                    appointmentSelect.innerHTML = '<option value="">-- Chọn bệnh nhân trước --</option>';

                    if (doctorId) {
                        // Load bệnh nhân của bác sĩ này
                        $.ajax({
                            url: 'get_doctor_patients.php',
                            method: 'GET',
                            data: {
                                doctor_id: doctorId
                            },
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    patientSelect.innerHTML = '<option value="">-- Chọn bệnh nhân --</option>';
                                    response.patients.forEach(function(patient) {
                                        const option = document.createElement('option');
                                        option.value = patient.pid;
                                        option.textContent = patient.fname + ' ' + patient.lname + ' - ' + patient.contact;
                                        patientSelect.appendChild(option);
                                    });
                                } else {
                                    patientSelect.innerHTML = '<option value="">Không có bệnh nhân</option>';
                                }
                            },
                            error: function() {
                                patientSelect.innerHTML = '<option value="">Lỗi khi tải dữ liệu</option>';
                            }
                        });
                    } else {
                        patientSelect.innerHTML = '<option value="">-- Chọn bác sĩ trước --</option>';
                    }
                });
            }

            // Khi chọn bệnh nhân → load lịch hẹn của bệnh nhân với bác sĩ đó
            if (patientSelect) {
                patientSelect.addEventListener('change', function() {
                    const patientId = this.value;
                    const doctorId = doctorSelect.value;

                    appointmentSelect.innerHTML = '<option value="">-- Đang tải... --</option>';

                    if (patientId && doctorId) {
                        // Load lịch hẹn của bệnh nhân này với bác sĩ đã chọn
                        $.ajax({
                            url: 'get_patient_appointments.php',
                            method: 'GET',
                            data: {
                                patient_id: patientId,
                                doctor_id: doctorId
                            },
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    appointmentSelect.innerHTML = '<option value="">-- Không bắt buộc --</option>';
                                    response.appointments.forEach(function(apt) {
                                        const option = document.createElement('option');
                                        option.value = apt.ID;
                                        option.textContent = 'Lịch #' + apt.ID + ' - ' + apt.appdate + ' ' + apt.apptime;
                                        appointmentSelect.appendChild(option);
                                    });
                                } else {
                                    appointmentSelect.innerHTML = '<option value="">Không có lịch hẹn</option>';
                                }
                            },
                            error: function() {
                                appointmentSelect.innerHTML = '<option value="">Lỗi khi tải dữ liệu</option>';
                            }
                        });
                    } else {
                        appointmentSelect.innerHTML = '<option value="">-- Chọn bệnh nhân trước --</option>';
                    }
                });
            }

            // Trigger change nếu đã có giá trị mặc định
            if (doctorSelect && doctorSelect.value) {
                doctorSelect.dispatchEvent(new Event('change'));
            }

            // Handle patient group expand/collapse
            document.querySelectorAll('.patient-header').forEach(header => {
                header.addEventListener('click', function() {
                    const group = this.closest('.patient-group');
                    const recordsDiv = group.querySelector('.patient-records');
                    const icon = group.querySelector('.expand-icon i');

                    const isExpanded = recordsDiv.style.maxHeight !== '0px' && recordsDiv.style.maxHeight !== '';

                    if (isExpanded) {
                        // Collapse
                        recordsDiv.style.maxHeight = '0';
                        icon.style.transform = 'rotate(0deg)';
                    } else {
                        // Expand
                        recordsDiv.style.maxHeight = recordsDiv.scrollHeight + 'px';
                        icon.style.transform = 'rotate(180deg)';
                    }
                });
            });
        });

        // Handle View Record Button
        document.querySelectorAll('.view-record').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation(); // Prevent triggering parent collapse
                const recordId = this.getAttribute('data-record-id');

                // Show modal
                $('#viewDetailModal').modal('show');
                $('#viewDetailContent').html('<div class="text-center p-4"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2">Đang tải dữ liệu...</p></div>');

                // Load record data
                $.ajax({
                    url: 'get_record_detail.php',
                    method: 'GET',
                    data: {
                        id: recordId
                    },
                    success: function(response) {
                        $('#viewDetailContent').html(response);
                    },
                    error: function() {
                        $('#viewDetailContent').html('<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> Lỗi khi tải dữ liệu. Vui lòng thử lại.</div>');
                    }
                });
            });
        });

        // Handle Edit Record Button  
        document.querySelectorAll('.edit-record').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation(); // Prevent triggering parent collapse
                const recordId = this.getAttribute('data-record-id');

                // Show edit modal
                $('#editModal').modal('show');
                $('#editRecordId').val(recordId);

                // Load record data
                $.ajax({
                    url: 'get_record_detail.php',
                    method: 'GET',
                    data: {
                        id: recordId,
                        mode: 'edit'
                    },
                    dataType: 'json',
                    success: function(data) {
                        if (data.success) {
                            const record = data.record;
                            $('#editDoctorName').val(record.doctor_name);
                            $('#editSymptoms').val(record.symptoms);
                            $('#editDiagnosis').val(record.diagnosis);
                            $('#editTreatment').val(record.treatment_plan);
                            $('#editHeight').val(record.height);
                            $('#editWeight').val(record.weight);
                            $('#editBloodPressure').val(record.blood_pressure);
                            $('#editHeartRate').val(record.heart_rate);
                            $('#editTemperature').val(record.temperature);
                            $('#editNotes').val(record.notes);
                        } else {
                            alert('Lỗi khi tải dữ liệu: ' + (data.message || 'Unknown error'));
                            $('#editModal').modal('hide');
                        }
                    },
                    error: function() {
                        alert('Lỗi kết nối. Vui lòng thử lại.');
                        $('#editModal').modal('hide');
                    }
                });
            });
        });

        // Handle Delete Record Button
        document.querySelectorAll('.delete-record').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation(); // Prevent triggering parent collapse
                const recordId = this.getAttribute('data-record-id');

                if (confirm('Bạn có chắc chắn muốn xóa bệnh án này không? Hành động này không thể hoàn tác.')) {
                    // Create form and submit
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '';

                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'delete_record_id';
                    input.value = recordId;

                    form.appendChild(input);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });

        // Handle Edit Form Submit
        $('#editForm').on('submit', function(e) {
            e.preventDefault();

            const formData = $(this).serialize();

            $.ajax({
                url: 'update_record.php',
                method: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert('Cập nhật bệnh án thành công!');
                        location.reload();
                    } else {
                        alert('Lỗi: ' + (response.message || 'Unknown error'));
                    }
                },
                error: function() {
                    alert('Lỗi kết nối. Vui lòng thử lại.');
                }
            });
        });
    </script>

    
    <script type="text/javascript">
        (function() {
            const isMobile = window.matchMedia('(max-width: 576px)').matches;
            const isTablet = window.matchMedia('(min-width: 577px) and (max-width: 992px)').matches;
            const petalCount = isMobile ? 15 : (isTablet ? 30 : 50);
            const petalImage = 'https://blogger.googleusercontent.com/img/b/R29vZ2xl/AVvXsEizrrtX-KQtKY8e8pxCHjLROT5pYW7sVkUpET9HHpW8QO-PnoIRKVsvRDxM6shrE4Q-44Oh9teSGK1SApaZ1OJvhR4z7ENgKSJOLWfsdKw9jPszAa2HqaE6W8ohyGHRvff6TgKXEUjnn73LLLp3FHbtMTJnIkPxPhujWwG5ZsFgW7ctQ0zrR5KKSqlewg/s16000/hoadao-anonyviet.com.png';

            const petals = [];
            let docWidth = window.innerWidth;
            let docHeight = window.innerHeight;

            for (let i = 0; i < petalCount; i++) {
                const size = 10 + Math.random() * 14;
                const opacity = 0.5 + Math.random() * 0.4;
                const rotation = Math.random() * 360;
                const blur = Math.random() * 0.5;

                const petal = {
                    x: Math.random() * docWidth,
                    y: Math.random() * docHeight - docHeight,
                    dx: 0,
                    rotation: rotation,
                    rotationSpeed: (Math.random() - 0.5) * 1.5,
                    amplitude: 20 + Math.random() * 35,
                    speedX: 0.01 + Math.random() / 15,
                    speedY: 0.3 + Math.random() * 0.6,
                    size: size,
                    opacity: opacity,
                    blur: blur,
                    element: null
                };

                const div = document.createElement('div');
                div.id = 'cherry-petal-' + i;
                div.style.cssText = `position:fixed;z-index:9998;visibility:visible;pointer-events:none;width:${size}px;left:${petal.x}px;top:${petal.y}px;opacity:${opacity};transition:transform 0.15s ease-out;will-change:transform,top,left`;
                div.innerHTML = `<img src="${petalImage}" alt="Hoa đào" style="width:100%;height:auto;transform:rotate(${rotation}deg);filter:drop-shadow(2px 2px 4px rgba(255,105,180,0.4)) blur(${blur}px) brightness(1.1);">`;
                document.body.appendChild(div);
                petal.element = div;
                petals.push(petal);
            }

            function animate() {
                docWidth = window.innerWidth;
                docHeight = window.innerHeight;

                petals.forEach(petal => {
                    petal.y += petal.speedY;
                    petal.rotation += petal.rotationSpeed;

                    if (petal.y > docHeight + 80) {
                        petal.x = Math.random() * docWidth;
                        petal.y = -80;
                        petal.speedX = 0.01 + Math.random() / 15;
                        petal.speedY = 0.3 + Math.random() * 0.6;
                        petal.rotationSpeed = (Math.random() - 0.5) * 1.5;
                    }

                    petal.dx += petal.speedX;
                    const swayX = petal.x + petal.amplitude * Math.sin(petal.dx);
                    const scaleEffect = 0.95 + Math.sin(petal.dx * 2) * 0.05;

                    petal.element.style.top = petal.y + 'px';
                    petal.element.style.left = swayX + 'px';
                    petal.element.querySelector('img').style.transform = `rotate(${petal.rotation}deg) scale(${scaleEffect})`;
                });

                requestAnimationFrame(animate);
            }

            animate();

            let resizeTimer;
            window.addEventListener('resize', () => {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(() => {
                    docWidth = window.innerWidth;
                    docHeight = window.innerHeight;
                }, 250);
            });
        })();
    </script>
</body>

</html>