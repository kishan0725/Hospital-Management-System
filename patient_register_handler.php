<?php
// patient_register_handler.php — handles new-patient registration.
// Passwords are hashed with password_hash() before storage.
// All queries use prepared statements.

session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();

$con = mysqli_connect("localhost", "root", "", "myhmsdb");
if (!$con) {
    error_log("DB connection failed: " . mysqli_connect_error());
    die("A server error occurred. Please try again later.");
}


if (isset($_POST['patsub1'])) {
    $fname     = $_POST['fname']     ?? '';
    $lname     = $_POST['lname']     ?? '';
    $gender    = $_POST['gender']    ?? '';
    $email     = $_POST['email']     ?? '';
    $contact   = $_POST['contact']   ?? '';
    $password  = $_POST['password']  ?? '';
    $cpassword = $_POST['cpassword'] ?? '';

    if ($password !== $cpassword) {
        header("Location: error_password_mismatch.php");
        exit();
    }

    // Length check (client-side version can be bypassed).
    if (strlen($password) < 6) {
        echo "<script>alert('Password must be at least 6 characters long.');
              window.location.href = 'register.php';</script>";
        exit();
    }

    $hashed  = password_hash($password,  PASSWORD_DEFAULT);
    // Store a hash in cpassword too rather than the plaintext — the
    // column exists in the legacy schema but should never hold cleartext.
    $chashed = password_hash($cpassword, PASSWORD_DEFAULT);

    $stmt = mysqli_prepare(
        $con,
        "INSERT INTO patreg (fname, lname, gender, email, contact, password, cpassword)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param(
        $stmt, "sssssss",
        $fname, $lname, $gender, $email, $contact, $hashed, $chashed
    );
    $ok = mysqli_stmt_execute($stmt);
    $newPid = mysqli_insert_id($con);
    mysqli_stmt_close($stmt);

    if ($ok) {
        session_regenerate_id(true);
        $_SESSION['role']     = 'patient';
        $_SESSION['pid']      = $newPid;
        $_SESSION['username'] = $fname . " " . $lname;
        $_SESSION['fname']    = $fname;
        $_SESSION['lname']    = $lname;
        $_SESSION['gender']   = $gender;
        $_SESSION['contact']  = $contact;
        $_SESSION['email']    = $email;
        header("Location: patient_dashboard.php");
        exit();
    } else {
        echo "<script>alert('Registration failed. Please try again.');
              window.location.href = 'register.php';</script>";
        exit();
    }
}


if (isset($_POST['update_data'])) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        die("Forbidden: admin access required.");
    }

    $contact = $_POST['contact'] ?? '';
    $status  = $_POST['status']  ?? '';

    $stmt = mysqli_prepare($con, "UPDATE appointmenttb SET payment = ? WHERE contact = ?");
    mysqli_stmt_bind_param($stmt, "ss", $status, $contact);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($ok) {
        header("Location: updated.php");
        exit();
    }
}


if (isset($_POST['doc_sub'])) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        die("Forbidden: admin access required.");
    }

    $name = $_POST['name'] ?? '';

    $stmt = mysqli_prepare($con, "INSERT INTO doctb (name) VALUES (?)");
    mysqli_stmt_bind_param($stmt, "s", $name);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($ok) {
        header("Location: adddoc.php");
        exit();
    }
}

// Deprecated shim (see receptionist_dashboard.php for the real UI).
function display_admin_panel() { /* deprecated */ }
