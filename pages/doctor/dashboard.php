<?php
ob_start();
session_start();
require_once(__DIR__ . '/../../config.php');
require_once('../../includes/messages.php');
require_once('../../includes/functions.php');

$doctor = $_SESSION['dname'] ?? null;


if (!$doctor) {
    header("Location: ../../pages/auth/login.php");
    exit();
}


$stmt = $pdo->prepare("SELECT id, fullname FROM doctb WHERE username = :doctor");
$stmt->execute([':doctor' => $doctor]);
$doc_info = $stmt->fetch(PDO::FETCH_ASSOC);
$doctor_id = $doc_info['id'] ?? 0;
$doctor_fullname = $doc_info['fullname'] ?? $doctor;


$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$allowed_pages = array('dashboard', 'appointments', 'schedule');
if (!in_array($page, $allowed_pages)) {
    $page = 'dashboard';
}


if (isset($_GET['cancel'])) {
    try {
        $stmt = $pdo->prepare("UPDATE appointmenttb SET doctorStatus='0' WHERE ID = :id");
        $stmt->execute([':id' => $_GET['ID']]);
        redirectWithMessage("dashboard.php?page=appointments", 'success', 'Đã hủy lịch hẹn thành công');
    } catch (PDOException $e) {
        error_log("Cancel appointment error: " . $e->getMessage());
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="shortcut icon" type="image/x-icon" href="../../images/favicon.png" />
    <title>Bảng điều khiển Bác sĩ - Bệnh viện Global</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link rel="stylesheet" href="../../assets/css/custom/medical-theme.css">
    <link rel="stylesheet" href="../../assets/css/custom/global-improvements.css">
    <style>
        /* CSS Tùy chỉnh */
        .navbar-user.dropdown .dropdown-toggle::after {
            display: none;
        }

        .navbar-user .dropdown-menu {
            min-width: 220px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            border: none;
            padding: 0.5rem 0;
            margin-top: 0.5rem;
        }

        .navbar-user .dropdown-item {
            padding: 0.6rem 1.2rem;
            font-size: 0.85rem;
            transition: all 0.2s;
            display: flex;
            align-items: center;
        }

        .navbar-user .dropdown-item i {
            width: 18px;
            font-size: 0.8rem;
        }

        .navbar-user .dropdown-item:hover {
            background: #fff5f5;
            color: #d2302c;
            padding-left: 1.5rem;
        }

        .navbar-user .dropdown-item.text-danger:hover {
            background: #fef2f2;
            color: #dc2626;
        }

        .navbar-user .dropdown-divider {
            margin: 0.5rem 0;
        }

        .navbar-user-info {
            margin-left: 1rem;
        }

        .main-content {
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
        }

        .content-section {
            width: 100%;
            max-width: 1600px;
            padding: 1.5rem;
            margin: 0 auto;
        }

        .data-table-container {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .data-table {
            min-width: 900px;
            white-space: nowrap;
        }

        .btn-sm {
            font-size: 0.75rem;
            padding: 0.35rem 0.7rem;
            white-space: nowrap;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.2rem;
            width: 100%;
            max-width: 1280px;
            margin: 0 auto;
        }

        .top-navbar {
            padding: 0.8rem 1.2rem;
            flex-wrap: wrap;
        }

        .navbar-title {
            font-size: 1.1rem;
        }

        .search-box-form .input-group {
            width: 100% !important;
            max-width: 100% !important;
        }

        /* Style riêng cho trang Lịch (List View) */
        .schedule-wrapper {
            background: linear-gradient(135deg, #fff5f5 0%, #ffffff 100%);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 8px 32px rgba(210, 48, 44, 0.1);
            border: 2px solid rgba(255, 215, 0, 0.2);
            overflow: hidden;
        }

        .table-schedule {
            border-collapse: collapse;
            border-spacing: 0;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
            border: none;
            width: 100%;
            table-layout: fixed;
        }

        .table-schedule thead {
            background: linear-gradient(135deg, #d2302c 0%, #ff4d4d 50%, #ff6b6b 100%);
            position: relative;
        }

        .table-schedule thead::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #ffd700, #d4af37, #ffd700);
        }

        .table-schedule th {
            background-color: transparent;
            color: white;
            font-weight: 700;
            text-align: center;
            padding: 1rem 0.8rem !important;
            font-size: 0.85rem;
            letter-spacing: 0.4px;
            text-transform: uppercase;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            border: none;
        }

        .table-schedule th:first-child {
            width: 25%;
            border-radius: 16px 0 0 0;
        }

        .table-schedule th:nth-child(2) {
            width: 50%;
        }

        .table-schedule th:last-child {
            width: 25%;
            border-radius: 0 16px 0 0;
        }

        .table-schedule tbody tr {
            border-bottom: 1px solid #f0f0f0;
            transition: all 0.3s ease;
            background: white;
        }

        .table-schedule tbody tr:hover {
            background: linear-gradient(135deg, rgba(255, 245, 245, 0.95), rgba(255, 235, 235, 0.95));
            box-shadow: 0 4px 12px rgba(210, 48, 44, 0.1);
        }

        .table-schedule tbody tr:last-child {
            border-bottom: none;
        }

        .table-schedule tbody tr:last-child td:first-child {
            border-radius: 0 0 0 16px;
        }

        .table-schedule tbody tr:last-child td:last-child {
            border-radius: 0 0 16px 0;
        }

        .table-schedule td {
            vertical-align: middle;
            padding: 1rem 0.8rem !important;
            font-size: 0.85rem;
            border: none;
            text-align: center;
        }

        .table-schedule td:first-child {
            font-weight: 700;
            color: #1e293b;
        }

        .table-schedule td:nth-child(2) {
            text-align: center;
        }

        .schedule-badge {
            font-size: 0.85rem;
            padding: 5px 10px;
            border-radius: 4px;
        }

        .today-row {
            background: linear-gradient(135deg, rgba(255, 215, 0, 0.1), rgba(210, 48, 44, 0.08));
            border-left: 6px solid #d2302c;
            box-shadow: 0 4px 16px rgba(210, 48, 44, 0.12);
        }

        .today-row::before {
            background: linear-gradient(180deg, #d2302c, #ffd700);
        }

        .today-row:hover {
            background: linear-gradient(135deg, rgba(255, 215, 0, 0.2), rgba(210, 48, 44, 0.15));
            box-shadow: 0 10px 30px rgba(210, 48, 44, 0.2);
        }

        .time-pill {
            display: inline-block;
            background: linear-gradient(135deg, #d2302c 0%, #ff4d4d 50%, #ff6b6b 100%);
            color: white;
            padding: 0.6rem 1.2rem;
            border-radius: 20px;
            margin: 0.3rem;
            font-weight: 700;
            font-size: 0.85rem;
            box-shadow: 0 3px 10px rgba(210, 48, 44, 0.25);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .time-pill::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s ease;
        }

        .time-pill:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(210, 48, 44, 0.4);
            background: linear-gradient(135deg, #ff4d4d 0%, #ff6b6b 50%, #ff8080 100%);
        }

        .time-pill:hover::before {
            left: 100%;
        }

        .off-day {
            color: #495057;
            font-style: normal;
            font-weight: 700;
            background: rgba(108, 117, 125, 0.15);
            padding: 0.5rem 1rem;
            border-radius: 8px;
            display: inline-block;
        }

        .schedule-card-header {
            background: linear-gradient(135deg, #ffffff 0%, #fff5f5 100%);
            border-bottom: 2px solid #ffd700;
            border-radius: 14px 14px 0 0;
            padding: 1rem 1.2rem;
        }

        .schedule-card-header h5 {
            font-size: 1.05rem;
            font-weight: 700;
            color: #8b0000;
            margin: 0;
            display: flex;
            align-items: center;
        }

        .schedule-table-container {
            padding: 0;
        }

        .day-label {
            font-size: 0.95rem;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .status-active {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, #28a745, #34ce57);
            color: white;
            padding: 0.6rem 1.2rem;
            border-radius: 20px;
            font-weight: 700;
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        }

        .status-inactive {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: #495057;
            color: white;
            padding: 0.6rem 1.2rem;
            border-radius: 20px;
            font-weight: 700;
        }

        /* Mobile Card Layout */
        .schedule-mobile-card {
            display: none;
        }

        /* Responsive Design for Schedule Table */
        @media (max-width: 768px) {

            /* Hide table, show cards */
            .table-schedule {
                display: none !important;
            }

            .schedule-mobile-card {
                display: block;
            }

            .mobile-day-card {
                background: white;
                border-radius: 12px;
                padding: 1.5rem;
                margin-bottom: 1rem;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
                border-left: 4px solid #e9ecef;
                transition: all 0.3s ease;
                display: grid;
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .mobile-day-card:hover {
                box-shadow: 0 6px 16px rgba(210, 48, 44, 0.15);
                transform: translateY(-2px);
            }

            .mobile-day-card.today {
                background: linear-gradient(135deg, rgba(255, 215, 0, 0.1), rgba(210, 48, 44, 0.05));
                border-left-color: #d2302c;
                border-left-width: 5px;
            }

            .mobile-card-header {
                display: grid;
                grid-template-columns: 1fr auto;
                align-items: center;
                gap: 1rem;
                padding-bottom: 1rem;
                border-bottom: 2px solid #f0f0f0;
            }

            .mobile-day-name {
                font-size: 1.3rem;
                font-weight: 700;
                color: #1e293b;
            }

            .mobile-today-badge {
                background: linear-gradient(135deg, #d2302c, #ff4d4d);
                color: white;
                padding: 0.4rem 1rem;
                border-radius: 20px;
                font-size: 0.85rem;
                font-weight: 700;
                white-space: nowrap;
            }

            .mobile-time-section {
                display: grid;
                grid-template-columns: 1fr;
                gap: 0.5rem;
            }

            .mobile-section-label {
                font-size: 0.8rem;
                font-weight: 600;
                color: #6c757d;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .mobile-time-content {
                display: grid;
                grid-template-columns: 1fr;
                gap: 0.5rem;
            }

            .mobile-time-pill {
                background: linear-gradient(135deg, #d2302c, #ff4d4d);
                color: white;
                padding: 0.6rem 1rem;
                border-radius: 20px;
                font-size: 0.9rem;
                font-weight: 700;
                box-shadow: 0 3px 8px rgba(210, 48, 44, 0.3);
                text-align: center;
            }

            .mobile-off-day {
                color: #495057;
                font-weight: 700;
                font-size: 1rem;
                background: rgba(108, 117, 125, 0.15);
                padding: 0.6rem 1rem;
                border-radius: 8px;
                text-align: center;
            }

            .mobile-status {
                display: grid;
                grid-template-columns: 1fr;
                gap: 0.5rem;
            }

            .mobile-status-active {
                background: linear-gradient(135deg, #28a745, #34ce57);
                color: white;
                padding: 0.5rem 1rem;
                border-radius: 20px;
                font-size: 0.9rem;
                font-weight: 700;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 0.5rem;
            }

            .mobile-status-inactive {
                background: #495057;
                color: white;
                padding: 0.5rem 1rem;
                border-radius: 20px;
                font-size: 0.9rem;
                font-weight: 700;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 0.5rem;
            }

            .schedule-wrapper {
                padding: 1rem;
                border-radius: 12px;
            }

            .schedule-card-header {
                padding: 1rem;
            }

            .schedule-card-header h5 {
                font-size: 1.1rem;
            }

            .section-header h2 {
                font-size: 1.8rem !important;
            }

            .section-header p {
                font-size: 1rem !important;
            }
        }

        @media (max-width: 480px) {
            .mobile-day-card {
                padding: 1.2rem;
            }

            .mobile-day-name {
                font-size: 1.1rem;
            }

            .mobile-today-badge {
                font-size: 0.75rem;
                padding: 0.3rem 0.8rem;
            }

            .mobile-time-pill {
                font-size: 0.85rem;
                padding: 0.5rem 0.9rem;
            }

            .section-header h2 {
                font-size: 1.5rem !important;
            }

            .section-header p {
                font-size: 0.9rem !important;
            }
        }

        @media (max-width: 1024px) {
            .sidebar {
                position: fixed;
                transform: translateX(-100%);
                transition: transform 0.3s ease;
                z-index: 1000;
                height: 100vh;
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0 !important;
                width: 100%;
            }

            .mobile-menu-btn {
                display: block !important;
            }
        }

        .mobile-menu-btn {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1001;
            background: linear-gradient(135deg, #d2302c 0%, #ff4d4d 100%);
            color: white;
            border: none;
            width: 45px;
            height: 45px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(210, 48, 44, 0.3);
            cursor: pointer;
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        .sidebar-overlay.active {
            display: block;
        }

        body {
            background-image:
                linear-gradient(135deg, rgba(254, 243, 199, 0.85) 0%, rgba(254, 215, 170, 0.85) 25%, rgba(253, 186, 116, 0.85) 50%, rgba(251, 146, 60, 0.85) 75%, rgba(249, 115, 22, 0.85) 100%),
                url('../../images/ngua.png');
            background-size: cover, contain;
            background-position: center, center;
            background-repeat: no-repeat, no-repeat;
            background-attachment: fixed, fixed;
            overflow-x: hidden;
        }

        .dashboard-container {
            width: 100%;
            max-width: 1920px;
            margin: 0 auto;
            overflow-x: hidden;
        }

        .content-section {
            width: 100%;
            max-width: 1600px;
            padding: 1.5rem;
            margin: 0 auto;
        }

        /* Hiệu ứng hoa đào rơi */
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
            transform-origin: center;
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

        /* Biến thể khác nhau cho hoa đào */
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

    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>

<body>
    
    <div class="petals-container" id="petals"></div>

    <script>
        // Tạo hoa đào rơi
        function createPetals() {
            const petalsContainer = document.getElementById('petals');
            const numberOfPetals = 25; // Số lượng hoa đào

            for (let i = 0; i < numberOfPetals; i++) {
                const petal = document.createElement('div');
                petal.className = 'petal';

                // Vị trí ngẫu nhiên
                petal.style.left = Math.random() * 100 + '%';

                // Delay ngẫu nhiên
                petal.style.animationDelay = Math.random() * 10 + 's';

                // Thời gian rơi ngẫu nhiên
                const duration = 8 + Math.random() * 10;
                petal.style.animationDuration = duration + 's';

                petalsContainer.appendChild(petal);
            }
        }

        // Gọi hàm khi trang load xong
        window.addEventListener('load', createPetals);

        // Hàm tìm kiếm lịch hẹn
        function filterAppointments() {
            const input = document.getElementById('appointmentSearch');
            if (!input) return;

            const filter = input.value.toLowerCase();
            const table = document.querySelector('.data-table tbody');
            if (!table) return;

            const rows = table.getElementsByTagName('tr');

            for (let i = 0; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName('td');
                let found = false;

                for (let j = 0; j < cells.length; j++) {
                    const cellText = cells[j].textContent || cells[j].innerText;
                    if (cellText.toLowerCase().indexOf(filter) > -1) {
                        found = true;
                        break;
                    }
                }

                rows[i].style.display = found ? '' : 'none';
            }
        }
    </script>

    <body>
        <?php displayMessage(); ?>
        <button class="mobile-menu-btn" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>
        <div class="sidebar-overlay" onclick="toggleSidebar()"></div>
        <div class="dashboard-container">
            <aside class="sidebar">
                <div class="sidebar-header">
                    <div class="sidebar-logo"><i class="fas fa-user-md"></i></div>
                    <div>
                        <h1 class="sidebar-title">Bệnh viện Global</h1>
                        <div class="sidebar-subtitle">Cổng Bác sĩ</div>
                    </div>
                </div>

                <ul class="sidebar-menu">
                    <li class="sidebar-menu-item">
                        <a href="?page=dashboard" class="sidebar-menu-link <?php echo ($page === 'dashboard') ? 'active' : ''; ?>">
                            <i class="fas fa-th-large sidebar-menu-icon"></i><span>Bảng điều khiển</span>
                        </a>
                    </li>

                    <li class="sidebar-menu-item">
                        <a href="?page=schedule" class="sidebar-menu-link <?php echo ($page === 'schedule') ? 'active' : ''; ?>">
                            <i class="fas fa-calendar-check sidebar-menu-icon"></i><span>Lịch làm việc</span>
                        </a>
                    </li>

                    <li class="sidebar-menu-item">
                        <a href="?page=appointments" class="sidebar-menu-link <?php echo ($page === 'appointments') ? 'active' : ''; ?>">
                            <i class="fas fa-calendar-alt sidebar-menu-icon"></i><span>Lịch hẹn</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="prescriptions.php" class="sidebar-menu-link">
                            <i class="fas fa-file-prescription sidebar-menu-icon"></i><span>Đơn thuốc</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="documents.php" class="sidebar-menu-link">
                            <i class="fas fa-file-upload sidebar-menu-icon"></i>
                            <span>Upload tài liệu</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="patient-history.php" class="sidebar-menu-link">
                            <i class="fas fa-history sidebar-menu-icon"></i>
                            <span>Lịch sử bệnh án</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="medical-records.php" class="sidebar-menu-link">
                            <i class="fas fa-file-medical sidebar-menu-icon"></i><span>Hồ sơ bệnh án</span>
                        </a>
                    </li>
                </ul>
            </aside>

            <main class="main-content">
                <nav class="top-navbar">
                    <div class="navbar-left">
                        <h1 class="navbar-title">Bảng điều khiển Bác sĩ</h1>
                    </div>
                    <div class="navbar-right">
                        <div class="navbar-user dropdown">
                            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" id="navbarUserDropdown" data-toggle="dropdown">
                                <div class="navbar-user-avatar"><?php echo strtoupper(substr($doctor, 0, 1)); ?></div>
                                <div class="navbar-user-info">
                                    <div class="navbar-user-name">BS. <?php echo $doctor; ?></div>
                                    <div class="navbar-user-role">Bác sĩ</div>
                                </div>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="../../index.php"><i class="fas fa-home mr-2"></i> Trang chủ</a>
                            </div>
                        </div>
                    </div>
                </nav>

                <?php if ($page === 'dashboard') { ?>
                    <?php $is_saturday = (date('N') == 6); ?>
                    <section class="content-section">
                        <?php if ($is_saturday): ?>
                            <div class="alert alert-info alert-dismissible fade show shadow-sm" role="alert" style="border-left: 5px solid #17a2b8;">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-clipboard-check fa-2x mr-3 animate__animated animate__headShake animate__infinite"></i>
                                    <div>
                                        <strong>Nhắc nhở:</strong> Hôm nay là Thứ 7. Vui lòng kiểm tra lại lịch trực được phân công cho tuần sau!
                                    </div>
                                </div>
                                <a href="?page=schedule" class="btn btn-sm btn-info ml-auto" style="position: absolute; right: 40px; top: 15px;">Xem lịch ngay</a>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        <?php endif; ?>

                        <div class="section-header">
                            <h2 class="section-title">Xin chào, BS. <?php echo $doctor; ?>!</h2>
                        </div>

                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-icon primary"><i class="fas fa-calendar-check"></i></div>
                                <div class="stat-content">
                                    <div class="stat-label">Tổng lịch hẹn</div>
                                    <div class="stat-value">
                                        <?php
                                        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM appointmenttb WHERE TRIM(doctor) = TRIM(:doctor)");
                                        $stmt->execute([':doctor' => $doctor_fullname]);
                                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                                        echo $row['total'];
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <div class="stat-card">
                                <div class="stat-icon success"><i class="fas fa-users"></i></div>
                                <div class="stat-content">
                                    <div class="stat-label">Bệnh nhân hôm nay</div>
                                    <div class="stat-value">
                                        <?php
                                        
                                        $stmt = $pdo->prepare("SELECT COUNT(*) as active FROM appointmenttb WHERE TRIM(doctor) = TRIM(:doctor) AND appdate = CURDATE() AND userStatus = '1' AND doctorStatus = '1'");
                                        $stmt->execute([':doctor' => $doctor_fullname]);
                                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                                        echo $row['active'];
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <div class="stat-card">
                                <div class="stat-icon warning" style="background: #fffdf0; color: #d4af37;"><i class="fas fa-calendar-week"></i></div>
                                <div class="stat-content">
                                    <div class="stat-label">Đơn thuốc đã kê</div>
                                    <div class="stat-value">
                                        <?php
                                        $stmt = $pdo->prepare("SELECT COUNT(*) as pres FROM prestb WHERE doctor = :doctor");
                                        $stmt->execute([':doctor' => $doctor_fullname]);
                                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                                        echo $row['pres'];
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <a href="documents.php" class="stat-card text-decoration-none">
                                <div class="stat-icon info">
                                    <i class="fas fa-folder-open"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="stat-label">Tài liệu đã upload</div>
                                    <div class="stat-value">
                                        <?php
                                        $stmt = $pdo->prepare("SELECT COUNT(*) as docs FROM medical_documents WHERE doctor = :doctor");
                                        $stmt->execute([':doctor' => $doctor]);
                                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                                        echo $row['docs'];
                                        ?>
                                    </div>
                                </div>
                            </a>

                            <a href="medical-records.php" class="stat-card text-decoration-none">
                                <div class="stat-icon" style="background: #ffe6e6; color: #ff4d4d;"><i class="fas fa-file-medical"></i></div>
                                <div class="stat-content">
                                    <div class="stat-label">Hồ sơ bệnh án</div>
                                    <div class="stat-value">
                                        <?php
                                        $stmt = $pdo->prepare("SELECT COUNT(*) as records FROM medical_records WHERE doctor_id = :doctor_id");
                                        $stmt->execute([':doctor_id' => $doctor_id]);
                                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                                        echo $row['records'];
                                        ?>
                                    </div>
                                </div>
                            </a>

                            <a href="medicine-inventory.php" class="stat-card text-decoration-none">
                                <div class="stat-icon" style="background: #fff5f5; color: #d2302c;"><i class="fas fa-capsules"></i></div>
                                <div class="stat-content">
                                    <div class="stat-label">Kho thuốc</div>
                                    <div class="stat-value">
                                        <?php
                                        $stmt = $pdo->prepare("SELECT COUNT(*) as meds FROM medicines WHERE created_by = :doctor");
                                        $stmt->execute([':doctor' => $doctor]);
                                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                                        echo $row['meds'];
                                        ?>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </section>
                <?php } ?>

                <?php if ($page === 'schedule') {
                    
                    $stmt = $pdo->prepare("
                    SELECT day_of_week, start_time, end_time
                    FROM doctor_schedules
                    WHERE doctor_id = ?
                    ORDER BY id DESC
                ");
                    $stmt->execute([$doctor_id]);
                    $rawData = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    
                    $grouped_schedule = [];
                    if (count($rawData) > 0) {
                        foreach ($rawData as $row) {
                            $day = $row['day_of_week'];
                            $time_str = date('H:i', strtotime($row['start_time'])) . ' - ' . date('H:i', strtotime($row['end_time']));

                            
                            if (!isset($grouped_schedule[$day])) {
                                $grouped_schedule[$day] = $time_str;
                            }
                        }
                    }

                    
                    $daysLoop = [1, 2, 3, 4, 5, 6, 0];

                    
                    $vnDays = [
                        0 => 'Chủ Nhật',
                        1 => 'Thứ 2',
                        2 => 'Thứ 3',
                        3 => 'Thứ 4',
                        4 => 'Thứ 5',
                        5 => 'Thứ 6',
                        6 => 'Thứ 7'
                    ];
                ?>
                    <section class="content-section">
                        <div class="section-header" style="text-align: center; margin-bottom: 2.5rem;">
                            <h2 class="section-title" style="font-size: 2.5rem; font-weight: 700; color: #000; margin-bottom: 1rem;">
                                <i class="fas fa-calendar-week" style="color: #ffd700; margin-right: 1rem;"></i>
                                Lịch làm việc cố định
                            </h2>
                            <p class="section-subtitle" style="font-size: 1.15rem; color: #d2302c; font-weight: 600;">Thời khóa biểu hàng tuần của bạn</p>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="schedule-wrapper">
                                    <div class="schedule-card-header">
                                        <h5><i class="fas fa-clock mr-2"></i>Thời gian biểu chi tiết</h5>
                                    </div>
                                    <div class="schedule-table-container">
                                        
                                        <div class="table-responsive">
                                            <table class="table table-schedule mb-0">
                                                <thead>
                                                    <tr>
                                                        <th><i class="fas fa-calendar-day mr-2"></i>Thứ</th>
                                                        <th><i class="fas fa-clock mr-2"></i>Khung giờ làm việc</th>
                                                        <th><i class="fas fa-check-circle mr-2"></i>Trạng thái</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($daysLoop as $dayNum):
                                                        $isToday = (date('w') == $dayNum);
                                                        $rowClass = $isToday ? "today-row" : "";
                                                        $dayLabel = $vnDays[$dayNum];
                                                        if ($isToday) $dayLabel .= ' <span class="badge badge-danger ml-2" style="background: linear-gradient(135deg, #d2302c, #ff4d4d); color: white;">Hôm nay</span>';
                                                    ?>
                                                        <tr class="<?php echo $rowClass; ?>">
                                                            <td>
                                                                <?php echo $dayLabel; ?>
                                                            </td>

                                                            <td>
                                                                <?php
                                                                
                                                                if (isset($grouped_schedule[$dayNum])) {
                                                                    echo '<span class="time-pill"><i class="far fa-clock mr-2"></i>' . $grouped_schedule[$dayNum] . '</span>';
                                                                } else {
                                                                    echo '<span class="off-day"><i class="fas fa-moon mr-2"></i>Ngày nghỉ</span>';
                                                                }
                                                                ?>
                                                            </td>

                                                            <td>
                                                                <?php if (isset($grouped_schedule[$dayNum])): ?>
                                                                    <span class="status-active"><i class="fas fa-check-circle"></i>Có lịch</span>
                                                                <?php else: ?>
                                                                    <span class="status-inactive"><i class="fas fa-times-circle"></i>Vắng</span>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>

                                        
                                        <div class="schedule-mobile-card">
                                            <?php foreach ($daysLoop as $dayNum):
                                                $isToday = (date('w') == $dayNum);
                                                $cardClass = $isToday ? "mobile-day-card today" : "mobile-day-card";
                                            ?>
                                                <div class="<?php echo $cardClass; ?>">
                                                    <div class="mobile-card-header">
                                                        <div class="mobile-day-name"><?php echo $vnDays[$dayNum]; ?></div>
                                                        <?php if ($isToday): ?>
                                                            <span class="mobile-today-badge"><i class="fas fa-star mr-1"></i>Hôm nay</span>
                                                        <?php endif; ?>
                                                    </div>

                                                    <div class="mobile-time-section">
                                                        <div class="mobile-section-label">
                                                            <i class="fas fa-clock mr-1"></i>Khung giờ làm việc
                                                        </div>
                                                        <div class="mobile-time-content">
                                                            <?php
                                                            
                                                            if (isset($grouped_schedule[$dayNum])) {
                                                                echo '<span class="mobile-time-pill"><i class="far fa-clock mr-1"></i>' . $grouped_schedule[$dayNum] . '</span>';
                                                            } else {
                                                                echo '<span class="mobile-off-day"><i class="fas fa-moon mr-2"></i>Ngày nghỉ</span>';
                                                            }
                                                            ?>
                                                        </div>
                                                    </div>

                                                    <div class="mobile-status">
                                                        <div class="mobile-section-label">
                                                            <i class="fas fa-info-circle mr-1"></i>Trạng thái
                                                        </div>
                                                        <?php if (isset($grouped_schedule[$dayNum])): ?>
                                                            <span class="mobile-status-active"><i class="fas fa-check-circle"></i>Có lịch</span>
                                                        <?php else: ?>
                                                            <span class="mobile-status-inactive"><i class="fas fa-times-circle"></i>Vắng</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 text-muted small pl-2">
                                    <i class="fas fa-info-circle"></i> Lịch này được áp dụng lặp lại hàng tuần. Vui lòng liên hệ Admin nếu cần thay đổi.
                                </div>
                            </div>
                        </div>
                    </section>
                <?php } ?>

                <?php if ($page === 'appointments') { ?>
                    <section class="content-section">
                        <div class="section-header">
                            <h2 class="section-title">Lịch hẹn bệnh nhân</h2>
                        </div>
                        <div class="mb-4">
                            <div class="input-group" style="max-width: 500px;">
                                <input type="text" id="appointmentSearch" class="form-control" placeholder="Tìm theo SĐT, tên bệnh nhân, email..." onkeyup="filterAppointments()">
                                <div class="input-group-append">
                                    <span class="btn btn-primary"><i class="fas fa-search"></i> Tìm</span>
                                </div>
                            </div>
                        </div>
                        <div class="data-table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Mã BN</th>
                                        <th>Tên BN</th>
                                        <th>Liên hệ</th>
                                        <th>Ngày hẹn</th>
                                        <th>Giờ</th>
                                        <th>Trạng thái</th>
                                        <th>Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    
                                    $stmt = $pdo->prepare("
                                        SELECT a.pid, a.ID, a.appdate, a.apptime, a.userStatus, a.doctorStatus,
                                               CONCAT(p.fname, ' ', p.lname) as patient_name,
                                               p.contact as patient_contact
                                        FROM appointmenttb a
                                        LEFT JOIN patreg p ON a.pid = p.pid
                                        WHERE TRIM(a.doctor) = TRIM(:doctor)
                                        ORDER BY a.appdate DESC, a.apptime DESC
                                    ");
                                    $stmt->execute([':doctor' => $doctor_fullname]);
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    ?>
                                        <tr>
                                            <td>#<?php echo $row['pid']; ?></td>
                                            <td><strong><?php echo $row['patient_name'] ? $row['patient_name'] : '-'; ?></strong></td>
                                            <td><?php echo $row['patient_contact'] ? $row['patient_contact'] : '-'; ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($row['appdate'])); ?></td>
                                            <td><?php echo date('H:i', strtotime($row['apptime'])); ?></td>
                                            <td>
                                                <?php if ($row['userStatus'] == 1 && $row['doctorStatus'] == 1) echo '<span class="badge badge-success"><i class="fas fa-check-circle"></i> Hoạt động</span>';
                                                else echo '<span class="badge badge-danger"><i class="fas fa-times-circle"></i> Đã hủy</span>'; ?>
                                            </td>
                                            <td><a href="prescribe.php?pid=<?php echo $row['pid']; ?>&ID=<?php echo $row['ID']; ?>" class="btn btn-sm btn-primary"><i class="fas fa-prescription"></i> Kê đơn</a></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </section>
                <?php } ?>

                <?php if ($page === 'prescriptions') { ?>
                    <section class="content-section">
                        <div class="section-header">
                            <h2 class="section-title">Quản lý đơn thuốc</h2>
                        </div>
                        <div class="data-table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Mã ĐT</th>
                                        <th>Bệnh nhân</th>
                                        <th>Chẩn đoán</th>
                                        <th>Số thuốc</th>
                                        <th>Ngày kê</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $stmt = $pdo->prepare("SELECT * FROM prestb WHERE doctor = :doctor ORDER BY appdate DESC");
                                    $stmt->execute([':doctor' => $doctor]);
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                        <tr>
                                            <td>#<?php echo $row['ID']; ?></td>
                                            <td><?php echo $row['lname'] . ' ' . $row['fname']; ?></td>
                                            <td><?php echo $row['disease']; ?></td>
                                            <td><span class="badge badge-info">Chi tiết</span></td>
                                            <td><?php echo date('d/m/Y', strtotime($row['appdate'])); ?></td>
                                            <td><a href="view_prescription.php?id=<?php echo $row['pres_id']; ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a></td>
                                        </tr>
                                        </thead>
                                <tbody>
                                    <?php
                                        
                                        
                                        $stmt = $pdo->prepare("
                                        SELECT p.pres_id, p.ID as app_id, p.pid, p.disease, p.treatment_duration, p.created_at, p.appdate,
                                               p.fname, p.lname,
                                               (SELECT COUNT(id) FROM prescription_medications WHERE prescription_id = p.pres_id) as medication_count
                                        FROM prestb p
                                        WHERE p.doctor = :doctor
                                        ORDER BY p.appdate DESC, p.created_at DESC
                                    ");
                                        $stmt->execute([':doctor' => $doctor_fullname]);

                                        if ($stmt->rowCount() > 0) {
                                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                
                                                
                                                
                                                $view_id = $row['pres_id'];
                                    ?>
                                            <tr>
                                                <td>#<?php echo $row['app_id']; ?></td>
                                                <td><?php echo htmlspecialchars($row['lname'] . ' ' . $row['fname']); ?></td>
                                                <td><?php echo htmlspecialchars($row['disease']); ?></td>
                                                <td>
                                                    <span class="badge badge-info">
                                                        <?php echo $row['medication_count']; ?> thuốc
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($row['treatment_duration'] ?? 'N/A'); ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($row['appdate'])); ?></td>
                                                <td>
                                                    <?php if ($view_id): ?>
                                                        <a href="view_prescription.php?id=<?php echo $view_id; ?>"
                                                            class="btn btn-sm btn-info"
                                                            title="Xem chi tiết">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="export_prescription_pdf.php?id=<?php echo $view_id; ?>"
                                                            class="btn btn-sm btn-danger"
                                                            target="_blank"
                                                            title="Tải PDF">
                                                            <i class="fas fa-file-pdf"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <small class="text-muted">Đơn cũ</small>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php
                                            }
                                        } else {
                                        ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">
                                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                                <p>Chưa có đơn thuốc nào. Nhấn "Tạo đơn thuốc mới" để bắt đầu.</p>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
        </div>
        </section>
    <?php } ?>

    
    <?php if ($page === 'documents') { ?>
        <section class="content-section">
            <div class="section-header">
                <h2 class="section-title">Upload tài liệu y tế</h2>
                <p class="section-subtitle">Quản lý tài liệu y tế của bệnh nhân</p>
            </div>

            <div class="data-card">
                <h5 class="mb-3"><i class="fas fa-file-upload"></i> Upload tài liệu mới</h5>
                <form method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Bệnh nhân *</label>
                                <select name="pid" class="form-control" required>
                                    <option value="">Chọn bệnh nhân</option>
                                    <?php
                                    $stmt = $pdo->prepare("SELECT DISTINCT pid, CONCAT(fname, ' ', lname) as pname, email as pemail FROM appointmenttb WHERE TRIM(doctor) = TRIM(:doctor)");
                                    $stmt->execute([':doctor' => $doctor_fullname]);
                                    while ($patient = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<option value='{$patient['pid']}'>{$patient['pname']} ({$patient['pemail']})</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Lịch hẹn (Tùy chọn)</label>
                                <select name="appointment_id" class="form-control">
                                    <option value="">Không liên kết lịch hẹn</option>
                                    <?php
                                    $stmt = $pdo->prepare("SELECT ID, pid, CONCAT(fname, ' ', lname) as pname, appdate FROM appointmenttb WHERE TRIM(doctor) = TRIM(:doctor) ORDER BY appdate DESC");
                                    $stmt->execute([':doctor' => $doctor_fullname]);
                                    while ($appt = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<option value='{$appt['ID']}'>{$appt['pname']} - " . date('d/m/Y', strtotime($appt['appdate'])) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>File tài liệu *</label>
                                <input type="file" name="document_file" class="form-control" required accept=".pdf,.jpg,.jpeg,.png,.gif,.doc,.docx">
                                <small class="text-muted">PDF, hình ảnh, Word (Tối đa 10MB)</small>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Mô tả</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Mô tả tài liệu..."></textarea>
                    </div>
                    <button type="submit" name="upload_document" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Upload tài liệu
                    </button>
                </form>
            </div>

            <div class="data-card mt-4">
                <h5 class="mb-3"><i class="fas fa-file-medical"></i> Danh sách tài liệu</h5>
                <div class="data-table-container">
                    <table class="table table-hover data-table">
                        <thead>
                            <tr>
                                <th>Bệnh nhân</th>
                                <th>Tên file</th>
                                <th>Loại</th>
                                <th>Kích thước</th>
                                <th>Mô tả</th>
                                <th>Ngày upload</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $filter_appointment = isset($_GET['appointment']) ? $_GET['appointment'] : '';
                            $query = "SELECT md.*, p.fname, p.lname FROM medical_documents md 
                                              LEFT JOIN patreg p ON md.pid = p.pid 
                                              WHERE md.doctor = :doctor";
                            if ($filter_appointment) {
                                $query .= " AND md.appointment_id = :appointment_id";
                            }
                            $query .= " ORDER BY md.uploaded_at DESC";

                            $stmt = $pdo->prepare($query);
                            $params = [':doctor' => $doctor];
                            if ($filter_appointment) {
                                $params[':appointment_id'] = $filter_appointment;
                            }
                            $stmt->execute($params);

                            if ($stmt->rowCount() > 0) {
                                while ($doc = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    $file_size_mb = round($doc['file_size'] / (1024 * 1024), 2);
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($doc['fname'] . ' ' . $doc['lname']) . "</td>";
                                    echo "<td>" . htmlspecialchars($doc['document_name']) . "</td>";
                                    echo "<td><span class='badge badge-info'>" . strtoupper(pathinfo($doc['file_path'], PATHINFO_EXTENSION)) . "</span></td>";
                                    echo "<td>{$file_size_mb} MB</td>";
                                    echo "<td>" . htmlspecialchars($doc['description']) . "</td>";
                                    echo "<td>" . date('d/m/Y H:i', strtotime($doc['uploaded_at'])) . "</td>";
                                    echo "<td>
                                                    <a href='../../uploads/medical_documents/{$doc['file_path']}' target='_blank' class='btn btn-sm btn-primary' title='Xem'>
                                                        <i class='fas fa-eye'></i>
                                                    </a>
                                                  </td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='7' class='text-center text-muted'>Chưa có tài liệu nào được upload.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    <?php } ?>

    
    <?php if ($page === 'patient_history') { ?>
        <section class="content-section">
            <div class="section-header">
                <h2 class="section-title">Lịch sử bệnh án</h2>
                <p class="section-subtitle">Tìm kiếm và xem lịch sử điều trị của bệnh nhân</p>
            </div>

            <div class="data-card">
                <h5 class="mb-3"><i class="fas fa-search"></i> Tìm kiếm bệnh nhân</h5>
                <form method="GET" action="">
                    <input type="hidden" name="page" value="patient_history">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Chọn bệnh nhân</label>
                                <select name="search_pid" class="form-control">
                                    <option value="">-- Chọn bệnh nhân --</option>
                                    <?php
                                    $stmt = $pdo->prepare("SELECT DISTINCT pid, CONCAT(fname, ' ', lname) as pname, email as pemail FROM appointmenttb WHERE TRIM(doctor) = TRIM(:doctor) ORDER BY fname, lname");
                                    $stmt->execute([':doctor' => $doctor_fullname]);
                                    while ($patient = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        $selected = (isset($_GET['search_pid']) && $_GET['search_pid'] == $patient['pid']) ? 'selected' : '';
                                        echo "<option value='{$patient['pid']}' $selected>{$patient['pname']} ({$patient['pemail']})</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-search"></i> Tìm kiếm
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <?php if (isset($_GET['search_pid']) && !empty($_GET['search_pid'])) {
                            $search_pid = $_GET['search_pid'];

                            
                            $stmt = $pdo->prepare("SELECT * FROM patreg WHERE pid = :pid");
                            $stmt->execute([':pid' => $search_pid]);
                            $patient_info = $stmt->fetch(PDO::FETCH_ASSOC);

                            if ($patient_info) {
            ?>
                    <div class="data-card mt-4">
                        <h5 class="mb-3"><i class="fas fa-user"></i> Thông tin bệnh nhân</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <p><strong>Họ tên:</strong> <?php echo htmlspecialchars($patient_info['fname'] . ' ' . $patient_info['lname']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($patient_info['email']); ?></p>
                            </div>
                            <div class="col-md-4">
                                <p><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($patient_info['contact']); ?></p>
                                <p><strong>Giới tính:</strong> <?php echo htmlspecialchars($patient_info['gender']); ?></p>
                            </div>
                            <div class="col-md-4">
                                <p><strong>Ngày sinh:</strong> <?php echo date('d/m/Y', strtotime($patient_info['dob'])); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="data-card mt-4">
                        <h5 class="mb-3"><i class="fas fa-calendar-alt"></i> Lịch sử lịch hẹn</h5>
                        <div class="data-table-container">
                            <table class="table table-hover data-table">
                                <thead>
                                    <tr>
                                        <th>Ngày hẹn</th>
                                        <th>Giờ hẹn</th>
                                        <th>Triệu chứng</th>
                                        <th>Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $stmt = $pdo->prepare("SELECT * FROM appointmenttb WHERE pid = :pid AND TRIM(doctor) = TRIM(:doctor) ORDER BY appdate DESC");
                                    $stmt->execute([':pid' => $search_pid, ':doctor' => $doctor_fullname]);
                                    while ($appt = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        $status = ($appt['userStatus'] == 1 && $appt['doctorStatus'] == 1) ?
                                            '<span class="badge badge-success">Đang hoạt động</span>' :
                                            '<span class="badge badge-secondary">Đã hủy</span>';
                                        echo "<tr>";
                                        echo "<td>" . date('d/m/Y', strtotime($appt['appdate'])) . "</td>";
                                        echo "<td>" . htmlspecialchars($appt['apptime']) . "</td>";
                                        echo "<td>" . htmlspecialchars($appt['symptoms']) . "</td>";
                                        echo "<td>$status</td>";
                                        echo "</tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="data-card mt-4">
                        <h5 class="mb-3"><i class="fas fa-file-prescription"></i> Lịch sử đơn thuốc</h5>
                        <div class="data-table-container">
                            <table class="table table-hover data-table">
                                <thead>
                                    <tr>
                                        <th>Ngày kê đơn</th>
                                        <th>Bệnh</th>
                                        <th>Dị ứng</th>
                                        <th>Đơn thuốc</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $stmt = $pdo->prepare("SELECT * FROM prestb WHERE pid = :pid AND doctor = :doctor ORDER BY appdate DESC");
                                    $stmt->execute([':pid' => $search_pid, ':doctor' => $doctor_fullname]);
                                    while ($pres = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<tr>";
                                        echo "<td>" . date('d/m/Y', strtotime($pres['appdate'])) . "</td>";
                                        echo "<td>" . htmlspecialchars($pres['disease']) . "</td>";
                                        echo "<td>" . htmlspecialchars($pres['allergy']) . "</td>";
                                        echo "<td>" . htmlspecialchars($pres['prescription']) . "</td>";
                                        echo "</tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="data-card mt-4">
                        <h5 class="mb-3"><i class="fas fa-notes-medical"></i> Hồ sơ lâm sàng</h5>
                        <div class="data-table-container">
                            <table class="table table-hover data-table">
                                <thead>
                                    <tr>
                                        <th>Ngày khám</th>
                                        <th>Loại khám</th>
                                        <th>Chẩn đoán</th>
                                        <th>Triệu chứng</th>
                                        <th>Kế hoạch điều trị</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $stmt = $pdo->prepare("SELECT * FROM medical_records WHERE patient_id = :pid AND (doctor_id = :doc_id OR created_by = :doc_id) ORDER BY record_date DESC");
                                    $stmt->execute([':pid' => $search_pid, ':doc_id' => $doctor_id]);
                                    if ($stmt->rowCount() > 0) {
                                        while ($rec = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            $type_map = [
                                                'consultation' => 'Khám bệnh',
                                                'checkup' => 'Kiểm tra',
                                                'emergency' => 'Cấp cứu',
                                                'followup' => 'Tái khám',
                                                'surgery' => 'Phẫu thuật'
                                            ];
                                            $rec_type = $type_map[$rec['record_type']] ?? $rec['record_type'];

                                            echo "<tr>";
                                            echo "<td>" . date('d/m/Y', strtotime($rec['record_date'])) . "</td>";
                                            echo "<td><span class='badge badge-primary'>" . htmlspecialchars($rec_type) . "</span></td>";
                                            echo "<td>" . htmlspecialchars($rec['diagnosis']) . "</td>";
                                            echo "<td>" . htmlspecialchars($rec['symptoms']) . "</td>";
                                            echo "<td>" . htmlspecialchars($rec['treatment_plan']) . "</td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='5' class='text-center text-muted'>Chưa có hồ sơ lâm sàng.</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="data-card mt-4">
                        <h5 class="mb-3"><i class="fas fa-file-medical"></i> Tài liệu y tế</h5>
                        <div class="data-table-container">
                            <table class="table table-hover data-table">
                                <thead>
                                    <tr>
                                        <th>Tên file</th>
                                        <th>Loại</th>
                                        <th>Mô tả</th>
                                        <th>Ngày upload</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $stmt = $pdo->prepare("SELECT * FROM medical_documents WHERE pid = :pid AND doctor = :doctor ORDER BY uploaded_at DESC");
                                    $stmt->execute([':pid' => $search_pid, ':doctor' => $doctor]);
                                    if ($stmt->rowCount() > 0) {
                                        while ($doc = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            echo "<tr>";
                                            echo "<td>" . htmlspecialchars($doc['document_name']) . "</td>";
                                            echo "<td><span class='badge badge-info'>" . strtoupper(pathinfo($doc['file_path'], PATHINFO_EXTENSION)) . "</span></td>";
                                            echo "<td>" . htmlspecialchars($doc['description']) . "</td>";
                                            echo "<td>" . date('d/m/Y H:i', strtotime($doc['uploaded_at'])) . "</td>";
                                            echo "<td>
                                                    <a href='../../uploads/medical_documents/{$doc['file_path']}' target='_blank' class='btn btn-sm btn-primary'>
                                                        <i class='fas fa-eye'></i>
                                                    </a>
                                                  </td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='5' class='text-center text-muted'>Chưa có tài liệu nào.</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
            <?php
                            }
                        } ?>
        </section>
    <?php } ?>

    
    <?php if ($page === 'medicine_inventory') { ?>
        <section class="content-section">
            <div class="section-header">
                <h2 class="section-title">Quản lý kho thuốc</h2>
                <p class="section-subtitle">Quản lý kho thuốc và tồn kho</p>
            </div>

            <div class="data-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5><i class="fas fa-pills"></i> Danh sách thuốc</h5>
                    <button class="btn btn-primary" data-toggle="modal" data-target="#addMedicineModal">
                        <i class="fas fa-plus"></i> Thêm thuốc mới
                    </button>
                </div>
                <div class="data-table-container">
                    <table class="table table-hover data-table">
                        <thead>
                            <tr>
                                <th>Tên thuốc</th>
                                <th>Tên chung</th>
                                <th>Danh mục</th>
                                <th>Dạng thuốc</th>
                                <th>Hàm lượng</th>
                                <th>Số lượng</th>
                                <th>Đơn giá</th>
                                <th>Hạn dùng</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->query("SELECT * FROM medicines ORDER BY name ASC");
                            while ($medicine = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                $low_stock = $medicine['quantity'] < 10 ? 'text-danger font-weight-bold' : '';
                                $expired = (strtotime($medicine['expiry_date']) < time()) ? 'text-danger' : '';
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($medicine['name']) . "</td>";
                                echo "<td>" . htmlspecialchars($medicine['generic_name']) . "</td>";
                                echo "<td>" . htmlspecialchars($medicine['category']) . "</td>";
                                echo "<td>" . htmlspecialchars($medicine['dosage_form']) . "</td>";
                                echo "<td>" . htmlspecialchars($medicine['strength']) . "</td>";
                                echo "<td class='$low_stock'>" . $medicine['quantity'] . "</td>";
                                echo "<td>" . number_format($medicine['unit_price'], 0, ',', '.') . " VNĐ</td>";
                                echo "<td class='$expired'>" . date('d/m/Y', strtotime($medicine['expiry_date'])) . "</td>";
                                echo "<td>
                                                <button class='btn btn-sm btn-info' onclick='editMedicine(" . json_encode($medicine) . ")' title='Sửa'>
                                                    <i class='fas fa-edit'></i>
                                                </button>
                                                <button class='btn btn-sm btn-warning' onclick='updateStock({$medicine['id']}, \"{$medicine['name']}\")' title='Cập nhật kho'>
                                                    <i class='fas fa-box'></i>
                                                </button>
                                                <button class='btn btn-sm btn-danger' onclick='deleteMedicine({$medicine['id']}, \"{$medicine['name']}\")' title='Xóa'>
                                                    <i class='fas fa-trash'></i>
                                                </button>
                                              </td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    <?php } ?>
    </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('active');
            document.querySelector('.sidebar-overlay').classList.toggle('active');
        }
        document.querySelector('.sidebar-overlay').addEventListener('click', toggleSidebar);
    </script>

    
    <div class="modal fade" id="addMedicineModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-plus-circle"></i> Thêm thuốc mới</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tên thuốc *</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tên chung</label>
                                    <input type="text" name="generic_name" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Danh mục</label>
                                    <input type="text" name="category" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Dạng thuốc</label>
                                    <input type="text" name="dosage_form" class="form-control" placeholder="Viên, Siro, v.v.">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Hàm lượng</label>
                                    <input type="text" name="strength" class="form-control" placeholder="500mg, v.v.">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nhà sản xuất</label>
                                    <input type="text" name="manufacturer" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Số lượng *</label>
                                    <input type="number" name="quantity" class="form-control" min="0" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Đơn giá (VNĐ) *</label>
                                    <input type="number" name="unit_price" class="form-control" min="0" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Hạn dùng *</label>
                                    <input type="date" name="expiry_date" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Mô tả</label>
                                    <textarea name="description" class="form-control" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                        <button type="submit" name="add_medicine" class="btn btn-primary">
                            <i class="fas fa-save"></i> Lưu
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    
    <div class="modal fade" id="editMedicineModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="fas fa-edit"></i> Chỉnh sửa thuốc</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form method="POST">
                    <input type="hidden" name="medicine_id" id="edit_medicine_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tên thuốc *</label>
                                    <input type="text" name="name" id="edit_name" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tên chung</label>
                                    <input type="text" name="generic_name" id="edit_generic_name" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Danh mục</label>
                                    <input type="text" name="category" id="edit_category" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Dạng thuốc</label>
                                    <input type="text" name="dosage_form" id="edit_dosage_form" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Hàm lượng</label>
                                    <input type="text" name="strength" id="edit_strength" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nhà sản xuất</label>
                                    <input type="text" name="manufacturer" id="edit_manufacturer" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Số lượng *</label>
                                    <input type="number" name="quantity" id="edit_quantity" class="form-control" min="0" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Đơn giá (VNĐ) *</label>
                                    <input type="number" name="unit_price" id="edit_unit_price" class="form-control" min="0" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Hạn dùng *</label>
                                    <input type="date" name="expiry_date" id="edit_expiry_date" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Mô tả</label>
                                    <textarea name="description" id="edit_description" class="form-control" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                        <button type="submit" name="update_medicine" class="btn btn-info">
                            <i class="fas fa-save"></i> Cập nhật
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    
    <div class="modal fade" id="updateStockModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="fas fa-box"></i> Cập nhật kho</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form method="POST">
                    <input type="hidden" name="medicine_id" id="stock_medicine_id">
                    <div class="modal-body">
                        <p><strong>Thuốc:</strong> <span id="stock_medicine_name"></span></p>
                        <div class="form-group">
                            <label>Thay đổi số lượng *</label>
                            <input type="number" name="quantity_change" class="form-control"
                                placeholder="Nhập số dương để thêm, số âm để trừ" required>
                            <small class="text-muted">Ví dụ: +50 để thêm 50 đơn vị, -20 để trừ 20 đơn vị</small>
                        </div>
                        <div class="form-group">
                            <label>Lý do *</label>
                            <textarea name="reason" class="form-control" rows="2"
                                placeholder="Nhập khẩu, Xuất kho, v.v." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                        <button type="submit" name="update_stock" class="btn btn-warning">
                            <i class="fas fa-save"></i> Cập nhật
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function editMedicine(medicine) {
            document.getElementById('edit_medicine_id').value = medicine.id;
            document.getElementById('edit_name').value = medicine.name;
            document.getElementById('edit_generic_name').value = medicine.generic_name || '';
            document.getElementById('edit_category').value = medicine.category || '';
            document.getElementById('edit_dosage_form').value = medicine.dosage_form || '';
            document.getElementById('edit_strength').value = medicine.strength || '';
            document.getElementById('edit_manufacturer').value = medicine.manufacturer || '';
            document.getElementById('edit_quantity').value = medicine.quantity;
            document.getElementById('edit_unit_price').value = medicine.unit_price;
            document.getElementById('edit_expiry_date').value = medicine.expiry_date;
            document.getElementById('edit_description').value = medicine.description || '';
            $('#editMedicineModal').modal('show');
        }

        function updateStock(id, name) {
            document.getElementById('stock_medicine_id').value = id;
            document.getElementById('stock_medicine_name').textContent = name;
            $('#updateStockModal').modal('show');
        }

        function deleteMedicine(id, name) {
            if (confirm('Bạn có chắc chắn muốn xóa thuốc "' + name + '"?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = '<input type="hidden" name="medicine_id" value="' + id + '">' +
                    '<input type="hidden" name="delete_medicine" value="1">';
                document.body.appendChild(form);
                form.submit();
            }
        }
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
<?php } 
?>