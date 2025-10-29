<?php
@session_start();

include 'dbconnect.php';
include 'function.php';
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {

  // 🐶 1. เพิ่มข้อมูลสุนัข ()
  case 'add':
    $user_id   = $_SESSION['user_id'];
    $clinic_id = $_POST['clinic_id']; //ADMIN
    $dog_name  = mysqli_real_escape_string($objCon, $_POST['dog_name']);
    $dog_breed = mysqli_real_escape_string($objCon, $_POST['dog_breed']);
    $dog_age   = (int)$_POST['dog_age'];
    $dog_weight= (int)$_POST['dog_weight'];
    $dog_gender= mysqli_real_escape_string($objCon, $_POST['dog_gender']);
    $dog_medical_history = mysqli_real_escape_string($objCon, $_POST['dog_medical_history']);
    $rfid_tag  = mysqli_real_escape_string($objCon, $_POST['rfid_tag']);

    $dog_image_path = '';
    $xray_image_path = '';

    // 📸 รูปสุนัข
    if (!empty($_FILES['dog_image']['name'])) {
      $ext = pathinfo($_FILES['dog_image']['name'], PATHINFO_EXTENSION);
      $newName = uniqid('dog_') . '.' . $ext;
      $targetDir = "uploads/dogs/";
      if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
      move_uploaded_file($_FILES['dog_image']['tmp_name'], $targetDir . $newName);
      $dog_image_path = $targetDir . $newName;
    }

    // 📸 รูป X-ray
    if (!empty($_FILES['xray_image']['name'])) {
      $ext2 = pathinfo($_FILES['xray_image']['name'], PATHINFO_EXTENSION);
      $newName2 = uniqid('xray_') . '.' . $ext2;
      $targetDir = "uploads/xrays/";
      if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
      move_uploaded_file($_FILES['xray_image']['tmp_name'], $targetDir . $newName2);
      $xray_image_path = $targetDir . $newName2;
    }

    $sql = "INSERT INTO dogs 
            (user_id, clinic_id, dog_name, dog_breed, dog_age, dog_weight, dog_gender, 
             dog_medical_history, dog_image_path, xray_image_path, rfid_tag)
            VALUES 
            ('$user_id', '$clinic_id', '$dog_name', '$dog_breed', '$dog_age', '$dog_weight', 
             '$dog_gender', '$dog_medical_history', '$dog_image_path', '$xray_image_path', '$rfid_tag')";
    $q = mysqli_query($objCon, $sql);
    echo $q ? "✅ เพิ่มข้อมูลสุนัขเรียบร้อยแล้ว" : "❌ เกิดข้อผิดพลาดในการเพิ่มข้อมูล";
    break;

  // ✏️ 2. ฟอร์มแก้ไข
  case 'editform':
    $dog_id = (int)$_GET['id'];
    $sql = "SELECT * FROM dogs WHERE dog_id=$dog_id";
    $q = mysqli_query($objCon, $sql);
    $r = mysqli_fetch_assoc($q);
    $clinic_id=$r['clinic_id'];
    ?>
    <div class="row g-3">
      <input type="hidden" name="dog_id" value="<?=$r['dog_id']?>">
    <?php if($_SESSION['role']==3){ ?>
        <div class="col-md-6">
        <label class="form-label">เลือกคลินิก</label>
        <select name="clinic_id" class="form-select" required>
            <option value="">-- เลือกคลินิก --</option>
            <?php opt_clinic($clinic_id, $objCon); ?>
        </select>
        </div>
    <?php } else { ?>
        <input type="hidden" name="clinic_id" value="<?= $_SESSION['clinic_id'] ?>">
    <?php } ?>      

      <div class="col-md-6">
        <label>ชื่อสุนัข</label>
        <input type="text" name="dog_name" value="<?=$r['dog_name']?>" class="form-control" required>
      </div>
      <div class="col-md-6">
        <label>สายพันธุ์</label>
        <input type="text" name="dog_breed" value="<?=$r['dog_breed']?>" class="form-control">
      </div>
      <div class="col-md-6">
        <label>เพศ</label>
        <select name="dog_gender" class="form-select">
          <option value="ผู้" <?=$r['dog_gender']=='ผู้'?'selected':''?>>ผู้</option>
          <option value="เมีย" <?=$r['dog_gender']=='เมีย'?'selected':''?>>เมีย</option>
        </select>
      </div>
      <div class="col-md-6">
        <label>อายุ (ปี)</label>
        <input type="number" name="dog_age" value="<?=$r['dog_age']?>" class="form-control">
      </div>
      <div class="col-md-6">
        <label>น้ำหนัก (กก.)</label>
        <input type="number" name="dog_weight" value="<?=$r['dog_weight']?>" class="form-control">
      </div>
      <div class="col-md-6">
        <label>RFID Tag</label>
        <input type="text" name="rfid_tag" value="<?=$r['rfid_tag']?>" class="form-control">
      </div>
      <div class="col-12">
        <label>ประวัติการรักษา</label>
        <textarea name="dog_medical_history" class="form-control"><?=$r['dog_medical_history']?></textarea>
      </div>

      <div class="col-6">
        <label>รูปสุนัข</label>
        <input type="file" name="dog_image" class="form-control">
        <?php if($r['dog_image_path']): ?>
          <img src="<?=$r['dog_image_path']?>" width="100" class="mt-2 rounded shadow">
          <input type="hidden" name="old_dog_image" value="<?=$r['dog_image_path']?>">
        <?php endif; ?>
      </div>

      <div class="col-6">
        <label>รูป X-Ray</label>
        <input type="file" name="xray_image" class="form-control">
        <?php if($r['xray_image_path']): ?>
          <img src="<?=$r['xray_image_path']?>" width="100" class="mt-2 rounded shadow">
          <input type="hidden" name="old_xray_image" value="<?=$r['xray_image_path']?>">
        <?php endif; ?>
      </div>
    </div>
<?php
    break;

  // 🔄 3. อัปเดตข้อมูล
  case 'update':
    $dog_id = (int)$_POST['dog_id'];
    $dog_name = mysqli_real_escape_string($objCon, $_POST['dog_name']);
    $dog_breed = mysqli_real_escape_string($objCon, $_POST['dog_breed']);
    $dog_gender = mysqli_real_escape_string($objCon, $_POST['dog_gender']);
    $dog_age = (int)$_POST['dog_age'];
    $dog_weight = (int)$_POST['dog_weight'];
    $dog_medical_history = mysqli_real_escape_string($objCon, $_POST['dog_medical_history']);
    $rfid_tag = mysqli_real_escape_string($objCon, $_POST['rfid_tag']);

    $dog_image_path = $_POST['old_dog_image'] ?? '';
    $xray_image_path = $_POST['old_xray_image'] ?? '';

    // 📸 อัปโหลดรูปสุนัขใหม่
    if (!empty($_FILES['dog_image']['name'])) {
      $ext = pathinfo($_FILES['dog_image']['name'], PATHINFO_EXTENSION);
      $newName = uniqid('dog_') . '.' . $ext;
      $targetDir = "uploads/dogs/";
      if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
      move_uploaded_file($_FILES['dog_image']['tmp_name'], $targetDir . $newName);
      $dog_image_path = $targetDir . $newName;
    }

    // 📸 อัปโหลดรูป X-ray ใหม่
    if (!empty($_FILES['xray_image']['name'])) {
      $ext2 = pathinfo($_FILES['xray_image']['name'], PATHINFO_EXTENSION);
      $newName2 = uniqid('xray_') . '.' . $ext2;
      $targetDir = "uploads/xrays/";
      if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
      move_uploaded_file($_FILES['xray_image']['tmp_name'], $targetDir . $newName2);
      $xray_image_path = $targetDir . $newName2;
    }

    $sql = "UPDATE dogs 
            SET dog_name='$dog_name', dog_breed='$dog_breed', dog_age='$dog_age', dog_weight='$dog_weight', 
                dog_gender='$dog_gender', dog_medical_history='$dog_medical_history', 
                dog_image_path='$dog_image_path', xray_image_path='$xray_image_path', rfid_tag='$rfid_tag'
            WHERE dog_id='$dog_id'";
    $q = mysqli_query($objCon, $sql);
    echo $q ? "✅ อัปเดตข้อมูลเรียบร้อย" : "❌ เกิดข้อผิดพลาดในการอัปเดตข้อมูล";
    break;

  // 🗑️ 4. ลบข้อมูล
  case 'delete':
    $id = (int)$_POST['id'];
    $sql = "DELETE FROM dogs WHERE dog_id=$id";
    $q = mysqli_query($objCon, $sql);
    echo $q ? "🗑️ ลบข้อมูลเรียบร้อย" : "❌ ไม่สามารถลบข้อมูลได้";
    break;

  default:
    echo "❌ ไม่พบ action ที่ร้องขอ";
}
?>
