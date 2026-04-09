<!DOCTYPE html>
<?php
// patient_auth.php starts the session and checks login
include('patient_auth.php');
// db_helpers.php has display_docs() and display_specs()
include('db_helpers.php');

$con = mysqli_connect("localhost", "root", "", "myhmsdb");

$pid      = $_SESSION['pid'];
$username = $_SESSION['username'];
$email    = $_SESSION['email'];
$fname    = $_SESSION['fname'];
$gender   = $_SESSION['gender'];
$lname    = $_SESSION['lname'];
$contact  = $_SESSION['contact'];


// ── Book Appointment ───────────────────────────────────────────────────────
if (isset($_POST['app-submit'])) {
    $doctor  = $_POST['doctor'];
    $docFees = $_POST['docFees'];
    $appdate = $_POST['appdate'];
    $apptime = $_POST['apptime'];

    $cur_date = date("Y-m-d");
    date_default_timezone_set('Asia/Kolkata');
    $cur_time  = date("H:i:s");
    $appdate1  = strtotime($appdate);
    $apptime1  = strtotime($apptime);

    if (date("Y-m-d", $appdate1) >= $cur_date) {
        $future_time = (date("Y-m-d", $appdate1) == $cur_date && date("H:i:s", $apptime1) > $cur_time)
                    || date("Y-m-d", $appdate1) > $cur_date;

        if ($future_time) {
            // Check if that slot is already taken
            $stmt = mysqli_prepare($con,
                "SELECT apptime FROM appointmenttb WHERE doctor=? AND appdate=? AND apptime=?"
            );
            mysqli_stmt_bind_param($stmt, "sss", $doctor, $appdate, $apptime);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            $slot_taken = mysqli_stmt_num_rows($stmt);
            mysqli_stmt_close($stmt);

            if ($slot_taken == 0) {
                // Book the appointment
                $stmt = mysqli_prepare($con,
                    "INSERT INTO appointmenttb
                        (pid,fname,lname,gender,email,contact,doctor,docFees,appdate,apptime,userStatus,doctorStatus)
                     VALUES (?,?,?,?,?,?,?,?,?,?,'1','1')"
                );
                mysqli_stmt_bind_param($stmt, "isssssssss",
                    $pid, $fname, $lname, $gender, $email, $contact,
                    $doctor, $docFees, $appdate, $apptime
                );
                $ok = mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);

                if ($ok) {
                    echo "<script>alert('Your appointment successfully booked');</script>";
                } else {
                    echo "<script>alert('Unable to process your request. Please try again!');</script>";
                }
            } else {
                echo "<script>alert('That time slot is already taken. Please choose a different time or date!');</script>";
            }
        } else {
            echo "<script>alert('Select a time or date in the future!');</script>";
        }
    } else {
        echo "<script>alert('Select a time or date in the future!');</script>";
    }
}


// ── Cancel Appointment ─────────────────────────────────────────────────────
if (isset($_GET['cancel'])) {
    $appointmentID = $_GET['ID'];

    // pid from session — only cancels appointments belonging to this patient
    $stmt = mysqli_prepare($con,
        "UPDATE appointmenttb SET userStatus='0' WHERE ID=? AND pid=?"
    );
    mysqli_stmt_bind_param($stmt, "si", $appointmentID, $pid);
    mysqli_stmt_execute($stmt);
    $affected = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);

    if ($affected > 0) {
        echo "<script>alert('Your appointment successfully cancelled');</script>";
    } else {
        echo "<script>alert('Cancellation failed: appointment not found.');</script>";
    }
}


// ── Generate Bill (PDF) ────────────────────────────────────────────────────
function generate_bill() {
    global $con, $pid;
    $ID     = $_GET['ID'];
    $output = '';

    $stmt = mysqli_prepare($con,
        "SELECT p.pid,p.ID,p.fname,p.lname,p.doctor,p.appdate,p.apptime,
                p.disease,p.allergy,p.prescription,a.docFees
         FROM prestb p
         INNER JOIN appointmenttb a ON p.ID = a.ID
         WHERE p.pid = ? AND p.ID = ?"
    );
    mysqli_stmt_bind_param($stmt, "is", $pid, $ID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_array($result)) {
        $output .= '
        <label>Patient ID:</label> '      . htmlspecialchars($row['pid'],          ENT_QUOTES,'UTF-8') . '<br/><br/>
        <label>Appointment ID:</label> '  . htmlspecialchars($row['ID'],           ENT_QUOTES,'UTF-8') . '<br/><br/>
        <label>Patient Name:</label> '    . htmlspecialchars($row['fname'],        ENT_QUOTES,'UTF-8') . ' '
                                          . htmlspecialchars($row['lname'],        ENT_QUOTES,'UTF-8') . '<br/><br/>
        <label>Doctor Name:</label> '     . htmlspecialchars($row['doctor'],       ENT_QUOTES,'UTF-8') . '<br/><br/>
        <label>Appointment Date:</label> '. htmlspecialchars($row['appdate'],      ENT_QUOTES,'UTF-8') . '<br/><br/>
        <label>Appointment Time:</label> '. htmlspecialchars($row['apptime'],      ENT_QUOTES,'UTF-8') . '<br/><br/>
        <label>Disease:</label> '         . htmlspecialchars($row['disease'],      ENT_QUOTES,'UTF-8') . '<br/><br/>
        <label>Allergies:</label> '       . htmlspecialchars($row['allergy'],      ENT_QUOTES,'UTF-8') . '<br/><br/>
        <label>Prescription:</label> '    . htmlspecialchars($row['prescription'], ENT_QUOTES,'UTF-8') . '<br/><br/>
        <label>Fees Paid:</label> '       . htmlspecialchars($row['docFees'],      ENT_QUOTES,'UTF-8') . '<br/>';
    }
    mysqli_stmt_close($stmt);
    return $output;
}

