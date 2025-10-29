<?php
@ob_start();
@session_start();
require_once('dbConnect.php');
require_once('function.php');

// ตรวจสอบสิทธิ์ (เฉพาะผู้ดูแลระบบ)
if (!isset($_SESSION['role']) || $_SESSION['role'] != 3) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>🏥 จัดการข้อมูลคลินิก</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.css"/>

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
  border-radius: 15px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}
.btn-add {
  background: linear-gradient(45deg, #007bff, #00c6ff);
  color: white;
  border: none;
}
.btn-add:hover { opacity: 0.9; }
.table td, .table th { vertical-align: middle; }
.toggle-dark { cursor: pointer; color: #198754; float: right; }
</style>
</head>

<body>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>🏥 จัดการข้อมูลคลินิก</h3>
    <div>
      <span class="toggle-dark" onclick="toggleDarkMode()"><i class="bi bi-moon-stars"></i> / <i class="bi bi-brightness-high"></i></span>
      <a href="dashboard.php" class="btn btn-secondary btn-sm"><i class="fa fa-home"></i> กลับหน้าหลัก</a>      
    </div>
  </div>

  <div class="card p-3">
    <div class="d-flex justify-content-between mb-3">
      <h5>รายการคลินิก</h5>
      <button class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addModal">
        <i class="bi bi-plus-circle"></i> เพิ่มข้อมูลคลินิก
      </button>
    </div>

    <table id="clinicTable" class="table table-striped table-bordered text-center align-middle">
      <thead class="table-dark">
        <tr>
          <th>#</th>
          <th>ชื่อคลินิก</th>
          <th>ที่อยู่</th>
          <th>โทรศัพท์</th>
          <th>อีเมล</th>
          <th>เจ้าของคลินิก</th>
          <th>วันที่สร้าง</th>
          <th>จัดการ</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $sql = "SELECT * FROM clinics ORDER BY clinic_id DESC";
        $result = mysqli_query($objCon, $sql);
        $i = 1;
        while ($row = mysqli_fetch_assoc($result)) {
          echo "<tr>
                  <td>{$i}</td>
                  <td>{$row['clinic_name']}</td>
                  <td>{$row['address']}</td>
                  <td>{$row['phone']}</td>
                  <td>{$row['email']}</td>
                  <td>{$row['owner_name']}</td>
                  <td>{$row['created_at']}</td>
                  <td>
                    <button class='btn btn-warning btn-sm editBtn' data-id='{$row['clinic_id']}'><i class='bi bi-pencil-square'></i></button>
                    <button class='btn btn-danger btn-sm delBtn' data-id='{$row['clinic_id']}'><i class='bi bi-trash'></i></button>
                  </td>
                </tr>";
          $i++;
        }
        ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal: Add Clinic -->
<div class="modal fade" id="addModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form id="addForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">➕ เพิ่มข้อมูลคลินิก</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3">
        <div class="col-md-6">
          <label>ชื่อคลินิก</label>
          <input type="text" name="clinic_name" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label>เจ้าของคลินิก</label>
          <input type="text" name="owner_name" class="form-control">
        </div>
        <div class="col-md-12">
          <label>ที่อยู่</label>
          <textarea name="address" class="form-control" rows="2"></textarea>
        </div>
        <div class="col-md-6">
          <label>โทรศัพท์</label>
          <input type="text" name="phone" class="form-control">
        </div>
        <div class="col-md-6">
          <label>อีเมล</label>
          <input type="email" name="email" class="form-control">
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">บันทึก</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal: Edit Clinic -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form id="editForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">✏️ แก้ไขข้อมูลคลินิก</h5>
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
  $('#clinicTable').DataTable({
    pageLength: 10,
    language: {
      lengthMenu: "แสดง _MENU_ รายการต่อหน้า",
      zeroRecords: "ไม่พบข้อมูล",
      info: "แสดงหน้า _PAGE_ จาก _PAGES_",
      search: "ค้นหา:",
      paginate: { previous: "ก่อนหน้า", next: "ถัดไป" }
    }
  });

  $('#addForm').on('submit', function(e){
    e.preventDefault();
    $.post('clinic_action.php?action=add', $(this).serialize(), function(res){
      alert(res);
      location.reload();
    });
  });

  $('.delBtn').click(function(){
    if(confirm('แน่ใจว่าต้องการลบข้อมูลนี้?')){
      $.post('clinic_action.php?action=delete',{id:$(this).data('id')},function(res){
        alert(res);
        location.reload();
      });
    }
  });

  $('.editBtn').click(function(){
    let id = $(this).data('id');
    $('#editBody').load('clinic_action.php?action=editform&id='+id);
    $('#editModal').modal('show');
  });

  $('#editForm').submit(function(e){
    e.preventDefault();
    $.post('clinic_action.php?action=update', $(this).serialize(), function(res){
      alert(res);
      location.reload();
    });
  });
});

function toggleDarkMode(){
  document.body.classList.toggle('dark-mode');
}
</script>

</body>
</html>
