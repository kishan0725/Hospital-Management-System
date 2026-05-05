<?php
// doctor-only dashboard. Lists the logged-in
// doctor's appointments and prescriptions, and lets them cancel their
// OWN appointments only (ownership is enforced in the UPDATE).

session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();

// Doctor-only endpoint. Anyone else is redirected to login.
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'doctor' || empty($_SESSION['dname'])) {
    header("Location: register.php");
    exit();
}

$con = mysqli_connect("localhost", "root", "", "myhmsdb");
if (!$con) {
    error_log("DB connection failed: " . mysqli_connect_error());
    die("A server error occurred. Please try again later.");
}

$doctor = $_SESSION['dname'];


if (isset($_GET['cancel'], $_GET['ID'])) {
    $appID = $_GET['ID'];

    $stmt = mysqli_prepare(
        $con,
        "UPDATE appointmenttb SET doctorStatus = '0' WHERE ID = ? AND doctor = ?"
    );
    mysqli_stmt_bind_param($stmt, "is", $appID, $doctor);
    mysqli_stmt_execute($stmt);
    $affected = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);

    if ($affected > 0) {
        echo "<script>alert('Your appointment successfully cancelled');</script>";
    } else {
        echo "<script>alert('Unable to cancel: appointment not found or not yours.');</script>";
    }
}

