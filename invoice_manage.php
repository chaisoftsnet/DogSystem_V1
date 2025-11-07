<?php
@session_start();
require_once('dbconnect.php');
if(!isset($_SESSION['user_id'])){ header("Location: index.php"); exit(); }

// รายการใบแจ้งหนี้
$invoices = mysqli_query($objCon,"SELECT i.*, u.fullname, d.dog_name, c.clinic_name
 FROM invoices i
 LEFT JOIN user u ON i.user_id=u.id
 LEFT JOIN dogs d ON i.dog_id=d.dog_id
 LEFT JOIN clinics c ON i.clinic_id=c.clinic_id
 ORDER BY i.invoice_id DESC");

// สำหรับ Modal สร้างบิล
$dogs     = mysqli_query($objCon,"SELECT d.dog_id, d.dog_name, u.fullname FROM dogs d LEFT JOIN user u ON d.user_id=u.id ORDER BY d.dog_name");
$products = mysqli_query($objCon,"SELECT * FROM products ORDER BY category, product_name");
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>🧾 ใบแจ้งหนี้ & ตัดสต๊อกอัตโนมัติ</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<style>
/* โหมดเทาอ่อนเป็นค่าเริ่มต้น */
:root{
  --bg-main:#e9ecef;         /* เทาอ่อนสบายตา */
  --card-bg:#ffffff;         /* กล่องขาว */
  --text-main:#111;          /* ตัวหนังสือดำ */
  --text-sub:#555;
  --thead-bg:#f1f3f5;        /* thead เทาอ่อน */
  --accent:#00bfa5;
}
body.dark-mode{
  --bg-main: #121212;
  --card-bg: #1e1e1e;       /* กล่องเทาดำ */
  --thead-bg: #2a2a2a;      /* header table */
  --text-main: #f5f5f5;     /* ตัวหนังสือขาวนวล */
  --text-sub: #aaa;
  --accent: #00e676;
}

body{
  background:var(--bg-main);
  color:var(--text-main);
  font-family:'Prompt',system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;
  transition:.25s;
}
.container-box{
  background:var(--card-bg);
  border-radius:16px;
  padding:24px;
  margin:40px auto;
  max-width:1200px;
  box-shadow:0 6px 18px rgba(0,0,0,.06);
}
.theme-toggle{
  position:fixed; top:15px; right:15px; width:44px; height:44px; border-radius:50%;
  display:flex; align-items:center; justify-content:center; background:var(--card-bg);
  border:1px solid rgba(0,0,0,.08); color:var(--text-main); cursor:pointer; z-index:9;
}
.table thead{ background:var(--thead-bg); color:var(--text-main); }
.table td, .table th { vertical-align: middle; }
.badge { font-size:.85rem; }

/* ให้ Modal อ่านง่ายทั้งสองโหมด */
.modal-content{ background:var(--card-bg); color:var(--text-main); }
.form-control, .form-select, .btn, .table, .modal-content { border-color: rgba(0,0,0,.12); }
a.btn-icon { padding:.35rem .55rem; }

.card { border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
.btn-add-row { background: linear-gradient(45deg, #00c853, #009624); color: white; border: none; }
.btn-add-row:hover { opacity: 0.9; }
.btn-del-row { color: red; }
.table td, .table th { vertical-align: middle; }
.toggle-dark { cursor: pointer; color: #00c853; font-size: 20px; }
</style>
</head>
<body>

<div class="theme-toggle" id="themeToggle" title="สลับโหมด"><i class="fa fa-moon"></i></div>

<div class="container container-box">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="m-0"><i class="fa fa-receipt"></i> ระบบใบแจ้งหนี้ / ใบเสร็จ</h3>
    <div class="d-flex gap-2">
      <a href="product_manage.php" class="btn btn-outline-info btn-sm"><i class="fa fa-warehouse"></i> คลังสินค้า</a>
      <a href="stock_ledger.php" class="btn btn-outline-warning btn-sm"><i class="fa fa-book"></i> สมุดรายวัน</a>
      <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addModal"><i class="fa fa-plus"></i> ออกใบแจ้งหนี้</button>
      <a href="invoice_dashboard.php" class="btn btn-secondary btn-sm"><i class="fa fa-home"></i> กลับหน้าหลัก</a>
    </div>
  </div>

  <table id="invTable" class="table table-striped table-hover text-center align-middle">
    <thead>
      <tr>
        <th>#</th>
        <th>เลขที่</th>
        <th>สุนัข</th>
        <th>คลินิก</th>
        <th>ยอดรวม</th>
        <th>สถานะ</th>
        <th>เมื่อ</th>
        <th>จัดการ</th>
      </tr>
    </thead>
    <tbody>
      <?php $i=1; while($r=mysqli_fetch_assoc($invoices)){ ?>
      <tr>
        <td><?=$i++;?></td>
        <td>INV-<?=$r['invoice_id']?></td>
        <td><?=htmlspecialchars($r['dog_name'])?></td>
        <td><?=htmlspecialchars($r['clinic_name'])?></td>
        <td><?=number_format($r['total_amount'],2)?></td>
        <td>
          <?php
            $color = $r['status']=='ชำระแล้ว'?'success':($r['status']=='ยกเลิก'?'danger':'warning');
          ?>
          <span class="badge bg-<?=$color?>"><?=$r['status']?></span>
        </td>
        <td><?=date('d/m/Y H:i', strtotime($r['invoice_date']))?></td>
        <td class="text-nowrap">
          <button class="btn btn-primary btn-sm viewBtn" data-id="<?=$r['invoice_id']?>" title="ดู/แก้ไข"><i class="fa fa-eye"></i></button>
          <a class="btn btn-info btn-sm" href="invoice_print.php?invoice_id=<?=$r['invoice_id']?>" target="_blank" title="พิมพ์"><i class="fa fa-print"></i></a>
          <button class="btn btn-danger btn-sm delInvBtn" data-id="<?=$r['invoice_id']?>" title="ลบทั้งใบ"><i class="fa fa-trash"></i></button>
        </td>
      </tr>
      <?php } ?>
    </tbody>
  </table>
</div>

<!-- Modal: สร้างใบแจ้งหนี้ -->
<div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form id="addForm" class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">➕ ออกใบแจ้งหนี้ใหม่</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3 mb-2">
          <div class="col-md-4">
            <label class="form-label">คลินิก</label>
            <select name="clinic_id" class="form-select">
              <?php
                $qc = mysqli_query($objCon,"SELECT clinic_id,clinic_name FROM clinics ORDER BY clinic_name");
                while($c=mysqli_fetch_assoc($qc)) echo "<option value='{$c['clinic_id']}'>{$c['clinic_name']}</option>";
              ?>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">สุนัข</label>
            <select name="dog_id" class="form-select">
              <?php while($d=mysqli_fetch_assoc($dogs)) echo "<option value='{$d['dog_id']}'>{$d['dog_name']} - {$d['fullname']}</option>"; ?>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">วิธีชำระ</label>
            <select name="payment_method" class="form-select">
              <option>เงินสด</option><option>โอน</option><option>บัตรเครดิต</option><option>PromptPay</option>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label">หมายเหตุ</label>
            <input type="text" name="note" class="form-control">
          </div>
        </div>

        <hr>
        <h6 class="mb-2">รายการสินค้า/บริการ</h6>
        <table class="table table-bordered text-center align-middle">
          <thead class="table-light">
            <tr><th style="width:35%">สินค้า</th><th style="width:15%">จำนวน</th><th style="width:20%">ราคา/หน่วย</th><th style="width:20%">รวม</th><th style="width:10%"></th></tr>
          </thead>
          <tbody id="itemTable">
            <tr>
              <td>
                <select class="form-select productSel">
                  <option value="">-- เลือก --</option>
                  <?php mysqli_data_seek($products,0); while($p=mysqli_fetch_assoc($products)){ 
                    echo "<option data-price='{$p['unit_price']}' value='{$p['product_id']}'>{$p['product_name']}</option>";
                  } ?>
                </select>
              </td>
              <td><input type="number" class="form-control qty" value="1" min="1"></td>
              <td><input type="number" class="form-control price" step="0.01" value="0.00"></td>
              <td><input type="text" class="form-control total" value="0.00" readonly></td>
              <td><button class="btn btn-danger btn-sm removeRow" type="button"><i class="fa fa-trash"></i></button></td>
            </tr>
          </tbody>
        </table>
        <button type="button" id="addRow" class="btn btn-outline-success btn-sm"><i class="fa fa-plus"></i> เพิ่มแถว</button>
        <div class="text-end mt-3"><b>ยอดรวม: <span id="grandTotal">0.00</span> บาท</b></div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-success">บันทึกใบแจ้งหนี้</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal: ดู/แก้ไข -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content" id="viewBody"></div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script>
// โหมด
(function(){
  const saved = localStorage.getItem('themeMode');
  if(saved==='dark') document.body.classList.add('dark-mode');
})();
document.getElementById('themeToggle').onclick = function(){
  document.body.classList.toggle('dark-mode');
  const mode = document.body.classList.contains('dark-mode')?'dark':'light';
  localStorage.setItem('themeMode', mode);
};

// DataTable + Delegation
$(function(){
  const dt = $('#invTable').DataTable({
    language:{search:"ค้นหา:",paginate:{previous:"ก่อนหน้า",next:"ถัดไป"}},
    pageLength:10
  });

  // ฟังก์ชันคำนวน
  function recalcRow($tr){
    let q = parseFloat($tr.find('.qty').val()||0);
    let p = parseFloat($tr.find('.price').val()||0);
    $tr.find('.total').val((q*p).toFixed(2));
  }
  function recalcGrand(){
    let g=0; $('#itemTable .total').each(function(){ g += parseFloat($(this).val()||0); });
    $('#grandTotal').text(g.toFixed(2));
  }

  // Add form events
  $(document).on('change','.productSel',function(){
    let price = $(this).find(':selected').data('price')||0;
    let $tr = $(this).closest('tr');
    $tr.find('.price').val(price);
    recalcRow($tr); recalcGrand();
  });
  $(document).on('input','.qty,.price',function(){
    let $tr = $(this).closest('tr'); recalcRow($tr); recalcGrand();
  });
  $('#addRow').click(function(){
    let $first = $('#itemTable tr:first').clone();
    $first.find('select').val('');
    $first.find('.qty').val(1);
    $first.find('.price').val('0.00');
    $first.find('.total').val('0.00');
    $('#itemTable').append($first);
  });
  $(document).on('click','.removeRow',function(){ $(this).closest('tr').remove(); recalcGrand(); });

  // บันทึกบิล (ยังไม่ตัดสต๊อก จนกว่าจะเปลี่ยนสถานะเป็น "ชำระแล้ว")
  $('#addForm').submit(async function(e){
    e.preventDefault();
    const fd = new FormData(this);
    fd.append('action','add_invoice');
    const r = await fetch('invoice_action.php',{method:'POST', body:fd});
    const t = await r.text();
    if(!t.startsWith('OK|')){ alert(t); return; }
    const invoice_id = t.split('|')[1];

    // ส่งรายการ
    const clinic_id = this.clinic_id.value;
    let rows = document.querySelectorAll('#itemTable tr');
    for(let tr of rows){
      const sel = tr.querySelector('.productSel'); if(!sel || !sel.value) continue;
      const pid = sel.value;
      const name = sel.options[sel.selectedIndex].text;
      const qty  = tr.querySelector('.qty').value||1;
      const price= tr.querySelector('.price').value||0;
      const fd2 = new FormData();
      fd2.append('action','add_item');
      fd2.append('invoice_id',invoice_id);
      fd2.append('product_id',pid);
      fd2.append('description',name);
      fd2.append('quantity',qty);
      fd2.append('unit_price',price);
      fd2.append('clinic_id',clinic_id);
      await fetch('invoice_action.php',{method:'POST', body:fd2});
    }
    alert('บันทึกใบแจ้งหนี้เรียบร้อย (สถานะ: รอชำระ) — ระบบจะตัดสต๊อกเมื่อเปลี่ยนเป็น “ชำระแล้ว”');
    location.reload();
  });

  // Delegation: เปิดดู/แก้ไข
  $(document).on('click','.viewBtn', async function(){
    const id = this.dataset.id;
    const html = await (await fetch('invoice_action.php?action=fetch_invoice&invoice_id='+id)).text();
    $('#viewBody').html(html);
    $('#viewModal').modal('show');
  });

  // ลบทั้งบิล
  $(document).on('click','.delInvBtn', async function(){
    if(!confirm('ลบใบแจ้งหนี้ทั้งใบ (และคืนสต๊อกหากชำระแล้ว)?'))return;
    const fd = new FormData(); fd.append('action','delete_invoice'); fd.append('invoice_id',this.dataset.id); fd.append('clinic_id',1);
    const t = await (await fetch('invoice_action.php',{method:'POST',body:fd})).text();
    alert(t); location.reload();
  });

  // ใน Modal (delegation)
  // ลบรายการ
  $(document).on('click','.delItemBtn', async function(){
    if(!confirm('ลบรายการนี้?'))return;
    const item_id = this.dataset.id;
    const invoice_id = this.dataset.invoice;
    const fd = new FormData();
    fd.append('action','delete_item');
    fd.append('item_id',item_id);
    fd.append('clinic_id',1);
    const t = await (await fetch('invoice_action.php',{method:'POST',body:fd})).text();
    alert(t);
    const html = await (await fetch('invoice_action.php?action=fetch_invoice&invoice_id='+invoice_id)).text();
    $('#viewBody').html(html);
  });

  // เพิ่มรายการใหม่ (ในบิลเดิม)
  $(document).on('click','#addItemExisting', async function(){
    const wrap = document.getElementById('addItemWrap');
    const invoice_id = this.dataset.invoice;
    const clinic_id  = this.dataset.clinic;
    // เก็บค่าจากแถว
    const pid   = wrap.querySelector('[name="product_id"]').value;
    const qty   = wrap.querySelector('[name="quantity"]').value || 1;
    const price = wrap.querySelector('[name="unit_price"]').value || 0;

    if(!pid){ alert('กรุณาเลือกสินค้า'); return; }

    const fd = new FormData();
    fd.append('action','add_item_existing');
    fd.append('invoice_id',invoice_id);
    fd.append('product_id',pid);
    fd.append('quantity',qty);
    fd.append('unit_price',price);
    fd.append('clinic_id',clinic_id);
    const t = await (await fetch('invoice_action.php',{method:'POST',body:fd})).text();
    if(t!=='OK'){ alert(t); return; }

    const html = await (await fetch('invoice_action.php?action=fetch_invoice&invoice_id='+invoice_id)).text();
    $('#viewBody').html(html);
  });

  // แก้ qty/price ของรายการ
  $(document).on('change','.itemQty,.itemPrice', async function(){
    const tr = this.closest('tr');
    const item_id = tr.dataset.id;
    const invoice_id = tr.dataset.invoice;
    const qty = tr.querySelector('.itemQty').value;
    const price = tr.querySelector('.itemPrice').value;
    const fd = new FormData();
    fd.append('action','update_invoice_item');
    fd.append('item_id',item_id);
    fd.append('quantity',qty);
    fd.append('unit_price',price);
    const t = await (await fetch('invoice_action.php',{method:'POST',body:fd})).text();
    if(t!=='OK'){ alert(t); return; }
    const html = await (await fetch('invoice_action.php?action=fetch_invoice&invoice_id='+invoice_id)).text();
    $('#viewBody').html(html);
  });

  // เปลี่ยนสถานะบิล
  $(document).on('change','#invoiceStatus', async function(){
    const invoice_id = this.dataset.invoice;
    const newStatus  = this.value;
    const fd = new FormData();
    fd.append('action','update_invoice_status');
    fd.append('invoice_id',invoice_id);
    fd.append('status',newStatus);
    fd.append('clinic_id',1);
    const t = await (await fetch('invoice_action.php',{method:'POST',body:fd})).text();
    alert(t);
    // reload modal + ตาราง
    const html = await (await fetch('invoice_action.php?action=fetch_invoice&invoice_id='+invoice_id)).text();
    $('#viewBody').html(html);
    $('#invTable').DataTable().ajax?.reload?.();
  });
});
</script>
</body>
</html>
