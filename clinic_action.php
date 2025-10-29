<?php
@session_start();
require_once('dbConnect.php'); // ✅ ใช้ไฟล์ของคุณ

// ป้องกันสิทธิ์เข้าถึง
if (!isset($_SESSION['role']) || $_SESSION['role'] != 3) {
    echo "คุณไม่มีสิทธิ์เข้าถึงหน้านี้ ❌";
    exit();
}

// อ่านค่า action จาก URL
$action = $_GET['action'] ?? '';

switch ($action) {

  /* =====================================================
     ✅ เพิ่มข้อมูลคลินิก
  ===================================================== */
  case 'add':
    $clinic_name = mysqli_real_escape_string($objCon, $_POST['clinic_name']);
    $address = mysqli_real_escape_string($objCon, $_POST['address']);
    $phone = mysqli_real_escape_string($objCon, $_POST['phone']);
    $email = mysqli_real_escape_string($objCon, $_POST['email']);
    $owner_name = mysqli_real_escape_string($objCon, $_POST['owner_name']);

    if (empty($clinic_name)) {
      echo "❌ กรุณากรอกชื่อคลินิก";
      exit();
    }

    $sql = "INSERT INTO clinics (clinic_name, address, phone, email, owner_name)
            VALUES ('$clinic_name', '$address', '$phone', '$email', '$owner_name')";
    if (mysqli_query($objCon, $sql)) {
      echo "✅ เพิ่มข้อมูลคลินิกเรียบร้อยแล้ว";
    } else {
      echo "❌ เกิดข้อผิดพลาด: " . mysqli_error($objCon);
    }
    break;

  /* =====================================================
     ✏️ โหลดฟอร์มแก้ไข (แสดงใน Modal)
  ===================================================== */
  case 'editform':
    $id = (int)$_GET['id'];
    $q = mysqli_query($objCon, "SELECT * FROM clinics WHERE clinic_id=$id");
    $r = mysqli_fetch_assoc($q);

    if (!$r) {
      echo "<div class='alert alert-danger'>❌ ไม่พบข้อมูลคลินิก</div>";
      exit();
    }
?>
  <div class="row g-3">
    <input type="hidden" name="clinic_id" value="<?=$r['clinic_id']?>">
    <div class="col-md-6">
      <label class="form-label">ชื่อคลินิก</label>
      <input type="text" name="clinic_name" class="form-control" value="<?=htmlspecialchars($r['clinic_name'])?>" required>
    </div>
    <div class="col-md-6">
      <label class="form-label">เจ้าของคลินิก</label>
      <input type="text" name="owner_name" class="form-control" value="<?=htmlspecialchars($r['owner_name'])?>">
    </div>
    <div class="col-md-12">
      <label class="form-label">ที่อยู่</label>
      <textarea name="address" class="form-control" rows="2"><?=htmlspecialchars($r['address'])?></textarea>
    </div>
    <div class="col-md-6">
      <label class="form-label">โทรศัพท์</label>
      <input type="text" name="phone" class="form-control" value="<?=htmlspecialchars($r['phone'])?>">
    </div>
    <div class="col-md-6">
      <label class="form-label">อีเมล</label>
      <input type="email" name="email" class="form-control" value="<?=htmlspecialchars($r['email'])?>">
    </div>
  </div>
<?php
    break;

  /* =====================================================
     🔄 อัปเดตข้อมูลคลินิก
  ===================================================== */
  case 'update':
    $id = (int)$_POST['clinic_id'];
    $clinic_name = mysqli_real_escape_string($objCon, $_POST['clinic_name']);
    $address = mysqli_real_escape_string($objCon, $_POST['address']);
    $phone = mysqli_real_escape_string($objCon, $_POST['phone']);
    $email = mysqli_real_escape_string($objCon, $_POST['email']);
    $owner_name = mysqli_real_escape_string($objCon, $_POST['owner_name']);

    if (empty($clinic_name)) {
      echo "❌ ต้องกรอกชื่อคลินิก";
      exit();
    }

    $sql = "UPDATE clinics SET 
            clinic_name='$clinic_name',
            address='$address',
            phone='$phone',
            email='$email',
            owner_name='$owner_name'
            WHERE clinic_id=$id";

    if (mysqli_query($objCon, $sql)) {
      echo "✅ อัปเดตข้อมูลคลินิกเรียบร้อยแล้ว";
    } else {
      echo "❌ ไม่สามารถอัปเดตข้อมูลได้: " . mysqli_error($objCon);
    }
    break;

  /* =====================================================
     🗑️ ลบข้อมูลคลินิก
  ===================================================== */
  case 'delete':
    $id = (int)$_POST['id'];
    if ($id <= 0) {
      echo "❌ รหัสคลินิกไม่ถูกต้อง";
      exit();
    }

    $check = mysqli_query($objCon, "SELECT clinic_id FROM clinics WHERE clinic_id=$id");
    if (mysqli_num_rows($check) == 0) {
      echo "❌ ไม่พบข้อมูลคลินิก";
      exit();
    }

    mysqli_query($objCon, "DELETE FROM clinics WHERE clinic_id=$id");
    echo "🗑️ ลบข้อมูลคลินิกเรียบร้อยแล้ว";
    break;

  /* =====================================================
     ⚠️ กรณีไม่พบ action
  ===================================================== */
  default:
    echo "⚠️ ไม่พบคำสั่งที่ต้องการทำงาน (action)";
    break;
}
?>
