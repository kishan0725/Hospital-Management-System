<?php
session_start();
require_once '../../config.php';
require_once '../../includes/messages.php';

if (isset($_POST['patsub1'])) {
    $fname = trim($_POST['fname']);
    $lname = trim($_POST['lname']);
    $gender = $_POST['gender'];
    $email = trim($_POST['email']);
    $contact = trim($_POST['contact']);
    $password = $_POST['password'];
    $cpassword = $_POST['cpassword'];

    
    $_SESSION['form_data'] = [
        'fname' => $fname,
        'lname' => $lname,
        'email' => $email,
        'contact' => $contact,
        'gender' => $gender
    ];

    $errors = [];

    
    if (empty($fname)) {
        $errors['fname'] = 'Vui lòng nhập họ';
    }

    if (empty($lname)) {
        $errors['lname'] = 'Vui lòng nhập tên';
    }

    
    if (empty($email)) {
        $errors['email'] = 'Vui lòng nhập email';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Địa chỉ email không hợp lệ';
    }

    
    if (empty($contact)) {
        $errors['contact'] = 'Vui lòng nhập số điện thoại';
    } elseif (!preg_match('/^[0-9]{10}$/', $contact)) {
        $errors['contact'] = 'Số điện thoại phải có 10 chữ số';
    }

    
    if (empty($password)) {
        $errors['password'] = 'Vui lòng nhập mật khẩu';
    } elseif (strlen($password) < 3) {
        $errors['password'] = 'Mật khẩu phải có ít nhất 3 ký tự';
    }

    if (empty($cpassword)) {
        $errors['cpassword'] = 'Vui lòng xác nhận mật khẩu';
    } elseif ($password !== $cpassword) {
        $errors['cpassword'] = 'Mật khẩu xác nhận không khớp';
    }

    
    if (!empty($errors)) {
        $_SESSION['form_errors'] = $errors;
        header('Location: register.php');
        exit();
    }

    try {
        
        $stmt = $pdo->prepare("SELECT email FROM patreg WHERE email = :email");
        $stmt->execute([':email' => $email]);

        if ($stmt->rowCount() > 0) {
            $_SESSION['form_errors'] = ['email' => 'Email đã được sử dụng'];
            header('Location: register.php');
            exit();
        }

        
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        
        $stmt = $pdo->prepare("INSERT INTO patreg (fname, lname, gender, email, contact, password, cpassword) 
                              VALUES (:fname, :lname, :gender, :email, :contact, :password, :cpassword)");

        $result = $stmt->execute([
            ':fname' => $fname,
            ':lname' => $lname,
            ':gender' => $gender,
            ':email' => $email,
            ':contact' => $contact,
            ':password' => $hashed_password,
            ':cpassword' => $hashed_password
        ]);

        if ($result) {
            
            $pid = $pdo->lastInsertId();

            
            unset($_SESSION['form_data']);

            
            $_SESSION['pid'] = $pid;
            $_SESSION['username'] = $fname . " " . $lname;
            $_SESSION['fname'] = $fname;
            $_SESSION['lname'] = $lname;
            $_SESSION['gender'] = $gender;
            $_SESSION['contact'] = $contact;
            $_SESSION['email'] = $email;
            $_SESSION['user_type'] = 'patient';

            redirectWithMessage('../patient/dashboard.php', 'success', 'Đăng ký thành công! Chào mừng bạn đến với hệ thống.');
        } else {
            redirectWithMessage('register.php', 'error', 'Đăng ký thất bại. Vui lòng thử lại!');
        }
    } catch (PDOException $e) {
        error_log("Registration error: " . $e->getMessage());
        redirectWithMessage('register.php', 'error', 'Lỗi hệ thống. Vui lòng thử lại sau!');
    }
} else {
    header("Location: register.php");
    exit();
}
