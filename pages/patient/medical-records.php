<?php
ob_start();
session_start();

set_exception_handler(function ($e) {
    error_log("Patient medical-records uncaught: " . $e->getMessage());
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

$pid = $_SESSION['pid'] ?? null;
$fname = $_SESSION['fname'] ?? '';
$lname = $_SESSION['lname'] ?? '';

if (!$pid) {
    header("Location: ../../index.php");
    exit();
}


$medical_records = [];
try {
    $stmt = $pdo->prepare("
        SELECT mr.*, 
               p.fname, p.lname, p.email, p.contact,
               d.fullname as doctor_name,
               apt.appointmentDate, apt.appointmentTime
        FROM medical_records mr
        LEFT JOIN patreg p ON mr.patient_id = p.pid
        LEFT JOIN doctb d ON mr.doctor_id = d.id
        LEFT JOIN appointmenttb apt ON mr.appointment_id = apt.ID
        WHERE mr.patient_id = :pid
        ORDER BY mr.created_at DESC
    ");
    $stmt->execute([':pid' => $pid]);
    $medical_records = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Fetch medical records error: " . $e->getMessage());
}


$patient_profile = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM patreg WHERE pid = :pid");
    $stmt->execute([':pid' => $pid]);
    $patient_profile = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Fetch patient profile error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="shortcut icon" type="image/x-icon" href="../../images/favicon.png" />
    <title>Lịch sử bệnh án - Bệnh viện Global</title>

    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/custom/medical-theme.css">
    <style>
        body {
            background-image:
                linear-gradient(135deg, rgba(254, 226, 226, 0.85) 0%, rgba(252, 165, 165, 0.85) 25%, rgba(248, 113, 113, 0.85) 50%, rgba(239, 68, 68, 0.85) 75%, rgba(220, 38, 38, 0.85) 100%),
                url('../../images/ngua.png');
            background-size: cover, contain;
            background-position: center, center;
            background-repeat: no-repeat, no-repeat;
            background-attachment: fixed, fixed;
            font-family: 'Inter', sans-serif;
        }

        .medical-record-card {
            background: white;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 16px;
            border-left: 4px solid #ffd700;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .medical-record-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .record-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 12px;
        }

        .record-date {
            font-size: 13px;
            color: #6b7280;
            font-weight: 500;
        }

        .record-doctor {
            color: #d2302c;
            font-weight: 600;
        }

        .record-status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-completed {
            background-color: #d1fae5;
            color: #065f46;
        }

        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }

        .record-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .record-item {
            border-left: 3px solid #cbd5e1;
            padding-left: 15px;
        }

        .record-item-label {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .record-item-value {
            font-size: 13px;
            color: #1f2937;
            font-weight: 500;
        }

        .vitals-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 12px;
            margin-top: 12px;
        }

        .vital-card {
            background: #f0fdf4;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #bbf7d0;
        }

        .vital-label {
            font-size: 11px;
            color: #8b0000;
            font-weight: 600;
            text-transform: uppercase;
        }

        .vital-value {
            font-size: 16px;
            font-weight: 700;
            color: #065f46;
            margin-top: 5px;
        }

        .records-empty {
            background: white;
            border-radius: 8px;
            padding: 60px 20px;
            text-align: center;
            color: #6b7280;
        }

        .records-empty i {
            font-size: 48px;
            color: #d1d5db;
            margin-bottom: 15px;
        }

        .page-header {
            background: linear-gradient(135deg, #d2302c 0%, #ff4d4d 100%);
            color: white;
            padding: 24px;
            border-radius: 8px;
            margin-bottom: 24px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .page-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }

        .page-header p {
            margin: 4px 0 0 0;
            opacity: 0.9;
            font-size: 13px;
        }

        .patient-info-card {
            background: white;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 24px;
            border-left: 4px solid #d2302c;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .patient-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .info-item {
            border-left: 3px solid #cbd5e1;
            padding-left: 15px;
        }

        .info-label {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 14px;
            color: #1f2937;
            font-weight: 600;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #d2302c;
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            color: #ff4d4d;
            gap: 12px;
        }

        .filter-section {
            background: white;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .filter-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }

        .filter-input {
            padding: 6px 10px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 13px;
        }

        .filter-input:focus {
            outline: none;
            border-color: #d2302c;
            box-shadow: 0 0 0 3px rgba(210, 48, 44, 0.1);
        }

        .btn-filter {
            padding: 6px 14px;
            background-color: #d2302c;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-filter:hover {
            background-color: #ff4d4d;
        }

        .btn-reset {
            padding: 8px 16px;
            background-color: #e5e7eb;
            color: #374151;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-reset:hover {
            background-color: #d1d5db;
        }

        @media (max-width: 768px) {
            .page-header {
                padding: 20px;
            }

            .page-header h1 {
                font-size: 22px;
            }

            .record-content {
                grid-template-columns: 1fr;
            }

            .vitals-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .patient-info-grid {
                grid-template-columns: 1fr;
            }

            .filter-group {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-input {
                width: 100%;
            }

            .btn-filter,
            .btn-reset {
                width: 100%;
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
            <h1><i class="fas fa-file-medical-alt"></i> Lịch sử bệnh án</h1>
            <p>Xem và quản lý hồ sơ bệnh án của bạn</p>
        </div>

        
        <?php if ($patient_profile) { ?>
            <div class="patient-info-card">
                <h5 class="mb-3" style="color: #d2302c; font-weight: 700;">
                    <i class="fas fa-user-circle"></i> Thông tin cá nhân
                </h5>
                <div class="patient-info-grid">
                    <div class="info-item">
                        <div class="info-label">Họ và tên</div>
                        <div class="info-value"><?php echo $patient_profile['lname'] . ' ' . $patient_profile['fname']; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Email</div>
                        <div class="info-value"><?php echo $patient_profile['email']; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Số điện thoại</div>
                        <div class="info-value"><?php echo $patient_profile['contact']; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Nhóm máu</div>
                        <div class="info-value"><?php echo $patient_profile['blood_group'] ?? 'Chưa cập nhật'; ?></div>
                    </div>
                </div>
            </div>
        <?php } ?>

        
        <div class="filter-section">
            <div class="filter-group">
                <input type="text" id="searchInput" class="filter-input" placeholder="Tìm kiếm bác sĩ, chẩn đoán...">
                <button class="btn-filter" onclick="filterRecords()">
                    <i class="fas fa-search"></i> Tìm kiếm
                </button>
                <button class="btn-reset" onclick="resetFilter()">
                    <i class="fas fa-redo"></i> Đặt lại
                </button>
            </div>
        </div>

        
        <div id="recordsContainer">
            <?php if (empty($medical_records)) { ?>
                <div class="records-empty">
                    <i class="fas fa-inbox"></i>
                    <h5>Không có hồ sơ bệnh án</h5>
                    <p>Bạn chưa có hồ sơ bệnh án nào. Các hồ sơ sẽ được tạo sau khi bác sĩ cập nhật thông tin khám phá.</p>
                </div>
            <?php } else { ?>
                <?php foreach ($medical_records as $record) { ?>
                    <div class="medical-record-card record-item-data">
                        <div class="record-header">
                            <div>
                                <div class="record-date">
                                    <i class="fas fa-calendar"></i>
                                    <?php echo date('d/m/Y H:i', strtotime($record['created_at'])); ?>
                                </div>
                                <div class="record-doctor" style="margin-top: 5px;">
                                    <i class="fas fa-user-md"></i>
                                    <?php echo $record['doctor_name'] ?? 'Chưa xác định'; ?>
                                </div>
                            </div>
                            <div>
                                <span class="record-status <?php echo $record['status'] === 'completed' ? 'status-completed' : 'status-pending'; ?>">
                                    <?php echo $record['status'] === 'completed' ? 'Hoàn thành' : 'Đang chờ'; ?>
                                </span>
                            </div>
                        </div>

                        <div class="record-content">
                            <?php if ($record['diagnosis']) { ?>
                                <div class="record-item">
                                    <div class="record-item-label">Chẩn đoán</div>
                                    <div class="record-item-value"><?php echo $record['diagnosis']; ?></div>
                                </div>
                            <?php } ?>

                            <?php if ($record['symptoms']) { ?>
                                <div class="record-item">
                                    <div class="record-item-label">Triệu chứng</div>
                                    <div class="record-item-value"><?php echo $record['symptoms']; ?></div>
                                </div>
                            <?php } ?>

                            <?php if ($record['treatment']) { ?>
                                <div class="record-item">
                                    <div class="record-item-label">Điều trị</div>
                                    <div class="record-item-value"><?php echo $record['treatment']; ?></div>
                                </div>
                            <?php } ?>

                            <?php if ($record['appointment_date']) { ?>
                                <div class="record-item">
                                    <div class="record-item-label">Ngày khám</div>
                                    <div class="record-item-value">
                                        <?php echo date('d/m/Y', strtotime($record['appointment_date'])); ?>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>

                        
                        <?php if ($record['height'] || $record['weight'] || $record['blood_pressure'] || $record['heart_rate'] || $record['temperature']) { ?>
                            <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #e5e7eb;">
                                <h6 style="color: #d2302c; font-weight: 700; margin-bottom: 15px;">
                                    <i class="fas fa-heartbeat"></i> Chỉ số sức khỏe
                                </h6>
                                <div class="vitals-grid">
                                    <?php if ($record['height']) { ?>
                                        <div class="vital-card">
                                            <div class="vital-label">Chiều cao</div>
                                            <div class="vital-value"><?php echo $record['height']; ?> cm</div>
                                        </div>
                                    <?php } ?>

                                    <?php if ($record['weight']) { ?>
                                        <div class="vital-card">
                                            <div class="vital-label">Cân nặng</div>
                                            <div class="vital-value"><?php echo $record['weight']; ?> kg</div>
                                        </div>
                                    <?php } ?>

                                    <?php if ($record['blood_pressure']) { ?>
                                        <div class="vital-card">
                                            <div class="vital-label">Huyết áp</div>
                                            <div class="vital-value"><?php echo $record['blood_pressure']; ?></div>
                                        </div>
                                    <?php } ?>

                                    <?php if ($record['heart_rate']) { ?>
                                        <div class="vital-card">
                                            <div class="vital-label">Nhịp tim</div>
                                            <div class="vital-value"><?php echo $record['heart_rate']; ?> bpm</div>
                                        </div>
                                    <?php } ?>

                                    <?php if ($record['temperature']) { ?>
                                        <div class="vital-card">
                                            <div class="vital-label">Nhiệt độ</div>
                                            <div class="vital-value"><?php echo $record['temperature']; ?>°C</div>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php } ?>

                        
                        <?php if ($record['notes']) { ?>
                            <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e5e7eb;">
                                <h6 style="color: #d2302c; font-weight: 700; margin-bottom: 10px;">
                                    <i class="fas fa-sticky-note"></i> Ghi chú
                                </h6>
                                <p style="color: #374151; margin: 0; line-height: 1.6;"><?php echo nl2br($record['notes']); ?></p>
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>
            <?php } ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        function filterRecords() {
            const searchText = document.getElementById('searchInput').value.toLowerCase();
            const records = document.querySelectorAll('.record-item-data');

            records.forEach(record => {
                const text = record.textContent.toLowerCase();
                if (text.includes(searchText)) {
                    record.style.display = 'block';
                } else {
                    record.style.display = 'none';
                }
            });
        }

        function resetFilter() {
            document.getElementById('searchInput').value = '';
            const records = document.querySelectorAll('.record-item-data');
            records.forEach(record => {
                record.style.display = 'block';
            });
        }

        document.getElementById('searchInput').addEventListener('keyup', filterRecords);
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