<?php
session_start();
require_once('../../config.php');

$doctor = $_SESSION['dname'] ?? null;

if (!$doctor) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}


$stmt = $pdo->prepare("SELECT id FROM doctb WHERE username = :username");
$stmt->execute([':username' => $doctor]);
$doctor_result = $stmt->fetch(PDO::FETCH_ASSOC);
$doctor_id = $doctor_result['id'] ?? null;

if (!$doctor_id || !isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

$record_id = intval($_GET['id']);
$mode = $_GET['mode'] ?? 'view';

try {
    
    $stmt = $pdo->prepare("
        SELECT mr.*, 
               p.fname, p.lname, p.contact, p.email, p.gender, p.date_of_birth, p.blood_group,
               d.fullname as doctor_name,
               a.appdate, a.apptime
        FROM medical_records mr
        LEFT JOIN patreg p ON mr.patient_id = p.pid
        LEFT JOIN doctb d ON mr.doctor_id = d.id
        LEFT JOIN appointmenttb a ON mr.appointment_id = a.ID
        WHERE mr.id = :id AND mr.doctor_id = :doctor_id
    ");
    $stmt->execute([':id' => $record_id, ':doctor_id' => $doctor_id]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$record) {
        echo json_encode(['success' => false, 'message' => 'Record not found']);
        exit();
    }

    if ($mode === 'edit') {
        
        echo json_encode(['success' => true, 'record' => $record]);
    } else {
        
?>
        <div class="record-detail-view">
            <div class="row mb-4">
                <div class="col-md-12">
                    <div style="background: linear-gradient(135deg, #f8fafc 0%, #e5e7eb 100%); padding: 20px; border-radius: 8px; border-left: 4px solid #48bb78;">
                        <h5 style="margin-bottom: 15px; color: #1f2937; font-weight: 700;">
                            <i class="fas fa-user-circle"></i> Thông tin bệnh nhân
                        </h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Họ tên:</strong> <?php echo $record['fname'] . ' ' . $record['lname']; ?></p>
                                <p><strong>Giới tính:</strong> <?php echo $record['gender'] === 'Male' ? 'Nam' : 'Nữ'; ?></p>
                                <p><strong>Ngày sinh:</strong> <?php echo $record['date_of_birth'] ? date('d/m/Y', strtotime($record['date_of_birth'])) : '-'; ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Liên hệ:</strong> <?php echo $record['contact']; ?></p>
                                <p><strong>Email:</strong> <?php echo $record['email']; ?></p>
                                <p><strong>Nhóm máu:</strong> <?php echo $record['blood_group'] ?? '-'; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <p><strong><i class="fas fa-user-md"></i> Bác sĩ khám:</strong> <?php echo $record['doctor_name']; ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong><i class="fas fa-calendar"></i> Ngày khám:</strong> <?php echo date('d/m/Y H:i', strtotime($record['record_date'])); ?></p>
                </div>
            </div>

            <?php if ($record['height'] || $record['weight'] || $record['blood_pressure'] || $record['heart_rate'] || $record['temperature']): ?>
                <div class="mb-4" style="background: #f8fafc; padding: 15px; border-radius: 8px;">
                    <h6 style="color: #1f2937; font-weight: 700; margin-bottom: 15px;">
                        <i class="fas fa-heartbeat"></i> Chỉ số sức khỏe
                    </h6>
                    <div class="row">
                        <?php if ($record['height']): ?>
                            <div class="col-md-4"><strong>Chiều cao:</strong> <?php echo $record['height']; ?> cm</div>
                        <?php endif; ?>
                        <?php if ($record['weight']): ?>
                            <div class="col-md-4"><strong>Cân nặng:</strong> <?php echo $record['weight']; ?> kg</div>
                        <?php endif; ?>
                        <?php if ($record['height'] && $record['weight']): ?>
                            <div class="col-md-4">
                                <strong>BMI:</strong>
                                <?php
                                $bmi = $record['weight'] / (($record['height'] / 100) * ($record['height'] / 100));
                                echo number_format($bmi, 1);
                                ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($record['blood_pressure']): ?>
                            <div class="col-md-4 mt-2"><strong>Huyết áp:</strong> <?php echo $record['blood_pressure']; ?></div>
                        <?php endif; ?>
                        <?php if ($record['heart_rate']): ?>
                            <div class="col-md-4 mt-2"><strong>Nhịp tim:</strong> <?php echo $record['heart_rate']; ?> bpm</div>
                        <?php endif; ?>
                        <?php if ($record['temperature']): ?>
                            <div class="col-md-4 mt-2"><strong>Nhiệt độ:</strong> <?php echo $record['temperature']; ?> °C</div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($record['symptoms']): ?>
                <div class="mb-3">
                    <h6 style="color: #1f2937; font-weight: 700;"><i class="fas fa-notes-medical"></i> Triệu chứng</h6>
                    <p style="white-space: pre-wrap; background: #f8fafc; padding: 15px; border-radius: 8px;"><?php echo htmlspecialchars($record['symptoms']); ?></p>
                </div>
            <?php endif; ?>

            <?php if ($record['diagnosis']): ?>
                <div class="mb-3">
                    <h6 style="color: #1f2937; font-weight: 700;"><i class="fas fa-diagnoses"></i> Chẩn đoán</h6>
                    <p style="white-space: pre-wrap; background: #fff3cd; padding: 15px; border-radius: 8px; border-left: 4px solid #ffc107;"><?php echo htmlspecialchars($record['diagnosis']); ?></p>
                </div>
            <?php endif; ?>

            <?php if ($record['treatment_plan']): ?>
                <div class="mb-3">
                    <h6 style="color: #1f2937; font-weight: 700;"><i class="fas fa-procedures"></i> Phương pháp điều trị</h6>
                    <p style="white-space: pre-wrap; background: #d1ecf1; padding: 15px; border-radius: 8px; border-left: 4px solid #17a2b8;"><?php echo htmlspecialchars($record['treatment_plan']); ?></p>
                </div>
            <?php endif; ?>

            <?php if ($record['notes']): ?>
                <div class="mb-3">
                    <h6 style="color: #1f2937; font-weight: 700;"><i class="fas fa-comment-medical"></i> Ghi chú</h6>
                    <p style="white-space: pre-wrap; background: #f8fafc; padding: 15px; border-radius: 8px;"><?php echo htmlspecialchars($record['notes']); ?></p>
                </div>
            <?php endif; ?>
        </div>
<?php
    }
} catch (PDOException $e) {
    error_log("Get record detail error: " . $e->getMessage());
    if ($mode === 'edit') {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    } else {
        echo '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> Lỗi khi tải dữ liệu</div>';
    }
}
