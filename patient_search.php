<!DOCTYPE html>
<?php
// Check session before antyhing else

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

// Only logged-in receptionist/admin may reach this page.
// admin_auth.php stores $_SESSION['username'] on successful login.
if (empty($_SESSION['username'])) {
    header('Location: register.php');
    exit();
}
?>
<html>
<head>
    <title>Patient Details</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css"
          integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M"
          crossorigin="anonymous">
</head>
<body>
<?php
if (isset($_POST['patient_search_submit'])) {

    $con = mysqli_connect("localhost", "root", "", "myhmsdb");
    if (!$con) {
        die("A server error occurred. Please try again later.");
    }

    
    $contact = $_POST['patient_contact'];
  // Prepared statement —less vulerable to sql injections
    $stmt = mysqli_prepare($con, "SELECT * FROM patreg WHERE contact = ?");
    if (!$stmt) {
        die("Query preparation failed.");
    }
    mysqli_stmt_bind_param($stmt, "s", $contact);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row    = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$row || ($row['lname'] === '' && $row['email'] === '' && $row['contact'] === '')) {
        echo "<script>
                alert('No entries found! Please enter valid details');
                window.location.href = 'receptionist_dashboard.php#list-doc';
              </script>";
    } else {
        // Output escaping — every value through htmlspecialchars
        $fname   = htmlspecialchars($row['fname'],   ENT_QUOTES, 'UTF-8');
        $lname   = htmlspecialchars($row['lname'],   ENT_QUOTES, 'UTF-8');
        $email   = htmlspecialchars($row['email'],   ENT_QUOTES, 'UTF-8');
        $contact = htmlspecialchars($row['contact'], ENT_QUOTES, 'UTF-8');

        echo "
        <div class='container-fluid' style='margin-top:50px;'>
          <div class='card'>
            <div class='card-body' style='background-color:#342ac1;color:#ffffff;'>
              <table class='table table-hover'>
                <thead>
                  <tr>
                    <th scope='col'>First Name</th>
                    <th scope='col'>Last Name</th>
                    <th scope='col'>Email</th>
                    <th scope='col'>Contact</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>{$fname}</td>
                    <td>{$lname}</td>
                    <td>{$email}</td>
                    <td>{$contact}</td>
                  </tr>
                </tbody>
              </table>
              <center>
                <a href='receptionist_dashboard.php' class='btn btn-light'>Back to dashboard</a>
              </center>
            </div>
          </div>
        </div>";
    }

    mysqli_close($con);
}
?>
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"
        integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN"
        crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"
        integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4"
        crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js"
        integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1"
        crossorigin="anonymous"></script>
</body>
</html>