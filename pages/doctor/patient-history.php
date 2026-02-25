<?php
ob_start();
session_start();

set_exception_handler(function ($e) {
    error_log("Doctor patient-history uncaught: " . $e->getMessage());
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


$stmt = $pdo->prepare("SELECT id, fullname FROM doctb WHERE username = :doctor");
$stmt->execute([':doctor' => $doctor]);
$doc_info = $stmt->fetch(PDO::FETCH_ASSOC);
$doctor_id = $doc_info['id'] ?? 0;


$view_record_id = isset($_GET['view']) ? intval($_GET['view']) : 0;
$record_detail = null;

if ($view_record_id > 0) {
    $stmt = $pdo->prepare("
        SELECT mr.*, 
               p.fname, p.lname, p.contact, p.email, p.gender, p.date_of_birth, p.blood_group,
               d.fullname as doctor_name
        FROM medical_records mr
        INNER JOIN patreg p ON mr.patient_id = p.pid
        LEFT JOIN doctb d ON mr.doctor_id = d.id
        WHERE mr.id = :id AND mr.doctor_id = :doctor_id
    ");
    $stmt->execute([':id' => $view_record_id, ':doctor_id' => $doctor_id]);
    $record_detail = $stmt->fetch(PDO::FETCH_ASSOC);
}


$search_query = '';
$search_condition = '';
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search_query = trim($_GET['search']);
    $search_condition = " AND (p.fname LIKE :search OR p.lname LIKE :search OR mr.diagnosis LIKE :search)";
}

$page_num = isset($_GET['page_num']) ? max(1, intval($_GET['page_num'])) : 1;
$records_per_page = 10;
$offset = ($page_num - 1) * $records_per_page;


$count_sql = "SELECT COUNT(*) FROM medical_records mr 
              INNER JOIN patreg p ON mr.patient_id = p.pid 
              WHERE mr.doctor_id = :doctor_id $search_condition";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->bindValue(':doctor_id', $doctor_id, PDO::PARAM_INT);
if ($search_query) {
    $count_stmt->bindValue(':search', "%$search_query%");
}
$count_stmt->execute();
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $records_per_page);


$sql = "SELECT mr.*, 
               p.fname, p.lname, p.contact, p.gender, p.date_of_birth
        FROM medical_records mr
        INNER JOIN patreg p ON mr.patient_id = p.pid
        WHERE mr.doctor_id = :doctor_id $search_condition
        ORDER BY mr.created_at DESC
        LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':doctor_id', $doctor_id, PDO::PARAM_INT);
