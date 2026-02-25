<?php
session_start();
$base_path = '../../';
require_once '../../config.php';
require_once '../../includes/forum_functions.php';


$isLoggedIn = isset($_SESSION['patientSession']) || isset($_SESSION['doctorSession']) || isset($_SESSION['adminSession']);


$user_id = null;
$user_type = null;
$user_name = '';

if (isset($_SESSION['patientSession'])) {
    $user_type = 'patient';
    $user_id = $_SESSION['patientSession'];
    $stmt = $pdo->prepare("SELECT CONCAT(lname, ' ', fname) as name FROM patreg WHERE pid = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    $user_name = $user['name'] ?? 'Patient';
} elseif (isset($_SESSION['doctorSession'])) {
    $user_type = 'doctor';
    $user_id = $_SESSION['doctorSession'];
    $stmt = $pdo->prepare("SELECT fullname as name FROM doctb WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    $user_name = $user['name'] ?? 'Doctor';
} elseif (isset($_SESSION['adminSession'])) {
    $user_type = 'admin';
    $user_id = 1;
    $user_name = 'Admin';
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $isLoggedIn) {
    if ($_POST['action'] === 'toggle_like' && isset($_POST['post_id'])) {
        toggleForumLike($pdo, $user_id, $user_type, $_POST['post_id'], 'post');
        header('Location: index.php' . (isset($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''));
        exit;
    }
}


$filters = [];
if (isset($_GET['search'])) $filters['search'] = trim($_GET['search']);
if (isset($_GET['category'])) $filters['category'] = $_GET['category'];
if (isset($_GET['status'])) $filters['status'] = $_GET['status'];
if (isset($_GET['sort'])) $filters['sort'] = $_GET['sort'];


$posts = getForumPosts($pdo, $filters, $user_id, $user_type);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diễn đàn - Global Hospitals</title>
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

        .forum-header {
            background: linear-gradient(135deg, rgba(210, 48, 44, 0.85) 0%, rgba(255, 77, 77, 0.85) 100%), url('../../images/navbar.png') center/cover no-repeat;
            color: white;
            padding: 2.5rem 0 3rem 0;
            margin-top: 70px;
            margin-bottom: 2.5rem;
            box-shadow: 0 4px 12px rgba(210, 48, 44, 0.15);
            position: relative;
            overflow: hidden;
        }

        .forum-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
        }

        .forum-header .lead {
            font-size: 1rem;
            opacity: 0.95;
            font-weight: 400;
            position: relative;
            z-index: 1;
            margin-bottom: 1rem;
        }

        .forum-stats {
            display: flex;
            gap: 1.5rem;
            margin-top: 1rem;
            position: relative;
            z-index: 1;
            flex-wrap: wrap;
        }

        .forum-stat-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .forum-stat-item i {
            font-size: 1.1rem;
        }

        .back-home-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            backdrop-filter: blur(10px);
            margin-bottom: 1.5rem;
        }

        .back-home-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
            text-decoration: none;
            transform: translateX(-5px);
        }

        .search-filter-bar {
            background: white;
            padding: 1.5rem;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }

        .post-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            transition: all 0.3s;
            border-left: 4px solid transparent;
        }

        .post-card:hover {
            box-shadow: 0 8px 24px rgba(210, 48, 44, 0.15);
            transform: translateY(-3px);
            border-left-color: #d2302c;
        }

        .post-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .author-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .author-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ff4d4d, #ff6b6b);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .badge-doctor {
            background: linear-gradient(135deg, #ffd700, #d4af37);
            color: #8b0000;
            padding: 0.3rem 0.85rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-patient {
            background: linear-gradient(135deg, #ff4d4d, #ff6b6b);
            color: white;
            padding: 0.3rem 0.85rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-admin {
            background: linear-gradient(135deg, #8b0000, #d2302c);
            color: white;
            padding: 0.3rem 0.85rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .post-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.75rem;
            transition: color 0.3s;
        }

        .post-title:hover {
            color: #d2302c;
        }

        .post-excerpt {
            color: #64748b;
            margin-bottom: 1rem;
            line-height: 1.6;
        }

        .post-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .tag {
            background: linear-gradient(135deg, #fff5f5, #ffe6e6);
            color: #d2302c;
            padding: 0.3rem 0.85rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .post-footer {
            display: flex;
            gap: 1.5rem;
            align-items: center;
            padding-top: 1rem;
            border-top: 2px solid #f1f5f9;
        }

        .btn-action {
            background: none;
            border: none;
            color: #64748b;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
            transition: all 0.3s;
            text-decoration: none;
        }

        .btn-action:hover {
            background: #fff5f5;
            color: #d2302c;
            text-decoration: none;
        }

        .btn-action.liked {
            color: #f43f5e;
        }

        .btn-create {
            background: linear-gradient(135deg, #d2302c, #ff4d4d);
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
        }

        .btn-create:hover {
            background: linear-gradient(135deg, #8b0000, #d2302c);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(210, 48, 44, 0.3);
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .empty-state i {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
        }
    </style>
</head>

<body>
    <?php include('../../includes/navbar.php'); ?>

    <div class="forum-header">
        <div class="container">
            <h1><i class="fas fa-comments mr-3"></i>Diễn đàn Y tế</h1>
            <p class="lead mb-0">Cộng đồng chia sẻ kiến thức, kinh nghiệm và hỏi đáp về sức khỏe</p>
            <?php
            
            $total_posts_stmt = $pdo->query("SELECT COUNT(*) as total FROM forum_posts");
            $total_posts = $total_posts_stmt->fetch()['total'] ?? 0;

            $total_comments_stmt = $pdo->query("SELECT COUNT(*) as total FROM forum_comments");
            $total_comments = $total_comments_stmt->fetch()['total'] ?? 0;

            $total_members_stmt = $pdo->query("SELECT (SELECT COUNT(*) FROM patreg) + (SELECT COUNT(*) FROM doctb) as total");
            $total_members = $total_members_stmt->fetch()['total'] ?? 0;
            ?>
            <div class="forum-stats">
                <div class="forum-stat-item">
                    <i class="fas fa-file-alt"></i>
                    <span><?php echo number_format($total_posts); ?> bài viết</span>
                </div>
                <div class="forum-stat-item">
                    <i class="fas fa-comments"></i>
                    <span><?php echo number_format($total_comments); ?> bình luận</span>
                </div>
                <div class="forum-stat-item">
                    <i class="fas fa-users"></i>
                    <span><?php echo number_format($total_members); ?> thành viên</span>
                </div>
            </div>
        </div>
    </div>

    <div class="container pb-5">
        
        <div class="search-filter-bar">
            <form method="GET" class="row align-items-end">
                <div class="col-md-4 mb-3 mb-md-0">
                    <label class="font-weight-600 mb-2"><i class="fas fa-search mr-1"></i>Tìm kiếm</label>
                    <input type="text" name="search" class="form-control" placeholder="Tìm bài viết..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                </div>
                <div class="col-md-3 mb-3 mb-md-0">
                    <label class="font-weight-600 mb-2"><i class="fas fa-folder mr-1"></i>Danh mục</label>
                    <select name="category" class="custom-select">
                        <option value="">Tất cả</option>
                        <option value="general" <?php echo (($_GET['category'] ?? '') === 'general') ? 'selected' : ''; ?>>Tổng quát</option>
                        <option value="health_tips" <?php echo (($_GET['category'] ?? '') === 'health_tips') ? 'selected' : ''; ?>>Mẹo sức khỏe</option>
                        <option value="qa" <?php echo (($_GET['category'] ?? '') === 'qa') ? 'selected' : ''; ?>>Hỏi đáp</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3 mb-md-0">
                    <label class="font-weight-600 mb-2"><i class="fas fa-sort mr-1"></i>Sắp xếp</label>
                    <select name="sort" class="custom-select">
                        <option value="newest" <?php echo (($_GET['sort'] ?? '') === 'newest') ? 'selected' : ''; ?>>Mới nhất</option>
                        <option value="popular" <?php echo (($_GET['sort'] ?? '') === 'popular') ? 'selected' : ''; ?>>Phổ biến</option>
                        <option value="most_viewed" <?php echo (($_GET['sort'] ?? '') === 'most_viewed') ? 'selected' : ''; ?>>Xem nhiều</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-block" style="background: linear-gradient(135deg, #d2302c, #ff4d4d); color: white; font-weight: 600;">
                        <i class="fas fa-filter mr-1"></i>Lọc
                    </button>
                </div>
            </form>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <?php if (empty($posts)): ?>
                    <div class="empty-state">
                        <i class="fas fa-comment-slash"></i>
                        <h4>Chưa có bài viết nào</h4>
                        <p class="text-muted">Hãy là người đầu tiên chia sẻ!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
                        <div class="post-card">
                            <div class="post-header">
                                <div class="author-info">
                                    <div class="author-avatar">
                                        <?php echo strtoupper(substr($post['author_name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <div class="font-weight-600"><?php echo htmlspecialchars($post['author_name']); ?></div>
                                        <div class="text-muted small"><?php echo date('d/m/Y H:i', strtotime($post['created_at'])); ?></div>
                                    </div>
                                </div>
                                <?php if ($post['user_type'] === 'doctor'): ?>
                                    <span class="badge-doctor"><i class="fas fa-user-md mr-1"></i>Bác sĩ</span>
                                <?php elseif ($post['user_type'] === 'patient'): ?>
                                    <span class="badge-patient"><i class="fas fa-user mr-1"></i>Bệnh nhân</span>
                                <?php else: ?>
                                    <span class="badge-admin"><i class="fas fa-shield-alt mr-1"></i>Admin</span>
                                <?php endif; ?>
                            </div>

                            <a href="post.php?id=<?php echo $post['id']; ?>" style="text-decoration: none;">
                                <h3 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h3>
                            </a>

                            <div class="post-excerpt">
                                <?php echo nl2br(htmlspecialchars(substr($post['content'], 0, 200))); ?><?php echo strlen($post['content']) > 200 ? '...' : ''; ?>
                            </div>

                            <?php if (!empty($post['tags'])): ?>
                                <div class="post-tags">
                                    <?php foreach (explode(',', $post['tags']) as $tag):
                                        $tag = trim($tag);
                                        $tag = ltrim($tag, '#'); 
                                    ?>
                                        <span class="tag">#<?php echo htmlspecialchars($tag); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <div class="post-footer">
                                <?php if ($isLoggedIn): ?>
                                    <?php $userLiked = !empty($post['user_liked']); ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="toggle_like">
                                        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                        <button type="submit" class="btn-action <?php echo $userLiked ? 'liked' : ''; ?>">
                                            <i class="<?php echo $userLiked ? 'fas' : 'far'; ?> fa-heart"></i>
                                            <span><?php echo $post['like_count']; ?></span>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="btn-action">
                                        <i class="far fa-heart"></i>
                                        <span><?php echo $post['like_count']; ?></span>
                                    </span>
                                <?php endif; ?>

                                <a href="post.php?id=<?php echo $post['id']; ?>#comments" class="btn-action">
                                    <i class="far fa-comment"></i>
                                    <span><?php echo $post['comment_count']; ?></span>
                                </a>

                                <span class="btn-action">
                                    <i class="far fa-eye"></i>
                                    <span><?php echo $post['views']; ?></span>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="col-lg-4">
                <?php if ($isLoggedIn): ?>
                    <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-pen-fancy" style="font-size: 3rem; color: #d2302c; margin-bottom: 1rem;"></i>
                            <h5 class="font-weight-700 mb-3">Chia sẻ câu chuyện của bạn</h5>
                            <p class="text-muted mb-3">Hãy chia sẻ kinh nghiệm hoặc đặt câu hỏi về sức khỏe</p>
                            <a href="create.php" class="btn btn-create btn-block">
                                <i class="fas fa-plus mr-2"></i>Viết bài mới
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-sign-in-alt" style="font-size: 3rem; color: #d2302c; margin-bottom: 1rem;"></i>
                            <h5 class="font-weight-700 mb-3">Đăng nhập để tham gia</h5>
                            <p class="text-muted mb-3">Đăng nhập để viết bài và tương tác</p>
                            <a href="../auth/login.php" class="btn btn-create btn-block">
                                <i class="fas fa-sign-in-alt mr-2"></i>Đăng nhập
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                    <div class="card-body p-4">
                        <h5 class="font-weight-700 mb-3"><i class="fas fa-info-circle mr-2 text-info"></i>Hướng dẫn</h5>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2"><i class="fas fa-check text-success mr-2"></i>Tôn trọng người khác</li>
                            <li class="mb-2"><i class="fas fa-check text-success mr-2"></i>Chia sẻ kinh nghiệm thực tế</li>
                            <li class="mb-2"><i class="fas fa-check text-success mr-2"></i>Không spam hoặc quảng cáo</li>
                            <li class="mb-0"><i class="fas fa-check text-success mr-2"></i>Luôn tham khảo ý kiến bác sĩ</li>
                        </ul>
                    </div>
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

    
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="footer-brand">
                        <i class="fas fa-hospital"></i> Global Hospitals
                    </div>
                    <p>Hệ thống quản lý bệnh viện hiện đại, chuyên nghiệp và tiện lợi.</p>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Liên kết nhanh</h5>
                    <ul class="footer-links">
                        <li><a href="../../index.php">Trang chủ</a></li>
                        <li><a href="../reviews.php">Đánh giá</a></li>
                        <li><a href="../contact.php">Liên hệ</a></li>
                        <li><a href="index.php">Diễn đàn</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Liên hệ</h5>
                    <ul class="footer-links">
                        <li><i class="fas fa-envelope"></i> stu735105020@hnue.edu.vn</li>
                        <li><i class="fas fa-phone"></i> (84) 123-456-789</li>
                        <li><i class="fas fa-map-marker-alt"></i> 136 Xuân Thuỷ, Trường Đại học Sư phạm Hà Nội, Việt Nam</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; Nhóm 17 - Hệ thống đặt lịch khám. All rights reserved.</p>
            </div>
        </div>
    </footer>

    
    <div class="medical-lantern-container left-side">
        <div class="medical-string"></div>
        <div class="medical-lantern">
            <div class="medical-lantern-top"></div>
            <div class="medical-lantern-body">
                <span class="medical-lantern-text">Xuân</span>
            </div>
            <div class="medical-lantern-bottom"></div>
            <div class="medical-tassels">
                <span></span><span></span><span></span>
            </div>
            <div class="medical-scroll">
                <div class="medical-scroll-text">
                    <span>Chúc</span>
                    <span>Tết</span>
                    <span>Đến</span>
                    <span>Trăm</span>
                    <span>Điều</span>
                    <span>Như</span>
                    <span>Ý</span>
                </div>
            </div>
        </div>
    </div>

    <div class="medical-lantern-container right-side">
        <div class="medical-string"></div>
        <div class="medical-lantern">
            <div class="medical-lantern-top"></div>
            <div class="medical-lantern-body">
                <span class="medical-lantern-text">2026</span>
            </div>
            <div class="medical-lantern-bottom"></div>
            <div class="medical-tassels">
                <span></span><span></span><span></span>
            </div>
            <div class="medical-scroll">
                <div class="medical-scroll-text">
                    <span>Mừng</span>
                    <span>Xuân</span>
                    <span>Sang</span>
                    <span>Vạn</span>
                    <span>Sự</span>
                    <span>Thành</span>
                    <span>Công</span>
                </div>
            </div>
        </div>
    </div>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Mr+Dafoe&family=Mea+Culpa&display=swap');

        .footer {
            background: linear-gradient(135deg, #d2302c 0%, #8b0000 50%, #d2302c 100%);
            color: #ffffff;
            padding: 3rem 0 1rem;
            position: relative;
            overflow: hidden;
            margin-top: 5rem;
        }

        .footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #ffd700 0%, #d4af37 50%, #ffd700 100%);
            box-shadow: 0 2px 8px rgba(255, 215, 0, 0.5);
        }

        .footer-brand {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #ffd700;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .footer-links {
            list-style: none;
            padding: 0;
        }

        .footer-links li {
            margin-bottom: 0.5rem;
        }

        .footer-links a {
            color: #ffe6e6;
            text-decoration: none;
            transition: all 0.3s;
            font-weight: 500;
        }

        .footer-links a:hover {
            color: #ffd700;
            text-shadow: 0 0 8px rgba(255, 215, 0, 0.6);
            transform: translateX(5px);
            display: inline-block;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            border-top: 2px solid rgba(255, 215, 0, 0.3);
            color: #ffe6e6;
            font-weight: 500;
        }

        .medical-lantern-container {
            position: fixed;
            top: 0;
            z-index: 9999;
            pointer-events: none;
            transform-origin: top center;
            animation: medical-swing 4s ease-in-out infinite alternate;
        }

        .left-side {
            left: 40px
        }

        .right-side {
            right: 40px;
            animation-delay: 1s
        }

        .medical-string {
            width: 2px;
            height: 70px;
            margin: 0 auto;
            background: linear-gradient(to bottom, #cfc09f, #b8860b);
            position: relative;
        }

        .medical-string::before {
            content: '';
            position: absolute;
            top: -6px;
            left: -4px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: radial-gradient(circle, #ffd700, #b8860b);
            box-shadow: 0 1px 2px rgba(0, 0, 0, .35), inset 0 1px 1px rgba(255, 255, 255, .6);
        }

        .medical-lantern {
            position: relative;
            pointer-events: auto;
        }

        .medical-lantern-body {
            width: 120px;
            height: 100px;
            border-radius: 35px;
            background: radial-gradient(circle at 30% 30%, #ff5a5a, #7a0000);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow:
                inset 0 0 12px rgba(255, 200, 120, .35),
                0 0 10px rgba(255, 140, 60, .25),
                0 0 20px rgba(255, 120, 40, .15);
            animation: medical-glow 3s ease-in-out infinite;
        }

        .medical-lantern-text {
            font-family: 'Mr Dafoe', cursive;
            font-size: 36px;
            color: #ffd700;
            text-shadow: 0 0 4px rgba(255, 220, 150, .8), 0 0 8px rgba(255, 180, 100, .5);
        }

        .medical-lantern-top,
        .medical-lantern-bottom {
            width: 60px;
            height: 12px;
            margin: 0 auto;
            background: linear-gradient(90deg, #b8860b, #ffd700, #b8860b);
        }

        .medical-lantern-top {
            margin-bottom: -5px
        }

        .medical-lantern-bottom {
            margin-top: -5px
        }

        .medical-tassels {
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            text-align: center;
            transition: opacity .25s ease;
        }

        .medical-tassels span {
            display: inline-block;
            width: 4px;
            height: 50px;
            margin: 0 2px;
            background: linear-gradient(to bottom, #d2302c, #ff4d4d);
            border-radius: 0 0 5px 5px;
            animation: medical-tassel-sway 2s ease-in-out infinite alternate;
        }

        .medical-tassels span:nth-child(2) {
            height: 65px
        }

        .medical-scroll {
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            opacity: 0;
            background:
                linear-gradient(to right, rgba(255, 255, 255, .06), rgba(0, 0, 0, .15), rgba(255, 255, 255, .06)),
                #8b0000;
            border: 2px solid #d4af37;
            box-shadow:
                0 0 0 3px #8b0000,
                0 0 0 5px #d4af37,
                0 6px 12px rgba(0, 0, 0, .4);
            border-radius: 2px;
            overflow: visible;
            transition: width .45s ease, opacity .25s ease;
        }

        .medical-scroll::before {
            content: '';
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 2px;
            height: 10px;
            background: #ffd700;
        }

        .medical-scroll::after {
            content: '';
            position: absolute;
            bottom: -12px;
            left: -12px;
            right: -12px;
            height: 14px;
            background: linear-gradient(to right, #8a6e2f, #ffd700, #8a6e2f);
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, .45);
        }

        .medical-scroll-text {
            font-family: 'Mea Culpa', cursive;
            font-size: 26px;
            color: #ffd700;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            padding: 16px 8px 20px;
            line-height: 1.15;
            text-shadow: 0 1px 1px rgba(0, 0, 0, .6), 0 0 6px rgba(255, 215, 0, .4);
        }

        .medical-lantern:hover .medical-tassels {
            opacity: 0
        }

        .medical-lantern:hover .medical-scroll {
            width: 82px;
            opacity: 1
        }

        @keyframes medical-swing {
            from {
                transform: rotate(-3deg)
            }

            to {
                transform: rotate(3deg)
            }
        }

        @keyframes medical-tassel-sway {
            from {
                transform: rotate(2deg)
            }

            to {
                transform: rotate(-2deg)
            }
        }

        @keyframes medical-glow {
            0% {
                filter: brightness(1)
            }

            50% {
                filter: brightness(1.08)
            }

            100% {
                filter: brightness(1)
            }
        }

        @media (min-width:821px) and (max-width:1024px) {
            .medical-lantern-container {
                transform: scale(.85)
            }
        }

        @media (min-width:1025px) and (max-width:1280px) {
            .medical-lantern-container {
                transform: scale(.95)
            }
        }
    </style>

    <div class="tet_bottom"><img src="https://blogger.googleusercontent.com/img/b/R29vZ2xl/AVvXsEgwruFlhClo3FUKNBQtDvqWYiDVOoi-IT7Jy4R11OU5HaOFR2N7CcX5sH4FWQI_GRoVrx4Hd5pVQREJ_QsAjvSA41v25TW0LEGW2jb8s3J2QwCrXp4qsMqdvxUZz9lglGyxL4YQxIbbf17zyqd99Rr28rDzx-foaXJRQ13kQUAblMtlt4U1rKMYbHkn5w/s16000/bottom-1.png" alt="Trang trí Tết phía dưới" /></div>

    <style type="text/css">
        .tet_bottom {
            position: fixed;
            bottom: 0;
            left: 80px;
            z-index: 99;
            width: 320px;
            pointer-events: none;
        }

        @media (max-width: 1331px) {

            .tet_left,
            .tet_right,
            .tet_bottom {
                display: none !important;
            }
        }
    </style>

</body>

</html>