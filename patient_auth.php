<?php


session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();

$con = mysqli_connect("localhost", "root", "", getenv("HMS_DB_NAME") ?: "myhmsdb");
if (!$con) {
    error_log("DB connection failed: " . mysqli_connect_error());
    die("A server error occurred. Please try again later.");
}


if (isset($_POST['patsub'])) {
    $email    = $_POST['email']     ?? '';
    $password = $_POST['password2'] ?? '';

    $stmt = mysqli_prepare(
        $con,
        "SELECT pid, fname, lname, gender, contact, email, password
         FROM patreg WHERE email = ? LIMIT 1"
    );
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($row && password_verify($password, $row['password'])) {
        session_regenerate_id(true);
        $_SESSION['role']     = 'patient';
        $_SESSION['pid']      = $row['pid'];
        $_SESSION['username'] = $row['fname'] . " " . $row['lname'];
        $_SESSION['fname']    = $row['fname'];
        $_SESSION['lname']    = $row['lname'];
        $_SESSION['gender']   = $row['gender'];
        $_SESSION['contact']  = $row['contact'];
        $_SESSION['email']    = $row['email'];
        header("Location: patient_dashboard.php");
        return;
    } else {
        echo "<script>alert('Invalid Username or Password. Try Again!');
              window.location.href = 'patient_login.php';</script>";
        return;
    }
}


if (isset($_POST['update_data'])) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        echo "Forbidden: admin access required.";
        return;
    }

    $contact = $_POST['contact'] ?? '';
    $status  = $_POST['status']  ?? '';

    $stmt = mysqli_prepare($con, "UPDATE appointmenttb SET payment = ? WHERE contact = ?");
    mysqli_stmt_bind_param($stmt, "ss", $status, $contact);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($ok) {
        header("Location: updated.php");
        return;
    }
}


if (isset($_POST['doc_sub'])) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        echo "Forbidden: admin access required.";
        return;
    }

    $doctor    = $_POST['doctor']    ?? '';
    $dpassword = $_POST['dpassword'] ?? '';
    $demail    = $_POST['demail']    ?? '';
    $docFees   = $_POST['docFees']   ?? '';
    $spec      = $_POST['spec']      ?? '';

    $hashed = password_hash($dpassword, PASSWORD_DEFAULT);

    $stmt = mysqli_prepare(
        $con,
        "INSERT INTO doctb (username, password, email, docFees, spec) VALUES (?, ?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, "sssss", $doctor, $hashed, $demail, $docFees, $spec);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($ok) {
        header("Location: adddoc.php");
        return;
    }
}


if (!function_exists("display_admin_panel")) {
function display_admin_panel() { /* deprecated */ }
}