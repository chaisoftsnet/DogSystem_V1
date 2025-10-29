<?php
@session_start();
require_once('dbconnect.php');

// ตรวจสอบสิทธิ์
if (!isset($_SESSION['user_id'])) {
  header("Location: index.php");
  exit();
}

// ดึงข้อมูลสรุปยอด
$total_all = mysqli_fetch_assoc(mysqli_query($objCon, "SELECT COUNT(*) AS c FROM invoices"))['c'];
$total_paid = mysqli_fetch_assoc(mysqli_query($objCon, "SELECT COUNT(*) AS c FROM invoices WHERE status='ชำระแล้ว'"))['c'];
$total_unpaid = mysqli_fetch_assoc(mysqli_query($objCon, "SELECT COUNT(*) AS c FROM invoices WHERE status='รอชำระ'"))['c'];
$total_month = mysqli_fetch_assoc(mysqli_query($objCon, "SELECT COUNT(*) AS c FROM invoices WHERE MONTH(invoice_date)=MONTH(CURDATE()) AND YEAR(invoice_date)=YEAR(CURDATE())"))['c'];
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>💰 ระบบจัดการใบแจ้งหนี้ / POS</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

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
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.05);
  transition: transform 0.2s, box-shadow 0.3s;
}
.card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}
.card i {
  font-size: 40px;
  margin-bottom: 10px;
}
.toggle-dark {
  cursor: pointer;
  font-size: 20px;
  color: #198754;
}
</style>
</head>

<body>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">💰 ระบบบริหารจัดการใบแจ้งหนี้ / POS</h3>
    <div>
      <i class="fa fa-moon toggle-dark me-3" onclick="toggleDarkMode()"></i>
      <a href="dashboard.php" class="btn btn-outline-secondary btn-sm"><i class="fa fa-arrow-left"></i> กลับหน้าหลักคลินิก</a>
    </div>
  </div>

  <div class="row text-center mb-4">
    <div class="col-md-3 mb-3">
      <div class="card bg-primary text-white p-3">
        <i class="fa-solid fa-file-invoice-dollar"></i>
        <h4><?=$total_all?></h4>
        <p>ใบแจ้งหนี้ทั้งหมด</p>
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="card bg-success text-white p-3">
        <i class="fa-solid fa-circle-check"></i>
        <h4><?=$total_paid?></h4>
        <p>ชำระแล้ว</p>
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="card bg-warning text-dark p-3">
        <i class="fa-solid fa-clock"></i>
        <h4><?=$total_unpaid?></h4>
        <p>รอชำระ</p>
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="card bg-info text-white p-3">
        <i class="fa-solid fa-calendar-days"></i>
        <h4><?=$total_month?></h4>
        <p>ใบแจ้งหนี้เดือนนี้</p>
      </div>
    </div>
  </div>

  <div class="row g-4 justify-content-center">

    <div class="col-sm-6 col-md-4 col-lg-3">
      <div class="card p-4 text-center">
        <i class="fa-solid fa-plus-circle text-success"></i>
        <h5>ออกใบแจ้งหนี้</h5>
        <p class="text-muted small">สร้างใบแจ้งหนี้/ใบเสร็จใหม่</p>
        <a href="invoice_add.php?from=invoice" class="btn btn-outline-success btn-sm">เริ่มต้น</a>
      </div>
    </div>

    <div class="col-sm-6 col-md-4 col-lg-3">
      <div class="card p-4 text-center">
        <i class="fa-solid fa-list text-primary"></i>
        <h5>รายการใบแจ้งหนี้</h5>
        <p class="text-muted small">ดู/แก้ไข/ลบ รายการทั้งหมด</p>
        <a href="invoice_manage.php?from=invoice" class="btn btn-outline-primary btn-sm">จัดการ</a>
      </div>
    </div>

    <div class="col-sm-6 col-md-4 col-lg-3">
      <div class="card p-4 text-center">
        <i class="fa-solid fa-capsules text-danger"></i>
        <h5>ข้อมูลสินค้าและบริการ</h5>
        <p class="text-muted small">จัดการยาและบริการคลินิก</p>
        <a href="product_manage.php?from=invoice" class="btn btn-outline-danger btn-sm">จัดการ</a>
      </div>
    </div>

    <div class="col-sm-6 col-md-4 col-lg-3">
      <div class="card p-4 text-center">
        <i class="fa-solid fa-chart-line text-warning"></i>
        <h5>รายงานสรุปยอดขาย</h5>
        <p class="text-muted small">รายงานรายวัน/เดือน/ปี</p>
        <a href="invoice_report.php?from=invoice" class="btn btn-outline-warning btn-sm">ดูรายงาน</a>
      </div>
    </div>
  </div>

  <div class="text-center mt-5">
    <a href="dashboard.php" class="btn btn-outline-secondary btn-lg"><i class="fa fa-arrow-left"></i> กลับระบบคลินิก</a>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const savedMode = localStorage.getItem("themeMode");
  if (!savedMode || savedMode === "dark") {
    document.body.classList.add("dark-mode");
  }
});

function toggleDarkMode(){
  document.body.classList.toggle('dark-mode');
  const isDark = document.body.classList.contains('dark-mode');
  localStorage.setItem("themeMode", isDark ? "dark" : "light");
}
</script>

</body>
</html>
