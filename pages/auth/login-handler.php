<?php
session_start();
require_once '../../config.php';
require_once '../../includes/messages.php';

if (isset($_POST['login_submit'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    
    $_SESSION['login_data'] = ['username' => $username];
    $errors = [];

    
    if (empty($username)) {
        $errors['username'] = 'Vui lòng nhập tên đăng nhập hoặc email';
    }

    if (empty($password)) {
        $errors['password'] = 'Vui lòng nhập mật khẩu';
    }

    if (!empty($errors)) {
        $_SESSION['login_errors'] = $errors;
        header('Location: login.php');
        exit();
    }

    try {
        $login_success = false;

        
        $stmt = $pdo->prepare("SELECT * FROM patreg WHERE email = :username");
        $stmt->execute([':username' => $username]);

        if ($row = $stmt->fetch()) {
            
            if (password_verify($password, $row['password']) || $row['password'] === $password) {
                unset($_SESSION['login_data'], $_SESSION['login_errors']);
                $_SESSION['pid'] = $row['pid'];
                $_SESSION['username'] = $row['lname'] . " " . $row['fname'];
                $_SESSION['fname'] = $row['fname'];
                $_SESSION['lname'] = $row['lname'];
                $_SESSION['gender'] = $row['gender'];
                $_SESSION['contact'] = $row['contact'];
                $_SESSION['email'] = $row['email'];
                $_SESSION['user_type'] = 'patient';
                $_SESSION['patientSession'] = true;
                $login_success = true;
                header("Location: ../../index.php");
                exit();
            }
        }

        
        if (!$login_success) {
            $stmt = $pdo->prepare("SELECT * FROM doctb WHERE username = :username");
            $stmt->execute([':username' => $username]);

            if ($row = $stmt->fetch()) {
                if (password_verify($password, $row['password']) || $row['password'] === $password) {
                    unset($_SESSION['login_data'], $_SESSION['login_errors']);
                    $_SESSION['dname'] = $row['username'];
                    $_SESSION['doctor_id'] = $row['id'];
                    $_SESSION['demail'] = $row['email'] ?? '';
                    $_SESSION['user_type'] = 'doctor';
                    $_SESSION['doctorSession'] = true;
                    $login_success = true;
                    header("Location: ../../index.php");
                    exit();
                }
            }
        }

        
        if (!$login_success) {
            $stmt = $pdo->prepare("SELECT * FROM admintb WHERE username = :username");
            $stmt->execute([':username' => $username]);

            if ($row = $stmt->fetch()) {
                if (password_verify($password, $row['password']) || $row['password'] === $password) {
                    unset($_SESSION['login_data'], $_SESSION['login_errors']);
                    $_SESSION['username'] = $row['username'];
                    $_SESSION['user_type'] = 'admin';
                    $_SESSION['adminSession'] = true;
                    $login_success = true;
                    header("Location: ../../index.php");
                    exit();
                }
            }
        }

        
        if (!$login_success) {
            $_SESSION['login_errors'] = [
                'username' => 'Tên đăng nhập hoặc mật khẩu không đúng',
                'password' => 'Tên đăng nhập hoặc mật khẩu không đúng'
            ];
            header('Location: login.php');
            exit();
        }
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        redirectWithMessage('login.php', 'error', 'Lỗi đăng nhập. Vui lòng thử lại sau!');
    }
}
