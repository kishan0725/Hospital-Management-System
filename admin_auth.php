<?php
// admin_auth.php — handles receptionist/admin login and admin-only

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


if (isset($_POST['adsub'])) {
    $username = $_POST['username1'] ?? '';
    $password = $_POST['password2'] ?? '';

    $stmt = mysqli_prepare($con, "SELECT username, password FROM admintb WHERE username = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($row && password_verify($password, $row['password'])) {
        // Rotate session ID on privilege change to stop session fixation.
        session_regenerate_id(true);
        $_SESSION['role']           = 'admin';
        $_SESSION['admin_username'] = $row['username'];
        header("Location: receptionist_dashboard.php");
        exit();
    } else {
        echo "<script>alert('Invalid Username or Password. Try Again!');
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

// Kept for backward compat with the original file — pages that include
// this file and call display_docs() will still get a dropdown.
function display_docs()
{
    global $con;
    $result = mysqli_query($con, "SELECT name FROM doctb");
    if ($result) {
        while ($row = mysqli_fetch_array($result)) {
            $name = htmlspecialchars($row['name'] ?? '', ENT_QUOTES, 'UTF-8');
            echo '<option value="' . $name . '">' . $name . '</option>';
        }
    }
}
