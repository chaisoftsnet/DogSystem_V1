<?php
@session_start();
include 'dbconnect.php';
$fullname = ret_user_fullname($user_id, $objCon); // ฟังก์ชันใน function.php สำหรับดึงชื่อเต็ม
$dogs = mysqli_query($objCon, "SELECT * FROM dogs WHERE user_id='$user_id' ORDER BY dog_name ASC");

?>
Ok