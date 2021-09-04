<?php
	session_start();
	session_unset('login_id');
	session_destroy();
	header('location:index.php');