<?php
// search.php — doctor-only search for their OWN appointments by
// patient contact number. Scopes the result set to the logged-in
// doctor so one doctor can't enumerate another doctor's patients.

session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();

// Doctor-only endpoint.
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'doctor' || empty($_SESSION['dname'])) {
    http_response_code(403);
    header("Location: register.php");
    exit();
}

$con = mysqli_connect("localhost", "root", "", "myhmsdb");
if (!$con) {
    error_log("DB connection failed: " . mysqli_connect_error());
    die("A server error occurred. Please try again later.");
}

if (isset($_POST['search_submit'])) {
    $contact = $_POST['contact'] ?? '';
    $docname = $_SESSION['dname'];

    $stmt = mysqli_prepare(
        $con,
        "SELECT fname, lname, email, contact, appdate, apptime
         FROM appointmenttb WHERE contact = ? AND doctor = ?"
    );
    mysqli_stmt_bind_param($stmt, "ss", $contact, $docname);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    echo '<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
  </head>
  <body style="background-color:#342ac1;color:white;text-align:center;padding-top:50px;">
  <div class="container" style="text-align:left;">
  <center><h3>Search Results</h3></center><br>
  <table class="table table-hover">
  <thead>
    <tr>
      <th>First Name</th>
      <th>Last Name</th>
      <th>Email</th>
      <th>Contact</th>
      <th>Appointment Date</th>
      <th>Appointment Time</th>
    </tr>
  </thead>
  <tbody>';

    while ($row = mysqli_fetch_assoc($result)) {
        $fname      = htmlspecialchars($row['fname']   ?? '', ENT_QUOTES, 'UTF-8');
        $lname      = htmlspecialchars($row['lname']   ?? '', ENT_QUOTES, 'UTF-8');
        $email      = htmlspecialchars($row['email']   ?? '', ENT_QUOTES, 'UTF-8');
        $contactOut = htmlspecialchars($row['contact'] ?? '', ENT_QUOTES, 'UTF-8');
        $appdate    = htmlspecialchars($row['appdate'] ?? '', ENT_QUOTES, 'UTF-8');
        $apptime    = htmlspecialchars($row['apptime'] ?? '', ENT_QUOTES, 'UTF-8');

        echo "<tr>
            <td>{$fname}</td>
            <td>{$lname}</td>
            <td>{$email}</td>
            <td>{$contactOut}</td>
            <td>{$appdate}</td>
            <td>{$apptime}</td>
          </tr>";
    }
    mysqli_stmt_close($stmt);

    echo '</tbody></table></div>
<div><a href="doctor_dashboard.php" class="btn btn-light">Go Back</a></div>
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
  </body>
</html>';
}
