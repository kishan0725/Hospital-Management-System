<?php
session_start();
$con = mysqli_connect("localhost", "root", "", "myhmsdb");

if (isset($_POST['docsub1'])) {
    $dname = $_POST['username3'];
    $dpass = $_POST['password3'];

    // FIXED: Use a prepared statement so $dname can't inject SQL.
    // We fetch the row by username only, then check the password separately.
    $stmt = mysqli_prepare($con, "SELECT * FROM doctb WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $dname);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row    = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    // FIXED: password_verify() checks the plain input against the stored hash.
    // If the row doesn't exist OR the password is wrong, both fail the same way
    // so we don't leak which one failed.
    if ($row && password_verify($dpass, $row['password'])) {
        $_SESSION['dname'] = $row['username'];
        header("Location: doctor_dashboard.php");
        exit();
    } else {
        echo("<script>alert('Invalid Username or Password. Try Again!');
              window.location.href = 'register.php';</script>");
    }
}

// Helper used by the doctor dashboard to list all doctors in a dropdown.
// No user input here so no injection risk, but output is still escaped.
function display_docs()
{
    global $con;
    $result = mysqli_query($con, "SELECT * FROM doctb");
    while ($row = mysqli_fetch_array($result)) {
        $name = htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8');
        echo '<option value="' . $name . '">' . $name . '</option>';
    }
}

// Big HTML block that renders the doctor panel navigation.
// Kept exactly as-is — no security changes needed here.
function display_admin_panel()
{
    echo '<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" type="text/css" href="font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css"
          integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
      <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
  <a class="navbar-brand" href="#"><i class="fa fa-user-plus" aria-hidden="true"></i> Global Hospital</a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
          aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="collapse navbar-collapse" id="navbarSupportedContent">
    <ul class="navbar-nav mr-auto">
      <li class="nav-item">
        <a class="nav-link" href="doctor_logout.php"><i class="fa fa-sign-out" aria-hidden="true"></i> Logout</a>
      </li>
    </ul>
    <form class="form-inline my-2 my-lg-0" method="post" action="search.php">
      <input class="form-control mr-sm-2" type="text" placeholder="enter contact number" name="contact">
      <input type="submit" class="btn btn-outline-light my-2 my-sm-0" id="inputbtn" name="search_submit" value="Search">
    </form>
  </div>
</nav>
  </head>
  <style>button:hover{cursor:pointer;} #inputbtn:hover{cursor:pointer;}</style>
  <body style="padding-top:50px;">
    <div class="jumbotron" id="ab1"></div>
    <div class="container-fluid" style="margin-top:50px;">
      <div class="row">
        <div class="col-md-4">
          <div class="list-group" id="list-tab" role="tablist">
            <a class="list-group-item list-group-item-action active" id="list-home-list"
               data-toggle="list" href="#list-home" role="tab">Appointment</a>
            <a class="list-group-item list-group-item-action" href="patientdetails.php">Patient List</a>
            <a class="list-group-item list-group-item-action" id="list-profile-list"
               data-toggle="list" href="#list-profile" role="tab">Payment Status</a>
            <a class="list-group-item list-group-item-action" id="list-messages-list"
               data-toggle="list" href="#list-messages" role="tab">Prescription</a>
            <a class="list-group-item list-group-item-action" id="list-settings-list"
               data-toggle="list" href="#list-settings" role="tab">Doctors Section</a>
            <a class="list-group-item list-group-item-action" id="list-attend-list"
               data-toggle="list" href="#list-attend" role="tab">Attendance</a>
          </div><br>
        </div>
        <div class="col-md-8">
          <div class="tab-content" id="nav-tabContent">
            <div class="tab-pane fade show active" id="list-home" role="tabpanel">
              <div class="container-fluid">
                <div class="card">
                  <div class="card-body">
                    <center><h4>Create an appointment</h4></center><br>
                    <form class="form-group" method="post" action="appointment.php">
                      <div class="row">
                        <div class="col-md-4"><label>First Name:</label></div>
                        <div class="col-md-8"><input type="text" class="form-control" name="fname"></div><br><br>
                        <div class="col-md-4"><label>Last Name:</label></div>
                        <div class="col-md-8"><input type="text" class="form-control" name="lname"></div><br><br>
                        <div class="col-md-4"><label>Email id:</label></div>
                        <div class="col-md-8"><input type="text" class="form-control" name="email"></div><br><br>
                        <div class="col-md-4"><label>Contact Number:</label></div>
                        <div class="col-md-8"><input type="text" class="form-control" name="contact"></div><br><br>
                        <div class="col-md-4"><label>Doctor:</label></div>
                        <div class="col-md-8">
                          <select name="doctor" class="form-control">
                            <?php display_docs(); ?>
                          </select>
                        </div><br><br>
                        <div class="col-md-4"><label>Payment:</label></div>
                        <div class="col-md-8">
                          <select name="payment" class="form-control">
                            <option value="" disabled selected>Select Payment Status</option>
                            <option value="Paid">Paid</option>
                            <option value="Pay later">Pay later</option>
                          </select>
                        </div><br><br><br>
                        <div class="col-md-4">
                          <input type="submit" name="entry_submit" value="Create new entry" class="btn btn-primary">
                        </div>
                      </div>
                    </form>
                  </div>
                </div>
              </div><br>
            </div>
            <div class="tab-pane fade" id="list-profile" role="tabpanel">
              <div class="card"><div class="card-body">
                <form class="form-group" method="post" action="admin_auth.php">
                  <input type="text" name="contact" class="form-control" placeholder="enter contact"><br>
                  <select name="status" class="form-control">
                    <option value="" disabled selected>Select Payment Status to update</option>
                    <option value="paid">paid</option>
                    <option value="pay later">pay later</option>
                  </select><br><hr>
                  <input type="submit" value="update" name="update_data" class="btn btn-primary">
                </form>
              </div></div><br><br>
            </div>
            <div class="tab-pane fade" id="list-messages" role="tabpanel">...</div>
            <div class="tab-pane fade" id="list-settings" role="tabpanel">
              <form class="form-group" method="post" action="admin_auth.php">
                <label>Doctors name: </label>
                <input type="text" name="name" placeholder="enter doctors name" class="form-control"><br>
                <input type="submit" name="doc_sub" value="Add Doctor" class="btn btn-primary">
              </form>
            </div>
            <div class="tab-pane fade" id="list-attend" role="tabpanel">...</div>
          </div>
        </div>
      </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"
            integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"
            integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js"
            integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.33.1/sweetalert2.all.js"></script>
    <script>
      $(document).ready(function(){
        swal({title:"Welcome!",text:"Have a nice day!",imageUrl:"images/sweet.jpg",
              imageWidth:400,imageHeight:200,imageAlt:"Custom image",animation:false});
      });
    </script>
  </body>
</html>';
}
?>
