<?php
if (!isset($base_path)) {
    $base_path = '';
}

if (!isset($pdo)) {
    require_once __DIR__ . '/../config.php';
}
?>

<nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container">
        <a class="navbar-brand" href="<?php echo $base_path; ?>index.php">
            <i class="fas fa-hospital"></i> Bệnh viện DBD
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto align-items-center">
                <?php
                if (isset($_SESSION['patientSession']) || isset($_SESSION['doctorSession']) || isset($_SESSION['adminSession'])):
                    $portal_url = '';
                    $portal_label = '';

                    if (isset($_SESSION['patientSession'])) {
                        $portal_url = $base_path . 'pages/patient/dashboard.php';
                        $portal_label = 'Cổng Bệnh nhân';
                    } elseif (isset($_SESSION['doctorSession'])) {
                        $portal_url = $base_path . 'pages/doctor/dashboard.php';
                        $portal_label = 'Cổng Bác sĩ';
                    } elseif (isset($_SESSION['adminSession'])) {
                        $portal_url = $base_path . 'pages/admin/dashboard.php';
                        $portal_label = 'Cổng Quản trị';
                    }
                ?>
                    <li class="nav-item">
                        <a href="<?php echo $portal_url; ?>" class="btn btn-nav btn-portal">
                            <i class="fas fa-th-large"></i> <?php echo $portal_label; ?>
                        </a>
                    </li>

                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link nav-link-custom" href="<?php echo $base_path; ?>pages/reviews.php">
                        <i class="fas fa-star"></i> Đánh giá
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link nav-link-custom" href="<?php echo $base_path; ?>pages/forum/index.php">
                        <i class="fas fa-comments"></i> Diễn đàn
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link nav-link-custom" href="<?php echo $base_path; ?>pages/contact.php">
                        <i class="fas fa-phone"></i> Liên hệ
                    </a>
                </li>
                <?php
                if (isset($_SESSION['patientSession']) || isset($_SESSION['doctorSession']) || isset($_SESSION['adminSession'])): ?>
                    <li class="nav-item">
                        <a href="<?php echo $base_path; ?>pages/auth/logout.php" class="btn btn-nav btn-logout">
                            <i class="fas fa-sign-out-alt"></i> Đăng xuất
                        </a>
                    </li>
                <?php endif; ?>
                <?php
                if (!isset($_SESSION['patientSession']) && !isset($_SESSION['doctorSession']) && !isset($_SESSION['adminSession'])): ?>
                    <li class="nav-item">
                        <a href="<?php echo $base_path; ?>pages/auth/login.php" class="btn btn-nav btn-login">
                            <i class="fas fa-sign-in-alt"></i> Đăng nhập
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo $base_path; ?>pages/auth/register.php" class="btn btn-nav btn-register">
                            <i class="fas fa-user-plus"></i> Đăng ký
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<style>
    /* Navbar Styles */
    .navbar-custom {
        background: white;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        padding: 0.75rem 0;
        position: fixed;
        width: 100%;
        top: 0;
        z-index: 1000;
    }

    .navbar-custom .container {
        max-width: 100%;
        padding-left: 2rem;
        padding-right: 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .navbar-custom .navbar-brand {
        font-size: 1.5rem;
        font-weight: 700;
        background: linear-gradient(135deg, #d2302c 0%, #ff4d4d 50%, #ffd700 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .navbar-custom .navbar-brand i {
        background: linear-gradient(135deg, #d2302c 0%, #ff4d4d 50%, #ffd700 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .navbar-custom .navbar-collapse {
        flex-grow: 0;
    }

    .navbar-custom .navbar-nav {
        margin-left: auto;
    }

    .nav-link-custom {
        color: #2d3748 !important;
        font-weight: 500;
        padding: 0.5rem 1rem !important;
        margin: 0 0.25rem;
        border-radius: 8px;
        transition: all 0.3s;
    }

    .nav-link-custom:hover {
        color: #d2302c !important;
        background-color: #fff5f5;
    }

    .navbar-custom .btn-nav {
        padding: 0.5rem 1rem !important;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.875rem !important;
        transition: all 0.3s;
        margin-left: 0.5rem;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        text-decoration: none !important;
        text-transform: none !important;
        letter-spacing: normal !important;
    }

    .navbar-custom .btn-login {
        color: #d2302c !important;
        border: 2px solid #d2302c;
        background: transparent;
    }

    .navbar-custom .btn-login:hover {
        background: #d2302c;
        color: white !important;
    }

    .navbar-custom .btn-register {
        background: linear-gradient(135deg, #d2302c, #ff4d4d);
        color: white !important;
        border: 2px solid transparent;
    }

    .navbar-custom .btn-register:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(210, 48, 44, 0.3);
        color: white !important;
    }

    .navbar-custom .btn-portal {
        background: linear-gradient(135deg, #ffd700, #d4af37);
        color: #8b0000 !important;
        border: 2px solid transparent;
        font-weight: 700;
    }

    .navbar-custom .btn-portal:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(255, 215, 0, 0.4);
        color: #8b0000 !important;
    }

    .navbar-custom .btn-logout {
        background: linear-gradient(135deg, #dc2626, #b91c1c);
        color: white !important;
        border: 2px solid transparent;
        font-weight: 600;
    }

    .navbar-custom .btn-logout:hover {
        background: linear-gradient(135deg, #b91c1c, #991b1b);
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(220, 38, 38, 0.4);
        color: white !important;
    }

    /* Welcome Message Dropdown */
    .navbar-welcome {
        position: relative;
    }

    .navbar-welcome .welcome-link {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.4rem 0.8rem;
        background: rgba(255, 255, 255, 0.1);
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 50px;
        color: white !important;
        text-decoration: none;
        transition: all 0.3s;
        cursor: pointer;
    }

    .navbar-welcome .welcome-link:hover {
        background: rgba(255, 255, 255, 0.2);
        border-color: rgba(255, 255, 255, 0.5);
        transform: translateY(-2px);
        color: white !important;
    }

    .navbar-welcome .welcome-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: linear-gradient(135deg, #f59e0b, #d97706);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .navbar-welcome .welcome-info {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        color: white !important;
    }

    .navbar-welcome .welcome-greeting {
        font-size: 0.7rem;
        opacity: 0.9;
        color: white !important;
    }

    .navbar-welcome .welcome-name {
        font-weight: 600;
        font-size: 0.85rem;
        color: white !important;
    }

    .navbar-welcome .fa-chevron-down {
        font-size: 0.75rem;
        opacity: 0.7;
        color: white !important;
    }

    .navbar-welcome .dropdown-toggle::after {
        display: none;
    }

    .navbar-welcome .dropdown-menu {
        min-width: 220px;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        border: none;
        padding: 0.5rem 0;
        margin-top: 0.5rem;
    }

    .navbar-welcome .dropdown-item {
        padding: 0.75rem 1.5rem;
        font-size: 0.95rem;
        transition: all 0.2s;
        display: flex;
        align-items: center;
    }

    .navbar-welcome .dropdown-item i {
        width: 20px;
        font-size: 0.9rem;
    }

    .navbar-welcome .dropdown-item:hover {
        background: #f0f9ff;
        color: #0891b2;
        padding-left: 1.75rem;
    }

    .navbar-welcome .dropdown-item.text-danger:hover {
        background: #fef2f2;
        color: #dc2626;
    }

    .navbar-welcome .dropdown-divider {
        margin: 0.5rem 0;
    }

    /* Navbar toggler for mobile */
    .navbar-toggler {
        border: none;
        padding: 0.5rem;
    }

    .navbar-toggler:focus {
        outline: none;
        box-shadow: none;
    }

    .navbar-toggler-icon {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(8, 145, 178, 1)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
    }

    @media (max-width: 991px) {
        .navbar-custom .navbar-nav {
            padding: 1rem 0;
        }

        .navbar-custom .nav-item {
            margin: 0.5rem 0;
        }

        .btn-nav {
            display: block;
            text-align: center;
            margin: 0.5rem 0;
        }
    }
</style>