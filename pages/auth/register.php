<?php
session_start();
require_once('../../includes/messages.php');
$base_path = '../../';


$form_data = $_SESSION['form_data'] ?? [];
$errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_data'], $_SESSION['form_errors']);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Đăng ký - Bệnh viện Global</title>
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

		.form-error {
			color: #dc3545;
			font-size: 0.85rem;
			margin-top: 0.25rem;
			display: block;
			animation: fadeIn 0.3s ease-in;
		}

		.form-input.is-invalid {
			border-color: #dc3545;
			background-color: #fff5f5;
		}

		.form-input.is-valid {
			border-color: #28a745;
			background-color: #f0fff4;
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

		.main-container {
			min-height: 100vh;
			padding: 100px 20px 40px;
			position: relative;
			overflow: hidden;
		}

		.main-container::before {
			content: '';
			position: absolute;
			top: -50%;
			right: -50%;
			width: 100%;
			height: 100%;
			background: radial-gradient(circle, rgba(20, 184, 166, 0.2) 0%, transparent 70%);
			animation: float 15s ease-in-out infinite;
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
			opacity: 0.6;
		}

		@keyframes float {

			0%,
			100% {
				transform: translate(0, 0) rotate(0deg);
			}

			50% {
				transform: translate(50px, 50px) rotate(180deg);
			}
		}

		.register-card {
			max-width: 700px;
			margin: 0 auto;
			background: white;
			border-radius: 20px;
			padding: 2rem 2.5rem;
			box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
			position: relative;
			z-index: 1;
		}

		.register-header {
			text-align: center;
			margin-bottom: 1.5rem;
		}

		.register-icon {
			width: 60px;
			height: 60px;
			background: linear-gradient(135deg, #d2302c, #ff4d4d);
			border-radius: 16px;
			display: flex;
			align-items: center;
			justify-content: center;
			margin: 0 auto 1rem;
			font-size: 2rem;
			color: white;
			box-shadow: 0 10px 30px rgba(8, 145, 178, 0.4);
		}

		.register-title {
			font-size: 1.6rem;
			font-weight: 700;
			color: #1a202c;
			margin-bottom: 0.25rem;
		}

		.register-subtitle {
			color: #718096;
			font-size: 0.9rem;
		}

		.form-row-custom {
			display: grid;
			grid-template-columns: 1fr 1fr;
			gap: 1rem;
			margin-bottom: 1rem;
		}

		.form-group-custom {
			position: relative;
		}

		.form-group-custom.full-width {
			grid-column: 1 / -1;
		}

		.form-label {
			display: block;
			font-weight: 600;
			color: #2d3748;
			margin-bottom: 0.35rem;
			font-size: 0.85rem;
		}

		.required {
			color: #e53e3e;
		}

		.form-input {
			width: 100%;
			padding: 0.7rem 0.875rem;
			border: 2px solid #e2e8f0;
			border-radius: 10px;
			font-size: 0.9rem;
			transition: all 0.2s ease;
			color: #000000;
			background: white;
		}

		.form-input:focus {
			outline: none;
			border-color: #d2302c;
			box-shadow: 0 0 0 3px rgba(210, 48, 44, 0.1);
		}

		.gender-group {
			display: flex;
			gap: 0.5rem;
		}

		.gender-option {
			flex: 0 0 auto;
			position: relative;
			cursor: pointer;
		}

		.gender-option input[type="radio"] {
			position: absolute;
			opacity: 0;
		}

		.gender-option span {
			display: block;
			padding: 0.5rem 1rem;
			border: 2px solid #e2e8f0;
			border-radius: 10px;
			text-align: center;
			transition: all 0.2s ease;
			font-weight: 500;
			color: #4a5568;
			font-size: 0.9rem;
		}

		.gender-option input:checked+span {
			border-color: #d2302c;
			background: linear-gradient(135deg, #d2302c, #ff4d4d);
			color: white;
			box-shadow: 0 4px 12px rgba(210, 48, 44, 0.3);
		}

		.btn-submit-register {
			width: 100%;
			padding: 0.85rem;
			background: linear-gradient(135deg, #d2302c, #ff4d4d);
			color: white;
			border: none;
			border-radius: 10px;
			font-size: 0.95rem;
			font-weight: 600;
			cursor: pointer;
			transition: all 0.3s ease;
			margin-top: 0.75rem;
		}

		.btn-submit-register:hover {
			transform: translateY(-2px);
			box-shadow: 0 10px 30px rgba(8, 145, 178, 0.4);
		}

		.login-link {
			text-align: center;
			margin-top: 1rem;
			font-size: 0.9rem;
		}

		.login-link a {
			color: #d2302c;
			font-weight: 600;
			text-decoration: none;
			margin-left: 0.5rem;
		}

		.login-link a:hover {
			text-decoration: underline;
		}

		.password-strength {
			margin-top: 0.5rem;
			height: 4px;
			border-radius: 2px;
			background: #e2e8f0;
			overflow: hidden;
		}

		.password-strength-bar {
			height: 100%;
			transition: all 0.3s ease;
			width: 0;
		}

		.password-match {
			margin-top: 0.5rem;
			font-size: 0.85rem;
			font-weight: 500;
		}

		.password-match.success {
			color: #28a745;
		}

		.password-match.error {
			color: #dc3545;
		}

		@media (max-width: 768px) {
			.form-row-custom {
				grid-template-columns: 1fr;
			}

			.register-card {
				padding: 2rem 1.5rem;
			}
		}
	</style>
</head>

<body>
	
	<div class="petals-container" id="petals"></div>
	<script>
		function createPetals() {
			const petalsContainer = document.getElementById('petals');
			const numberOfPetals = 30;
			for (let i = 0; i < numberOfPetals; i++) {
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
	<?php include($base_path . 'includes/navbar.php'); ?>

	
	<div class="main-container">
		<div class="register-card">
			
			<div class="register-header">
				<div class="register-icon">
					<i class="fas fa-user-plus"></i>
				</div>
				<h1 class="register-title">Tạo tài khoản</h1>
				<p class="register-subtitle">Đăng ký để đặt lịch khám bệnh online</p>
			</div>

			
			<?php displayMessage(); ?>

			
			<form method="post" action="register-handler.php" id="registerForm" novalidate>
				
				<div class="form-row-custom">
					<div class="form-group-custom">
						<label class="form-label">Họ <span class="required">*</span></label>
						<input type="text" class="form-input <?php echo isset($errors['fname']) ? 'is-invalid' : ''; ?>"
							name="fname" placeholder="Nguyễn" value="<?php echo htmlspecialchars($form_data['fname'] ?? ''); ?>"
							onkeydown="return alphaOnly(event);" required>
						<?php if (isset($errors['fname'])): ?>
							<span class="form-error"><i class="fas fa-exclamation-circle"></i> <?php echo $errors['fname']; ?></span>
						<?php endif; ?>
					</div>
					<div class="form-group-custom">
						<label class="form-label">Tên <span class="required">*</span></label>
						<input type="text" class="form-input <?php echo isset($errors['lname']) ? 'is-invalid' : ''; ?>"
							name="lname" placeholder="Văn A" value="<?php echo htmlspecialchars($form_data['lname'] ?? ''); ?>"
							onkeydown="return alphaOnly(event);" required>
						<?php if (isset($errors['lname'])): ?>
							<span class="form-error"><i class="fas fa-exclamation-circle"></i> <?php echo $errors['lname']; ?></span>
						<?php endif; ?>
					</div>
				</div>

				
				<div class="form-row-custom">
					<div class="form-group-custom">
						<label class="form-label">Email <span class="required">*</span></label>
						<input type="email" class="form-input <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>"
							name="email" placeholder="example@email.com" value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>" required>
						<?php if (isset($errors['email'])): ?>
							<span class="form-error"><i class="fas fa-exclamation-circle"></i> <?php echo $errors['email']; ?></span>
						<?php endif; ?>
					</div>
					<div class="form-group-custom">
						<label class="form-label">Số điện thoại <span class="required">*</span></label>
						<input type="tel" class="form-input <?php echo isset($errors['contact']) ? 'is-invalid' : ''; ?>"
							name="contact" placeholder="0912345678" value="<?php echo htmlspecialchars($form_data['contact'] ?? ''); ?>"
							minlength="10" maxlength="10" required>
						<?php if (isset($errors['contact'])): ?>
							<span class="form-error"><i class="fas fa-exclamation-circle"></i> <?php echo $errors['contact']; ?></span>
						<?php endif; ?>
					</div>
				</div>

				
				<div class="form-group-custom">
					<label class="form-label">Giới tính <span class="required">*</span></label>
					<div class="gender-group">
						<label class="gender-option">
							<input type="radio" name="gender" value="Male" checked>
							<span>Nam</span>
						</label>
						<label class="gender-option">
							<input type="radio" name="gender" value="Female">
							<span>Nữ</span>
						</label>
					</div>
				</div>

				
				<div class="form-row-custom">
					<div class="form-group-custom">
						<label class="form-label">Mật khẩu <span class="required">*</span></label>
						<input type="password" class="form-input <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>"
							id="password" name="password" placeholder="Tối thiểu 6 ký tự"
							onkeyup="checkPasswordStrength(); checkPassword();" required>
						<div class="password-strength" id="passwordStrength">
							<div class="password-strength-bar" id="passwordStrengthBar"></div>
						</div>
						<span class="form-error" id="passwordError" style="display: none;"></span>
						<?php if (isset($errors['password'])): ?>
							<span class="form-error"><i class="fas fa-exclamation-circle"></i> <?php echo $errors['password']; ?></span>
						<?php endif; ?>
					</div>
					<div class="form-group-custom">
						<label class="form-label">Xác nhận mật khẩu <span class="required">*</span></label>
						<input type="password" class="form-input <?php echo isset($errors['cpassword']) ? 'is-invalid' : ''; ?>"
							id="cpassword" name="cpassword" placeholder="Nhập lại mật khẩu"
							onkeyup="checkPassword();" required>
						<div id="passwordMessage" class="password-match"></div>
						<?php if (isset($errors['cpassword'])): ?>
							<span class="form-error"><i class="fas fa-exclamation-circle"></i> <?php echo $errors['cpassword']; ?></span>
						<?php endif; ?>
					</div>
				</div>

				
				<button type="submit" class="btn-submit-register" name="patsub1" onclick="return validateForm();">
					<i class="fas fa-user-plus"></i> Đăng ký ngay
				</button>

				
				<div class="login-link">
					<span style="color: #718096;">Đã có tài khoản?</span>
					<a href="login.php"><i class="fas fa-sign-in-alt"></i> Đăng nhập</a>
				</div>
			</form>
		</div>
	</div>

	
	<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

	<script>
		// Only allow letters and spaces
		function alphaOnly(event) {
			const key = event.keyCode;
			return ((key >= 65 && key <= 90) || key == 8 || key == 32);
		}

		// Check password strength
		function checkPasswordStrength() {
			const password = document.getElementById('password').value;
			const strengthBar = document.getElementById('passwordStrengthBar');
			const errorMsg = document.getElementById('passwordError');

			if (password.length === 0) {
				strengthBar.style.width = '0';
				errorMsg.style.display = 'none';
				return;
			}

			let strength = 0;
			if (password.length >= 6) strength += 25;
			if (password.length >= 8) strength += 25;
			if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength += 25;
			if (/[0-9]/.test(password)) strength += 25;

			strengthBar.style.width = strength + '%';

			if (strength < 25) {
				strengthBar.style.background = '#dc3545';
				errorMsg.innerHTML = '<i class="fas fa-exclamation-circle"></i> Mật khẩu quá yếu';
				errorMsg.style.display = 'block';
				errorMsg.style.color = '#dc3545';
			} else if (strength < 50) {
				strengthBar.style.background = '#ffc107';
				errorMsg.innerHTML = '<i class="fas fa-info-circle"></i> Mật khẩu yếu';
				errorMsg.style.display = 'block';
				errorMsg.style.color = '#ffc107';
			} else if (strength < 75) {
				strengthBar.style.background = '#17a2b8';
				errorMsg.innerHTML = '<i class="fas fa-check-circle"></i> Mật khẩu trung bình';
				errorMsg.style.display = 'block';
				errorMsg.style.color = '#17a2b8';
			} else {
				strengthBar.style.background = '#28a745';
				errorMsg.innerHTML = '<i class="fas fa-check-circle"></i> Mật khẩu mạnh';
				errorMsg.style.display = 'block';
				errorMsg.style.color = '#28a745';
			}
		}

		// Check password match
		function checkPassword() {
			const password = document.getElementById('password').value;
			const cpassword = document.getElementById('cpassword').value;
			const message = document.getElementById('passwordMessage');
			const cpasswordInput = document.getElementById('cpassword');

			if (cpassword === '') {
				message.innerHTML = '';
				message.className = 'password-match';
				cpasswordInput.classList.remove('is-valid', 'is-invalid');
			} else if (password === cpassword) {
				message.innerHTML = '<i class="fas fa-check-circle"></i> Mật khẩu khớp';
				message.className = 'password-match success';
				cpasswordInput.classList.add('is-valid');
				cpasswordInput.classList.remove('is-invalid');
			} else {
				message.innerHTML = '<i class="fas fa-times-circle"></i> Mật khẩu không khớp';
				message.className = 'password-match error';
				cpasswordInput.classList.add('is-invalid');
				cpasswordInput.classList.remove('is-valid');
			}
		}

		// Real-time validation
		document.addEventListener('DOMContentLoaded', function() {
			const form = document.getElementById('registerForm');
			const inputs = form.querySelectorAll('input[required]');

			inputs.forEach(input => {
				input.addEventListener('blur', function() {
					if (this.value.trim() === '') {
						this.classList.add('is-invalid');
						this.classList.remove('is-valid');
					} else {
						this.classList.add('is-valid');
						this.classList.remove('is-invalid');
					}
				});
			});

			// Email validation
			const emailInput = form.querySelector('input[type="email"]');
			if (emailInput) {
				emailInput.addEventListener('blur', function() {
					const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
					if (!emailRegex.test(this.value)) {
						this.classList.add('is-invalid');
						this.classList.remove('is-valid');
					} else {
						this.classList.add('is-valid');
						this.classList.remove('is-invalid');
					}
				});
			}

			// Phone validation
			const phoneInput = form.querySelector('input[type="tel"]');
			if (phoneInput) {
				phoneInput.addEventListener('input', function() {
					this.value = this.value.replace(/[^0-9]/g, '');
				});
				phoneInput.addEventListener('blur', function() {
					if (this.value.length !== 10) {
						this.classList.add('is-invalid');
						this.classList.remove('is-valid');
					} else {
						this.classList.add('is-valid');
						this.classList.remove('is-invalid');
					}
				});
			}
		});

		// Form validation
		function validateForm() {
			const password = document.getElementById('password').value;
			const cpassword = document.getElementById('cpassword').value;
			const form = document.getElementById('registerForm');
			let isValid = true;

			// Check all required fields
			const inputs = form.querySelectorAll('input[required]');
			inputs.forEach(input => {
				if (input.value.trim() === '') {
					input.classList.add('is-invalid');
					isValid = false;
				}
			});

			if (!isValid) {
				return false;
			}

			if (password.length < 6) {
				document.getElementById('password').classList.add('is-invalid');
				return false;
			}

			if (password !== cpassword) {
				document.getElementById('cpassword').classList.add('is-invalid');
				return false;
			}

			return true;
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