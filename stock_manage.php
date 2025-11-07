<?php
@session_start();
require_once('dbconnect.php');
if(!isset($_SESSION['user_id'])) {
  header("Location: index.php");
  exit();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>💊 ระบบจัดการคลังยาและวัคซีน</title>

<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<style>
:root {
  --bg-dark: radial-gradient(circle at top, #1b2735 0%, #090a0f 80%);
  --card-bg: rgba(255,255,255,0.08);
  --text-main: #fff;
  --text-sub: #aaa;
  --accent: #00e676;
}
body.light-mode {
  --bg-dark: linear-gradient(150deg, #f2f6fa 0%, #e8f5e9 100%);
  --card-bg: rgba(255,255,255,0.95);
  --text-main: #222;
  --text-sub: #555;
  --accent: #00bfa5;
}
body {
  font-family: 'Prompt', sans-serif;
  background: var(--bg-dark);
  color: var(--text-main);
  transition: 0.3s;
}
.theme-toggle {
  position: fixed;
  top: 15px; right: 15px;
  background: var(--card-bg);
  border: 1px solid rgba(255,255,255,0.3);
  color: var(--text-main);
  border-radius: 50%;
  width: 45px; height: 45px;
  display: flex; justify-content: center; align-items: center;
  cursor: pointer;
  z-index: 999;
}
.container-box {
  background: var(--card-bg);
  border-radius: 15px;
  padding: 25px;
  margin-top: 90px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.2);
}
.table th, .table td { vertical-align: middle; }
</style>
</head>

<body>
<div class="theme-toggle" onclick="toggleTheme()"><i class="fa fa-moon"></i></div>

<div class="container container-box">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3><i class="fa-solid fa-pills"></i> ระบบจัดการคลังยาและวัคซีน</h3>
    <div>
      <button class="btn btn-success btn-sm" id="addNewBtn"><i class="fa fa-plus-circle"></i> เพิ่มสินค้าใหม่</button>
      <a href="stock_ledger.php" class="btn btn-info btn-sm"><i class="fa fa-print"></i> พิมพ์รายงานรวมคลัง</a>
      <a href="purchase_dashboard.php" class="btn btn-secondary btn-sm"><i class="fa fa-home"></i> กลับหน้าหลัก</a>
    </div>
  </div>

  <table id="stockTable" class="table table-striped table-hover text-center">
    <thead class="table-dark">
      <tr>
        <th>#</th>
        <th>ชื่อสินค้า</th>
        <th>หมวดหมู่</th>
        <th>ราคาขาย (บาท)</th>
        <th>คงเหลือ</th>
        <th>จุดสั่งซื้อซ้ำ</th>
        <th>สถานะ</th>
        <th>จัดการ</th>
      </tr>
    </thead>
    <tbody>
    <?php
      $sql = "SELECT * FROM products ORDER BY category, product_name ASC";
      $q = mysqli_query($objCon, $sql);
      $i=1;
      while($r = mysqli_fetch_assoc($q)) {
        $low = ($r['stock_qty'] <= $r['reorder_point']);
        $badge = $low ? "<span class='badge bg-danger'>⚠️ เหลือน้อย</span>" : "<span class='badge bg-success'>เพียงพอ</span>";
        echo "
        <tr ".($low ? "style='background:rgba(255,0,0,0.08)'" : "").">
          <td>{$i}</td>
          <td>
            <a href='stock_ledger.php?product_id={$r['product_id']}' 
               class='text-decoration-none fw-bold'
               style='color: var(--accent);'>
               {$r['product_name']}
            </a>
          </td>
          <td>{$r['category']}</td>
          <td>".number_format($r['unit_price'],2)."</td>
          <td>{$r['stock_qty']}</td>
          <td>{$r['reorder_point']}</td>
          <td>{$badge}</td>
          <td>
            <button class='btn btn-success btn-sm addBtn' data-id='{$r['product_id']}'><i class='fa fa-plus'></i></button>
            <button class='btn btn-warning btn-sm outBtn' data-id='{$r['product_id']}'><i class='fa fa-minus'></i></button>
            <button class='btn btn-primary btn-sm editBtn' data-id='{$r['product_id']}'><i class='fa fa-pen'></i></button>
            <button class='btn btn-danger btn-sm delBtn' data-id='{$r['product_id']}'><i class='fa fa-trash'></i></button>
          </td>
        </tr>";
        $i++;
      }
    ?>
    </tbody>
  </table>
</div>

<!-- 🟩 Modal เพิ่มสินค้าใหม่ -->
<div class="modal fade" id="addModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="addForm" class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">➕ เพิ่มสินค้าใหม่</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label>ชื่อสินค้า</label>
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
          <label>ราคาขาย (บาท)</label>
          <input type="number" step="0.01" name="unit_price" class="form-control" required>
        </div>
        <div class="mb-3">
          <label>จุดสั่งซื้อซ้ำ</label>
          <input type="number" name="reorder_point" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">💾 บันทึกสินค้าใหม่</button>
      </div>
    </form>
  </div>
</div>

<!-- 🔹 Modal แก้ไขสินค้า -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="editForm" class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">🖊️ แก้ไขข้อมูลสินค้า</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="product_id" id="edit_id">
        <div class="mb-3">
          <label>ชื่อสินค้า</label>
          <input type="text" name="product_name" id="edit_name" class="form-control" required>
        </div>
        <div class="mb-3">
          <label>หมวดหมู่</label>
          <select name="category" id="edit_category" class="form-select">
            <option value="ยา">ยา</option>
            <option value="วัคซีน">วัคซีน</option>
            <option value="บริการ">บริการ</option>
            <option value="อื่นๆ">อื่นๆ</option>
          </select>
        </div>
        <div class="mb-3">
          <label>ราคาขาย (บาท)</label>
          <input type="number" step="0.01" name="unit_price" id="edit_price" class="form-control" required>
        </div>
        <div class="mb-3">
          <label>จุดสั่งซื้อซ้ำ</label>
          <input type="number" name="reorder_point" id="edit_reorder" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">💾 บันทึกการแก้ไข</button>
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
  $('#stockTable').DataTable({
    language:{ search:"ค้นหา:", paginate:{ previous:"ก่อนหน้า", next:"ถัดไป" } }
  });

  // 🌙 เพิ่มสินค้าใหม่
  $('#addNewBtn').click(function(){
    $('#addModal').modal('show');
  });

  $('#addForm').submit(function(e){
    e.preventDefault();
    $.post('stock_action.php?action=add', $(this).serialize(), function(res){
      alert(res);
      location.reload();
    });
  });

  // 🔧 แก้ไขสินค้า
  $(document).on('click', '.editBtn', function() {
    let id = $(this).data('id');
    $.ajax({
      url: 'stock_action.php',
      method: 'POST',
      data: { action_type:'FETCH', product_id:id },
      dataType: 'json',
      success: function(data){
        if(data.error){ alert(data.error); return; }
        $('#edit_id').val(data.product_id);
        $('#edit_name').val(data.product_name);
        $('#edit_category').val(data.category);
        $('#edit_price').val(data.unit_price);
        $('#edit_reorder').val(data.reorder_point);
        $('#editModal').modal('show');
      },
      error: function(xhr){
        alert("เกิดข้อผิดพลาดในการโหลดข้อมูลสินค้า");
        console.error(xhr.responseText);
      }
    });
  });

  $('#editForm').submit(function(e){
    e.preventDefault();
    $.post('stock_action.php', $(this).serialize() + '&action_type=UPDATE', function(res){
      alert(res);
      location.reload();
    });
  });

  // 🟩 ปรับจำนวนเข้า / ออก
  $('.addBtn, .outBtn').click(function(){
    let id = $(this).data('id');
    let action = $(this).hasClass('addBtn') ? 'IN' : 'OUT';
    let qty = prompt("กรอกจำนวนที่จะ" + (action=='IN' ? 'เพิ่ม' : 'เบิกออก') + ":");
    if(qty && qty > 0){
      $.post('stock_action.php', { action_type:action, product_id:id, quantity:qty, note:'' }, function(res){
        alert(res); location.reload();
      });
    }
  });

  // 🗑️ ลบสินค้า
  $('.delBtn').click(function(){
    if(confirm('ต้องการลบสินค้านี้หรือไม่?')){
      $.post('stock_action.php', { action_type:'DELETE', product_id:$(this).data('id') }, function(res){
        alert(res);
        location.reload();
      });
    }
  });
});

function toggleTheme(){
  document.body.classList.toggle('light-mode');
  const icon = document.querySelector('.theme-toggle i');
  icon.classList.toggle('fa-sun');
  icon.classList.toggle('fa-moon');
}
</script>
</body>
</html>
