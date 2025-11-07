<?php
@session_start();
require_once('dbconnect.php');

if(!isset($_SESSION['user_id'])) {
  header("Location: index.php");
  exit();
}

$po_id = isset($_GET['po_id']) ? (int)$_GET['po_id'] : 0;
if($po_id <= 0){
  die("ไม่พบรหัสใบสั่งซื้อ (po_id)");
}

// ดึงหัวข้อมูลใบสั่งซื้อ + ซัพพลายเออร์ + คลินิก
$sqlHead = "
SELECT p.*, s.supplier_name, s.contact_name AS contact_person, s.phone, s.email, s.address AS supplier_address,
       c.clinic_name, c.address AS clinic_address, c.phone AS clinic_phone, c.email AS clinic_email
FROM purchase_orders p
LEFT JOIN suppliers s ON p.supplier_id = s.supplier_id
LEFT JOIN clinics c   ON p.clinic_id   = c.clinic_id
WHERE p.po_id = $po_id
LIMIT 1";
$headRes = mysqli_query($objCon, $sqlHead);
$H = mysqli_fetch_assoc($headRes);
if(!$H){ die("ไม่พบบันทึกใบสั่งซื้อที่ระบุ"); }

// ดึงรายการสินค้า
$sqlItems = "
SELECT i.*, pr.product_name, pr.category
FROM purchase_order_items i
LEFT JOIN products pr ON i.product_id = pr.product_id
WHERE i.po_id = $po_id
ORDER BY i.item_id ASC";
$itemsRes = mysqli_query($objCon, $sqlItems);

// รวมยอด (เผื่อข้อมูล head ยังไม่อัปเดต)
$grand = 0.00;
$rows  = [];
while($row = mysqli_fetch_assoc($itemsRes)){
  $row['total_cost_calc'] = (float)$row['quantity'] * (float)$row['unit_cost'];
  $grand += $row['total_cost_calc'];
  $rows[] = $row;
}

// ถ้า head.total_amount ยังเป็น 0 ให้แสดงตาม grand ที่คำนวณ
$total_amount = (float)$H['total_amount'];
if($total_amount <= 0) $total_amount = $grand;

// สีแถบสถานะ
$statusColor = [
  'รออนุมัติ'   => '#f59f00', // ส้ม
  'สั่งซื้อแล้ว' => '#0dcaf0', // ฟ้า
  'ได้รับของแล้ว'=> '#28a745', // เขียว
  'ยกเลิก'      => '#dc3545'  // แดง
];
$badgeColor = $statusColor[$H['status']] ?? '#6c757d';
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ใบสั่งซื้อยาและวัคซีน | PO-<?=htmlspecialchars($po_id)?></title>

