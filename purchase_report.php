<?php
@session_start();
require_once('dbconnect.php');

// 🔹 รับค่าจากฟอร์ม
$month = $_GET['month'] ?? date('m');
$year  = $_GET['year'] ?? date('Y');
$supplier = $_GET['supplier_id'] ?? '';

// 🔹 ดึงรายการผู้จำหน่ายทั้งหมด
$suppliers = mysqli_query($objCon, "SELECT * FROM suppliers ORDER BY supplier_name ASC");

// 🔹 ดึงข้อมูลสรุปใบสั่งซื้อ
$condition = "WHERE MONTH(p.po_date)='$month' AND YEAR(p.po_date)='$year'";
if ($supplier != '') $condition .= " AND p.supplier_id='$supplier'";

$sql = "
  SELECT p.po_id, p.po_date, p.total_amount, p.status, s.supplier_name
  FROM purchase_orders p
  LEFT JOIN suppliers s ON p.supplier_id = s.supplier_id
  $condition
  ORDER BY s.supplier_name, p.po_date DESC
";
$result = mysqli_query($objCon, $sql);
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>📊 รายงานใบสั่งซื้อ</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
:root {
  --bg-dark: #121212;
  --card-bg: #1e1e1e;
  --text-main: #fff;
  --text-sub: #bbb;
  --accent: #00e676;
}
body.light-mode {
  --bg-dark: #f2f6fa;
  --card-bg: #ffffff;
  --text-main: #222;
  --text-sub: #555;
  --accent: #00796b;
}
body {
  font-family: 'Prompt', sans-serif;
  background: var(--bg-dark);
  color: var(--text-main);
  transition: all 0.3s ease;
}
.container-box {
  background: var(--card-bg);
  border-radius: 15px;
  padding: 25px;
  margin-top: 70px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.3);
}
.table th, .table td { vertical-align: middle; }
body.light-mode .table th, body.light-mode .table td { color: #000 !important; }
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
@media print {
  .no-print { display: none !important; }
  body { background: #fff; color: #000; }
  .container-box { background: #fff; box-shadow: none; }
}
</style>
</head>
<body>
<div class="theme-toggle" onclick="toggleTheme()"><i class="fa fa-moon"></i></div>

<div class="container container-box">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4>📋 รายงานใบสั่งซื้อประจำเดือน <?=date('F Y', strtotime("$year-$month-01"))?></h4>
    <div class="no-print">
      <form method="get" class="d-flex">
        <select name="month" class="form-select me-2">
          <?php for($m=1;$m<=12;$m++): ?>
          <option value="<?=$m?>" <?=$m==$month?'selected':''?>><?=date('F', mktime(0,0,0,$m,1))?></option>
          <?php endfor; ?>
        </select>
        <select name="year" class="form-select me-2">
          <?php for($y=date('Y')-2;$y<=date('Y');$y++): ?>
          <option value="<?=$y?>" <?=$y==$year?'selected':''?>><?=$y+543?></option>
          <?php endfor; ?>
        </select>
        <select name="supplier_id" class="form-select me-2">
          <option value="">-- ผู้จำหน่ายทั้งหมด --</option>
          <?php while($s = mysqli_fetch_assoc($suppliers)): ?>
            <option value="<?=$s['supplier_id']?>" <?=$supplier==$s['supplier_id']?'selected':''?>><?=$s['supplier_name']?></option>
          <?php endwhile; ?>
        </select>
        <button class="btn btn-success"><i class="fa fa-filter"></i> กรอง</button>
      </form>
    </div>
  </div>

  <?php
  $current_supplier = '';
  $total_all = 0;
  $total_supplier = 0;
  if (mysqli_num_rows($result) > 0):
  ?>
  <table class="table table-bordered text-center align-middle">
    <thead class="table-dark">
      <tr>
        <th>วันที่</th>
        <th>รหัสใบสั่งซื้อ</th>
        <th>ผู้จำหน่าย</th>
        <th>สถานะ</th>
        <th>ยอดรวม (บาท)</th>
      </tr>
    </thead>
    <tbody>
      <?php
      while($r = mysqli_fetch_assoc($result)):
        if($r['supplier_name'] != $current_supplier):
          if($current_supplier != '') {
            echo "<tr class='table-secondary'><td colspan='4' class='text-end fw-bold'>รวมของผู้จำหน่ายนี้:</td>
                  <td class='fw-bold text-success'>".number_format($total_supplier,2)."</td></tr>";
            $total_supplier = 0;
          }
          $current_supplier = $r['supplier_name'];
          echo "<tr><td colspan='5' class='table-info fw-bold text-start'>🏢 {$current_supplier}</td></tr>";
        endif;

        $color = [
          'รออนุมัติ'=>'warning',
          'สั่งซื้อแล้ว'=>'info',
          'ได้รับของแล้ว'=>'success',
          'ยกเลิก'=>'danger'
        ][$r['status']] ?? 'secondary';
        $total_supplier += $r['total_amount'];
        $total_all += $r['total_amount'];

        echo "
        <tr>
          <td>".date('d/m/Y', strtotime($r['po_date']))."</td>
          <td>PO-{$r['po_id']}</td>
          <td>{$r['supplier_name']}</td>
          <td><span class='badge bg-$color'>{$r['status']}</span></td>
          <td>".number_format($r['total_amount'],2)."</td>
        </tr>";
      endwhile;

      echo "<tr class='table-secondary'><td colspan='4' class='text-end fw-bold'>รวมของผู้จำหน่ายนี้:</td>
            <td class='fw-bold text-success'>".number_format($total_supplier,2)."</td></tr>";
      ?>
    </tbody>
    <tfoot class="table-light">
      <tr>
        <th colspan="4" class="text-end">ยอดรวมทั้งหมด:</th>
        <th><?=number_format($total_all,2)?> บาท</th>
      </tr>
    </tfoot>
  </table>
  <?php else: ?>
    <div class="alert alert-warning text-center">ไม่พบข้อมูลในเดือนนี้</div>
  <?php endif; ?>

  <div class="text-end no-print mt-3">
    <button onclick="window.print()" class="btn btn-primary"><i class="fa fa-print"></i> พิมพ์รายงาน</button>
    <a href="export_purchase_excel.php?month=<?=$month?>&year=<?=$year?>&supplier_id=<?=$supplier?>" class="btn btn-success">
      <i class="fa fa-file-excel"></i> ส่งออก Excel
    </a>
    <a href="purchase_dashboard.php" class="btn btn-secondary"><i class="fa fa-home"></i> กลับหน้าหลัก</a>
  </div>
</div>

<script>
function toggleTheme(){
  document.body.classList.toggle('light-mode');
  const icon = document.querySelector('.theme-toggle i');
  icon.classList.toggle('fa-sun');
  icon.classList.toggle('fa-moon');
}
</script>
</body>
</html>
