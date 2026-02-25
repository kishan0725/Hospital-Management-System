<?php
session_start();
$base_path = '../../';
require_once '../../config.php';
require_once '../../includes/forum_functions.php';


$post_id = isset($_GET['id']) ? intval($_GET['id']) : 0;


$post = getForumPost($pdo, $post_id);

if (!$post) {
    header('Location: index.php');
    exit;
}


$comments = getForumComments($pdo, $post_id);


$is_logged_in = isset($_SESSION['patientSession']) || isset($_SESSION['doctorSession']) || isset($_SESSION['adminSession']);


$user_type = null;
$user_id = null;
if (isset($_SESSION['patientSession'])) {
    $user_type = 'patient';
    $user_id = $_SESSION['patientSession'];
} elseif (isset($_SESSION['doctorSession'])) {
    $user_type = 'doctor';
    $user_id = $_SESSION['doctorSession'];
} elseif (isset($_SESSION['adminSession'])) {
    $user_type = 'admin';
    $user_id = 1;
}


if (isset($_POST['like_action']) && $is_logged_in) {
    toggleForumLike($pdo, $user_id, $user_type, $post_id, 'post');
    header('Location: post.php?id=' . $post_id);
    exit;
}


if (isset($_POST['action']) && $_POST['action'] === 'like_comment' && $is_logged_in) {
    $comment_id = intval($_POST['comment_id']);
    toggleForumLike($pdo, $user_id, $user_type, $comment_id, 'comment');
    header('Location: post.php?id=' . $post_id);
    exit;
}


if (isset($_POST['submit_comment']) && $is_logged_in) {
    $comment = trim($_POST['comment'] ?? '');
    $parent_id = isset($_POST['parent_id']) ? intval($_POST['parent_id']) : null;

    if (!empty($comment)) {
        $data = [
            'post_id' => $post_id,
            'user_id' => $user_id,
            'user_type' => $user_type,
            'content' => $comment,
            'parent_id' => $parent_id
        ];

        addForumComment($pdo, $data);
        header('Location: post.php?id=' . $post_id);
        exit;
    }
}


incrementPostViews($pdo, $post_id);


$author_name = '';
$author_email = '';
if ($post['user_type'] == 'doctor') {
    $stmt = $pdo->prepare("SELECT docFname, docEmail FROM doctb WHERE docId = ?");
    $stmt->execute([$post['user_id']]);
    $author = $stmt->fetch(PDO::FETCH_ASSOC);
    $author_name = $author['docFname'] ?? 'Bác sĩ';
    $author_email = $author['docEmail'] ?? '';
} elseif ($post['user_type'] == 'patient') {
    $stmt = $pdo->prepare("SELECT fname, email FROM patreg WHERE pid = ?");
    $stmt->execute([$post['user_id']]);
    $author = $stmt->fetch(PDO::FETCH_ASSOC);
    $author_name = $author['fname'] ?? 'Bệnh nhân';
    $author_email = $author['email'] ?? '';
} else {
    $author_name = 'Quản trị viên';
}

