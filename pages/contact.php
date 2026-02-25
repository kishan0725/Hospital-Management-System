<?php



session_start();
require_once '../includes/messages.php';
require_once '../config.php';


if (isset($_POST['btnSubmit'])) {
	try {
		$name = htmlspecialchars(trim($_POST['txtName']));
		$email = htmlspecialchars(trim($_POST['txtEmail']));
		$contact = htmlspecialchars(trim($_POST['txtPhone']));
		$message = htmlspecialchars(trim($_POST['txtMsg']));

		$stmt = $pdo->prepare("INSERT INTO contact(name,email,contact,message) VALUES(:name,:email,:contact,:message)");
		$result = $stmt->execute([
			':name' => $name,
			':email' => $email,
			':contact' => $contact,
			':message' => $message
		]);

		if ($result) {
			redirectWithMessage('contact.php', 'success', 'Tin nhắn đã được gửi thành công! Chúng tôi sẽ liên hệ lại sớm nhất.');
		}
	} catch (PDOException $e) {
		error_log("Contact form error: " . $e->getMessage());
		redirectWithMessage('contact.php', 'error', 'Lỗi khi gửi tin nhắn. Vui lòng thử lại!');
	}
}

$base_path = '../';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="shortcut icon" type="image/x-icon" href="../images/favicon.png" />
	<title>Liên hệ - Bệnh viện Global</title>

	
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" />
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />

	<style>
		:root {
			--primary-color: #d2302c;
			--primary-dark: #8b0000;
			--primary-light: #ff4d4d;
		}

		body {
			font-family: 'Inter', sans-serif;
			background-image:
				linear-gradient(135deg, rgba(139, 0, 0, 0.85) 0%, rgba(210, 48, 44, 0.85) 50%, rgba(255, 77, 77, 0.85) 100%),
				url('../images/ngua.png');
			background-size: cover, contain;
			background-position: center, center;
			background-repeat: no-repeat, no-repeat;
			background-attachment: fixed, fixed;
			position: relative;
			overflow-x: hidden;
		}

		body::before {
			content: '';
			position: absolute;
			top: 0;
			left: 0;
			right: 0;
			bottom: 0;
			background: radial-gradient(circle at 20% 80%, rgba(20, 184, 166, 0.2) 0%, transparent 50%),
				radial-gradient(circle at 80% 20%, rgba(6, 182, 212, 0.2) 0%, transparent 50%);
			pointer-events: none;
		}

		.contact-container {
			max-width: 1200px;
			margin: 2rem auto;
			padding: 0 1rem;
			position: relative;
			z-index: 2;
		}

		.contact-header {
			margin-top: 100px;
			text-align: center;
			margin-bottom: 3rem;
			color: white;
		}

		.contact-header h1 {
			font-size: 2.5rem;
			font-weight: 700;
			margin-bottom: 1rem;
		}

		.contact-header p {
			font-size: 1.125rem;
			opacity: 0.9;
		}

		.contact-grid {
			display: grid;
			grid-template-columns: 1fr 1fr;
			gap: 2rem;
			margin-bottom: 2rem;
		}

		.contact-info,
		.contact-form {
			background: white;
			padding: 2.5rem;
			border-radius: 20px;
			box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
		}

		.contact-info h2,
		.contact-form h2 {
			color: var(--primary-color);
			font-size: 1.75rem;
			font-weight: 700;
			margin-bottom: 1.5rem;
		}

		.info-item {
			display: flex;
			align-items: flex-start;
			gap: 1rem;
			margin-bottom: 1.5rem;
		}

		.info-icon {
			width: 50px;
			height: 50px;
			background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
			color: white;
			border-radius: 12px;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 1.25rem;
			flex-shrink: 0;
		}

		.info-content h3 {
			color: #374151;
			font-size: 1rem;
			font-weight: 600;
			margin-bottom: 0.5rem;
		}

		.info-content p {
			color: #6b7280;
			margin: 0;
			line-height: 1.6;
			font-size: 0.9rem;
		}

		.form-group {
			margin-bottom: 1.25rem;
		}

		.form-label {
			display: block;
			font-weight: 600;
			color: #374151;
			margin-bottom: 0.5rem;
			font-size: 0.9rem;
		}

		.form-control {
			width: 100%;
			padding: 0.875rem 1rem;
			border: 2px solid #e5e7eb;
			border-radius: 10px;
			font-size: 0.9rem;
			transition: all 0.2s ease;
			font-family: 'Inter', sans-serif;
			color: #000000;
		}

		.form-control:focus {
			outline: none;
			border-color: var(--primary-color);
			box-shadow: 0 0 0 3px rgba(8, 145, 178, 0.12);
		}

		textarea.form-control {
			min-height: 120px;
			resize: vertical;
		}

		.btn-submit {
			width: 100%;
			padding: 1rem;
			background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
			color: white;
			border: none;
			border-radius: 10px;
			font-size: 1rem;
			font-weight: 600;
			cursor: pointer;
			transition: all 0.3s ease;
		}

		.btn-submit:hover {
			transform: translateY(-2px);
			box-shadow: 0 10px 30px rgba(8, 145, 178, 0.4);
		}

		.map-container {
			background: white;
			padding: 2rem;
			border-radius: 20px;
			box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
			margin-bottom: 2rem;
		}

		.map-container h2 {
			color: var(--primary-color);
			font-size: 1.75rem;
			font-weight: 700;
			margin-bottom: 1.5rem;
			text-align: center;
		}

		.map-container iframe {
			width: 100%;
			height: 350px;
			border: none;
			border-radius: 10px;
		}

		@media (max-width: 768px) {
			.contact-grid {
				grid-template-columns: 1fr;
			}

			.contact-header h1 {
				font-size: 2rem;
			}

			.contact-info,
			.contact-form {
				padding: 1.5rem;
			}
		}
	</style>
