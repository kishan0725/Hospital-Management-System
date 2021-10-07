<?php
define('DB_SERVER',getenv('DB_SERVER'));
define('DB_USER',getenv('DB_USER'));
define('DB_PASS' ,getenv('DB_PASS'));
define('DB_NAME', getenv('DB_NAME'));
$con = mysqli_connect(DB_SERVER,DB_USER,DB_PASS,DB_NAME);
// Check connection
if (mysqli_connect_errno())
{
 echo "Failed to connect to MySQL: " . mysqli_connect_error();
}
?>
