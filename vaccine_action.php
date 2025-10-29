<?php
@session_start();
require_once('dbconnect.php');

// ตรวจสอบว่าเป็นการเรียกผ่าน AJAX หรือไม่
$action = $_GET['action'] ?? '';

if ($action == 'add') {
    // ➕ เพิ่มข้อมูลวัคซีนใหม่
    $dog_id = mysqli_real_escape_string($objCon, $_POST['dog_id']);
    $clinic_id = mysqli_real_escape_string($objCon, $_POST['clinic_id']);
    $vaccine_name = mysqli_real_escape_string($objCon, $_POST['vaccine_name']);
    $vaccine_type = mysqli_real_escape_string($objCon, $_POST['vaccine_type']);
    $vaccine_date = mysqli_real_escape_string($objCon, $_POST['vaccine_date']);
    $next_due_date = mysqli_real_escape_string($objCon, $_POST['next_due_date']);
    $doctor_name = mysqli_real_escape_string($objCon, $_POST['doctor_name']);
    $note = mysqli_real_escape_string($objCon, $_POST['note']);

    $sql = "INSERT INTO vaccinations (dog_id, clinic_id, vaccine_name, vaccine_type, vaccine_date, next_due_date, doctor_name, note)
            VALUES ('$dog_id', '$clinic_id', '$vaccine_name', '$vaccine_type', '$vaccine_date', 
                    " . ($next_due_date ? "'$next_due_date'" : "NULL") . ", '$doctor_name', '$note')";

    if (mysqli_query($objCon, $sql)) {
        echo "✅ เพิ่มข้อมูลวัคซีนเรียบร้อยแล้ว";
    } else {
        echo "❌ เกิดข้อผิดพลาด: " . mysqli_error($objCon);
    }
    exit();
}

if ($action == 'delete') {
    // 🗑️ ลบข้อมูลวัคซีน
    $id = (int)$_POST['id'];
    $sql = "DELETE FROM vaccinations WHERE vaccine_id = $id";
    if (mysqli_query($objCon, $sql)) {
        echo "✅ ลบข้อมูลเรียบร้อยแล้ว";
    } else {
        echo "❌ เกิดข้อผิดพลาด: " . mysqli_error($objCon);
    }
    exit();
}

if ($action == 'editform') {
    // 🧩 โหลดฟอร์มแก้ไขข้อมูลวัคซีน (ใช้ใน Modal)
    $id = (int)$_GET['id'];
    $sql = "SELECT * FROM vaccinations WHERE vaccine_id = $id";
    echo $sql;
    $q = mysqli_query($objCon, $sql);
    $r = mysqli_fetch_assoc($q);
    ?>
    <div class="row g-3">
        <input type="hidden" name="vaccine_id" value="<?=$r['vaccine_id']?>">

        <div class="col-md-6">
          <label>สุนัข</label>
          <select name="dog_id" class="form-select" required>
            <?php
            $dq = mysqli_query($objCon, "SELECT dog_id, dog_name FROM dogs ORDER BY dog_name");
            while($d = mysqli_fetch_assoc($dq)){
                $sel = ($d['dog_id'] == $r['dog_id']) ? "selected" : "";
                echo "<option value='{$d['dog_id']}' $sel>{$d['dog_name']}</option>";
            }
            ?>
          </select>
        </div>

        <div class="col-md-6">
          <label>คลินิก</label>
          <select name="clinic_id" class="form-select" required>
            <?php
            $cq = mysqli_query($objCon, "SELECT clinic_id, clinic_name FROM clinics ORDER BY clinic_name");
            while($c = mysqli_fetch_assoc($cq)){
                $sel = ($c['clinic_id'] == $r['clinic_id']) ? "selected" : "";
                echo "<option value='{$c['clinic_id']}' $sel>{$c['clinic_name']}</option>";
            }
            ?>
          </select>
        </div>

        <div class="col-md-6">
          <label>ชื่อวัคซีน</label>
          <input type="text" name="vaccine_name" class="form-control" value="<?=$r['vaccine_name']?>" required>
        </div>

        <div class="col-md-6">
          <label>ประเภท</label>
          <input type="text" name="vaccine_type" class="form-control" value="<?=$r['vaccine_type']?>">
        </div>

        <div class="col-md-6">
          <label>วันที่ฉีด</label>
          <input type="date" name="vaccine_date" class="form-control" value="<?=$r['vaccine_date']?>" required>
        </div>

        <div class="col-md-6">
          <label>วันครบถัดไป</label>
          <input type="date" name="next_due_date" class="form-control" value="<?=$r['next_due_date']?>">
        </div>

        <div class="col-md-6">
          <label>สัตวแพทย์ผู้ฉีด</label>
          <input type="text" name="doctor_name" class="form-control" value="<?=$r['doctor_name']?>">
        </div>

        <div class="col-12">
          <label>หมายเหตุ</label>
          <textarea name="note" class="form-control"><?=$r['note']?></textarea>
        </div>
    </div>
    <?php
    exit();
}

if ($action == 'update') {
    // ✏️ อัปเดตข้อมูลวัคซีน
    $vaccine_id = (int)$_POST['vaccine_id'];
    $dog_id = mysqli_real_escape_string($objCon, $_POST['dog_id']);
    $clinic_id = mysqli_real_escape_string($objCon, $_POST['clinic_id']);
    $vaccine_name = mysqli_real_escape_string($objCon, $_POST['vaccine_name']);
    $vaccine_type = mysqli_real_escape_string($objCon, $_POST['vaccine_type']);
    $vaccine_date = mysqli_real_escape_string($objCon, $_POST['vaccine_date']);
    $next_due_date = mysqli_real_escape_string($objCon, $_POST['next_due_date']);
    $doctor_name = mysqli_real_escape_string($objCon, $_POST['doctor_name']);
    $note = mysqli_real_escape_string($objCon, $_POST['note']);

    $sql = "UPDATE vaccinations 
            SET dog_id='$dog_id', clinic_id='$clinic_id', vaccine_name='$vaccine_name', 
                vaccine_type='$vaccine_type', vaccine_date='$vaccine_date',
                next_due_date=" . ($next_due_date ? "'$next_due_date'" : "NULL") . ",
                doctor_name='$doctor_name', note='$note'
            WHERE vaccine_id=$vaccine_id";

    if (mysqli_query($objCon, $sql)) {
        echo "✅ อัปเดตข้อมูลวัคซีนเรียบร้อยแล้ว";
    } else {
        echo "❌ เกิดข้อผิดพลาด: " . mysqli_error($objCon);
    }
    exit();
}
?>
