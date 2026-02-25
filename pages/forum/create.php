<?php
session_start();
$base_path = '../../';
require_once '../../config.php';
require_once '../../includes/forum_functions.php';


if (!isset($_SESSION['patientSession']) && !isset($_SESSION['doctorSession']) && !isset($_SESSION['adminSession'])) {
    header('Location: ../auth/login.php');
    exit;
}


if (isset($_SESSION['patientSession'])) {
    $user_type = 'patient';
    $user_id = $_SESSION['patientSession'];
} elseif (isset($_SESSION['doctorSession'])) {
    $user_type = 'doctor';
    $user_id = $_SESSION['doctorSession'];
} else {
    $user_type = 'admin';
    $user_id = 1;
}

$success = '';
$error = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $tags = trim($_POST['tags'] ?? '');
    $category = $_POST['category'] ?? 'general';
    $privacy = $_POST['privacy'] ?? 'public';

    if (empty($title)) {
        $error = 'Vui lòng nhập tiêu đề';
    } elseif (empty($content)) {
        $error = 'Vui lòng nhập nội dung';
    } else {
        $data = [
            'user_id' => $user_id,
            'user_type' => $user_type,
            'title' => $title,
            'content' => $content,
            'tags' => $tags,
            'category' => $category,
            'privacy' => $privacy
        ];

        if (createForumPost($pdo, $data)) {
            $post_id = $pdo->lastInsertId();
            header('Location: post.php?id=' . $post_id);
            exit;
        } else {
            $error = 'Có lỗi xảy ra, vui lòng thử lại';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo bài viết - Diễn đàn</title>
    <link rel="shortcut icon" href="../../images/favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-image:
                linear-gradient(135deg, rgba(255, 245, 245, 0.85) 0%, rgba(255, 230, 230, 0.85) 50%, rgba(255, 240, 240, 0.85) 100%),
                url('../../images/ngua.png');
            background-size: cover, contain;
            background-position: center, center;
            background-repeat: no-repeat, no-repeat;
            background-attachment: fixed, fixed;
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
        }

        .page-header {
            background: linear-gradient(135deg, #d2302c 0%, #ff4d4d 100%);
            color: white;
            padding: 2.5rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 4px 12px rgba(210, 48, 44, 0.15);
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 0.6rem 1.25rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            backdrop-filter: blur(10px);
            margin-bottom: 1rem;
        }

        .back-link:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
            text-decoration: none;
            transform: translateX(-5px);
        }

        .form-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .form-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .form-control,
        .custom-select {
            border-radius: 8px;
            border: 2px solid #e5e7eb;
            transition: all 0.3s;
        }

        .form-control:focus,
        .custom-select:focus {
            border-color: #d2302c;
            box-shadow: 0 0 0 3px rgba(210, 48, 44, 0.1);
        }

        textarea.form-control {
            min-height: 200px;
        }

        .btn-submit {
            background: linear-gradient(135deg, #d2302c, #ff4d4d);
            color: white;
            padding: 0.75rem 2.5rem;
            border-radius: 10px;
            font-weight: 600;
            border: none;
            transition: all 0.3s;
        }

        .btn-submit:hover {
            background: linear-gradient(135deg, #8b0000, #d2302c);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(210, 48, 44, 0.3);
        }

        .btn-cancel {
            background: #f1f5f9;
            color: #64748b;
            padding: 0.75rem 2.5rem;
            border-radius: 10px;
            font-weight: 600;
            border: none;
            transition: all 0.3s;
        }

        .btn-cancel:hover {
            background: #e2e8f0;
            color: #475569;
        }

        .help-text {
            font-size: 0.875rem;
            color: #6b7280;
            margin-top: 0.25rem;
        }

        .icon-label {
            color: #d2302c;
            margin-right: 0.5rem;
        }
    </style>
</head>

<body>
    <?php include('../../includes/navbar.php'); ?>

    <div class="page-header">
        <div class="container">
            <a href="index.php" class="back-link">
                <i class="fas fa-arrow-left"></i>
                Quay lại diễn đàn
            </a>
            <h1><i class="fas fa-pen-fancy mr-3"></i>Tạo bài viết mới</h1>
            <p class="lead mb-0">Chia sẻ kiến thức và kinh nghiệm của bạn</p>
        </div>
    </div>

    <div class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle mr-2"></i><?= htmlspecialchars($error) ?>
                        <button type="button" class="close" data-dismiss="alert">
                            <span>&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <div class="form-card">
                    <form method="POST" action="">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-heading icon-label"></i>Tiêu đề bài viết <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="title" class="form-control" placeholder="Nhập tiêu đề hấp dẫn..." required maxlength="200">
                            <small class="help-text">Tiêu đề ngắn gọn, súc tích (tối đa 200 ký tự)</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-folder icon-label"></i>Danh mục <span class="text-danger">*</span>
                                    </label>
                                    <select name="category" class="custom-select" required>
                                        <option value="general">Tổng quát</option>
                                        <option value="health_tips">Mẹo sức khỏe</option>
                                        <option value="qa">Hỏi đáp</option>
                                        <option value="experience">Kinh nghiệm</option>
                                        <option value="nutrition">Dinh dưỡng</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-lock icon-label"></i>Quyền riêng tư
                                    </label>
                                    <select name="privacy" class="custom-select">
                                        <option value="public">Công khai</option>
                                        <option value="private">Riêng tư</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-align-left icon-label"></i>Nội dung <span class="text-danger">*</span>
                            </label>
                            <textarea name="content" class="form-control" placeholder="Viết nội dung chi tiết..." required></textarea>
                            <small class="help-text">Chia sẻ chi tiết để mọi người hiểu rõ hơn</small>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-tags icon-label"></i>Thẻ tag (tùy chọn)
                            </label>
                            <input type="text" name="tags" class="form-control" placeholder="VD: tim mạch, tiểu đường, cao huyết áp (cách nhau bởi dấu phẩy)">
                            <small class="help-text">Giúp người đọc dễ tìm kiếm bài viết của bạn</small>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-between">
                            <a href="index.php" class="btn btn-cancel">
                                <i class="fas fa-times mr-2"></i>Hủy
                            </a>
                            <button type="submit" class="btn btn-submit">
                                <i class="fas fa-paper-plane mr-2"></i>Đăng bài viết
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    
    <script type="text/javascript">
        (function() {
            const isMobile = window.matchMedia('(max-width: 767px)').matches;
            const petalCount = isMobile ? 10 : 20;
            const petalImage = 'https://blogger.googleusercontent.com/img/b/R29vZ2xl/AVvXsEizrrtX-KQtKY8e8pxCHjLROT5pYW7sVkUpET9HHpW8QO-PnoIRKVsvRDxM6shrE4Q-44Oh9teSGK1SApaZ1OJvhR4z7ENgKSJOLWfsdKw9jPszAa2HqaE6W8ohyGHRvff6TgKXEUjnn73LLLp3FHbtMTJnIkPxPhujWwG5ZsFgW7ctQ0zrR5KKSqlewg/s16000/hoadao-anonyviet.com.png';

            const petals = [];
            let docWidth = window.innerWidth - 10;
            let docHeight = window.innerHeight;

            // Khởi tạo hoa đào
            for (let i = 0; i < petalCount; i++) {
                const petal = {
                    x: Math.random() * docWidth,
                    y: Math.random() * docHeight,
                    dx: 0,
                    amplitude: Math.random() * 20,
                    speedX: 0.02 + Math.random() / 10,
                    speedY: 0.7 + Math.random(),
                    element: null
                };

                const div = document.createElement('div');
                div.id = 'petal' + i;
                div.style.cssText = `position:fixed;z-index:${99+i};visibility:visible;pointer-events:none;width:15px;left:${petal.x}px;top:${petal.y}px`;
                div.innerHTML = `<img src="${petalImage}" alt="Hoa đào" style="width:100%;height:auto">`;
                document.body.appendChild(div);
                petal.element = div;
                petals.push(petal);
            }

            // Animation loop
            function animate() {
                docWidth = window.innerWidth - 10;
                docHeight = window.innerHeight;

                petals.forEach(petal => {
                    petal.y += petal.speedY;
                    if (petal.y > docHeight - 50) {
                        petal.x = Math.random() * (docWidth - petal.amplitude - 30);
                        petal.y = 0;
                        petal.speedX = 0.02 + Math.random() / 10;
                        petal.speedY = 0.7 + Math.random();
                    }
                    petal.dx += petal.speedX;
                    petal.element.style.top = petal.y + 'px';
                    petal.element.style.left = (petal.x + petal.amplitude * Math.sin(petal.dx)) + 'px';
                });

                requestAnimationFrame(animate);
            }

            animate();
        })();
    </script>
</body>

</html>