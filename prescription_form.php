<?php
// prescription_form.php — doctor-only. Lets a doctor write a
// prescription against an appointment that belongs to them. Ownership
// of the appointment is verified before insert (IDOR protection).

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
    return;
}

$con = mysqli_connect("localhost", "root", "", getenv("HMS_DB_NAME") ?: "myhmsdb");
if (!$con) {
    error_log("DB connection failed: " . mysqli_connect_error());
    die("A server error occurred. Please try again later.");
}

$doctor  = $_SESSION['dname'];
$pid     = '';
$ID      = '';
$appdate = '';
$apptime = '';
$fname   = '';
$lname   = '';

if (isset($_GET['pid'], $_GET['ID'], $_GET['appdate'], $_GET['apptime'], $_GET['fname'], $_GET['lname'])) {
    $pid     = $_GET['pid'];
    $ID      = $_GET['ID'];
    $fname   = $_GET['fname'];
    $lname   = $_GET['lname'];
    $appdate = $_GET['appdate'];
    $apptime = $_GET['apptime'];
}



if (
    isset($_POST['prescribe'], $_POST['pid'], $_POST['ID'],
          $_POST['appdate'],   $_POST['apptime'],
          $_POST['lname'],     $_POST['fname'])
) {
    $appdate      = $_POST['appdate'];
    $apptime      = $_POST['apptime'];
    $disease      = $_POST['disease']      ?? '';
    $allergy      = $_POST['allergy']      ?? '';
    $fname        = $_POST['fname'];
    $lname        = $_POST['lname'];
    $pid          = $_POST['pid'];
    $ID           = $_POST['ID'];
    $prescription = $_POST['prescription'] ?? '';

    // Ownership check: the appointment must belong to the logged-in doctor.
    $check = mysqli_prepare(
        $con,
        "SELECT ID FROM appointmenttb WHERE ID = ? AND doctor = ? LIMIT 1"
    );
    mysqli_stmt_bind_param($check, "is", $ID, $doctor);
    mysqli_stmt_execute($check);
    $ownRes = mysqli_stmt_get_result($check);
    $owns   = mysqli_fetch_assoc($ownRes) !== null;
    mysqli_stmt_close($check);

    if (!$owns) {
        http_response_code(403);
        echo "<script>alert('Forbidden: that appointment is not yours.');
              window.location.href = 'doctor_dashboard.php';</script>";
        return;
    }

    $stmt = mysqli_prepare(
        $con,
        "INSERT INTO prestb
         (doctor, pid, ID, fname, lname, appdate, apptime, disease, allergy, prescription)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param(
        $stmt, "siisssssss",
        $doctor, $pid, $ID, $fname, $lname,
        $appdate, $apptime, $disease, $allergy, $prescription
    );
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($ok) {
        echo "<script>alert('Prescribed successfully!');</script>";
    } else {
        echo "<script>alert('Unable to process your request. Try again!');</script>";
    }
}

// Escape variables that appear in the form below.
$doctor_html  = htmlspecialchars($doctor,  ENT_QUOTES, 'UTF-8');
$fname_html   = htmlspecialchars($fname,   ENT_QUOTES, 'UTF-8');
$lname_html   = htmlspecialchars($lname,   ENT_QUOTES, 'UTF-8');
$appdate_html = htmlspecialchars($appdate, ENT_QUOTES, 'UTF-8');
$apptime_html = htmlspecialchars($apptime, ENT_QUOTES, 'UTF-8');
$pid_html     = htmlspecialchars((string)$pid, ENT_QUOTES, 'UTF-8');
$ID_html      = htmlspecialchars((string)$ID,  ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <link rel="shortcut icon" type="image/x-icon" href="images/favicon.png" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" type="text/css" href="font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="style.css">

    <link rel="stylesheet" href="vendor/fontawesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css?family=IBM+Plex+Sans&display=swap" rel="stylesheet">

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
      <a class="navbar-brand" href="#"><i class="fa fa-user-plus" aria-hidden="true"></i> Global Hospital </a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <style>
        .bg-primary { background: -webkit-linear-gradient(left, #3931af, #00c6ff); }
        .list-group-item.active { z-index: 2; color: #fff; background-color: #342ac1; border-color: #007bff; }
        .text-primary { color: #342ac1 !important; }
        .btn-primary { background-color: #3c50c1; border-color: #3c50c1; }
      </style>

      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">
          <li class="nav-item">
            <a class="nav-link" href="doctor_logout.php"><i class="fa fa-sign-out" aria-hidden="true"></i>Logout</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="doctor_dashboard.php"><i class="fa fa-sign-out" aria-hidden="true"></i>Back</a>
          </li>
        </ul>
      </div>
    </nav>
  </head>
  <style type="text/css">
    button:hover { cursor: pointer; }
    #inputbtn:hover { cursor: pointer; }
  </style>
  <body style="padding-top:50px;">
    <div class="container-fluid" style="margin-top:50px;">
      <h3 style="margin-left: 40%; padding-bottom: 20px; font-family: 'IBM Plex Sans', sans-serif;">
        Welcome &nbsp;<?php echo $doctor_html; ?>
      </h3>

      <div class="tab-pane" id="list-pres" role="tabpanel" aria-labelledby="list-pres-list">
        <form class="form-group" name="prescribeform" method="post" action="prescription_form.php">
          <div class="row">
            <div class="col-md-4"><label>Disease:</label></div>
            <div class="col-md-8">
              <textarea id="disease" cols="86" rows="5" name="disease" required></textarea>
            </div><br><br><br>

            <div class="col-md-4"><label>Allergies:</label></div>
            <div class="col-md-8">
              <textarea id="allergy" cols="86" rows="5" name="allergy" required></textarea>
            </div><br><br><br>

            <div class="col-md-4"><label>Prescription:</label></div>
            <div class="col-md-8">
              <textarea id="prescription" cols="86" rows="10" name="prescription" required></textarea>
            </div><br><br><br>

            <input type="hidden" name="fname"   value="<?php echo $fname_html; ?>" />
            <input type="hidden" name="lname"   value="<?php echo $lname_html; ?>" />
            <input type="hidden" name="appdate" value="<?php echo $appdate_html; ?>" />
            <input type="hidden" name="apptime" value="<?php echo $apptime_html; ?>" />
            <input type="hidden" name="pid"     value="<?php echo $pid_html; ?>" />
            <input type="hidden" name="ID"      value="<?php echo $ID_html; ?>" />
            <br><br><br><br>

            <input type="submit" name="prescribe" value="Prescribe" class="btn btn-primary" style="margin-left: 40pc;">
          </div>
        </form>
        <br>
      </div>
    </div>
  </body>
</html>
