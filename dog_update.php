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
<title>🐾 จัดการข้อมูลสุนัข | Dog Management</title>

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
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.05);
}
.table td, .table th { vertical-align: middle; }
img.dog-img { width: 60px; height: 60px; border-radius: 50%; object-fit: cover; }
img.xray-img { width: 60px; height: 60px; border-radius: 8px; object-fit: cover; border:1px solid #ccc; }
.btn-add {
  background: linear-gradient(45deg, #28a745, #00c853);
  color: white;
  border: none;
}
.btn-add:hover { opacity: 0.9; }
.toggle-dark { cursor: pointer; color: #198754; float: left; font-size: 20px; }
</style>
</head>
<link rel="stylesheet" href="css/theme.css">
<script src="js/theme.js"></script>

<body>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>🐶 จัดการข้อมูลสุนัข</h3>
    <div>
      <span class="toggle-dark" onclick="toggleDarkMode()"> <i class="bi bi-moon-stars"></i> / <i class="bi bi-brightness-high"></i></span>
      <a href="dashboard.php" class="btn btn-secondary btn-sm ms-2"><i class="fa fa-home"></i> กลับหน้าหลัก</a>
    </div>
  </div>


  <div class="card p-3">
    <div class="d-flex justify-content-between mb-3">
      <h5 class="mb-0">รายการข้อมูลสุนัข</h5>
      <button class="btn btn-add btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
        <i class="fa fa-plus-circle"></i> เพิ่มข้อมูลสุนัข
      </button>
    </div>

    <table id="dogTable" class="table table-striped table-bordered text-center">
      <thead class="table-dark">
        <tr>
          <th>#</th>
          <th>รูป</th>
          <th>ชื่อ</th>
          <th>สายพันธุ์</th>
          <th>เพศ</th>
          <th>อายุ</th>
          <th>น้ำหนัก</th>
          <th>RFID</th>
          <th>X-ray</th>
          <th>คลินิก</th>
          <th>เจ้าของ</th>          
          <th>จัดการ</th>
        </tr>
      </thead>
      <tbody>
      <?php
        $sql = "SELECT d.*, c.clinic_name, u.fullname AS owner_name
                FROM dogs d
                LEFT JOIN clinics c ON d.clinic_id = c.clinic_id
                LEFT JOIN user u ON d.user_id = u.id
                ORDER BY d.dog_id DESC";
        $result = mysqli_query($objCon, $sql);
        $i = 1;
        while ($row = mysqli_fetch_assoc($result)) {
          $img  = !empty($row['dog_image_path']) ? $row['dog_image_path'] : 'images/no-dog.png';
          $xray = !empty($row['xray_image_path']) ? $row['xray_image_path'] : 'images/no-xray.png';
          echo "
          <tr>
            <td>{$i}</td>
            <td>
              <a data-fancybox='gallery{$i}' href='{$img}'>
                <img src='{$img}' class='dog-img'>
              </a>
            </td>
            <td>{$row['dog_name']}</td>
            <td>{$row['dog_breed']}</td>
            <td>{$row['dog_gender']}</td>
            <td>{$row['dog_age']} ปี</td>
            <td>{$row['dog_weight']} กก.</td>
            <td>{$row['rfid_tag']}</td>            
            <td>
              <a data-fancybox='gallery{$i}' href='{$xray}'>
                <img src='{$xray}' class='xray-img'>
              </a>
            </td>
            <td>{$row['clinic_name']}</td>
            <td>{$row['owner_name']}</td>            
            <td>
              <button class='btn btn-warning btn-sm editBtn' data-id='{$row['dog_id']}'><i class='fa fa-pen'></i></button>
              <button class='btn btn-danger btn-sm delBtn' data-id='{$row['dog_id']}'><i class='fa fa-trash'></i></button>
            </td>
          </tr>";
          $i++;
        }
      ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal: เพิ่มข้อมูลสุนัข -->
<div class="modal fade" id="addModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form id="addForm" class="modal-content" enctype="multipart/form-data">
      <div class="modal-header">
        <h5 class="modal-title">➕ เพิ่มข้อมูลสุนัข</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3">
        <div class="col-md-6"><label>ชื่อสุนัข</label><input type="text" name="dog_name" class="form-control" required></div>
        <div class="col-md-6"><label>สายพันธุ์</label><input type="text" name="dog_breed" class="form-control"></div>
        <div class="col-md-6"><label>เพศ</label>
          <select name="dog_gender" class="form-select">
            <option value="ผู้">ผู้</option><option value="เมีย">เมีย</option>
          </select>
        </div>
        <div class="col-md-6"><label>อายุ (ปี)</label><input type="number" name="dog_age" class="form-control"></div>
        <div class="col-md-6"><label>น้ำหนัก (กก.)</label><input type="number" name="dog_weight" class="form-control"></div>
        <div class="col-md-6"><label>RFID Tag</label><input type="text" name="rfid_tag" class="form-control"></div>
        <div class="col-12"><label>ประวัติการรักษา</label><textarea name="dog_medical_history" class="form-control"></textarea></div>
        <div class="col-md-6"><label>รูปสุนัข</label><input type="file" name="dog_image" class="form-control" accept="image/*"></div>
        <div class="col-md-6"><label>ภาพ X-Ray</label><input type="file" name="xray_image" class="form-control" accept="image/*"></div>
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
    <form id="editForm" class="modal-content" enctype="multipart/form-data">
      <div class="modal-header">
        <h5 class="modal-title">✏️ แก้ไขข้อมูลสุนัข</h5>
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
<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.umd.js"></script>
<script>
$(function(){

  /* ===============================
     DATATABLE
  ================================ */
  var table = $('#dogTable').DataTable({
    language: {
      lengthMenu: "แสดง _MENU_ รายการต่อหน้า",
      zeroRecords: "ไม่พบข้อมูล",
      info: "หน้า _PAGE_ จาก _PAGES_",
      search: "ค้นหา:",
      paginate: { previous: "ก่อนหน้า", next: "ถัดไป" }
    }
  });

  /* ===============================
     ADD DOG
  ================================ */
  $('#addForm').on('submit', function(e){
    e.preventDefault();
    let formData = new FormData(this);
    $.ajax({
      url: 'dog_action.php?action=add',
      type: 'POST',
      data: formData,
      contentType: false,
      processData: false,
      success: function(res){
        alert(res);
        location.reload();
      }
    });
  });

  /* ===============================
     ✅ DELETE (delegation)
  ================================ */
  $(document).on('click', '.delBtn', function(){
    let id = $(this).data('id');
    if(confirm('แน่ใจว่าต้องการลบข้อมูลนี้?')){
      $.post(
        'dog_action.php?action=delete',
        {id:id},
        function(res){
          alert(res);
          location.reload();
        }
      );
    }
  });

  /* ===============================
     ✅ EDIT (delegation)
  ================================ */
  $(document).on('click', '.editBtn', function(){
    let id = $(this).data('id');
    $('#editBody').html('<div class="text-center p-3">กำลังโหลด...</div>');
    $('#editBody').load('dog_action.php?action=editform&id=' + id);
    $('#editModal').modal('show');
  });

  /* ===============================
     UPDATE DOG
  ================================ */
  $('#editForm').on('submit', function(e){
    e.preventDefault();
    let formData = new FormData(this);
    $.ajax({
      url: 'dog_action.php?action=update',
      type: 'POST',
      data: formData,
      contentType: false,
      processData: false,
      success: function(res){
        alert(res);
        location.reload();
      }
    });
  });

});

/* ===============================
   DARK MODE
================================ */
function toggleDarkMode(){
  document.body.classList.toggle('dark-mode');
}
</script>


</body>
</html>
