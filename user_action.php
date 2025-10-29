<?php
@session_start();
require_once('dbConnect.php');
require_once('function.php');

$action = $_GET['action'] ?? '';

switch($action) {

  // 🔹 เพิ่มข้อมูลผู้ใช้
  case 'add':
    $username = trim($_POST['username']);
    $password = md5(trim($_POST['password']));
    $fullname = trim($_POST['fullname']);
    $clinic_id = intval($_POST['clinic_id']);
    $role = intval($_POST['role']);
    $email = trim($_POST['email']);
    $tel = trim($_POST['tel']);
    $id_card = trim($_POST['id_card']);
    $line_id = trim($_POST['line_id']);
    $address = trim($_POST['address']);
    $created_at = date("Y-m-d H:i:s");

    $chk = mysqli_query($objCon, "SELECT * FROM user WHERE username='$username'");
    if (mysqli_num_rows($chk) > 0) {
      echo "❌ มีชื่อผู้ใช้นี้อยู่แล้ว!";
      exit();
    }

    $sql = "INSERT INTO user (username, password, fullname, clinic_id, role, email, tel, address, id_card, line_id, created_at)
            VALUES ('$username', '$password', '$fullname', '$clinic_id', '$role', '$email', '$tel', '$address', '$id_card', '$line_id', '$created_at')";
    if (mysqli_query($objCon, $sql)) {
      echo "✅ เพิ่มผู้ใช้งานสำเร็จ!";
    } else {
      echo "❌ เกิดข้อผิดพลาด: " . mysqli_error($objCon);
    }
    break;

  // 🔹 โหลดข้อมูลฟอร์มแก้ไข
  case 'editform':
    $id = intval($_GET['id']);
    $sql = "SELECT * FROM user WHERE id=$id";
    $q = mysqli_query($objCon, $sql);
    $r = mysqli_fetch_assoc($q);
    ?>
    <div class="row g-3">
      <input type="hidden" name="id" value="<?=$r['id']?>">
      <div class="col-md-6"><label>ชื่อผู้ใช้</label><input type="text" name="username" class="form-control" value="<?=$r['username']?>" required></div>
      <div class="col-md-6"><label>รหัสผ่านใหม่ (ถ้ามี)</label><input type="password" name="password" class="form-control" placeholder="เว้นว่างหากไม่เปลี่ยน"></div>
      <div class="col-md-6"><label>ชื่อ-สกุล</label><input type="text" name="fullname" class="form-control" value="<?=$r['fullname']?>" required></div>
      <div class="col-md-6"><label>อีเมล</label><input type="email" name="email" class="form-control" value="<?=$r['email']?>"></div>
      <div class="col-md-6"><label>เบอร์โทร</label><input type="text" name="tel" class="form-control" value="<?=$r['tel']?>"></div>
      <div class="col-md-6"><label>เลขบัตรประชาชน</label><input type="text" name="id_card" class="form-control" value="<?=$r['id_card']?>"></div>
      <div class="col-md-6"><label>LINE ID</label><input type="text" name="line_id" class="form-control" value="<?=$r['line_id']?>"></div>
      <div class="col-md-6">
        <label>สิทธิ์ผู้ใช้งาน</label>
        <select name="role" class="form-select">
          <option value="1" <?=($r['role']==1)?'selected':''?>>ลูกค้า</option>
          <option value="2" <?=($r['role']==2)?'selected':''?>>เจ้าหน้าที่คลินิก</option>
          <option value="3" <?=($r['role']==3)?'selected':''?>>ผู้ดูแลระบบ</option>
        </select>
      </div>
      <div class="col-md-6">
        <label>เลือกคลินิก</label>
        <select name="clinic_id" class="form-select">
          <option value="">-- เลือกคลินิก --</option>
          <?php
          $cq = mysqli_query($objCon,"SELECT * FROM clinics ORDER BY clinic_name");
          while($c = mysqli_fetch_assoc($cq)){
            $sel = ($c['clinic_id'] == $r['clinic_id']) ? "selected" : "";
            echo "<option value='{$c['clinic_id']}' $sel>{$c['clinic_name']}</option>";
          }
          ?>
        </select>
      </div>
      <div class="col-12"><label>ที่อยู่</label><textarea name="address" class="form-control" rows="2"><?=$r['address']?></textarea></div>
    </div>
    <?php
    break;

  // 🔹 อัปเดตข้อมูล
  case 'update':
    $id = intval($_POST['id']);
    $username = trim($_POST['username']);
    $fullname = trim($_POST['fullname']);
    $clinic_id = intval($_POST['clinic_id']);
    $role = intval($_POST['role']);
    $email = trim($_POST['email']);
    $tel = trim($_POST['tel']);
    $id_card = trim($_POST['id_card']);
    $line_id = trim($_POST['line_id']);
    $address = trim($_POST['address']);
    $password = trim($_POST['password']);

    if($password != ''){
      $password = md5($password);
      $pw_sql = ", password='$password'";
    } else {
      $pw_sql = "";
    }

    $sql = "UPDATE user SET
              username='$username',
              fullname='$fullname',
              clinic_id='$clinic_id',
              role='$role',
              email='$email',
              tel='$tel',
              address='$address',
              id_card='$id_card',
              line_id='$line_id'
              $pw_sql
            WHERE id=$id";
    if (mysqli_query($objCon, $sql)) {
      echo "✅ อัปเดตข้อมูลสำเร็จ!";
    } else {
      echo "❌ เกิดข้อผิดพลาด: " . mysqli_error($objCon);
    }
    break;

  // 🔹 ลบข้อมูล
  case 'delete':
    $id = intval($_POST['id']);
    if (mysqli_query($objCon, "DELETE FROM user WHERE id=$id")) {
      echo "✅ ลบข้อมูลสำเร็จ!";
    } else {
      echo "❌ เกิดข้อผิดพลาด: " . mysqli_error($objCon);
    }
    break;

  default:
    echo "❌ ไม่พบคำสั่งที่ร้องขอ";
}
?>
