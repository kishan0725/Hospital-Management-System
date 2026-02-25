<?php

error_reporting(0);
ini_set('display_errors', 0);

session_start();


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['view_patient_history'])) {
    header('Content-Type: application/json');
    ob_clean(); 

    try {
        require_once('../../config.php');

        $pid = intval($_POST['history_pid']);

        if (!isset($pdo)) {
            throw new Exception("Không thể kết nối database");
        }

        
        $patientStmt = $pdo->prepare("SELECT fname, lname, email FROM patreg WHERE pid = :pid");
        $patientStmt->execute([':pid' => $pid]);
        $patient = $patientStmt->fetch(PDO::FETCH_ASSOC);

        if (!$patient) {
            echo json_encode(['html' => '<div class="alert alert-warning text-center">Không tìm thấy thông tin bệnh nhân</div>']);
            exit();
        }

        
        $stmt = $pdo->prepare("
            SELECT mr.*, 
                   d.fullname as doctor_name,
                   d.spec as doctor_specialty,
                   COALESCE(s.name_vi, d.spec) as doctor_specialty_vi
            FROM medical_records mr
            LEFT JOIN doctb d ON mr.doctor_id = d.id
            LEFT JOIN specializations s ON d.spec_id = s.id
            WHERE mr.patient_id = :pid
            ORDER BY mr.record_date DESC, mr.created_at DESC
        ");
        $stmt->execute([':pid' => $pid]);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($records)) {
            $html = '<div class="alert alert-info text-center">Chưa có hồ sơ bệnh án nào cho bệnh nhân này</div>';
        } else {
            $html = '<div class="mb-3"><strong>Bệnh nhân:</strong> ' . htmlspecialchars($patient['fname'] . ' ' . $patient['lname']) . '</div>';
            $html .= '<div class="table-responsive">';
            $html .= '<table class="table table-bordered table-hover">';
            $html .= '<thead class="thead-light">';
            $html .= '<tr>';
            $html .= '<th style="width: 12%;">Ngày khám</th>';
            $html .= '<th style="width: 15%;">Bác sĩ</th>';
            $html .= '<th style="width: 13%;">Chuyên khoa</th>';
            $html .= '<th style="width: 30%;">Triệu chứng</th>';
            $html .= '<th style="width: 30%;">Chẩn đoán & Điều trị</th>';
            $html .= '</tr>';
            $html .= '</thead><tbody>';

            foreach ($records as $rec) {
                $html .= '<tr>';
                $html .= '<td style="color: #1f2937; font-weight: 500;">' . date('d/m/Y', strtotime($rec['record_date'])) . '</td>';
                $html .= '<td style="color: #1f2937; font-weight: 500;">' . htmlspecialchars($rec['doctor_name'] ?: 'N/A') . '</td>';
                $html .= '<td><span class="badge badge-info" style="background: linear-gradient(135deg, #3b82f6, #2563eb); padding: 0.4rem 0.8rem; font-weight: 600;">' . htmlspecialchars($rec['doctor_specialty_vi'] ?: 'N/A') . '</span></td>';

                
                $symptoms = $rec['symptoms'] ?: $rec['chief_complaint'] ?: 'Không ghi nhận';
                $html .= '<td><small>' . htmlspecialchars(substr($symptoms, 0, 150)) . '</small></td>';

                
                $treatment = '';
                if (!empty($rec['diagnosis'])) {
                    $treatment .= '<strong>Chẩn đoán:</strong> ' . htmlspecialchars(substr($rec['diagnosis'], 0, 100)) . '<br>';
                }
                if (!empty($rec['treatment_plan'])) {
                    $treatment .= '<strong>Điều trị:</strong> ' . htmlspecialchars(substr($rec['treatment_plan'], 0, 100));
                }
                if (!empty($rec['prescription'])) {
                    $treatment .= '<br><strong>Đơn thuốc:</strong> ' . htmlspecialchars(substr($rec['prescription'], 0, 80));
                }
                if (empty($treatment)) {
                    $treatment = '<span class="text-muted">Chưa có thông tin</span>';
                }
                $html .= '<td><small>' . $treatment . '</small></td>';
                $html .= '</tr>';
            }
            $html .= '</tbody></table></div>';
            $html .= '<div class="text-muted small mt-2">📋 Tổng cộng: <strong>' . count($records) . '</strong> hồ sơ bệnh án</div>';
        }

        echo json_encode(['html' => $html], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        echo json_encode(['html' => '<div class="alert alert-danger">Lỗi: ' . htmlspecialchars($e->getMessage()) . '</div>'], JSON_UNESCAPED_UNICODE);
    }
    exit();
}


require_once('../../config.php');
require_once('../../includes/messages.php');
require_once('../../includes/functions.php');

if (!isset($_SESSION['username']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../../pages/auth/login.php");
    exit();
}


$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$allowed_pages = array('dashboard', 'doctors', 'patients', 'appointments', 'prescriptions', 'queries', 'medical-records', 'manage_schedule');
if (!in_array($page, $allowed_pages)) {
    $page = 'dashboard';
}


if ($page === 'dashboard') {
    
    $patientsMonthlyQuery = "SELECT 
        DATE_FORMAT(first_appointment, '%Y-%m') as month,
        COUNT(*) as count
        FROM (
            SELECT pid, MIN(created_at) as first_appointment
            FROM appointmenttb
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY pid
        ) as new_patients
        GROUP BY DATE_FORMAT(first_appointment, '%Y-%m')
        ORDER BY month ASC";
    $patientsMonthlyStmt = $pdo->query($patientsMonthlyQuery);
    $patientsMonthlyData = $patientsMonthlyStmt->fetchAll(PDO::FETCH_ASSOC);

    
    $revenueMonthlyQuery = "SELECT 
        DATE_FORMAT(a.appdate, '%Y-%m') as month,
        SUM(CAST(d.docFees AS DECIMAL(10,2))) as revenue
        FROM appointmenttb a
        INNER JOIN doctb d ON a.doctor = d.id
        WHERE a.appdate >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        AND a.userStatus = 1 AND a.doctorStatus = 1
        GROUP BY DATE_FORMAT(a.appdate, '%Y-%m')
        ORDER BY month ASC";
    $revenueMonthlyStmt = $pdo->query($revenueMonthlyQuery);
    $revenueMonthlyData = $revenueMonthlyStmt->fetchAll(PDO::FETCH_ASSOC);

    
    $appointmentStatsQuery = "SELECT 
        SUM(CASE WHEN userStatus = 1 AND doctorStatus = 1 THEN 1 ELSE 0 END) as success,
        SUM(CASE WHEN userStatus = 0 OR doctorStatus = 0 THEN 1 ELSE 0 END) as cancelled,
        COUNT(*) as total
        FROM appointmenttb";
    $appointmentStatsStmt = $pdo->query($appointmentStatsQuery);
    $appointmentStats = $appointmentStatsStmt->fetch(PDO::FETCH_ASSOC);

    
    $topDoctorsQuery = "SELECT 
        d.fullname,
        COALESCE(s.name_vi, d.spec) as spec,
        COUNT(a.ID) as appointment_count,
        SUM(CASE WHEN a.userStatus = 1 AND a.doctorStatus = 1 THEN 1 ELSE 0 END) as success_count
        FROM doctb d
        LEFT JOIN appointmenttb a ON d.id = a.doctor
        LEFT JOIN specializations s ON d.spec_id = s.id
        GROUP BY d.id, d.fullname, d.spec, s.name_vi
        HAVING appointment_count > 0
        ORDER BY appointment_count DESC
        LIMIT 5";
    $topDoctorsStmt = $pdo->query($topDoctorsQuery);
    $topDoctors = $topDoctorsStmt->fetchAll(PDO::FETCH_ASSOC);
}


if (isset($_POST['docsub'])) {
    try {
        $fullname = $_POST['fullname'];
        $username = $_POST['username'];
        $dpassword = password_hash($_POST['dpassword'], PASSWORD_DEFAULT);
        $demail = $_POST['demail'];
        $spec_id = $_POST['special_id'];
        $docFees = $_POST['docFees'];

        
        $stmt_spec = $pdo->prepare("SELECT name FROM specializations WHERE id = ?");
        $stmt_spec->execute([$spec_id]);
        $spec_row = $stmt_spec->fetch();
        $spec_name = $spec_row ? $spec_row['name'] : '';

        $check = $pdo->prepare("SELECT * FROM doctb WHERE username = ? OR email = ?");
        $check->execute([$username, $demail]);
        if ($check->rowCount() > 0) {
            redirectWithMessage($_SERVER['PHP_SELF'] . '?page=doctors', 'error', 'Username or Email already exists!');
        } else {
            $stmt = $pdo->prepare("INSERT INTO doctb(username, fullname, password, email, spec, spec_id, docFees) VALUES(:username, :fullname, :dpassword, :demail, :spec, :spec_id, :docFees)");
            $stmt->execute([
                ':username' => $username,
                ':fullname' => $fullname,
                ':dpassword' => $dpassword,
                ':demail' => $demail,
                ':spec' => $spec_name,
                ':spec_id' => $spec_id,
                ':docFees' => $docFees
            ]);
            redirectWithMessage($_SERVER['PHP_SELF'] . '?page=doctors', 'success', 'Doctor added successfully!');
        }
    } catch (PDOException $e) {
        error_log("Add doctor error: " . $e->getMessage());
        redirectWithMessage($_SERVER['PHP_SELF'] . '?page=doctors', 'error', 'Error adding doctor: ' . $e->getMessage());
    }
}


if (isset($_POST['docsub1'])) {
    try {
        $demail = $_POST['demail'];
        $stmt = $pdo->prepare("DELETE FROM doctb WHERE email = :demail");
        $stmt->execute([':demail' => $demail]);
        redirectWithMessage($_SERVER['PHP_SELF'] . '?page=doctors', 'success', 'Doctor removed successfully!');
    } catch (PDOException $e) {
        error_log("Delete doctor error: " . $e->getMessage());
        redirectWithMessage($_SERVER['PHP_SELF'] . '?page=doctors', 'error', 'Error removing doctor!');
    }
}


if (isset($_POST['assign_schedule'])) {
    $doctor_id = $_POST['doctor_id'];
    $days = isset($_POST['day_of_week']) ? $_POST['day_of_week'] : [];
    $start = $_POST['start_time'];
    $end = $_POST['end_time'];

    if (empty($days)) {
        redirectWithMessage($_SERVER['PHP_SELF'] . '?page=manage_schedule', 'error', 'Vui lòng chọn ít nhất một ngày!');
    } elseif (strtotime($end) <= strtotime($start)) {
        redirectWithMessage($_SERVER['PHP_SELF'] . '?page=manage_schedule', 'error', 'Giờ kết thúc phải sau giờ bắt đầu!');
    } else {
        $successCount = 0;
        foreach ($days as $day_num) {
            
            $delete_stmt = $pdo->prepare("DELETE FROM doctor_schedules WHERE doctor_id = ? AND day_of_week = ?");
            $delete_stmt->execute([$doctor_id, $day_num]);
            
            
            $sql = "INSERT INTO doctor_schedules (doctor_id, day_of_week, start_time, end_time) VALUES (?,?,?,?)";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$doctor_id, $day_num, $start, $end])) {
                $successCount++;
            }
        }

        if ($successCount > 0) {
            redirectWithMessage($_SERVER['PHP_SELF'] . '?page=manage_schedule', 'success', "Đã cập nhật lịch thành công cho $successCount ngày.");
        } else {
            redirectWithMessage($_SERVER['PHP_SELF'] . '?page=manage_schedule', 'error', "Không thể cập nhật lịch. Vui lòng thử lại.");
        }
    }
}


