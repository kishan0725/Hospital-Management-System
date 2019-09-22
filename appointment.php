<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
  </head>
  <body style="background-color:#3498DB;color:white;padding-top:100px;text-align:center;">
    <h3>Your appointment has been booked.</h3><br><br>
    <a href="admin-panel.php" class="btn btn-outline-light">Return to your page</a>
    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
  </body>
</html>
<?php 
include('func2.php');
  

if(isset($_POST['app-submit']))
{
  $username = $_SESSION['username'];
  $email = $_SESSION['email'];
  $fname = $_SESSION['fname'];
  $lname = $_SESSION['lname'];
  $contact = $_SESSION['contact'];
  $doctor=$_SESSION['doctor'];
  $appdate=$_SESSION['appdate'];
  $apptime=$_SESSION['apptime'];

  $fname=$_POST['fname'];
  $lname=$_POST['lname'];
  $email=$_POST['email'];
  $contact=$_POST['contact'];
  $doctor=$_POST['doctor'];
 
  # $fees=$_POST['fees'];
  $appdate=$_POST['appdate'];
  $apptime=$_POST['apptime'];



  $result=mysqli_query($con,"insert into appointmenttb(fname,lname,email,contact,doctor,appdate,apptime) values('$fname','$lname','$email','$contact','$doctor','$appdate','$apptime')");
  if($result)
    header("Location:appointment.php");
}
?>