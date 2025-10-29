<?php
@session_start();
require_once('dbconnect.php');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch($action){

// ✅ เพิ่มข้อมูลการผ่าตัด
case 'add':
    $clinic_id    = $_POST['clinic_id'];
    $dog_id       = $_POST['dog_id'];
    $surgery_date = $_POST['surgery_date'];
    $surgery_type = $_POST['surgery_type'];
    $description  = $_POST['description'];
    $doctor_name  = $_POST['doctor_name'];
    $outcome      = $_POST['outcome'];
    $notes        = $_POST['notes'];

    $file_path = '';
    if (!empty($_FILES['file_path']['name'])) {
        $dir = "uploads/surgeries/";
        if(!file_exists($dir)) mkdir($dir, 0777, true);
        $file_path = $dir . time() . "_" . basename($_FILES['file_path']['name']);
        move_uploaded_file($_FILES['file_path']['tmp_name'], $file_path);
    }

    $sql = "INSERT INTO surgeries (clinic_id, dog_id, surgery_date, surgery_type, description, doctor_name, outcome, notes, created_at)
            VALUES ('$clinic_id','$dog_id','$surgery_date','$surgery_type','$description','$doctor_name','$outcome','$notes',NOW())";
    $q = mysqli_query($objCon, $sql);
    echo $q ? "✅ เพิ่มข้อมูลสำเร็จ" : "❌ เกิดข้อผิดพลาด: ".mysqli_error($objCon);
break;


// ✏️ โหลดฟอร์มแก้ไข
case 'editform':
    $id = $_GET['id'];
    $q = mysqli_query($objCon,"SELECT * FROM surgeries WHERE surgery_id='$id'");
    $r = mysqli_fetch_assoc($q);
?>
    <div class="row g-3">
        <input type="hidden" name="surgery_id" value="<?=$r['surgery_id']?>">
        <div class="col-md-6">
          <label>ชื่อสุนัข</label>
          <select name="dog_id" class="form-select" required>
            <option value="">-- เลือกสุนัข --</option>
            <?php
              $dogs = mysqli_query($objCon, "SELECT dog_id, dog_name FROM dogs ORDER BY dog_name");
              while($d = mysqli_fetch_assoc($dogs)){
                $sel = ($d['dog_id']==$r['dog_id']) ? "selected" : "";
                echo "<option value='{$d['dog_id']}' $sel>{$d['dog_name']}</option>";
              }
            ?>
          </select>
        </div>

        <div class="col-md-6">
          <label>คลินิก</label>
          <select name="clinic_id" class="form-select" required>
            <option value="">-- เลือกคลินิก --</option>
            <?php
              $cl = mysqli_query($objCon, "SELECT clinic_id, clinic_name FROM clinics ORDER BY clinic_name");
              while($c = mysqli_fetch_assoc($cl)){
                $sel = ($c['clinic_id']==$r['clinic_id']) ? "selected" : "";
                echo "<option value='{$c['clinic_id']}' $sel>{$c['clinic_name']}</option>";
              }
            ?>
          </select>
        </div>

        <div class="col-md-6"><label>วันที่ผ่าตัด</label><input type="date" name="surgery_date" class="form-control" value="<?=$r['surgery_date']?>"></div>
        <div class="col-md-6"><label>ประเภทผ่าตัด</label><input type="text" name="surgery_type" class="form-control" value="<?=$r['surgery_type']?>"></div>
        <div class="col-md-6"><label>สัตวแพทย์</label><input type="text" name="doctor_name" class="form-control" value="<?=$r['doctor_name']?>"></div>

        <div class="col-md-6">
          <label>ไฟล์แนบผลผ่าตัด (ถ้ามี)</label>
          <input type="file" name="file_path" class="form-control" accept="image/*,application/pdf">
          <?php if(!empty($r['file_path'])): ?>
            <a href="<?=$r['file_path']?>" target="_blank">ดูไฟล์เดิม</a>
          <?php endif; ?>
        </div>

        <div class="col-md-12"><label>รายละเอียด</label><textarea name="description" class="form-control"><?=$r['description']?></textarea></div>
        <div class="col-md-12"><label>ผลลัพธ์</label><textarea name="outcome" class="form-control"><?=$r['outcome']?></textarea></div>
        <div class="col-md-12"><label>หมายเหตุ</label><textarea name="notes" class="form-control"><?=$r['notes']?></textarea></div>
    </div>
<?php
break;


// 🔁 อัปเดตข้อมูล
case 'update':
    $id           = $_POST['surgery_id'];
    $clinic_id    = $_POST['clinic_id'];
    $dog_id       = $_POST['dog_id'];
    $surgery_date = $_POST['surgery_date'];
    $surgery_type = $_POST['surgery_type'];
    $description  = $_POST['description'];
    $doctor_name  = $_POST['doctor_name'];
    $outcome      = $_POST['outcome'];
    $notes        = $_POST['notes'];

    $file_part = "";
    if (!empty($_FILES['file_path']['name'])) {
        $dir = "uploads/surgeries/";
        if(!file_exists($dir)) mkdir($dir, 0777, true);
        $file_path = $dir . time() . "_" . basename($_FILES['file_path']['name']);
        move_uploaded_file($_FILES['file_path']['tmp_name'], $file_path);
        $file_part = ", file_path='$file_path'";
    }

    $sql = "UPDATE surgeries SET
              clinic_id='$clinic_id',
              dog_id='$dog_id',
              surgery_date='$surgery_date',
              surgery_type='$surgery_type',
              description='$description',
              doctor_name='$doctor_name',
              outcome='$outcome',
              notes='$notes'
              $file_part
            WHERE surgery_id='$id'";
    $q = mysqli_query($objCon, $sql);
    echo $q ? "✅ อัปเดตข้อมูลสำเร็จ" : "❌ เกิดข้อผิดพลาด: ".mysqli_error($objCon);
break;


// ❌ ลบข้อมูล
case 'delete':
    $id = $_POST['id'];
    $sql = "DELETE FROM surgeries WHERE surgery_id='$id'";
    $q = mysqli_query($objCon, $sql);
    echo $q ? "🗑️ ลบข้อมูลเรียบร้อย" : "❌ ลบไม่สำเร็จ: ".mysqli_error($objCon);
break;

default:
    echo "❗ ไม่มีการกระทำที่ถูกต้อง";
}
?>
