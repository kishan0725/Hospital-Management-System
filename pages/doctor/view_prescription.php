<?php
ob_start();
session_start();

set_exception_handler(function ($e) {
    error_log("Doctor view_prescription uncaught: " . $e->getMessage());
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


if (!isset($_GET['id'])) {
    header("Location: prescriptions.php");
    exit();
}

$prescription_id = $_GET['id'];


$stmt = $pdo->prepare("
    SELECT p.*, 
           p.fname, p.lname, p.ID as app_id,
           pat.email, pat.gender, pat.contact,
           d.fullname as doctor_name, d.spec as doctor_spec
    FROM prestb p
    LEFT JOIN patreg pat ON p.pid = pat.pid
    LEFT JOIN doctb d ON (p.doctor = d.username OR p.doctor = d.fullname)
    WHERE p.pres_id = ?
");
$stmt->execute([$prescription_id]);
$prescription = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$prescription) {
    header("Location: prescriptions.php");
    exit();
}


$med_stmt = $pdo->prepare("
    SELECT * FROM prescription_medications 
    WHERE prescription_id = ?
    ORDER BY id
");
$med_stmt->execute([$prescription_id]);
$medications = $med_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết đơn thuốc</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <style>
        :root {
            --primary-color: #d2302c;
            --secondary-color: #8b0000;
        }

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

        .prescription-view {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .prescription-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 1.6rem;
            text-align: center;
        }

        .prescription-header h1 {
            margin: 0;
            font-size: 1.6rem;
            font-weight: 700;
        }

        .prescription-body {
            padding: 1.6rem;
        }

        .info-section {
            margin-bottom: 1.6rem;
            padding: 1.2rem;
            background: #f8fafc;
            border-radius: 8px;
            border-left: 4px solid var(--primary-color);
        }

        .info-section h3 {
            color: var(--primary-color);
            font-size: 1.0rem;
            font-weight: 600;
            margin-bottom: 0.8rem;
        }

        .info-row {
            display: flex;
            margin-bottom: 0.5rem;
        }

        .info-label {
            font-weight: 600;
            color: #64748b;
            width: 150px;
        }

        .info-value {
            color: #1e293b;
        }

        .medications-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .medications-table th {
            background: var(--primary-color);
            color: white;
            padding: 0.8rem;
            text-align: left;
            font-weight: 600;
        }

        .medications-table td {
            padding: 0.8rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .medications-table tr:hover {
            background: #f1f5f9;
        }

        .medication-number {
            background: var(--primary-color);
            color: white;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .btn-action {
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }

        .btn-pdf {
            background: #ef4444;
            color: white;
        }

        .btn-pdf:hover {
            background: #dc2626;
            transform: translateY(-2px);
        }

        .btn-back {
            background: #6b7280;
            color: white;
        }

        .btn-back:hover {
            background: #4b5563;
        }

        @media print {
            body {
                background: white;
            }

            .prescription-view {
                box-shadow: none;
            }

            .btn-action {
                display: none;
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
    <div class="container-lg py-4">
        <a href="dashboard.php" class="back-link">
            <i class="fas fa-arrow-left"></i>
            Quay lại bảng điều khiển
        </a>

        <div class="prescription-view">
            <div class="prescription-header">
                <h1><i class="fas fa-file-medical"></i> Đơn Thuốc</h1>
                <p class="mb-0">Bệnh viện Global - Chăm sóc sức khỏe toàn diện</p>
            </div>

            <div class="prescription-body">
                
                <div class="text-right mb-4">
                    <a href="export_prescription_pdf.php?id=<?php echo $prescription_id; ?>" class="btn-action btn-pdf" target="_blank">
                        <i class="fas fa-file-pdf mr-2"></i>Tải PDF
                    </a>
                    <a href="prescriptions.php" class="btn-action btn-back ml-2">
                        <i class="fas fa-list mr-2"></i>Danh sách đơn
                    </a>
                </div>

                
                <div class="info-section">
                    <h3><i class="fas fa-user-md mr-2"></i>Thông tin bác sĩ</h3>
                    <div class="info-row">
                        <div class="info-label">Bác sĩ:</div>
                        <div class="info-value"><?php echo htmlspecialchars($prescription['doctor_name']); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Chuyên khoa:</div>
                        <div class="info-value"><?php echo htmlspecialchars($prescription['doctor_spec']); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Ngày kê:</div>
                        <div class="info-value"><?php echo date('d/m/Y', strtotime($prescription['created_at'])); ?></div>
                    </div>
                </div>

                
                <div class="info-section">
                    <h3><i class="fas fa-user-injured mr-2"></i>Thông tin bệnh nhân</h3>
                    <div class="info-row">
                        <div class="info-label">Họ tên:</div>
                        <div class="info-value"><?php echo htmlspecialchars($prescription['fname'] . ' ' . $prescription['lname']); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Giới tính:</div>
                        <div class="info-value"><?php echo htmlspecialchars($prescription['gender']); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Số điện thoại:</div>
                        <div class="info-value"><?php echo htmlspecialchars($prescription['contact']); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Email:</div>
                        <div class="info-value"><?php echo htmlspecialchars($prescription['email']); ?></div>
                    </div>
                </div>

                
                <div class="info-section">
                    <h3><i class="fas fa-stethoscope mr-2"></i>Chẩn đoán</h3>
                    <div class="info-row">
                        <div class="info-label">Bệnh:</div>
                        <div class="info-value"><?php echo htmlspecialchars($prescription['disease']); ?></div>
                    </div>
                    <?php if (!empty($prescription['allergy'])): ?>
                        <div class="info-row">
                            <div class="info-label">Dị ứng:</div>
                            <div class="info-value text-danger font-weight-bold"><?php echo htmlspecialchars($prescription['allergy']); ?></div>
                        </div>
                    <?php endif; ?>
                    <div class="info-row">
                        <div class="info-label">Thời gian điều trị:</div>
                        <div class="info-value"><?php echo htmlspecialchars($prescription['treatment_duration'] ?? ''); ?></div>
                    </div>
                </div>

                
                <div class="info-section">
                    <h3><i class="fas fa-pills mr-2"></i>Danh sách thuốc</h3>
                    <?php if (count($medications) > 0): ?>
                        <table class="medications-table">
                            <thead>
                                <tr>
                                    <th width="50">#</th>
                                    <th>Tên thuốc</th>
                                    <th>Liều lượng</th>
                                    <th>Tần suất</th>
                                    <th>Thời gian</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($medications as $index => $med): ?>
                                    <tr>
                                        <td><span class="medication-number"><?php echo $index + 1; ?></span></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($med['medication_name']); ?></strong>
                                            <?php if (!empty($med['special_notes'])): ?>
                                                <br><small class="text-muted"><i class="fas fa-info-circle mr-1"></i><?php echo htmlspecialchars($med['special_notes']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($med['dosage']); ?></td>
                                        <td><?php echo htmlspecialchars($med['frequency']); ?></td>
                                        <td><?php echo htmlspecialchars($med['duration']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="text-muted">Chưa có thuốc nào được kê.</p>
                    <?php endif; ?>
                </div>

                
                <?php if (!empty($prescription['general_notes'])): ?>
                    <div class="info-section">
                        <h3><i class="fas fa-notes-medical mr-2"></i>Lời dặn dò</h3>
                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($prescription['general_notes'])); ?></p>
                    </div>
                <?php endif; ?>

                
                <div class="text-center mt-4 pt-4 border-top">
                    <p class="text-muted mb-1"><small>Đơn thuốc được tạo tự động bởi hệ thống</small></p>
                    <p class="text-muted mb-0"><small>Bệnh viện Global | Hotline: (84) 123-456-789 | Email: info@globalhospitals.com</small></p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    
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