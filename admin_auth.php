<?php
session_start();
$con=mysqli_connect("localhost","root","","myhmsdb");
if(isset($_POST['adsub'])){
	$username=$_POST['username1'];
	$password=$_POST['password2'];

  // FIXED: SELECT by username only, then verify the hash separately.
  // FIXED: prepared statement.
  $stmt = mysqli_prepare($con, "SELECT * FROM admintb WHERE username = ?");
  mysqli_stmt_bind_param($stmt, "s", $username);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  $row    = mysqli_fetch_assoc($result);
  mysqli_stmt_close($stmt);

	if($row && password_verify($password, $row['password']))
	{
		$_SESSION['username'] = $row['username'];
		header("Location:receptionist_dashboard.php");
    exit();
	}
	else
		echo("<script>alert('Invalid Username or Password. Try Again!');
          window.location.href = 'register.php';</script>");
}
if(isset($_POST['update_data']))
{
	$contact=$_POST['contact'];
	$status=$_POST['status'];
	$query="update appointmenttb set payment='$status' where contact='$contact';";
	$result=mysqli_query($con,$query);
	if($result)
		header("Location:updated.php");
}




function display_docs()
{
	global $con;
	$query="select * from doctb";
	$result=mysqli_query($con,$query);
	while($row=mysqli_fetch_array($result))
	{
		$name=$row['name'];
		# echo'<option value="" disabled selected>Select Doctor</option>';
		echo '<option value="'.$name.'">'.$name.'</option>';
	}
}

if(isset($_POST['doc_sub']))
{
	$name=$_POST['name'];
	$query="insert into doctb(name)values('$name')";
	$result=mysqli_query($con,$query);
	if($result)
		header("Location:adddoc.php");
}