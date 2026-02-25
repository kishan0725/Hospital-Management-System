<?php
session_start();
require_once('../../config.php');
require_once('../../TCPDF/tcpdf.php');


if (!isset($_GET['id'])) {
    die('Prescription ID not provided');
}

$prescription_id = $_GET['id'];


$stmt = $pdo->prepare("
    SELECT p.*, 
           p.fname, p.lname,
           pat.email, pat.gender, pat.contact,
           d.fullname as doctor_name, 
           CASE d.spec 
               WHEN 'Pediatrics' THEN 'Nhi khoa'
               WHEN 'Obstetrics_Gynecology' THEN 'Sơ sinh - Phụ khoa'
               WHEN 'Dermatology' THEN 'Da liễu'
               WHEN 'Gastroenterology' THEN 'Tiêu hóa'
               WHEN 'Rheumatology' THEN 'Cơ xương khớp'
               WHEN 'ENT' THEN 'Tai Mũi Họng'
               WHEN 'Oncology' THEN 'Ung bướu'
               WHEN 'Cardiology' THEN 'Tim mạch'
               WHEN 'Orthopedics' THEN 'Chấn thương chỉnh hình'
               WHEN 'Dentistry' THEN 'Nha khoa'
               WHEN 'Endocrinology' THEN 'Nội tiết'
               WHEN 'Psychiatry' THEN 'Tâm thần'
               WHEN 'Pulmonology' THEN 'Hô hấp'
               WHEN 'Neurology' THEN 'Thần kinh'
               WHEN 'Traditional_Medicine' THEN 'Y học cổ truyền'
               WHEN 'Ophthalmology' THEN 'Mắt'
               WHEN 'Internal_Medicine' THEN 'Nội khoa'
               WHEN 'Allergy_Immunology' THEN 'Dị ứng - Miễn dịch'
               WHEN 'Anesthesiology' THEN 'Gây mê hồi sức'
               WHEN 'Geriatrics' THEN 'Lão khoa'
               WHEN 'Emergency_Medicine' THEN 'Cấp cứu'
               WHEN 'General_Surgery' THEN 'Ngoại khoa'
               WHEN 'Preventive_Medicine' THEN 'Y học dự phòng'
               WHEN 'Infectious_Disease' THEN 'Truyền nhiễm'
               WHEN 'Nephrology' THEN 'Thận học'
               WHEN 'Laboratory' THEN 'Xét nghiệm'
               WHEN 'Hematology' THEN 'Huyết học'
               ELSE d.spec
           END as doctor_spec
    FROM prestb p
    JOIN patreg pat ON p.pid = pat.pid
    LEFT JOIN doctb d ON TRIM(p.doctor) = TRIM(d.fullname)
    WHERE p.pres_id = ?
");
$stmt->execute([$prescription_id]);
$prescription = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$prescription) {
    die('Prescription not found');
}


$med_stmt = $pdo->prepare("
    SELECT * FROM prescription_medications 
    WHERE prescription_id = ?
    ORDER BY id
");
$med_stmt->execute([$prescription_id]);
$medications = $med_stmt->fetchAll(PDO::FETCH_ASSOC);


$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);


$pdf->SetCreator('Bệnh viện D.B.D');
$pdf->SetAuthor($prescription['doctor_name']);
$pdf->SetTitle('Medical Prescription');
$pdf->SetSubject('Prescription for ' . $prescription['fname'] . ' ' . $prescription['lname']);


$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);


$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(TRUE, 15);


$pdf->AddPage();


$pdf->SetFont('dejavusans', '', 10);


$pdf->SetFillColor(8, 145, 178);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('dejavusans', 'B', 18);
$pdf->Cell(0, 15, 'BỆNH VIỆN D.B.D', 0, 1, 'C', true);

$pdf->SetFont('dejavusans', '', 10);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(0, 6, 'Dịch vụ Chăm sóc Sức khỏe Chuyên nghiệp', 0, 1, 'C', true);
$pdf->Cell(0, 6, 'Address: Hanoi, Vietnam | Phone: (84) 123-456-789', 0, 1, 'C', true);

$pdf->Ln(10);


$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('dejavusans', 'B', 16);
$pdf->Cell(0, 10, 'ĐƠN THUỐC Y TẾ', 0, 1, 'C');
$pdf->Ln(5);


$pdf->SetFont('dejavusans', '', 10);
$pdf->Cell(0, 6, 'Ngày: ' . date('d/m/Y', strtotime($prescription['created_at'])), 0, 1, 'R');
$pdf->Ln(3);


$pdf->SetFillColor(240, 249, 255);
$pdf->SetFont('dejavusans', 'B', 11);
$pdf->Cell(0, 8, 'Thông tin Bác sĩ', 0, 1, 'L', true);
$pdf->SetFont('dejavusans', '', 10);
$pdf->Cell(50, 6, 'Tên bác sĩ:', 0, 0);
$pdf->Cell(0, 6, $prescription['doctor_name'], 0, 1);
$pdf->Cell(50, 6, 'Chuyên khoa:', 0, 0);
$pdf->Cell(0, 6, $prescription['doctor_spec'], 0, 1);
$pdf->Ln(5);


