<?php
ob_start();
session_start();

set_exception_handler(function ($e) {
    error_log("Patient profile uncaught: " . $e->getMessage());
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
$username = $_SESSION['username'] ?? '';
$email = $_SESSION['email'] ?? '';
$fname = $_SESSION['fname'] ?? '';
$lname = $_SESSION['lname'] ?? '';
$gender = $_SESSION['gender'] ?? '';
$contact = $_SESSION['contact'] ?? '';

if (!$pid) {
    header("Location: ../../index.php");
    exit();
}


$stmt = $pdo->prepare("SELECT * FROM patreg WHERE pid = :pid");
$stmt->execute([':pid' => $pid]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="shortcut icon" type="image/x-icon" href="../../images/favicon.png" />
    <title>Hồ sơ cá nhân - Bệnh viện Global</title>

    
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

        .profile-header {
            background: linear-gradient(135deg, #d2302c 0%, #ff4d4d 100%);
            padding: 2.4rem 0;
            margin-bottom: 1.6rem;
        }

        .profile-avatar-section {
            text-align: center;
            color: white;
        }

        .profile-avatar {
            width: 130px;
            height: 130px;
            border-radius: 50%;
            border: 5px solid white;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
            object-fit: cover;
            background: white;
        }

        .avatar-upload-btn {
            margin-top: 0.8rem;
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid white;
            color: white;
            padding: 0.4rem 1.2rem;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .avatar-upload-btn:hover {
            background: white;
            color: #d2302c;
        }

        .profile-tabs {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .nav-tabs {
            border-bottom: 2px solid #e5e7eb;
        }

        .nav-tabs .nav-link {
            border: none;
            color: #6b7280;
            font-weight: 600;
            padding: 0.8rem 1.6rem;
            transition: all 0.3s;
        }

        .nav-tabs .nav-link:hover {
            color: #d2302c;
        }

        .nav-tabs .nav-link.active {
            color: #d2302c;
            border-bottom: 3px solid #d2302c;
        }

        .tab-content {
            padding: 1.6rem;
        }

        .info-card {
            background: #f9fafb;
            border-radius: 10px;
            padding: 1.2rem;
            margin-bottom: 1.2rem;
        }

        .info-label {
            font-weight: 600;
            color: #6b7280;
            font-size: 0.8rem;
            margin-bottom: 0.4rem;
        }

        .info-value {
            font-size: 0.9rem;
            color: #111827;
            font-weight: 500;
        }

        .form-section-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 1.2rem;
            padding-bottom: 0.6rem;
            border-bottom: 2px solid #e5e7eb;
        }

        .avatar-preview {
            width: 170px;
            height: 170px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #e5e7eb;
            margin: 0.8rem auto;
            display: block;
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

    
    <div class="profile-header">
        <div class="container">
            <div class="profile-avatar-section" style="position: relative;">
                <a href="dashboard.php?page=profile" style="position: absolute; top: 0; left: 0; color: white; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: rgba(255, 255, 255, 0.2); border-radius: 8px; font-weight: 600; transition: all 0.3s;" onmouseover="this.style.background='rgba(255, 255, 255, 0.3)'" onmouseout="this.style.background='rgba(255, 255, 255, 0.2)'">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
                <img src="<?php echo $patient['avatar'] ? '../../' . $patient['avatar'] : '../../assets/images/default-avatar.png'; ?>"
                    alt="Avatar" class="profile-avatar" id="headerAvatar">
                <h2 class="mt-3"><?php echo htmlspecialchars($lname . ' ' . $fname); ?></h2>
                <p class="mb-0"><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($email); ?></p>
                <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($contact); ?></p>
            </div>
        </div>
    </div>

    <div class="container pb-5">
        <div class="profile-tabs">
            
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-toggle="tab" href="#info-tab">
                        <i class="fas fa-user"></i> Thông tin cá nhân
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#edit-tab">
                        <i class="fas fa-edit"></i> Chỉnh sửa hồ sơ
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#password-tab">
                        <i class="fas fa-key"></i> Đổi mật khẩu
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#avatar-tab">
                        <i class="fas fa-camera"></i> Ảnh đại diện
                    </a>
                </li>
            </ul>

            
            <div class="tab-content">
                
                <div id="info-tab" class="tab-pane fade show active">
                    <h3 class="form-section-title">Thông tin cá nhân</h3>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-card">
                                <div class="info-label"><i class="fas fa-user"></i> Họ</div>
                                <div class="info-value"><?php echo htmlspecialchars($patient['fname']); ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-card">
                                <div class="info-label"><i class="fas fa-user"></i> Tên</div>
                                <div class="info-value"><?php echo htmlspecialchars($patient['lname']); ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-card">
                                <div class="info-label"><i class="fas fa-venus-mars"></i> Giới tính</div>
                                <div class="info-value"><?php echo htmlspecialchars($patient['gender']); ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-card">
                                <div class="info-label"><i class="fas fa-birthday-cake"></i> Ngày sinh</div>
                                <div class="info-value">
                                    <?php echo $patient['date_of_birth'] ? date('d/m/Y', strtotime($patient['date_of_birth'])) : 'Chưa cập nhật'; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-card">
                                <div class="info-label"><i class="fas fa-envelope"></i> Email</div>
                                <div class="info-value"><?php echo htmlspecialchars($patient['email']); ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-card">
                                <div class="info-label"><i class="fas fa-phone"></i> Số điện thoại</div>
                                <div class="info-value"><?php echo htmlspecialchars($patient['contact']); ?></div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="info-card">
                                <div class="info-label"><i class="fas fa-map-marker-alt"></i> Địa chỉ</div>
                                <div class="info-value">
                                    <?php echo $patient['address'] ? htmlspecialchars($patient['address']) : 'Chưa cập nhật'; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-card">
                                <div class="info-label"><i class="fas fa-tint"></i> Nhóm máu</div>
                                <div class="info-value">
                                    <?php echo $patient['blood_group'] ? htmlspecialchars($patient['blood_group']) : 'Chưa cập nhật'; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-card">
                                <div class="info-label"><i class="fas fa-phone-square"></i> Liên hệ khẩn cấp</div>
                                <div class="info-value">
                                    <?php
                                    if ($patient['emergency_contact']) {
                                        echo htmlspecialchars($patient['emergency_contact_name'] . ' - ' . $patient['emergency_contact']);
                                    } else {
                                        echo 'Chưa cập nhật';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div id="edit-tab" class="tab-pane fade">
                    <h3 class="form-section-title">Chỉnh sửa thông tin</h3>

                    <form method="post" action="profile-handler.php">
                        <input type="hidden" name="action" value="update_profile">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label"><i class="fas fa-user"></i> Họ *</label>
                                    <input type="text" class="form-control" name="first_name"
                                        value="<?php echo htmlspecialchars($patient['firstname'] ?? ''); ?>"
                                        required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label"><i class="fas fa-user"></i> Tên *</label>
                                    <input type="text" class="form-control" name="last_name"
                                        value="<?php echo htmlspecialchars($patient['lastname'] ?? ''); ?>"
                                        required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label"><i class="fas fa-venus-mars"></i> Giới tính</label>
                                    <select class="form-control" name="gender">
                                        <option value="">Chọn giới tính</option>
                                        <option value="Nam" <?php echo ($patient['gender'] == 'Nam') ? 'selected' : ''; ?>>Nam</option>
                                        <option value="Nữ" <?php echo ($patient['gender'] == 'Nữ') ? 'selected' : ''; ?>>Nữ</option>
                                        <option value="Khác" <?php echo ($patient['gender'] == 'Khác') ? 'selected' : ''; ?>>Khác</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label"><i class="fas fa-phone"></i> Số điện thoại *</label>
                                    <input type="text" class="form-control" name="contact"
                                        value="<?php echo htmlspecialchars($patient['contact']); ?>"
                                        pattern="[0-9]{10}" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label"><i class="fas fa-birthday-cake"></i> Ngày sinh</label>
                                    <input type="date" class="form-control" name="date_of_birth"
                                        value="<?php echo $patient['date_of_birth']; ?>">
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label"><i class="fas fa-map-marker-alt"></i> Địa chỉ</label>
                                    <textarea class="form-control" name="address" rows="3"><?php echo htmlspecialchars($patient['address'] ?? ''); ?></textarea>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label"><i class="fas fa-tint"></i> Nhóm máu</label>
                                    <select class="form-control" name="blood_group">
                                        <option value="">Chọn nhóm máu</option>
                                        <?php
                                        $blood_groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
                                        foreach ($blood_groups as $group) {
                                            $selected = ($patient['blood_group'] == $group) ? 'selected' : '';
                                            echo "<option value='$group' $selected>$group</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label"><i class="fas fa-user"></i> Tên người liên hệ khẩn cấp</label>
                                    <input type="text" class="form-control" name="emergency_contact_name"
                                        value="<?php echo htmlspecialchars($patient['emergency_contact_name'] ?? ''); ?>">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label"><i class="fas fa-phone"></i> SĐT liên hệ khẩn cấp</label>
                                    <input type="text" class="form-control" name="emergency_contact"
                                        value="<?php echo htmlspecialchars($patient['emergency_contact'] ?? ''); ?>"
                                        pattern="[0-9]{10}">
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> Lưu thay đổi
                        </button>
                    </form>
                </div>

                
                <div id="password-tab" class="tab-pane fade">
                    <h3 class="form-section-title">Đổi mật khẩu</h3>

                    <form method="post" action="profile-handler.php" onsubmit="return validatePassword()">
                        <input type="hidden" name="action" value="change_password">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label"><i class="fas fa-lock"></i> Mật khẩu hiện tại *</label>
                                    <input type="password" class="form-control" name="current_password" required>
                                </div>
                            </div>
                            <div class="col-md-6"></div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label"><i class="fas fa-lock"></i> Mật khẩu mới *</label>
                                    <input type="password" class="form-control" name="new_password"
                                        id="new_password" minlength="3" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label"><i class="fas fa-lock"></i> Xác nhận mật khẩu mới *</label>
                                    <input type="password" class="form-control" name="confirm_password"
                                        id="confirm_password" minlength="3" required>
                                    <small id="password_message"></small>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-key"></i> Đổi mật khẩu
                        </button>
                    </form>
                </div>

                
                <div id="avatar-tab" class="tab-pane fade">
                    <h3 class="form-section-title">Ảnh đại diện</h3>

                    <div class="text-center">
                        <img src="<?php echo $patient['avatar'] ? '../../' . $patient['avatar'] : '../../assets/images/default-avatar.png'; ?>"
                            alt="Avatar" class="avatar-preview" id="avatarPreview">
                    </div>

                    <form method="post" action="profile-handler.php" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="upload_avatar">

                        <div class="form-group">
                            <label class="form-label"><i class="fas fa-image"></i> Chọn ảnh mới</label>
                            <input type="file" class="form-control" name="avatar" accept="image/*"
                                onchange="previewAvatar(event)" required>
                            <small class="form-text text-muted">
                                Chấp nhận: JPG, PNG, GIF. Kích thước tối đa: 5MB
                            </small>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-upload"></i> Tải lên ảnh
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        function validatePassword() {
            const newPass = document.getElementById('new_password').value;
            const confirmPass = document.getElementById('confirm_password').value;

            if (newPass !== confirmPass) {
                alert('Mật khẩu xác nhận không khớp!');
                return false;
            }
            return true;
        }

        // Check password matching on typing
        document.getElementById('confirm_password').addEventListener('keyup', function() {
            const newPass = document.getElementById('new_password').value;
            const confirmPass = this.value;
            const message = document.getElementById('password_message');

            if (confirmPass === '') {
                message.innerHTML = '';
            } else if (newPass === confirmPass) {
                message.style.color = '#d2302c';
                message.innerHTML = '<i class="fas fa-check-circle"></i> Mật khẩu khớp';
            } else {
                message.style.color = '#EF4444';
                message.innerHTML = '<i class="fas fa-times-circle"></i> Mật khẩu không khớp';
            }
        });

        function previewAvatar(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('avatarPreview').src = e.target.result;
                }
                reader.readAsDataURL(file);
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