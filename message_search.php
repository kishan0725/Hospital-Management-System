<?php
// message_search.php — admin-only search for contact-form submissions
// by the contact number the user supplied.

session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    header("Location: register.php");
    exit();
}

$con = mysqli_connect("localhost", "root", "", "myhmsdb");
if (!$con) {
    error_log("DB connection failed: " . mysqli_connect_error());
    die("A server error occurred. Please try again later.");
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>User Messages</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
</head>
<body>
<?php
if (isset($_POST['mes_search_submit'])) {
    $contact = $_POST['mes_contact'] ?? '';

    $stmt = mysqli_prepare(
        $con,
        "SELECT name, email, contact, message FROM contact WHERE contact = ? LIMIT 1"
    );
    mysqli_stmt_bind_param($stmt, "s", $contact);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$row) {
        echo "<script>alert('No entries found! Please enter valid details');
              window.location.href = 'receptionist_dashboard.php#list-doc';</script>";
    } else {
        $name       = htmlspecialchars($row['name']    ?? '', ENT_QUOTES, 'UTF-8');
        $email      = htmlspecialchars($row['email']   ?? '', ENT_QUOTES, 'UTF-8');
        $contactOut = htmlspecialchars($row['contact'] ?? '', ENT_QUOTES, 'UTF-8');
        $message    = htmlspecialchars($row['message'] ?? '', ENT_QUOTES, 'UTF-8');

        echo "<div class='container-fluid' style='margin-top:50px;'>
        <div class='card'>
        <div class='card-body' style='background-color:#342ac1;color:#ffffff;'>
      <table class='table table-hover'>
        <thead>
          <tr>
            <th scope='col'>User Name</th>
            <th scope='col'>Email</th>
            <th scope='col'>Contact</th>
            <th scope='col'>Message</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>$name</td>
            <td>$email</td>
            <td>$contactOut</td>
            <td>$message</td>
          </tr>
        </tbody>
      </table>
      <center><a href='receptionist_dashboard.php' class='btn btn-light'>Back to your Dashboard</a></center>
      </div></div></div>";
    }
}
?>
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
</body>
</html>
