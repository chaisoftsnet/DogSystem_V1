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
<title>🩺 จัดการข้อมูลการผ่าตัด | Surgery Management</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.css"/>

<style>
body { background-color: #f8f9fa; transition: background 0.3s, color 0.3s; }
.dark-mode { background-color: #121212; color: #f1f1f1; }
.card { border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
.table td, .table th { vertical-align: middle; }
img.surgery-img { width: 70px; height: 70px; border-radius: 8px; object-fit: cover; border: 1px solid #ccc; }
.btn-add { background: linear-gradient(45deg, #28a745, #00c853); color: white; border: none; }
.btn-add:hover { opacity: 0.9; }
.toggle-dark { cursor: pointer; color: #198754; float: right; font-size: 20px; }
</style>
</head>
<link rel="stylesheet" href="css/theme.css">
<script src="js/theme.js"></script>
<body>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>🩺 จัดการข้อมูลการผ่าตัด</h3>
    <div>
      <span class="toggle-dark" onclick="toggleDarkMode()"> <i class="bi bi-moon-stars"></i> / <i class="bi bi-brightness-high"></i></span>
      <a href="dashboard.php" class="btn btn-secondary btn-sm ms-2"><i class="fa fa-home"></i> กลับหน้าหลัก</a>
    </div>
  </div>

  <div class="card p-3">
    <div class="d-flex justify-content-between mb-3">
      <h5 class="mb-0">รายการการผ่าตัดทั้งหมด</h5>
      <button class="btn btn-add btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
        <i class="fa fa-plus-circle"></i> เพิ่มข้อมูลผ่าตัด
      </button>
    </div>

    <table id="surgeryTable" class="table table-striped table-bordered text-center">
      <thead class="table-dark">
        <tr>
          <th>#</th>
          <th>ชื่อสุนัข</th>
          <th>ประเภทผ่าตัด</th>
          <th>วันที่ผ่าตัด</th>
          <th>สัตวแพทย์</th>
          <th>ผลลัพธ์</th>
          <th>คลินิก</th>
          <th>ไฟล์แนบ</th>
          <th>จัดการ</th>
        </tr>
      </thead>
      <tbody>
      <?php
        $sql = "SELECT s.*, d.dog_name, c.clinic_name
                FROM surgeries s
                LEFT JOIN dogs d ON s.dog_id = d.dog_id
                LEFT JOIN clinics c ON s.clinic_id = c.clinic_id
                ORDER BY s.surgery_date DESC";
        $result = mysqli_query($objCon, $sql);
        $i = 1;
        while ($row = mysqli_fetch_assoc($result)) {
          $img = !empty($row['file_path']) ? $row['file_path'] : 'images/no-file.png';
          echo "
          <tr>
            <td>{$i}</td>
            <td>{$row['dog_name']}</td>
            <td>{$row['surgery_type']}</td>
            <td>" . date('d/m/Y', strtotime($row['surgery_date'])) . "</td>
            <td>{$row['doctor_name']}</td>
            <td>{$row['outcome']}</td>
            <td>{$row['clinic_name']}</td>
            <td>
              <a data-fancybox='gallery{$i}' href='{$img}'>
                <img src='{$img}' class='surgery-img'>
              </a>
            </td>
            <td>
              <button class='btn btn-warning btn-sm editBtn' data-id='{$row['surgery_id']}'><i class='fa fa-pen'></i></button>
              <button class='btn btn-danger btn-sm delBtn' data-id='{$row['surgery_id']}'><i class='fa fa-trash'></i></button>
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
        <h5 class="modal-title">➕ เพิ่มข้อมูลการผ่าตัด</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3">
        <div class="col-md-6">
          <label>ชื่อสุนัข</label>
          <select name="dog_id" class="form-select" required>
            <option value="">-- เลือกสุนัข --</option>
            <?php
              $dogs = mysqli_query($objCon, "SELECT dog_id, dog_name FROM dogs ORDER BY dog_name");
              while($d = mysqli_fetch_assoc($dogs)) echo "<option value='{$d['dog_id']}'>{$d['dog_name']}</option>";
            ?>
          </select>
        </div>
        <div class="col-md-6">
          <label>คลินิก</label>
          <select name="clinic_id" class="form-select" required>
            <option value="">-- เลือกคลินิก --</option>
            <?php
              $cl = mysqli_query($objCon, "SELECT clinic_id, clinic_name FROM clinics ORDER BY clinic_name");
              while($c = mysqli_fetch_assoc($cl)) echo "<option value='{$c['clinic_id']}'>{$c['clinic_name']}</option>";
            ?>
          </select>
        </div>
        <div class="col-md-6"><label>วันที่ผ่าตัด</label><input type="date" name="surgery_date" class="form-control" required></div>
        <div class="col-md-6"><label>ประเภทผ่าตัด</label><input type="text" name="surgery_type" class="form-control" required></div>
        <div class="col-md-6"><label>สัตวแพทย์</label><input type="text" name="doctor_name" class="form-control"></div>
        <div class="col-md-6"><label>ไฟล์แนบผลผ่าตัด</label><input type="file" name="file_path" class="form-control" accept="image/*,application/pdf"></div>
        <div class="col-md-12"><label>รายละเอียด</label><textarea name="description" class="form-control"></textarea></div>
        <div class="col-md-12"><label>ผลลัพธ์</label><textarea name="outcome" class="form-control"></textarea></div>
        <div class="col-md-12"><label>หมายเหตุ</label><textarea name="notes" class="form-control"></textarea></div>
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
        <h5 class="modal-title">✏️ แก้ไขข้อมูลผ่าตัด</h5>
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
  $('#surgeryTable').DataTable({
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
      url: 'surgery_action.php?action=add',
      type: 'POST',
      data: formData,
      contentType: false, processData: false,
      success: function(res){ alert(res); location.reload(); }
    });
  });

  // ลบข้อมูล
  $('.delBtn').click(function(){
    if(confirm('แน่ใจว่าต้องการลบข้อมูลนี้?')){
      $.post('surgery_action.php?action=delete',{id:$(this).data('id')},function(res){
        alert(res); location.reload();
      });
    }
  });

  // แก้ไข
  $('.editBtn').click(function(){
    let id = $(this).data('id');
    $('#editBody').load('surgery_action.php?action=editform&id='+id);
    $('#editModal').modal('show');
  });

  // อัปเดต
  $('#editForm').submit(function(e){
    e.preventDefault();
    let formData = new FormData(this);
    $.ajax({
      url: 'surgery_action.php?action=update',
      type: 'POST',
      data: formData,
      contentType: false, processData: false,
      success: function(res){ alert(res); location.reload(); }
    });
  });
});

function toggleDarkMode(){ document.body.classList.toggle('dark-mode'); }
</script>

</body>
</html>
