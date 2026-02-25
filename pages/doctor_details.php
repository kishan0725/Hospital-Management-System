<!DOCTYPE html>
<html lang="vi">
<?php
session_start();
$base_path = '../';
require_once('../config.php');

$doc_id = $_GET['id'] ?? null;
if (!$doc_id) {
    header("Location: reviews.php");
    exit();
}


$stmt = $pdo->prepare("
    SELECT d.*, s.name_vi as spec_name 
    FROM doctb d 
    LEFT JOIN specializations s ON d.spec_id = s.id 
    WHERE d.id = ?
");
$stmt->execute([$doc_id]);
$doctor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$doctor) {
    die("Bác sĩ không tồn tại");
}


$stmt = $pdo->prepare("
    SELECT r.*, p.fname, p.lname 
    FROM doctor_ratings r
    JOIN patreg p ON r.patient_id = p.pid
    WHERE r.doctor_id = ?
    ORDER BY r.created_at DESC
");
$stmt->execute([$doc_id]);
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!isset($_SESSION['pid'])) {
        $error = "Vui lòng đăng nhập để đánh giá!";
    } else {
        $rating = $_POST['rating'];
        $review_text = $_POST['review'];
        $patient_id = $_SESSION['pid']; 

        
        try {
            $stmt = $pdo->prepare("
                INSERT INTO doctor_ratings (doctor_id, patient_id, rating, review, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$doc_id, $patient_id, $rating, $review_text]);

            
            
            $stmt = $pdo->prepare("SELECT AVG(rating) as avg_rate, COUNT(*) as total FROM doctor_ratings WHERE doctor_id = ?");
            $stmt->execute([$doc_id]);
            $stats = $stmt->fetch();

            
            $update = $pdo->prepare("UPDATE doctb SET average_rating = ?, total_ratings = ? WHERE id = ?");
            $update->execute([$stats['avg_rate'], $stats['total'], $doc_id]);

            $success = "Cảm ơn bạn đã đánh giá!";
            
            $stmt = $pdo->prepare("
                SELECT r.*, p.fname, p.lname 
                FROM doctor_ratings r
                JOIN patreg p ON r.patient_id = p.pid
                WHERE r.doctor_id = ?
                ORDER BY r.created_at DESC
            ");
            $stmt->execute([$doc_id]);
            $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

            
            $stmt = $pdo->prepare("SELECT * FROM doctb WHERE id = ?");
            $stmt->execute([$doc_id]);
            $doc_refreshed = $stmt->fetch();
            $doctor['average_rating'] = $doc_refreshed['average_rating'];
            $doctor['total_ratings'] = $doc_refreshed['total_ratings'];
        } catch (Exception $e) {
            $error = "Lỗi: " . $e->getMessage();
        }
    }
}
?>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Chi tiết Bác sĩ - <?php echo htmlspecialchars($doctor['fullname']); ?></title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            background: #f9fafb;
            font-family: 'Inter', sans-serif;
        }

        .doc-header {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .rating-stars {
            color: #f59e0b;
        }

        .review-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border: 1px solid #e5e7eb;
        }

        .rating-input {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
            gap: 0.5rem;
        }

        .rating-input input {
            display: none;
        }

        .rating-input label {
            cursor: pointer;
            font-size: 1.5rem;
            color: #d1d5db;
            transition: color 0.2s;
        }

        .rating-input input:checked~label,
        .rating-input label:hover,
        .rating-input label:hover~label {
            color: #f59e0b;
        }
    </style>
</head>

