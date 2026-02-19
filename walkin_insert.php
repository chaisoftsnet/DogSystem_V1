<?php
@session_start();
include 'dbconnect.php';

if(!isset($_POST['dog_id'])){
    exit('invalid');
}

$clinic_id = intval($_SESSION['clinic_id']);
$dog_id    = intval($_POST['dog_id']);

// หา user_id จาก dog
$q = mysqli_query($objCon,"
    SELECT user_id FROM dogs WHERE dog_id=$dog_id
");
$r = mysqli_fetch_assoc($q);
$user_id = intval($r['user_id']);

// INSERT visit
mysqli_query($objCon,"
INSERT INTO visits
(clinic_id,dog_id,user_id,visit_date,status)
VALUES
($clinic_id,$dog_id,$user_id,NOW(),'รอตรวจ')
");

echo "OK";
?>