if (isset($_GET['generate_bill'])) {
    require_once("TCPDF/tcpdf.php");
    $obj_pdf = new TCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $obj_pdf->SetCreator(PDF_CREATOR);
    $obj_pdf->SetTitle("Generate Bill");
    $obj_pdf->SetHeaderData('', '', PDF_HEADER_TITLE, PDF_HEADER_STRING);
    $obj_pdf->SetHeaderFont([PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN]);
    $obj_pdf->SetFooterFont([PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN]);
    $obj_pdf->SetDefaultMonospacedFont('helvetica');
    $obj_pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    $obj_pdf->SetMargins(PDF_MARGIN_LEFT, '5', PDF_MARGIN_RIGHT);
    $obj_pdf->SetPrintHeader(false);
    $obj_pdf->SetPrintFooter(false);
    $obj_pdf->SetAutoPageBreak(true, 10);
    $obj_pdf->SetFont('helvetica', '', 12);
    $obj_pdf->AddPage();

    $content  = '<br/><h2 align="center">Global Hospitals</h2><br/><h3 align="center">Bill</h3>';
    $content .= generate_bill();
    $obj_pdf->writeHTML($content);
    ob_end_clean();
    $obj_pdf->Output("bill.pdf", 'I');
}
?>

<html lang="en">
<head>
  <meta charset="utf-8">
  <link rel="shortcut icon" type="image/x-icon" href="images/favicon.png" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="stylesheet" type="text/css" href="font-awesome-4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="vendor/fontawesome/css/font-awesome.min.css">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css"
        integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
        integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
  <link href="https://fonts.googleapis.com/css?family=IBM+Plex+Sans&display=swap" rel="stylesheet">

  <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
    <a class="navbar-brand" href="#"><i class="fa fa-user-plus" aria-hidden="true"></i> Global Hospital</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent">
      <span class="navbar-toggler-icon"></span>
    </button>
    <style>
      .bg-primary { background: -webkit-linear-gradient(left, #3931af, #00c6ff); }
      .list-group-item.active { color:#fff; background-color:#342ac1; border-color:#007bff; }
      .btn-primary { background-color:#3c50c1; border-color:#3c50c1; }
    </style>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav mr-auto">
        <li class="nav-item">
          <a class="nav-link" href="patient_logout.php">
            <i class="fa fa-sign-out" aria-hidden="true"></i> Logout
          </a>
        </li>
      </ul>
    </div>
  </nav>
</head>

<style>button:hover{cursor:pointer;} #inputbtn:hover{cursor:pointer;}</style>

<body style="padding-top:50px;">
  <div class="container-fluid" style="margin-top:50px;">
    <h3 style="margin-left:40%; padding-bottom:20px; font-family:'IBM Plex Sans',sans-serif;">
      Welcome &nbsp;<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>
    </h3>

    <div class="row">
      <!-- ── Sidebar ──────────────────────────────────────────────────── -->
      <div class="col-md-4" style="max-width:25%; margin-top:3%">
        <div class="list-group" id="list-tab" role="tablist">
          <a class="list-group-item list-group-item-action active"
             id="list-dash-list" data-toggle="list" href="#list-dash" role="tab">Dashboard</a>
          <a class="list-group-item list-group-item-action"
             id="list-home-list" data-toggle="list" href="#list-home" role="tab">Book Appointment</a>
          <a class="list-group-item list-group-item-action"
             id="list-pat-list"  data-toggle="list" href="#app-hist"  role="tab">Appointment History</a>
          <a class="list-group-item list-group-item-action"
             id="list-pres-list" data-toggle="list" href="#list-pres" role="tab">Prescriptions</a>
        </div><br>
      </div>

      <!-- ── Tab Content ──────────────────────────────────────────────── -->
      <div class="col-md-8" style="margin-top:3%;">
        <div class="tab-content" id="nav-tabContent" style="width:950px;">

          <!-- DASHBOARD TAB -->
          <div class="tab-pane fade show active" id="list-dash" role="tabpanel" aria-labelledby="list-dash-list">
            <div class="container-fluid bg-white">
              <div class="row">

                <div class="col-sm-4" style="left:5%">
                  <div class="panel panel-white text-center">
                    <div class="panel-body">
                      <span class="fa-stack fa-2x">
                        <i class="fa fa-square fa-stack-2x text-primary"></i>
                        <i class="fa fa-terminal fa-stack-1x fa-inverse"></i>
                      </span>
                      <h4 style="margin-top:5%">Book My Appointment</h4>
                      <script>
                        function clickDiv(id) { document.querySelector(id).click(); }
                      </script>
                      <p><a href="#list-home" onclick="clickDiv('#list-home-list')">Book Appointment</a></p>
                    </div>
                  </div>
                </div>

                <div class="col-sm-4" style="left:10%">
                  <div class="panel panel-white text-center">
                    <div class="panel-body">
                      <span class="fa-stack fa-2x">
                        <i class="fa fa-square fa-stack-2x text-primary"></i>
                        <i class="fa fa-paperclip fa-stack-1x fa-inverse"></i>
                      </span>
                      <h4 style="margin-top:5%">My Appointments</h4>
                      <p><a href="#app-hist" onclick="clickDiv('#list-pat-list')">View Appointment History</a></p>
                    </div>
                  </div>
                </div>

                <div class="col-sm-4" style="left:20%; margin-top:5%">
                  <div class="panel panel-white text-center">
                    <div class="panel-body">
                      <span class="fa-stack fa-2x">
                        <i class="fa fa-square fa-stack-2x text-primary"></i>
                        <i class="fa fa-list-ul fa-stack-1x fa-inverse"></i>
                      </span>
                      <h4 style="margin-top:5%">Prescriptions</h4>
                      <p><a href="#list-pres" onclick="clickDiv('#list-pres-list')">View Prescription List</a></p>
                    </div>
                  </div>
                </div>

              </div>
            </div>
          </div>
          <!-- END DASHBOARD TAB — this closing div was missing, which trapped all other tabs inside -->


          <!-- BOOK APPOINTMENT TAB -->
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
                        document.getElementById('spec').onchange = function () {
                          let spec = this.value;
                          [...document.getElementById('doctor').options].forEach(el => {
                            el.style.display = (el.getAttribute('data-spec') != spec) ? 'none' : '';
                          });
                        };
                      </script>

                      <div class="col-md-4"><label for="doctor">Doctor:</label></div>
                      <div class="col-md-8">
                        <select name="doctor" class="form-control" id="doctor" required>
                          <option value="" disabled selected>Select Doctor</option>
                          <?php display_docs(); ?>
                        </select>
                      </div><br><br>

                      <script>
                        document.getElementById('doctor').onchange = function () {
                          var fees = document.querySelector(`[value="${this.value}"]`).getAttribute('data-value');
                          document.getElementById('docFees').value = fees;
                        };
                      </script>

                      <div class="col-md-4"><label>Consultancy Fees</label></div>
                      <div class="col-md-8">
                        <input class="form-control" type="text" name="docFees" id="docFees" readonly />
                      </div><br><br>

                      <div class="col-md-4"><label>Appointment Date</label></div>
                      <div class="col-md-8">
                        <input type="date" class="form-control" name="appdate">
                      </div><br><br>

                      <div class="col-md-4"><label>Appointment Time</label></div>
                      <div class="col-md-8">
                        <select name="apptime" class="form-control" required>
                          <option value="" disabled selected>Select Time</option>
                          <option value="08:00:00">8:00 AM</option>
                          <option value="10:00:00">10:00 AM</option>
                          <option value="12:00:00">12:00 PM</option>
                          <option value="14:00:00">2:00 PM</option>
                          <option value="16:00:00">4:00 PM</option>
                        </select>
                      </div><br><br>

                      <div class="col-md-4">
                        <input type="submit" name="app-submit" value="Book Appointment"
                               class="btn btn-primary" id="inputbtn">
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            </div><br>
          </div>
          <!-- END BOOK APPOINTMENT TAB -->


          <!-- APPOINTMENT HISTORY TAB -->
          <div class="tab-pane fade" id="app-hist" role="tabpanel" aria-labelledby="list-pat-list">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Doctor Name</th>
                  <th>Consultancy Fees</th>
                  <th>Appointment Date</th>
                  <th>Appointment Time</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php
                  // Query by pid (from session) — not by name, which could be shared
                  $stmt = mysqli_prepare($con,
                      "SELECT ID,doctor,docFees,appdate,apptime,userStatus,doctorStatus
                       FROM appointmenttb WHERE pid = ?"
                  );
                  mysqli_stmt_bind_param($stmt, "i", $pid);
                  mysqli_stmt_execute($stmt);
                  $result = mysqli_stmt_get_result($stmt);

                  while ($row = mysqli_fetch_array($result)) {
                    $uS = $row['userStatus'];
                    $dS = $row['doctorStatus'];

                    if ($uS == 1 && $dS == 1)      $status = "Active";
                    elseif ($uS == 0 && $dS == 1)  $status = "Cancelled by You";
                    elseif ($uS == 1 && $dS == 0)  $status = "Cancelled by Doctor";
                    else                            $status = "Cancelled";
                ?>
                <tr>
                  <td><?php echo htmlspecialchars($row['doctor'],  ENT_QUOTES,'UTF-8'); ?></td>
                  <td><?php echo htmlspecialchars($row['docFees'], ENT_QUOTES,'UTF-8'); ?></td>
                  <td><?php echo htmlspecialchars($row['appdate'], ENT_QUOTES,'UTF-8'); ?></td>
                  <td><?php echo htmlspecialchars($row['apptime'], ENT_QUOTES,'UTF-8'); ?></td>
                  <td><?php echo htmlspecialchars($status,         ENT_QUOTES,'UTF-8'); ?></td>
                  <td>
                    <?php if ($uS == 1 && $dS == 1): ?>
                      <a href="patient_dashboard.php?ID=<?php echo urlencode($row['ID']); ?>&cancel=update"
                         onclick="return confirm('Are you sure you want to cancel this appointment?')">
                        <button class="btn btn-danger">Cancel</button>
                      </a>
                    <?php else: ?>
                      Cancelled
                    <?php endif; ?>
                  </td>
                </tr>
                <?php } mysqli_stmt_close($stmt); ?>
              </tbody>
            </table>
          </div>
          <!-- END APPOINTMENT HISTORY TAB -->


          <!-- PRESCRIPTIONS TAB -->
          <div class="tab-pane fade" id="list-pres" role="tabpanel" aria-labelledby="list-pres-list">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Doctor Name</th>
                  <th>Appt. ID</th>
                  <th>Date</th>
                  <th>Time</th>
                  <th>Disease</th>
                  <th>Allergies</th>
                  <th>Prescription</th>
                  <th>Bill</th>
                </tr>
              </thead>
              <tbody>
                <?php
                  $stmt = mysqli_prepare($con,
                      "SELECT doctor,ID,appdate,apptime,disease,allergy,prescription
                       FROM prestb WHERE pid = ?"
                  );
                  mysqli_stmt_bind_param($stmt, "i", $pid);
                  mysqli_stmt_execute($stmt);
                  $result = mysqli_stmt_get_result($stmt);

                  while ($row = mysqli_fetch_array($result)) {
                ?>
                <tr>
                  <td><?php echo htmlspecialchars($row['doctor'],       ENT_QUOTES,'UTF-8'); ?></td>
                  <td><?php echo htmlspecialchars($row['ID'],           ENT_QUOTES,'UTF-8'); ?></td>
                  <td><?php echo htmlspecialchars($row['appdate'],      ENT_QUOTES,'UTF-8'); ?></td>
                  <td><?php echo htmlspecialchars($row['apptime'],      ENT_QUOTES,'UTF-8'); ?></td>
                  <td><?php echo htmlspecialchars($row['disease'],      ENT_QUOTES,'UTF-8'); ?></td>
                  <td><?php echo htmlspecialchars($row['allergy'],      ENT_QUOTES,'UTF-8'); ?></td>
                  <td><?php echo htmlspecialchars($row['prescription'], ENT_QUOTES,'UTF-8'); ?></td>
                  <td>
                    <form method="get" action="patient_dashboard.php">
                      <input type="hidden" name="ID" value="<?php echo htmlspecialchars($row['ID'], ENT_QUOTES,'UTF-8'); ?>"/>
                      <input type="submit" name="generate_bill"
                             onclick="alert('Bill Paid Successfully');"
                             class="btn btn-success" value="Pay Bill"/>
                    </form>
                  </td>
                </tr>
                <?php } mysqli_stmt_close($stmt); ?>
              </tbody>
            </table>
          </div>
          <!-- END PRESCRIPTIONS TAB -->

        </div><!-- end tab-content -->
      </div><!-- end col-md-8 -->
    </div><!-- end row -->
  </div><!-- end container-fluid -->

  <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"
          integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"
          integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js"
          integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
</body>
</html>