<?php
// patient_dashboard.php — patient-only dashboard. Patients can book
// appointments, cancel their OWN appointments (ownership enforced),
// view their own history and prescriptions, and generate a bill PDF
// scoped to their own appointments.

session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();

// Patient-only endpoint.
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'patient' || empty($_SESSION['pid'])) {
    header("Location: patient_login.php");
    exit();
}

$con = mysqli_connect("localhost", "root", "", "myhmsdb");
if (!$con) {
    error_log("DB connection failed: " . mysqli_connect_error());
    die("A server error occurred. Please try again later.");
}

// db_helpers.php defines display_specs() and display_docs() used by
// the booking form below. It's the only shared helper still in use.
include 'db_helpers.php';

$pid      = $_SESSION['pid'];
$username = $_SESSION['username'];
$email    = $_SESSION['email'];
$fname    = $_SESSION['fname'];
$gender   = $_SESSION['gender'];
$lname    = $_SESSION['lname'];
$contact  = $_SESSION['contact'];


if (isset($_POST['app-submit'])) {
    $doctor  = $_POST['doctor']  ?? '';
    $docFees = $_POST['docFees'] ?? '';
    $appdate = $_POST['appdate'] ?? '';
    $apptime = $_POST['apptime'] ?? '';

    date_default_timezone_set('Asia/Kolkata');
    $cur_date  = date("Y-m-d");
    $cur_time  = date("H:i:s");
    $apptime1  = strtotime($apptime);
    $appdate1  = strtotime($appdate);

    if (date("Y-m-d", $appdate1) >= $cur_date) {
        if (
            (date("Y-m-d", $appdate1) == $cur_date && date("H:i:s", $apptime1) > $cur_time)
            || date("Y-m-d", $appdate1) > $cur_date
        ) {
            // Check that slot isn't already taken.
            $check = mysqli_prepare(
                $con,
                "SELECT apptime FROM appointmenttb
                 WHERE doctor = ? AND appdate = ? AND apptime = ?"
            );
            mysqli_stmt_bind_param($check, "sss", $doctor, $appdate, $apptime);
            mysqli_stmt_execute($check);
            $checkRes = mysqli_stmt_get_result($check);
            $taken = mysqli_fetch_assoc($checkRes) !== null;
            mysqli_stmt_close($check);

            if (!$taken) {
                $userStatus   = '1';
                $doctorStatus = '1';

                $stmt = mysqli_prepare(
                    $con,
                    "INSERT INTO appointmenttb
                     (pid, fname, lname, gender, email, contact, doctor,
                      docFees, appdate, apptime, userStatus, doctorStatus)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
                );
                mysqli_stmt_bind_param(
                    $stmt, "isssssssssss",
                    $pid, $fname, $lname, $gender, $email, $contact,
                    $doctor, $docFees, $appdate, $apptime,
                    $userStatus, $doctorStatus
                );
                $ok = mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);

                if ($ok) {
                    echo "<script>alert('Your appointment successfully booked');</script>";
                } else {
                    echo "<script>alert('Unable to process your request. Please try again!');</script>";
                }
            } else {
                echo "<script>alert('We are sorry to inform that the doctor is not available in this time or date. Please choose different time or date!');</script>";
            }
        } else {
            echo "<script>alert('Select a time or date in the future!');</script>";
        }
    } else {
        echo "<script>alert('Select a time or date in the future!');</script>";
    }
}


if (isset($_GET['cancel'], $_GET['ID'])) {
    $appID = $_GET['ID'];

    $stmt = mysqli_prepare(
        $con,
        "UPDATE appointmenttb SET userStatus = '0' WHERE ID = ? AND pid = ?"
    );
    mysqli_stmt_bind_param($stmt, "ii", $appID, $pid);
    mysqli_stmt_execute($stmt);
    $affected = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);

    if ($affected > 0) {
        echo "<script>alert('Your appointment successfully cancelled');</script>";
    } else {
        echo "<script>alert('Unable to cancel: appointment not found or not yours.');</script>";
    }
}


