<?php
include 'dbconnect.php';

$dog_id = intval($_POST['dog_id']);

$q = mysqli_query($objCon,
  "SELECT dog_image_path FROM dogs WHERE dog_id=$dog_id"
);
$dog = mysqli_fetch_assoc($q);

if(!empty($_FILES['dog_image']['name'])){

  if(!empty($dog['dog_image_path'])){
    @unlink('uploads/dogs/'.$dog['dog_image_path']);
  }

  $ext = pathinfo($_FILES['dog_image']['name'], PATHINFO_EXTENSION);
  $newName = 'dog_'.$dog_id.'_'.time().'.'.$ext;

  move_uploaded_file(
    $_FILES['dog_image']['tmp_name'],
    'uploads/dogs/'.$newName
  );

  mysqli_query($objCon,
    "UPDATE dogs SET dog_image_path='$newName' WHERE dog_id=$dog_id"
  );

  echo json_encode(['status'=>'success']);
  exit;
}

echo json_encode(['status'=>'error']);
