<?php
@session_start();
require_once('dbconnect.php');

$action = $_GET['action'] ?? '';

if ($action == 'add') {
    // ➕ เพิ่มข้อมูลถ่ายพยาธิ
    $dog_id = mysqli_real_escape_string($objCon, $_POST['dog_id']);
    $clinic_id = mysqli_real_escape_string($objCon, $_POST['clinic_id']);
    $drug_name = mysqli_real_escape_string($objCon, $_POST['drug_name']);
    $treatment_date = mysqli_real_escape_string($objCon, $_POST['treatment_date']);
    $next_due_date = mysqli_real_escape_string($objCon, $_POST['next_due_date']);
    $note = mysqli_real_escape_string($objCon, $_POST['note']);

    $sql = "INSERT INTO dewormings (dog_id, clinic_id, drug_name, treatment_date, next_due_date, note)
            VALUES ('$dog_id', '$clinic_id', '$drug_name', '$treatment_date', 
            " . ($next_due_date ? "'$next_due_date'" : "NULL") . ", '$note')";

    if (mysqli_query($objCon, $sql)) {
        echo "✅ เพิ่มข้อมูลการถ่ายพยาธิเรียบร้อยแล้ว";
    } else {
        echo "❌ เกิดข้อผิดพลาด: " . mysqli_error($objCon);
    }
    exit();
}

if ($action == 'delete') {
    // 🗑️ ลบข้อมูล
    $id = (int)$_POST['id'];
    $sql = "DELETE FROM dewormings WHERE deworming_id = $id";
    if (mysqli_query($objCon, $sql)) {
        echo "✅ ลบข้อมูลเรียบร้อยแล้ว";
    } else {
        echo "❌ เกิดข้อผิดพลาด: " . mysqli_error($objCon);
    }
    exit();
}

if ($action == 'editform') {
    // ✏️ โหลดฟอร์มแก้ไขข้อมูล (ใช้ใน Modal)
    $id = (int)$_GET['id'];
    $sql = "SELECT * FROM dewormings WHERE deworming_id = $id";
    $q = mysqli_query($objCon, $sql);
    $r = mysqli_fetch_assoc($q);
    ?>
    <div class="row g-3">
        <input type="hidden" name="deworming_id" value="<?=$r['deworming_id']?>">

        <div class="col-md-6">
          <label>ชื่อสุนัข</label>
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
          <label>ชื่อยา</label>
          <input type="text" name="drug_name" class="form-control" value="<?=$r['drug_name']?>" required>
        </div>

        <div class="col-md-6">
          <label>วันที่ถ่ายพยาธิ</label>
          <input type="date" name="treatment_date" class="form-control" value="<?=$r['treatment_date']?>" required>
        </div>

        <div class="col-md-6">
          <label>วันครบถัดไป</label>
          <input type="date" name="next_due_date" class="form-control" value="<?=$r['next_due_date']?>">
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
    // ✏️ อัปเดตข้อมูล
    $id = (int)$_POST['deworming_id'];
    $dog_id = mysqli_real_escape_string($objCon, $_POST['dog_id']);
    $clinic_id = mysqli_real_escape_string($objCon, $_POST['clinic_id']);
    $drug_name = mysqli_real_escape_string($objCon, $_POST['drug_name']);
    $treatment_date = mysqli_real_escape_string($objCon, $_POST['treatment_date']);
    $next_due_date = mysqli_real_escape_string($objCon, $_POST['next_due_date']);
    $note = mysqli_real_escape_string($objCon, $_POST['note']);

    $sql = "UPDATE dewormings 
            SET dog_id='$dog_id', clinic_id='$clinic_id', drug_name='$drug_name',
                treatment_date='$treatment_date', 
                next_due_date=" . ($next_due_date ? "'$next_due_date'" : "NULL") . ",
                note='$note'
            WHERE deworming_id=$id";

    if (mysqli_query($objCon, $sql)) {
        echo "✅ อัปเดตข้อมูลเรียบร้อยแล้ว";
    } else {
        echo "❌ เกิดข้อผิดพลาด: " . mysqli_error($objCon);
    }
    exit();
}
?>
