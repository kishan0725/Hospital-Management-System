<?php


session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();

// Admin-only endpoint.
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    header("Location: register.php");
    return;
}

$con = mysqli_connect("localhost", "root", "", getenv("HMS_DB_NAME") ?: "myhmsdb");
if (!$con) {
    error_log("DB connection failed: " . mysqli_connect_error());
    die("A server error occurred. Please try again later.");
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Patient Details</title>
  <link rel="shortcut icon" type="image/x-icon" href="images/favicon.png" />
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
</head>
<body>
<?php
if (isset($_POST['app_search_submit'])) {
    $contact = $_POST['app_contact'] ?? '';

    $stmt = mysqli_prepare(
        $con,
        "SELECT fname, lname, email, contact, doctor, docFees, appdate, apptime,
                userStatus, doctorStatus
         FROM appointmenttb WHERE contact = ? LIMIT 1"
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
        // Derive human-readable status BEFORE escaping so conditions use raw ints.
        $appstatus = '';
        if ($row['userStatus'] == 1 && $row['doctorStatus'] == 1) $appstatus = "Active";
        if ($row['userStatus'] == 0 && $row['doctorStatus'] == 1) $appstatus = "Cancelled by You";
        if ($row['userStatus'] == 1 && $row['doctorStatus'] == 0) $appstatus = "Cancelled by Doctor";

        // Escape everything for output.
        $fname      = htmlspecialchars($row['fname']      ?? '', ENT_QUOTES, 'UTF-8');
        $lname      = htmlspecialchars($row['lname']      ?? '', ENT_QUOTES, 'UTF-8');
        $email      = htmlspecialchars($row['email']      ?? '', ENT_QUOTES, 'UTF-8');
        $contactOut = htmlspecialchars($row['contact']    ?? '', ENT_QUOTES, 'UTF-8');
        $doctor     = htmlspecialchars($row['doctor']     ?? '', ENT_QUOTES, 'UTF-8');
        $docFees    = htmlspecialchars($row['docFees']    ?? '', ENT_QUOTES, 'UTF-8');
        $appdate    = htmlspecialchars($row['appdate']    ?? '', ENT_QUOTES, 'UTF-8');
        $apptime    = htmlspecialchars($row['apptime']    ?? '', ENT_QUOTES, 'UTF-8');
        $appstatus  = htmlspecialchars($appstatus,              ENT_QUOTES, 'UTF-8');

        echo "<div class='container-fluid' style='margin-top:50px;'>
        <div class='card'>
        <div class='card-body' style='background-color:#342ac1;color:#ffffff;'>
      <table class='table table-hover'>
        <thead>
          <tr>
            <th scope='col'>First Name</th>
            <th scope='col'>Last Name</th>
            <th scope='col'>Email</th>
            <th scope='col'>Contact</th>
            <th scope='col'>Doctor Name</th>
            <th scope='col'>Consultancy Fees</th>
            <th scope='col'>Appointment Date</th>
            <th scope='col'>Appointment Time</th>
            <th scope='col'>Appointment Status</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>$fname</td>
            <td>$lname</td>
            <td>$email</td>
            <td>$contactOut</td>
            <td>$doctor</td>
            <td>$docFees</td>
            <td>$appdate</td>
            <td>$apptime</td>
            <td>$appstatus</td>
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
