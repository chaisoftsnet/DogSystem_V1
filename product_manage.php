<?php
@session_start();
require_once('dbconnect.php');

// ตรวจสอบสิทธิ์ผู้ใช้
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
<title>💊 จัดการสินค้าและบริการ | Clinic POS</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<style>
body {
  background-color: #f8f9fa;
  transition: background 0.3s, color 0.3s;
  font-family: 'Prompt', sans-serif;
}
.dark-mode {
  background-color: #121212;
  color: #f1f1f1;
}
.card {
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.05);
}
.table td, .table th {
  vertical-align: middle;
}
.btn-add {
  background: linear-gradient(45deg, #28a745, #00c853);
  color: white;
  border: none;
}
.btn-add:hover {
  opacity: 0.9;
}
.toggle-dark {
  cursor: pointer;
  font-size: 20px;
  color: #198754;
}
/* 🌗 Dark Mode Settings */
.dark-mode {
  background-color: #121212;
  color: #f1f1f1;
}

/* Card */
.dark-mode .card {
  background-color: #1e1e1e;
  color: #f1f1f1;
  border: 1px solid #333;
}

/* Table */
.dark-mode .table {
  color: #f1f1f1;
}
.dark-mode .table thead {
  background-color: #222;
  color: #00e676;
}

/* 🔹 Modal */
.dark-mode .modal-content {
  background-color: #1e1e1e;
  color: #f1f1f1;
  border: 1px solid #333;
}
.dark-mode .modal-header,
.dark-mode .modal-footer {
  border-color: #333;
}
.dark-mode .modal-title {
  color: #00e676;
}

/* Input, Select, Textarea */
.dark-mode input,
.dark-mode select,
.dark-mode textarea {
  background-color: #2c2c2c !important;
  color: #f1f1f1 !important;
  border: 1px solid #555 !important;
}
.dark-mode input::placeholder {
  color: #aaa !important;
}
.dark-mode select option {
  background-color: #2c2c2c;
  color: #f1f1f1;
}

/* ปุ่ม */
.dark-mode .btn-close {
  filter: invert(1);
}
.dark-mode .btn {
  color: #fff;
}
.dark-mode .btn:hover {
  opacity: 0.9;
}

/* DataTables Dark Style */
.dark-mode .dataTables_wrapper .dataTables_paginate .paginate_button {
  color: #f1f1f1 !important;
}
.dark-mode .dataTables_wrapper .dataTables_filter input {
  background-color: #2c2c2c;
  color: #f1f1f1;
  border: 1px solid #555;
}

</style>
</head>

<body>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>💊 จัดการสินค้าและบริการ</h3>
    <div>
      <i class="fa fa-moon toggle-dark" onclick="toggleDarkMode()"></i>
      <a href="invoice_dashboard.php" class="btn btn-secondary btn-sm ms-2"><i class="fa fa-arrow-left"></i> กลับหน้าหลัก POS</a>
    </div>
  </div>

  <div class="card p-3">
    <div class="d-flex justify-content-between mb-3">
      <h5 class="mb-0">รายการสินค้า / บริการ</h5>
      <button class="btn btn-add btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
        <i class="fa fa-plus-circle"></i> เพิ่มสินค้า / บริการ
      </button>
    </div>

    <table id="productTable" class="table table-striped table-bordered text-center align-middle">
      <thead class="table-dark">
        <tr>
          <th>#</th>
          <th>ชื่อสินค้า / บริการ</th>
          <th>หมวดหมู่</th>
          <th>ราคา (บาท)</th>
          <th>วันที่เพิ่ม</th>
          <th>จัดการ</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $sql = "SELECT * FROM products ORDER BY product_id DESC";
        $result = mysqli_query($objCon, $sql);
        $i = 1;
        while ($row = mysqli_fetch_assoc($result)) {
          echo "
          <tr>
            <td>{$i}</td>
            <td>{$row['product_name']}</td>
            <td>{$row['category']}</td>
            <td>".number_format($row['unit_price'],2)."</td>
            <td>".date('d/m/Y', strtotime($row['created_at']))."</td>
            <td>
              <button class='btn btn-warning btn-sm editBtn' data-id='{$row['product_id']}'><i class='fa fa-pen'></i></button>
              <button class='btn btn-danger btn-sm delBtn' data-id='{$row['product_id']}'><i class='fa fa-trash'></i></button>
            </td>
          </tr>";
          $i++;
        }
        ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal: เพิ่มสินค้า -->
<div class="modal fade" id="addModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="addForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">➕ เพิ่มสินค้า / บริการ</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label>ชื่อสินค้า / บริการ</label>
          <input type="text" name="product_name" class="form-control" required>
        </div>
        <div class="mb-3">
          <label>หมวดหมู่</label>
          <select name="category" class="form-select">
            <option value="ยา">ยา</option>
            <option value="วัคซีน">วัคซีน</option>
            <option value="บริการ">บริการ</option>
            <option value="อื่นๆ">อื่นๆ</option>
          </select>
        </div>
        <div class="mb-3">
          <label>ราคาต่อหน่วย (บาท)</label>
          <input type="number" step="0.01" name="unit_price" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">บันทึก</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal: แก้ไขสินค้า -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="editForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">✏️ แก้ไขสินค้า / บริการ</h5>
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
  $('#productTable').DataTable({
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
    $.ajax({
      url: 'product_action.php?action=add',
      type: 'POST',
      data: $(this).serialize(),
      success: function(res){ alert(res); location.reload(); }
    });
  });

  // ลบข้อมูล
  $('.delBtn').click(function(){
    if(confirm('แน่ใจว่าต้องการลบข้อมูลนี้?')){
      $.post('product_action.php?action=delete',{id:$(this).data('id')},function(res){
        alert(res); location.reload();
      });
    }
  });

  // แก้ไขข้อมูล
  $('.editBtn').click(function(){
    let id = $(this).data('id');
    $('#editBody').load('product_action.php?action=editform&id='+id);
    $('#editModal').modal('show');
  });

  // อัปเดตข้อมูล
  $('#editForm').submit(function(e){
    e.preventDefault();
    $.ajax({
      url: 'product_action.php?action=update',
      type: 'POST',
      data: $(this).serialize(),
      success: function(res){ alert(res); location.reload(); }
    });
  });
});

// Dark mode จำสถานะไว้ใน localStorage
document.addEventListener("DOMContentLoaded", () => {
  const savedMode = localStorage.getItem("themeMode");
  if (!savedMode || savedMode === "dark") document.body.classList.add("dark-mode");
});
function toggleDarkMode(){
  document.body.classList.toggle('dark-mode');
  const isDark = document.body.classList.contains('dark-mode');
  localStorage.setItem("themeMode", isDark ? "dark" : "light");
}
</script>
</body>
</html>
