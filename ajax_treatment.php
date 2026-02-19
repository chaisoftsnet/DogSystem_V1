<?php
@session_start();
include 'dbconnect.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

/* ===== GET DATA ===== */
if($action=='get'){
  $id = (int)$_GET['id'];
  $q = mysqli_fetch_assoc(mysqli_query($objCon,"
    SELECT * FROM treatments WHERE treatment_id=$id LIMIT 1
  "));
  echo json_encode($q);
  exit;
}

/* ===== DELETE ===== */
if($action=='delete'){
  $id = (int)$_POST['id'];
  mysqli_query($objCon,"
    DELETE FROM treatments WHERE treatment_id=$id
  ");
  echo 'ok';
}
