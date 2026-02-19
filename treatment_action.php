<?php
@session_start();
require_once('dbconnect.php');
require_once('function.php');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {

  // ------------------ ✳️ ADD ------------------
  case 'add':
    $dog_id = $_POST['dog_id'];
    $clinic_id = ($_SESSION['role']==3) ? $_POST['clinic_id'] : $_SESSION['clinic_id'];
    $treatment_date = $_POST['treatment_date'];
    $symptoms = mysqli_real_escape_string($objCon, $_POST['symptoms']);
    $diagnosis = mysqli_real_escape_string($objCon, $_POST['diagnosis']);
    $treatment = mysqli_real_escape_string($objCon, $_POST['treatment']);
    $medication = mysqli_real_escape_string($objCon, $_POST['medication']);
    $doctor_name = $_POST['doctor_name'];
    $next_appointment = $_POST['next_appointment'];

    // บันทึกข้อมูลการรักษา
    $sql = "INSERT INTO treatments 
            (dog_id, clinic_id, treatment_date, symptoms, diagnosis, treatment, medication, doctor_name, next_appointment, created_at)
            VALUES ('$dog_id','$clinic_id','$treatment_date','$symptoms','$diagnosis','$treatment','$medication','$doctor_name','$next_appointment',NOW())";
    $res = mysqli_query($objCon, $sql);
    $treatment_id = mysqli_insert_id($objCon);

    // แนบไฟล์
    if (!empty($_FILES['attachment']['name'])) {
      $target_dir = "uploads/treatment/";
      if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
      $filename = time() . "_" . basename($_FILES["attachment"]["name"]);
      $target_file = $target_dir . $filename;
      move_uploaded_file($_FILES["attachment"]["tmp_name"], $target_file);

      $file_type = $_POST['file_type'] ?? 'อื่นๆ';
      $note = "ไฟล์แนบจากการรักษา ID: $treatment_id";

      $fsql = "INSERT INTO attachments (dog_id, clinic_id, file_type, file_path, note)
               VALUES ('$dog_id','$clinic_id','$file_type','$target_file','$note')";
      mysqli_query($objCon, $fsql);
    }

    echo "✅ บันทึกข้อมูลการรักษาเรียบร้อยแล้ว";
  break;


  // ------------------ ✳️ EDIT FORM ------------------
  case 'editform':
    $id = $_GET['id'];
    $sql = "SELECT * FROM treatments WHERE treatment_id='$id'";
    $q = mysqli_query($objCon, $sql);
    $r = mysqli_fetch_assoc($q);
?>
    <div class="row g-3">
      <input type="hidden" name="treatment_id" value="<?=$r['treatment_id']?>">

      <div class="col-md-4"><label>วันที่รักษา</label>
        <input type="date" name="treatment_date" value="<?=$r['treatment_date']?>" class="form-control">
      </div>

      <div class="col-md-4"><label>สัตว์</label>
        <select name="dog_id" class="form-select">
          <?php
            $dq = mysqli_query($objCon, "SELECT * FROM dogs WHERE clinic_id='{$_SESSION['clinic_id']}'");
            while($d=mysqli_fetch_assoc($dq)){
              $sel = ($r['dog_id']==$d['dog_id']) ? 'selected' : '';
              echo "<option value='{$d['dog_id']}' $sel>{$d['dog_name']}</option>";
            }
          ?>
        </select>
      </div>

      <div class="col-md-4"><label>สัตวแพทย์</label>
        <input type="text" name="doctor_name" value="<?=$r['doctor_name']?>" class="form-control">
      </div>

      <div class="col-12"><label>อาการ</label>
        <textarea name="symptoms" class="form-control"><?=$r['symptoms']?></textarea>
      </div>

      <div class="col-12"><label>การวินิจฉัย</label>
        <textarea name="diagnosis" class="form-control"><?=$r['diagnosis']?></textarea>
      </div>

      <div class="col-12"><label>การรักษา</label>
        <textarea name="treatment" class="form-control"><?=$r['treatment']?></textarea>
      </div>

      <div class="col-12"><label>ยา / เวชภัณฑ์</label>
        <textarea name="medication" class="form-control"><?=$r['medication']?></textarea>
      </div>

      <div class="col-md-6"><label>วันนัดถัดไป</label>
        <input type="date" name="next_appointment" value="<?=$r['next_appointment']?>" class="form-control">
      </div>

      <div class="col-md-6"><label>แนบไฟล์ใหม่ (ถ้ามี)</label>
        <input type="file" name="attachment" class="form-control" accept="image/*,application/pdf">
        <select name="file_type" class="form-select mt-2">
          <option value="ใบเสร็จ">ใบเสร็จ</option>
          <option value="ใบรับรองแพทย์">ใบรับรองแพทย์</option>
          <option value="โอนกรรมสิทธิ์">โอนกรรมสิทธิ์</option>
          <option value="อื่นๆ" selected>อื่นๆ</option>
        </select>
      </div>

      <?php if($_SESSION['role']==3){ ?>
      <div class="col-md-6">
        <label>คลินิก</label>
        <select name="clinic_id" class="form-select">
          <?php opt_clinic($r['clinic_id'], $objCon); ?>
        </select>
      </div>
      <?php } else { ?>
        <input type="hidden" name="clinic_id" value="<?=$r['clinic_id']?>">
      <?php } ?>
    </div>
<?php
  break;


  // ------------------ ✳️ UPDATE ------------------
  case 'update':
    $treatment_id = $_POST['treatment_id'];
    $dog_id = $_POST['dog_id'];
    $clinic_id = ($_SESSION['role']==3) ? $_POST['clinic_id'] : $_SESSION['clinic_id'];
    $treatment_date = $_POST['treatment_date'];
    $symptoms = mysqli_real_escape_string($objCon, $_POST['symptoms']);
    $diagnosis = mysqli_real_escape_string($objCon, $_POST['diagnosis']);
    $treatment = mysqli_real_escape_string($objCon, $_POST['treatment']);
    $medication = mysqli_real_escape_string($objCon, $_POST['medication']);
    $doctor_name = $_POST['doctor_name'];
    $next_appointment = $_POST['next_appointment'];

    $sql = "UPDATE treatments SET 
              dog_id='$dog_id',
              clinic_id='$clinic_id',
              treatment_date='$treatment_date',
              symptoms='$symptoms',
              diagnosis='$diagnosis',
              treatment='$treatment',
              medication='$medication',
              doctor_name='$doctor_name',
              next_appointment='$next_appointment'
            WHERE treatment_id='$treatment_id'";
    mysqli_query($objCon, $sql);

    // ถ้ามีการแนบไฟล์ใหม่
    if (!empty($_FILES['attachment']['name'])) {
      $target_dir = "uploads/treatment/";
      if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
      $filename = time() . "_" . basename($_FILES["attachment"]["name"]);
      $target_file = $target_dir . $filename;
      move_uploaded_file($_FILES["attachment"]["tmp_name"], $target_file);

      $file_type = $_POST['file_type'] ?? 'อื่นๆ';
      $note = "อัปเดตไฟล์แนบจากการรักษา ID: $treatment_id";

      $fsql = "INSERT INTO attachments (dog_id, clinic_id, file_type, file_path, note, uploaded_at)
               VALUES ('$dog_id','$clinic_id','$file_type','$target_file','$note',NOW())";
      mysqli_query($objCon, $fsql);
    }

    echo "✅ อัปเดตข้อมูลเรียบร้อยแล้ว";
  break;


  // ------------------ ✳️ DELETE ------------------
  case 'delete':
    $id = $_POST['id'];
    mysqli_query($objCon, "DELETE FROM treatments WHERE treatment_id='$id'");
    echo "🗑️ ลบข้อมูลเรียบร้อยแล้ว";
  break;


  // ------------------ ✳️ DEFAULT ------------------
  default:
    echo "❌ ไม่พบคำสั่งที่ร้องขอ";
  break;
}
?>
