<?php
@session_start();
require_once('dbconnect.php');

$action = $_GET['action'] ?? '';

if ($action == 'add') {
    // ➕ เพิ่มข้อมูลผลแล็บใหม่
    $dog_id = mysqli_real_escape_string($objCon, $_POST['dog_id']);
    $clinic_id = mysqli_real_escape_string($objCon, $_POST['clinic_id']);
    $test_name = mysqli_real_escape_string($objCon, $_POST['test_name']);
    $test_date = mysqli_real_escape_string($objCon, $_POST['test_date']);
    $blood_result = mysqli_real_escape_string($objCon, $_POST['blood_result']);
    $urine_result = mysqli_real_escape_string($objCon, $_POST['urine_result']);
    $note = mysqli_real_escape_string($objCon, $_POST['note']);

    // 📎 อัปโหลดไฟล์แนบ (X-ray, Ultrasound)
    $file_path = "";
    if (!empty($_FILES['lab_file']['name'])) {
        $target_dir = "uploads/lab/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $filename = time() . "_" . basename($_FILES["lab_file"]["name"]);
        $target_file = $target_dir . $filename;
        if (move_uploaded_file($_FILES["lab_file"]["tmp_name"], $target_file)) {
            $file_path = $target_file;
        }
    }

    $sql = "INSERT INTO lab_results (dog_id, clinic_id, test_name, test_date, blood_result, urine_result, file_path, note)
            VALUES ('$dog_id', '$clinic_id', '$test_name', '$test_date', '$blood_result', '$urine_result', 
            '$file_path', '$note')";
    if (mysqli_query($objCon, $sql)) {
        echo "✅ เพิ่มข้อมูลผลแล็บเรียบร้อยแล้ว";
    } else {
        echo "❌ เกิดข้อผิดพลาด: " . mysqli_error($objCon);
    }
    exit();
}

if ($action == 'delete') {
    // 🗑️ ลบข้อมูล
    $id = (int)$_POST['id'];
    $sql = "DELETE FROM lab_results WHERE lab_id = $id";
    if (mysqli_query($objCon, $sql)) {
        echo "✅ ลบข้อมูลเรียบร้อยแล้ว";
    } else {
        echo "❌ เกิดข้อผิดพลาด: " . mysqli_error($objCon);
    }
    exit();
}

if ($action == 'editform') {
    // 🧩 โหลดฟอร์มแก้ไข
    $id = (int)$_GET['id'];
    $sql = "SELECT * FROM lab_results WHERE lab_id = $id";
    $q = mysqli_query($objCon, $sql);
    $r = mysqli_fetch_assoc($q);
    ?>
    <div class="row g-3">
      <input type="hidden" name="lab_id" value="<?=$r['lab_id']?>">

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

      <div class="col-md-6"><label>ชื่อการตรวจ</label><input type="text" name="test_name" class="form-control" value="<?=$r['test_name']?>"></div>
      <div class="col-md-6"><label>วันที่ตรวจ</label><input type="date" name="test_date" class="form-control" value="<?=$r['test_date']?>" required></div>
      <div class="col-12"><label>ผลเลือด</label><textarea name="blood_result" class="form-control"><?=$r['blood_result']?></textarea></div>
      <div class="col-12"><label>ผลปัสสาวะ</label><textarea name="urine_result" class="form-control"><?=$r['urine_result']?></textarea></div>
      <div class="col-12">
        <label>ไฟล์แนบ (X-ray / Ultrasound)</label>
        <?php if($r['file_path']){ ?>
          <p><a href="<?=$r['file_path']?>" target="_blank" class="btn btn-outline-info btn-sm"><i class="fa fa-file-medical"></i> เปิดไฟล์เดิม</a></p>
        <?php } ?>
        <input type="file" name="lab_file" class="form-control" accept="image/*,.pdf">
      </div>
      <div class="col-12"><label>หมายเหตุ</label><textarea name="note" class="form-control"><?=$r['note']?></textarea></div>
    </div>
    <?php
    exit();
}

if ($action == 'update') {
    // ✏️ อัปเดตข้อมูล
    $lab_id = (int)$_POST['lab_id'];
    $dog_id = mysqli_real_escape_string($objCon, $_POST['dog_id']);
    $clinic_id = mysqli_real_escape_string($objCon, $_POST['clinic_id']);
    $test_name = mysqli_real_escape_string($objCon, $_POST['test_name']);
    $test_date = mysqli_real_escape_string($objCon, $_POST['test_date']);
    $blood_result = mysqli_real_escape_string($objCon, $_POST['blood_result']);
    $urine_result = mysqli_real_escape_string($objCon, $_POST['urine_result']);
    $note = mysqli_real_escape_string($objCon, $_POST['note']);

    $file_update = "";
    if (!empty($_FILES['lab_file']['name'])) {
        $target_dir = "uploads/lab/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $filename = time() . "_" . basename($_FILES["lab_file"]["name"]);
        $target_file = $target_dir . $filename;
        if (move_uploaded_file($_FILES["lab_file"]["tmp_name"], $target_file)) {
            $file_update = ", file_path='$target_file'";
        }
    }

    $sql = "UPDATE lab_results 
            SET dog_id='$dog_id', clinic_id='$clinic_id', test_name='$test_name', 
                test_date='$test_date', blood_result='$blood_result', urine_result='$urine_result',
                note='$note' $file_update
            WHERE lab_id=$lab_id";

    if (mysqli_query($objCon, $sql)) {
        echo "✅ อัปเดตข้อมูลผลแล็บเรียบร้อยแล้ว";
    } else {
        echo "❌ เกิดข้อผิดพลาด: " . mysqli_error($objCon);
    }
    exit();
}
?>