if ($search_query) {
    $stmt->bindValue(':search', "%$search_query%");
}
$stmt->bindValue(':limit', $records_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$medical_records = $stmt->fetchAll(PDO::FETCH_ASSOC);


function calculateAge($dob)
{
    if (!$dob) return 'N/A';
    $birthDate = new DateTime($dob);
    $today = new DateTime('today');
    $age = $birthDate->diff($today)->y;
    return $age . ' tuổi';
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch sử Bệnh án - Bệnh viện Global</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/custom/medical-theme.css">
    <style>
        * {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
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

        .page-header {
            background: linear-gradient(135deg, #d2302c 0%, #8b0000 100%);
            padding: 32px;
            border-radius: 20px;
            color: white;
            margin-bottom: 24px;
            box-shadow: 0 20px 60px rgba(210, 48, 44, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.2);
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

        .records-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08), 0 0 0 1px rgba(0, 0, 0, 0.02);
        }

        .record-item {
            background: linear-gradient(135deg, #fde2e4 0%, #fee2e2 100%);
            border: 2px solid #f8d7da;
            border-radius: 14px;
            padding: 12px;
            margin-bottom: 10px;
            position: relative;
            overflow: hidden;
        }

        .record-item:hover {
            box-shadow: 0 12px 48px rgba(210, 48, 44, 0.25);
            transform: translateY(-4px) scale(1.01);
            border-color: #f8d7da;
        }

        .record-item:hover::before {
            width: 100%;
            opacity: 0.03;
        }

        .vital-signs {
            background: linear-gradient(135deg, #fde2e4 0%, #fee2e2 100%);
            padding: 10px;
            border-radius: 10px;
            margin: 10px 0;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .vital-item {
            display: inline-block;
            margin-right: 14px;
            margin-bottom: 5px;
            padding: 5px 10px;
            background: white;
            border-radius: 8px;
            font-size: 11px;
        }

        .btn-medical {
            background: linear-gradient(135deg, #d2302c 0%, #8b0000 100%);
            color: white;
            border: none;
            padding: 6px 14px;
            border-radius: 8px;
            font-weight: 700;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 16px rgba(210, 48, 44, 0.3);
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
        }

        .btn-medical:hover {
            background: linear-gradient(135deg, #8b0000 0%, #6b0000 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(210, 48, 44, 0.4);
        }

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            z-index: 1000;
            display: none;
            backdrop-filter: blur(4px);
        }

        .modal-overlay.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .modal-content-detail {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: 20px;
            padding: 32px;
            max-width: 900px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            z-index: 1001;
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.4);
            animation: slideUp 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translate(-50%, -40%);
            }

            to {
                opacity: 1;
                transform: translate(-50%, -50%);
            }
        }

        .patient-info-box {
            background: linear-gradient(135deg, #fde2e4 0%, #fee2e2 100%);
            padding: 24px;
            border-radius: 16px;
            margin-bottom: 24px;
            box-shadow: 0 4px 16px rgba(210, 48, 44, 0.2);
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: #d2302c;
            background: white;
            padding: 12px 24px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
            margin-bottom: 20px;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .back-link:hover {
            color: #8b0000;
            text-decoration: none;
            transform: translateX(-8px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .form-control:focus {
            border-color: #d2302c;
            box-shadow: 0 0 0 0.2rem rgba(210, 48, 44, 0.25);
        }

        .badge {
            padding: 6px 12px;
            font-weight: 600;
            border-radius: 8px;
        }

        .pagination .page-link {
            border-radius: 8px;
            margin: 0 4px;
            border: 2px solid #f8d7da;
            color: #d2302c;
            font-weight: 600;
        }

        .pagination .page-item.active .page-link {
            background: linear-gradient(135deg, #d2302c, #8b0000);
            border-color: #d2302c;
        }

        .pagination .page-link:hover {
            background-color: #fde2e4;
            border-color: #d2302c;
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
            <h1><i class="fas fa-notes-medical mr-3"></i>Lịch sử Bệnh án</h1>
            <p class="mb-0 mt-2">Quản lý và theo dõi hồ sơ bệnh án của bệnh nhân</p>
        </div>

        <?php displayMessage(); ?>

        
        <div class="records-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0"><i class="fas fa-folder-open mr-2" style="color: #d2302c;"></i>Danh sách Bệnh án</h5>
                <div class="d-flex">
                    <form method="GET" class="form-inline mr-3">
                        <input type="text" name="search" class="form-control mr-2" placeholder="Tìm theo tên hoặc chẩn đoán..." value="<?php echo htmlspecialchars($search_query); ?>" style="width: 280px;">
                        <button type="submit" class="btn btn-medical btn-sm"><i class="fas fa-search"></i></button>
                        <?php if ($search_query): ?>
                            <a href="patient-history.php" class="btn btn-secondary btn-sm ml-2"><i class="fas fa-times"></i></a>
                        <?php endif; ?>
                    </form>
                    <a href="medical-records.php?page=add" class="btn btn-success">
                        <i class="fas fa-plus mr-2"></i>Thêm bệnh án mới
                    </a>
                </div>
            </div>

            <?php if (empty($medical_records)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-2"></i>
                    <?php echo $search_query ? 'Không tìm thấy bệnh án nào.' : 'Chưa có bệnh án nào.'; ?>
                </div>
            <?php else: ?>
                <?php foreach ($medical_records as $record): ?>
                    <div class="record-item">
                        <div class="row">
                            <div class="col-md-4">
                                <h6 class="mb-2">
                                    <i class="fas fa-user-injured text-primary mr-1"></i>
                                    <strong><?php echo htmlspecialchars($record['fname'] . ' ' . $record['lname']); ?></strong>
                                </h6>
                                <p class="mb-1 text-muted">
                                    <small>
                                        <i class="fas fa-<?php echo ($record['gender'] == 'Nam') ? 'mars text-info' : 'venus text-danger'; ?> mr-1"></i>
                                        <?php echo $record['gender']; ?> - <?php echo calculateAge($record['date_of_birth']); ?>
                                    </small>
                                </p>
                                <p class="mb-0 text-muted">
                                    <small>
                                        <i class="fas fa-phone text-success mr-1"></i>
                                        <?php echo htmlspecialchars($record['contact']); ?>
                                    </small>
                                </p>
                            </div>
                            <div class="col-md-5">
                                <div class="vital-signs">
                                    <?php if ($record['blood_pressure']): ?>
                                        <span class="vital-item">
                                            <i class="fas fa-heartbeat text-danger mr-1"></i>
                                            <strong>HA:</strong> <?php echo $record['blood_pressure']; ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($record['heart_rate']): ?>
                                        <span class="vital-item">
                                            <i class="fas fa-heart text-danger mr-1"></i>
                                            <strong>NT:</strong> <?php echo $record['heart_rate']; ?> bpm
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($record['temperature']): ?>
                                        <span class="vital-item">
                                            <i class="fas fa-thermometer-half text-warning mr-1"></i>
                                            <strong>T°:</strong> <?php echo $record['temperature']; ?>°C
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <p class="mb-1"><strong>Triệu chứng:</strong> <?php echo htmlspecialchars(substr($record['symptoms'], 0, 80)); ?>...</p>
                                <p class="mb-0">
                                    <strong>Chẩn đoán:</strong>
                                    <span class="badge badge-warning"><?php echo htmlspecialchars($record['diagnosis']); ?></span>
                                </p>
                            </div>
                            <div class="col-md-3 text-right">
                                <p class="mb-1">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar mr-1"></i>
                                        <?php echo date('d/m/Y', strtotime($record['record_date'])); ?>
                                    </small>
                                </p>
                                <p class="mb-3">
                                    <small class="text-muted">
                                        <i class="fas fa-clock mr-1"></i>
                                        <?php echo date('H:i', strtotime($record['created_at'])); ?>
                                    </small>
                                </p>
                                <a href="?view=<?php echo $record['id']; ?>" class="btn btn-medical btn-sm">
                                    <i class="fas fa-eye mr-1"></i>Xem chi tiết
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                
                <?php if ($total_pages > 1): ?>
                    <nav class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($page_num > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page_num=<?php echo ($page_num - 1); ?><?php echo $search_query ? '&search=' . urlencode($search_query) : ''; ?>">
                                        <i class="fas fa-chevron-left"></i> Trước
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo ($i == $page_num) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page_num=<?php echo $i; ?><?php echo $search_query ? '&search=' . urlencode($search_query) : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($page_num < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page_num=<?php echo ($page_num + 1); ?><?php echo $search_query ? '&search=' . urlencode($search_query) : ''; ?>">
                                        Sau <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    
    <?php if ($record_detail): ?>
        <div class="modal-overlay active" onclick="window.location.href='patient-history.php'"></div>
        <div class="modal-content-detail">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4><i class="fas fa-file-medical text-primary mr-2"></i>Chi tiết Bệnh án</h4>
                <a href="patient-history.php" class="btn btn-secondary">
                    <i class="fas fa-times mr-1"></i>Đóng
                </a>
            </div>

            <div class="patient-info-box">
                <h5 class="mb-3">Thông tin Bệnh nhân</h5>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Họ tên:</strong> <?php echo htmlspecialchars($record_detail['fname'] . ' ' . $record_detail['lname']); ?></p>
                        <p><strong>Giới tính:</strong> <?php echo $record_detail['gender']; ?></p>
                        <p><strong>Tuổi:</strong> <?php echo calculateAge($record_detail['date_of_birth']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Điện thoại:</strong> <?php echo htmlspecialchars($record_detail['contact']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($record_detail['email']); ?></p>
                        <p><strong>Nhóm máu:</strong> <?php echo $record_detail['blood_group'] ?: 'N/A'; ?></p>
                    </div>
                </div>
            </div>

            <div class="vital-signs">
                <h6 class="mb-3">Chỉ số Sức khỏe</h6>
                <div class="row">
                    <div class="col-md-3">
                        <p><strong>Chiều cao:</strong> <?php echo $record_detail['height'] ? $record_detail['height'] . ' cm' : 'N/A'; ?></p>
                    </div>
                    <div class="col-md-3">
                        <p><strong>Cân nặng:</strong> <?php echo $record_detail['weight'] ? $record_detail['weight'] . ' kg' : 'N/A'; ?></p>
                    </div>
                    <div class="col-md-3">
                        <p><strong>Huyết áp:</strong> <?php echo $record_detail['blood_pressure'] ?: 'N/A'; ?></p>
                    </div>
                    <div class="col-md-3">
                        <p><strong>Nhịp tim:</strong> <?php echo $record_detail['heart_rate'] ? $record_detail['heart_rate'] . ' bpm' : 'N/A'; ?></p>
                    </div>
                </div>
            </div>

            <hr>
            <h6>Thông tin Khám bệnh</h6>
            <p><strong>Ngày khám:</strong> <?php echo date('d/m/Y', strtotime($record_detail['record_date'])); ?></p>
            <p><strong>Bác sĩ khám:</strong> <?php echo htmlspecialchars($record_detail['doctor_name']); ?></p>

            <div class="mt-3">
                <h6 class="text-primary">Triệu chứng</h6>
                <p><?php echo nl2br(htmlspecialchars($record_detail['symptoms'])); ?></p>
            </div>

            <div class="mt-3">
                <h6 class="text-danger">Chẩn đoán</h6>
                <p class="font-weight-bold"><?php echo nl2br(htmlspecialchars($record_detail['diagnosis'])); ?></p>
            </div>

            <div class="mt-3">
                <h6 class="text-success">Phương án Điều trị</h6>
                <p><?php echo nl2br(htmlspecialchars($record_detail['treatment_plan'])); ?></p>
            </div>

            <?php if ($record_detail['notes']): ?>
                <div class="mt-3">
                    <h6 class="text-info">Ghi chú</h6>
                    <p><?php echo nl2br(htmlspecialchars($record_detail['notes'])); ?></p>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

    
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