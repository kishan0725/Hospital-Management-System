<?php
//  handles receptionist/admin login and admin-only

session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();
<<<<<<< HEAD

$con = mysqli_connect("localhost", "root", "", getenv("HMS_DB_NAME") ?: "myhmsdb");
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
        return;
    } else {
        echo "<script>alert('Invalid Username or Password. Try Again!');
              window.location.href = 'register.php';</script>";
        return;
    }
}


if (isset($_POST["update_data"])) {
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

    $name = $_POST['name'] ?? '';

    $stmt = mysqli_prepare($con, "INSERT INTO doctb (username) VALUES (?)");
    mysqli_stmt_bind_param($stmt, "s", $name);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($ok) {
        header("Location: adddoc.php");
        return;
    }
}

if (!function_exists("display_docs")) {
=======
$con=mysqli_connect("localhost","root","","myhmsdb");
if(isset($_POST['adsub'])){
	$username=$_POST['username1'];
	$password=$_POST['password2'];
	$query="select * from admintb where username='$username' and password='$password';";
	$result=mysqli_query($con,$query);
	if(mysqli_num_rows($result)==1)
	{
		$_SESSION['username']=$username;
		header("Location:receptionist_dashboard.php");
	}
	else
		// header("Location:error_admin_login.php");
		echo("<script>alert('Invalid Username or Password. Try Again!');
          window.location.href = 'register.php';</script>");
}
if(isset($_POST['update_data']))
{
	$contact=$_POST['contact'];
	$status=$_POST['status'];
	$query="update appointmenttb set payment='$status' where contact='$contact';";
	$result=mysqli_query($con,$query);
	if($result)
		header("Location:updated.php");
}




>>>>>>> master
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
}
<<<<<<< HEAD
=======

if(isset($_POST['doc_sub']))
{
	$name=$_POST['name'];
	$query="insert into doctb(name)values('$name')";
	$result=mysqli_query($con,$query);
	if($result)
		header("Location:adddoc.php");
}
>>>>>>> master
