<?php
@session_start();
require_once('dbconnect.php');

if (!isset($_SESSION['user_id'])) {
  header("Location: index.php");
  exit();
}

$aStatus = ['รอชำระ'=>'warning','ชำระแล้ว'=>'success','ยกเลิก'=>'secondary'];

$sql = "
  SELECT i.*, u.fullname, d.dog_name, c.clinic_name
  FROM invoices i
  LEFT JOIN user u ON i.user_id = u.id
  LEFT JOIN dogs d ON i.dog_id = d.dog_id
  LEFT JOIN clinics c ON i.clinic_id = c.clinic_id
  ORDER BY i.invoice_date DESC";
$q = mysqli_query($objCon, $sql);
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>💰 จัดการใบแจ้งหนี้ | ระบบคลินิกรักษาสัตว์</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<style>
body { font-family: 'Prompt', sans-serif; background-color: #f8f9fa; transition: 0.3s; }
.dark-mode { background-color: #121212; color: #f1f1f1; }
.card { border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
.btn-add { background: linear-gradient(45deg, #00c853, #009624); color: white; border: none; }
.btn-add:hover { opacity: 0.9; }
.toggle-dark { cursor: pointer; color: #00c853; font-size: 20px; }
.table td, .table th { vertical-align: middle; }
.status-badge { padding: 6px 12px; border-radius: 8px; font-weight: 500; }
</style>
</head>
<link rel="stylesheet" href="css/theme.css">
<script src="js/theme.js"></script>
<body>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>💰 จัดการใบแจ้งหนี้ / ใบเสร็จ</h3>
    <div>
      <i class="fa fa-moon toggle-dark me-3" onclick="toggleDarkMode()"></i>
      <a href="invoice_dashboard.php" class="btn btn-secondary btn-sm"><i class="fa fa-home"></i> หน้าหลัก</a>
    </div>
  </div>

  <div class="card p-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="mb-0">รายการใบแจ้งหนี้ทั้งหมด</h5>
      <a href="invoice_add.php" class="btn btn-add btn-sm"><i class="fa fa-plus-circle"></i> ออกใบแจ้งหนี้ใหม่</a>
    </div>

    <table id="invoiceTable" class="table table-bordered table-striped text-center align-middle">
      <thead class="table-dark">
        <tr>
          <th>#</th>
          <th>วันที่</th>
          <th>ลูกค้า</th>
          <th>สุนัข</th>
          <th>ยอดรวม</th>
          <th>สถานะ</th>
          <th>ช่องทางชำระ</th>
          <th>คลินิก</th>
          <th>จัดการ</th>
        </tr>
      </thead>
      <tbody>
        <?php 
        $i=1;
        while($r=mysqli_fetch_assoc($q)){
          $badge = $aStatus[$r['status']] ?? 'secondary';
          echo "
          <tr>
            <td>$i</td>
            <td>".date('d/m/Y H:i', strtotime($r['invoice_date']))."</td>
            <td>{$r['fullname']}</td>
            <td>{$r['dog_name']}</td>
            <td class='text-end'>".number_format($r['total_amount'],2)."</td>
            <td><span class='badge bg-$badge'>{$r['status']}</span></td>
            <td>{$r['payment_method']}</td>
            <td>{$r['clinic_name']}</td>
            <td>
              <a href='invoice_print.php?invoice_id={$r['invoice_id']}' target='_blank' class='btn btn-success btn-sm'><i class='fa fa-print'></i></a>
              <button class='btn btn-warning btn-sm editBtn' data-id='{$r['invoice_id']}'><i class='fa fa-pen'></i></button>
              <button class='btn btn-danger btn-sm delBtn' data-id='{$r['invoice_id']}'><i class='fa fa-trash'></i></button>
            </td>
          </tr>";
          $i++;
        }
        ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal แก้ไข -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="editForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">✏️ แก้ไขสถานะใบแจ้งหนี้</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="invoice_id" id="edit_invoice_id">
        <label>สถานะใหม่</label>
        <select name="status" class="form-select">
          <option value="รอชำระ">รอชำระ</option>
          <option value="ชำระแล้ว">ชำระแล้ว</option>
          <option value="ยกเลิก">ยกเลิก</option>
        </select>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">บันทึก</button>
      </div>
    </form>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<script>
$(function(){

  // ✅ ตั้งค่า DataTable
  const table = $('#invoiceTable').DataTable({
    language: {
      lengthMenu: "แสดง _MENU_ รายการต่อหน้า",
      zeroRecords: "ไม่พบข้อมูล",
      info: "หน้า _PAGE_ จาก _PAGES_",
      search: "ค้นหา:",
      paginate: { previous: "ก่อนหน้า", next: "ถัดไป" }
    },
    order: [[1, "desc"]]
  });

  // ✅ เปิด Modal แก้ไขสถานะ (ใช้ event delegation)
  $(document).on('click', '.editBtn', function(){
    const id = $(this).data('id');
    $('#edit_invoice_id').val(id);
    $('#editModal').modal('show');
  });

  // ✅ เมื่อกดบันทึกใน Modal แก้ไข
  $('#editForm').on('submit', function(e){
    e.preventDefault();
    $.ajax({
      url: 'invoice_action.php?action=update',
      type: 'POST',
      data: $(this).serialize(),
      beforeSend: function(){
        $('#editForm button[type="submit"]').prop('disabled', true).text('⏳ กำลังบันทึก...');
      },
      success: function(res){
        alert(res);
        $('#editModal').modal('hide');
        location.reload();
      },
      error: function(){
        alert('❌ เกิดข้อผิดพลาดในการอัปเดตข้อมูล');
      },
      complete: function(){
        $('#editForm button[type="submit"]').prop('disabled', false).text('บันทึก');
      }
    });
  });

  // ✅ ลบใบแจ้งหนี้ (ใช้ event delegation)
  $(document).on('click', '.delBtn', function(){
    const id = $(this).data('id');
    if(confirm('แน่ใจว่าต้องการลบใบแจ้งหนี้นี้?')){
      $.ajax({
        url: 'invoice_action.php?action=delete',
        type: 'POST',
        data: {id: id},
        success: function(res){
          alert(res);
          location.reload();
        },
        error: function(){
          alert('❌ ไม่สามารถลบข้อมูลได้');
        }
      });
    }
  });

  // ✅ ปุ่มพิมพ์ (เปิดในแท็บใหม่)
  $(document).on('click', '.printBtn', function(){
    const id = $(this).data('id');
    window.open('invoice_print.php?invoice_id=' + id, '_blank');
  });

});

// ✅ Dark Mode Toggle
function toggleDarkMode(){
  document.body.classList.toggle('dark-mode');
}
</script>

</body>
</html>