function generate_bill()
{
    $con = mysqli_connect("localhost", "root", "", "myhmsdb");
    $pid = $_SESSION['pid'];
    $appID = $_GET['ID'] ?? '';

    $output = '';
    $stmt = mysqli_prepare(
        $con,
        "SELECT p.pid, p.ID, p.fname, p.lname, p.doctor, p.appdate, p.apptime,
                p.disease, p.allergy, p.prescription, a.docFees
         FROM prestb p
         INNER JOIN appointmenttb a ON p.ID = a.ID
         WHERE p.pid = ? AND p.ID = ?"
    );
    mysqli_stmt_bind_param($stmt, "ii", $pid, $appID);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($res)) {
        // These values go into a PDF which itself escapes HTML for
        // rendering, but we still run htmlspecialchars defensively.
        $pidE   = htmlspecialchars((string)($row['pid']   ?? ''), ENT_QUOTES, 'UTF-8');
        $IDE    = htmlspecialchars((string)($row['ID']    ?? ''), ENT_QUOTES, 'UTF-8');
        $fnE    = htmlspecialchars($row['fname']        ?? '', ENT_QUOTES, 'UTF-8');
        $lnE    = htmlspecialchars($row['lname']        ?? '', ENT_QUOTES, 'UTF-8');
        $docE   = htmlspecialchars($row['doctor']       ?? '', ENT_QUOTES, 'UTF-8');
        $adE    = htmlspecialchars($row['appdate']      ?? '', ENT_QUOTES, 'UTF-8');
        $atE    = htmlspecialchars($row['apptime']      ?? '', ENT_QUOTES, 'UTF-8');
        $disE   = htmlspecialchars($row['disease']      ?? '', ENT_QUOTES, 'UTF-8');
        $allE   = htmlspecialchars($row['allergy']      ?? '', ENT_QUOTES, 'UTF-8');
        $presE  = htmlspecialchars($row['prescription'] ?? '', ENT_QUOTES, 'UTF-8');
        $feesE  = htmlspecialchars((string)($row['docFees'] ?? ''), ENT_QUOTES, 'UTF-8');

        $output .= '
        <label> Patient ID : </label>' . $pidE  . '<br/><br/>
        <label> Appointment ID : </label>' . $IDE   . '<br/><br/>
        <label> Patient Name : </label>' . $fnE . ' ' . $lnE . '<br/><br/>
        <label> Doctor Name : </label>' . $docE  . '<br/><br/>
        <label> Appointment Date : </label>' . $adE . '<br/><br/>
        <label> Appointment Time : </label>' . $atE . '<br/><br/>
        <label> Disease : </label>' . $disE . '<br/><br/>
        <label> Allergies : </label>' . $allE . '<br/><br/>
        <label> Prescription : </label>' . $presE . '<br/><br/>
        <label> Fees Paid : </label>' . $feesE . '<br/>
        ';
    }
    mysqli_stmt_close($stmt);
    return $output;
}

if (isset($_GET["generate_bill"])) {
    require_once("TCPDF/tcpdf.php");
    $obj_pdf = new TCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $obj_pdf->SetCreator(PDF_CREATOR);
    $obj_pdf->SetTitle("Generate Bill");
    $obj_pdf->SetHeaderData('', '', PDF_HEADER_TITLE, PDF_HEADER_STRING);
    $obj_pdf->SetHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $obj_pdf->SetFooterFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $obj_pdf->SetDefaultMonospacedFont('helvetica');
    $obj_pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    $obj_pdf->SetMargins(PDF_MARGIN_LEFT, '5', PDF_MARGIN_RIGHT);
    $obj_pdf->SetPrintHeader(false);
    $obj_pdf->SetPrintFooter(false);
    $obj_pdf->SetAutoPageBreak(TRUE, 10);
    $obj_pdf->SetFont('helvetica', '', 12);
    $obj_pdf->AddPage();

    $content  = '<br/><h2 align="center"> Global Hospitals</h2></br><h3 align="center"> Bill</h3>';
    $content .= generate_bill();
    $obj_pdf->writeHTML($content);
    ob_end_clean();
    $obj_pdf->Output("bill.pdf", 'I');
}

