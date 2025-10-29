<?php
@session_start();
require_once('dbconnect.php');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch($action){

// ✅ เพิ่มข้อมูลใหม่
case 'add':
    $clinic_id  = $_POST['clinic_id'];
    $dog_id     = $_POST['dog_id'];
    $start_date = $_POST['start_date'];
    $end_date   = $_POST['end_date'];
    $symptoms   = mysqli_real_escape_string($objCon, $_POST['symptoms']);
    $care       = mysqli_real_escape_string($objCon, $_POST['care']);

    $sql = "INSERT INTO boarding (clinic_id, dog_id, start_date, end_date, symptoms, care, created_at)
            VALUES ('$clinic_id', '$dog_id', '$start_date', '$end_date', '$symptoms', '$care', NOW())";
    $q = mysqli_query($objCon, $sql);
    echo $q ? "✅ เพิ่มข้อมูลเรียบร้อย" : "❌ เกิดข้อผิดพลาด: " . mysqli_error($objCon);
break;


// ✏️ โหลดฟอร์มแก้ไข
case 'editform':
    $id = $_GET['id'];
    $q = mysqli_query($objCon,"SELECT * FROM boarding WHERE boarding_id='$id'");
    $r = mysqli_fetch_assoc($q);
?>
    <div class="row g-3">
        <input type="hidden" name="boarding_id" value="<?=$r['boarding_id']?>">

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

        <div class="col-md-6"><label>วันที่ฝาก</label><input type="date" name="start_date" value="<?=$r['start_date']?>" class="form-control" required></div>
        <div class="col-md-6"><label>วันที่รับกลับ</label><input type="date" name="end_date" value="<?=$r['end_date']?>" class="form-control" required></div>
        <div class="col-12"><label>อาการตอนฝาก</label><textarea name="symptoms" class="form-control"><?=$r['symptoms']?></textarea></div>
        <div class="col-12"><label>การดูแลระหว่างฝาก</label><textarea name="care" class="form-control"><?=$r['care']?></textarea></div>
    </div>
<?php
break;


// 🔁 อัปเดตข้อมูล
case 'update':
    $id         = $_POST['boarding_id'];
    $clinic_id  = $_POST['clinic_id'];
    $dog_id     = $_POST['dog_id'];
    $start_date = $_POST['start_date'];
    $end_date   = $_POST['end_date'];
    $symptoms   = mysqli_real_escape_string($objCon, $_POST['symptoms']);
    $care       = mysqli_real_escape_string($objCon, $_POST['care']);

    $sql = "UPDATE boarding SET 
              clinic_id='$clinic_id',
              dog_id='$dog_id',
              start_date='$start_date',
              end_date='$end_date',
              symptoms='$symptoms',
              care='$care'
            WHERE boarding_id='$id'";
    $q = mysqli_query($objCon, $sql);
    echo $q ? "✅ อัปเดตข้อมูลเรียบร้อย" : "❌ เกิดข้อผิดพลาด: " . mysqli_error($objCon);
break;


// ❌ ลบข้อมูล
case 'delete':
    $id = $_POST['id'];
    $sql = "DELETE FROM boarding WHERE boarding_id='$id'";
    $q = mysqli_query($objCon, $sql);
    echo $q ? "🗑️ ลบข้อมูลสำเร็จ" : "❌ ลบไม่สำเร็จ: " . mysqli_error($objCon);
break;
default:
    echo "❗ ไม่มีการกระทำที่ถูกต้อง";
}
?>
