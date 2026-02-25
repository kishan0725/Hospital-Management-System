<?php
require_once(__DIR__ . '/../TCPDF/tcpdf.php');


class CustomPDF extends TCPDF
{
    public function Header()
    {
        $this->SetFont('dejavusans', 'B', 16);
        $this->Cell(0, 15, 'Bệnh viện Global', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(10);
    }

    public function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('dejavusans', 'I', 8);
        $this->Cell(0, 10, 'Trang ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}


function exportPatientsList($pdo)
{
    $pdf = new CustomPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Bệnh viện Global');
    $pdf->SetTitle('Danh sách Bệnh nhân');
    $pdf->SetSubject('Danh sách Bệnh nhân');

    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    $pdf->SetMargins(15, 30, 15);
    $pdf->SetAutoPageBreak(TRUE, 25);
    $pdf->SetFont('dejavusans', '', 10);

    $pdf->AddPage();

    $html = '<h2 style="text-align:center; color:#0891b2;">Danh sách Bệnh nhân</h2>';
    $html .= '<p style="text-align:center; font-size:10px;">Ngày xuất: ' . date('d/m/Y H:i') . '</p><br>';

    $html .= '<table border="1" cellpadding="5" style="border-collapse: collapse; width: 100%;">
        <thead>
            <tr style="background-color: #0891b2; color: white; font-weight: bold;">
                <th width="8%">ID</th>
                <th width="25%">Họ tên</th>
                <th width="10%">Giới tính</th>
                <th width="20%">Email</th>
                <th width="15%">SĐT</th>
                <th width="22%">Ngày đăng ký</th>
            </tr>
        </thead>
        <tbody>';

    $stmt = $pdo->query("SELECT pid, fname, lname, gender, email, contact, created_at FROM patreg ORDER BY pid DESC");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $html .= '<tr>
            <td>' . $row['pid'] . '</td>
            <td>' . htmlspecialchars($row['lname'] . ' ' . $row['fname']) . '</td>
            <td>' . htmlspecialchars($row['gender']) . '</td>
            <td>' . htmlspecialchars($row['email']) . '</td>
            <td>' . htmlspecialchars($row['contact']) . '</td>
            <td>' . date('d/m/Y', strtotime($row['created_at'])) . '</td>
        </tr>';
    }

    $html .= '</tbody></table>';

    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output('danh-sach-benh-nhan.pdf', 'I');
}


function exportDoctorsList($pdo)
{
    $pdf = new CustomPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Bệnh viện Global');
    $pdf->SetTitle('Danh sách Bác sĩ');
    $pdf->SetSubject('Danh sách Bác sĩ');

    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    $pdf->SetMargins(15, 30, 15);
    $pdf->SetAutoPageBreak(TRUE, 25);
    $pdf->SetFont('dejavusans', '', 10);

    $pdf->AddPage();

    $html = '<h2 style="text-align:center; color:#0891b2;">Danh sách Bác sĩ</h2>';
    $html .= '<p style="text-align:center; font-size:10px;">Ngày xuất: ' . date('d/m/Y H:i') . '</p><br>';

    $html .= '<table border="1" cellpadding="5" style="border-collapse: collapse; width: 100%;">
        <thead>
            <tr style="background-color: #0891b2; color: white; font-weight: bold;">
                <th width="8%">ID</th>
                <th width="30%">Họ tên</th>
                <th width="25%">Chuyên khoa</th>
                <th width="20%">Email</th>
                <th width="17%">Phí khám</th>
            </tr>
        </thead>
        <tbody>';

    $stmt = $pdo->query("SELECT d.id, d.fullname, d.email, d.docFees, s.name_vi as spec_name 
                         FROM doctb d 
                         LEFT JOIN specializations s ON d.spec_id = s.id 
                         ORDER BY d.id DESC");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $html .= '<tr>
            <td>' . $row['id'] . '</td>
            <td>' . htmlspecialchars($row['fullname']) . '</td>
            <td>' . htmlspecialchars($row['spec_name'] ?? 'N/A') . '</td>
            <td>' . htmlspecialchars($row['email']) . '</td>
            <td>' . number_format($row['docFees']) . ' VNĐ</td>
        </tr>';
    }

    $html .= '</tbody></table>';

    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output('danh-sach-bac-si.pdf', 'I');
}


function exportRevenueReport($pdo)
{
    $pdf = new CustomPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Bệnh viện Global');
    $pdf->SetTitle('Báo cáo Doanh thu');
    $pdf->SetSubject('Báo cáo Doanh thu');

    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    $pdf->SetMargins(15, 30, 15);
    $pdf->SetAutoPageBreak(TRUE, 25);
    $pdf->SetFont('dejavusans', '', 10);

    $pdf->AddPage();

    $html = '<h2 style="text-align:center; color:#0891b2;">Báo cáo Doanh thu</h2>';
    $html .= '<p style="text-align:center; font-size:10px;">Ngày xuất: ' . date('d/m/Y H:i') . '</p><br>';

    
    $html .= '<h3 style="color:#0891b2;">Doanh thu theo tháng</h3>';
    $html .= '<table border="1" cellpadding="5" style="border-collapse: collapse; width: 100%;">
        <thead>
            <tr style="background-color: #0891b2; color: white; font-weight: bold;">
                <th width="30%">Tháng</th>
                <th width="35%">Số lịch khám</th>
                <th width="35%">Doanh thu</th>
            </tr>
        </thead>
        <tbody>';

    $stmt = $pdo->query("SELECT DATE_FORMAT(appdate, '%Y-%m') as month, 
                                COUNT(*) as count, 
                                SUM(docFees) as revenue 
                         FROM appointmenttb 
                         WHERE userStatus = '1' AND doctorStatus = '1'
                         GROUP BY month 
                         ORDER BY month DESC 
                         LIMIT 12");

    $total_revenue = 0;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $total_revenue += $row['revenue'];
        $html .= '<tr>
            <td>' . date('m/Y', strtotime($row['month'] . '-01')) . '</td>
            <td>' . $row['count'] . '</td>
            <td>' . number_format($row['revenue']) . ' VNĐ</td>
        </tr>';
    }

    $html .= '<tr style="background-color: #f0f9ff; font-weight: bold;">
        <td colspan="2">Tổng cộng</td>
        <td>' . number_format($total_revenue) . ' VNĐ</td>
    </tr>';

    $html .= '</tbody></table>';

    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output('bao-cao-doanh-thu.pdf', 'I');
}


function exportAppointmentSchedule($pdo)
{
    $pdf = new CustomPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Bệnh viện Global');
    $pdf->SetTitle('Lịch hẹn');
    $pdf->SetSubject('Lịch hẹn');

    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    $pdf->SetMargins(15, 30, 15);
    $pdf->SetAutoPageBreak(TRUE, 25);
    $pdf->SetFont('dejavusans', '', 9);

    $pdf->AddPage();

    $html = '<h2 style="text-align:center; color:#0891b2;">Lịch hẹn khám bệnh</h2>';
    $html .= '<p style="text-align:center; font-size:10px;">Ngày xuất: ' . date('d/m/Y H:i') . '</p><br>';

    $html .= '<table border="1" cellpadding="4" style="border-collapse: collapse; width: 100%; font-size: 9px;">
        <thead>
            <tr style="background-color: #0891b2; color: white; font-weight: bold;">
                <th width="6%">ID</th>
                <th width="18%">Bệnh nhân</th>
                <th width="18%">Bác sĩ</th>
                <th width="12%">Ngày</th>
                <th width="10%">Giờ</th>
                <th width="10%">SĐT</th>
                <th width="13%">Phí khám</th>
                <th width="13%">Trạng thái</th>
            </tr>
        </thead>
        <tbody>';

    $stmt = $pdo->query("SELECT ID, fname, lname, doctor, appdate, apptime, contact, docFees, userStatus, doctorStatus 
                         FROM appointmenttb 
                         ORDER BY appdate DESC, apptime DESC 
                         LIMIT 100");

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $status = '';
        if ($row['userStatus'] == '1' && $row['doctorStatus'] == '1') {
            $status = 'Đang hoạt động';
        } elseif ($row['userStatus'] == '0') {
            $status = 'BN đã hủy';
        } else {
            $status = 'BS đã hủy';
        }

        $html .= '<tr>
            <td>' . $row['ID'] . '</td>
            <td>' . htmlspecialchars($row['lname'] . ' ' . $row['fname']) . '</td>
            <td>' . htmlspecialchars($row['doctor']) . '</td>
            <td>' . date('d/m/Y', strtotime($row['appdate'])) . '</td>
            <td>' . date('H:i', strtotime($row['apptime'])) . '</td>
            <td>' . htmlspecialchars($row['contact']) . '</td>
            <td>' . number_format($row['docFees']) . ' VNĐ</td>
            <td>' . $status . '</td>
        </tr>';
    }

    $html .= '</tbody></table>';

    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output('lich-hen.pdf', 'I');
}


function exportAppointmentsList($pdo)
{
    exportAppointmentSchedule($pdo);
}
