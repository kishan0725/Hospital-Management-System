<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error - Global Hospital</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #8b0000 0%, #d2302c 50%, #ff4d4d 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 30% 70%, rgba(20, 184, 166, 0.2) 0%, transparent 50%);
            pointer-events: none;
        }

        .error-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 3rem;
            text-align: center;
            max-width: 600px;
            width: 100%;
            animation: slideUp 0.5s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .error-icon {
            width: 120px;
            height: 120px;
            margin: 0 auto 2rem;
            background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            color: white;
            box-shadow: 0 10px 30px rgba(239, 68, 68, 0.3);
        }

        .error-icon.warning {
            background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
            box-shadow: 0 10px 30px rgba(245, 158, 11, 0.3);
        }

        .error-icon.info {
            background: linear-gradient(135deg, #ffd700 0%, #d4af37 100%);
            box-shadow: 0 10px 30px rgba(255, 215, 0, 0.3);
        }

        h1 {
            font-size: 2rem;
            color: #1F2937;
            margin-bottom: 1rem;
            font-weight: 700;
        }

        p {
            color: #6B7280;
            font-size: 1.125rem;
            line-height: 1.7;
            margin-bottom: 2rem;
        }

        .error-code {
            display: inline-block;
            background: #FEE2E2;
            color: #DC2626;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.875rem;
            margin-bottom: 2rem;
        }

        .button-group {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 1rem 2rem;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #d2302c 0%, #ff4d4d 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(210, 48, 44, 0.3);
        }

        .btn-secondary {
            background: #F3F4F6;
            color: #4B5563;
        }

        .btn-secondary:hover {
            background: #E5E7EB;
        }

        @media (max-width: 768px) {
            .error-container {
                padding: 2rem;
            }

            h1 {
                font-size: 1.5rem;
            }

            .button-group {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>

<body>
    <div class="error-container">
        <?php
        
        $errorType = $_GET['type'] ?? 'error';
        $errorMessage = $_GET['message'] ?? 'Đã xảy ra lỗi không xác định';
        $errorCode = $_GET['code'] ?? 'ERROR';

        $iconClass = '';
        $icon = 'fa-exclamation-triangle';
        $title = 'Đã xảy ra lỗi';

        switch ($errorType) {
            case 'password_mismatch':
                $iconClass = 'warning';
                $icon = 'fa-key';
                $title = 'Mật khẩu không khớp';
                $errorMessage = 'Mật khẩu và xác nhận mật khẩu không khớp. Vui lòng thử lại.';
                $errorCode = 'PASSWORD_MISMATCH';
                break;

            case 'login_failed':
                $iconClass = '';
                $icon = 'fa-times-circle';
                $title = 'Đăng nhập thất bại';
                $errorMessage = 'Tên đăng nhập hoặc mật khẩu không đúng. Vui lòng kiểm tra lại.';
                $errorCode = 'LOGIN_FAILED';
                break;

            case 'registration_failed':
                $iconClass = '';
                $icon = 'fa-user-times';
                $title = 'Đăng ký thất bại';
                $errorMessage = 'Không thể tạo tài khoản. Email hoặc số điện thoại có thể đã được sử dụng.';
                $errorCode = 'REGISTRATION_FAILED';
                break;

            case 'access_denied':
                $iconClass = '';
                $icon = 'fa-ban';
                $title = 'Truy cập bị từ chối';
                $errorMessage = 'Bạn không có quyền truy cập trang này.';
                $errorCode = 'ACCESS_DENIED';
                break;

            case 'session_expired':
                $iconClass = 'warning';
                $icon = 'fa-clock';
                $title = 'Phiên đăng nhập hết hạn';
                $errorMessage = 'Phiên đăng nhập của bạn đã hết hạn. Vui lòng đăng nhập lại.';
                $errorCode = 'SESSION_EXPIRED';
                break;

            case 'not_found':
                $iconClass = 'info';
                $icon = 'fa-question-circle';
                $title = 'Không tìm thấy';
                $errorMessage = 'Trang hoặc tài nguyên bạn đang tìm không tồn tại.';
                $errorCode = '404';
                break;

            case 'database_error':
                $iconClass = '';
                $icon = 'fa-database';
                $title = 'Lỗi cơ sở dữ liệu';
                $errorMessage = 'Không thể kết nối đến cơ sở dữ liệu. Vui lòng thử lại sau.';
                $errorCode = 'DB_ERROR';
                break;
        }
        ?>

        <div class="error-icon <?php echo $iconClass; ?>">
            <i class="fas <?php echo $icon; ?>"></i>
        </div>

        <h1><?php echo htmlspecialchars($title); ?></h1>

        <div class="error-code"><?php echo htmlspecialchars($errorCode); ?></div>

        <p><?php echo htmlspecialchars($errorMessage); ?></p>

        <div class="button-group">
            <a href="javascript:history.back()" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
            <a href="../auth/login.php" class="btn btn-primary">
                <i class="fas fa-home"></i> Trang chủ
            </a>
        </div>
    </div>
</body>

</html>