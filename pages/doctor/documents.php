<?php
ob_start();
session_start();

set_exception_handler(function ($e) {
    error_log("Doctor documents uncaught: " . $e->getMessage());
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
$doctor_fullname = $doc_info['fullname'] ?? $doctor;


if (isset($_POST['upload_document'])) {
    if (isset($_FILES['document_file']) && $_FILES['document_file']['error'] == 0) {
        $pid = $_POST['pid'];
        $appointment_id = !empty($_POST['appointment_id']) ? $_POST['appointment_id'] : null;
        $description = trim($_POST['description'] ?? '');

        $file = $_FILES['document_file'];
        $fileName = basename($file['name']);
        $fileTmp = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileType = $file['type'];

        $allowedTypes = [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/gif',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];

        if (!in_array($fileType, $allowedTypes)) {
            redirectWithMessage('documents.php', 'error', 'Loại file không được phép. Chỉ chấp nhận PDF, JPG, PNG, GIF, DOC, DOCX.');
            exit();
        }

        if ($fileSize > 10 * 1024 * 1024) {
            redirectWithMessage('documents.php', 'error', 'Kích thước file quá lớn. Tối đa 10MB.');
            exit();
        }

        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExts = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'doc', 'docx'];
        if (!in_array($fileExt, $allowedExts)) {
            redirectWithMessage('documents.php', 'error', 'Phần mở rộng file không hợp lệ.');
            exit();
        }

        $newFileName = uniqid('med_doc_') . '.' . $fileExt;
        $uploadPath = '../../uploads/medical_documents/' . $newFileName;

        if (!is_dir('../../uploads/medical_documents/')) {
            mkdir('../../uploads/medical_documents/', 0755, true);
        }

        if (move_uploaded_file($fileTmp, $uploadPath)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO medical_documents (pid, doctor, appointment_id, document_name, file_path, file_type, file_size, description, uploaded_at) VALUES (:pid, :doctor, :appointment_id, :document_name, :file_path, :file_type, :file_size, :description, NOW())");
                $stmt->execute([
                    ':pid' => $pid,
                    ':doctor' => $doctor,
                    ':appointment_id' => $appointment_id,
                    ':document_name' => $fileName,
                    ':file_path' => $newFileName,
                    ':file_type' => $fileType,
                    ':file_size' => $fileSize,
                    ':description' => $description
                ]);
                redirectWithMessage('documents.php', 'success', 'Tài liệu đã được upload thành công!');
            } catch (PDOException $e) {
                @unlink($uploadPath);
                redirectWithMessage('documents.php', 'error', 'Lỗi khi lưu thông tin tài liệu: ' . $e->getMessage());
            }
        } else {
            redirectWithMessage('documents.php', 'error', 'Lỗi khi upload file.');
        }
    } else {
        redirectWithMessage('documents.php', 'error', 'Vui lòng chọn file để upload.');
    }
}


if (isset($_GET['delete_id'])) {
    $doc_id = intval($_GET['delete_id']);
    try {
        $stmt = $pdo->prepare("SELECT file_path FROM medical_documents WHERE id = :id AND doctor = :doctor");
        $stmt->execute([':id' => $doc_id, ':doctor' => $doctor]);
        $document = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($document) {
            $filePath = '../../uploads/medical_documents/' . $document['file_path'];
            if (file_exists($filePath)) {
                @unlink($filePath);
            }

            $stmt = $pdo->prepare("DELETE FROM medical_documents WHERE id = :id AND doctor = :doctor");
            $stmt->execute([':id' => $doc_id, ':doctor' => $doctor]);
            redirectWithMessage('documents.php', 'success', 'Xóa tài liệu thành công!');
        } else {
            redirectWithMessage('documents.php', 'error', 'Không tìm thấy tài liệu.');
        }
    } catch (PDOException $e) {
        redirectWithMessage('documents.php', 'error', 'Lỗi khi xóa tài liệu: ' . $e->getMessage());
    }
}


$search_query = '';
$search_condition = '';
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search_query = trim($_GET['search']);
    $search_condition = " AND (CONCAT(p.fname, ' ', p.lname) LIKE :search OR md.document_name LIKE :search)";
}