<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<style>
:root{
  --bg: radial-gradient(circle at top, #1b2735 0%, #090a0f 80%);
  --panel: rgba(255,255,255,0.08);
  --text: #e9f5ef;
  --text-sub:#a7c5bc;
  --accent:#22d3ee; /* ฟ้า-เขียว */
  --accent2:#00e676; /* เขียวหลัก */
  --border: rgba(255,255,255,0.15);
}
body.light {
  --bg: #f4f7fb;
  --panel:#ffffff;
  --text:#1f2a37;
  --text-sub:#6b7280;
  --accent:#14b8a6;
  --accent2:#16a34a;
  --border: rgba(0,0,0,0.08);
}

*{ box-sizing: border-box; }
html, body{
  margin:0; padding:0;
  font-family: 'Prompt', sans-serif;
  background: var(--bg);
  color: var(--text);
}
.wrapper{
  max-width: 1000px; margin: 30px auto; padding: 0 16px;
}
.paper{
  background: var(--panel);
  border: 1px solid var(--border);
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 10px 30px rgba(0,0,0,0.25);
}
.header{
  display: flex; gap: 16px; align-items: center;
  padding: 20px;
  border-bottom: 1px dashed var(--border);
}
.logo{
  width: 90px; height: 90px; border-radius: 12px;
  background:#0b1220; display:flex; align-items:center; justify-content:center;
  overflow:hidden; border: 1px solid var(--border);
}
.logo img{ width:100%; height:100%; object-fit: contain; }

.hgroup{
  flex:1;
}
.hgroup h1{
  margin:0; font-size: 24px; font-weight: 700; letter-spacing: .3px;
  color: var(--accent2);
}
.sub{
  margin:4px 0 0; color: var(--text-sub); font-size: 14px;
}
.status-pill{
  padding: 6px 10px; border-radius: 20px; font-size: 12px; font-weight: 700; color: #111;
  background: #eee;
  border: 1px solid var(--border);
}

.meta{
  display:grid; grid-template-columns: 1fr 1fr; gap:18px;
  padding: 16px 20px 6px;
}
.card{
  border: 1px solid var(--border);
  background: transparent;
  border-radius: 12px; padding: 14px 16px;
}
.card h3{ margin: 0 0 6px; font-size: 16px; color: var(--accent); }
.card p{ margin: 2px 0; font-size: 14px; color: var(--text); }
.muted{ color: var(--text-sub); }

.table-wrap{ padding: 8px 20px 20px; }
table{
  width:100%; border-collapse: collapse; overflow:hidden;
  border: 1px solid var(--border);
  border-radius: 12px;
}
thead th{
  background: rgba(20,200,170,0.10);
  color: var(--text);
  text-align:center; padding:10px; font-size: 14px; border-bottom: 1px solid var(--border);
}
tbody td{
  padding:10px; border-bottom: 1px dashed var(--border); font-size: 14px;
}
tbody tr:last-child td{ border-bottom: none; }
tbody td.num, tfoot td.num{ text-align: right; }
tfoot td{
  padding: 10px; font-weight: 700; border-top: 1px solid var(--border);
}

.footer{
  display:grid; grid-template-columns: 2fr 1fr; gap: 18px;
  padding: 10px 20px 20px;
}
.sign{
  border: 1px solid var(--border);
  border-radius: 12px; padding: 14px 16px; min-height: 130px;
  background: transparent;
}
.sign h3{ margin: 0 0 6px; font-size: 16px; color: var(--accent); }
.sign .line{
  margin-top: 40px; text-align: center; color: var(--text-sub);
}
.sign img{ display:block; width: 180px; height: auto; margin: 10px auto 0; opacity: .9; }

.btns{
  display:flex; gap: 8px; justify-content: flex-end; padding: 14px 20px;
  border-top: 1px dashed var(--border);
  background: transparent;
}
.btn{
  padding: 10px 14px; border-radius: 10px; cursor: pointer; border: 1px solid var(--border);
  background: rgba(255,255,255,0.08); color: var(--text); font-weight: 700;
}
.btn:hover{ filter: brightness(1.08); }
.btn-primary{ background: linear-gradient(45deg, #22d3ee, #00e676); color: #111; border: none; }
.btn-danger{ background: #ef4444; color: #fff; border: none; }

.badge-status{
  background: <?= $badgeColor ?>;
  color:#111; padding: 6px 10px; border-radius: 16px; font-size: 12px; font-weight: 800;
  display:inline-block;
}

/* พิมพ์ให้พื้นขาวอัตโนมัติ */
@media print{
  body{ background: #fff !important; color: #111 !important; }
  .paper{ box-shadow: none; border-color: #e5e7eb; }
  .header, .meta .card, table, .footer .sign{ border-color: #e5e7eb; }
  .btns, .theme-toggle{ display:none !important; }
  thead th{ background: #f1f5f9 !important; color: #111 !important; }
}

/* Toggle Theme */
.theme-toggle{
  position: fixed; right: 16px; top: 16px; z-index: 9;
  width: 42px; height: 42px; border-radius: 50%;
  background: var(--panel); border: 1px solid var(--border);
  display:flex; align-items:center; justify-content:center; cursor:pointer;
}
.theme-toggle i{ color: var(--text); }
</style>
</head>
<body class="">
<div class="theme-toggle" onclick="toggleTheme()"><i class="fa fa-moon"></i></div>

<div class="wrapper">
  <div class="paper">

    <div class="header">
      <div class="logo">
        <img src="images/clinic_logoPP.png" alt="clinic logo" onerror="this.style.display='none'">
      </div>
      <div class="hgroup">
        <h1>ใบสั่งซื้อยาและวัคซีน • Purchase Order</h1>
        <div class="sub">PPC บ้านรามอินทรา • Animal Clinic Management</div>
      </div>
      <div class="status-pill">
        <span class="badge-status"><?=htmlspecialchars($H['status'])?></span>
      </div>
    </div>

    <div class="meta">
      <div class="card">
        <h3>ข้อมูลคลินิก / Clinic</h3>
        <p><strong><?=htmlspecialchars($H['clinic_name'])?></strong></p>
        <p class="muted"><?=nl2br(htmlspecialchars($H['clinic_address'] ?: '-'))?></p>
        <p class="muted">โทร: <?=htmlspecialchars($H['clinic_phone'] ?: '-')?>
           • อีเมล: <?=htmlspecialchars($H['clinic_email'] ?: '-')?></p>
      </div>
      <div class="card">
        <h3>ข้อมูลผู้จำหน่าย / Supplier</h3>
        <p><strong><?=htmlspecialchars($H['supplier_name'])?></strong></p>
        <p class="muted"><?=nl2br(htmlspecialchars($H['supplier_address'] ?: '-'))?></p>
        <p class="muted">ผู้ติดต่อ: <?=htmlspecialchars($H['contact_person'] ?: '-')?></p>
        <p class="muted">โทร: <?=htmlspecialchars($H['phone'] ?: '-')?>
           • อีเมล: <?=htmlspecialchars($H['email'] ?: '-')?></p>
      </div>
    </div>

    <div class="meta" style="padding-top:6px;">
      <div class="card">
        <h3>ข้อมูลเอกสาร</h3>
        <p>เลขที่ใบสั่งซื้อ: <strong>PO-<?=htmlspecialchars($H['po_id'])?></strong></p>
        <p>วันที่สั่งซื้อ: <strong><?=date('d/m/Y H:i', strtotime($H['po_date']))?></strong></p>
        <p>บันทึกเมื่อ: <span class="muted"><?=date('d/m/Y H:i', strtotime($H['created_at']))?></span></p>
      </div>
      <div class="card">
        <h3>หมายเหตุ</h3>
        <p class="muted"><?=nl2br(htmlspecialchars($H['note'] ?: '-'))?></p>
      </div>
    </div>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th style="width:40px">#</th>
            <th>รายการ</th>
            <th style="width:120px">หมวดหมู่</th>
            <th style="width:100px">จำนวน</th>
            <th style="width:140px">ราคาต่อหน่วย (฿)</th>
            <th style="width:150px">รวม (฿)</th>
          </tr>
        </thead>
        <tbody>
        <?php if(count($rows)==0): ?>
          <tr><td colspan="6" style="text-align:center;color:var(--text-sub)">— ไม่มีรายการสินค้า —</td></tr>
        <?php else: 
          $i=1;
          foreach($rows as $r): ?>
          <tr>
            <td class="num"><?= $i++; ?></td>
            <td><?= htmlspecialchars($r['product_name'] ?: '-') ?></td>
            <td style="text-align:center"><?= htmlspecialchars($r['category'] ?: '-') ?></td>
            <td class="num"><?= number_format($r['quantity']) ?></td>
            <td class="num"><?= number_format($r['unit_cost'],2) ?></td>
            <td class="num"><?= number_format($r['total_cost_calc'],2) ?></td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="5" style="text-align:right">ยอดรวมทั้งสิ้น</td>
            <td class="num"><?= number_format($total_amount,2) ?></td>
          </tr>
        </tfoot>
      </table>
    </div>

    <div class="footer">
      <div class="sign">
        <h3>ลงชื่อผู้จัดซื้อ / ผู้อนุมัติ</h3>
        <img src="images/signature.png" alt="signature" onerror="this.style.display='none'">
        <div class="line">..............................................................</div>
        <div class="muted" style="text-align:center">ชื่อ-สกุล / ตำแหน่ง / วันที่</div>
      </div>
      <div class="card">
        <h3>เงื่อนไข</h3>
        <p class="muted">1) กรุณาตรวจสอบรายการและจำนวนให้ถูกต้องก่อนจัดส่ง</p>
        <p class="muted">2) หากมีข้อผิดพลาด โปรดติดต่อคลินิกภายใน 3 วันทำการ</p>
        <p class="muted">3) เอกสารนี้จัดทำจากระบบอัตโนมัติของ PPC บ้านรามอินทรา</p>
      </div>
    </div>

    <div class="btns">
      <button class="btn" onclick="history.back()"><i class="fa fa-arrow-left"></i> กลับ</button>
      <button class="btn btn-primary" onclick="window.print()"><i class="fa fa-print"></i> พิมพ์</button>
    </div>

  </div>
</div>

<script>
// โหมดสว่าง/มืด (จำค่าไว้ใน localStorage)
(function(){
  const saved = localStorage.getItem('po_theme') || 'dark';
  if(saved === 'light') document.body.classList.add('light');
})();
function toggleTheme(){
  document.body.classList.toggle('light');
  localStorage.setItem('po_theme', document.body.classList.contains('light') ? 'light' : 'dark');
}
</script>
</body>
</html>