$pdf->SetFillColor(240, 249, 255);
$pdf->SetFont('dejavusans', 'B', 11);
$pdf->Cell(0, 8, 'Thông tin Bệnh nhân', 0, 1, 'L', true);
$pdf->SetFont('dejavusans', '', 10);
$pdf->Cell(50, 6, 'Tên bệnh nhân:', 0, 0);
$pdf->Cell(0, 6, $prescription['fname'] . ' ' . $prescription['lname'], 0, 1);
$pdf->Cell(50, 6, 'Giới tính:', 0, 0);
$pdf->Cell(0, 6, $prescription['gender'], 0, 1);
$pdf->Cell(50, 6, 'Liên hệ:', 0, 0);
$pdf->Cell(0, 6, $prescription['contact'], 0, 1);
$pdf->Ln(5);


$pdf->SetFillColor(240, 249, 255);
$pdf->SetFont('dejavusans', 'B', 11);
$pdf->Cell(0, 8, 'Chẩn đoán', 0, 1, 'L', true);
$pdf->SetFont('dejavusans', '', 10);
$pdf->Cell(50, 6, 'Bệnh:', 0, 0);
$pdf->Cell(0, 6, $prescription['disease'], 0, 1);
if (!empty($prescription['allergy'])) {
    $pdf->SetTextColor(239, 68, 68);
    $pdf->Cell(50, 6, 'Dị ứng:', 0, 0);
    $pdf->SetFont('dejavusans', 'B', 10);
    $pdf->Cell(0, 6, $prescription['allergy'], 0, 1);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('dejavusans', '', 10);
}
$pdf->Cell(50, 6, 'Thời gian điều trị:', 0, 0);
$pdf->Cell(0, 6, $prescription['treatment_duration'], 0, 1);
$pdf->Ln(5);


$pdf->SetFillColor(8, 145, 178);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('dejavusans', 'B', 11);
$pdf->Cell(0, 8, 'Danh sách Thuốc được Kê đơn', 0, 1, 'L', true);
$pdf->Ln(2);


$pdf->SetFillColor(14, 116, 144);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('dejavusans', 'B', 9);
$pdf->Cell(10, 8, '#', 1, 0, 'C', true);
$pdf->Cell(50, 8, 'Thuốc', 1, 0, 'C', true);
$pdf->Cell(30, 8, 'Liều lượng', 1, 0, 'C', true);
$pdf->Cell(40, 8, 'Tần suất', 1, 0, 'C', true);
$pdf->Cell(25, 8, 'Thời gian', 1, 0, 'C', true);
$pdf->Cell(25, 8, 'Ghi chú', 1, 1, 'C', true);


$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('dejavusans', '', 9);
foreach ($medications as $index => $med) {
    $pdf->Cell(10, 8, ($index + 1), 1, 0, 'C');
    $pdf->Cell(50, 8, $med['medication_name'], 1, 0, 'L');
    $pdf->Cell(30, 8, $med['dosage'], 1, 0, 'L');
    $pdf->Cell(40, 8, $med['frequency'], 1, 0, 'L');
    $pdf->Cell(25, 8, $med['duration'], 1, 0, 'C');
    $pdf->Cell(25, 8, !empty($med['special_notes']) ? 'Có' : '-', 1, 1, 'C');

    
    if (!empty($med['special_notes'])) {
        $pdf->SetFont('dejavusans', 'I', 8);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(10, 6, '', 0, 0);
        $pdf->Cell(0, 6, 'Ghi chú: ' . $med['special_notes'], 0, 1);
        $pdf->SetFont('dejavusans', '', 9);
        $pdf->SetTextColor(0, 0, 0);
    }
}

$pdf->Ln(5);


if (!empty($prescription['general_notes'])) {
    $pdf->SetFillColor(240, 249, 255);
    $pdf->SetFont('dejavusans', 'B', 11);
    $pdf->Cell(0, 8, 'Hướng dẫn chung', 0, 1, 'L', true);
    $pdf->SetFont('dejavusans', '', 10);
    $pdf->MultiCell(0, 6, $prescription['general_notes'], 0, 'L');
    $pdf->Ln(5);
}


$pdf->Ln(10);
$pdf->Cell(0, 6, '___________________________', 0, 1, 'R');
$pdf->SetFont('dejavusans', 'B', 10);
$pdf->Cell(0, 6, $prescription['doctor_name'], 0, 1, 'R');
$pdf->SetFont('dejavusans', '', 9);
$pdf->Cell(0, 6, $prescription['doctor_spec'], 0, 1, 'R');


$pdf->SetY(-20);
$pdf->SetFont('dejavusans', 'I', 8);
$pdf->SetTextColor(128, 128, 128);
$pdf->Cell(0, 6, 'Đây là đơn thuốc được tạo bằng máy tính và có hiệu lực mà không cần chữ ký.', 0, 1, 'C');
$pdf->Cell(0, 6, 'Bệnh viện Toàn cầu | Email: info@globalhospitals.com | Cấp cứu: (84) 123-456-789', 0, 1, 'C');


$filename = 'Prescription_' . $prescription['fname'] . '_' . $prescription['lname'] . '_' . date('Ymd') . '.pdf';
$pdf->Output($filename, 'I'); 