$page_num = isset($_GET['page_num']) ? max(1, intval($_GET['page_num'])) : 1;
$records_per_page = 10;
$offset = ($page_num - 1) * $records_per_page;


$count_sql = "SELECT COUNT(*) FROM medical_documents md 
              INNER JOIN patreg p ON md.pid = p.pid 
              WHERE md.doctor = :doctor $search_condition";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->bindValue(':doctor', $doctor);
if ($search_query) {
    $count_stmt->bindValue(':search', "%$search_query%");
}
$count_stmt->execute();
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $records_per_page);


$sql = "SELECT md.*, 
               p.fname, p.lname, p.contact
        FROM medical_documents md
        INNER JOIN patreg p ON md.pid = p.pid
        WHERE md.doctor = :doctor $search_condition
        ORDER BY md.uploaded_at DESC
        LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':doctor', $doctor);
if ($search_query) {
    $stmt->bindValue(':search', "%$search_query%");
}
$stmt->bindValue(':limit', $records_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$documents = $stmt->fetchAll(PDO::FETCH_ASSOC);


$patients_stmt = $pdo->prepare("SELECT DISTINCT p.pid, p.fname, p.lname, p.contact 
                                 FROM patreg p 
                                 INNER JOIN appointmenttb a ON p.pid = a.pid 
                                 WHERE TRIM(a.doctor) = TRIM(:doctor)
                                 ORDER BY p.fname, p.lname");
$patients_stmt->execute([':doctor' => $doctor_fullname]);
$patients = $patients_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Tài liệu - Bệnh viện Global</title>
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

        .upload-card {
            background: linear-gradient(135deg, #fde2e4 0%, #fee2e2 100%);
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08), 0 0 0 1px rgba(0, 0, 0, 0.02);
            border-left: 6px solid #d2302c;
        }

        .documents-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08), 0 0 0 1px rgba(0, 0, 0, 0.02);
        }

        .doc-item {
            background: linear-gradient(135deg, #fde2e4 0%, #fee2e2 100%);
            border: 2px solid #f8d7da;
            border-radius: 14px;
            padding: 12px;
            margin-bottom: 10px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .doc-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(180deg, #d2302c, #8b0000);
            transition: width 0.3s ease;
        }

        .doc-item:hover {
            box-shadow: 0 12px 48px rgba(210, 48, 44, 0.25);
            transform: translateY(-4px) scale(1.01);
            border-color: #f8d7da;
        }

        .doc-item:hover::before {
            width: 100%;
            opacity: 0.03;
        }

        .file-icon {
            font-size: 34px;
            margin-right: 14px;
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.1));
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

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #d2302c;
            background: white;
            padding: 10px 20px;
            border-radius: 10px;
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

        .form-control {
            border-radius: 8px;
            border: 2px solid #e5e7eb;
            padding: 8px 14px;
        }

        label {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 8px;
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
            <h1><i class="fas fa-file-upload mr-3"></i>Upload Tài liệu Y tế</h1>
            <p class="mb-0 mt-2">Quản lý tài liệu và hồ sơ bệnh nhân</p>
        </div>

        <?php displayMessage(); ?>

        
        <div class="upload-card">
            <h5 class="mb-4"><i class="fas fa-cloud-upload-alt mr-2" style="color: #d2302c;"></i>Upload Tài liệu Mới</h5>
            <form method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="fas fa-user-injured mr-1"></i>Chọn bệnh nhân <span class="text-danger">*</span></label>
                            <select name="pid" class="form-control" required>
                                <option value="">-- Chọn bệnh nhân --</option>
                                <?php foreach ($patients as $patient): ?>
                                    <option value="<?php echo $patient['pid']; ?>">
                                        <?php echo htmlspecialchars($patient['fname'] . ' ' . $patient['lname'] . ' - ' . $patient['contact']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="fas fa-file-alt mr-1"></i>Chọn file <span class="text-danger">*</span></label>
                            <input type="file" name="document_file" class="form-control" required accept=".pdf,.jpg,.jpeg,.png,.gif,.doc,.docx">
                            <small class="form-text text-muted">Chấp nhận: PDF, JPG, PNG, GIF, DOC, DOCX (Tối đa 10MB)</small>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label><i class="fas fa-align-left mr-1"></i>Mô tả</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Nhập mô tả về tài liệu..."></textarea>
                        </div>
                    </div>
                </div>
                <button type="submit" name="upload_document" class="btn btn-medical">
                    <i class="fas fa-upload mr-2"></i>Upload Tài liệu
                </button>
            </form>
        </div>

        
        <div class="documents-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0"><i class="fas fa-folder-open mr-2" style="color: #d2302c;"></i>Danh sách Tài liệu</h5>
                <form method="GET" class="form-inline">
                    <input type="text" name="search" class="form-control mr-2" placeholder="Tìm kiếm..." value="<?php echo htmlspecialchars($search_query); ?>" style="width: 250px;">
                    <button type="submit" class="btn btn-medical btn-sm"><i class="fas fa-search"></i></button>
                    <?php if ($search_query): ?>
                        <a href="documents.php" class="btn btn-secondary btn-sm ml-2"><i class="fas fa-times"></i></a>
                    <?php endif; ?>
                </form>
            </div>

            <?php if (empty($documents)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-2"></i>
                    <?php echo $search_query ? 'Không tìm thấy tài liệu nào.' : 'Chưa có tài liệu nào được upload.'; ?>
                </div>
            <?php else: ?>
                <?php foreach ($documents as $doc): ?>
                    <?php
                    $ext = strtolower(pathinfo($doc['document_name'], PATHINFO_EXTENSION));
                    if ($ext == 'pdf') {
                        $icon = '<i class="fas fa-file-pdf file-icon" style="color: #ef4444;"></i>';
                    } elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                        $icon = '<i class="fas fa-file-image file-icon" style="color: #ffd700;"></i>';
                    } elseif (in_array($ext, ['doc', 'docx'])) {
                        $icon = '<i class="fas fa-file-word file-icon" style="color: #d2302c;"></i>';
                    } else {
                        $icon = '<i class="fas fa-file file-icon" style="color: #6b7280;"></i>';
                    }
                    ?>
                    <div class="doc-item">
                        <div class="d-flex align-items-center">
                            <?php echo $icon; ?>
                            <div class="flex-grow-1">
                                <h6 class="mb-1"><strong><?php echo htmlspecialchars($doc['document_name']); ?></strong></h6>
                                <p class="mb-1">
                                    <i class="fas fa-user text-primary mr-1"></i>
                                    <strong>Bệnh nhân:</strong> <?php echo htmlspecialchars($doc['fname'] . ' ' . $doc['lname']); ?>
                                    <span class="mx-2">|</span>
                                    <i class="fas fa-phone text-success mr-1"></i><?php echo htmlspecialchars($doc['contact']); ?>
                                </p>
                                <?php if ($doc['description']): ?>
                                    <p class="mb-1 text-muted"><i class="fas fa-info-circle mr-1"></i><?php echo htmlspecialchars($doc['description']); ?></p>
                                <?php endif; ?>
                                <p class="mb-0 text-muted">
                                    <small>
                                        <i class="fas fa-clock mr-1"></i><?php echo date('d/m/Y H:i', strtotime($doc['uploaded_at'])); ?>
                                        <span class="mx-2">|</span>
                                        <i class="fas fa-hdd mr-1"></i><?php echo number_format($doc['file_size'] / 1024, 1); ?> KB
                                    </small>
                                </p>
                            </div>
                            <div>
                                <a href="../../uploads/medical_documents/<?php echo $doc['file_path']; ?>" target="_blank" class="btn btn-sm btn-info mr-2">
                                    <i class="fas fa-eye"></i> Xem
                                </a>
                                <a href="../../uploads/medical_documents/<?php echo $doc['file_path']; ?>" download class="btn btn-sm btn-success mr-2">
                                    <i class="fas fa-download"></i> Tải
                                </a>
                                <a href="?delete_id=<?php echo $doc['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc muốn xóa tài liệu này?');">
                                    <i class="fas fa-trash"></i> Xóa
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