<?php
ob_start();
session_start();
require_once('../../config.php');
require_once('../../includes/messages.php');
require_once('../../includes/functions.php');

$pid = $_SESSION['pid'] ?? null;
$username = $_SESSION['username'] ?? '';
$email = $_SESSION['email'] ?? '';
$fname = $_SESSION['fname'] ?? '';
$gender = $_SESSION['gender'] ?? '';
$lname = $_SESSION['lname'] ?? '';
$contact = $_SESSION['contact'] ?? '';

if (!$pid) {
    header("Location: ../../index.php");
    exit();
}


$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$allowed_pages = array('dashboard', 'book-appointment', 'appointment-history', 'prescriptions', 'profile');
if (!in_array($page, $allowed_pages)) {
    $page = 'dashboard';
}


$booking_step = isset($_GET['step']) ? intval($_GET['step']) : 1;
$selected_spec = isset($_GET['spec_id']) ? intval($_GET['spec_id']) : null;
$selected_doctor = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : null;


$selected_date = null;
if (isset($_GET['date']) && !empty($_GET['date'])) {
    $selected_date = $_GET['date'];
} elseif (isset($_GET['year']) && isset($_GET['month']) && isset($_GET['day'])) {
    
    $year = intval($_GET['year']);
    $month = str_pad(intval($_GET['month']), 2, '0', STR_PAD_LEFT);
    $day = str_pad(intval($_GET['day']), 2, '0', STR_PAD_LEFT);

    
    $selected_date = "$year-$month-$day";

    
    $date_obj = DateTime::createFromFormat('Y-m-d', $selected_date);
    if (!$date_obj || $date_obj->format('Y-m-d') !== $selected_date) {
        $selected_date = null; 
    } elseif ($date_obj < new DateTime('today')) {
        $selected_date = null; 
    }
}


$specializations = [];
if ($page === 'book-appointment') {
    $spec_stmt = $pdo->query("SELECT id, name, name_vi, icon, description FROM specializations WHERE status = 1 ORDER BY name_vi");
    $specializations = $spec_stmt->fetchAll(PDO::FETCH_ASSOC);
}


