<?php
session_start();
require_once '../../config.php';
require_once '../../includes/forum_functions.php';


if (!isset($_SESSION['username']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'approve_post' && isset($_POST['post_id'])) {
        $stmt = $pdo->prepare("UPDATE forum_posts SET is_approved = 1 WHERE id = ?");
        $stmt->execute([$_POST['post_id']]);
        header('Location: moderate_posts.php?msg=approved');
        exit;
    } elseif ($_POST['action'] === 'reject_post' && isset($_POST['post_id'])) {
        $stmt = $pdo->prepare("DELETE FROM forum_posts WHERE id = ?");
        $stmt->execute([$_POST['post_id']]);
        header('Location: moderate_posts.php?msg=rejected');
        exit;
    }
}


$stmt = $pdo->query("
    SELECT fp.*,
        CASE 
            WHEN fp.user_type = 'patient' THEN CONCAT(p.fname, ' ', p.lname)
            WHEN fp.user_type = 'doctor' THEN d.fullname
            WHEN fp.user_type = 'admin' THEN 'Admin'
        END as author_name
    FROM forum_posts fp
    LEFT JOIN patreg p ON (fp.user_id = p.pid AND fp.user_type = 'patient')
    LEFT JOIN doctb d ON (fp.user_id = d.id AND fp.user_type = 'doctor')
    WHERE fp.is_approved = 0
    ORDER BY fp.created_at DESC
");
$pending_posts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Duyệt bài viết - Admin</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/custom/medical-theme.css">
    <style>
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

        /* Enhanced White Text Contrast for Better Readability */
        body {
            color: #ffffff !important;
        }

        .sidebar-title,
        .sidebar-subtitle,
        .sidebar-menu-link,
        .sidebar-menu-link span,
        .sidebar-menu-icon {
            color: #ffffff !important;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
        }

        .navbar-title {
            color: #ffffff !important;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.4);
            font-weight: 700;
        }

        .data-table-title,
        .data-table-header h3 {
            color: #ffffff !important;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.4);
        }

        .alert {
            color: #155724 !important;
            background-color: #d4edda !important;
            border-color: #c3e6cb !important;
            font-weight: 600;
        }

        /* Card styling - keep dark text on white background */
        .card {
            background: rgba(255, 255, 255, 0.98) !important;
            color: #333333 !important;
        }

        .card-title {
            color: #1a1a1a !important;
            font-weight: 700 !important;
        }

        .card-text,
        .card-body p {
            color: #333333 !important;
        }

        .text-muted {
            color: #666666 !important;
        }

        .text-center.text-muted i {
            color: #999999 !important;
        }

        .badge {
            font-weight: 600 !important;
            padding: 0.4em 0.8em !important;
            text-shadow: none !important;
        }

        .badge-info {
            background: linear-gradient(135deg, #17a2b8, #138496) !important;
            color: #ffffff !important;
        }

        /* Button styling */
        .btn {
            font-weight: 600 !important;
            text-shadow: none !important;
            border: none !important;
        }

        .btn-success {
            background: linear-gradient(135deg, #28a745, #218838) !important;
            color: #ffffff !important;
            box-shadow: 0 2px 6px rgba(40, 167, 69, 0.4) !important;
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #218838, #1e7e34) !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(40, 167, 69, 0.5) !important;
        }

        .btn-danger {
            background: linear-gradient(135deg, #dc3545, #c82333) !important;
            color: #ffffff !important;
            box-shadow: 0 2px 6px rgba(220, 53, 69, 0.4) !important;
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #c82333, #bd2130) !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(220, 53, 69, 0.5) !important;
        }

        .btn-info {
            background: linear-gradient(135deg, #17a2b8, #138496) !important;
            color: #ffffff !important;
            box-shadow: 0 2px 6px rgba(23, 162, 184, 0.4) !important;
        }

        .btn-info:hover {
            background: linear-gradient(135deg, #138496, #117a8b) !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(23, 162, 184, 0.5) !important;
        }

        /* Empty state styling */
        .p-5.text-center {
            color: #ffffff !important;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.4);
        }

        .p-5.text-center i {
            color: #ffffff !important;
            opacity: 0.8;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .p-5.text-center p {
            color: #ffffff !important;
            font-weight: 600;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
        }

        /* Data table container */
        .data-table-container {
            background: rgba(255, 255, 255, 0.15) !important;
            backdrop-filter: blur(10px);
            border-radius: 12px;
            overflow: hidden;
        }

        .data-table-header {
            background: rgba(210, 48, 44, 0.3) !important;
            padding: 20px;
        }
    </style>
</head>

<body>
    <div class="petals-container" id="petals"></div>
    <script>
        function createPetals() {
            const petalsContainer = document.getElementById('petals');
            for (let i = 0; i < 25; i++) {
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
    <div class="dashboard-container">
        
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div>
                    <h1 class="sidebar-title">Bệnh viện Global</h1>
                    <div class="sidebar-subtitle">Cổng Quản trị</div>
                </div>
            </div>

            <ul class="sidebar-menu">
                <li class="sidebar-menu-item">
                    <a href="dashboard.php" class="sidebar-menu-link">
                        <i class="fas fa-th-large sidebar-menu-icon"></i>
                        <span>Bảng điều khiển</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="moderate_posts.php" class="sidebar-menu-link active">
                        <i class="fas fa-check-circle sidebar-menu-icon"></i>
                        <span>Duyệt bài viết</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="../forum/index.php" class="sidebar-menu-link">
                        <i class="fas fa-comments sidebar-menu-icon"></i>
                        <span>Diễn đàn</span>
                    </a>
                </li>
            </ul>

            <div class="sidebar-footer">
                <a href="../auth/logout.php" class="btn btn-danger btn-block">
                    <i class="fas fa-sign-out-alt"></i> Đăng xuất
                </a>
            </div>
        </aside>

        
        <main class="main-content">
            <nav class="top-navbar">
                <div class="navbar-left">
                    <h1 class="navbar-title">Duyệt bài viết diễn đàn</h1>
                </div>
            </nav>

            <section class="content-section">
                <?php if (isset($_GET['msg'])): ?>
                    <div class="alert alert-success">
                        <?= $_GET['msg'] === 'approved' ? 'Đã duyệt bài viết!' : 'Đã từ chối bài viết!' ?>
                    </div>
                <?php endif; ?>

                <div class="data-table-container">
                    <div class="data-table-header">
                        <h3 class="data-table-title">
                            <i class="fas fa-list"></i> Bài viết chờ duyệt (<?= count($pending_posts) ?>)
                        </h3>
                    </div>

                    <?php if (empty($pending_posts)): ?>
                        <div class="p-5 text-center text-muted">
                            <i class="fas fa-check-circle" style="font-size: 4rem;"></i>
                            <p class="mt-3">Không có bài viết nào chờ duyệt</p>
                        </div>
                    <?php else: ?>
                        <div class="p-4">
                            <?php foreach ($pending_posts as $post): ?>
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <h5 class="card-title"><?= htmlspecialchars($post['title']) ?></h5>
                                                <p class="text-muted small">
                                                    <i class="fas fa-user"></i> <?= htmlspecialchars($post['author_name']) ?>
                                                    <span class="ml-3"><i class="fas fa-clock"></i> <?= timeAgo($post['created_at']) ?></span>
                                                    <span class="ml-3"><i class="fas fa-tag"></i> <?= ucfirst($post['category']) ?></span>
                                                </p>
                                                <p class="card-text"><?= nl2br(htmlspecialchars(substr($post['content'], 0, 300))) ?>...</p>
                                                <?php if ($post['tags']): ?>
                                                    <div class="mb-2">
                                                        <?php foreach (explode(',', $post['tags']) as $tag): ?>
                                                            <span class="badge badge-info"><?= htmlspecialchars(trim($tag)) ?></span>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="approve_post">
                                                <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                                <button type="submit" class="btn btn-success">
                                                    <i class="fas fa-check"></i> Duyệt
                                                </button>
                                            </form>
                                            <form method="POST" class="d-inline ml-2" onsubmit="return confirm('Bạn có chắc muốn từ chối bài viết này?')">
                                                <input type="hidden" name="action" value="reject_post">
                                                <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                                <button type="submit" class="btn btn-danger">
                                                    <i class="fas fa-times"></i> Từ chối
                                                </button>
                                            </form>
                                            <a href="../forum/post.php?id=<?= $post['id'] ?>" class="btn btn-info ml-2" target="_blank">
                                                <i class="fas fa-eye"></i> Xem chi tiết
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    
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