<body>
    <?php include('../includes/navbar.php'); ?>

    <div class="container py-5" style="margin-top: 80px;">
        <a href="reviews.php" class="btn btn-outline-secondary mb-3"><i class="fas fa-arrow-left"></i> Quay lại</a>

        <div class="doc-header">
            <div class="row align-items-center">
                <div class="col-md-3 text-center">
                    <?php
                    $avatar = !empty($doctor['avatar']) ? $doctor['avatar'] : 'images/user.png';
                    if (strpos($avatar, 'http') !== 0) {
                        $avatar = '../' . $avatar;
                    }
                    ?>
                    <img src="<?php echo $avatar; ?>"
                        class="rounded-circle img-fluid mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                </div>
                <div class="col-md-9">
                    <h2 class="font-weight-bold mb-2"><?php echo htmlspecialchars($doctor['fullname']); ?></h2>
                    <h5 class="text-info mb-3"><?php echo htmlspecialchars($doctor['spec_name']); ?></h5>

                    <div class="d-flex align-items-center mb-4">
                        <div class="rating-stars mr-2">
                            <?php
                            $stars = round($doctor['average_rating'] ?? 0);
                            for ($i = 1; $i <= 5; $i++) {
                                echo $i <= $stars ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                            }
                            ?>
                        </div>
                        <span class="font-weight-bold h5 mb-0 mr-2"><?php echo number_format($doctor['average_rating'] ?? 0, 1); ?></span>
                        <span class="text-muted">(<?php echo $doctor['total_ratings']; ?> đánh giá)</span>
                    </div>

                    <p class="text-muted"><?php echo !empty($doctor['bio']) ? $doctor['bio'] : 'Bác sĩ chuyên khoa giàu kinh nghiệm...'; ?></p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <h4 class="mb-4">Đánh giá từ bệnh nhân</h4>

                <?php if (empty($reviews)): ?>
                    <div class="alert alert-light text-center">Chưa có đánh giá nào. Hãy là người đầu tiên!</div>
                <?php else: ?>
                    <?php foreach ($reviews as $rev): ?>
                        <div class="review-card">
                            <div class="d-flex justify-content-between mb-2">
                                <h6 class="font-weight-bold"><?php echo htmlspecialchars($rev['lname'] . ' ' . $rev['fname']); ?></h6>
                                <small class="text-muted"><?php echo date('d/m/Y', strtotime($rev['created_at'])); ?></small>
                            </div>
                            <div class="rating-stars mb-2" style="font-size: 0.8rem;">
                                <?php for ($i = 1; $i <= 5; $i++) echo $i <= $rev['rating'] ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>'; ?>
                            </div>
                            <p class="mb-0 text-secondary"><?php echo htmlspecialchars($rev['review']); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title font-weight-bold mb-3">Viết đánh giá</h5>
                        <?php
                        
                        $can_rate = false;
                        $role_message = "";

                        if (isset($_SESSION['patientSession'])) {
                            
                            
                            if (isset($_SESSION['pid'])) {
                                $can_rate = true;
                            } else {
                                $role_message = "Vui lòng đăng nhập lại để xác thực danh tính.";
                            }
                        } elseif (isset($_SESSION['doctorSession'])) {
                            $role_message = "Bác sĩ không thể thực hiện đánh giá.";
                        } elseif (isset($_SESSION['adminSession'])) {
                            $role_message = "Quản trị viên không thể thực hiện đánh giá.";
                        }
                        ?>

                        <?php if ($can_rate): ?>
                            <?php if (isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
                            <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

                            <form method="post">
                                <div class="form-group text-center">
                                    <label class="d-block mb-2">Chọn số sao</label>
                                    <div class="rating-input justify-content-center">
                                        <input type="radio" name="rating" id="star5" value="5" required><label for="star5"><i class="fas fa-star"></i></label>
                                        <input type="radio" name="rating" id="star4" value="4"><label for="star4"><i class="fas fa-star"></i></label>
                                        <input type="radio" name="rating" id="star3" value="3"><label for="star3"><i class="fas fa-star"></i></label>
                                        <input type="radio" name="rating" id="star2" value="2"><label for="star2"><i class="fas fa-star"></i></label>
                                        <input type="radio" name="rating" id="star1" value="1"><label for="star1"><i class="fas fa-star"></i></label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <textarea name="review" class="form-control" rows="4" placeholder="Chia sẻ trải nghiệm của bạn..." required></textarea>
                                </div>
                                <button type="submit" name="submit_review" class="btn btn-primary btn-block">Gửi đánh giá</button>
                            </form>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <?php if ($role_message): ?>
                                    <i class="fas fa-user-shield fa-2x text-muted mb-3"></i>
                                    <p class="text-muted"><?php echo $role_message; ?></p>
                                <?php else: ?>
                                    <i class="fas fa-lock fa-2x text-muted mb-3"></i>
                                    <p class="mb-3">Vui lòng đăng nhập tài khoản <strong>Bệnh nhân</strong> để đánh giá</p>
                                    <a href="../pages/auth/login.php" class="btn btn-outline-primary">Đăng nhập ngay</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
</body>

</html>