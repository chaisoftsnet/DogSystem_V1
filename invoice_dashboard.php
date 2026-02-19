<?php
@session_start();
require_once('dbconnect.php');
if(!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }

// ✅ ดึงข้อมูลสรุปยอด
$total_invoice = mysqli_fetch_assoc(mysqli_query($objCon, "SELECT COUNT(*) AS total FROM invoices"))['total'];
$total_pending = mysqli_fetch_assoc(mysqli_query($objCon, "SELECT COUNT(*) AS total FROM invoices WHERE status='รอชำระ'"))['total'];
$total_paid = mysqli_fetch_assoc(mysqli_query($objCon, "SELECT COUNT(*) AS total FROM invoices WHERE status='ชำระแล้ว'"))['total'];
$sum_amount = mysqli_fetch_assoc(mysqli_query($objCon, "SELECT SUM(total_amount) AS total FROM invoices"))['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>📊 Dashboard ใบแจ้งหนี้ & ใบเสร็จ</title>

<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
  --card-bg: rgba(255,255,255,0.9);
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
.dashboard-container {
  max-width: 1100px;
  margin: 100px auto;
  padding: 20px;
}
.card-box {
  background: var(--card-bg);
  border-radius: 20px;
  padding: 30px;
  box-shadow: 0 8px 25px rgba(0,0,0,0.3);
  text-align: center;
  transition: transform 0.2s ease, background 0.3s;
}
.card-box:hover {
  transform: translateY(-5px);
  background: rgba(255,255,255,0.15);
}
.card-box i {
  font-size: 2.5rem;
  margin-bottom: 10px;
  color: var(--accent);
}
h4 {
  margin-bottom: 0;
  font-weight: 600;
}
.stat-value {
  font-size: 1.8rem;
  font-weight: 700;
  margin-bottom: 10px;
  color: var(--accent);
}
.btn-main {
  background: linear-gradient(45deg, #00e676, #00bfa5);
  border: none;
  color: #000;
  font-weight: bold;
  border-radius: 10px;
  transition: 0.2s;
}
.btn-main:hover {
  opacity: 0.9;
  transform: scale(1.02);
}
</style>
</head>
<body>
<div class="theme-toggle" onclick="toggleTheme()"><i class="fa fa-moon"></i></div>

<div class="dashboard-container text-center">
  <h2 class="mb-4"><i class="fa-solid fa-file-invoice-dollar"></i> ระบบบริหารจัดการใบแจ้งหนี้ & ใบเสร็จ</h2>

  <div class="row g-4 mb-5">
    <div class="col-md-3">
      <div class="card-box">
        <i class="fa fa-file-invoice"></i>
        <h5>จำนวนใบแจ้งหนี้ทั้งหมด</h5>
        <div class="stat-value"><?=number_format($total_invoice)?></div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card-box">
        <i class="fa fa-hourglass-half"></i>
        <h5>รอชำระ</h5>
        <div class="stat-value"><?=number_format($total_pending)?></div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card-box">
        <i class="fa fa-check-circle"></i>
        <h5>ชำระแล้ว</h5>
        <div class="stat-value"><?=number_format($total_paid)?></div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card-box">
        <i class="fa fa-coins"></i>
        <h5>ยอดรวมทั้งหมด (บาท)</h5>
        <div class="stat-value"><?=number_format($sum_amount,2)?></div>
      </div>
    </div>
  </div>

  <div class="row justify-content-center g-3">
<div class="col-md-3">
      <a href="invoice_add.php" class="btn btn-main w-100 py-3">
        <i class="fa fa-plus-circle"></i> ออกใบแจ้งหนี้ใหม่
      </a>
    </div>    
    <div class="col-md-3">
      <a href="invoice_manage.php" class="btn btn-main w-100 py-3">
        <i class="fa fa-file-invoice"></i> จัดการใบแจ้งหนี้
      </a>
    </div>
    <div class="col-md-3">
      <a href="invoice_report.php" class="btn btn-main w-100 py-3">
        <i class="fa fa-chart-line"></i> รายงานสรุปยอด
      </a>
    </div>
    <div class="col-md-3">
      <a href="document/document_pos.pdf" class="btn btn-secondary w-100 py-3">
        <i class="fa fa-file"></i> คู่มือ
      </a>
    </div>

    <div class="col-md-3">
      <a href="dashboard.php" class="btn btn-secondary w-100 py-3">
        <i class="fa fa-home"></i> กลับหน้าหลัก
      </a>
    </div>

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
