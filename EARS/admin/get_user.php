<?php
	include 'db_connect.php';
		extract($_POST);
		$data=array();
		$get=$conn->query("SELECT * FROM `users` where id=$id") or die(mysqli_error());
		
		if($get->num_rows > 0 ){
			$data = $get->fetch_array();
		}		
		echo json_encode($data);
$conn->close();