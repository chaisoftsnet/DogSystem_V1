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
<title>🏢 จัดการข้อมูลผู้จำหน่าย (Supplier Management)</title>

<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<style>
body {
  font-family: 'Prompt', sans-serif;
  background: var(--bg-main, radial-gradient(circle at top, #1b2735 0%, #090a0f 80%));
  color: var(--text-main, #fff);
  transition: 0.3s;
}
body.light-mode {
  --bg-main: linear-gradient(150deg, #f1f8e9, #e0f7fa);
  --text-main: #222;
  --text-sub: #444;
}
.container-box {
  background: rgba(255,255,255,0.08);
  border-radius: 15px;
  padding: 25px;
  margin-top: 80px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.3);
}
.table th, .table td { vertical-align: middle; }
.theme-toggle {
  position: fixed;
  top: 15px; right: 15px;
  width: 50px; height: 50px;
  border-radius: 50%;
  display: flex; justify-content: center; align-items: center;
  background: rgba(255,255,255,0.1);
  border: 1px solid rgba(255,255,255,0.3);
  cursor: pointer;
  z-index: 999;
  color: #fff;
}
.theme-toggle:hover { transform: rotate(15deg); }
.btn-main { background: linear-gradient(45deg, #00e676, #00bfa5); color: #000; font-weight: bold; border: none; }
.btn-main:hover { background: linear-gradient(45deg, #00c853, #1de9b6); }
</style>
</head>

<body>
<div class="theme-toggle" onclick="toggleTheme()"><i class="bi bi-moon-stars"></i></div>

<div class="container container-box">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3><i class="fa-solid fa-truck-field"></i> จัดการข้อมูลผู้จำหน่าย (Suppliers)</h3>
    <div>
      <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
        <i class="fa fa-plus-circle"></i> เพิ่มข้อมูล
      </button>
      <a href="purchase_dashboard.php" class="btn btn-secondary btn-sm">
        <i class="bi bi-house-door"></i> กลับหน้าหลัก
      </a>
    </div>
  </div>

  <table id="supplierTable" class="table table-striped table-hover text-center align-middle">
    <thead class="table-dark">
      <tr>
        <th>#</th>
        <th>ชื่อผู้จำหน่าย</th>
        <th>โทรศัพท์</th>
        <th>อีเมล</th>
        <th>LINE</th>
        <th>ที่อยู่</th>
        <th>จัดการ</th>
      </tr>
    </thead>
    <tbody>
      <?php
        $sql = "SELECT * FROM suppliers ORDER BY supplier_id DESC";
        $result = mysqli_query($objCon, $sql);
        $i = 1;
        while ($r = mysqli_fetch_assoc($result)) {
          echo "
          <tr>
            <td>{$i}</td>
            <td>{$r['supplier_name']}</td>
            <td>{$r['phone']}</td>
            <td>{$r['email']}</td>
            <td>{$r['line_id']}</td>
            <td>{$r['address']}</td>
            <td>
              <button class='btn btn-warning btn-sm editBtn' data-id='{$r['supplier_id']}'><i class='fa fa-pen'></i></button>
              <button class='btn btn-danger btn-sm delBtn' data-id='{$r['supplier_id']}'><i class='fa fa-trash'></i></button>
            </td>
          </tr>";
          $i++;
        }
      ?>
    </tbody>
  </table>
</div>

<!-- Modal: เพิ่มข้อมูล -->
<div class="modal fade" id="addModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form id="addForm" class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">➕ เพิ่มข้อมูลผู้จำหน่าย</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3">
        <div class="col-md-6">
          <label>ชื่อผู้จำหน่าย</label>
          <input type="text" name="supplier_name" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label>เบอร์โทรศัพท์</label>
          <input type="text" name="phone" class="form-control">
        </div>
        <div class="col-md-6">
          <label>อีเมล</label>
          <input type="email" name="email" class="form-control">
        </div>
        <div class="col-md-6">
          <label>LINE ID</label>
          <input type="text" name="line_id" class="form-control">
        </div>
        <div class="col-12">
          <label>ที่อยู่</label>
          <textarea name="address" class="form-control"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">บันทึกข้อมูล</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal: แก้ไข -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form id="editForm" class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title">✏️ แก้ไขข้อมูลผู้จำหน่าย</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="editBody"></div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">อัปเดตข้อมูล</button>
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
  $('#supplierTable').DataTable({ language: { search: "ค้นหา:", paginate: { previous: "ก่อนหน้า", next: "ถัดไป" } } });

  $('#addForm').submit(function(e){
    e.preventDefault();
    $.post('supplier_action.php?action=add', $(this).serialize(), function(res){
      alert(res); location.reload();
    });
  });

  $('.delBtn').click(function(){
    if(confirm('แน่ใจว่าต้องการลบข้อมูลนี้?')){
      $.post('supplier_action.php?action=delete',{id:$(this).data('id')},function(res){
        alert(res); location.reload();
      });
    }
  });

  $('.editBtn').click(function(){
    let id = $(this).data('id');
    $('#editBody').load('supplier_action.php?action=editform&id='+id);
    $('#editModal').modal('show');
  });

  $('#editForm').submit(function(e){
    e.preventDefault();
    $.post('supplier_action.php?action=update', $(this).serialize(), function(res){
      alert(res); location.reload();
    });
  });
});

function toggleTheme(){
  document.body.classList.toggle('light-mode');
  const icon = document.querySelector('.theme-toggle i');
  icon.classList.toggle('bi-sun');
  icon.classList.toggle('bi-moon-stars');
}
</script>
</body>
</html>
