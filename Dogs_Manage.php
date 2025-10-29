<?php
include 'dashboardwin.php';

// ==== ลบข้อมูล ==== //
if(isset($_GET['del_id'])){
    $dog_id = intval($_GET['del_id']);
    $sql = "DELETE FROM dogs WHERE dog_id=$dog_id";
    mysqli_query($objCon,$sql);
}

// ==== เพิ่ม / แก้ไข ==== //
if(isset($_POST['save'])){
    $dog_id   = $_POST['dog_id'] ?? null;
    $dog_name = mysqli_real_escape_string($objCon,$_POST['dog_name']);
    $dog_breed= mysqli_real_escape_string($objCon,$_POST['dog_breed']);
    $dog_age  = intval($_POST['dog_age']);
    $dog_weight = intval($_POST['dog_weight']);
    $dog_gender= mysqli_real_escape_string($objCon,$_POST['dog_gender']);
    $dog_medical_history = mysqli_real_escape_string($objCon,$_POST['dog_medical_history']);
    $user_id = 1;   // <== ปรับจาก session
    $clinic_id = 1; // <== ปรับจาก session

    // --- อัปโหลดรูป --- //
    $dog_image_path = null;
    if(!empty($_FILES['dog_image']['name'])){
        $target_dir = "uploads/dogs/";
        if(!is_dir($target_dir)) mkdir($target_dir,0777,true);
        $filename = time()."_".basename($_FILES["dog_image"]["name"]);
        $target_file = $target_dir.$filename;
        if(move_uploaded_file($_FILES["dog_image"]["tmp_name"], $target_file)){
            $dog_image_path = $target_file;
        }
    }

    // --- insert / update --- //
    if($dog_id){ 
        $sql = "UPDATE dogs SET 
                dog_name='$dog_name',
                dog_breed='$dog_breed',
                dog_age=$dog_age,
                dog_weight=$dog_weight,
                dog_gender='$dog_gender',
                dog_medical_history='$dog_medical_history'";

        if($dog_image_path){
            $sql .= ", dog_image_path='$dog_image_path'";
        }

        $sql .= " WHERE dog_id=$dog_id";
    }else{
        $sql = "INSERT INTO dogs(user_id,clinic_id,dog_name,dog_breed,dog_age,dog_weight,dog_gender,dog_medical_history,dog_image_path) 
                VALUES($user_id,$clinic_id,'$dog_name','$dog_breed',$dog_age,$dog_weight,'$dog_gender','$dog_medical_history','$dog_image_path')";
    }
    mysqli_query($objCon,$sql);
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>จัดการข้อมูลสุนัข</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="p-4">

<div class="container">
    <h3 class="mb-4">🐶 จัดการข้อมูลสุนัข</h3>

    <!-- ฟอร์มเพิ่ม / แก้ไข -->
    <form method="post" enctype="multipart/form-data" class="mb-4">
        <input type="hidden" name="dog_id" value="">
        <div class="row g-2">
            <div class="col-md-4">
                <input type="text" name="dog_name" class="form-control" placeholder="ชื่อสุนัข" required>
            </div>
            <div class="col-md-4">
                <input type="text" name="dog_breed" class="form-control" placeholder="สายพันธุ์">
            </div>
            <div class="col-md-2">
                <input type="number" name="dog_age" class="form-control" placeholder="อายุ">
            </div>
            <div class="col-md-2">
                <input type="number" name="dog_weight" class="form-control" placeholder="น้ำหนัก">
            </div>
            <div class="col-md-4">
                <select name="dog_gender" class="form-control">
                    <option value="ผู้">ผู้</option>
                    <option value="เมีย">เมีย</option>
                </select>
            </div>
            <div class="col-md-4">
                <input type="file" name="dog_image" class="form-control">
            </div>
            <div class="col-md-12">
                <textarea name="dog_medical_history" class="form-control" placeholder="ประวัติการรักษา"></textarea>
            </div>
            <div class="col-md-12 mt-2">
                <button type="submit" name="save" class="btn btn-success">💾 บันทึก</button>
            </div>
        </div>
    </form>

    <!-- ตารางแสดง -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>รูป</th>
                <th>ชื่อ</th>
                <th>สายพันธุ์</th>
                <th>อายุ</th>
                <th>น้ำหนัก</th>
                <th>เพศ</th>
                <th>การจัดการ</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $q = mysqli_query($objCon,"SELECT * FROM dogs ORDER BY dog_id DESC");
        while($r=mysqli_fetch_assoc($q)){
            echo "<tr>";
            echo "<td>";
            if($r['dog_image_path']){
                echo "<img src='{$r['dog_image_path']}' width='60'>";
            }
            echo "</td>";
            echo "<td>{$r['dog_name']}</td>";
            echo "<td>{$r['dog_breed']}</td>";
            echo "<td>{$r['dog_age']}</td>";
            echo "<td>{$r['dog_weight']}</td>";
            echo "<td>{$r['dog_gender']}</td>";
            echo "<td>
                    <a href='?edit_id={$r['dog_id']}' class='btn btn-sm btn-warning'>✏️</a>
                    <a href='?del_id={$r['dog_id']}' class='btn btn-sm btn-danger' onclick=\"return confirm('ลบข้อมูลนี้?');\">🗑️</a>
                  </td>";
            echo "</tr>";
        }
        ?>
        </tbody>
    </table>
</div>

</body>
</html>
