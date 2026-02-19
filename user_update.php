<?php
@session_start();
require_once('dbConnect.php');
require_once('function.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] < 2) {
  header("Location: index.php");
  exit();
}

$aRole = ['คนทั่วไป','ลูกค้า','เจ้าหน้าที่คลินิก','หมอ','ผู้ดูแลระบบ'];
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>👤 จัดการผู้ใช้งานระบบ</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.css"/>

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
  border-radius: 15px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}
.table td, .table th { vertical-align: middle; }
.btn-add {
  background: linear-gradient(45deg, #007bff, #00b4d8);
  color: #fff;
  border: none;
}
.btn-add:hover { opacity: 0.9; }
.toggle-dark {
  cursor: pointer;
  float: left;
  color: #007bff;
  font-size: 1.2rem;
}
</style>
</head>
<link rel="stylesheet" href="css/theme.css">
<script src="js/theme.js"></script>
<body>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>👤 จัดการผู้ใช้งานระบบ</h3>
    <div>
      <span class="toggle-dark" onclick="toggleDarkMode()"><i class="bi bi-moon-stars"></i> / <i class="bi bi-brightness-high"></i></span>
      <a href="dashboard.php" class="btn btn-secondary btn-sm"><i class="fa fa-home"></i> กลับหน้าหลัก</a>      
    </div>
  </div>

  <div class="card p-3">
    <div class="d-flex justify-content-between mb-3">
      <h5>รายชื่อผู้ใช้งาน</h5>
      <button class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addModal"><i class="bi bi-person-plus"></i> เพิ่มผู้ใช้งาน</button>
    </div>

    <table id="userTable" class="table table-striped table-bordered text-center align-middle">
      <thead class="table-dark">
        <tr>
          <th>#</th>
          <th>ชื่อผู้ใช้</th>
          <th>ชื่อ-สกุล</th>
          <th>สิทธิ์</th>
          <th>คลินิก</th>
          <th>อีเมล</th>
          <th>โทรศัพท์</th>
          <th>จัดการ</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $sql = "SELECT u.*, c.clinic_name FROM user u 
                LEFT JOIN clinics c ON u.clinic_id = c.clinic_id
                ORDER BY u.id DESC";
        $result = mysqli_query($objCon, $sql);
        $i = 1;
        while ($r = mysqli_fetch_assoc($result)) {
          echo "
          <tr>
            <td>$i</td>
            <td>{$r['username']}</td>
            <td>{$r['fullname']}</td>
            <td>{$aRole[$r['role']]}</td>
            <td>{$r['clinic_name']}</td>
            <td>{$r['email']}</td>
            <td>{$r['tel']}</td>
            <td>
              <button class='btn btn-sm btn-warning editBtn' data-id='{$r['id']}'><i class='bi bi-pencil-square'></i></button>
              <button class='btn btn-sm btn-danger delBtn' data-id='{$r['id']}'><i class='bi bi-trash'></i></button>
            </td>
          </tr>";
          $i++;
        }
        ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal: Add -->
<div class="modal fade" id="addModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form id="addForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">➕ เพิ่มผู้ใช้งาน</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3">
        <div class="col-md-6"><label>ชื่อผู้ใช้</label><input type="text" name="username" class="form-control" required></div>
        <div class="col-md-6"><label>รหัสผ่าน</label><input type="password" name="password" class="form-control" required></div>
        <div class="col-md-6"><label>ชื่อ-สกุล</label><input type="text" name="fullname" class="form-control" required></div>
        <div class="col-md-6"><label>อีเมล</label><input type="email" name="email" class="form-control"></div>
        <div class="col-md-6"><label>เบอร์โทร</label><input type="text" name="tel" class="form-control"></div>
        <div class="col-md-6"><label>เลขบัตรประชาชน</label><input type="text" name="id_card" class="form-control"></div>
        <div class="col-md-6"><label>LINE ID</label><input type="text" name="line_id" class="form-control"></div>
        <div class="col-md-6">
          <label>สิทธิ์ผู้ใช้งาน</label>
          <select name="role" class="form-select" required>
            <option value="">-- เลือกสิทธิ์ --</option>
            <option value="1">ลูกค้า</option>
            <option value="2">เจ้าหน้าที่คลินิก</option>
            <option value="3">หมอ</option>
            <option value="4">ผู้ดูแลระบบ</option>            
          </select>
        </div>
        <div class="col-md-6">
          <label>เลือกคลินิก</label>
          <select name="clinic_id" class="form-select" required>
            <option value="">-- เลือกคลินิก --</option>
            <?php
            $q = mysqli_query($objCon, "SELECT * FROM clinics ORDER BY clinic_name");
            while($c = mysqli_fetch_assoc($q)){
              echo "<option value='{$c['clinic_id']}'>{$c['clinic_name']}</option>";
            }
            ?>
          </select>
        </div>
        <div class="col-12"><label>ที่อยู่</label><textarea name="address" class="form-control" rows="2"></textarea></div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">บันทึก</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal: Edit -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form id="editForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">✏️ แก้ไขผู้ใช้งาน</h5>
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

  // ===============================
  // DATATABLE
  // ===============================
  var table = $('#userTable').DataTable({
    pageLength: 50,
    language: {
      lengthMenu: "แสดง _MENU_ รายการต่อหน้า",
      zeroRecords: "ไม่พบข้อมูล",
      info: "แสดงหน้า _PAGE_ จาก _PAGES_",
      search: "ค้นหา:",
      paginate: { previous: "ก่อนหน้า", next: "ถัดไป" }
    }
  });

  // ===============================
  // ADD USER
  // ===============================
  $('#addForm').on('submit', function(e){
    e.preventDefault();
    $.post('user_action.php?action=add', $(this).serialize(), function(res){
      alert(res);
      location.reload();
    });
  });

  // ===============================
  // ✅ EDIT (ใช้ event delegation)
  // ===============================
  $(document).on('click', '.editBtn', function(){
    let id = $(this).data('id');
    $('#editBody').html('<div class="text-center p-3">กำลังโหลด...</div>');
    $('#editBody').load('user_action.php?action=editform&id=' + id);
    $('#editModal').modal('show');
  });

  // ===============================
  // UPDATE USER
  // ===============================
  $('#editForm').on('submit', function(e){
    e.preventDefault();
    $.post('user_action.php?action=update', $(this).serialize(), function(res){
      alert(res);
      location.reload();
    });
  });

  // ===============================
  // ✅ DELETE (ต้อง delegation เช่นกัน)
  // ===============================
  $(document).on('click', '.delBtn', function(){
    let id = $(this).data('id');
    if(confirm('แน่ใจว่าต้องการลบผู้ใช้นี้?')){
      $.post('user_action.php?action=delete',{id:id},function(res){
        alert(res);
        location.reload();
      });
    }
  });

});

// ===============================
// DARK MODE
// ===============================
function toggleDarkMode(){
  document.body.classList.toggle('dark-mode');
}
</script>

</body>
</html>
