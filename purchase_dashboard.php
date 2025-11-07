<?php
@session_start();
require_once('dbconnect.php');

if (!isset($_SESSION['user_id'])) {
  header("Location: index.php");
  exit();
}

// 📊 ดึงข้อมูลสรุปใบสั่งซื้อ
$total_po = mysqli_fetch_assoc(mysqli_query($objCon, "SELECT COUNT(*) AS c FROM purchase_orders"))['c'] ?? 0;
$total_wait = mysqli_fetch_assoc(mysqli_query($objCon, "SELECT COUNT(*) AS c FROM purchase_orders WHERE status='รออนุมัติ'"))['c'] ?? 0;
$total_ordered = mysqli_fetch_assoc(mysqli_query($objCon, "SELECT COUNT(*) AS c FROM purchase_orders WHERE status='สั่งซื้อแล้ว'"))['c'] ?? 0;
$total_received = mysqli_fetch_assoc(mysqli_query($objCon, "SELECT COUNT(*) AS c FROM purchase_orders WHERE status='ได้รับของแล้ว'"))['c'] ?? 0;

$total_amount_month = mysqli_fetch_assoc(mysqli_query($objCon, "
  SELECT SUM(total_amount) AS total 
  FROM purchase_orders 
  WHERE MONTH(po_date)=MONTH(CURDATE()) AND YEAR(po_date)=YEAR(CURDATE())
"))['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>📦 ระบบจัดซื้อยา / คลังวัคซีน</title>

<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<style>
:root {
  --bg-dark: radial-gradient(circle at top, #1b2735 0%, #090a0f 80%);
  --text-main: #ffffff;
  --text-sub: #bbbbbb;
  --accent: #00e676;
  --card-bg: rgba(255,255,255,0.08);
  --border: rgba(255,255,255,0.15);
}
body.light-mode {
  --bg-dark: linear-gradient(150deg, #f1f8e9, #e0f7fa);
  --text-main: #222;
  --text-sub: #666;
  --accent: #00bfa5;
  --card-bg: #ffffff;
  --border: rgba(0,0,0,0.1);
}
body {
  background: var(--bg-dark);
  color: var(--text-main);
  font-family: 'Prompt', sans-serif;
  transition: 0.3s;
  min-height: 100vh;
}
.theme-toggle {
  position: fixed;
  top: 20px;
  right: 20px;
  background: var(--card-bg);
  border: 1px solid var(--border);
  width: 45px; height: 45px;
  border-radius: 50%;
  display: flex; justify-content: center; align-items: center;
  color: var(--text-main);
  cursor: pointer;
  z-index: 999;
}
.container { max-width: 1100px; margin-top: 80px; }
h2 { color: var(--accent); text-shadow: 0 0 10px rgba(0,230,118,0.4); }
.card-glass {
  background: var(--card-bg);
  border: 1px solid var(--border);
  border-radius: 16px;
  text-align: center;
  padding: 30px 20px;
  transition: 0.3s;
}
.card-glass:hover {
  transform: translateY(-5px);
  box-shadow: 0 0 20px rgba(0,230,118,0.2);
}
.card-glass h5 { margin-top: 10px; color: var(--text-main); }
.card-glass p { color: var(--text-sub); margin-bottom: 0; }
.btn-main {
  background: linear-gradient(45deg, #00e676, #00bfa5);
  border: none;
  color: #000;
  font-weight: bold;
  padding: 8px 16px;
  border-radius: 10px;
  margin-top: 10px;
  transition: 0.3s;
}
.btn-main:hover { transform: scale(1.05); }
.dashboard {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 25px;
}
</style>
</head>

<body>
<div class="theme-toggle" onclick="toggleTheme()"><i class="fa fa-moon"></i></div>

<div class="container">
  <h2 class="text-center mb-3">📦 ระบบจัดซื้อยาและคลังวัคซีน</h2>
  <p class="text-center text-secondary">Purchase & Inventory Management System - PPC บ้านรามอินทรา</p>

  <div class="dashboard mb-4">
    <div class="card-glass">
      <i class="fa-solid fa-file-invoice fa-2x text-success"></i>
      <h5><?=number_format($total_po)?></h5>
      <p>ใบสั่งซื้อทั้งหมด</p>
    </div>

    <div class="card-glass">
      <i class="fa-solid fa-clock fa-2x text-warning"></i>
      <h5><?=number_format($total_wait)?></h5>
      <p>รออนุมัติ</p>
    </div>

    <div class="card-glass">
      <i class="fa-solid fa-truck fa-2x text-info"></i>
      <h5><?=number_format($total_ordered)?></h5>
      <p>สั่งซื้อแล้ว</p>
    </div>

    <div class="card-glass">
      <i class="fa-solid fa-box-open fa-2x text-primary"></i>
      <h5><?=number_format($total_received)?></h5>
      <p>ได้รับของแล้ว</p>
    </div>

    <div class="card-glass">
      <i class="fa-solid fa-coins fa-2x text-success"></i>
      <h5><?=number_format($total_amount_month,2)?></h5>
      <p>มูลค่ารวมเดือนนี้ (บาท)</p>
    </div>
  </div>

  <div class="dashboard">
    <div class="card-glass">
      <i class="fa-solid fa-truck-field fa-2x text-success"></i>
      <h5>จัดการผู้จำหน่าย</h5>
      <p>ข้อมูลติดต่อ Supplier</p><br>
      <a href="supplier_manage.php" class="btn-main">เปิดหน้าจอ</a>
    </div>

    <div class="card-glass">
      <i class="fa-solid fa-file-invoice-dollar fa-2x text-info"></i>
      <h5>ใบสั่งซื้อ</h5>
      <p>ออกใบสั่งซื้อยา วัคซีน อุปกรณ์</p><br>
      <a href="purchase_order_manage.php" class="btn-main">เปิดหน้าจอ</a>
    </div>

    <div class="card-glass">
      <i class="fa-solid fa-boxes-stacked fa-2x text-warning"></i>
      <h5>คลังสินค้า</h5>
      <p>ดูจำนวนสินค้าในสต็อก</p><br>
      <a href="product_manage.php" class="btn-main">เปิดหน้าจอ</a>
    </div>

    <div class="card-glass">
      <i class="fa-solid fa-file-lines fa-2x text-danger"></i>
      <h5>รายงานสรุป</h5>
      <p>ดูยอดสั่งซื้อรายเดือน</p><br>
      <a href="purchase_report.php" class="btn-main">เปิดหน้าจอ</a>
    </div>
  </div>

  <div class="text-center mt-5">
    <a href="dashboard.php" class="btn btn-outline-light"><i class="fa fa-arrow-left"></i> กลับหน้าหลักคลินิก</a>
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
