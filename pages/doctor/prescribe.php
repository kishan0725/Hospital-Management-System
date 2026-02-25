<?php
ob_start();
session_start();

set_exception_handler(function ($e) {
    error_log("Doctor prescribe uncaught: " . $e->getMessage());
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
    header("Location: ../auth/login.php");
    exit();
}


$doctor_username = $_SESSION['dname'];
$doctor_id = null;
$doctor_info = null;

$stmt = $pdo->prepare("SELECT id, fullname, spec FROM doctb WHERE username = ?");
$stmt->execute([$doctor_username]);
$doctor_info = $stmt->fetch(PDO::FETCH_ASSOC);

if ($doctor_info) {
    $doctor_id = $doctor_info['id'];
} else {
    error_log("Warning: Doctor with username '$doctor_username' not found in doctb.");
}


$pid = '';
$ID = '';
$appdate = '';
$apptime = '';
$fname = '';
$lname = '';


if (isset($_GET['pid']) && isset($_GET['ID'])) {
    $pid = $_GET['pid'];
    $ID = $_GET['ID'];
    $fname = $_GET['fname'] ?? '';
    $lname = $_GET['lname'] ?? '';
    $appdate = $_GET['appdate'] ?? '';
    $apptime = $_GET['apptime'] ?? '';
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['prescribe'])) {
    try {
        $pdo->beginTransaction();

        
        $pid = $_POST['pid'];
        $ID = $_POST['ID'] ?? null;
        $disease = $_POST['disease'];
        $allergy = $_POST['allergy'] ?? '';
        $treatment_duration = $_POST['treatment_duration'];
        $general_notes = $_POST['general_notes'] ?? '';

        $fname = $_POST['fname'] ?? '';
        $lname = $_POST['lname'] ?? '';
        $appdate = $_POST['appdate'] ?? date('Y-m-d');
        $apptime = $_POST['apptime'] ?? date('H:i:s');

        
        $med_summary = "";
        if (isset($_POST['medications']) && is_array($_POST['medications'])) {
            foreach ($_POST['medications'] as $med) {
                if (!empty($med['name'])) {
                    $med_summary .= $med['name'] . " (" . $med['dosage'] . ") - " . $med['frequency'] . "; ";
                }
            }
        }

        if (empty($med_summary)) {
            $med_summary = "Chi tiết trong bảng thuốc";
        }

        $stmt = $pdo->prepare("
            INSERT INTO prestb (doctor, pid, ID, fname, lname, appdate, apptime, disease, allergy, prescription, treatment_duration, general_notes, created_at)
            VALUES (:doctor, :pid, :ID, :fname, :lname, :appdate, :apptime, :disease, :allergy, :prescription, :treatment_duration, :general_notes, NOW())
        ");

        $result = $stmt->execute([
            ':doctor' => $doctor_info['fullname'] ?? $doctor,
            ':pid' => $pid,
            ':ID' => $ID,
            ':fname' => $fname,
            ':lname' => $lname,
            ':appdate' => $appdate,
            ':apptime' => $apptime,
            ':disease' => $disease,
            ':allergy' => $allergy,
            ':prescription' => $med_summary,
            ':treatment_duration' => $treatment_duration,
            ':general_notes' => $general_notes
        ]);

        if (!$result) {
            $errorInfo = $stmt->errorInfo();
            throw new Exception("Failed to insert prescription: " . $errorInfo[2]);
        }

        $prescription_id = $pdo->lastInsertId();

        
        if (isset($_POST['medications']) && is_array($_POST['medications'])) {
            $stmt = $pdo->prepare("
                INSERT INTO prescription_medications (prescription_id, medication_name, dosage, frequency, duration, special_notes)
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            foreach ($_POST['medications'] as $med) {
                if (!empty($med['name'])) {
                    $stmt->execute([
                        $prescription_id,
                        $med['name'],
                        $med['dosage'],
                        $med['frequency'],
                        $med['duration'],
                        $med['notes'] ?? ''
                    ]);
                }
            }
        }

        $pdo->commit();
        redirectWithMessage('prescriptions.php', 'success', 'Kê đơn thuốc thành công!');
    } catch (Exception $e) {
        $pdo->rollBack();
        $error_message = "Lỗi khi tạo đơn thuốc: " . $e->getMessage();
        error_log($error_message);
    }
}
?>

<!DOCTYPE html>
="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="shortcut icon" type="image/x-icon" href="../../images/favicon.png" />
    <title>Kê đơn thuốc - Bệnh viện Global</title>
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

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #d2302c;
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 20px;
            transition: all 0.3s;
        }

        .back-link:hover {
            color: #8b0000;
            text-decoration: none;
            transform: translateX(-5px);
        }

        .page-header {
            background: linear-gradient(135deg, #d2302c 0%, #ff4d4d 100%);
            color: white;
            padding: 24px;
            border-radius: 14px;
            margin-bottom: 24px;
            box-shadow: 0 8px 24px rgba(210, 48, 44, 0.15);
        }

        .page-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }

        .page-header p {
            margin: 6px 0 0 0;
            opacity: 0.95;
            font-size: 13px;
        }

        .patient-info-card {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            padding: 20px;
            border-radius: 14px;
            margin-bottom: 24px;
            box-shadow: 0 4px 12px rgba(251, 191, 36, 0.1);
        }

        .patient-info-card h4 {
            color: #78350f;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .info-item {
            margin-bottom: 10px;
        }

        .info-item small {
            display: block;
            color: #92400e;
            font-weight: 500;
            font-size: 11px;
            margin-bottom: 3px;
        }

        .info-item strong {
            color: #78350f;
            font-size: 13px;
        }

        .form-card {
            background: white;
            border-radius: 14px;
            padding: 24px;
            margin-bottom: 20px;
            box-shadow: 0 4px 12px rgba(8, 145, 178, 0.12);
            border-left: 5px solid #d2302c;
        }

        .section-title {
            color: #d2302c;
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #ffe6e6;
        }

        .form-label {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 6px;
            font-size: 13px;
        }

        .required-field::after {
            content: " *";
            color: #f43f5e;
        }

        .form-control {
            border-radius: 8px;
            border: 1px solid #d1d5db;
            padding: 8px 10px;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: #d2302c;
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
        }

        .medication-item {
            background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 16px;
            border: 2px solid #ddd6fe;
            position: relative;
        }

        .medication-number {
            position: absolute;
            top: -10px;
            left: 18px;
            background: linear-gradient(135deg, #d2302c, #ff4d4d);
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            box-shadow: 0 4px 8px rgba(210, 48, 44, 0.3);
        }

        .btn-remove-medication {
            position: absolute;
            top: 15px;
            right: 15px;
            background: #f43f5e;
            color: white;
            border: none;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-remove-medication:hover {
            background: #e11d48;
            transform: rotate(90deg);
        }

        .btn-medical {
            background: linear-gradient(135deg, #d2302c, #ff4d4d);
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-medical:hover {
            background: linear-gradient(135deg, #8b0000, #d2302c);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(210, 48, 44, 0.3);
        }

        .btn-add-med {
            background: linear-gradient(135deg, #ffd700, #d4af37);
            color: #8b0000;
            border: none;
            padding: 8px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-add-med:hover {
            background: linear-gradient(135deg, #d4af37, #b8860b);
            color: #8b0000;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .patient-info-card .row>div {
                margin-bottom: 15px;
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
                transform: translateY(0) rotateZ(0deg);
                opacity: 0.8;
            }

            100% {
                transform: translateY(100vh) rotateZ(360deg);
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
        }

        .petal:nth-child(5n) {
            animation-duration: 15s;
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
            <h1><i class="fas fa-file-prescription"></i> Kê Đơn Thuốc Chi Tiết</h1>
            <p>Tạo đơn thuốc và hướng dẫn điều trị cho bệnh nhân</p>
        </div>

        <?php if ($pid): ?>
            <div class="patient-info-card">
                <h4><i class="fas fa-user-injured mr-2"></i>Thông tin bệnh nhân</h4>
                <div class="row">
                    <div class="col-md-3">
                        <div class="info-item">
                            <small>Tên bệnh nhân</small>
                            <strong><?php echo htmlspecialchars($fname . ' ' . $lname); ?></strong>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-item">
                            <small>Mã hồ sơ</small>
                            <strong>#<?php echo htmlspecialchars($pid); ?></strong>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-item">
                            <small>Ngày hẹn</small>
                            <strong><?php echo $appdate ? date('d/m/Y', strtotime($appdate)) : 'N/A'; ?></strong>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-item">
                            <small>Giờ hẹn</small>
                            <strong><?php echo htmlspecialchars($apptime); ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle mr-2"></i><?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="prescriptionForm">
            <input type="hidden" name="pid" value="<?php echo htmlspecialchars($pid); ?>">
            <input type="hidden" name="ID" value="<?php echo htmlspecialchars($ID); ?>">
            <input type="hidden" name="fname" value="<?php echo htmlspecialchars($fname); ?>">
            <input type="hidden" name="lname" value="<?php echo htmlspecialchars($lname); ?>">
            <input type="hidden" name="appdate" value="<?php echo htmlspecialchars($appdate); ?>">
            <input type="hidden" name="apptime" value="<?php echo htmlspecialchars($apptime); ?>">

            
            <div class="form-card">
                <h5 class="section-title"><i class="fas fa-stethoscope mr-2"></i>Chẩn đoán & Điều trị</h5>
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label class="form-label required-field">Chẩn đoán / Bệnh</label>
                            <input type="text" name="disease" class="form-control" placeholder="Nhập tên bệnh hoặc chẩn đoán" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label required-field">Thời gian điều trị</label>
                            <input type="text" name="treatment_duration" class="form-control" placeholder="VD: 7 ngày" required>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Dị ứng (nếu có)</label>
                    <input type="text" name="allergy" class="form-control" placeholder="Nhập tên thuốc/thực phẩm gây dị ứng">
                </div>
            </div>

            
            <div class="form-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="section-title mb-0"><i class="fas fa-pills mr-2"></i>Danh sách thuốc</h5>
                    <button type="button" class="btn btn-add-med" onclick="addMedication()">
                        <i class="fas fa-plus mr-1"></i> Thêm thuốc
                    </button>
                </div>

                <div id="medications-container">
                    
                </div>
            </div>

            
            <div class="form-card">
                <h5 class="section-title"><i class="fas fa-comment-medical mr-2"></i>Hướng dẫn chung</h5>
                <div class="form-group">
                    <label class="form-label">Lời dặn dò của bác sĩ</label>
                    <textarea name="general_notes" class="form-control" rows="4" placeholder="Nhập hướng dẫn chung cho bệnh nhân về chế độ ăn uống, nghỉ ngơi, tái khám..."></textarea>
                </div>
            </div>

            <div class="text-center">
                <button type="submit" name="prescribe" class="btn btn-medical btn-lg px-5">
                    <i class="fas fa-save mr-2"></i> Lưu Đơn Thuốc
                </button>
            </div>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script>
        let medicationCount = 0;

        function addMedication() {
            medicationCount++;
            const container = document.getElementById('medications-container');
            const html = `
                <div class="medication-item" id="medication-${medicationCount}">
                    <div class="medication-number">${medicationCount}</div>
                    <button type="button" class="btn-remove-medication" onclick="removeMedication(${medicationCount})">
                        <i class="fas fa-times"></i>
                    </button>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label required-field">Tên thuốc</label>
                                <input type="text" name="medications[${medicationCount}][name]" class="form-control" placeholder="Nhập tên thuốc" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label required-field">Liều lượng</label>
                                <input type="text" name="medications[${medicationCount}][dosage]" class="form-control" placeholder="VD: 500mg, 1 viên" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label required-field">Tần suất</label>
                                <input type="text" name="medications[${medicationCount}][frequency]" class="form-control" placeholder="VD: Sáng 1, Chiều 1, Tối 1" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label required-field">Thời gian dùng</label>
                                <input type="text" name="medications[${medicationCount}][duration]" class="form-control" placeholder="VD: 7 ngày" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label">Lưu ý riêng</label>
                        <input type="text" name="medications[${medicationCount}][notes]" class="form-control" placeholder="VD: Uống sau ăn, Tránh uống với sữa">
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
        }

        function removeMedication(id) {
            const el = document.getElementById(`medication-${id}`);
            if (el) el.remove();
        }

        document.addEventListener('DOMContentLoaded', () => {
            addMedication(); // Add first medication by default
        });

        document.getElementById('prescriptionForm').addEventListener('submit', function(e) {
            if (document.querySelectorAll('.medication-item').length === 0) {
                e.preventDefault();
                alert('Vui lòng thêm ít nhất một loại thuốc!');
            }
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