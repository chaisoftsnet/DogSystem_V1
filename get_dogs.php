<?php
include "dbconnect.php";
$clinic_id = $_GET['clinic_id'] ?? '';
$result = [];

if($clinic_id != ""){
    $query = mysqli_query($objCon, "SELECT dog_id, dog_name FROM dogs WHERE clinic_id='".mysqli_real_escape_string($objCon,$clinic_id)."' ORDER BY dog_name ASC");
    while($row = mysqli_fetch_assoc($query)){
        $result[] = $row;
    }
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($result);
