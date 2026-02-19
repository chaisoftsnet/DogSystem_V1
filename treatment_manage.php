<?php
@session_start();
require_once('dbconnect.php');
require_once('function.php');

if (!isset($_SESSION['user_id'])) {
  header("Location: index.php");
  exit();
}

$clinic_id = $_SESSION['clinic_id'];
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>💉 การจัดการข้อมูลการรักษาพยาบาลสัตว์</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.css"/>

<style>
body { background-color: #f8f9fa; transition: background 0.3s, color 0.3s; font-family: 'Prompt', sans-serif; }
.dark-mode { background-color: #121212; color: #f1f1f1; }
.card { border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
.table td, .table th { vertical-align: middle; }
img.dog-img { width: 60px; height: 60px; border-radius: 50%; object-fit: cover; }
img.file-thumb { width: 60px; height: 60px; border-radius: 8px; object-fit: cover; border:1px solid #ccc; }
.btn-add {
  background: linear-gradient(45deg, #28a745, #00c853);
  color: white; border: none;
}
.btn-add:hover { opacity: 0.9; }
.toggle-dark { cursor: pointer; color: #198754; font-size: 20px; }
</style>
</head>
<link rel="stylesheet" href="css/theme.css">
<script src="js/theme.js"></script>
<body>
  
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>💉 การจัดการข้อมูลการรักษาพยาบาลสัตว์</h3>
        <div>          
      <span class="toggle-dark" onclick="toggleDarkMode()"> <i class="bi bi-moon-stars"></i> / <i class="bi bi-brightness-high"></i></span>
      <a href="dashboard.php" class="btn btn-secondary btn-sm ms-2"><i class="fa fa-home"></i> กลับหน้าหลัก</a>
    </div>
  </div>


  <div class="card p-3">
    <div class="d-flex justify-content-between mb-3">
      <h5 class="mb-0">รายการข้อมูลการรักษา</h5>
      <button class="btn btn-add btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
        <i class="fa fa-plus-circle"></i> เพิ่มข้อมูลการรักษา
      </button>
    </div>

    <table id="treatmentTable" class="table table-striped table-bordered text-center">
      <thead class="table-dark">
        <tr>
          <th>#</th>
          <th>วันที่รักษา</th>
          <th>ชื่อสุนัข</th>
          <th>อาการ</th>
          <th>สัตวแพทย์</th>
          <th>วันนัดถัดไป</th>
          <th>ไฟล์แนบ</th>
          <th>คลินิก</th>
          <th>จัดการ</th>
        </tr>
      </thead>
      <tbody>
      <?php
        $sql = ($_SESSION['role']==3)
          ? "SELECT t.*, d.dog_name, c.clinic_name 
              FROM treatments t 
              LEFT JOIN dogs d ON t.dog_id=d.dog_id 
              LEFT JOIN clinics c ON t.clinic_id=c.clinic_id
              ORDER BY t.treatment_id DESC"
          : "SELECT t.*, d.dog_name, c.clinic_name 
              FROM treatments t 
              LEFT JOIN dogs d ON t.dog_id=d.dog_id 
              LEFT JOIN clinics c ON t.clinic_id=c.clinic_id
              WHERE t.clinic_id='$clinic_id' 
              ORDER BY t.treatment_id DESC";

        $res = mysqli_query($objCon, $sql);
        $i = 1;
        while ($r = mysqli_fetch_assoc($res)) {
          $file = "SELECT * FROM attachments WHERE dog_id='{$r['dog_id']}' ORDER BY uploaded_at DESC LIMIT 1";
          $fres = mysqli_query($objCon, $file);
          $filePath = (mysqli_num_rows($fres)>0) ? mysqli_fetch_assoc($fres)['file_path'] : "images/no-file.png";
          echo "
          <tr>
            <td>{$i}.<sup>{$r['treatment_id']}</sup></td>
            <td>{$r['treatment_date']}</td>
            <td>{$r['dog_name']}</td>
            <td>{$r['symptoms']}</td>
            <td>{$r['doctor_name']}</td>
            <td>{$r['next_appointment']}</td>
            <td><a data-fancybox='file{$i}' href='{$filePath}'><img src='{$filePath}' class='file-thumb'></a></td>
            <td>{$r['clinic_name']}</td>
            <td>
              <button class='btn btn-warning btn-sm editBtn' data-id='{$r['treatment_id']}'><i class='fa fa-pen'></i></button>
              <button class='btn btn-danger btn-sm delBtn' data-id='{$r['treatment_id']}'><i class='fa fa-trash'></i></button>
            </td>
          </tr>";
          $i++;
        }
      ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal: เพิ่มข้อมูล -->
<div class="modal fade" id="addModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form id="addForm" class="modal-content" enctype="multipart/form-data">
      <div class="modal-header">
        <h5 class="modal-title">➕ เพิ่มข้อมูลการรักษา</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3">
        <div class="col-md-4"><label>วันที่รักษา</label><input type="date" name="treatment_date" class="form-control" required></div>
        <div class="col-md-4">
          <label>สุนัข</label>
          <select name="dog_id" class="form-select" required>
            <option value="">-- เลือกสุนัข --</option>
            <?php
              $dogq = mysqli_query($objCon, "SELECT * FROM dogs WHERE clinic_id='$clinic_id'");
              while($d=mysqli_fetch_assoc($dogq)){
                echo "<option value='{$d['dog_id']}'>{$d['dog_name']}</option>";
              }
            ?>
          </select>
        </div>
        <div class="col-md-4"><label>สัตวแพทย์</label><input type="text" name="doctor_name" class="form-control"></div>
        <div class="col-12"><label>อาการ</label><textarea name="symptoms" class="form-control"></textarea></div>
        <div class="col-12"><label>การวินิจฉัย</label><textarea name="diagnosis" class="form-control"></textarea></div>
        <div class="col-12"><label>การรักษา</label><textarea name="treatment" class="form-control"></textarea></div>
        <div class="col-12"><label>ยา/เวชภัณฑ์</label><textarea name="medication" class="form-control"></textarea></div>
        <div class="col-md-6"><label>วันนัดถัดไป</label><input type="date" name="next_appointment" class="form-control"></div>
        <div class="col-md-6">
          <label>ไฟล์แนบ-image</label>
          <input type="file" name="attachment" class="form-control" accept="image/*,application/pdf">
          <select name="file_type" class="form-select mt-2">
            <option value="ใบเสร็จ">ใบเสร็จ</option>
            <option value="ใบรับรองแพทย์">ใบรับรองแพทย์</option>
            <option value="โอนกรรมสิทธิ์">โอนกรรมสิทธิ์</option>
            <option value="อื่นๆ" selected>อื่นๆ</option>
          </select>
        </div>
        <?php if($_SESSION['role']==3){ ?>
          <div class="col-md-6"><label>คลินิก</label>
            <select name="clinic_id" class="form-select">
              <option value="">-- เลือกคลินิก --</option>
              <?php opt_clinic('', $objCon); ?>
            </select>
          </div>
        <?php } else { ?>
          <input type="hidden" name="clinic_id" value="<?=$clinic_id?>">
        <?php } ?>
      </div>
      <div class="modal-footer"><button type="submit" class="btn btn-success">บันทึก</button></div>
    </form>
  </div>
</div>

<!-- Modal: แก้ไข -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form id="editForm" class="modal-content" enctype="multipart/form-data">
      <div class="modal-header">
        <h5 class="modal-title">✏️ แก้ไขข้อมูลการรักษา</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="editBody"></div>
      <div class="modal-footer"><button type="submit" class="btn btn-primary">อัปเดต</button></div>
    </form>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.umd.js"></script>

<script>
$(function(){
  $('#treatmentTable').DataTable({
    language: {
      lengthMenu: "แสดง _MENU_ รายการต่อหน้า",
      zeroRecords: "ไม่พบข้อมูล",
      info: "หน้า _PAGE_ จาก _PAGES_",
      search: "ค้นหา:",
      paginate: { previous: "ก่อนหน้า", next: "ถัดไป" }
    }
  });

  // เพิ่มข้อมูล
  $('#addForm').submit(function(e){
    e.preventDefault();
    let formData = new FormData(this);
    $.ajax({
      url: 'treatment_action.php?action=add',
      type: 'POST',
      data: formData,
      contentType: false, processData: false,
      success: function(res){ alert(res); location.reload(); }
    });
  });

  // ลบข้อมูล
  $('.delBtn').click(function(){
    if(confirm('แน่ใจว่าต้องการลบข้อมูลนี้?')){
      $.post('treatment_action.php?action=delete',{id:$(this).data('id')},function(res){
        alert(res); location.reload();
      });
    }
  });

  // แก้ไขข้อมูล
  $('.editBtn').click(function(){
    let id = $(this).data('id');
    $('#editBody').load('treatment_action.php?action=editform&id='+id);
    $('#editModal').modal('show');
  });

  // อัปเดตข้อมูล
  $(document).on('submit','#editForm',function(e){
    e.preventDefault();
    let formData = new FormData(this);
    $.ajax({
      url: 'treatment_action.php?action=update',
      type: 'POST',
      data: formData,
      contentType: false, processData: false,
      success: function(res){ alert(res); location.reload(); }
    });
  });
});

function toggleDarkMode(){
  document.body.classList.toggle('dark-mode');
}
</script>
</body>
</html>