$doctors = [];
if ($page === 'book-appointment' && $selected_spec && $booking_step >= 2) {
    $doc_stmt = $pdo->prepare("
        SELECT d.id, d.username, d.fullname, d.email, d.docFees, d.experience_years, d.bio,
               s.name_vi as spec_name
        FROM doctb d
        LEFT JOIN specializations s ON d.spec_id = s.id
        WHERE d.spec_id = :spec_id AND d.status = 1
        ORDER BY d.fullname
    ");
    $doc_stmt->execute([':spec_id' => $selected_spec]);
    $doctors = $doc_stmt->fetchAll(PDO::FETCH_ASSOC);
}


$time_slots = [];
$schedule_info = null;
if ($page === 'book-appointment' && $selected_doctor && $selected_date && $booking_step >= 4) {
    
    $date_obj = DateTime::createFromFormat('Y-m-d', $selected_date);
    if ($date_obj && $date_obj->format('Y-m-d') === $selected_date) {
        $day_of_week = date('w', strtotime($selected_date));

        
        $schedule_stmt = $pdo->prepare("
            SELECT start_time, end_time, slot_duration, max_patients
            FROM doctor_schedules
            WHERE doctor_id = :doctor_id AND day_of_week = :day_of_week AND is_active = 1
            LIMIT 1
        ");

        $schedule_stmt->execute([
            ':doctor_id' => $selected_doctor,
            ':day_of_week' => $day_of_week
        ]);

        $schedule_info = $schedule_stmt->fetch(PDO::FETCH_ASSOC);

        if ($schedule_info) {
            
            $start_time = new DateTime($schedule_info['start_time']);
            $end_time = new DateTime($schedule_info['end_time']);
            $slot_duration = intval($schedule_info['slot_duration']);

            $current_time = clone $start_time;

            while ($current_time < $end_time) {
                $slot_time = $current_time->format('H:i:s');
                $slot_time_display = $current_time->format('H:i');

                
                $check_stmt = $pdo->prepare("
                    SELECT id, status, appointment_id
                    FROM time_slots
                    WHERE doctor_id = :doctor_id 
                    AND slot_date = :slot_date 
                    AND slot_time = :slot_time
                ");

                $check_stmt->execute([
                    ':doctor_id' => $selected_doctor,
                    ':slot_date' => $selected_date,
                    ':slot_time' => $slot_time
                ]);

                $existing_slot = $check_stmt->fetch(PDO::FETCH_ASSOC);

                if ($existing_slot) {
                    
                    $time_slots[] = [
                        'id' => $existing_slot['id'],
                        'slot_time' => $slot_time_display,
                        'slot_time_full' => $slot_time,
                        'status' => $existing_slot['status'],
                        'appointment_id' => $existing_slot['appointment_id']
                    ];
                } else {
                    
                    $insert_stmt = $pdo->prepare("
                        INSERT INTO time_slots (doctor_id, slot_date, slot_time, status)
                        VALUES (:doctor_id, :slot_date, :slot_time, 'available')
                    ");

                    $insert_stmt->execute([
                        ':doctor_id' => $selected_doctor,
                        ':slot_date' => $selected_date,
                        ':slot_time' => $slot_time
                    ]);

                    $new_slot_id = $pdo->lastInsertId();

                    $time_slots[] = [
                        'id' => $new_slot_id,
                        'slot_time' => $slot_time_display,
                        'slot_time_full' => $slot_time,
                        'status' => 'available',
                        'appointment_id' => null
                    ];
                }

                
                $current_time->modify("+{$slot_duration} minutes");
            }

            
            if ($selected_date === date('Y-m-d')) {
                $current_time_now = date('H:i:s');
                foreach ($time_slots as &$slot) {
                    if ($slot['slot_time_full'] <= $current_time_now && $slot['status'] === 'available') {
                        $slot['status'] = 'blocked';

                        
                        $update_stmt = $pdo->prepare("
                            UPDATE time_slots 
                            SET status = 'blocked' 
                            WHERE id = :id
                        ");
                        $update_stmt->execute([':id' => $slot['id']]);
                    }
                }
            }
        }
    }
}


if (isset($_POST['app-submit'])) {
    $doctor_id = intval($_POST['doctor_id']);
    $slot_id = intval($_POST['slot_id']);
    $appdate = $_POST['appdate'];
    $apptime = $_POST['apptime'];

    
    $stmt = $pdo->prepare("SELECT fullname, docFees FROM doctb WHERE id = :id");
    $stmt->execute([':id' => $doctor_id]);
    $doctor = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($doctor) {
        
        $docFees = intval($doctor['docFees']);
        $doctorName = $doctor['fullname'];

        
        $check_stmt = $pdo->prepare("SELECT status FROM time_slots WHERE id = :slot_id AND status = 'available'");
        $check_stmt->execute([':slot_id' => $slot_id]);

        if ($check_stmt->rowCount() > 0) {
            
            $stmt = $pdo->prepare("INSERT INTO appointmenttb(pid,fname,lname,gender,email,contact,doctor,docFees,appdate,apptime,slot_id,userStatus,doctorStatus) VALUES(:pid,:fname,:lname,:gender,:email,:contact,:doctor,:docFees,:appdate,:apptime,:slot_id,'1','1')");
            $result = $stmt->execute([
                ':pid' => $pid,
                ':fname' => $fname,
                ':lname' => $lname,
                ':gender' => $gender,
                ':email' => $email,
                ':contact' => $contact,
                ':doctor' => $doctorName,
                ':docFees' => $docFees,
                ':appdate' => $appdate,
                ':apptime' => $apptime,
                ':slot_id' => $slot_id
            ]);

            if ($result) {
                $appointment_id = $pdo->lastInsertId();
                
                $update_stmt = $pdo->prepare("UPDATE time_slots SET status = 'booked', appointment_id = :app_id WHERE id = :slot_id");
                $update_stmt->execute([':app_id' => $appointment_id, ':slot_id' => $slot_id]);

                redirectWithMessage($_SERVER['PHP_SELF'] . '?page=appointment-history', 'success', 'Đặt lịch hẹn thành công!');
            } else {
                redirectWithMessage($_SERVER['PHP_SELF'], 'error', 'Không thể xử lý yêu cầu. Vui lòng thử lại!');
            }
        } else {
            redirectWithMessage($_SERVER['PHP_SELF'], 'error', 'Khung giờ này đã được đặt. Vui lòng chọn khung giờ khác!');
        }
    }
}


if (isset($_GET['cancel'])) {
    $stmt = $pdo->prepare("SELECT slot_id FROM appointmenttb WHERE ID = :id");
    $stmt->execute([':id' => $_GET['ID']]);
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

    
    $stmt = $pdo->prepare("UPDATE appointmenttb SET userStatus='0' WHERE ID = :id");
    $result = $stmt->execute([':id' => $_GET['ID']]);

    if ($result && $appointment['slot_id']) {
        
        $slot_stmt = $pdo->prepare("UPDATE time_slots SET status = 'available', appointment_id = NULL WHERE id = :slot_id");
        $slot_stmt->execute([':slot_id' => $appointment['slot_id']]);
    }

    if ($result) {
        redirectWithMessage($_SERVER['PHP_SELF'] . '?page=appointment-history', 'success', 'Đã hủy lịch hẹn thành công');
    }
}


function generate_bill()
{
    global $pdo;
    $pid = $_SESSION['pid'];
    $output = '';
    
    
    $stmt = $pdo->prepare("
        SELECT p.pres_id, p.pid, p.ID, p.fname, p.lname, p.doctor, p.appdate, p.apptime, 
               p.disease, p.allergy, p.prescription, a.docFees 
        FROM prestb p 
        INNER JOIN appointmenttb a ON p.ID = a.ID 
        WHERE p.pid = :pid AND p.ID = :id
    ");
    $stmt->execute([':pid' => $pid, ':id' => $_GET['ID']]);
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        
        $prescription_text = $row['prescription'] ?? '';
        $pres_id = $row['pres_id'] ?? null;
        
        
        if (empty($prescription_text) || $prescription_text === 'Chi tiết trong bảng thuốc' || strlen($prescription_text) < 5) {
            if ($pres_id) {
                $med_stmt = $pdo->prepare("
                    SELECT medication_name, dosage, frequency, duration, special_notes 
                    FROM prescription_medications 
                    WHERE prescription_id = :pres_id
                ");
                $med_stmt->execute([':pres_id' => $pres_id]);
                $medications = $med_stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (!empty($medications)) {
                    $prescription_text = '';
                    foreach ($medications as $idx => $med) {
                        $prescription_text .= ($idx + 1) . '. ' . $med['medication_name'];
                        if (!empty($med['dosage'])) $prescription_text .= ' - ' . $med['dosage'];
                        if (!empty($med['frequency'])) $prescription_text .= ' - ' . $med['frequency'];
                        if (!empty($med['duration'])) $prescription_text .= ' (' . $med['duration'] . ')';
                        $prescription_text .= '<br/>';
                    }
                }
            }
        }
        
        
        if (empty($prescription_text)) {
            $prescription_text = 'Chưa có thông tin đơn thuốc';
        }
        
        $output .= '
    <label> Mã bệnh nhân : </label>' . htmlspecialchars($row['pid']) . '<br/><br/>
    <label> Mã lịch hẹn : </label>' . htmlspecialchars($row['ID']) . '<br/><br/>
    <label> Tên bệnh nhân : </label>' . htmlspecialchars($row['lname'] . ' ' . $row['fname']) . '<br/><br/>
    <label> Bác sĩ khám : </label>' . htmlspecialchars($row['doctor']) . '<br/><br/>
    <label> Ngày khám : </label>' . date('d/m/Y', strtotime($row['appdate'])) . '<br/><br/>
    <label> Giờ khám : </label>' . date('H:i', strtotime($row['apptime'])) . '<br/><br/>
    <label> Chẩn đoán : </label>' . htmlspecialchars($row['disease']) . '<br/><br/>
    <label> Dị ứng : </label>' . htmlspecialchars($row['allergy']) . '<br/><br/>
    <label> Đơn thuốc : </label><br/>' . $prescription_text . '<br/>
    <label> Chi phí khám : </label>' . number_format($row['docFees']) . ' VNĐ<br/>
    ';
    }
    return $output;
}

if (isset($_GET["generate_bill"])) {
    require_once("../../TCPDF/tcpdf.php");
    $obj_pdf = new TCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $obj_pdf->SetCreator(PDF_CREATOR);
    $obj_pdf->SetTitle("Hóa đơn khám bệnh");
    $obj_pdf->SetHeaderData('', '', PDF_HEADER_TITLE, PDF_HEADER_STRING);
    $obj_pdf->SetHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $obj_pdf->SetFooterFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $obj_pdf->SetDefaultMonospacedFont('helvetica');
    $obj_pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    $obj_pdf->SetMargins(PDF_MARGIN_LEFT, '5', PDF_MARGIN_RIGHT);
    $obj_pdf->SetPrintHeader(false);
    $obj_pdf->SetPrintFooter(false);
    $obj_pdf->SetAutoPageBreak(TRUE, 10);
    $obj_pdf->SetFont('dejavusans', '', 12);
    $obj_pdf->AddPage();

    $content = '';
    $content .= '
      <br/>
      <h2 align ="center"> Bệnh viện Đa khoa Global</h2></br>
      <h3 align ="center"> Hóa đơn khám bệnh</h3>
  ';
    $content .= generate_bill();
    $obj_pdf->writeHTML($content);
    ob_end_clean();
    $obj_pdf->Output("hoa-don-kham-benh.pdf", 'I');
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="shortcut icon" type="image/x-icon" href="../../images/favicon.png" />
    <title>Bảng điều khiển bệnh nhân - Bệnh viện Global</title>

    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/custom/medical-theme.css">
    <link rel="stylesheet" href="../../assets/css/custom/global-improvements.css">
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

        /* Dropdown Menu Styling */
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

        /* Improved spacing for user info */
        .navbar-user-info {
            margin-left: 1rem;
        }
    </style>

    <style>
        /* Time Slots Grid - Cinema Style */
        .specializations-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1.2rem;
            margin-top: 1.2rem;
        }

        .spec-card {
            background: white;
            border-radius: 14px;
            padding: 1.2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            box-shadow: 0 3px 10px rgba(210, 48, 44, 0.1);
        }

        .spec-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 24px rgba(210, 48, 44, 0.2);
            border-color: var(--medical-blue);
        }

        .spec-card.active {
            background: linear-gradient(135deg, #d2302c 0%, #ff4d4d 100%);
            color: white;
            border-color: var(--medical-blue-dark);
        }

        .spec-icon {
            width: 70px;
            height: 70px;
            margin: 0 auto 0.8rem;
            background: linear-gradient(135deg, rgba(8, 145, 178, 0.1), rgba(6, 182, 212, 0.2));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            color: var(--medical-blue);
        }

        .spec-card.active .spec-icon {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .spec-name {
            font-size: 0.95rem;
            font-weight: 600;
            margin-bottom: 0.4rem;
        }

        .spec-count {
            font-size: 0.8rem;
            opacity: 0.8;
        }

        .doctors-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.2rem;
            margin-top: 1.2rem;
        }

        .doctor-card {
            background: white;
            border-radius: 14px;
            padding: 1.2rem;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            box-shadow: 0 3px 10px rgba(8, 145, 178, 0.1);
        }

        .doctor-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 24px rgba(8, 145, 178, 0.2);
            border-color: var(--medical-blue);
        }

        .doctor-card.active {
            border-color: var(--medical-blue-dark);
            background: linear-gradient(135deg, rgba(6, 182, 212, 0.05), rgba(8, 145, 178, 0.1));
        }

        .doctor-avatar {
            width: 70px;
            height: 70px;
            margin: 0 auto 0.8rem;
            background: linear-gradient(135deg, var(--medical-blue), var(--medical-teal));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            color: white;
            font-weight: 700;
        }

        .doctor-name {
            font-size: 1.05rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 0.4rem;
            color: var(--medical-dark);
        }

        .doctor-spec {
            text-align: center;
            color: var(--medical-blue);
            font-size: 0.8rem;
            margin-bottom: 0.8rem;
        }

        .doctor-fee {
            text-align: center;
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--health-green);
        }

        .time-slots-container {
            margin-top: 1.5rem;
        }

        .slots-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
            gap: 0.8rem;
            margin-top: 1.2rem;
        }

        .time-slot {
            background: white;
            border: 2px solid var(--steel-gray);
            border-radius: 10px;
            padding: 0.8rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
        }

        .time-slot:hover:not(.booked):not(.blocked) {
            border-color: var(--medical-blue);
            background: rgba(8, 145, 178, 0.05);
            transform: scale(1.05);
        }

        .time-slot.available {
            border-color: var(--health-green);
            color: var(--health-green);
        }

        .time-slot.available:hover {
            background: var(--health-green);
            color: white;
        }

        .time-slot.selected {
            background: var(--medical-blue);
            border-color: var(--medical-blue-dark);
            color: white;
        }

        .time-slot.booked {
            background: #f1f5f9;
            border-color: #cbd5e1;
            color: #94a3b8;
            cursor: not-allowed;
            opacity: 0.6;
        }

        .time-slot.blocked {
            background: #fee2e2;
            border-color: #fca5a5;
            color: #dc2626;
            cursor: not-allowed;
            text-decoration: line-through;
        }

        .slot-time {
            font-size: 0.9rem;
            display: block;
        }

        .slot-status {
            font-size: 0.7rem;
            display: block;
            margin-top: 0.4rem;
            opacity: 0.8;
        }

        .booking-summary {
            background: linear-gradient(135deg, rgba(8, 145, 178, 0.05), rgba(6, 182, 212, 0.1));
            border-radius: 14px;
            padding: 1.5rem;
            margin-top: 1.5rem;
            border: 2px solid var(--medical-blue-light);
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 0.6rem 0;
            border-bottom: 1px solid rgba(8, 145, 178, 0.1);
        }

        .summary-item:last-child {
            border-bottom: none;
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--medical-blue);
        }

        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1.5rem;
            position: relative;
        }

        .step {
            flex: 1;
            text-align: center;
            position: relative;
        }

        .step-number {
            width: 45px;
            height: 45px;
            margin: 0 auto 0.4rem;
            background: white;
            border: 2px solid var(--steel-gray);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.05rem;
            color: var(--steel-gray);
            position: relative;
            z-index: 2;
        }

        .step.active .step-number {
            background: linear-gradient(135deg, #d2302c 0%, #ff4d4d 100%);
            border-color: #d2302c;
            color: white;
            box-shadow: 0 4px 15px rgba(210, 48, 44, 0.3);
        }

        .step.completed .step-number {
            background: #ffd700;
            border-color: #ffd700;
            color: #8b0000;
        }

        .step-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--charcoal);
        }

        .step-line {
            position: absolute;
            top: 22px;
            left: 50%;
            right: -50%;
            height: 2px;
            background: var(--steel-gray);
            z-index: 1;
        }

        .step.completed .step-line {
            background: #d2302c;
        }

        .step:last-child .step-line {
            display: none;
        }

        .modal-backdrop {}

        .modal {
            z-index: 1050;
        }

        .empty-state {
            text-align: center;
            padding: 2.5rem;
            color: var(--charcoal);
        }

        .empty-state i {
            font-size: 3.5rem;
            color: var(--steel-gray);
            margin-bottom: 0.8rem;
        }

        @media (max-width: 768px) {

            .specializations-grid,
            .doctors-grid {
                grid-template-columns: 1fr;
            }

            .slots-grid {
                grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            }
        }
    </style>

    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <style>
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
    <div class="dashboard-container">
        
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <i class="fas fa-hospital"></i>
                </div>
                <div>
                    <h1 class="sidebar-title">Bệnh viện Global</h1>
                    <div class="sidebar-subtitle">Cổng bệnh nhân</div>
                </div>
            </div>

            <ul class="sidebar-menu">
                <li class="sidebar-menu-item">
                    <a href="?page=dashboard" class="sidebar-menu-link <?php echo ($page === 'dashboard') ? 'active' : ''; ?>">
                        <i class="fas fa-th-large sidebar-menu-icon"></i>
                        <span>Tổng quan</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="?page=profile" class="sidebar-menu-link <?php echo ($page === 'profile') ? 'active' : ''; ?>">
                        <i class="fas fa-user sidebar-menu-icon"></i>
                        <span>Hồ sơ cá nhân</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="?page=book-appointment" class="sidebar-menu-link <?php echo ($page === 'book-appointment') ? 'active' : ''; ?>">
                        <i class="fas fa-calendar-plus sidebar-menu-icon"></i>
                        <span>Đặt lịch khám</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="?page=appointment-history" class="sidebar-menu-link <?php echo ($page === 'appointment-history') ? 'active' : ''; ?>">
                        <i class="fas fa-history sidebar-menu-icon"></i>
                        <span>Lịch sử khám</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="?page=prescriptions" class="sidebar-menu-link <?php echo ($page === 'prescriptions') ? 'active' : ''; ?>">
                        <i class="fas fa-file-prescription sidebar-menu-icon"></i>
                        <span>Đơn thuốc</span>
                    </a>
                </li>
            </ul>
        </aside>

        
        <main class="main-content">
            
            <nav class="top-navbar">
                <div class="navbar-left">
                    <h1 class="navbar-title">Bảng điều khiển bệnh nhân</h1>
                </div>
                <div class="navbar-right">
                    <div class="navbar-user dropdown">
                        <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" id="navbarUserDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="cursor: pointer;">
                            <div class="navbar-user-avatar">
                                <?php echo strtoupper(substr($fname, 0, 1)); ?>
                            </div>
                            <div class="navbar-user-info">
                                <div class="navbar-user-name"><?php echo $lname . ' ' . $fname; ?></div>
                                <div class="navbar-user-role">Bệnh nhân</div>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarUserDropdown">
                            <a class="dropdown-item" href="../../index.php">
                                <i class="fas fa-home mr-2"></i> Quay về trang chủ
                            </a>
                        </div>
                    </div>
                </div>
            </nav>

            
            <?php if ($page === 'dashboard') { ?>
                <section class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Xin chào, <?php echo $fname; ?>!</h2>
                        <p class="section-subtitle">Quản lý lịch khám và hồ sơ bệnh án của bạn</p>
                    </div>

                    
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon primary">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-label">Tổng số lịch hẹn</div>
                                <div class="stat-value">
                                    <?php
                                    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM appointmenttb WHERE fname = :fname AND lname = :lname");
                                    $stmt->execute([':fname' => $fname, ':lname' => $lname]);
                                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                                    echo $row['total'];
                                    ?>
                                </div>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon success">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-label">Lịch hẹn đang hoạt động</div>
                                <div class="stat-value">
                                    <?php
                                    $stmt = $pdo->prepare("SELECT COUNT(*) as active FROM appointmenttb WHERE fname = :fname AND lname = :lname AND userStatus='1' AND doctorStatus='1'");
                                    $stmt->execute([':fname' => $fname, ':lname' => $lname]);
                                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                                    echo $row['active'];
                                    ?>
                                </div>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon warning">
                                <i class="fas fa-file-prescription"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-label">Đơn thuốc</div>
                                <div class="stat-value">
                                    <?php
                                    $stmt = $pdo->prepare("SELECT COUNT(*) as pres FROM prestb WHERE pid = :pid");
                                    $stmt->execute([':pid' => $pid]);
                                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                                    echo $row['pres'];
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    
                    <div class="row mt-4">
                        <div class="col-md-4">
                            <a href="?page=book-appointment" class="stat-card" style="cursor: pointer; text-decoration: none; color: inherit;">
                                <div class="stat-icon primary">
                                    <i class="fas fa-calendar-plus"></i>
                                </div>
                                <div class="stat-content">
                                    <h5>Đặt lịch khám</h5>
                                    <p class="text-muted mb-0">Đặt lịch hẹn khám bệnh mới</p>
                                </div>
                            </a>
                        </div>

                        <div class="col-md-4">
                            <a href="?page=appointment-history" class="stat-card" style="cursor: pointer; text-decoration: none; color: inherit;">
                                <div class="stat-icon success">
                                    <i class="fas fa-history"></i>
                                </div>
                                <div class="stat-content">
                                    <h5>Xem lịch sử</h5>
                                    <p class="text-muted mb-0">Kiểm tra các lịch hẹn trước đây</p>
                                </div>
                            </a>
                        </div>

                        <div class="col-md-4">
                            <a href="?page=prescriptions" class="stat-card" style="cursor: pointer; text-decoration: none; color: inherit;">
                                <div class="stat-icon warning">
                                    <i class="fas fa-file-prescription"></i>
                                </div>
                                <div class="stat-content">
                                    <h5>Đơn thuốc</h5>
                                    <p class="text-muted mb-0">Xem các đơn thuốc của bạn</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </section>
            <?php } ?>

            <?php if ($page === 'book-appointment') { ?>
                <section class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Đặt lịch khám bệnh</h2>
                        <p class="section-subtitle">Chọn chuyên khoa, bác sĩ và thời gian khám phù hợp</p>
                    </div>

                    
                    <div class="step-indicator">
                        <div class="step <?php echo ($booking_step >= 1) ? 'active' : ''; ?> <?php echo ($booking_step > 1) ? 'completed' : ''; ?>" id="step1">
                            <div class="step-number"><?php echo ($booking_step > 1) ? '<i class="fas fa-check"></i>' : '1'; ?></div>
                            <div class="step-label">Chọn chuyên khoa</div>
                            <div class="step-line"></div>
                        </div>
                        <div class="step <?php echo ($booking_step >= 2) ? 'active' : ''; ?> <?php echo ($booking_step > 2) ? 'completed' : ''; ?>" id="step2">
                            <div class="step-number"><?php echo ($booking_step > 2) ? '<i class="fas fa-check"></i>' : '2'; ?></div>
                            <div class="step-label">Chọn bác sĩ</div>
                            <div class="step-line"></div>
                        </div>
                        <div class="step <?php echo ($booking_step >= 3) ? 'active' : ''; ?> <?php echo ($booking_step > 3) ? 'completed' : ''; ?>" id="step3">
                            <div class="step-number"><?php echo ($booking_step > 3) ? '<i class="fas fa-check"></i>' : '3'; ?></div>
                            <div class="step-label">Chọn ngày</div>
                            <div class="step-line"></div>
                        </div>
                        <div class="step <?php echo ($booking_step >= 4) ? 'active' : ''; ?>" id="step4">
                            <div class="step-number">4</div>
                            <div class="step-label">Chọn giờ</div>
                        </div>
                    </div>

                    <?php if ($booking_step == 1) { ?>
                        <div class="data-table-container">
                            <div class="data-table-header">
                                <h3 class="data-table-title"><i class="fas fa-stethoscope"></i> Chọn chuyên khoa y tế</h3>
                            </div>
                            <div class="p-4">
                                <div class="specializations-grid">
                                    <?php foreach ($specializations as $spec) { ?>
                                        <a href="?page=book-appointment&step=2&spec_id=<?php echo $spec['id']; ?>" class="spec-card" style="text-decoration: none; color: inherit;">
                                            <div class="spec-icon">
                                                <i class="<?php echo htmlspecialchars($spec['icon'] ?? 'fas fa-stethoscope'); ?>"></i>
                                            </div>
                                            <div class="spec-name"><?php echo htmlspecialchars($spec['name_vi']); ?></div>
                                            <div class="spec-count">
                                                <?php
                                                $count_stmt = $pdo->prepare("SELECT COUNT(*) as total FROM doctb WHERE spec_id = :spec_id AND status = 1");
                                                $count_stmt->execute([':spec_id' => $spec['id']]);
                                                $count = $count_stmt->fetch(PDO::FETCH_ASSOC);
                                                echo $count['total'] . ' bác sĩ';
                                                ?>
                                            </div>
                                        </a>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    <?php } ?>

                    <?php if ($booking_step == 2 && $selected_spec) { ?>
                        <div class="data-table-container">
                            <div class="data-table-header">
                                <h3 class="data-table-title"><i class="fas fa-user-md"></i> Chọn bác sĩ</h3>
                                <a href="?page=book-appointment&step=1" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-arrow-left"></i> Quay lại
                                </a>
                            </div>
                            <div class="p-4">
                                <?php if (count($doctors) > 0) { ?>
                                    <div class="doctors-grid">
                                        <?php foreach ($doctors as $doctor) { ?>
                                            <a href="?page=book-appointment&step=3&spec_id=<?php echo $selected_spec; ?>&doctor_id=<?php echo $doctor['id']; ?>" class="doctor-card" style="text-decoration: none; color: inherit;">
                                                <div class="doctor-avatar">
                                                    <?php echo strtoupper(substr($doctor['fullname'], 0, 1)); ?>
                                                </div>
                                                <div class="doctor-name">BS. <?php echo htmlspecialchars($doctor['fullname']); ?></div>
                                                <div class="doctor-spec"><?php echo htmlspecialchars($doctor['spec_name']); ?></div>
                                                <?php if ($doctor['experience_years']) { ?>
                                                    <div class="doctor-spec">
                                                        <i class="fas fa-briefcase"></i> <?php echo $doctor['experience_years']; ?> năm kinh nghiệm
                                                    </div>
                                                <?php } ?>
                                                <div class="doctor-fee">
                                                    <?php echo number_format($doctor['docFees']); ?> VNĐ
                                                </div>
                                            </a>
                                        <?php } ?>
                                    </div>
                                <?php } else { ?>
                                    <div class="empty-state">
                                        <i class="fas fa-user-md"></i>
                                        <h4>Không có bác sĩ nào</h4>
                                        <p>Hiện tại chưa có bác sĩ nào trong chuyên khoa này</p>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                    <?php if ($booking_step == 3 && $selected_doctor) { ?>
                        <div class="data-table-container">
                            <div class="data-table-header">
                                <h3 class="data-table-title"><i class="fas fa-calendar"></i> Chọn ngày khám</h3>
                                <a href="?page=book-appointment&step=2&spec_id=<?php echo $selected_spec; ?>" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-arrow-left"></i> Quay lại
                                </a>
                            </div>
                            <div class="p-4">
                                <div class="row justify-content-center">
                                    <div class="col-md-8">
                                        <form method="GET" action="" class="card p-4">
                                            <input type="hidden" name="page" value="book-appointment">
                                            <input type="hidden" name="step" value="4">
                                            <input type="hidden" name="spec_id" value="<?php echo $selected_spec; ?>">
                                            <input type="hidden" name="doctor_id" value="<?php echo $selected_doctor; ?>">

                                            <h5 class="mb-4"><i class="fas fa-calendar-alt"></i> Chọn ngày khám bệnh</h5>

                                            <?php
                                            
                                            $current_year = date('Y');
                                            $current_month = date('m');
                                            $current_day = date('d');
                                            ?>

                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label class="font-weight-bold"><i class="fas fa-calendar-day"></i> Ngày</label>
                                                        <select name="day" class="form-control form-control-lg" required>
                                                            <option value="">-- Chọn ngày --</option>
                                                            <?php for ($d = 1; $d <= 31; $d++) { ?>
                                                                <option value="<?php echo str_pad($d, 2, '0', STR_PAD_LEFT); ?>">
                                                                    <?php echo $d; ?>
                                                                </option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label class="font-weight-bold"><i class="fas fa-calendar"></i> Tháng</label>
                                                        <select name="month" class="form-control form-control-lg" required>
                                                            <option value="">-- Chọn tháng --</option>
                                                            <?php
                                                            $months = [
                                                                '01' => 'Tháng 1',
                                                                '02' => 'Tháng 2',
                                                                '03' => 'Tháng 3',
                                                                '04' => 'Tháng 4',
                                                                '05' => 'Tháng 5',
                                                                '06' => 'Tháng 6',
                                                                '07' => 'Tháng 7',
                                                                '08' => 'Tháng 8',
                                                                '09' => 'Tháng 9',
                                                                '10' => 'Tháng 10',
                                                                '11' => 'Tháng 11',
                                                                '12' => 'Tháng 12'
                                                            ];
                                                            foreach ($months as $num => $name) { ?>
                                                                <option value="<?php echo $num; ?>">
                                                                    <?php echo $name; ?>
                                                                </option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label class="font-weight-bold"><i class="fas fa-calendar-alt"></i> Năm</label>
                                                        <select name="year" class="form-control form-control-lg" required>
                                                            <option value="">-- Chọn năm --</option>
                                                            <?php for ($y = $current_year; $y <= $current_year + 1; $y++) { ?>
                                                                <option value="<?php echo $y; ?>">
                                                                    <?php echo $y; ?>
                                                                </option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="alert alert-info mt-3">
                                                <i class="fas fa-info-circle"></i>
                                                Vui lòng chọn ngày từ hôm nay trở đi. Bác sĩ có thể không làm việc vào một số ngày trong tuần.
                                            </div>

                                            <button type="submit" class="btn btn-primary btn-block btn-lg mt-3">
                                                <i class="fas fa-arrow-right"></i> Xem lịch trống
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>

                    <?php if ($booking_step == 4 && $selected_doctor && $selected_date) {
                        $doc_info_stmt = $pdo->prepare("SELECT username, fullname, docFees FROM doctb WHERE id = :id");
                        $doc_info_stmt->execute([':id' => $selected_doctor]);
                        $doc_info = $doc_info_stmt->fetch(PDO::FETCH_ASSOC);
                    ?>
                        <div class="data-table-container">
                            <div class="data-table-header">
                                <h3 class="data-table-title"><i class="fas fa-clock"></i> Chọn giờ khám</h3>
                                <a href="?page=book-appointment&step=3&spec_id=<?php echo $selected_spec; ?>&doctor_id=<?php echo $selected_doctor; ?>" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-arrow-left"></i> Quay lại
                                </a>
                            </div>

                            <div class="p-4">
                                <div class="alert alert-info">
                                    <strong><i class="fas fa-calendar-day"></i> Ngày khám:</strong>
                                    <?php echo date('d/m/Y', strtotime($selected_date)); ?>
                                    (<?php
                                        $days = ['Chủ nhật', 'Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7'];
                                        echo $days[date('w', strtotime($selected_date))];
                                        ?>)
                                    <br>
                                    <strong><i class="fas fa-user-md"></i> Bác sĩ:</strong> BS. <?php echo htmlspecialchars($doc_info['fullname']); ?>
                                </div>

                                <?php if ($schedule_info && count($time_slots) > 0) { ?>
                                    <div class="time-slots-container">
                                        <h5 class="mb-3">
                                            <i class="fas fa-clock"></i>
                                            Lịch làm việc: <?php echo date('H:i', strtotime($schedule_info['start_time'])); ?> -
                                            <?php echo date('H:i', strtotime($schedule_info['end_time'])); ?>
                                        </h5>

                                        <div class="slots-grid">
                                            <?php foreach ($time_slots as $slot) {
                                                $slot_class = 'time-slot ' . $slot['status'];
                                                $is_disabled = ($slot['status'] !== 'available');
                                            ?>
                                                <div class="<?php echo $slot_class; ?>"
                                                    id="slot-<?php echo $slot['id']; ?>"
                                                    <?php if (!$is_disabled) { ?>
                                                    onclick="selectSlot(<?php echo $slot['id']; ?>, '<?php echo $slot['slot_time']; ?>', '<?php echo $slot['slot_time_full']; ?>')"
                                                    <?php } ?>>
                                                    <span class="slot-time"><?php echo $slot['slot_time']; ?></span>
                                                    <span class="slot-status">
                                                        <?php
                                                        if ($slot['status'] === 'available') echo 'Còn trống';
                                                        elseif ($slot['status'] === 'booked') echo 'Đã đặt';
                                                        elseif ($slot['status'] === 'blocked') echo 'Đã qua';
                                                        ?>
                                                    </span>
                                                </div>
                                            <?php } ?>
                                        </div>

                                        <div class="mt-3">
                                            <small class="text-muted">
                                                <i class="fas fa-circle text-success"></i> Còn trống &nbsp;&nbsp;
                                                <i class="fas fa-circle text-secondary"></i> Đã đặt &nbsp;&nbsp;
                                                <i class="fas fa-circle text-danger"></i> Đã qua giờ
                                            </small>
                                        </div>
                                    </div>

                                    
                                    <form method="POST" action="" id="booking-form" class="booking-summary mt-4" style="display: none;">
                                        <h4 class="mb-3">
                                            <i class="fas fa-file-medical"></i> Xác nhận thông tin đặt lịch
                                        </h4>

                                        <div class="summary-item">
                                            <span><i class="fas fa-user-md"></i> Bác sĩ:</span>
                                            <strong>BS. <?php echo htmlspecialchars($doc_info['fullname']); ?></strong>
                                        </div>

                                        <div class="summary-item">
                                            <span><i class="fas fa-calendar-alt"></i> Ngày khám:</span>
                                            <strong><?php echo date('d/m/Y', strtotime($selected_date)); ?></strong>
                                        </div>

                                        <div class="summary-item">
                                            <span><i class="fas fa-clock"></i> Giờ khám:</span>
                                            <strong id="selected-time-display">--:--</strong>
                                        </div>

                                        <div class="summary-item">
                                            <span><i class="fas fa-money-bill-wave"></i> Chi phí khám:</span>
                                            <strong class="text-success"><?php echo number_format($doc_info['docFees']); ?> VNĐ</strong>
                                        </div>

                                        <input type="hidden" name="doctor_id" value="<?php echo $selected_doctor; ?>">
                                        <input type="hidden" name="slot_id" id="selected-slot-id" value="">
                                        <input type="hidden" name="appdate" value="<?php echo $selected_date; ?>">
                                        <input type="hidden" name="apptime" id="selected-time-value" value="">

                                        <button type="submit" name="app-submit" class="btn btn-primary btn-block btn-lg mt-4">
                                            <i class="fas fa-check-circle"></i> Xác nhận đặt lịch
                                        </button>
                                    </form>

                                <?php } elseif ($schedule_info) { ?>
                                    <div class="empty-state">
                                        <i class="fas fa-calendar-times"></i>
                                        <h4>Không có lịch trống</h4>
                                        <p>Bác sĩ không có lịch trống trong ngày này. Vui lòng chọn ngày khác.</p>
                                        <a href="?page=book-appointment&step=3&spec_id=<?php echo $selected_spec; ?>&doctor_id=<?php echo $selected_doctor; ?>" class="btn btn-primary mt-3">
                                            <i class="fas fa-arrow-left"></i> Chọn ngày khác
                                        </a>
                                    </div>
                                <?php } else { ?>
                                    <div class="empty-state">
                                        <i class="fas fa-calendar-times"></i>
                                        <h4>Bác sĩ không làm việc</h4>
                                        <p>Bác sĩ không làm việc vào ngày này trong tuần. Vui lòng chọn ngày khác.</p>
                                        <a href="?page=book-appointment&step=3&spec_id=<?php echo $selected_spec; ?>&doctor_id=<?php echo $selected_doctor; ?>" class="btn btn-primary mt-3">
                                            <i class="fas fa-arrow-left"></i> Chọn ngày khác
                                        </a>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                </section>
            <?php } ?>

            
            <?php if ($page === 'appointment-history') { ?>
                <section class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Lịch sử khám bệnh</h2>
                        <p class="section-subtitle">Xem và quản lý các lịch hẹn của bạn</p>
                    </div>

                    <div class="data-table-container">
                        <div class="data-table-header">
                            <h3 class="data-table-title">Danh sách lịch hẹn</h3>
                        </div>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Bác sĩ</th>
                                    <th>Chi phí</th>
                                    <th>Ngày khám</th>
                                    <th>Giờ khám</th>
                                    <th>Trạng thái</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stmt = $pdo->prepare("
                                    SELECT a.ID, COALESCE(d.fullname, a.doctor) as doctor_fullname, a.docFees, a.appdate, a.apptime, a.userStatus, a.doctorStatus
                                    FROM appointmenttb a
                                    LEFT JOIN doctb d ON a.doctor = d.fullname OR a.doctor = d.username
                                    WHERE a.fname = :fname AND a.lname = :lname 
                                    ORDER BY a.appdate DESC, a.apptime DESC
                                ");
                                $stmt->execute([':fname' => $fname, ':lname' => $lname]);

                                if ($stmt->rowCount() == 0) {
                                    echo '<tr><td colspan="6" class="text-center py-5">
                                        <div class="empty-state">
                                            <i class="fas fa-calendar-times"></i>
                                            <p>Bạn chưa có lịch hẹn nào</p>
                                            <a href="?page=book-appointment" class="btn btn-primary mt-3">Đặt lịch ngay</a>
                                        </div>
                                    </td></tr>';
                                }

                                while ($row = $stmt->fetch(PDO::FETCH_BOTH)) {
                                ?>
                                    <tr>
                                        <td>Bác sĩ <?php echo $row['doctor_fullname']; ?></td>
                                        <td><?php echo number_format($row['docFees']); ?> VNĐ</td>
                                        <td><?php echo date('d/m/Y', strtotime($row['appdate'])); ?></td>
                                        <td><?php echo date('H:i', strtotime($row['apptime'])); ?></td>
                                        <td>
                                            <?php
                                            if (($row['userStatus'] == 1) && ($row['doctorStatus'] == 1)) {
                                                echo '<span class="badge badge-success">Đang hoạt động</span>';
                                            }
                                            if (($row['userStatus'] == 0) && ($row['doctorStatus'] == 1)) {
                                                echo '<span class="badge badge-danger">Đã hủy bởi bạn</span>';
                                            }
                                            if (($row['userStatus'] == 1) && ($row['doctorStatus'] == 0)) {
                                                echo '<span class="badge badge-warning">Đã hủy bởi bác sĩ</span>';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php if (($row['userStatus'] == 1) && ($row['doctorStatus'] == 1)) { ?>
                                                <a href="?page=appointment-history&ID=<?php echo $row['ID'] ?>&cancel=update"
                                                    onclick="return confirm('Bạn có chắc muốn hủy lịch hẹn này?')"
                                                    class="btn btn-danger btn-sm">
                                                    <i class="fas fa-times"></i> Hủy lịch
                                                </a>
                                            <?php } else {
                                                echo '<span class="text-muted">Đã hủy</span>';
                                            } ?>
                                        </td>
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
                        <h2 class="section-title">Đơn thuốc</h2>
                        <p class="section-subtitle">Xem và tải xuống đơn thuốc của bạn</p>
                    </div>

                    <div class="data-table-container">
                        <div class="data-table-header">
                            <h3 class="data-table-title">Danh sách đơn thuốc</h3>
                        </div>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Bác sĩ</th>
                                    <th>Mã lịch hẹn</th>
                                    <th>Ngày khám</th>
                                    <th>Giờ khám</th>
                                    <th>Chẩn đoán</th>
                                    <th>Dị ứng</th>
                                    <th>Đơn thuốc</th>
                                    <th>Hóa đơn</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stmt = $pdo->prepare("SELECT doctor,ID,appdate,apptime,disease,allergy,prescription FROM prestb WHERE pid = :pid ORDER BY appdate DESC");
                                $stmt->execute([':pid' => $pid]);

                                if ($stmt->rowCount() == 0) {
                                    echo '<tr><td colspan="8" class="text-center py-5">
                                        <div class="empty-state">
                                            <i class="fas fa-file-prescription"></i>
                                            <p>Bạn chưa có đơn thuốc nào</p>
                                        </div>
                                    </td></tr>';
                                }

                                while ($row = $stmt->fetch(PDO::FETCH_BOTH)) {
                                ?>
                                    <tr>
                                        <td>Bác sĩ <?php echo $row['doctor']; ?></td>
                                        <td>#<?php echo $row['ID']; ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($row['appdate'])); ?></td>
                                        <td><?php echo date('H:i', strtotime($row['apptime'])); ?></td>
                                        <td><?php echo $row['disease']; ?></td>
                                        <td><?php echo $row['allergy'] ?: 'Không có'; ?></td>
                                        <td><?php echo $row['prescription']; ?></td>
                                        <td>
                                            <a href="?page=prescriptions&ID=<?php echo $row['ID'] ?>&generate_bill=true"
                                                class="btn btn-success btn-sm">
                                                <i class="fas fa-file-pdf"></i> Tải hóa đơn
                                            </a>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            <?php } ?>

            <?php if ($page === 'profile') {
                $patient_stmt = $pdo->prepare("SELECT * FROM patreg WHERE pid = :pid");
                $patient_stmt->execute([':pid' => $pid]);
                $patient = $patient_stmt->fetch(PDO::FETCH_ASSOC);
            ?>
                <section class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Hồ sơ cá nhân</h2>
                        <p class="section-subtitle">Quản lý thông tin cá nhân và tài khoản của bạn</p>
                    </div>

                    <div class="profile-container">
                        <div class="profile-card" style="background: linear-gradient(135deg, #d2302c 0%, #ff4d4d 100%); color: white; border-radius: 12px; padding: 2rem; margin-bottom: 2rem; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);">
                            <div style="display: flex; align-items: center; gap: 2rem;">
                                <div style="width: 120px; height: 120px; border-radius: 50%; background: white; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: bold; color: #d2302c;">
                                    <?php echo strtoupper(substr($fname, 0, 1) . substr($lname, 0, 1)); ?>
                                </div>
                                <div>
                                    <h3 style="font-size: 1.5rem; margin: 0; font-weight: 700;"><?php echo htmlspecialchars($lname . ' ' . $fname); ?></h3>
                                    <p style="margin: 0.5rem 0 0 0; opacity: 0.9;"><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($email); ?></p>
                                    <p style="margin: 0.25rem 0 0 0; opacity: 0.9;"><i class="fas fa-phone"></i> <?php echo htmlspecialchars($contact); ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="data-table-container">
                            <div class="data-table-header">
                                <h3 class="data-table-title">Thông tin chi tiết</h3>
                            </div>
                            <div style="padding: 2rem;">
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                                    <div>
                                        <h5 style="font-weight: 600; color: #6b7280; font-size: 0.875rem; margin-bottom: 1rem; text-transform: uppercase;">Thông tin cá nhân</h5>
                                        <div style="background: #f9fafb; padding: 1rem; border-radius: 8px; margin-bottom: 0.75rem;">
                                            <p style="margin: 0 0 0.5rem 0; color: #6b7280; font-size: 0.875rem;">Họ:</p>
                                            <p style="margin: 0; font-weight: 600; color: #111827;"><?php echo htmlspecialchars($fname); ?></p>
                                        </div>
                                        <div style="background: #f9fafb; padding: 1rem; border-radius: 8px; margin-bottom: 0.75rem;">
                                            <p style="margin: 0 0 0.5rem 0; color: #6b7280; font-size: 0.875rem;">Tên:</p>
                                            <p style="margin: 0; font-weight: 600; color: #111827;"><?php echo htmlspecialchars($lname); ?></p>
                                        </div>
                                        <div style="background: #f9fafb; padding: 1rem; border-radius: 8px;">
                                            <p style="margin: 0 0 0.5rem 0; color: #6b7280; font-size: 0.875rem;">Giới tính:</p>
                                            <p style="margin: 0; font-weight: 600; color: #111827;">
                                                <?php
                                                $gender_display = ['Male' => 'Nam', 'Female' => 'Nữ', 'Other' => 'Khác'];
                                                echo $gender_display[$patient['gender'] ?? ''] ?? 'Chưa cập nhật';
                                                ?>
                                            </p>
                                        </div>
                                    </div>

                                    <div>
                                        <h5 style="font-weight: 600; color: #6b7280; font-size: 0.875rem; margin-bottom: 1rem; text-transform: uppercase;">Thông tin liên hệ</h5>
                                        <div style="background: #f9fafb; padding: 1rem; border-radius: 8px; margin-bottom: 0.75rem;">
                                            <p style="margin: 0 0 0.5rem 0; color: #6b7280; font-size: 0.875rem;">Email:</p>
                                            <p style="margin: 0; font-weight: 600; color: #111827;"><?php echo htmlspecialchars($patient['email'] ?? $email); ?></p>
                                        </div>
                                        <div style="background: #f9fafb; padding: 1rem; border-radius: 8px; margin-bottom: 0.75rem;">
                                            <p style="margin: 0 0 0.5rem 0; color: #6b7280; font-size: 0.875rem;">Số điện thoại:</p>
                                            <p style="margin: 0; font-weight: 600; color: #111827;"><?php echo htmlspecialchars($patient['contact'] ?? $contact); ?></p>
                                        </div>
                                        <div style="background: #f9fafb; padding: 1rem; border-radius: 8px;">
                                            <p style="margin: 0 0 0.5rem 0; color: #6b7280; font-size: 0.875rem;">Địa chỉ:</p>
                                            <p style="margin: 0; font-weight: 600; color: #111827;"><?php echo htmlspecialchars($patient['address'] ?? 'Chưa cập nhật'); ?></p>
                                        </div>
                                    </div>

                                    <div>
                                        <h5 style="font-weight: 600; color: #6b7280; font-size: 0.875rem; margin-bottom: 1rem; text-transform: uppercase;">Thông tin y tế</h5>
                                        <div style="background: #f9fafb; padding: 1rem; border-radius: 8px; margin-bottom: 0.75rem;">
                                            <p style="margin: 0 0 0.5rem 0; color: #6b7280; font-size: 0.875rem;">Ngày sinh:</p>
                                            <p style="margin: 0; font-weight: 600; color: #111827;">
                                                <?php
                                                if (isset($patient['date_of_birth']) && $patient['date_of_birth']) {
                                                    echo date('d/m/Y', strtotime($patient['date_of_birth']));
                                                } else {
                                                    echo 'Chưa cập nhật';
                                                }
                                                ?>
                                            </p>
                                        </div>
                                        <div style="background: #f9fafb; padding: 1rem; border-radius: 8px; margin-bottom: 0.75rem;">
                                            <p style="margin: 0 0 0.5rem 0; color: #6b7280; font-size: 0.875rem;">Nhóm máu:</p>
                                            <p style="margin: 0; font-weight: 600; color: #111827;"><?php echo htmlspecialchars($patient['blood_group'] ?? 'Chưa cập nhật'); ?></p>
                                        </div>
                                        <div style="background: #f9fafb; padding: 1rem; border-radius: 8px;">
                                            <p style="margin: 0 0 0.5rem 0; color: #6b7280; font-size: 0.875rem;">Liên hệ khẩn cấp:</p>
                                            <p style="margin: 0; font-weight: 600; color: #111827;">
                                                <?php
                                                if (isset($patient['emergency_contact_name']) && $patient['emergency_contact_name']) {
                                                    echo htmlspecialchars($patient['emergency_contact_name']);
                                                    if (isset($patient['emergency_contact']) && $patient['emergency_contact']) {
                                                        echo ' - ' . htmlspecialchars($patient['emergency_contact']);
                                                    }
                                                } else {
                                                    echo 'Chưa cập nhật';
                                                }
                                                ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                            <a href="profile.php" class="btn" style="background: linear-gradient(135deg, #d2302c, #ff4d4d); color: white; padding: 0.75rem 1.5rem; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; font-weight: 600;">
                                <i class="fas fa-edit"></i> Chỉnh sửa hồ sơ
                            </a>
                            <a href="profile.php" class="btn" style="background: #f3f4f6; color: #111827; padding: 0.75rem 1.5rem; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; font-weight: 600; border: 1px solid #e5e7eb;">
                                <i class="fas fa-password"></i> Đổi mật khẩu
                            </a>
                        </div>
                    </div>
                </section>
            <?php } ?>
            <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
            <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

            <script>
                function selectSlot(slotId, slotTime, slotTimeFull) {
                    document.querySelectorAll('.time-slot').forEach(slot => {
                        slot.classList.remove('selected');
                    });

                    document.getElementById('slot-' + slotId).classList.add('selected');

                    document.getElementById('selected-slot-id').value = slotId;
                    document.getElementById('selected-time-value').value = slotTimeFull;
                    document.getElementById('selected-time-display').textContent = slotTime;

                    document.getElementById('booking-form').style.display = 'block';

                    document.getElementById('booking-form').scrollIntoView({
                        behavior: 'smooth',
                        block: 'nearest'
                    });
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