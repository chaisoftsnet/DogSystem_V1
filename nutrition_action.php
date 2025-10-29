<?php
@session_start();
require_once('dbconnect.php');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch($action){

// ✅ เพิ่มข้อมูลโภชนาการ
case 'add':
    $clinic_id = $_POST['clinic_id'];
    $dog_id    = $_POST['dog_id'];
    $food      = mysqli_real_escape_string($objCon, $_POST['food']);
    $allergy   = mysqli_real_escape_string($objCon, $_POST['allergy']);
    $advice    = mysqli_real_escape_string($objCon, $_POST['advice']);

    $sql = "INSERT INTO nutrition (clinic_id, dog_id, food, allergy, advice, created_at)
            VALUES ('$clinic_id', '$dog_id', '$food', '$allergy', '$advice', NOW())";
    $q = mysqli_query($objCon, $sql);
    echo $q ? "✅ เพิ่มข้อมูลสำเร็จ" : "❌ เกิดข้อผิดพลาด: " . mysqli_error($objCon);
break;


// ✏️ โหลดฟอร์มแก้ไข
case 'editform':
    $id = $_GET['id'];
    $q = mysqli_query($objCon,"SELECT * FROM nutrition WHERE nutrition_id='$id'");
    $r = mysqli_fetch_assoc($q);
?>
    <div class="row g-3">
        <input type="hidden" name="nutrition_id" value="<?=$r['nutrition_id']?>">

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

        <div class="col-12"><label>อาหารประจำ</label><textarea name="food" class="form-control"><?=$r['food']?></textarea></div>
        <div class="col-12"><label>แพ้อาหาร</label><textarea name="allergy" class="form-control"><?=$r['allergy']?></textarea></div>
        <div class="col-12"><label>คำแนะนำ</label><textarea name="advice" class="form-control"><?=$r['advice']?></textarea></div>
    </div>
<?php
break;


// 🔁 อัปเดตข้อมูล
case 'update':
    $id        = $_POST['nutrition_id'];
    $clinic_id = $_POST['clinic_id'];
    $dog_id    = $_POST['dog_id'];
    $food      = mysqli_real_escape_string($objCon, $_POST['food']);
    $allergy   = mysqli_real_escape_string($objCon, $_POST['allergy']);
    $advice    = mysqli_real_escape_string($objCon, $_POST['advice']);

    $sql = "UPDATE nutrition SET 
              clinic_id='$clinic_id',
              dog_id='$dog_id',
              food='$food',
              allergy='$allergy',
              advice='$advice'
            WHERE nutrition_id='$id'";
    $q = mysqli_query($objCon, $sql);
    echo $q ? "✅ อัปเดตข้อมูลสำเร็จ" : "❌ เกิดข้อผิดพลาด: " . mysqli_error($objCon);
break;


// ❌ ลบข้อมูล
case 'delete':
    $id = $_POST['id'];
    $sql = "DELETE FROM nutrition WHERE nutrition_id='$id'";
    $q = mysqli_query($objCon, $sql);
    echo $q ? "🗑️ ลบข้อมูลเรียบร้อย" : "❌ ลบไม่สำเร็จ: " . mysqli_error($objCon);
break;

default:
    echo "❗ ไม่มีการกระทำที่ถูกต้อง";
}
?>
