<?php
@session_start();
require_once('dbconnect.php');
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }

// ดึง Supplier / Product สำหรับใช้ใน Modal เพิ่ม
$suppliers = mysqli_query($objCon, "SELECT * FROM suppliers ORDER BY supplier_name ASC");
$products  = mysqli_query($objCon, "SELECT * FROM products ORDER BY product_name ASC");
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>📄 ระบบใบสั่งซื้อ (Purchase Order Management)</title>

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
  --text-main: #222; --text-sub: #555; --accent: #00bfa5;
}
body { font-family: 'Prompt', sans-serif; background: var(--bg-dark); color: var(--text-main); transition: .3s; }
.theme-toggle { position: fixed; top: 15px; right: 15px; background: var(--card-bg); border: 1px solid rgba(255,255,255,0.3);
  color: var(--text-main); border-radius: 50%; width: 45px; height:45px; display:flex; justify-content:center; align-items:center; cursor:pointer; z-index:999; }
.container-box { background: var(--card-bg); border-radius: 15px; padding: 25px; margin-top: 90px; box-shadow: 0 4px 20px rgba(0,0,0,0.2); }
.table th, .table td { vertical-align: middle; }
.badge.rounded-pill { padding:.5em .7em; }
.btn-main { background: linear-gradient(45deg, #00e676, #00bfa5); border: none; color: #000; font-weight: bold; }
.btn-main:hover{opacity:.9}

/* Dark mode for modals & tables */
body:not(.light-mode) .modal-content{ background:#1e1e1e; color:#f1f1f1; border:1px solid #333; }
body:not(.light-mode) .modal-header{ background:#2a2a2a; border-bottom:1px solid #444;}
body:not(.light-mode) .modal-footer{ background:#2a2a2a; border-top:1px solid #444;}
body:not(.light-mode) input, body:not(.light-mode) select, body:not(.light-mode) textarea{ background:#2b2b2b; color:#fff; border:1px solid #555; }
body:not(.light-mode) .table thead { background:#333; color:#fff;}
</style>
</head>
<body>
<div class="theme-toggle" onclick="toggleTheme()"><i class="fa fa-moon"></i></div>

<div class="container container-box">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="m-0"><i class="fa-solid fa-file-invoice-dollar"></i> ระบบใบสั่งซื้อยาและวัคซีน</h3>
    <div class="d-flex gap-2">
      <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
        <i class="fa fa-plus-circle"></i> เพิ่มใบสั่งซื้อ
      </button>
      <a href="purchase_dashboard.php" class="btn btn-secondary btn-sm"><i class="fa fa-home"></i> กลับ Dashboard</a>
    </div>
  </div>

  <table id="poTable" class="table table-striped table-hover text-center">
    <thead class="table-dark">
      <tr>
        <th>#</th>
        <th>รหัส</th>
        <th>ผู้จำหน่าย</th>
        <th>วันที่สั่งซื้อ</th>
        <th>ยอดรวม (บาท)</th>
        <th>สถานะ</th>
        <th>จัดการ</th>
      </tr>
    </thead>
    <tbody>
      <?php
        $sql = "SELECT p.*, s.supplier_name
                FROM purchase_orders p
                LEFT JOIN suppliers s ON p.supplier_id = s.supplier_id
                ORDER BY p.po_id DESC";
        $q = mysqli_query($objCon, $sql);
        $i=1;
        while($r = mysqli_fetch_assoc($q)){
          $status = $r['status'];
          $statusColor = [
            'รออนุมัติ'=>'warning','สั่งซื้อแล้ว'=>'info','ได้รับของแล้ว'=>'success','ยกเลิก'=>'danger'
          ][$status] ?? 'secondary';

          $editDisabled   = ($status!=='รออนุมัติ') ? 'disabled' : '';
          $approveDisabled= ($status!=='รออนุมัติ') ? 'disabled' : '';
          $receiveDisabled= ($status!=='สั่งซื้อแล้ว') ? 'disabled' : '';

          echo "<tr>
            <td>{$i}</td>
            <td>PO-{$r['po_id']}</td>
            <td>{$r['supplier_name']}</td>
            <td>".date('d/m/Y H:i', strtotime($r['po_date']))."</td>
            <td>".number_format($r['total_amount'],2)."</td>
            <td><span class='badge rounded-pill bg-{$statusColor}'>{$status}</span></td>
            <td class='d-flex gap-1 justify-content-center'>
              <button class='btn btn-primary btn-sm viewBtn' data-id='{$r['po_id']}' title='ดูรายละเอียด'><i class='fa fa-eye'></i></button>

              <button class='btn btn-warning btn-sm editBtn' data-id='{$r['po_id']}' title='แก้ไข' ".($editDisabled ? 'disabled' : '').">
                <i class='fa fa-pen'></i>
              </button>

              <button class='btn btn-info btn-sm approveBtn' data-id='{$r['po_id']}' title='อนุมัติ/สั่งซื้อ' ".($approveDisabled ? 'disabled' : '').">
                <i class='fa fa-check'></i>
              </button>

              <button class='btn btn-success btn-sm receiveBtn' data-id='{$r['po_id']}' title='ได้รับของแล้ว' ".($receiveDisabled ? 'disabled' : '').">
                <i class='fa fa-box-open'></i>
              </button>

              <a class='btn btn-secondary btn-sm' href='purchase_order_print.php?po_id={$r['po_id']}' target='_blank' title='พิมพ์ใบสั่งซื้อ'>
                <i class='fa fa-print'></i>
              </a>

              <button class='btn btn-danger btn-sm delBtn' data-id='{$r['po_id']}' title='ลบ' ".($status==='ได้รับของแล้ว'?'disabled':'').">
                <i class='fa fa-trash'></i>
              </button>
            </td>
          </tr>";
          $i++;
        }
      ?>
    </tbody>
  </table>
</div>

<!-- Modal เพิ่มใบสั่งซื้อ -->
<div class="modal fade" id="addModal" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <form id="addForm" class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">➕ เพิ่มใบสั่งซื้อใหม่</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label class="form-label">เลือกผู้จำหน่าย (Supplier)</label>
            <select name="supplier_id" class="form-select" required>
              <option value="">-- เลือก --</option>
              <?php while($s=mysqli_fetch_assoc($suppliers)) echo "<option value='{$s['supplier_id']}'>{$s['supplier_name']}</option>"; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">หมายเหตุ</label>
            <input type="text" name="note" class="form-control">
          </div>
        </div>

        <table class="table table-bordered text-center align-middle">
          <thead class="table-light">
            <tr>
              <th>สินค้า</th>
              <th>จำนวน</th>
              <th>ราคาต่อหน่วย</th>
              <th>รวม</th>
              <th></th>
            </tr>
          </thead>
          <tbody id="itemTable">
            <tr>
              <td>
                <select name="product_id[]" class="form-select" required>
                  <option value="">-- เลือกสินค้า --</option>
                  <?php
                    mysqli_data_seek($products, 0);
                    while($p=mysqli_fetch_assoc($products)) echo "<option value='{$p['product_id']}'>{$p['product_name']}</option>";
                  ?>
                </select>
              </td>
              <td><input type="number" name="quantity[]" class="form-control text-center" value="1" min="1"></td>
              <td><input type="number" step="0.01" name="unit_cost[]" class="form-control text-center" value="0.00"></td>
              <td><input type="text" class="form-control text-center total" readonly value="0.00"></td>
              <td><button type="button" class="btn btn-danger btn-sm removeRow"><i class="fa fa-trash"></i></button></td>
            </tr>
          </tbody>
        </table>
        <button type="button" id="addRow" class="btn btn-outline-success btn-sm"><i class="fa fa-plus"></i> เพิ่มสินค้า</button>
        <hr>
        <div class="text-end">
          <strong>ยอดรวมทั้งหมด: <span id="grandTotal">0.00</span> บาท</strong>
        </div>
      </div>

      <div class="modal-footer">
        <button type="submit" class="btn btn-success">บันทึกใบสั่งซื้อ</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal ดู/แก้ไข -->
<div class="modal fade" id="viewModal" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content" id="viewBody"></div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<script>
function toggleTheme(){
  document.body.classList.toggle('light-mode');
  const i = document.querySelector('.theme-toggle i');
  i.classList.toggle('fa-sun'); i.classList.toggle('fa-moon');
}

$(function(){
  $('#poTable').DataTable({
    language:{ search:"ค้นหา:", paginate:{ previous:"ก่อนหน้า", next:"ถัดไป" } },
    pageLength: 10
  });

  function calcTotal(){
    let total = 0;
    $('#itemTable tr').each(function(){
      let qty   = parseFloat($(this).find('input[name="quantity[]"]').val()) || 0;
      let price = parseFloat($(this).find('input[name="unit_cost[]"]').val()) || 0;
      let sum = qty * price;
      $(this).find('.total').val(sum.toFixed(2));
      total += sum;
    });
    $('#grandTotal').text(total.toFixed(2));
  }
  $(document).on('input','input[name="quantity[]"],input[name="unit_cost[]"]',calcTotal);

  $('#addRow').click(function(){
    let $first = $('#itemTable tr:first').clone();
    $first.find('select').val('');
    $first.find('input').val('');
    $first.find('.total').val('0.00');
    $('#itemTable').append($first);
  });
  $(document).on('click','.removeRow',function(){
    if($('#itemTable tr').length>1){ $(this).closest('tr').remove(); calcTotal(); }
  });

  // Create
  $('#addForm').submit(function(e){
    e.preventDefault();
    $.ajax({
      url:'purchase_order_action.php?action=add',
      type:'POST',
      data: $(this).serialize(),
      success: function(res){ alert(res); location.reload(); }
    });
  });

  // View
  $('.viewBtn').click(function(){
    const id = $(this).data('id');
    $('#viewBody').load('purchase_order_action.php?action=view&id='+id, function(){
      $('#viewModal').modal('show');
    });
  });

  // Edit (แบบเพิ่มแถวได้)
  $('.editBtn').click(function(){
    const id = $(this).data('id');
    $('#viewBody').load('purchase_order_action.php?action=editform&id='+id, function(){
      $('#viewModal').modal('show');
    });
  });

  // Approve
  $('.approveBtn').click(function(){
    if(!confirm('ยืนยันการอนุมัติใบสั่งซื้อฉบับนี้?')) return;
    const id = $(this).data('id');
    $.post('purchase_order_action.php?action=approve', { id }, function(res){
      alert(res); location.reload();
    });
  });

  // Receive (update stock)
  $('.receiveBtn').click(function(){
    if(!confirm('ยืนยันว่ารับของครบถ้วนและต้องการนำเข้า Stock?')) return;
    const id = $(this).data('id');
    $.post('purchase_order_action.php?action=receive', { id }, function(res){
      alert(res); location.reload();
    });
  });

  // Delete
  $('.delBtn').click(function(){
    if(!confirm('ต้องการลบใบสั่งซื้อนี้หรือไม่?')) return;
    const id = $(this).data('id');
    $.post('purchase_order_action.php?action=delete', { id }, function(res){
      alert(res); location.reload();
    });
  });
});
</script>
</body>
</html>