if (isset($_POST['edit_schedule'])) {
    $schedule_id = $_POST['schedule_id'];
    $new_start = $_POST['new_start_time'];
    $new_end = $_POST['new_end_time'];
    
    if (strtotime($new_end) <= strtotime($new_start)) {
        redirectWithMessage($_SERVER['PHP_SELF'] . '?page=manage_schedule', 'error', 'Giờ kết thúc phải sau giờ bắt đầu!');
    } else {
        $stmt = $pdo->prepare("UPDATE doctor_schedules SET start_time = ?, end_time = ? WHERE id = ?");
        if ($stmt->execute([$new_start, $new_end, $schedule_id])) {
            redirectWithMessage($_SERVER['PHP_SELF'] . '?page=manage_schedule', 'success', 'Đã cập nhật khung giờ thành công.');
        } else {
            redirectWithMessage($_SERVER['PHP_SELF'] . '?page=manage_schedule', 'error', 'Không thể cập nhật khung giờ.');
        }
    }
}

if (isset($_GET['reset_schedule'])) {
    $doc_id = $_GET['reset_schedule'];

    
    $stmt_name = $pdo->prepare("SELECT fullname FROM doctb WHERE id = ?");
    $stmt_name->execute([$doc_id]);
    $doc_row = $stmt_name->fetch();

    if ($doc_row) {
        
        $stmt_ids = $pdo->prepare("SELECT id FROM doctb WHERE fullname = ?");
        $stmt_ids->execute([$doc_row['fullname']]);
        $all_ids = $stmt_ids->fetchAll(PDO::FETCH_COLUMN);

        
        if (!empty($all_ids)) {
            $placeholders = implode(',', array_fill(0, count($all_ids), '?'));
            $stmt = $pdo->prepare("DELETE FROM doctor_schedules WHERE doctor_id IN ($placeholders)");
            $stmt->execute($all_ids);
        }

        redirectWithMessage($_SERVER['PHP_SELF'] . '?page=manage_schedule', 'success', 'Đã xóa toàn bộ lịch của bác sĩ ' . $doc_row['fullname'] . '.');
    } else {
        redirectWithMessage($_SERVER['PHP_SELF'] . '?page=manage_schedule', 'error', 'Không tìm thấy bác sĩ.');
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="shortcut icon" type="image/x-icon" href="../../images/favicon.png" />
    <title>Bảng điều khiển Quản trị - Bệnh viện Global</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link rel="stylesheet" href="../../assets/css/custom/medical-theme.css">
    <link rel="stylesheet" href="../../assets/css/custom/global-improvements.css">
    <style>
        /* ==============================================
           ADMIN COLOR IMPROVEMENTS - Cải thiện màu chữ
           Chủ đạo màu trắng, tương phản tốt, dễ đọc
           ============================================== */
        
        /* Text colors - Màu chữ chính */
        body, .content-section, .section-header, .section-title {
            color: #ffffff !important;
        }
        
        /* Headers và Titles */
        h1, h2, h3, h4, h5, h6,
        .section-title, .data-table-title, .modal-title {
            color: #ffffff !important;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        /* Stats cards với text trắng */
        .stat-item .stat-label,
        .stat-item .stat-number {
            color: #ffffff !important;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3);
        }
        
        /* Table headers - Bold white text */
        .data-table thead th,
        .table thead th {
            color: #ffffff !important;
            font-weight: 700 !important;
            background: linear-gradient(135deg, #d2302c 0%, #8b0000 100%) !important;
        }
        
        /* Table body - Dark text on light background */
        .data-table tbody td,
        .table tbody td {
            color: #1f2937 !important;
            font-weight: 500;
            background: rgba(255, 255, 255, 0.95);
        }
        
        /* Table row hover */
        .data-table tbody tr:hover td,
        .table tbody tr:hover td {
            background: rgba(255, 248, 220, 0.95) !important;
            color: #111827 !important;
        }
        
        /* Form labels - Black/Dark for maximum contrast */
        label, .form-label {
            color: #000000 !important;
            font-weight: 700 !important;
            font-size: 1rem !important;
            display: block !important;
            margin-bottom: 8px !important;
            background: rgba(255, 255, 255, 0.25) !important;
            padding: 6px 10px !important;
            border-radius: 4px !important;
            letter-spacing: 0.3px;
        }
        
        /* Form inputs - Dark text on warm background matching admin theme */
        .form-control, .custom-select, input, textarea, select {
            color: #000000 !important;
            background-color: #fffbeb !important;
            border: 3px solid #d2302c !important;
            font-size: 1rem !important;
            font-weight: 500;
            transition: background-color 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease !important;
        }
        
        .form-control:focus, .custom-select:focus, input:focus, textarea:focus, select:focus {
            color: #000000 !important;
            background-color: #fffbeb !important;
            border-color: #ffd700 !important;
            box-shadow: 0 0 0 4px rgba(255, 215, 0, 0.4) !important;
            font-weight: 600;
        }

        /* Lock autofill background to same warm tone so no white flash */
        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus,
        input:-webkit-autofill:blur {
            -webkit-box-shadow: 0 0 0 1000px #fffbeb inset !important;
            box-shadow: 0 0 0 1000px #fffbeb inset !important;
            -webkit-text-fill-color: #000000 !important;
            transition: background-color 99999s ease-in-out 0s !important;
        }
        
        /* Placeholder text - clearly visible at rest, no need to click */
        .form-control::placeholder,
        input::placeholder,
        textarea::placeholder {
            color: #64748b !important;
            -webkit-text-fill-color: #64748b !important;
            opacity: 1 !important;
            font-weight: 400;
        }
        .form-control::-moz-placeholder,
        input::-moz-placeholder,
        textarea::-moz-placeholder {
            color: #64748b !important;
            opacity: 1 !important;
        }
        
        /* Card content */
        .card-body, .modal-body {
            color: #1f2937 !important;
        }
        
        /* Filter và Search bar */
        .custom-search-input, .custom-select-filter {
            background: #ffffff !important;
            color: #1f2937 !important;
            border: 2px solid rgba(210, 48, 44, 0.3);
            font-weight: 500;
        }
        
        /* Text muted - Light gray cho text phụ */
        .text-muted, small.text-muted {
            color: #e5e7eb !important;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
        }
        
        /* Badges - Tương phản tốt */
        .badge {
            font-weight: 700;
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
            text-shadow: none;
        }
        
        .badge-primary {
            background: linear-gradient(135deg, #3b82f6, #2563eb) !important;
            color: #ffffff !important;
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.4);
        }
        
        .badge-info {
            background: linear-gradient(135deg, #06b6d4, #0891b2) !important;
            color: #ffffff !important;
            box-shadow: 0 2px 8px rgba(6, 182, 212, 0.4);
        }
        
        .badge-success {
            background: linear-gradient(135deg, #10b981, #059669) !important;
            color: #ffffff !important;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.4);
        }
        
        .badge-danger {
            background: linear-gradient(135deg, #ef4444, #dc2626) !important;
            color: #ffffff !important;
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.4);
        }
        
        /* Buttons - High contrast */
        .btn {
            font-weight: 600 !important;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #d2302c, #8b0000) !important;
            border: none !important;
            color: #ffffff !important;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #ff4d4d, #d2302c) !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(210, 48, 44, 0.4);
        }
        
        /* Section headers */
        .section-header {
            background: linear-gradient(135deg, rgba(210, 48, 44, 0.9), rgba(139, 0, 0, 0.9));
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
        
        /* Stats section - White text */
        .quick-stats .stat-item {
            background: linear-gradient(135deg, rgba(210, 48, 44, 0.85), rgba(139, 0, 0, 0.85));
            color: #ffffff;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
        
        .quick-stats .stat-item:hover {
            background: linear-gradient(135deg, rgba(255, 77, 77, 0.9), rgba(210, 48, 44, 0.9));
            transform: translateY(-5px);
            box-shadow: 0 8px 24px rgba(210, 48, 44, 0.3);
        }
        
        /* Alert boxes */
        .alert {
            font-weight: 500;
            border-left: 4px solid;
        }
        
        .alert-success {
            background: rgba(209, 250, 229, 0.95) !important;
            color: #065f46 !important;
            border-color: #10b981;
        }
        
        .alert-danger {
            background: rgba(254, 226, 226, 0.95) !important;
            color: #991b1b !important;
            border-color: #ef4444;
        }
        
        .alert-info {
            background: rgba(219, 234, 254, 0.95) !important;
            color: #1e40af !important;
            border-color: #3b82f6;
        }
        
        /* Modal improvements */
        .modal-header {
            background: linear-gradient(135deg, #d2302c, #8b0000);
            color: #ffffff !important;
        }
        
        .modal-header .modal-title {
            color: #ffffff !important;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3);
        }
        
        .modal-header .close {
            color: #ffffff !important;
            opacity: 0.9;
            text-shadow: none;
        }
        
        /* Paragraph và text content */
        p, span, div {
            color: inherit;
        }
        
        /* Links */
        a {
            color: #fbbf24;
            font-weight: 600;
        }
        
        a:hover {
            color: #fcd34d;
            text-decoration: underline;
        }
        
        /* ============================================== */

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

        /* CSS cho nút chọn ngày (Week Selector) */
        .weekDays-selector {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }

        .weekDays-selector input[type=checkbox] {
            display: none !important;
        }

        .weekDays-selector input[type=checkbox]+label {
            display: inline-block;
            border-radius: 20px;
            background: #f1f5f9;
            color: #64748b;
            padding: 8px 15px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border: 1px solid #e2e8f0;
            transition: all 0.2s ease;
            user-select: none;
            margin-bottom: 0;
        }

        .weekDays-selector input[type=checkbox]:hover+label {
            background: #e2e8f0;
        }

        .weekDays-selector input[type=checkbox]:checked+label {
            background: #d2302c;
            color: #ffffff;
            border-color: #d2302c;
            box-shadow: 0 2px 5px rgba(210, 48, 44, 0.3);
            transform: translateY(-1px);
        }

        /* CSS cho bảng lịch (Matrix) - Cải thiện */
        .table-schedule {
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .table-schedule thead {
            background: linear-gradient(135deg, #d2302c 0%, #8b0000 100%);
            box-shadow: 0 2px 10px rgba(210, 48, 44, 0.3);
        }

        .table-schedule th {
            text-align: center;
            vertical-align: middle !important;
            color: white;
            font-weight: 700;
            font-size: 0.85rem;
            padding: 1rem 0.5rem !important;
            border: none;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        }

        .table-schedule th:first-child {
            border-top-left-radius: 12px;
        }

        .table-schedule th:last-child {
            border-top-right-radius: 12px;
        }

        .table-schedule tbody tr {
            transition: all 0.3s ease;
            background: white;
            border-left: 3px solid transparent;
        }

        .table-schedule tbody tr:hover {
            background: linear-gradient(135deg, rgba(210, 48, 44, 0.05), rgba(139, 0, 0, 0.05));
            border-left: 3px solid #d2302c;
            box-shadow: 0 3px 12px rgba(210, 48, 44, 0.15);
            transform: translateX(2px);
        }

        .table-schedule tbody tr:nth-child(even) {
            background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
        }

        .table-schedule tbody tr:nth-child(even):hover {
            background: linear-gradient(135deg, rgba(210, 48, 44, 0.08), rgba(139, 0, 0, 0.08));
        }

        .table-schedule td {
            text-align: center;
            vertical-align: middle !important;
            padding: 1rem 0.5rem !important;
            font-size: 0.8rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .table-schedule tbody tr:last-child td {
            border-bottom: none;
        }

        .table-schedule .doctor-name-col {
            font-weight: 700;
            color: #1e293b;
            text-align: left;
            padding-left: 1.2rem !important;
            font-size: 0.9rem;
        }

        .table-schedule .spec-col {
            color: #64748b;
            font-size: 0.75rem;
            text-align: left;
            font-style: italic;
            padding-left: 1rem !important;
        }

        .check-icon {
            color: #10b981;
            font-size: 1.3rem;
            cursor: help;
            filter: drop-shadow(0 2px 3px rgba(16, 185, 129, 0.4));
            animation: checkBounce 0.5s ease;
        }

        @keyframes checkBounce {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }
        }

        .cross-icon {
            color: #d1d5db;
            font-size: 0.95rem;
            opacity: 0.4;
        }

        .table-schedule small {
            display: block;
            margin-top: 0.25rem;
            font-size: 0.7rem;
            font-weight: 700;
            color: #059669;
            letter-spacing: 0.3px;
        }

        .table-schedule .btn-outline-danger {
            border-radius: 6px;
            transition: all 0.3s;
        }

        .table-schedule .btn-outline-danger:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        .saturday-alert {
            border-left: 5px solid #f59e0b;
            background-color: #fffbeb;
            color: #92400e;
        }

        .search-card {
            background: #fff;
            padding: 12px;
            border-radius: 8px;
            box-shadow: 0 3px 5px -1px rgba(0, 0, 0, 0.1), 0 2px 3px -1px rgba(0, 0, 0, 0.06);
            margin-bottom: 16px;
            border: 1px solid #e5e7eb;
        }

        .custom-search-input {
            border-radius: 50px;
            padding-left: 40px;
            border: 1px solid #d1d5db;
            transition: all 0.3s;
        }

        .custom-search-input:focus {
            box-shadow: 0 0 0 3px rgba(210, 48, 44, 0.2);
            border-color: #d2302c;
        }

        .search-icon-overlay {
            position: absolute;
            left: 6px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            z-index: 10;
        }

        .custom-select-filter {
            border-radius: 50px;
            border: 1px solid #d1d5db;
            cursor: pointer;
        }

        /* ===============================
   FIX MODAL MEDICAL RECORDS
   =============================== */

        body.page-medical-records.modal-open {
            overflow: auto !important;
            padding-right: 0 !important;
        }

        /* ===============================
   MODAL SIDEBAR RIGHT - CENTERED
   =============================== */

        .page-medical-records .modal.modal-right .modal-dialog {
            position: fixed;
            left: 50%;
            top: 50%;
            height: auto;
            max-height: 90vh;
            width: 800px;
            max-width: 90%;
            margin: 0;
            transform: translate(-50%, -50%);
            transition: transform 0.3s ease-in-out, opacity 0.3s ease-in-out;
        }

        .page-medical-records .modal.modal-right.show .modal-dialog {
            transform: translate(-50%, -50%);
            opacity: 1;
        }

        .page-medical-records .modal.modal-right .modal-content {
            height: auto;
            max-height: 90vh;
            overflow-y: auto;
            border-radius: 8px;
            border: none;
            box-shadow: 0 5px 30px rgba(0, 0, 0, 0.3);
        }

        /* ===============================
   CLOSE BUTTON
   =============================== */

        .page-medical-records .modal-header .close {
            margin-left: auto;
            padding: 1.25rem;
            opacity: 0.7;
        }

        .page-medical-records .modal-header .close:hover {
            opacity: 1;
        }

        sidebar {
            position: relative;
            z-index: 1100 !important;
        }

        /* main content thấp hơn sidebar */
        .main-content {
            position: relative;
            z-index: 1000;
            padding: 1rem;
        }

        /* ===============================
   FIX: KHÔNG BỊ BACKDROP ĐÈ LÊN MODAL (KHÔNG CLICK ĐƯỢC)
   =============================== */

        /* Backdrop PHẢI nằm dưới modal và KHÔNG chặn click */
        .page-medical-records .modal-backdrop {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            height: 100% !important;
            z-index: 1040 !important;
            background-color: transparent !important;
            pointer-events: none !important;
        }

        /* Modal luôn nằm trên backdrop và CHO PHÉP click */
        .page-medical-records .modal {
            position: fixed !important;
            z-index: 1050 !important;
            pointer-events: auto !important;
        }

        .page-medical-records .modal-dialog {
            position: relative !important;
            z-index: 1051 !important;
            pointer-events: auto !important;
        }

        .page-medical-records .modal-content {
            position: relative !important;
            z-index: 1052 !important;
            pointer-events: auto !important;
        }

        /* Nếu có backdrop bị nhân đôi -> ẩn cái thứ 2 trở đi */
        .page-medical-records .modal-backdrop+.modal-backdrop {
            display: none !important;
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

<body class="page-medical-records">
    
    <div class="petals-container" id="petals"></div>
    <script>
        function createPetals() {
            const petalsContainer = document.getElementById('petals');
            const numberOfPetals = 25;
            for (let i = 0; i < numberOfPetals; i++) {
                const petal = document.createElement('div');
                petal.className = 'petal';
                petal.style.left = Math.random() * 100 + '%';
                petal.style.animationDelay = Math.random() * 10 + 's';
                petal.style.animationDuration = (8 + Math.random() * 10) + 's';
                petalsContainer.appendChild(petal);
            }
        }
        window.addEventListener('load', createPetals);
    </script>
    <?php displayMessage(); ?>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo"><i class="fas fa-user-shield"></i></div>
                <div>
                    <h1 class="sidebar-title">Bệnh viện Global</h1>
                    <div class="sidebar-subtitle">Cổng Quản trị</div>
                </div>
            </div>

            <ul class="sidebar-menu">
                <li class="sidebar-menu-item">
                    <a href="?page=dashboard" class="sidebar-menu-link <?php echo ($page === 'dashboard') ? 'active' : ''; ?>">
                        <i class="fas fa-th-large sidebar-menu-icon"></i>
                        <span>Bảng điều khiển</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="?page=doctors" class="sidebar-menu-link <?php echo ($page === 'doctors') ? 'active' : ''; ?>">
                        <i class="fas fa-user-md sidebar-menu-icon"></i>
                        <span>Danh sách bác sĩ</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="?page=manage_schedule" class="sidebar-menu-link <?php echo ($page === 'manage_schedule') ? 'active' : ''; ?>">
                        <i class="fas fa-calendar-week sidebar-menu-icon"></i>
                        <span>Sắp xếp lịch bác sĩ</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="?page=patients" class="sidebar-menu-link <?php echo ($page === 'patients') ? 'active' : ''; ?>">
                        <i class="fas fa-users sidebar-menu-icon"></i>
                        <span>Danh sách bệnh nhân</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="?page=appointments" class="sidebar-menu-link <?php echo ($page === 'appointments') ? 'active' : ''; ?>">
                        <i class="fas fa-calendar-alt sidebar-menu-icon"></i>
                        <span>Lịch hẹn</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="?page=medical-records" class="sidebar-menu-link <?php echo ($page === 'medical-records') ? 'active' : ''; ?>">
                        <i class="fas fa-file-medical sidebar-menu-icon"></i>
                        <span>Hồ sơ bệnh án</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="?page=prescriptions" class="sidebar-menu-link <?php echo ($page === 'prescriptions') ? 'active' : ''; ?>">
                        <i class="fas fa-file-prescription sidebar-menu-icon"></i>
                        <span>Đơn thuốc</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="medicine-inventory.php" class="sidebar-menu-link">
                        <i class="fas fa-pills sidebar-menu-icon"></i>
                        <span>Quản lý Kho thuốc</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="?page=queries" class="sidebar-menu-link <?php echo ($page === 'queries') ? 'active' : ''; ?>">
                        <i class="fas fa-comments sidebar-menu-icon"></i>
                        <span>Liên hệ</span>
                    </a>
                </li>
            </ul>
        </aside>

        <main class="main-content">
            <nav class="top-navbar">
                <div class="navbar-left">
                    <h1 class="navbar-title">Bảng điều khiển Quản trị</h1>
                </div>
                <div class="navbar-right">
                    <div class="navbar-user dropdown">
                        <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" id="navbarUserDropdown" data-toggle="dropdown">
                            <div class="navbar-user-avatar"><i class="fas fa-user-shield"></i></div>
                            <div class="navbar-user-info">
                                <div class="navbar-user-name">Quản trị viên</div>
                                <div class="navbar-user-role">Lễ tân</div>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="../../index.php"><i class="fas fa-home mr-2"></i> Quay về trang chủ</a>
                        </div>
                    </div>
                </div>
            </nav>

            <?php if ($page === 'dashboard') { ?>
                <section class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Chào mừng, Quản trị viên!</h2>
                        <p class="section-subtitle">Quản lý bác sĩ, bệnh nhân và lịch hẹn</p>
                    </div>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon primary"><i class="fas fa-user-md"></i></div>
                            <div class="stat-content">
                                <div class="stat-label">Tổng số bác sĩ</div>
                                <div class="stat-value"><?php $query = $pdo->query("select count(*) as total from doctb");
                                                        echo $query->fetch(PDO::FETCH_ASSOC)['total']; ?></div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon success"><i class="fas fa-users"></i></div>
                            <div class="stat-content">
                                <div class="stat-label">Tổng số bệnh nhân</div>
                                <div class="stat-value"><?php $query = $pdo->query("select count(*) as total from patreg");
                                                        echo $query->fetch(PDO::FETCH_ASSOC)['total']; ?></div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon warning"><i class="fas fa-calendar-check"></i></div>
                            <div class="stat-content">
                                <div class="stat-label">Tổng số lịch hẹn</div>
                                <div class="stat-value"><?php $query = $pdo->query("select count(*) as total from appointmenttb");
                                                        echo $query->fetch(PDO::FETCH_ASSOC)['total']; ?></div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon danger"><i class="fas fa-file-medical"></i></div>
                            <div class="stat-content">
                                <div class="stat-label">Tổng số đơn thuốc</div>
                                <div class="stat-value"><?php $query = $pdo->query("select count(*) as total from prestb");
                                                        echo $query->fetch(PDO::FETCH_ASSOC)['total']; ?></div>
                            </div>
                        </div>
                    </div>

                    
                    <div class="row mt-4">
                        
                        <div class="col-lg-6 mb-4">
                            <div class="data-table-container">
                                <div class="data-table-header">
                                    <h3 class="data-table-title"><i class="fas fa-user-plus"></i> Bệnh nhân mới theo tháng</h3>
                                </div>
                                <div class="p-4">
                                    <canvas id="patientsChart" style="max-height: 300px;"></canvas>
                                </div>
                            </div>
                        </div>

                        
                        <div class="col-lg-6 mb-4">
                            <div class="data-table-container">
                                <div class="data-table-header">
                                    <h3 class="data-table-title"><i class="fas fa-money-bill-wave"></i> Doanh thu theo tháng</h3>
                                </div>
                                <div class="p-4">
                                    <canvas id="revenueChart" style="max-height: 300px;"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        
                        <div class="col-lg-6 mb-4">
                            <div class="data-table-container">
                                <div class="data-table-header">
                                    <h3 class="data-table-title"><i class="fas fa-chart-pie"></i> Tỷ lệ lịch khám</h3>
                                </div>
                                <div class="p-4">
                                    <div class="row text-center mb-3">
                                        <div class="col-md-4">
                                            <div class="stat-mini success">
                                                <div class="stat-mini-icon"><i class="fas fa-check-circle"></i></div>
                                                <div class="stat-mini-value"><?php echo number_format($appointmentStats['success']); ?></div>
                                                <div class="stat-mini-label">Thành công</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="stat-mini danger">
                                                <div class="stat-mini-icon"><i class="fas fa-times-circle"></i></div>
                                                <div class="stat-mini-value"><?php echo number_format($appointmentStats['cancelled']); ?></div>
                                                <div class="stat-mini-label">Đã hủy</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="stat-mini info">
                                                <div class="stat-mini-icon"><i class="fas fa-calendar-check"></i></div>
                                                <div class="stat-mini-value"><?php echo number_format($appointmentStats['total']); ?></div>
                                                <div class="stat-mini-label">Tổng số</div>
                                            </div>
                                        </div>
                                    </div>
                                    <canvas id="appointmentChart" style="max-height: 250px;"></canvas>
                                </div>
                            </div>
                        </div>

                        
                        <div class="col-lg-6 mb-4">
                            <div class="data-table-container">
                                <div class="data-table-header">
                                    <h3 class="data-table-title"><i class="fas fa-trophy"></i> Top bác sĩ có nhiều lịch khám nhất</h3>
                                </div>
                                <div class="p-4">
                                    <div class="top-doctors-list">
                                        <?php
                                        if (empty($topDoctors) || count($topDoctors) == 0):
                                        ?>
                                            <div class="text-center py-4">
                                                <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                                                <p class="text-muted">Chưa có dữ liệu lịch khám để xếp hạng bác sĩ</p>
                                            </div>
                                            <?php
                                        else:
                                            $rank = 1;
                                            foreach ($topDoctors as $doctor):
                                                $successRate = $doctor['appointment_count'] > 0 ?
                                                    round(($doctor['success_count'] / $doctor['appointment_count']) * 100, 1) : 0;
                                            ?>
                                                <div class="top-doctor-item rank-<?php echo $rank; ?>">
                                                    <div class="doctor-rank">
                                                        <span class="rank-number"><?php echo $rank; ?></span>
                                                        <?php if ($rank === 1): ?>
                                                            <i class="fas fa-crown rank-icon"></i>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="doctor-info">
                                                        <div class="doctor-name"><?php echo htmlspecialchars($doctor['fullname']); ?></div>
                                                        <div class="doctor-spec"><?php echo htmlspecialchars($doctor['spec']); ?></div>
                                                    </div>
                                                    <div class="doctor-stats">
                                                        <div class="stat-item">
                                                            <i class="fas fa-calendar-check"></i>
                                                            <span><?php echo number_format($doctor['appointment_count']); ?> lịch</span>
                                                        </div>
                                                        <div class="stat-item success-rate">
                                                            <i class="fas fa-check-circle"></i>
                                                            <span><?php echo $successRate; ?>%</span>
                                                        </div>
                                                    </div>
                                                </div>
                                        <?php
                                                $rank++;
                                            endforeach;
                                        endif;
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            <?php } ?>

            <?php if ($page === 'doctors') { ?>
                <section class="content-section">
                    <div class="section-header">
                        <h2 class="section-title"><i class="fas fa-user-md"></i> Quản lý bác sĩ</h2>
                    </div>

                    <div class="data-table-container mb-4">
                        <div class="data-table-header" style="background: linear-gradient(135deg, #d2302c 0%, #8b0000 100%);">
                            <h3 class="data-table-title" style="color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);"><i class="fas fa-user-plus"></i> Thêm bác sĩ mới</h3>
                        </div>
                        <div class="p-4" style="background: linear-gradient(135deg, rgba(254, 243, 199, 0.9) 0%, rgba(254, 215, 170, 0.9) 100%);">
                            <form method="post" action="?page=doctors">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group"><label style="color: #000000; background: rgba(255, 255, 255, 0.6); padding: 8px 12px; border-radius: 6px;"><i class="fas fa-user-circle" style="color: #d2302c; margin-right: 5px;"></i>Họ tên bác sĩ *</label><input type="text" class="form-control" name="fullname" required style="border: 3px solid #d2302c; color: #000000; font-weight: 500;"></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group"><label style="color: #000000; background: rgba(255, 255, 255, 0.6); padding: 8px 12px; border-radius: 6px;"><i class="fas fa-user-tag" style="color: #d2302c; margin-right: 5px;"></i>Tên đăng nhập *</label><input type="text" class="form-control" name="username" required style="border: 3px solid #d2302c; color: #000000; font-weight: 500;"></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group"><label style="color: #000000; background: rgba(255, 255, 255, 0.6); padding: 8px 12px; border-radius: 6px;"><i class="fas fa-stethoscope" style="color: #d2302c; margin-right: 5px;"></i>Chuyên khoa *</label><select name="special_id" class="form-control" required style="border: 3px solid #d2302c; color: #000000; font-weight: 500;">
                                                <option value="">-- Chọn chuyên khoa --</option>
                                                <?php
                                                $specList = $pdo->query("SELECT id, name, name_vi FROM specializations WHERE status = 1 ORDER BY name_vi ASC")->fetchAll();
                                                foreach ($specList as $spec) {
                                                    echo '<option value="' . htmlspecialchars($spec['id']) . '">' . htmlspecialchars($spec['name_vi']) . '</option>';
                                                }
                                                ?>
                                            </select></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group"><label style="color: #000000; background: rgba(255, 255, 255, 0.6); padding: 8px 12px; border-radius: 6px;"><i class="fas fa-envelope" style="color: #d2302c; margin-right: 5px;"></i>Email *</label><input type="email" class="form-control" name="demail" required style="border: 3px solid #d2302c; color: #000000; font-weight: 500;"></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group"><label style="color: #000000; background: rgba(255, 255, 255, 0.6); padding: 8px 12px; border-radius: 6px;"><i class="fas fa-dollar-sign" style="color: #d2302c; margin-right: 5px;"></i>Phí khám *</label><input type="number" class="form-control" name="docFees" min="0" required style="border: 3px solid #d2302c; color: #000000; font-weight: 500;"></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group"><label style="color: #000000; background: rgba(255, 255, 255, 0.6); padding: 8px 12px; border-radius: 6px;"><i class="fas fa-lock" style="color: #d2302c; margin-right: 5px;"></i>Mật khẩu *</label><input type="password" class="form-control" name="dpassword" required style="border: 3px solid #d2302c; color: #000000; font-weight: 500;"></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group"><label style="color: #000000; background: rgba(255, 255, 255, 0.6); padding: 8px 12px; border-radius: 6px;"><i class="fas fa-lock" style="color: #d2302c; margin-right: 5px;"></i>Xác nhận mật khẩu *</label><input type="password" class="form-control" name="cdpassword" required style="border: 3px solid #d2302c; color: #000000; font-weight: 500;"></div>
                                    </div>
                                    <div class="col-md-12"><button type="submit" name="docsub" class="btn btn-lg" style="background: linear-gradient(135deg, #d2302c 0%, #8b0000 100%); color: #ffffff; border: none; border-radius: 8px; padding: 14px 32px; font-weight: 700; font-size: 1rem; box-shadow: 0 4px 15px rgba(210, 48, 44, 0.4); transition: all 0.3s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 25px rgba(210, 48, 44, 0.6)';"><i class="fas fa-check-circle"></i> Thêm bác sĩ</button></div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="search-card">
                        <div class="row align-items-center">
                            <div class="col-md-6 mb-2 mb-md-0">
                                <div class="position-relative">
                                    <i class="fas fa-search search-icon-overlay"></i>
                                    <input type="text" id="live_search" class="form-control custom-search-input" placeholder="Nhập tên bác sĩ hoặc email để tìm...">
                                </div>
                            </div>
                            <div class="col-md-4 mb-2 mb-md-0">
                                <select id="filter_spec" class="form-control custom-select-filter">
                                    <option value="">-- Tất cả chuyên khoa --</option>
                                    <?php
                                    
                                    $specs_query = $pdo->query("SELECT name, name_vi FROM specializations WHERE status = 1 ORDER BY name_vi ASC");
                                    while ($spec_row = $specs_query->fetch(PDO::FETCH_ASSOC)) {
                                        echo '<option value="' . htmlspecialchars($spec_row['name_vi']) . '">' . htmlspecialchars($spec_row['name_vi']) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-2 text-right">
                                <span class="text-muted small"><i class="fas fa-spinner fa-spin" style="display:none;" id="loading_spinner"></i></span>
                            </div>
                        </div>
                    </div>

                    <div class="data-table-container">
                        <div class="data-table-header">
                            <h3 class="data-table-title">Danh sách bác sĩ</h3>
                        </div>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Tên bác sĩ</th>
                                    <th>Chuyên khoa</th>
                                    <th>Email</th>
                                    <th>Phí khám</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody id="doctor_table_body">
                                <?php
                                
                                $query = "SELECT d.*, s.name_vi FROM doctb d LEFT JOIN specializations s ON d.spec_id = s.id ORDER BY d.fullname ASC";
                                $result = $pdo->query($query);
                                $serial = 1;
                                if ($result->rowCount() > 0) {
                                    while ($row = $result->fetch(PDO::FETCH_ASSOC)) { ?>
                                        <tr>
                                            <td><?php echo $serial++; ?></td>
                                            <td><strong>BS. <?php echo htmlspecialchars($row['fullname'] ?? $row['username']); ?></strong></td>
                                            <td><span class="badge" style="background: linear-gradient(135deg, #d2302c, #8b0000); color: #ffffff; padding: 0.5rem 0.8rem; font-weight: 700;"><?php echo htmlspecialchars($row['name_vi'] ?? $row['spec']); ?></span></td>
                                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                                            <td><strong style="color: #d2302c;">₹<?php echo htmlspecialchars($row['docFees']); ?></strong></td>
                                            <td>
                                                <form method="post" action="?page=doctors" style="display:inline" onsubmit="return confirm('Bạn có chắc muốn xóa?');">
                                                    <input type="hidden" name="demail" value="<?php echo htmlspecialchars($row['email']); ?>">
                                                    <button type="submit" name="docsub1" class="btn btn-sm" style="background: linear-gradient(135deg, #ef4444, #dc2626); color: #ffffff; border: none; border-radius: 6px; padding: 8px 14px; font-weight: 600; transition: all 0.3s; box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(239, 68, 68, 0.5)';"><i class="fas fa-trash-alt"></i> Xóa</button>
                                                </form>
                                            </td>
                                        </tr>
                                <?php }
                                } else {
                                    echo '<tr><td colspan="6" class="text-center">Không có dữ liệu</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        // 1. Khai báo các element cần thao tác
                        const searchInput = document.getElementById('live_search');
                        const filterSpec = document.getElementById('filter_spec');
                        const tableBody = document.getElementById('doctor_table_body');
                        const loadingSpinner = document.getElementById('loading_spinner');

                        // 2. Hàm xử lý Fetch dữ liệu
                        function load_data(query = '', spec = '') {
                            // Hiện icon loading
                            if (loadingSpinner) loadingSpinner.style.display = 'inline-block';

                            // Tạo dữ liệu gửi đi (dạng Form Data)
                            const formData = new FormData();
                            formData.append('search', query);
                            formData.append('spec', spec);

                            // Gọi Fetch API
                            fetch('ajax/get_doctors.php', {
                                    method: 'POST',
                                    body: formData
                                })
                                .then(response => {
                                    if (!response.ok) {
                                        throw new Error('Network response was not ok');
                                    }
                                    return response.text(); // Vì server trả về HTML table row
                                })
                                .then(data => {
                                    // Cập nhật bảng dữ liệu
                                    tableBody.innerHTML = data;
                                    // Ẩn icon loading
                                    if (loadingSpinner) loadingSpinner.style.display = 'none';
                                })
                                .catch(error => {
                                    console.error('Lỗi Fetch:', error);
                                    if (loadingSpinner) loadingSpinner.style.display = 'none';
                                });
                        }

                        // 3. Bắt sự kiện Gõ phím (Tìm theo tên)
                        if (searchInput) {
                            searchInput.addEventListener('keyup', function() {
                                const query = this.value;
                                const spec = filterSpec ? filterSpec.value : '';
                                load_data(query, spec);
                            });
                        }

                        // 4. Bắt sự kiện Đổi Select (Lọc theo khoa)
                        if (filterSpec) {
                            filterSpec.addEventListener('change', function() {
                                const query = searchInput ? searchInput.value : '';
                                const spec = this.value;
                                load_data(query, spec);
                            });
                        }
                    });
                </script>

            <?php } ?>

            <?php if ($page === 'manage_schedule') {
                $is_saturday = (date('N') == 6);

                
                $doctors = $pdo->query("SELECT MIN(id) as id, fullname FROM doctb GROUP BY fullname ORDER BY fullname")->fetchAll(PDO::FETCH_ASSOC);

                
                $sql_docs = "SELECT MIN(d.id) as id, d.fullname, 
                             (SELECT COALESCE(s.name_vi, '---') FROM specializations s WHERE s.id = d.spec_id LIMIT 1) as spec_name 
                             FROM doctb d 
                             GROUP BY d.fullname, d.spec_id
                             ORDER BY d.fullname";
                $all_docs = $pdo->query($sql_docs)->fetchAll(PDO::FETCH_ASSOC);

                
                $sql_sch = "SELECT id as schedule_id, doctor_id, day_of_week, start_time, end_time 
                           FROM doctor_schedules 
                           ORDER BY id DESC";
                $all_schedules = $pdo->query($sql_sch)->fetchAll(PDO::FETCH_ASSOC);

                
                $scheduleMap = [];
                
                $doctorNameMap = [];
                foreach ($doctors as $doc) {
                    $doctorNameMap[$doc['id']] = $doc['fullname'];
                }
                
                $allDoctorIds = $pdo->query("SELECT id, fullname FROM doctb")->fetchAll(PDO::FETCH_ASSOC);
                $fullnameToIds = [];
                foreach ($allDoctorIds as $d) {
                    $fullnameToIds[$d['fullname']][] = $d['id'];
                }

                
                foreach ($all_schedules as $sch) {
                    
                    foreach ($fullnameToIds as $fname => $ids) {
                        if (in_array($sch['doctor_id'], $ids)) {
                            
                            $mainId = min($ids);
                            
                            if (!isset($scheduleMap[$mainId][$sch['day_of_week']])) {
                                $scheduleMap[$mainId][$sch['day_of_week']] = [
                                    'time' => date('H:i', strtotime($sch['start_time'])) . ' - ' . date('H:i', strtotime($sch['end_time'])),
                                    'schedule_id' => $sch['schedule_id'],
                                    'start_time' => date('H:i', strtotime($sch['start_time'])),
                                    'end_time' => date('H:i', strtotime($sch['end_time']))
                                ];
                            }
                            break;
                        }
                    }
                }
                
                
                $specs_for_filter = $pdo->query("SELECT name, name_vi FROM specializations WHERE status = 1 ORDER BY name_vi ASC")->fetchAll(PDO::FETCH_ASSOC);

                $daysOfWeek = [1 => 'Thứ 2', 2 => 'Thứ 3', 3 => 'Thứ 4', 4 => 'Thứ 5', 5 => 'Thứ 6', 6 => 'Thứ 7', 0 => 'CN'];
            ?>

                <section class="content-section">
                    <div class="section-header">
                        <h2 class="section-title"><i class="fas fa-calendar-alt"></i> Phân công lịch trực</h2>
                    </div>
                    <?php if ($is_saturday): ?>
                        <div class="alert saturday-alert mb-4 shadow-sm animate__animated animate__fadeIn">
                            <div class="d-flex align-items-center"><i class="fas fa-bell fa-2x mr-3 animate__animated animate__swing animate__infinite"></i>
                                <div>
                                    <h5 class="mb-1 font-weight-bold">Nhắc nhở quan trọng!</h5>
                                    <p class="mb-0">Hôm nay là Thứ 7. Vui lòng kiểm tra và đăng ký lịch làm việc cho tuần tới.</p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>


                    <div class="card shadow-sm mb-5 border-0" style="border-radius: 12px; overflow: hidden;">
                        <div class="card-header py-3" style="background: linear-gradient(135deg, #d2302c 0%, #8b0000 100%); border: none;">
                            <h5 class="mb-0 text-white font-weight-bold" style="text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);"><i class="fas fa-calendar-plus"></i> Thêm lịch làm việc mới</h5>
                        </div>
                        <div class="card-body" style="background: linear-gradient(135deg, rgba(254, 243, 199, 0.9) 0%, rgba(254, 215, 170, 0.9) 100%);">
                            <form method="POST" id="scheduleForm">
                                <div class="row">
                                    <div class="col-lg-3 col-md-6 mb-3">
                                        <div class="form-group">
                                            <label class="font-weight-bold" style="color: #000000; background: rgba(255, 255, 255, 0.6); padding: 8px 12px; border-radius: 6px; display: block; margin-bottom: 10px;"><i class="fas fa-user-md" style="color: #d2302c;"></i> Chọn Bác sĩ</label>
                                            <select name="doctor_id" class="form-control" required style="border-radius: 8px; border: 3px solid #d2302c; color: #000000; font-weight: 500;">
                                                <option value="">-- Chọn bác sĩ --</option>
                                                <?php foreach ($doctors as $doc): ?>
                                                    <option value="<?php echo $doc['id']; ?>">BS. <?php echo $doc['fullname']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-5 col-md-12 mb-3">
                                        <div class="form-group">
                                            <label class="font-weight-bold" style="color: #000000; background: rgba(255, 255, 255, 0.6); padding: 8px 12px; border-radius: 6px; display: block; margin-bottom: 10px;"><i class="fas fa-calendar-week" style="color: #d2302c;"></i> Chọn ngày làm việc (có thể chọn nhiều)</label>
                                            <div class="weekDays-selector">
                                                <input type="checkbox" id="weekday-1" name="day_of_week[]" value="1"><label for="weekday-1">Thứ 2</label>
                                                <input type="checkbox" id="weekday-2" name="day_of_week[]" value="2"><label for="weekday-2">Thứ 3</label>
                                                <input type="checkbox" id="weekday-3" name="day_of_week[]" value="3"><label for="weekday-3">Thứ 4</label>
                                                <input type="checkbox" id="weekday-4" name="day_of_week[]" value="4"><label for="weekday-4">Thứ 5</label>
                                                <input type="checkbox" id="weekday-5" name="day_of_week[]" value="5"><label for="weekday-5">Thứ 6</label>
                                                <input type="checkbox" id="weekday-6" name="day_of_week[]" value="6"><label for="weekday-6">Thứ 7</label>
                                                <input type="checkbox" id="weekday-0" name="day_of_week[]" value="0"><label for="weekday-0">CN</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-md-6 mb-3">
                                        <label class="font-weight-bold" style="color: #000000; background: rgba(255, 255, 255, 0.6); padding: 8px 12px; border-radius: 6px; display: block; margin-bottom: 10px;"><i class="fas fa-clock" style="color: #d2302c;"></i> Khung giờ làm việc</label>
                                        <div class="row mb-2">
                                            <div class="col-6">
                                                <input type="time" name="start_time" class="form-control" required style="border-radius: 8px; border: 3px solid #d2302c; color: #000000; font-weight: 500;">
                                                <small class="d-block mt-1" style="color: #000000; font-weight: 600;"><i class="fas fa-arrow-right"></i> Giờ bắt đầu</small>
                                            </div>
                                            <div class="col-6">
                                                <input type="time" name="end_time" class="form-control" required style="border-radius: 8px; border: 3px solid #d2302c; color: #000000; font-weight: 500;">
                                                <small class="d-block mt-1" style="color: #000000; font-weight: 600;"><i class="fas fa-arrow-left"></i> Giờ kết thúc</small>
                                            </div>
                                        </div>
                                        <button type="submit" name="assign_schedule" class="btn btn-block mt-2" style="background: linear-gradient(135deg, #d2302c 0%, #8b0000 100%); color: white; border: none; border-radius: 8px; padding: 12px; font-weight: bold; box-shadow: 0 4px 15px rgba(210, 48, 44, 0.4); transition: all 0.3s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(210, 48, 44, 0.6)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(210, 48, 44, 0.4)';">
                                            <i class="fas fa-save"></i> Lưu lịch làm việc
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    
                    <div class="search-card">
                        <div class="row align-items-center">
                            <div class="col-md-6 mb-2 mb-md-0">
                                <div class="position-relative">
                                    <i class="fas fa-search search-icon-overlay"></i>
                                    <input type="text" id="matrix_search" class="form-control custom-search-input" placeholder="Nhập tên bác sĩ để lọc bảng...">
                                </div>
                            </div>
                            <div class="col-md-4 mb-2 mb-md-0">
                                <select id="matrix_spec" class="form-control custom-select-filter">
                                    <option value="">-- Tất cả chuyên khoa --</option>
                                    <?php foreach ($specs_for_filter as $spec_row): ?>
                                        <option value="<?php echo htmlspecialchars($spec_row['name_vi']); ?>"><?php echo htmlspecialchars($spec_row['name_vi']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2 text-right">
                                <span class="text-muted small"><i class="fas fa-spinner fa-spin" style="display:none;" id="matrix_loading"></i></span>
                            </div>
                        </div>
                    </div>

                    <div class="data-table-container shadow-sm bg-white rounded">
                        <div class="data-table-header border-bottom">
                            <h3 class="data-table-title">Bảng phân công nhân sự</h3>
                        </div>
                        <div class="table-responsive p-0">
                            <table class="table table-bordered table-striped table-hover table-schedule mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width: 20%;">Bác sĩ</th>
                                        <th style="width: 15%;">Chuyên ngành</th>
                                        <?php foreach ($daysOfWeek as $day): ?><th><?php echo $day; ?></th><?php endforeach; ?>
                                        <th style="width: 5%;">Xóa</th>
                                    </tr>
                                </thead>
                                <tbody id="matrix_body">
                                    <?php foreach ($all_docs as $doc): $docId = $doc['id']; ?>
                                        <tr>
                                            <td class="doctor-name-col"><?php echo $doc['fullname']; ?></td>
                                            <td class="spec-col"><?php echo $doc['spec_name'] ?? '---'; ?></td>
                                            <?php foreach ($daysOfWeek as $dayKey => $dayLabel): ?>
                                                <td>
                                                    <?php if (isset($scheduleMap[$docId][$dayKey])): 
                                                        $schData = $scheduleMap[$docId][$dayKey];
                                                    ?>
                                                        <i class="fas fa-check-circle check-icon" title="<?php echo $schData['time']; ?>"></i>
                                                        <br><small class="text-success font-weight-bold"><?php echo $schData['time']; ?></small>
                                                        <br><button type="button" class="btn btn-outline-primary btn-sm mt-1 btn-edit-schedule" 
                                                            data-schedule-id="<?php echo $schData['schedule_id']; ?>"
                                                            data-start-time="<?php echo $schData['start_time']; ?>"
                                                            data-end-time="<?php echo $schData['end_time']; ?>"
                                                            data-day="<?php echo $dayLabel; ?>"
                                                            data-doctor="<?php echo htmlspecialchars($doc['fullname']); ?>"
                                                            style="font-size: 0.65rem; padding: 2px 6px;">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                    <?php else: ?><i class="fas fa-times cross-icon"></i><?php endif; ?>
                                                </td>
                                            <?php endforeach; ?>
                                            <td><a href="?page=manage_schedule&reset_schedule=<?php echo $docId; ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Xóa hết lịch của BS này?');"><i class="fas fa-trash-alt"></i></a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                
                <div class="modal fade" id="editScheduleModal" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header" style="background: linear-gradient(135deg, #d2302c 0%, #8b0000 100%);">
                                <h5 class="modal-title text-white"><i class="fas fa-clock"></i> Chỉnh sửa khung giờ</h5>
                                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <form method="POST">
                                <div class="modal-body" style="background: linear-gradient(135deg, rgba(254, 243, 199, 0.9) 0%, rgba(254, 215, 170, 0.9) 100%);">
                                    <input type="hidden" name="schedule_id" id="edit_schedule_id">
                                    <div class="form-group">
                                        <label style="color: #000;"><strong>Bác sĩ:</strong></label>
                                        <p id="edit_doctor_name" class="font-weight-bold" style="color: #d2302c;"></p>
                                    </div>
                                    <div class="form-group">
                                        <label style="color: #000;"><strong>Ngày:</strong></label>
                                        <p id="edit_day_label" class="font-weight-bold" style="color: #d2302c;"></p>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label style="color: #000;"><i class="fas fa-play" style="color: #10b981;"></i> Giờ bắt đầu</label>
                                                <input type="time" name="new_start_time" id="edit_start_time" class="form-control" required style="border: 2px solid #d2302c;">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label style="color: #000;"><i class="fas fa-stop" style="color: #ef4444;"></i> Giờ kết thúc</label>
                                                <input type="time" name="new_end_time" id="edit_end_time" class="form-control" required style="border: 2px solid #d2302c;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                                    <button type="submit" name="edit_schedule" class="btn btn-primary" style="background: linear-gradient(135deg, #d2302c 0%, #8b0000 100%); border: none;">
                                        <i class="fas fa-save"></i> Lưu thay đổi
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <script>
                    // Xử lý tìm kiếm và lọc bằng JavaScript phía client
                    document.addEventListener('DOMContentLoaded', function() {
                        const mSearch = document.getElementById('matrix_search');
                        const mSpec = document.getElementById('matrix_spec');
                        const tableRows = document.querySelectorAll('#matrix_body tr');

                        function filterTable() {
                            const searchQuery = mSearch.value.toLowerCase().trim();
                            const specFilter = mSpec.value.trim();

                            tableRows.forEach(row => {
                                const doctorName = row.querySelector('.doctor-name-col').textContent.toLowerCase();
                                const specName = row.querySelector('.spec-col').textContent.trim();

                                const matchesSearch = searchQuery === '' || doctorName.includes(searchQuery);
                                const matchesSpec = specFilter === '' || specName === specFilter;

                                if (matchesSearch && matchesSpec) {
                                    row.style.display = '';
                                } else {
                                    row.style.display = 'none';
                                }
                            });
                        }

                        if (mSearch) {
                            mSearch.addEventListener('keyup', filterTable);
                        }
                        if (mSpec) {
                            mSpec.addEventListener('change', filterTable);
                        }
                        
                        // Xử lý nút chỉnh sửa khung giờ
                        document.querySelectorAll('.btn-edit-schedule').forEach(btn => {
                            btn.addEventListener('click', function() {
                                document.getElementById('edit_schedule_id').value = this.dataset.scheduleId;
                                document.getElementById('edit_start_time').value = this.dataset.startTime;
                                document.getElementById('edit_end_time').value = this.dataset.endTime;
                                document.getElementById('edit_doctor_name').textContent = 'BS. ' + this.dataset.doctor;
                                document.getElementById('edit_day_label').textContent = this.dataset.day;
                                $('#editScheduleModal').modal('show');
                            });
                        });
                    });
                </script>
            <?php } ?>

            <?php if ($page === 'patients') { ?>
                <section class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Hồ sơ bệnh nhân</h2>
                    </div>
                    <div class="data-table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Mã BN</th>
                                    <th>Họ tên</th>
                                    <th>Giới tính</th>
                                    <th>Email</th>
                                    <th>Liên hệ</th>
                                </tr>
                            </thead>
                            <tbody><?php $query = "select * from patreg";
                                    $result = $pdo->query($query);
                                    while ($row = $result->fetch(PDO::FETCH_BOTH)) { ?><tr>
                                        <td>#<?php echo $row['pid']; ?></td>
                                        <td><?php echo $row['lname'] . ' ' . $row['fname']; ?></td>
                                        <td><?php echo $row['gender']; ?></td>
                                        <td><?php echo $row['email']; ?></td>
                                        <td><?php echo $row['contact']; ?></td>
                                    </tr><?php } ?></tbody>
                        </table>
                    </div>
                </section>
            <?php } ?>

            <?php if ($page === 'appointments') { ?>
                <section class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Chi tiết lịch hẹn</h2>
                    </div>
                    <div class="data-table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Mã LH</th>
                                    <th>Tên bệnh nhân</th>
                                    <th>Liên hệ</th>
                                    <th>Bác sĩ</th>
                                    <th>Phí</th>
                                    <th>Ngày</th>
                                    <th>Giờ</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody><?php
                                    
                                    $query = "SELECT a.*, 
                                              CONCAT(p.fname, ' ', p.lname) as patient_fullname,
                                              p.contact as patient_contact
                                              FROM appointmenttb a
                                              LEFT JOIN patreg p ON a.pid = p.pid
                                              ORDER BY a.appdate DESC, a.apptime DESC";
                                    $result = $pdo->query($query);
                                    while ($row = $result->fetch(PDO::FETCH_ASSOC)) { ?><tr>
                                        <td>#<?php echo $row['ID']; ?></td>
                                        <td><strong><?php echo $row['patient_fullname'] ? $row['patient_fullname'] : ($row['fname'] . ' ' . $row['lname']); ?></strong></td>
                                        <td><?php echo $row['patient_contact'] ? $row['patient_contact'] : $row['contact']; ?></td>
                                        <td>BS. <?php echo $row['doctor']; ?></td>
                                        <td><?php echo number_format($row['docFees'], 0, ',', '.'); ?> ₫</td>
                                        <td><?php echo date('d/m/Y', strtotime($row['appdate'])); ?></td>
                                        <td><?php echo date('H:i', strtotime($row['apptime'])); ?></td>
                                        <td><?php if ($row['userStatus'] == 1 && $row['doctorStatus'] == 1) echo '<span class="badge badge-success"><i class="fas fa-check-circle"></i> Hoạt động</span>';
                                            else echo '<span class="badge badge-danger"><i class="fas fa-times-circle"></i> Đã hủy</span>'; ?></td>
                                    </tr><?php } ?></tbody>
                        </table>
                    </div>
                </section>
            <?php } ?>

            <?php if ($page === 'prescriptions') { ?>
                <section class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Hồ sơ đơn thuốc</h2>
                    </div>
                    <div class="data-table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Bác sĩ</th>
                                    <th>Tên bệnh nhân</th>
                                    <th>Ngày</th>
                                    <th>Bệnh</th>
                                    <th>Đơn thuốc</th>
                                </tr>
                            </thead>
                            <tbody><?php $query = "select * from prestb order by appdate desc";
                                    $result = $pdo->query($query);
                                    while ($row = $result->fetch(PDO::FETCH_BOTH)) { ?><tr>
                                        <td><?php echo $row['doctor']; ?></td>
                                        <td><?php echo $row['lname'] . ' ' . $row['fname']; ?></td>
                                        <td><?php echo date('d M Y', strtotime($row['appdate'])); ?></td>
                                        <td><?php echo $row['disease']; ?></td>
                                        <td><?php echo $row['prescription']; ?></td>
                                    </tr><?php } ?></tbody>
                        </table>
                    </div>
                </section>
            <?php } ?>

            <?php if ($page === 'queries') { ?>
                <section class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Thắc mắc khách hàng</h2>
                    </div>
                    <div class="data-table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Họ tên</th>
                                    <th>Email</th>
                                    <th>Liên hệ</th>
                                    <th>Tin nhắn</th>
                                </tr>
                            </thead>
                            <tbody><?php $query = "select * from contact";
                                    $result = $pdo->query($query);
                                    while ($row = $result->fetch(PDO::FETCH_BOTH)) { ?><tr>
                                        <td><?php echo $row['name']; ?></td>
                                        <td><?php echo $row['email']; ?></td>
                                        <td><?php echo $row['contact']; ?></td>
                                        <td><?php echo $row['message']; ?></td>
                                    </tr><?php } ?></tbody>
                        </table>
                    </div>
                </section>
            <?php } ?>

            <?php if ($page === 'medical-records') {
                
                $specs = $pdo->query("SELECT * FROM specializations ORDER BY name_vi")->fetchAll(PDO::FETCH_ASSOC);
                
                $doctors = $pdo->query("SELECT MIN(id) as id, fullname, spec_id FROM doctb GROUP BY fullname, spec_id")->fetchAll(PDO::FETCH_ASSOC);

                
                $filter_spec_id = isset($_POST['filter_spec_id']) ? $_POST['filter_spec_id'] : '';

                
                $sql_pat = "SELECT p.pid, p.fname, p.lname, p.contact, p.email, p.gender, p.date_of_birth,
                            (SELECT COUNT(*) FROM medical_records m WHERE m.patient_id = p.pid) as total_records,
                            (SELECT MAX(record_date) FROM medical_records m WHERE m.patient_id = p.pid) as last_visit
                            FROM patreg p ";
                $params = [];

                if (!empty($filter_spec_id)) {
                    
                    $sql_pat .= " WHERE p.pid IN (
                                      SELECT DISTINCT mr.patient_id 
                                      FROM medical_records mr
                                      INNER JOIN doctb d ON mr.doctor_id = d.id
                                      WHERE d.spec_id = ?
                                  )";
                    $params[] = $filter_spec_id;
                }
                $sql_pat .= " ORDER BY p.pid DESC";

                $stmt = $pdo->prepare($sql_pat);
                $stmt->execute($params);
                $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>
                <section class="content-section">
                    <div class="section-header">
                        <h2 class="section-title"><i class="fas fa-notes-medical"></i> Quản lý Hồ sơ Bệnh án</h2>
                    </div>

                    <div class="search-card mb-4">
                        <form method="POST" class="row align-items-center">
                            <div class="col-md-4"><label class="font-weight-bold mb-0">Lọc bệnh nhân theo khoa đã khám:</label></div>
                            <div class="col-md-6">
                                <select name="filter_spec_id" class="form-control custom-select-filter" onchange="this.form.submit()">
                                    <option value="">-- Tất cả bệnh nhân --</option>
                                    <?php foreach ($specs as $s): ?>
                                        <option value="<?php echo $s['id']; ?>" <?php if ($filter_spec_id == $s['id']) echo 'selected'; ?>><?php echo $s['name_vi']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2 text-right">
                                <?php if ($filter_spec_id): ?><a href="?page=medical-records" class="btn btn-sm btn-secondary"><i class="fas fa-sync"></i> Reset</a><?php endif; ?>
                            </div>
                        </form>
                    </div>

                    <div class="data-table-container">
                        <div class="data-table-header">
                            <h3 class="data-table-title">Danh sách bệnh nhân</h3>
                        </div>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Mã BN</th>
                                    <th>Họ tên</th>
                                    <th>Liên hệ</th>
                                    <th>Số hồ sơ</th>
                                    <th>Khám lần cuối</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($patients) > 0): foreach ($patients as $p): ?>
                                        <tr>
                                            <td>#<?php echo $p['pid']; ?></td>
                                            <td><strong><?php echo $p['fname'] . ' ' . $p['lname']; ?></strong></td>
                                            <td><?php echo $p['contact']; ?></td>
                                            <td><span class="badge badge-info"><?php echo $p['total_records']; ?> hồ sơ</span></td>
                                            <td><?php echo $p['last_visit'] ? date('d/m/Y', strtotime($p['last_visit'])) : '-'; ?></td>
                                            <td><button class="btn btn-primary btn-sm" onclick="viewPatientHistory(<?php echo $p['pid']; ?>)"><i class="fas fa-folder-open"></i> Chi tiết</button></td>
                                        </tr>
                                <?php endforeach;
                                else: echo '<tr><td colspan="6" class="text-center py-4">Không tìm thấy dữ liệu.</td></tr>';
                                endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <div class="modal fade" id="historyModal" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title">Lịch sử khám bệnh</h5>
                                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body" id="history_content" style="background-color: #f8f9fa;"></div>
                        </div>
                    </div>
                </div>

                <div class="modal fade modal-right"
                    id="recordModal"
                    tabindex="-1"
                    role="dialog"
                    aria-hidden="true">

                    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="recordModalLabel"><i class="fas fa-file-medical-alt text-primary mr-2"></i>Phiếu Khám Bệnh</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true" style="font-size: 1.5rem;">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form id="recordForm">
                                    <input type="hidden" name="action" id="record_action" value="add">
                                    <input type="hidden" name="patient_id" id="record_pid">
                                    <input type="hidden" name="record_id" id="record_id">

                                    <div class="row g-3">
                                        <div class="col-12 col-md-6">
                                            <div class="form-group">
                                                <label>Bác sĩ khám <span class="text-danger">*</span></label>
                                                <select name="doctor_id" id="record_doctor" class="form-control" required>
                                                    <option value="">-- Chọn bác sĩ --</option>
                                                    <?php foreach ($doctors as $d): ?><option value="<?php echo $d['id']; ?>">BS. <?php echo $d['fullname']; ?></option><?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <div class="form-group">
                                                <label>Ngày giờ khám</label>
                                                <input type="datetime-local" name="record_date" id="record_date" class="form-control" value="<?php echo date('Y-m-d\TH:i'); ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <fieldset class="vital-signs-group">
                                        <legend class="vital-legend"><i class="fas fa-heartbeat"></i> Chỉ số sinh tồn</legend>
                                        <div class="row g-3">
                                            <div class="col-6 col-md-3">
                                                <label class="small text-muted">Chiều cao (cm)</label>
                                                <input type="number" step="0.01" name="height" id="height" class="form-control" oninput="calcBMI()" placeholder="Ví dụ: 170">
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <label class="small text-muted">Cân nặng (kg)</label>
                                                <input type="number" step="0.01" name="weight" id="weight" class="form-control" oninput="calcBMI()" placeholder="Ví dụ: 65">
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <label class="small text-muted">BMI</label>
                                                <input type="text" name="bmi" id="bmi" class="form-control bg-light" readonly>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <label class="small text-muted">Nhiệt độ (°C)</label>
                                                <input type="number" step="0.1" name="temperature" id="temp" class="form-control" placeholder="37">
                                            </div>
                                            <div class="col-6 col-md-4">
                                                <label class="small text-muted">Huyết áp (mmHg)</label>
                                                <input type="text" name="blood_pressure" id="bp" class="form-control" placeholder="120/80">
                                            </div>
                                            <div class="col-6 col-md-4">
                                                <label class="small text-muted">Mạch (lần/phút)</label>
                                                <input type="number" name="heart_rate" id="hr" class="form-control">
                                            </div>
                                            <div class="col-6 col-md-4">
                                                <label class="small text-muted">Nhịp thở (lần/phút)</label>
                                                <input type="number" name="respiratory_rate" id="rr" class="form-control">
                                            </div>
                                        </div>
                                    </fieldset>

                                    <div class="form-group">
                                        <label>Lý do khám bệnh</label>
                                        <input type="text" name="chief_complaint" id="chief" class="form-control" placeholder="Ví dụ: Đau đầu, chóng mặt...">
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-12 col-md-6">
                                            <div class="form-group">
                                                <label>Triệu chứng lâm sàng</label>
                                                <textarea name="symptoms" id="symp" class="form-control" rows="3" placeholder="Mô tả triệu chứng..."></textarea>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <div class="form-group">
                                                <label>Chẩn đoán sơ bộ <span class="text-danger">*</span></label>
                                                <textarea name="diagnosis" id="diag" class="form-control" rows="3" required placeholder="Kết luận bệnh..."></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-12 col-md-6">
                                            <div class="form-group">
                                                <label>Hướng điều trị</label>
                                                <textarea name="treatment_plan" id="plan" class="form-control" rows="3"></textarea>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <div class="form-group">
                                                <label>Đơn thuốc</label>
                                                <textarea name="prescription" id="pres" class="form-control" rows="3" placeholder="Tên thuốc - Liều lượng..."></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-12 col-md-6">
                                            <div class="form-group">
                                                <label>Ghi chú thêm</label>
                                                <textarea name="notes" id="notes" class="form-control" rows="2"></textarea>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <div class="form-group">
                                                <label>Hẹn tái khám</label>
                                                <input type="date" name="follow_up_date" id="fup" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer bg-light" style="border-top: 1px solid #f0f0f0;">
                                <button type="button" class="btn btn-secondary" onclick="backToHistory()"><i class="fas fa-arrow-left mr-1"></i> Quay lại</button>
                                <button type="button" class="btn btn-primary px-4" onclick="saveRecord()"><i class="fas fa-save mr-1"></i> Lưu Hồ Sơ</button>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    function backToHistory() {
                        $('#recordModal').modal('hide');
                        setTimeout(function() {
                            $('#historyModal').modal('show');
                        }, 300);
                    }
                </script>
            <?php } ?>

        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script>
        function checkPassword() {
            if (document.getElementById('dpassword').value == document.getElementById('cdpassword').value) {
                document.getElementById('message').style.color = '#d2302c';
                document.getElementById('message').innerHTML = '✓ Khớp';
            } else {
                document.getElementById('message').style.color = '#EF4444';
                document.getElementById('message').innerHTML = '✗ Không khớp';
            }
        }
        $(function() {
            $('[data-toggle="tooltip"]').tooltip()
        })

        <?php if ($page === 'dashboard'): ?>
            document.addEventListener('DOMContentLoaded', function() {
                // Biểu đồ bệnh nhân mới theo tháng
                <?php
                $monthLabels = [];
                $patientCounts = [];

                
                for ($i = 11; $i >= 0; $i--) {
                    $date = date('Y-m', strtotime("-$i months"));
                    $monthLabels[$date] = date('m/Y', strtotime("-$i months"));
                    $patientCounts[$date] = 0;
                }

                
                foreach ($patientsMonthlyData as $row) {
                    if (isset($monthLabels[$row['month']])) {
                        $patientCounts[$row['month']] = (int)$row['count'];
                    }
                }
                ?>

                const patientsCtx = document.getElementById('patientsChart');
                if (patientsCtx) {
                    new Chart(patientsCtx, {
                        type: 'line',
                        data: {
                            labels: <?php echo json_encode(array_values($monthLabels)); ?>,
                            datasets: [{
                                label: 'Bệnh nhân mới',
                                data: <?php echo json_encode(array_values($patientCounts)); ?>,
                                borderColor: '#3b82f6',
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                borderWidth: 3,
                                fill: true,
                                tension: 0.4,
                                pointRadius: 5,
                                pointHoverRadius: 7,
                                pointBackgroundColor: '#3b82f6',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'top',
                                    labels: {
                                        font: {
                                            size: 14,
                                            weight: 'bold'
                                        },
                                        padding: 15
                                    }
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                    padding: 12,
                                    titleFont: {
                                        size: 14
                                    },
                                    bodyFont: {
                                        size: 13
                                    },
                                    callbacks: {
                                        label: function(context) {
                                            return context.dataset.label + ': ' + context.parsed.y + ' bệnh nhân';
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        font: {
                                            size: 12
                                        }
                                    },
                                    grid: {
                                        color: 'rgba(0, 0, 0, 0.05)'
                                    }
                                },
                                x: {
                                    ticks: {
                                        font: {
                                            size: 12
                                        }
                                    },
                                    grid: {
                                        display: false
                                    }
                                }
                            }
                        }
                    });
                }

                // Biểu đồ doanh thu theo tháng
                <?php
                $revenueLabels = [];
                $revenueCounts = [];

                
                for ($i = 11; $i >= 0; $i--) {
                    $date = date('Y-m', strtotime("-$i months"));
                    $revenueLabels[$date] = date('m/Y', strtotime("-$i months"));
                    $revenueCounts[$date] = 0;
                }

                
                foreach ($revenueMonthlyData as $row) {
                    if (isset($revenueLabels[$row['month']])) {
                        $revenueCounts[$row['month']] = (float)$row['revenue'];
                    }
                }
                ?>

                const revenueCtx = document.getElementById('revenueChart');
                if (revenueCtx) {
                    new Chart(revenueCtx, {
                        type: 'bar',
                        data: {
                            labels: <?php echo json_encode(array_values($revenueLabels)); ?>,
                            datasets: [{
                                label: 'Doanh thu (VNĐ)',
                                data: <?php echo json_encode(array_values($revenueCounts)); ?>,
                                backgroundColor: 'rgba(34, 197, 94, 0.7)',
                                borderColor: '#22c55e',
                                borderWidth: 2,
                                borderRadius: 8,
                                hoverBackgroundColor: 'rgba(34, 197, 94, 0.9)'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'top',
                                    labels: {
                                        font: {
                                            size: 14,
                                            weight: 'bold'
                                        },
                                        padding: 15
                                    }
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                    padding: 12,
                                    titleFont: {
                                        size: 14
                                    },
                                    bodyFont: {
                                        size: 13
                                    },
                                    callbacks: {
                                        label: function(context) {
                                            return 'Doanh thu: ' + new Intl.NumberFormat('vi-VN', {
                                                style: 'currency',
                                                currency: 'VND'
                                            }).format(context.parsed.y);
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        font: {
                                            size: 12
                                        },
                                        callback: function(value) {
                                            return new Intl.NumberFormat('vi-VN', {
                                                notation: 'compact',
                                                compactDisplay: 'short'
                                            }).format(value);
                                        }
                                    },
                                    grid: {
                                        color: 'rgba(0, 0, 0, 0.05)'
                                    }
                                },
                                x: {
                                    ticks: {
                                        font: {
                                            size: 12
                                        }
                                    },
                                    grid: {
                                        display: false
                                    }
                                }
                            }
                        }
                    });
                }

                // Biểu đồ tỷ lệ lịch khám
                const appointmentCtx = document.getElementById('appointmentChart');
                if (appointmentCtx) {
                    new Chart(appointmentCtx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Thành công', 'Đã hủy'],
                            datasets: [{
                                data: [
                                    <?php echo (int)$appointmentStats['success']; ?>,
                                    <?php echo (int)$appointmentStats['cancelled']; ?>
                                ],
                                backgroundColor: [
                                    'rgba(34, 197, 94, 0.8)',
                                    'rgba(239, 68, 68, 0.8)'
                                ],
                                borderColor: [
                                    '#22c55e',
                                    '#ef4444'
                                ],
                                borderWidth: 2,
                                hoverOffset: 15
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        font: {
                                            size: 13,
                                            weight: 'bold'
                                        },
                                        padding: 15,
                                        usePointStyle: true,
                                        pointStyle: 'circle'
                                    }
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                    padding: 12,
                                    titleFont: {
                                        size: 14
                                    },
                                    bodyFont: {
                                        size: 13
                                    },
                                    callbacks: {
                                        label: function(context) {
                                            const total = <?php echo (int)$appointmentStats['total']; ?>;
                                            const value = context.parsed;
                                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                            return context.label + ': ' + value + ' (' + percentage + '%)';
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            });

        <?php endif; ?>
        $(document).ready(function() {
            $('.modal').on('hidden.bs.modal', function() {
                if ($('.modal:visible').length == 0) {
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open').css({
                        'overflow': '',
                        'padding-right': ''
                    });
                }
            });
        });

        function calcBMI() {
            var h = document.getElementById('height').value;
            var w = document.getElementById('weight').value;
            if (h > 0 && w > 0) {
                var bmi = w / ((h / 100) * (h / 100));
                document.getElementById('bmi').value = bmi.toFixed(2);
            }
        }

        function viewPatientHistory(pid) {
            $('#historyModal').modal('show');
            $('#history_content').html('<div class="text-center p-5"><i class="fas fa-spinner fa-spin"></i> Đang tải...</div>');

            // Gửi AJAX request để lấy dữ liệu
            $.ajax({
                type: 'POST',
                url: window.location.href,
                data: {
                    view_patient_history: 1,
                    history_pid: pid
                },
                dataType: 'json',
                success: function(response) {
                    $('#history_content').html(response.html);
                },
                error: function(xhr, status, error) {
                    console.log('Error:', error);
                    console.log('Status:', status);
                    console.log('Response:', xhr.responseText);
                    $('#history_content').html('<p class="text-danger text-center">Lỗi tải dữ liệu: ' + error + '</p>');
                }
            });
        }

        function openAddRecordModal(pid) {
            $('#recordForm')[0].reset();
            $('#record_action').val('add');
            $('#record_pid').val(pid);

            $('#historyModal').modal('hide');

            $('#recordModal').modal({
                backdrop: false,
                keyboard: false,
                show: true
            });
        }

        function editRecord(rec) {
            $('#record_action').val('edit');
            $('#record_id').val(rec.id);
            $('#record_pid').val(rec.patient_id);

            $('#record_doctor').val(rec.doctor_id);
            $('#record_date').val(rec.record_date.replace(' ', 'T').substring(0, 16));

            $('#height').val(rec.height);
            $('#weight').val(rec.weight);
            $('#bmi').val(rec.bmi);
            $('#bp').val(rec.blood_pressure);
            $('#hr').val(rec.heart_rate);
            $('#rr').val(rec.respiratory_rate);
            $('#temp').val(rec.temperature);

            $('#chief').val(rec.chief_complaint);
            $('#symp').val(rec.symptoms);
            $('#diag').val(rec.diagnosis);
            $('#plan').val(rec.treatment_plan);
            $('#pres').val(rec.prescription);
            $('#notes').val(rec.notes);
            $('#fup').val(rec.follow_up_date);

            $('#historyModal').modal('hide');

            $('#recordModal').modal({
                backdrop: false,
                keyboard: false,
                show: true
            });
        }


        function saveRecord() {
            if ($('#record_doctor').val() == '' || $('#diag').val() == '') {
                alert("Vui lòng nhập bác sĩ và chẩn đoán.");
                return;
            }
            $.post('ajax/manage_record.php', $('#recordForm').serialize(), function(res) {
                if (res.trim() == 'success') {
                    alert("Đã lưu thành công!");
                    $('#recordModal').modal('hide');
                    viewPatientHistory($('#record_pid').val());
                } else {
                    alert("Lỗi: " + res);
                }
            });
        }

        function deleteRecord(id, pid) {
            if (confirm('Chắc chắn xóa?')) {
                $.post('ajax/manage_record.php', {
                    action: 'delete',
                    record_id: id
                }, function(res) {
                    if (res.trim() == 'success') viewPatientHistory(pid);
                    else alert("Lỗi xóa.");
                });
            }
        }

        $('#recordModal').on('hidden.bs.modal', function() {
            // Tùy chọn: Mở lại history khi đóng form
            // viewPatientHistory($('#record_pid').val());
        });

        $('.modal').on('hidden.bs.modal', function() {
            if ($('.modal.show').length === 0) {
                $('body').removeClass('modal-open');
                $('body').css({
                    overflow: '',
                    paddingRight: ''
                });
                $('.modal-backdrop').remove();
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