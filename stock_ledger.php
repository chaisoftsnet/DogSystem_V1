<?php
@session_start();
require_once('dbconnect.php');

if(!isset($_SESSION['user_id'])) {
  header("Location: index.php");
  exit();
}

$start = $_GET['start'] ?? '';
$end = $_GET['end'] ?? '';
$product_id = $_GET['product_id'] ?? '';

$where = "WHERE 1";
if ($product_id) $where .= " AND st.product_id = $product_id";
if ($start && $end) $where .= " AND DATE(st.created_at) BETWEEN '$start' AND '$end'";

$sql = "
SELECT st.*, p.product_name, p.category, u.username
FROM stock_transactions st
LEFT JOIN products p ON st.product_id = p.product_id
LEFT JOIN user u ON st.user_id = u.id
$where
ORDER BY p.category, p.product_name, st.created_at ASC
";
$q = mysqli_query($objCon, $sql);
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>📒 สมุดรายวันคลังยาและวัคซีน (Stock Ledger)</title>
<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<style>
:root {
  --bg-main: radial-gradient(circle at top, #1b2735 0%, #090a0f 80%);
  --card-bg: rgba(255,255,255,0.08);
  --text-main: #fff;
  --text-sub: #aaa;
  --accent: #00e676;
  --table-head-bg: #333;
  --table-head-text: #fff;
}

body.light-mode {
  --bg-main: linear-gradient(150deg, #f9f9f9 0%, #e0f7fa 100%);
  --card-bg: #ffffff;
  --text-main: #111;
  --text-sub: #444;
  --accent: #00bfa5;
  --table-head-bg: #e0f2f1;
  --table-head-text: #000;
}

body {
  font-family: 'Prompt', sans-serif;
  background: var(--bg-main);
  color: var(--text-main);
  padding: 30px;
  transition: all 0.4s ease;
}
/* 🎨 ปรับสีตัวหนังสือของตารางใน Light Mode */
body.light-mode .table th,
body.light-mode .table td {
  color: #000 !important;
}

/* 🧾 หัวตารางใน Light Mode */
body.light-mode .table thead {
  background-color: #e0f2f1 !important;
  color: #000 !important;
  border-bottom: 2px solid #ccc;
}

/* 🌗 ปรับสีเส้นขอบ */
body.light-mode .table-bordered {
  border-color: #ccc !important;
}

/* 🟢 ปรับสีพื้นหลังของ summary ให้สว่างอ่านง่าย */
body.light-mode .summary-row {
  background: #f9fbe7 !important;
  color: #000 !important;
}

body.light-mode .total-summary {
  background: #e8f5e9 !important;
  color: #000 !important;
}

.theme-toggle {
  position: fixed;
  top: 15px; right: 15px;
  background: var(--card-bg);
  border: 1px solid rgba(255,255,255,0.2);
  color: var(--text-main);
  border-radius: 50%;
  width: 45px; height: 45px;
  display: flex; justify-content: center; align-items: center;
  cursor: pointer;
  z-index: 1000;
}

.container-box {
  background: var(--card-bg);
  border-radius: 15px;
  padding: 25px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.2);
}

.table th, .table td { vertical-align: middle; color: var(--text-main); }
.table thead { background: var(--table-head-bg); color: var(--table-head-text); }

.product-group {
  background: rgba(0,230,118,0.15);
  color: var(--accent);
  font-weight: bold;
  text-align: left;
  padding-left: 15px;
}

.summary-row {
  background: rgba(255,255,255,0.1);
  color: #ffd54f;
  font-weight: bold;
}

.total-summary {
  background: rgba(0,191,165,0.25);
  color: #00e676;
  font-weight: bold;
  font-size: 1.1em;
}

.trans-in { color: #00e676; }
.trans-out { color: #ff5252; }

.filter-box {
  background: rgba(0,0,0,0.3);
  padding: 10px 15px;
  border-radius: 10px;
  margin-bottom: 15px;
}
body.light-mode .filter-box {
  background: rgba(240,240,240,0.8);
}

.btn-main {
  background: linear-gradient(45deg, #00e676, #00bfa5);
  border: none;
  color: #000;
  font-weight: bold;
}
.btn-main:hover { opacity: 0.9; }

@media print {
  .btn, .print-hide, .theme-toggle, .filter-box { display: none !important; }
  body { background: #fff; color: #000; }
  .container-box { background: #fff; color: #000; }
  .product-group { color: #000; background: #e0f2f1; }
  .summary-row, .total-summary { color: #000; background: #f1f8e9; }
}

</style>
</head>

<body>
<div class="theme-toggle" onclick="toggleTheme()"><i class="fa fa-moon"></i></div>

<div class="container container-box">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3><i class="fa-solid fa-book"></i> สมุดรายวันคลังยาและวัคซีน</h3>
    <div class="print-hide">
      <button onclick="window.print()" class="btn btn-main btn-sm"><i class="fa fa-print"></i> พิมพ์รายงาน</button>
      <a href="stock_manage.php" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> กลับคลัง</a>
    </div>
  </div>

  <form method="GET" class="filter-box row g-2 align-items-center print-hide">
    <div class="col-md-3">
      <label class="form-label mb-0">จากวันที่:</label>
      <input type="date" name="start" value="<?=$start?>" class="form-control">
    </div>
    <div class="col-md-3">
      <label class="form-label mb-0">ถึงวันที่:</label>
      <input type="date" name="end" value="<?=$end?>" class="form-control">
    </div>
    <div class="col-md-3">
      <button type="submit" class="btn btn-main mt-3"><i class="fa fa-filter"></i> กรองข้อมูล</button>
      <a href="stock_ledger.php" class="btn btn-outline-light mt-3">รีเซ็ต</a>
    </div>
  </form>

  <table class="table table-bordered table-hover text-center align-middle">
    <thead>
      <tr>
        <th>วันที่</th>
        <th>สินค้า</th>
        <th>หมวดหมู่</th>
        <th>ประเภท</th>
        <th>จำนวน</th>
        <th>หมายเหตุ</th>
        <th>ผู้บันทึก</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $current_product = "";
      $sum_in = $sum_out = 0;
      $grand_in = $grand_out = 0;

      while($r = mysqli_fetch_assoc($q)) {
        if(!$product_id && $r['product_name'] != $current_product){
          if($current_product != ""){
            $balance = $sum_in - $sum_out;
            echo "<tr class='summary-row'>
                    <td colspan='3'>รวมของ {$current_product}</td>
                    <td>รับเข้า: {$sum_in}</td>
                    <td>เบิกออก: {$sum_out}</td>
                    <td colspan='2'>คงเหลือ: {$balance}</td>
                  </tr>";
            $sum_in = $sum_out = 0;
          }
          $current_product = $r['product_name'];
          echo "<tr><td colspan='7' class='product-group'>📦 {$current_product}</td></tr>";
        }

        if($r['trans_type'] == 'IN') { $sum_in += $r['quantity']; $grand_in += $r['quantity']; }
        else if($r['trans_type'] == 'OUT') { $sum_out += $r['quantity']; $grand_out += $r['quantity']; }

        $colorClass = ($r['trans_type'] == 'IN') ? 'trans-in' : 'trans-out';
        $sign = ($r['trans_type'] == 'IN') ? '+' : '-';

        echo "
          <tr>
            <td>".date('d/m/Y H:i', strtotime($r['created_at']))."</td>
            <td>{$r['product_name']}</td>
            <td>{$r['category']}</td>
            <td>{$r['trans_type']}</td>
            <td class='{$colorClass}'>{$sign}{$r['quantity']}</td>
            <td>{$r['note']}</td>
            <td>{$r['username']}</td>
          </tr>
        ";
      }

      if($current_product != ""){
        $balance = $sum_in - $sum_out;
        echo "<tr class='summary-row'>
                <td colspan='3'>รวมของ {$current_product}</td>
                <td>รับเข้า: {$sum_in}</td>
                <td>เบิกออก: {$sum_out}</td>
                <td colspan='2'>คงเหลือ: {$balance}</td>
              </tr>";
      }

      $grand_balance = $grand_in - $grand_out;
      echo "<tr class='total-summary'>
              <td colspan='3'>รวมทั้งหมดของคลัง</td>
              <td>รับเข้า: {$grand_in}</td>
              <td>เบิกออก: {$grand_out}</td>
              <td colspan='2'>คงเหลือรวม: {$grand_balance}</td>
            </tr>";
      ?>
    </tbody>
  </table>
</div>

<script>
function toggleTheme(){
  document.body.classList.toggle('light-mode');
  const icon = document.querySelector('.theme-toggle i');
  icon.classList.toggle('fa-sun');
  icon.classList.toggle('fa-moon');
  localStorage.setItem('theme', document.body.classList.contains('light-mode') ? 'light' : 'dark');
}

window.addEventListener('DOMContentLoaded',()=>{
  const saved = localStorage.getItem('theme');
  if(saved === 'light') document.body.classList.add('light-mode');
});
</script>
</body>
</html>
