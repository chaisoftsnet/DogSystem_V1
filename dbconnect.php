<?php
$objCon=mysqli_connect("localhost","root","11111111","dogsys");
if (!$objCon) {
	die("Connection failed: " . mysqli_connect_error());
}
mysqli_set_charset($objCon,"utf8");
?>