<?php
session_start();
require_once('../../includes/messages.php');
$base_path = '../../';


$login_data = $_SESSION['login_data'] ?? [];
$errors = $_SESSION['login_errors'] ?? [];
unset($_SESSION['login_data'], $_SESSION['login_errors']);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Đăng nhập - Bệnh viện Global</title>
	<link rel="shortcut icon" type="image/x-icon" href="../../images/favicon.png" />

	
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

	
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

	
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

	
	<link rel="stylesheet" href="../../assets/css/custom/modern-auth.css?v=2.2">
	<link rel="stylesheet" href="../../assets/css/custom/global-improvements.css">

	<style>
		body {
			background-image:
				linear-gradient(135deg, rgba(220, 38, 38, 0.85) 0%, rgba(239, 68, 68, 0.85) 25%, rgba(248, 113, 113, 0.85) 50%, rgba(252, 165, 165, 0.85) 75%, rgba(254, 202, 202, 0.85) 100%),
				url('../../images/nendo.png') !important;
			background-size: cover, contain !important;
			background-position: center, center !important;
			background-repeat: no-repeat, no-repeat !important;
			background-attachment: fixed, fixed !important;
		}

		.form-control-modern,
		.form-control-modern:focus,
		input.form-control-modern,
		input.form-control-modern:focus {
			color: #000000 !important;
			-webkit-text-fill-color: #000000 !important;
		}

		.form-control-modern:-webkit-autofill,
		.form-control-modern:-webkit-autofill:hover,
		.form-control-modern:-webkit-autofill:focus {
			-webkit-text-fill-color: #000000 !important;
			-webkit-box-shadow: 0 0 0px 1000px #fff5f5 inset !important;
			transition: background-color 5000s ease-in-out 0s;
		}

		.form-error {
			color: #dc3545;
			font-size: 0.85rem;
			margin-top: 0.25rem;
			display: block;
			animation: fadeIn 0.3s ease-in;
		}

		.form-control-modern.is-invalid {
			border-color: #dc3545;
			background-color: #fff5f5;
		}

		@keyframes fadeIn {
			from {
				opacity: 0;
				transform: translateY(-5px);
			}

			to {
				opacity: 1;
				transform: translateY(0);
			}
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
	</style>
</head>

<body>
	
	<div class="petals-container" id="petals"></div>
	<script>
		// Tạo hoa đào rơi
		function createPetals() {
			const petalsContainer = document.getElementById('petals');
			const numberOfPetals = 30;

			for (let i = 0; i < numberOfPetals; i++) {
				const petal = document.createElement('div');
				petal.className = 'petal';
				petal.style.left = Math.random() * 100 + '%';
				petal.style.animationDelay = Math.random() * 10 + 's';
				const duration = 8 + Math.random() * 10;
				petal.style.animationDuration = duration + 's';
				petalsContainer.appendChild(petal);
			}
		}
		window.addEventListener('load', createPetals);
	</script>
	<?php require_once($base_path . 'includes/navbar.php'); ?>

	
	<div class="auth-container">
		<div class="auth-wrapper">
			
			<div class="auth-card">
				<div class="auth-icon">
					<i class="fas fa-sign-in-alt"></i>
				</div>
				<h2 class="auth-heading"><i class="fas fa-lock"></i> Đăng nhập</h2>
				<?php displayMessage(); ?>
				<form method="post" action="login-handler.php">
					<div class="form-group-modern">
						<label class="form-label-modern">
							<i class="fas fa-user"></i> Tên đăng nhập / Email
						</label>
						<input type="text" class="form-control-modern <?php echo isset($errors['username']) ? 'is-invalid' : ''; ?>"
							placeholder="Nhập tên đăng nhập hoặc email"
							name="username" value="<?php echo htmlspecialchars($login_data['username'] ?? ''); ?>" required>
						<?php if (isset($errors['username'])): ?>
							<span class="form-error"><i class="fas fa-exclamation-circle"></i> <?php echo $errors['username']; ?></span>
						<?php endif; ?>
					</div>

					<div class="form-group-modern">
						<label class="form-label-modern">
							<i class="fas fa-key"></i> Mật khẩu
						</label>
						<input type="password" class="form-control-modern <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>"
							placeholder="Nhập mật khẩu" name="password" required>
						<?php if (isset($errors['password'])): ?>
							<span class="form-error"><i class="fas fa-exclamation-circle"></i> <?php echo $errors['password']; ?></span>
						<?php endif; ?>
					</div>

					<button type="submit" class="btn-modern btn-primary-modern" name="login_submit">
						<i class="fas fa-sign-in-alt"></i> Đăng nhập
					</button>

					<div class="auth-divider">hoặc</div>

					<a href="register.php" class="auth-link">
						<i class="fas fa-user-plus"></i> Chưa có tài khoản? Đăng ký ngay
					</a>
				</form>
			</div>

			
			<div class="auth-card info-card">
				<div class="auth-icon">
					<i class="fas fa-hospital"></i>
				</div>
				<h3 class="info-title">Chào mừng đến</h3>

				<div class="info-feature">
					<div class="info-feature-icon">
						<i class="fas fa-calendar-check"></i>
					</div>
					<div>
						<h5>Đặt lịch dễ dàng</h5>
						<p>Đặt lịch khám bác sĩ trong vài giây, theo dõi mọi lúc mọi nơi</p>
					</div>
				</div>

				<div class="info-feature">
					<div class="info-feature-icon">
						<i class="fas fa-user-md"></i>
					</div>
					<div>
						<h5>Bác sĩ chuyên nghiệp</h5>
						<p>Đội ngũ bác sĩ giàu kinh nghiệm, nhiều chuyên khoa</p>
					</div>
				</div>

				<div class="info-feature">
					<div class="info-feature-icon">
						<i class="fas fa-file-medical"></i>
					</div>
					<div>
						<h5>Hồ sơ điện tử</h5>
						<p>Lưu trữ hồ sơ bệnh án an toàn, bảo mật tuyệt đối</p>
					</div>
				</div>

				<div class="info-feature">
					<div class="info-feature-icon">
						<i class="fas fa-shield-alt"></i>
					</div>
					<div>
						<h5>Bảo mật cao</h5>
						<p>Thông tin bệnh nhân được bảo vệ theo tiêu chuẩn quốc tế</p>
					</div>
				</div>
			</div>
		</div>
	</div>

	
	<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
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