// Pre-escape header values.
$username_h = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
$fname_for_query = $fname;
$lname_for_query = $lname;
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
            <a class="nav-link" href="patient_logout.php"><i class="fa fa-sign-out" aria-hidden="true"></i>Logout</a>
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
        Welcome &nbsp;<?php echo $username_h; ?>
      </h3>

      <div class="row">
        <div class="col-md-4" style="max-width:25%; margin-top: 3%">
          <div class="list-group" id="list-tab" role="tablist">
            <a class="list-group-item list-group-item-action active" id="list-dash-list" data-toggle="list" href="#list-dash" role="tab" aria-controls="home">Dashboard</a>
            <a class="list-group-item list-group-item-action" id="list-home-list" data-toggle="list" href="#list-home" role="tab" aria-controls="home">Book Appointment</a>
            <a class="list-group-item list-group-item-action" href="#app-hist" id="list-pat-list"  role="tab" data-toggle="list" aria-controls="home">Appointment History</a>
            <a class="list-group-item list-group-item-action" href="#list-pres" id="list-pres-list" role="tab" data-toggle="list" aria-controls="home">Prescriptions</a>
          </div><br>
        </div>

        <div class="col-md-8" style="margin-top: 3%;">
          <div class="tab-content" id="nav-tabContent" style="width: 950px;">

            <!-- DASHBOARD -->
            <div class="tab-pane fade show active" id="list-dash" role="tabpanel" aria-labelledby="list-dash-list">
              <div class="container-fluid container-fullw bg-white">
                <div class="row">
                  <div class="col-sm-4" style="left: 5%">
                    <div class="panel panel-white no-radius text-center">
                      <div class="panel-body">
                        <span class="fa-stack fa-2x"><i class="fa fa-square fa-stack-2x text-primary"></i><i class="fa fa-terminal fa-stack-1x fa-inverse"></i></span>
                        <h4 class="StepTitle" style="margin-top: 5%;">Book My Appointment</h4>
                        <script>function clickDiv(id) { document.querySelector(id).click(); }</script>
                        <p class="links cl-effect-1"><a href="#list-home" onclick="clickDiv('#list-home-list')">Book Appointment</a></p>
                      </div>
                    </div>
                  </div>

                  <div class="col-sm-4" style="left: 10%">
                    <div class="panel panel-white no-radius text-center">
                      <div class="panel-body">
                        <span class="fa-stack fa-2x"><i class="fa fa-square fa-stack-2x text-primary"></i><i class="fa fa-paperclip fa-stack-1x fa-inverse"></i></span>
                        <h4 class="StepTitle" style="margin-top: 5%;">My Appointments</h4>
                        <p class="cl-effect-1"><a href="#app-hist" onclick="clickDiv('#list-pat-list')">View Appointment History</a></p>
                      </div>
                    </div>
                  </div>

                  <div class="col-sm-4" style="left: 20%;margin-top:5%">
                    <div class="panel panel-white no-radius text-center">
                      <div class="panel-body">
                        <span class="fa-stack fa-2x"><i class="fa fa-square fa-stack-2x text-primary"></i><i class="fa fa-list-ul fa-stack-1x fa-inverse"></i></span>
                        <h4 class="StepTitle" style="margin-top: 5%;">Prescriptions</h4>
                        <p class="cl-effect-1"><a href="#list-pres" onclick="clickDiv('#list-pres-list')">View Prescription List</a></p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- BOOK APPOINTMENT -->
            <div class="tab-pane fade" id="list-home" role="tabpanel" aria-labelledby="list-home-list">
              <div class="container-fluid">
                <div class="card">
                  <div class="card-body">
                    <center><h4>Create an appointment</h4></center><br>
                    <form class="form-group" method="post" action="patient_dashboard.php">
                      <div class="row">
                        <div class="col-md-4"><label for="spec">Specialization:</label></div>
                        <div class="col-md-8">
                          <select name="spec" class="form-control" id="spec">
                            <option value="" disabled selected>Select Specialization</option>
                            <?php display_specs(); ?>
                          </select>
                        </div><br><br>

                        <script>
                          document.getElementById('spec').onchange = function() {
                            let spec = this.value;
                            let docs = [...document.getElementById('doctor').options];
                            docs.forEach((el, ind, arr) => {
                              arr[ind].setAttribute("style", "");
                              if (el.getAttribute("data-spec") != spec) {
                                arr[ind].setAttribute("style", "display: none");
                              }
                            });
                          };
                        </script>

                        <div class="col-md-4"><label for="doctor">Doctors:</label></div>
                        <div class="col-md-8">
                          <select name="doctor" class="form-control" id="doctor" required="required">
                            <option value="" disabled selected>Select Doctor</option>
                            <?php display_docs(); ?>
                          </select>
                        </div><br/><br/>

                        <script>
                          document.getElementById('doctor').onchange = function() {
                            var selection = document.querySelector(`[value=${this.value}]`).getAttribute('data-value');
                            document.getElementById('docFees').value = selection;
                          };
                        </script>

                        <div class="col-md-4"><label for="consultancyfees">Consultancy Fees</label></div>
                        <div class="col-md-8">
                          <input class="form-control" type="text" name="docFees" id="docFees" readonly="readonly"/>
                        </div><br><br>

                        <div class="col-md-4"><label>Appointment Date</label></div>
                        <div class="col-md-8"><input type="date" class="form-control datepicker" name="appdate"></div><br><br>

                        <div class="col-md-4"><label>Appointment Time</label></div>
                        <div class="col-md-8">
                          <select name="apptime" class="form-control" id="apptime" required="required">
                            <option value="" disabled selected>Select Time</option>
                            <option value="08:00:00">8:00 AM</option>
                            <option value="10:00:00">10:00 AM</option>
                            <option value="12:00:00">12:00 PM</option>
                            <option value="14:00:00">2:00 PM</option>
                            <option value="16:00:00">4:00 PM</option>
                          </select>
                        </div><br><br>

                        <div class="col-md-4">
                          <input type="submit" name="app-submit" value="Create new entry" class="btn btn-primary" id="inputbtn">
                        </div>
                      </div>
                    </form>
                  </div>
                </div>
              </div><br>
            </div>

            <!-- APPOINTMENT HISTORY -->
            <div class="tab-pane fade" id="app-hist" role="tabpanel" aria-labelledby="list-pat-list">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th scope="col">Doctor Name</th>
                    <th scope="col">Consultancy Fees</th>
                    <th scope="col">Appointment Date</th>
                    <th scope="col">Appointment Time</th>
                    <th scope="col">Current Status</th>
                    <th scope="col">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                    // Scope to the logged-in patient's pid (not their name) to
                    // avoid leaking appointments of another patient sharing a name.
                    $stmt = mysqli_prepare(
                        $con,
                        "SELECT ID, doctor, docFees, appdate, apptime, userStatus, doctorStatus
                         FROM appointmenttb WHERE pid = ?"
                    );
                    mysqli_stmt_bind_param($stmt, "i", $pid);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);

                    while ($row = mysqli_fetch_assoc($result)) {
                        $raw_ID = $row['ID'];
                        $ID_h   = htmlspecialchars((string)$raw_ID, ENT_QUOTES, 'UTF-8');
                        $doc_h  = htmlspecialchars($row['doctor']   ?? '', ENT_QUOTES, 'UTF-8');
                        $fees_h = htmlspecialchars((string)($row['docFees'] ?? ''), ENT_QUOTES, 'UTF-8');
                        $ad_h   = htmlspecialchars($row['appdate']  ?? '', ENT_QUOTES, 'UTF-8');
                        $at_h   = htmlspecialchars($row['apptime']  ?? '', ENT_QUOTES, 'UTF-8');

                        $status = '';
                        if ($row['userStatus'] == 1 && $row['doctorStatus'] == 1) $status = 'Active';
                        if ($row['userStatus'] == 0 && $row['doctorStatus'] == 1) $status = 'Cancelled by You';
                        if ($row['userStatus'] == 1 && $row['doctorStatus'] == 0) $status = 'Cancelled by Doctor';
                        $status_h = htmlspecialchars($status, ENT_QUOTES, 'UTF-8');

                        $active = ($row['userStatus'] == 1 && $row['doctorStatus'] == 1);

                        echo "<tr>
                                <td>{$doc_h}</td>
                                <td>{$fees_h}</td>
                                <td>{$ad_h}</td>
                                <td>{$at_h}</td>
                                <td>{$status_h}</td>";

                        if ($active) {
                            $cancel_url = 'patient_dashboard.php?ID=' . urlencode((string)$raw_ID) . '&cancel=update';
                            echo "<td><a href='" . htmlspecialchars($cancel_url, ENT_QUOTES, 'UTF-8') . "'
                                       onClick=\"return confirm('Are you sure you want to cancel this appointment ?')\"
                                       title='Cancel Appointment'>
                                    <button class='btn btn-danger'>Cancel</button></a></td>";
                        } else {
                            echo "<td>Cancelled</td>";
                        }
                        echo "</tr>";
                    }
                    mysqli_stmt_close($stmt);
                  ?>
                </tbody>
              </table>
            </div>

            <!-- PRESCRIPTIONS -->
            <div class="tab-pane fade" id="list-pres" role="tabpanel" aria-labelledby="list-pres-list">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th scope="col">Doctor Name</th>
                    <th scope="col">Appointment ID</th>
                    <th scope="col">Appointment Date</th>
                    <th scope="col">Appointment Time</th>
                    <th scope="col">Diseases</th>
                    <th scope="col">Allergies</th>
                    <th scope="col">Prescriptions</th>
                    <th scope="col">Bill Payment</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                    $stmt = mysqli_prepare(
                        $con,
                        "SELECT doctor, ID, appdate, apptime, disease, allergy, prescription
                         FROM prestb WHERE pid = ?"
                    );
                    mysqli_stmt_bind_param($stmt, "i", $pid);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);

                    while ($row = mysqli_fetch_assoc($result)) {
                        $raw_ID = $row['ID'];
                        $doc_h  = htmlspecialchars($row['doctor']        ?? '', ENT_QUOTES, 'UTF-8');
                        $ID_h   = htmlspecialchars((string)$raw_ID,            ENT_QUOTES, 'UTF-8');
                        $ad_h   = htmlspecialchars($row['appdate']       ?? '', ENT_QUOTES, 'UTF-8');
                        $at_h   = htmlspecialchars($row['apptime']       ?? '', ENT_QUOTES, 'UTF-8');
                        $dis_h  = htmlspecialchars($row['disease']       ?? '', ENT_QUOTES, 'UTF-8');
                        $all_h  = htmlspecialchars($row['allergy']       ?? '', ENT_QUOTES, 'UTF-8');
                        $pres_h = htmlspecialchars($row['prescription']  ?? '', ENT_QUOTES, 'UTF-8');

                        $bill_url = 'patient_dashboard.php?ID=' . urlencode((string)$raw_ID) . '&generate_bill=1';
                        $bill_url_h = htmlspecialchars($bill_url, ENT_QUOTES, 'UTF-8');

                        echo "<tr>
                                <td>{$doc_h}</td>
                                <td>{$ID_h}</td>
                                <td>{$ad_h}</td>
                                <td>{$at_h}</td>
                                <td>{$dis_h}</td>
                                <td>{$all_h}</td>
                                <td>{$pres_h}</td>
                                <td>
                                  <a href='{$bill_url_h}'>
                                    <button type='button' onclick=\"alert('Bill Paid Successfully');\" class='btn btn-success'>Pay Bill</button>
                                  </a>
                                </td>
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