</head>

<body>
	<?php include($base_path . 'includes/navbar.php'); ?>

	
	<div class="contact-container">
		<div class="contact-header">
			<h1><i class="fas fa-phone-alt"></i> Liên hệ với chúng tôi</h1>
			<p>Chúng tôi luôn sẵn sàng lắng nghe và hỗ trợ bạn. Hãy gửi tin nhắn cho chúng tôi.</p>
		</div>

		<?php displayMessage(); ?>

		<div class="contact-grid">
			<div class="contact-info">
				<h2><i class="fas fa-info-circle"></i> Thông tin liên hệ</h2>

				<div class="info-item">
					<div class="info-icon">
						<i class="fas fa-map-marker-alt"></i>
					</div>
					<div class="info-content">
						<h3>Địa chỉ</h3>
						<b>
							<p>136 Xuân thuỷ<br>Trường Đại học Sư Phạm Hà Nội<br>Việt Nam</p>
						</b>
					</div>
				</div>

				<div class="info-item">
					<div class="info-icon">
						<i class="fas fa-phone"></i>
					</div>
					<div class="info-content">
						<h3>Điện thoại</h3>
						<b>
							<p>Cấp cứu: 1900 1234<br>Lễ tân: 1900 5678<br>Fax: 1900 9012</p>
						</b>
					</div>
				</div>

				<div class="info-item">
					<div class="info-icon">
						<i class="fas fa-envelope"></i>
					</div>
					<div class="info-content">
						<h3>Email</h3>
						<b>
							<p>stu735105020@hnue.edu.vn<br>stu735105017@hnue.edu.vn<br>stu735105024@hnue.edu.vn</p>
						</b>
					</div>
				</div>

				<div class="info-item">
					<div class="info-icon">
						<i class="fas fa-clock"></i>
					</div>
					<div class="info-content">
						<h3>Giờ làm việc</h3>
						<b>
							<p>Thứ 2 - Thứ 6: 7:00 - 20:00<br>Thứ 7 - Chủ nhật: 8:00 - 17:00<br>Cấp cứu: 24/7</p>
						</b>
					</div>
				</div>
			</div>

			
			<div class="contact-form">
				<h2><i class="fas fa-paper-plane"></i> Gửi tin nhắn</h2>

				<form method="post" action="">
					<div class="form-group">
						<label class="form-label">Họ và tên *</label>
						<input type="text" name="txtName" class="form-control" placeholder="Nhập họ và tên của bạn" required>
					</div>

					<div class="form-group">
						<label class="form-label">Email *</label>
						<input type="email" name="txtEmail" class="form-control" placeholder="Nhập địa chỉ email" required>
					</div>

					<div class="form-group">
						<label class="form-label">Số điện thoại *</label>
						<input type="tel" name="txtPhone" class="form-control" placeholder="Nhập số điện thoại" required>
					</div>

					<div class="form-group">
						<label class="form-label">Nội dung tin nhắn *</label>
						<textarea name="txtMsg" class="form-control" placeholder="Nhập nội dung tin nhắn của bạn..." required></textarea>
					</div>

					<button type="submit" name="btnSubmit" class="btn-submit">
						<i class="fas fa-paper-plane"></i> Gửi tin nhắn
					</button>
				</form>
			</div>
		</div>

		
		<div class="map-container">
			<h2><i class="fas fa-map"></i> Vị trí trên bản đồ</h2>
			<iframe
				src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3723.8374169573435!2d105.80580547620408!3d21.028547880626528!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x313455b74444fbff%3A0xe1e0e0e0e0e0e0e0!2sTr%C6%B0%E1%BB%9Dng%20%C4%90%E1%BA%A1i%20h%E1%BB%8Dc%20S%C6%B0%20Ph%E1%BA%A1m%20H%C3%A0%20N%E1%BB%99i!5e0!3m2!1svi!2s!4v1706878800000"
				allowfullscreen=""
				loading="lazy">
			</iframe>
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
						<li><a href="../index.php">Trang chủ</a></li>
						<li><a href="reviews.php">Đánh giá</a></li>
						<li><a href="contact.php">Liên hệ</a></li>
						<li><a href="forum/index.php">Diễn đàn</a></li>
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