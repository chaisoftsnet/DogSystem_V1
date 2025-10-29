<?php
@session_start();
require_once('dbconnect.php');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch($action){

// ✅ เพิ่มไฟล์แนบ
case 'add':
    $dog_id     = $_POST['dog_id'];
    $clinic_id  = $_POST['clinic_id'];
    $file_type  = $_POST['file_type'];
    $note       = mysqli_real_escape_string($objCon, $_POST['note']);

    $file_path = '';
    if(isset($_FILES['file_path']) && $_FILES['file_path']['error'] == 0){
        $ext = strtolower(pathinfo($_FILES['file_path']['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf','jpg','jpeg','png'];
        if(in_array($ext, $allowed)){
            $folder = "uploads/attachments/";
            if(!is_dir($folder)) mkdir($folder, 0777, true);
            $newName = uniqid('att_') . '.' . $ext;
            move_uploaded_file($_FILES['file_path']['tmp_name'], $folder.$newName);
            $file_path = $folder.$newName;
        } else {
            echo "❌ ไม่รองรับไฟล์ชนิดนี้ ($ext)";
            exit;
        }
    }

    $sql = "INSERT INTO attachments (dog_id, clinic_id, file_type, file_path, note, uploaded_at)
            VALUES ('$dog_id','$clinic_id','$file_type','$file_path','$note',NOW())";
    $q = mysqli_query($objCon, $sql);
    echo $q ? "✅ เพิ่มไฟล์แนบเรียบร้อย" : "❌ เกิดข้อผิดพลาด: ".mysqli_error($objCon);
break;


// ✏️ โหลดฟอร์มแก้ไข
case 'editform':
    $id = $_GET['id'];
    $q = mysqli_query($objCon,"SELECT * FROM attachments WHERE attachment_id='$id'");
    $r = mysqli_fetch_assoc($q);
?>
<div class="row g-3">
  <input type="hidden" name="attachment_id" value="<?=$r['attachment_id']?>">

  <div class="col-md-6">
    <label>สุนัข</label>
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

  <div class="col-md-6">
    <label>ประเภทไฟล์</label>
    <select name="file_type" class="form-select">
      <?php
        $types = ['ใบเสร็จ','ใบรับรองแพทย์','โอนกรรมสิทธิ์','อื่นๆ'];
        foreach($types as $t){
          $sel = ($r['file_type']==$t) ? "selected" : "";
          echo "<option value='$t' $sel>$t</option>";
        }
      ?>
    </select>
  </div>

  <div class="col-md-6">
    <label>อัปโหลดไฟล์ใหม่ (ถ้ามี)</label>
    <input type="file" name="file_path" class="form-control" accept=".pdf,.jpg,.png,.jpeg">
    <div class="mt-1">
      <?php if($r['file_path']): ?>
        <a href="<?=$r['file_path']?>" target="_blank"><i class="fa fa-file"></i> ดูไฟล์เดิม</a>
        <input type="hidden" name="old_file" value="<?=$r['file_path']?>">
      <?php endif; ?>
    </div>
  </div>

  <div class="col-12">
    <label>หมายเหตุ</label>
    <textarea name="note" class="form-control"><?=$r['note']?></textarea>
  </div>
</div>
<?php
break;


// 🔁 อัปเดตไฟล์
case 'update':
    $id         = $_POST['attachment_id'];
    $dog_id     = $_POST['dog_id'];
    $clinic_id  = $_POST['clinic_id'];
    $file_type  = $_POST['file_type'];
    $note       = mysqli_real_escape_string($objCon, $_POST['note']);
    $file_path  = $_POST['old_file'] ?? '';

    if(isset($_FILES['file_path']) && $_FILES['file_path']['error']==0){
        $ext = strtolower(pathinfo($_FILES['file_path']['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf','jpg','jpeg','png'];
        if(in_array($ext, $allowed)){
            $folder = "uploads/attachments/";
            if(!is_dir($folder)) mkdir($folder, 0777, true);
            $newName = uniqid('att_') . '.' . $ext;
            move_uploaded_file($_FILES['file_path']['tmp_name'], $folder.$newName);
            $file_path = $folder.$newName;
        }
    }

    $sql = "UPDATE attachments SET 
              dog_id='$dog_id',
              clinic_id='$clinic_id',
              file_type='$file_type',
              file_path='$file_path',
              note='$note'
            WHERE attachment_id='$id'";
    $q = mysqli_query($objCon, $sql);
    echo $q ? "✅ อัปเดตข้อมูลเรียบร้อย" : "❌ เกิดข้อผิดพลาด: ".mysqli_error($objCon);
break;


// ❌ ลบไฟล์
case 'delete':
    $id = $_POST['id'];
    $q = mysqli_query($objCon,"SELECT file_path FROM attachments WHERE attachment_id='$id'");
    $r = mysqli_fetch_assoc($q);
    if($r && file_exists($r['file_path'])) unlink($r['file_path']);
    mysqli_query($objCon,"DELETE FROM attachments WHERE attachment_id='$id'");
    echo "🗑️ ลบข้อมูลเรียบร้อย";
break;
default:
    echo "❗ ไม่มีการกระทำที่ถูกต้อง";
}
?>
