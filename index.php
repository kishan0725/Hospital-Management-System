<?php
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
	<title>Bệnh viện D.B.D - Hệ thống Quản lý Bệnh viện</title>
	<link rel="shortcut icon" type="image/x-icon" href="images/favicon.png" />

	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,700;1,700&display=swap" rel="stylesheet">

	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

	
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

	
	<link rel="stylesheet" href="assets/css/custom/global-improvements.css">

	<style>
		:root {
			--primary-gradient: linear-gradient(135deg, #d2302c 0%, #ff4d4d 50%, #ff6b6b 100%);
			--primary-color: #d2302c;
			--primary-dark: #8b0000;
			--secondary-color: #ffd700;
			--accent-gold: #d4af37;
			--health-red: #ff4d4d;
			--text-dark: #1e293b;
			--text-light: #64748b;
			--bg-light: #fff5f5;
		}

		* {
			margin: 0;
			padding: 0;
			box-sizing: border-box;
		}

		html {
			font-size: 16px;
			-webkit-text-size-adjust: 100%;
			-ms-text-size-adjust: 100%;
		}

		body {
			font-family: 'Inter', sans-serif;
			color: var(--text-dark);
			overflow-x: hidden;
			font-size: 1rem;
			line-height: 1.6;
		}

		/* Container customization */
		.container {
			max-width: 1100px;
			padding-left: 12px;
			padding-right: 12px;
		}

		@media (min-width: 1600px) {
			.container {
				max-width: 1200px;
			}
		}

		@media (min-width: 1920px) {
			.container {
				max-width: 1280px;
			}
		}

		/* Navbar */
		.navbar-custom {
			background: white;
			box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
			padding: 0.6rem 0;
			position: fixed;
			width: 100%;
			top: 0;
			z-index: 1000;
		}

		.navbar-brand {
			font-size: 1.3rem;
			font-weight: 700;
			background: var(--primary-gradient);
			-webkit-background-clip: text;
			-webkit-text-fill-color: transparent;
			background-clip: text;
			padding-left: 0.3rem;
		}

		.navbar-brand i {
			background: var(--primary-gradient);
			-webkit-background-clip: text;
			-webkit-text-fill-color: transparent;
			font-size: 1.3rem;
		}

		.nav-link-custom {
			color: var(--text-dark) !important;
			font-weight: 500;
			font-size: 0.875rem;
			margin: 0 0.6rem;
			transition: color 0.3s;
		}

		.nav-link-custom:hover {
			color: var(--primary-color) !important;
		}

		.btn-nav {
			padding: 0.4rem 1.1rem;
			border-radius: 7px;
			font-weight: 600;
			font-size: 0.85rem;
			transition: all 0.3s;
			margin-left: 0.4rem;
		}

		.btn-login {
			color: var(--primary-color);
			border: 2px solid var(--primary-color);
			background: transparent;
		}

		.btn-login:hover {
			background: var(--primary-color);
			color: white;
		}

		.btn-register {
			background: var(--primary-gradient);
			color: white;
			border: none;
			position: relative;
			overflow: hidden;
		}

		.btn-register::before {
			content: '';
			position: absolute;
			top: 0;
			left: -100%;
			width: 100%;
			height: 100%;
			background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
			transition: left 0.5s ease;
		}

		.btn-register:hover::before {
			left: 100%;
		}

		.btn-register:hover {
			transform: translateY(-2px);
			box-shadow: 0 10px 24px rgba(8, 145, 178, 0.3);
		}

		/* Hero Section */
		.hero-section {
			min-height: 100vh;
			background: linear-gradient(135deg, rgba(210, 48, 44, 0.75) 0%, rgba(139, 0, 0, 0.75) 100%), url('images/clinic/tet.png') center/cover no-repeat;
			display: flex;
			align-items: center;
			padding-top: 80px;
			position: relative;
			overflow: hidden;
		}

		.hero-section::before {
			content: '';
			background: radial-gradient(circle, rgba(255, 255, 255, 0.15) 0%, transparent 70%);
			border-radius: 50%;
			top: -250px;
			right: -250px;
			animation: float 8s ease-in-out infinite;
		}

		.hero-section::after {
			content: '';
			position: absolute;
			width: 450px;
			height: 450px;
			background: radial-gradient(circle, rgba(255, 255, 255, 0.12) 0%, transparent 70%);
			border-radius: 50%;
			bottom: -180px;
			left: -180px;
			animation: float 10s ease-in-out infinite reverse;
		}

		@keyframes float {

			0%,
			100% {
				transform: translate(0, 0) scale(1);
			}

			50% {
				transform: translate(30px, -30px) scale(1.05);
			}
		}

		.hero-content {
			position: relative;
			z-index: 2;
			color: white;
		}

		.hero-content h1 {
			font-family: 'Playfair Display', serif;
			font-size: 2.8rem;
			font-weight: 700;
			margin-bottom: 1rem;
			line-height: 1.25;
			text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.3);
			letter-spacing: 1px;
			font-style: italic;
			color: #ffffff;
		}

		.hero-content p {
			font-size: 0.95rem;
			margin-bottom: 1.5rem;
			opacity: 0.95;
			line-height: 1.6;
			font-weight: 500;
			letter-spacing: 0.2px;
			text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.2);
		}

		.hero-buttons .btn {
			padding: 0.75rem 1.8rem;
			font-size: 0.9rem;
			border-radius: 8px;
			font-weight: 600;
			margin-right: 0.8rem;
			margin-bottom: 0.8rem;
		}

		.btn-hero-primary {
			background: white;
			color: var(--primary-color);
			border: none;
		}

		.btn-hero-primary:hover {
			transform: translateY(-3px);
			box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
		}

		.btn-hero-secondary {
			background: transparent;
			color: white;
			border: 2px solid white;
		}

		.btn-hero-secondary:hover {
			background: white;
			color: var(--primary-color);
		}

		.hero-image {
			position: relative;
			z-index: 2;
			padding: 2rem;
			display: flex;
			align-items: center;
			justify-content: center;
		}

		.hero-image img {
			max-width: 90%;
			height: auto;
			filter: drop-shadow(0 20px 40px rgba(0, 0, 0, 0.2));
		}

		/* Features Section */
		.features-section {
			padding: 4rem 0;
			background: linear-gradient(135deg, #fff5f5 0%, #ffffff 50%, #fff5f5 100%);
		}

		.section-title {
			text-align: center;
			margin-bottom: 3rem;
		}

		.section-title h2 {
			font-size: 1.9rem;
			font-weight: 700;
			margin-bottom: 0.8rem;
			color: #8b0000;
			text-shadow: 1px 1px 2px rgba(139, 0, 0, 0.1);
		}

		.section-title p {
			font-size: 0.95rem;
			color: #d2302c;
			font-weight: 500;
		}

		.feature-card {
			background: linear-gradient(135deg, #ffffff 0%, #fffbfb 100%);
			padding: 1.6rem;
			border-radius: 12px;
			text-align: center;
			transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
			border: 2px solid rgba(255, 215, 0, 0.2);
			height: 100%;
			position: relative;
			overflow: hidden;
			box-shadow: 0 3px 10px rgba(210, 48, 44, 0.08);
		}

		.feature-card::before {
			content: '';
			position: absolute;
			top: 0;
			left: 0;
			right: 0;
			height: 4px;
			background: linear-gradient(90deg, #d2302c 0%, #ff6b6b 50%, #d2302c 100%);
			transform: scaleX(0);
			transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
		}

		.feature-card:hover::before {
			transform: scaleX(1);
		}

		.feature-card:hover {
			transform: translateY(-10px);
			box-shadow: 0 20px 40px rgba(210, 48, 44, 0.2);
			border-color: #ffd700;
			background: linear-gradient(135deg, #ffffff 0%, #fff5f5 100%);
		}

		.feature-icon {
			width: 60px;
			height: 60px;
			background: linear-gradient(135deg, #ff6b6b 0%, #d2302c 100%);
			border-radius: 12px;
			display: flex;
			align-items: center;
			justify-content: center;
			margin: 0 auto 1rem;
			box-shadow: 0 6px 14px rgba(210, 48, 44, 0.25);
			transition: all 0.35s ease;
		}

		.feature-card:hover .feature-icon {
			transform: scale(1.08) rotate(5deg);
			box-shadow: 0 10px 20px rgba(210, 48, 44, 0.35);
			background: linear-gradient(135deg, #ffd700 0%, #d4af37 100%);
		}

		.feature-icon i {
			font-size: 1.5rem;
			color: white;
		}

		.feature-card h3 {
			font-size: 1.15rem;
			font-weight: 600;
			margin-bottom: 0.7rem;
			color: #8b0000;
		}

		.feature-card p {
			color: #666;
			line-height: 1.55;
			font-size: 0.875rem;
		}

		/* Stats Section */
		.stats-section {
			padding: 4rem 0;
			background: var(--primary-gradient);
			color: white;
			position: relative;
			overflow: hidden;
		}

		.stats-section::before {
			content: '';
			position: absolute;
			top: 0;
			left: 0;
			right: 0;
			bottom: 0;
			background:
				radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
				radial-gradient(circle at 80% 50%, rgba(255, 255, 255, 0.08) 0%, transparent 50%);
			pointer-events: none;
		}

		.stat-item {
			text-align: center;
			padding: 1.5rem;
			position: relative;
			z-index: 1;
		}

		.stat-number {
			font-size: 2.4rem;
			font-weight: 800;
			margin-bottom: 0.4rem;
			text-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
		}

		.stat-label {
			font-size: 0.95rem;
			opacity: 0.9;
		}

		/* CTA Section */
		.cta-section {
			padding: 3.5rem 0;
			background: white;
		}

		.cta-content {
			text-align: center;
			max-width: 650px;
			margin: 0 auto;
		}

		.cta-content h2 {
			font-size: 1.9rem;
			font-weight: 700;
			margin-bottom: 1rem;
		}

		.cta-content p {
			font-size: 0.95rem;
			color: var(--text-light);
			margin-bottom: 1.5rem;
		}

		/* Footer - Tet Theme */
		.footer {
			background: linear-gradient(135deg, #d2302c 0%, #8b0000 50%, #d2302c 100%);
			color: #ffffff;
			padding: 3rem 0 1rem;
			position: relative;
			overflow: hidden;
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

		.footer-content {
			display: flex;
			justify-content: space-between;
			margin-bottom: 2rem;
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

		/* Responsive - Large screens */
		@media (min-width: 1400px) {
			.hero-content h1 {
				font-size: 3rem;
			}

			.hero-content p {
				font-size: 1rem;
			}

			.section-title h2 {
				font-size: 2.1rem;
			}

			.feature-card h3 {
				font-size: 1.25rem;
			}
		}

		@media (min-width: 1600px) {
			.hero-content h1 {
				font-size: 3.3rem;
			}

			.navbar-brand {
				font-size: 1.45rem;
			}

			.btn-nav {
				padding: 0.45rem 1.25rem;
				font-size: 0.88rem;
			}
		}

		/* Responsive - Tablets */
		@media (max-width: 992px) {
			.hero-content h1 {
				font-size: 2.4rem;
			}

			.hero-content p {
				font-size: 0.9rem;
			}

			.section-title h2 {
				font-size: 1.75rem;
			}
		}

		/* Responsive - Mobile */
		@media (max-width: 768px) {
			.hero-content h1 {
				font-size: 2.5rem;
			}

			.hero-content p {
				font-size: 1rem;
			}

			.section-title h2 {
				font-size: 2rem;
			}

			.stat-number {
				font-size: 2rem;
			}

			.navbar-brand {
				font-size: 1.5rem;
			}

			.btn-nav {
				padding: 0.5rem 1.2rem;
				font-size: 0.95rem;
			}
		}
	</style>

	
	<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>

<body>
	
	<?php include('includes/navbar.php'); ?>

	
	<section class="hero-section" id="home">
		<div class="container">
			<div class="row align-items-center">
				<div class="col-lg-6 hero-content">
					<?php
					
					$welcomeName = '';
					$isUserLoggedIn = false;

					if (isset($_SESSION['patientSession']) && isset($_SESSION['pid'])) {
						$isUserLoggedIn = true;
						try {
							$stmt = $pdo->prepare("SELECT fname, lname FROM patreg WHERE pid = ?");
							$stmt->execute([$_SESSION['pid']]);
							$user = $stmt->fetch(PDO::FETCH_ASSOC);
							if ($user) {
								$welcomeName = trim($user['lname'] . ' ' . $user['fname']);
							}
						} catch (Exception $e) {
							$welcomeName = '';
						}
					} elseif (isset($_SESSION['doctorSession']) && isset($_SESSION['doctor_id'])) {
						$isUserLoggedIn = true;
						try {
							$stmt = $pdo->prepare("SELECT fullname FROM doctb WHERE id = ?");
							$stmt->execute([$_SESSION['doctor_id']]);
							$user = $stmt->fetch(PDO::FETCH_ASSOC);
							if ($user && !empty($user['fullname'])) {
								$welcomeName = trim($user['fullname']);
							}
						} catch (Exception $e) {
							$welcomeName = '';
						}
					} elseif (isset($_SESSION['adminSession'])) {
						$isUserLoggedIn = true;
						$welcomeName = 'Quản trị viên';
					}

					if ($isUserLoggedIn && !empty($welcomeName)): ?>
						<h1>Chào mừng</h1>
						<h1 style="color: #ffd700; margin: 0.5rem 0;"><?php echo htmlspecialchars($welcomeName); ?></h1>
						<h1 style="margin-top: 0.5rem;">đến với </h1>
						<h1 class="gradient-text-yellow-orange" style="margin-top: 0.5rem; margin-bottom: 1rem; font-size: 2.8rem;"> Bệnh viện D.B.D</h1>
					<?php else: ?>
						<h1>Chào mừng</h1>
						<h1 style="margin-top: 0.5rem;">đến với </h1>
						<h1 class="gradient-text-yellow-orange" style="margin-top: 0.5rem; margin-bottom: 1rem; font-size: 2.8rem;"> Bệnh viện D.B.D</h1>
					<?php endif; ?>

					<style>
						.gradient-text-yellow-orange {
							background: linear-gradient(135deg, #ffff99 0%, #ffff66 25%, #ffeb99 50%, #ffd699 75%, #ffb366 100%);
							-webkit-background-clip: text;
							-webkit-text-fill-color: transparent;
							background-clip: text;
						}

						@media (min-width: 1400px) {
							.gradient-text-yellow-orange {
								font-size: 3rem !important;
							}
						}

						@media (min-width: 1600px) {
							.gradient-text-yellow-orange {
								font-size: 3.3rem !important;
							}
						}

						@media (max-width: 992px) {
							.gradient-text-yellow-orange {
								font-size: 2.4rem !important;
							}
						}

						@media (max-width: 768px) {
							.gradient-text-yellow-orange {
								font-size: 2rem !important;
							}
						}
					</style>

					<p style="background: rgba(255, 255, 255, 0.08); padding: 1.5rem; border-left: 4px solid #ffd700; border-radius: 8px; backdrop-filter: blur(10px);">
						<strong>Hệ thống quản lý bệnh viện hiện đại</strong> — Đặt lịch khám nhanh chóng, tiện lợi, an toàn. Chăm sóc sức khỏe của bạn là ưu tiên hàng đầu của chúng tôi.

						<?php if (!$isUserLoggedIn): ?>
					<div class="hero-buttons">
						<a href="pages/auth/register.php" class="btn btn-hero-primary">
							<i class="fas fa-user-plus"></i> Đăng ký ngay
						</a>
						<a href="pages/auth/login.php" class="btn btn-hero-secondary">
							<i class="fas fa-sign-in-alt"></i> Đăng nhập
						</a>
					</div>
				<?php endif; ?>
				</div>
				<div class="col-lg-6 hero-image">
					<img src="images/ngo.png" alt="Global Hospitals" style="max-width: 100%; height: auto; filter: drop-shadow(0 20px 40px rgba(0, 0, 0, 0.3));">
	</section>

	
	<section class="features-section" id="features">
		<div class="container">
			<div class="section-title">
				<h2>Tính năng nổi bật</h2>
				<p>Hệ thống quản lý bệnh viện toàn diện với nhiều tính năng hiện đại</p>
			</div>
			<div class="row">
				<div class="col-md-4 mb-4">
					<div class="feature-card">
						<div class="feature-icon">
							<i class="fas fa-calendar-check"></i>
						</div>
						<h3>Đặt lịch khám</h3>
						<p>Đặt lịch hẹn với bác sĩ nhanh chóng, dễ dàng. Theo dõi lịch hẹn của bạn mọi lúc mọi nơi.</p>
					</div>
				</div>
				<div class="col-md-4 mb-4">
					<div class="feature-card">
						<div class="feature-icon">
							<i class="fas fa-user-md"></i>
						</div>
						<h3>Quản lý bác sĩ</h3>
						<p>Hệ thống quản lý thông tin bác sĩ, chuyên khoa và lịch làm việc hiệu quả.</p>
					</div>
				</div>
				<div class="col-md-4 mb-4">
					<div class="feature-card">
						<div class="feature-icon">
							<i class="fas fa-file-medical"></i>
						</div>
						<h3>Hồ sơ điện tử</h3>
						<p>Lưu trữ và quản lý hồ sơ bệnh án điện tử an toàn, bảo mật tuyệt đối.</p>
					</div>
				</div>
				<div class="col-md-4 mb-4">
					<div class="feature-card">
						<div class="feature-icon">
							<i class="fas fa-pills"></i>
						</div>
						<h3>Đơn thuốc</h3>
						<p>Quản lý đơn thuốc và lịch sử điều trị của bệnh nhân một cách chi tiết.</p>
					</div>
				</div>
				<div class="col-md-4 mb-4">
					<div class="feature-card">
						<div class="feature-icon">
							<i class="fas fa-chart-line"></i>
						</div>
						<h3>Thống kê báo cáo</h3>
						<p>Báo cáo và thống kê chi tiết về hoạt động của bệnh viện.</p>
					</div>
				</div>
				<div class="col-md-4 mb-4">
					<div class="feature-card">
						<div class="feature-icon">
							<i class="fas fa-shield-alt"></i>
						</div>
						<h3>Bảo mật cao</h3>
						<p>Hệ thống bảo mật thông tin bệnh nhân theo tiêu chuẩn quốc tế.</p>
					</div>
				</div>
			</div>
		</div>
	</section>

	
	<section class="specializations-section" style="padding: 5rem 0; background: linear-gradient(135deg, #fff5f5 0%, #ffe6e6 50%, #fff0f0 100%);">
		<div class="container">
			<div class="section-title" style="text-align: center; margin-bottom: 3rem;">
				<h2 style="font-size: 2.5rem; font-weight: 700; color: #8b0000; margin-bottom: 1rem;">
					<i class="fas fa-stethoscope" style="color: #ffd700; margin-right: 1rem;"></i>
					Chuyên khoa của chúng tôi
					<i class="fas fa-stethoscope" style="color: #ffd700; margin-left: 1rem;"></i>
				</h2>
				<p style="font-size: 1.1rem; color: #d2302c; font-weight: 500;">Danh sách các chuyên khoa và bác sĩ giỏi chuyên môn</p>
			</div>
			<div class="row">
				<?php
				try {
					$specs = $pdo->query("SELECT s.*, COUNT(d.id) as doctor_count 
						FROM specializations s 
						LEFT JOIN doctb d ON s.id = d.spec_id 
						WHERE s.status = 1
						GROUP BY s.id
						ORDER BY s.id ASC
						LIMIT 11")->fetchAll(PDO::FETCH_ASSOC);

					foreach ($specs as $spec): ?>
						<div class="col-md-4 col-lg-3 mb-4">
							<div class="spec-card" style="
								background: linear-gradient(135deg, #ffffff 0%, #fff5f5 100%);
								border-radius: 16px;
								padding: 2rem 1.5rem;
								text-align: center;
								box-shadow: 0 4px 12px rgba(210, 48, 44, 0.1);
								transition: all 0.3s ease;
								border: 2px solid rgba(255, 215, 0, 0.3);
								cursor: pointer;
								height: 100%;
								display: flex;
								flex-direction: column;
								justify-content: center;
								align-items: center;
							" onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 12px 24px rgba(210, 48, 44, 0.2)'; this.style.borderColor='#ffd700';"
								onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(210, 48, 44, 0.1)'; this.style.borderColor='rgba(255, 215, 0, 0.3)';">
								<div style="
									width: 80px;
									height: 80px;
									background: linear-gradient(135deg, #ff6b6b 0%, #d2302c 100%);
									border-radius: 50%;
									display: flex;
									align-items: center;
									justify-content: center;
									margin-bottom: 1.5rem;
									font-size: 2.5rem;
									color: #ffffff;
									box-shadow: 0 4px 12px rgba(210, 48, 44, 0.3);
								">
									<i class="<?php echo htmlspecialchars($spec['icon']); ?>"></i>
								</div>
								<h4 style="
									font-size: 1.25rem;
									font-weight: 600;
									color: #8b0000;
									margin-bottom: 0.5rem;
								"><?php echo htmlspecialchars($spec['name_vi']); ?></h4>
								<p style="
									font-size: 0.95rem;
									color: #d2302c;
									margin-bottom: 0;
									font-weight: 500;
								"><?php echo $spec['doctor_count']; ?> bác sĩ</p>
							</div>
						</div>
				<?php endforeach;
				} catch (Exception $e) {
					echo '<div class="col-12"><p class="text-danger">Lỗi tải chuyên khoa</p></div>';
				}
				?>

				
				<div class="col-md-4 col-lg-3 mb-4">
					<div class="spec-card" style="
						background: linear-gradient(135deg, #ffd700 0%, #d4af37 100%);
						border-radius: 16px;
						padding: 2rem 1.5rem;
						text-align: center;
						box-shadow: 0 4px 12px rgba(255, 215, 0, 0.3);
						transition: all 0.3s ease;
						border: 2px solid #b8860b;
						cursor: pointer;
						height: 100%;
						display: flex;
						flex-direction: column;
						justify-content: center;
						align-items: center;
					" onmouseover="this.style.transform='translateY(-8px) scale(1.05)'; this.style.boxShadow='0 12px 24px rgba(255, 215, 0, 0.5)';"
						onmouseout="this.style.transform='translateY(0) scale(1)'; this.style.boxShadow='0 4px 12px rgba(255, 215, 0, 0.3)';">
						<div style="
							width: 80px;
							height: 80px;
							background: linear-gradient(135deg, #d2302c 0%, #8b0000 100%);
							border-radius: 50%;
							display: flex;
							align-items: center;
							justify-content: center;
							margin-bottom: 1.5rem;
							font-size: 3rem;
							color: #ffd700;
							font-weight: 700;
							box-shadow: 0 4px 12px rgba(139, 0, 0, 0.4);
						">
							<i class="fas fa-plus"></i>
						</div>
						<h4 style="
							font-size: 1.5rem;
							font-weight: 700;
							color: #8b0000;
							margin-bottom: 0.5rem;
						">+31 Chuyên khoa</h4>
						<p style="
							font-size: 1rem;
							color: #8b0000;
							margin-bottom: 0;
							font-weight: 600;
						">Còn lại</p>
					</div>
				</div>
			</div>
		</div>
	</section>

	
	<section class="stats-section">
		<div class="container">
			<div class="row">
				<div class="col-md-3 col-6">
					<div class="stat-item">
						<div class="stat-number">500+</div>
						<div class="stat-label">Bệnh nhân</div>
					</div>
				</div>
				<div class="col-md-3 col-6">
					<div class="stat-item">
						<div class="stat-number">50+</div>
						<div class="stat-label">Bác sĩ</div>
					</div>
				</div>
				<div class="col-md-3 col-6">
					<div class="stat-item">
						<div class="stat-number">1000+</div>
						<div class="stat-label">Lịch hẹn</div>
					</div>
				</div>
				<div class="col-md-3 col-6">
					<div class="stat-item">
						<div class="stat-number">24/7</div>
						<div class="stat-label">Hỗ trợ</div>
					</div>
				</div>
			</div>
		</div>
	</section>

	
	<section class="cta-section">
		<div class="container">
			<div class="cta-content">
				<h2>Sẵn sàng bắt đầu?</h2>
				<p>Đăng ký ngay hôm nay để trải nghiệm dịch vụ chăm sóc sức khỏe tốt nhất</p>
				<a href="pages/auth/register.php" class="btn btn-hero-primary" style="padding: 1rem 3rem; font-size: 1.1rem;">
					<i class="fas fa-rocket"></i> Đăng ký miễn phí
				</a>
			</div>
		</div>
	</section>

	
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
						<li><a href="#home">Trang chủ</a></li>
						<li><a href="#features">Tính năng</a></li>
						<li><a href="pages/reviews.php">Dịch vụ</a></li>
						<li><a href="pages/contact.php">Liên hệ</a></li>
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