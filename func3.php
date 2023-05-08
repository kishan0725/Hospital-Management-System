<?php
session_start();
$con=mysqli_connect("localhost","root","","myhmsdb");
if(isset($_POST['adsub'])){
    $username = $_POST['username1'];
    $password = $_POST['password2'];

    // prepare the query with placeholders for parameters
    $query = "SELECT * FROM admintb WHERE username=? AND password=?";
    $stmt = mysqli_prepare($con, $query);

    // bind parameters to the query
    mysqli_stmt_bind_param($stmt, "ss", $username, $password);

    // execute the query
    mysqli_stmt_execute($stmt);

    // fetch the result
    $result = mysqli_stmt_get_result($stmt);

    if(mysqli_num_rows($result) == 1) {
        $_SESSION['username'] = $username;
        header("Location:admin-panel1.php");
    } else {
        echo("<script>alert('Invalid Username or Password. Try Again!');
          window.location.href = 'index.php';</script>");
    }

    // close the statement
    mysqli_stmt_close($stmt);
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
