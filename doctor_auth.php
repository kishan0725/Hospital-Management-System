<?php
//handles doctor login. All queries are parameterized;


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


if (isset($_POST['docsub1'])) {
    $dname = $_POST['username3'] ?? '';
    $dpass = $_POST['password3'] ?? '';

    $stmt = mysqli_prepare($con, "SELECT username, password FROM doctb WHERE username = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $dname);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($row && password_verify($dpass, $row['password'])) {
        session_regenerate_id(true);
        $_SESSION['role']  = 'doctor';
        $_SESSION['dname'] = $row['username'];
        header("Location: doctor_dashboard.php");
        exit();
    } else {
        echo "<script>alert('Invalid Username or Password. Try Again!');
              window.location.href = 'register.php';</script>";
        exit();
    }
}


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


function display_admin_panel() { /* deprecated: see receptionist_dashboard.php */ }