$author_avatar = !empty($author_email) ? 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($author_email))) . '?s=80&d=identicon' : '';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($post['title']) ?> - Diễn đàn</title>
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
            margin-top: 70px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 12px rgba(210, 48, 44, 0.15);
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            border-radius: 50%;
            transform: translate(-30%, -30%);
        }

        .page-header .container {
            position: relative;
            z-index: 1;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: rgba(255, 255, 255, 0.25);
            color: white;
            padding: 0.65rem 1.5rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .back-link:hover {
            background: rgba(255, 255, 255, 0.35);
            color: white;
            text-decoration: none;
            transform: translateX(-5px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .back-link i {
            font-size: 1rem;
        }

        .post-container {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }

        .post-header {
            border-bottom: 2px solid #f3f4f6;
            padding-bottom: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .post-title {
            font-size: 1.875rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 1rem;
        }

        .post-meta {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            flex-wrap: wrap;
        }

        .author-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .author-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ff4d4d, #ff6b6b);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            overflow: hidden;
        }

        .author-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .author-details {
            display: flex;
            flex-direction: column;
        }

        .author-name {
            font-weight: 600;
            color: #111827;
        }

        .post-date {
            font-size: 0.875rem;
            color: #6b7280;
        }

        .author-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .author-badge.doctor {
            background: linear-gradient(135deg, #ffd700, #d4af37);
            color: #8b0000;
        }

        .author-badge.patient {
            background: linear-gradient(135deg, #ff4d4d, #ff6b6b);
            color: white;
        }

        .author-badge.admin {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
        }

        .post-stats {
            display: flex;
            gap: 1.5rem;
            font-size: 0.875rem;
            color: #6b7280;
        }

        .post-stat {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .post-content {
            font-size: 1rem;
            line-height: 1.75;
            color: #374151;
            margin-bottom: 1.5rem;
        }

        .post-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .post-tag {
            background: #fff5f5;
            color: #d2302c;
            padding: 0.375rem 0.875rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .post-actions {
            display: flex;
            gap: 1rem;
            padding-top: 1.5rem;
            border-top: 2px solid #f3f4f6;
        }

        .btn-like {
            background: linear-gradient(135deg, #d2302c, #ff4d4d);
            color: white;
            border: none;
            padding: 0.625rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-like:hover {
            background: linear-gradient(135deg, #8b0000, #d2302c);
            transform: translateY(-2px);
        }

        .btn-like.liked {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }

        .comments-section {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .comments-header {
            font-size: 1.5rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f3f4f6;
        }

        .comment-form {
            margin-bottom: 2rem;
        }

        .comment-form textarea {
            border-radius: 10px;
            border: 2px solid #e5e7eb;
            padding: 0.75rem 1rem;
            transition: all 0.3s;
        }

        .comment-form textarea:focus {
            border-color: #d2302c;
            box-shadow: 0 0 0 3px rgba(210, 48, 44, 0.1);
        }

        .btn-comment {
            background: linear-gradient(135deg, #d2302c, #ff4d4d);
            color: white;
            border: none;
            padding: 0.625rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-comment:hover {
            background: linear-gradient(135deg, #8b0000, #d2302c);
            transform: translateY(-2px);
        }

        .comment-list {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .comment-item {
            display: flex;
            gap: 1rem;
            padding: 1rem;
            border-radius: 10px;
            background: #f9fafb;
            transition: all 0.3s;
        }

        .comment-item:hover {
            background: #f3f4f6;
        }

        .comment-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ff4d4d, #ff6b6b);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            overflow: hidden;
            flex-shrink: 0;
        }

        .comment-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .comment-content {
            flex: 1;
        }

        .comment-author {
            font-weight: 600;
            color: #111827;
            margin-bottom: 0.25rem;
        }

        .comment-date {
            font-size: 0.75rem;
            color: #9ca3af;
            margin-bottom: 0.5rem;
        }

        .comment-text {
            color: #374151;
            line-height: 1.6;
            margin-bottom: 0.5rem;
        }

        .comment-actions {
            display: flex;
            gap: 1rem;
            margin-top: 0.5rem;
        }

        .comment-btn {
            background: none;
            border: none;
            color: #9ca3af;
            font-size: 0.875rem;
            cursor: pointer;
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            text-decoration: none;
        }

        .comment-btn:hover {
            color: #d2302c;
            background: #fff5f5;
            text-decoration: none;
        }

        .comment-btn.liked {
            color: #f43f5e;
        }

        .reply-form {
            margin-top: 1rem;
            padding-left: 3rem;
            display: none;
        }

        .reply-form.active {
            display: block;
        }

        .nested-comment {
            margin-left: 3rem;
            margin-top: 1rem;
            padding-left: 1rem;
            border-left: 3px solid #e5e7eb;
        }

        .no-comments {
            text-align: center;
            padding: 3rem;
            color: #9ca3af;
        }

        .login-prompt {
            text-align: center;
            padding: 2rem;
            background: #fef3c7;
            border-radius: 10px;
            margin-bottom: 2rem;
        }

        .login-prompt a {
            color: #d2302c;
            font-weight: 600;
        }

        .badge-purple {
            background: linear-gradient(135deg, #d2302c, #ff4d4d);
            color: white;
            padding: 0.35rem 0.65rem;
            border-radius: 0.25rem;
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
        </div>
    </div>

    <div class="container pb-5">
        <div class="row">
            <div class="col-lg-8">
                <div class="post-container">
                    <div class="post-header">
                        <h1 class="post-title"><?= htmlspecialchars($post['title']) ?></h1>

                        <div class="post-meta">
                            <div class="author-info">
                                <div class="author-avatar">
                                    <?php if ($author_avatar): ?>
                                        <img src="<?= $author_avatar ?>" alt="<?= htmlspecialchars($author_name) ?>">
                                    <?php else: ?>
                                        <?= strtoupper(substr($author_name, 0, 1)) ?>
                                    <?php endif; ?>
                                </div>
                                <div class="author-details">
                                    <div class="author-name"><?= htmlspecialchars($author_name) ?></div>
                                    <div class="post-date">
                                        <i class="far fa-clock mr-1"></i>
                                        <?= date('d/m/Y H:i', strtotime($post['created_at'])) ?>
                                    </div>
                                </div>
                                <span class="author-badge <?= $post['user_type'] ?>">
                                    <i class="fas fa-<?= $post['user_type'] == 'doctor' ? 'user-md' : ($post['user_type'] == 'admin' ? 'crown' : 'user') ?>"></i>
                                    <?= $post['user_type'] == 'doctor' ? 'Bác sĩ' : ($post['user_type'] == 'admin' ? 'Admin' : 'Bệnh nhân') ?>
                                </span>
                            </div>
                        </div>

                        <div class="post-stats mt-3">
                            <div class="post-stat">
                                <i class="fas fa-eye"></i>
                                <span><?= number_format($post['views'] ?? 0) ?> lượt xem</span>
                            </div>
                            <div class="post-stat">
                                <i class="fas fa-heart"></i>
                                <span><?= number_format($post['like_count'] ?? 0) ?> thích</span>
                            </div>
                            <div class="post-stat">
                                <i class="fas fa-comment"></i>
                                <span><?= count($comments) ?> bình luận</span>
                            </div>
                        </div>
                    </div>

                    <div class="post-content">
                        <?= nl2br(htmlspecialchars($post['content'])) ?>
                    </div>

                    <?php if (!empty($post['tags'])): ?>
                        <div class="post-tags">
                            <?php foreach (explode(',', $post['tags']) as $tag):
                                $tag = trim($tag);
                                $tag = ltrim($tag, '#'); 
                            ?>
                                <span class="post-tag">#<?= htmlspecialchars($tag) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($is_logged_in): ?>
                        <div class="post-actions">
                            <form method="POST" style="display:inline;">
                                <button type="submit" name="like_action" class="btn btn-like">
                                    <i class="fas fa-heart mr-2"></i>Thích
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="comments-section">
                    <h3 class="comments-header">
                        <i class="fas fa-comments mr-2"></i>
                        Bình luận (<?= count($comments) ?>)
                    </h3>

                    <?php if ($is_logged_in): ?>
                        <div class="comment-form">
                            <form method="POST">
                                <div class="form-group">
                                    <textarea name="comment" class="form-control" rows="3" placeholder="Viết bình luận của bạn..." required></textarea>
                                </div>
                                <button type="submit" name="submit_comment" class="btn btn-comment">
                                    <i class="fas fa-paper-plane mr-2"></i>Gửi bình luận
                                </button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="login-prompt">
                            <i class="fas fa-lock fa-2x mb-3" style="color: #f59e0b;"></i>
                            <p class="mb-2">Vui lòng <a href="../auth/login.php">đăng nhập</a> để bình luận</p>
                        </div>
                    <?php endif; ?>

                    <?php if (count($comments) > 0): ?>
                        <div class="comment-list">
                            <?php foreach ($comments as $comment):
                                
                                $commenter_name = '';
                                $commenter_email = '';
                                if ($comment['user_type'] == 'doctor') {
                                    $stmt = $pdo->prepare("SELECT docFname, docEmail FROM doctb WHERE docId = ?");
                                    $stmt->execute([$comment['user_id']]);
                                    $commenter = $stmt->fetch(PDO::FETCH_ASSOC);
                                    $commenter_name = $commenter['docFname'] ?? 'Bác sĩ';
                                    $commenter_email = $commenter['docEmail'] ?? '';
                                } elseif ($comment['user_type'] == 'patient') {
                                    $stmt = $pdo->prepare("SELECT fname, email FROM patreg WHERE pid = ?");
                                    $stmt->execute([$comment['user_id']]);
                                    $commenter = $stmt->fetch(PDO::FETCH_ASSOC);
                                    $commenter_name = $commenter['fname'] ?? 'Bệnh nhân';
                                    $commenter_email = $commenter['email'] ?? '';
                                } else {
                                    $commenter_name = 'Quản trị viên';
                                }
                                $commenter_avatar = !empty($commenter_email) ? 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($commenter_email))) . '?s=80&d=identicon' : '';
                            ?>
                                <div class="comment-item">
                                    <div class="comment-avatar">
                                        <?php if ($commenter_avatar): ?>
                                            <img src="<?= $commenter_avatar ?>" alt="<?= htmlspecialchars($commenter_name) ?>">
                                        <?php else: ?>
                                            <?= strtoupper(substr($commenter_name, 0, 1)) ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="comment-content">
                                        <div class="comment-author">
                                            <?= htmlspecialchars($commenter_name) ?>
                                            <span class="author-badge <?= $comment['user_type'] ?> ml-2">
                                                <?= $comment['user_type'] == 'doctor' ? 'Bác sĩ' : ($comment['user_type'] == 'admin' ? 'Admin' : 'BN') ?>
                                            </span>
                                        </div>
                                        <div class="comment-date">
                                            <?= date('d/m/Y H:i', strtotime($comment['created_at'])) ?>
                                        </div>
                                        <div class="comment-text">
                                            <?= nl2br(htmlspecialchars($comment['content'])) ?>
                                        </div>
                                        <?php if ($is_logged_in): ?>
                                            <div class="comment-actions">
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="action" value="like_comment">
                                                    <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                                                    <button type="submit" class="comment-btn <?= hasForumLiked($pdo, $user_id, $user_type, $comment['id'], 'comment') ? 'liked' : '' ?>">
                                                        <i class="<?= hasForumLiked($pdo, $user_id, $user_type, $comment['id'], 'comment') ? 'fas' : 'far' ?> fa-heart"></i>
                                                        <span><?= $comment['like_count'] ?? 0 ?></span>
                                                    </button>
                                                </form>
                                                <button class="comment-btn" onclick="toggleReplyForm(<?= $comment['id'] ?>)">
                                                    <i class="far fa-comment"></i>
                                                    <span>Trả lời</span>
                                                </button>
                                            </div>
                                            <div class="reply-form" id="reply-form-<?= $comment['id'] ?>">
                                                <form method="POST">
                                                    <input type="hidden" name="submit_comment" value="1">
                                                    <input type="hidden" name="parent_id" value="<?= $comment['id'] ?>">
                                                    <div class="form-group">
                                                        <textarea name="comment" class="form-control" rows="2" placeholder="Viết câu trả lời..." required></textarea>
                                                    </div>
                                                    <div class="d-flex gap-2">
                                                        <button type="submit" name="submit_comment" class="btn btn-sm" style="background: linear-gradient(135deg, #d2302c, #ff4d4d); color: white;">
                                                            <i class="fas fa-paper-plane mr-1"></i>Gửi
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-secondary" onclick="toggleReplyForm(<?= $comment['id'] ?>)">
                                                            Hủy
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-comments">
                            <i class="fas fa-comment-slash fa-3x mb-3" style="color: #d1d5db;"></i>
                            <p>Chưa có bình luận nào. Hãy là người đầu tiên!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="post-container">
                    <h5 class="mb-3"><i class="fas fa-info-circle mr-2" style="color: #d2302c;"></i>Thông tin</h5>
                    <div class="mb-3">
                        <small class="text-muted d-block mb-1">Danh mục</small>
                        <span class="badge badge-purple"><?= htmlspecialchars($post['category'] ?? 'Tổng quát') ?></span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block mb-1">Trạng thái</small>
                        <span class="badge badge-success"><?= $post['privacy'] == 'public' ? 'Công khai' : 'Riêng tư' ?></span>
                    </div>
                    <hr>
                    <a href="create.php" class="btn btn-block" style="background: linear-gradient(135deg, #d2302c, #ff4d4d); color: white;">
                        <i class="fas fa-pen mr-2"></i>Viết bài mới
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        function toggleReplyForm(commentId) {
            const form = document.getElementById('reply-form-' + commentId);
            if (form.classList.contains('active')) {
                form.classList.remove('active');
            } else {
                // Hide all other reply forms
                document.querySelectorAll('.reply-form').forEach(f => f.classList.remove('active'));
                form.classList.add('active');
            }
        }
    </script>

    
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