// HTML-escape the doctor name once for header display.
$doctor_html = htmlspecialchars($doctor, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" type="text/css" href="font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="vendor/fontawesome/css/font-awesome.min.css">
    <link rel="shortcut icon" type="image/x-icon" href="images/favicon.png" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css?family=IBM+Plex+Sans&display=swap" rel="stylesheet">

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
      <a class="navbar-brand" href="#"><i class="fa fa-user-plus" aria-hidden="true"></i> Global Hospital </a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <style>
        .btn-outline-light:hover { color: #25bef7; background-color: #f8f9fa; border-color: #f8f9fa; }
        .bg-primary { background: -webkit-linear-gradient(left, #3931af, #00c6ff); }
        .list-group-item.active { z-index: 2; color: #fff; background-color: #342ac1; border-color: #007bff; }
        .text-primary { color: #342ac1 !important; }
      </style>

      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">
          <li class="nav-item">
            <a class="nav-link" href="doctor_logout.php"><i class="fa fa-sign-out" aria-hidden="true"></i>Logout</a>
          </li>
        </ul>
        <form class="form-inline my-2 my-lg-0" method="post" action="search.php">
          <input class="form-control mr-sm-2" type="text" placeholder="Enter contact number" aria-label="Search" name="contact">
          <input type="submit" class="btn btn-outline-light" id="inputbtn" name="search_submit" value="Search">
        </form>
      </div>
    </nav>
  </head>
  <style type="text/css">
    button:hover { cursor: pointer; }
    #inputbtn:hover { cursor: pointer; }
  </style>
  <body style="padding-top:50px;">
    <div class="container-fluid" style="margin-top:50px;">
      <h3 style="margin-left: 40%; padding-bottom: 20px;font-family:'IBM Plex Sans', sans-serif;">
        Welcome &nbsp;<?php echo $doctor_html; ?>
      </h3>

      <div class="row">
        <div class="col-md-4" style="max-width:18%;margin-top: 3%;">
          <div class="list-group" id="list-tab" role="tablist">
            <a class="list-group-item list-group-item-action active" href="#list-dash" role="tab" aria-controls="home" data-toggle="list">Dashboard</a>
            <a class="list-group-item list-group-item-action" href="#list-app"  id="list-app-list"  role="tab" data-toggle="list" aria-controls="home">Appointments</a>
            <a class="list-group-item list-group-item-action" href="#list-pres" id="list-pres-list" role="tab" data-toggle="list" aria-controls="home">Prescription List</a>
          </div><br>
        </div>

        <div class="col-md-8" style="margin-top: 3%;">
          <div class="tab-content" id="nav-tabContent" style="width: 950px;">

            <!-- DASHBOARD -->
            <div class="tab-pane fade show active" id="list-dash" role="tabpanel" aria-labelledby="list-dash-list">
              <div class="container-fluid container-fullw bg-white">
                <div class="row">
                  <div class="col-sm-4" style="left: 10%">
                    <div class="panel panel-white no-radius text-center">
                      <div class="panel-body">
                        <span class="fa-stack fa-2x"><i class="fa fa-square fa-stack-2x text-primary"></i><i class="fa fa-list fa-stack-1x fa-inverse"></i></span>
                        <h4 class="StepTitle" style="margin-top: 5%;">View Appointments</h4>
                        <script>
                          function clickDiv(id) { document.querySelector(id).click(); }
                        </script>
                        <p class="links cl-effect-1">
                          <a href="#list-app" onclick="clickDiv('#list-app-list')">Appointment List</a>
                        </p>
                      </div>
                    </div>
                  </div>

                  <div class="col-sm-4" style="left: 15%">
                    <div class="panel panel-white no-radius text-center">
                      <div class="panel-body">
                        <span class="fa-stack fa-2x"><i class="fa fa-square fa-stack-2x text-primary"></i><i class="fa fa-list-ul fa-stack-1x fa-inverse"></i></span>
                        <h4 class="StepTitle" style="margin-top: 5%;">Prescriptions</h4>
                        <p class="links cl-effect-1">
                          <a href="#list-pres" onclick="clickDiv('#list-pres-list')">Prescription List</a>
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- APPOINTMENTS -->
            <div class="tab-pane fade" id="list-app" role="tabpanel" aria-labelledby="list-home-list">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th scope="col">Patient ID</th>
                    <th scope="col">Appointment ID</th>
                    <th scope="col">First Name</th>
                    <th scope="col">Last Name</th>
                    <th scope="col">Gender</th>
                    <th scope="col">Email</th>
                    <th scope="col">Contact</th>
                    <th scope="col">Appointment Date</th>
                    <th scope="col">Appointment Time</th>
                    <th scope="col">Current Status</th>
                    <th scope="col">Action</th>
                    <th scope="col">Prescribe</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                    $stmt = mysqli_prepare(
                        $con,
                        "SELECT pid, ID, fname, lname, gender, email, contact,
                                appdate, apptime, userStatus, doctorStatus
                         FROM appointmenttb WHERE doctor = ?"
                    );
                    mysqli_stmt_bind_param($stmt, "s", $doctor);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);

                    while ($row = mysqli_fetch_assoc($result)) {
                        // Raw IDs used for URL building — urlencode them.
                        $raw_pid   = $row['pid'];
                        $raw_ID    = $row['ID'];
                        $raw_fname = $row['fname'] ?? '';
                        $raw_lname = $row['lname'] ?? '';
                        $raw_appd  = $row['appdate'] ?? '';
                        $raw_appt  = $row['apptime'] ?? '';

                        // Escaped values for HTML display.
                        $pid_h    = htmlspecialchars((string)$raw_pid, ENT_QUOTES, 'UTF-8');
                        $ID_h     = htmlspecialchars((string)$raw_ID,  ENT_QUOTES, 'UTF-8');
                        $fname_h  = htmlspecialchars($raw_fname,       ENT_QUOTES, 'UTF-8');
                        $lname_h  = htmlspecialchars($raw_lname,       ENT_QUOTES, 'UTF-8');
                        $gender_h = htmlspecialchars($row['gender']  ?? '', ENT_QUOTES, 'UTF-8');
                        $email_h  = htmlspecialchars($row['email']   ?? '', ENT_QUOTES, 'UTF-8');
                        $cont_h   = htmlspecialchars($row['contact'] ?? '', ENT_QUOTES, 'UTF-8');
                        $appd_h   = htmlspecialchars($raw_appd,        ENT_QUOTES, 'UTF-8');
                        $appt_h   = htmlspecialchars($raw_appt,        ENT_QUOTES, 'UTF-8');

                        // Status string.
                        $status = '';
                        if ($row['userStatus'] == 1 && $row['doctorStatus'] == 1) $status = 'Active';
                        if ($row['userStatus'] == 0 && $row['doctorStatus'] == 1) $status = 'Cancelled by Patient';
                        if ($row['userStatus'] == 1 && $row['doctorStatus'] == 0) $status = 'Cancelled by You';
                        $status_h = htmlspecialchars($status, ENT_QUOTES, 'UTF-8');

                        $active = ($row['userStatus'] == 1 && $row['doctorStatus'] == 1);

                        echo "<tr>";
                        echo "<td>{$pid_h}</td><td>{$ID_h}</td>";
                        echo "<td>{$fname_h}</td><td>{$lname_h}</td>";
                        echo "<td>{$gender_h}</td><td>{$email_h}</td>";
                        echo "<td>{$cont_h}</td>";
                        echo "<td>{$appd_h}</td><td>{$appt_h}</td>";
                        echo "<td>{$status_h}</td>";

                        // Cancel cell.
                        if ($active) {
                            $cancel_url = 'doctor_dashboard.php?ID=' . urlencode((string)$raw_ID) . '&cancel=update';
                            echo "<td><a href='" . htmlspecialchars($cancel_url, ENT_QUOTES, 'UTF-8') . "'
                                        onClick=\"return confirm('Are you sure you want to cancel this appointment ?')\"
                                        title='Cancel Appointment'>
                                    <button class='btn btn-danger'>Cancel</button></a></td>";
                        } else {
                            echo "<td>Cancelled</td>";
                        }

                        // Prescribe cell.
                        if ($active) {
                            $presc_url = 'prescription_form.php'
                                . '?pid='     . urlencode((string)$raw_pid)
                                . '&ID='      . urlencode((string)$raw_ID)
                                . '&fname='   . urlencode($raw_fname)
                                . '&lname='   . urlencode($raw_lname)
                                . '&appdate=' . urlencode($raw_appd)
                                . '&apptime=' . urlencode($raw_appt);
                            echo "<td><a href='" . htmlspecialchars($presc_url, ENT_QUOTES, 'UTF-8') . "' title='prescribe'>
                                    <button class='btn btn-success'>Prescribe</button></a></td>";
                        } else {
                            echo "<td>-</td>";
                        }
                        echo "</tr>";
                    }
                    mysqli_stmt_close($stmt);
                  ?>
                </tbody>
              </table>
              <br>
            </div>

            <!-- PRESCRIPTIONS -->
            <div class="tab-pane fade" id="list-pres" role="tabpanel" aria-labelledby="list-pres-list">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th scope="col">Patient ID</th>
                    <th scope="col">First Name</th>
                    <th scope="col">Last Name</th>
                    <th scope="col">Appointment ID</th>
                    <th scope="col">Appointment Date</th>
                    <th scope="col">Appointment Time</th>
                    <th scope="col">Disease</th>
                    <th scope="col">Allergy</th>
                    <th scope="col">Prescribe</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                    $stmt = mysqli_prepare(
                        $con,
                        "SELECT pid, fname, lname, ID, appdate, apptime,
                                disease, allergy, prescription
                         FROM prestb WHERE doctor = ?"
                    );
                    mysqli_stmt_bind_param($stmt, "s", $doctor);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);

                    while ($row = mysqli_fetch_assoc($result)) {
                        $pid_h   = htmlspecialchars((string)($row['pid']   ?? ''), ENT_QUOTES, 'UTF-8');
                        $fname_h = htmlspecialchars($row['fname']        ?? '', ENT_QUOTES, 'UTF-8');
                        $lname_h = htmlspecialchars($row['lname']        ?? '', ENT_QUOTES, 'UTF-8');
                        $ID_h    = htmlspecialchars((string)($row['ID']    ?? ''), ENT_QUOTES, 'UTF-8');
                        $appd_h  = htmlspecialchars($row['appdate']      ?? '', ENT_QUOTES, 'UTF-8');
                        $appt_h  = htmlspecialchars($row['apptime']      ?? '', ENT_QUOTES, 'UTF-8');
                        $dis_h   = htmlspecialchars($row['disease']      ?? '', ENT_QUOTES, 'UTF-8');
                        $all_h   = htmlspecialchars($row['allergy']      ?? '', ENT_QUOTES, 'UTF-8');
                        $pres_h  = htmlspecialchars($row['prescription'] ?? '', ENT_QUOTES, 'UTF-8');

                        echo "<tr>
                                <td>{$pid_h}</td>
                                <td>{$fname_h}</td>
                                <td>{$lname_h}</td>
                                <td>{$ID_h}</td>
                                <td>{$appd_h}</td>
                                <td>{$appt_h}</td>
                                <td>{$dis_h}</td>
                                <td>{$all_h}</td>
                                <td>{$pres_h}</td>
                              </tr>";
                    }
                    mysqli_stmt_close($stmt);
                  ?>
                </tbody>
              </table>
            </div>

          </div>
        </div>
      </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/6.10.1/sweetalert2.all.min.js"></script>
  </body>
</html>
