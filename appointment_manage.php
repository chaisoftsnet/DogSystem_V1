<?php
@session_start();
require_once('dbconnect.php');


if (!isset($_SESSION['user_id'])) {
  header("Location: index.php");
  exit();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>📅 จัดการการนัดหมาย | Appointment Management</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">

<style>
body {
  background-color: #f8f9fa;
  transition: background 0.3s, color 0.3s;
}
.dark-mode {
  background-color: #121212;
  color: #f1f1f1;
}
.card {
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.05);
}
.table td, .table th { vertical-align: middle; }
.btn-add {
  background: linear-gradient(45deg, #28a745, #00c853);
  color: white;
  border: none;
}
.btn-add:hover { opacity: 0.9; }
.toggle-dark { cursor: pointer; color: #198754; font-size: 20px; }
</style>
</head>

<body>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>📅 จัดการการนัดหมาย</h3>
    <div>          
      <span class="toggle-dark" onclick="toggleDarkMode()"> <i class="bi bi-moon-stars"></i> / <i class="bi bi-brightness-high"></i></span>
      <a href="dashboard.php" class="btn btn-secondary btn-sm ms-2"><i class="fa fa-home"></i> กลับหน้าหลัก</a>
    </div>
  </div>

  <div class="card p-3">
    <div class="d-flex justify-content-between mb-3">
      <h5 class="mb-0">รายการนัดหมายทั้งหมด</h5>
      <button class="btn btn-add btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
        <i class="fa fa-plus-circle"></i> เพิ่มการนัดหมาย
      </button>
    </div>

    <div class="col-md-3 align-self-end">  
  <a href="appointment_report.php?year=<?=$year?>&month=<?=$month?>" target="_blank" class="btn btn-outline-primary ms-2">
    <i class="fa fa-print"></i> พิมพ์รายงาน
  </a>
</div>

    <table id="appointTable" class="table table-striped table-bordered text-center">
      <thead class="table-dark">
        <tr>
          <th>#</th>
          <th>ชื่อสุนัข</th>
          <th>คลินิก</th>
          <th>วันและเวลานัด</th>
          <th>รายละเอียด</th>
          <th>สถานะ</th>
          <th>จัดการ</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $sql = "SELECT a.*, d.dog_name, c.clinic_name 
                FROM appointments a
                LEFT JOIN dogs d ON a.dog_id = d.dog_id
                LEFT JOIN clinics c ON a.clinic_id = c.clinic_id
                ORDER BY a.appointment_date DESC";
        $q = mysqli_query($objCon, $sql);
        $i = 1;
        while($r = mysqli_fetch_assoc($q)){
          echo "
          <tr>
            <td>{$i}</td>
            <td>{$r['dog_name']}</td>
            <td>{$r['clinic_name']}</td>
            <td>".date("d/m/Y H:i", strtotime($r['appointment_date']))."</td>
            <td>{$r['description']}</td>
            <td><span class='badge bg-".
              ($r['status']=='เสร็จสิ้น'?'success':($r['status']=='ยกเลิก'?'danger':'warning')).
              "'>{$r['status']}</span></td>
            <td>
              <button class='btn btn-warning btn-sm editBtn' data-id='{$r['appointment_id']}'><i class='fa fa-pen'></i></button>
              <button class='btn btn-danger btn-sm delBtn' data-id='{$r['appointment_id']}'><i class='fa fa-trash'></i></button>
            </td>
          </tr>";
          $i++;
        }
        ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal: เพิ่ม -->
<div class="modal fade" id="addModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form id="addForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">➕ เพิ่มการนัดหมาย</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3">
        <?php if($_SESSION['role']==3){ ?>
        <div class="col-md-6">
          <label>เลือกคลินิก</label>
          <select name="clinic_id" class="form-select" required>
            <option value="">-- เลือกคลินิก --</option>
            <?php
            $c = mysqli_query($objCon,"SELECT * FROM clinics ORDER BY clinic_name");
            while($cc = mysqli_fetch_assoc($c)){
              echo "<option value='{$cc['clinic_id']}'>{$cc['clinic_name']}</option>";
            }
            ?>
          </select>
        </div>
        <?php } ?>
        <div class="col-md-6">
          <label>เลือกสุนัข</label>
          <select name="dog_id" class="form-select" required>
            <option value="">-- เลือกสุนัข --</option>
            <?php
            $d = mysqli_query($objCon,"SELECT dog_id,dog_name FROM dogs ORDER BY dog_name");
            while($dd = mysqli_fetch_assoc($d)){
              echo "<option value='{$dd['dog_id']}'>{$dd['dog_name']}</option>";
            }
            ?>
          </select>
        </div>
        <div class="col-md-6">
          <label>วันและเวลานัด</label>
          <input type="datetime-local" name="appointment_date" class="form-control" required>
        </div>
        <div class="col-12">
          <label>รายละเอียด</label>
          <textarea name="description" class="form-control" rows="2"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">บันทึก</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal: แก้ไข -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form id="editForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">✏️ แก้ไขการนัดหมาย</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="editBody"></div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">อัปเดต</button>
      </div>
    </form>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<script>
$(function(){
  $('#appointTable').DataTable({
    language:{
      lengthMenu:"แสดง _MENU_ รายการต่อหน้า",
      zeroRecords:"ไม่พบข้อมูล",
      info:"หน้า _PAGE_ จาก _PAGES_",
      search:"ค้นหา:",
      paginate:{previous:"ก่อนหน้า",next:"ถัดไป"}
    }
  });

  // เพิ่มข้อมูล
  $('#addForm').on('submit', function(e){
    e.preventDefault();
    $.ajax({
      url:'appointment_action.php?action=add',
      type:'POST',
      data:$(this).serialize(),
      success:function(res){
        alert(res);
        location.reload();
      }
    });
  });

  // ลบ
  $(document).on('click','.delBtn',function(){
    if(confirm('แน่ใจว่าต้องการลบข้อมูลนี้?')){
      $.post('appointment_action.php?action=delete',{id:$(this).data('id')},function(res){
        alert(res);
        location.reload();
      });
    }
  });

  // แก้ไข
  $(document).on('click','.editBtn',function(){
    let id = $(this).data('id');
    $('#editBody').load('appointment_action.php?action=editform&id='+id, function(){
      $('#editModal').modal('show');
    });
  });

  // อัปเดต
  $('#editForm').submit(function(e){
    e.preventDefault();
    $.ajax({
      url:'appointment_action.php?action=update',
      type:'POST',
      data:$(this).serialize(),
      success:function(res){
        alert(res);
        location.reload();
      }
    });
  });
});

function toggleDarkMode(){
  document.body.classList.toggle('dark-mode');
}
</script>
</body>
</html>
