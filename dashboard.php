<?php
@session_start();
include 'dbconnect.php';
include 'function.php';
$aRole = ['คนทั่วไป','ลูกค้า','เจ้าหน้าที่คลินิก','ผู้ดูแลระบบ'];
$Mode = $_REQUEST["Mode"] ?? '';
if (!isset($_SESSION['username'])) {
  header("Location: index.php");
  exit();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>🏥 เมนูหลักระบบคลินิกรักษาสัตว์</title>
<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">


<style>
:root {
  --bg-main: radial-gradient(circle at top, #1b2735 0%, #090a0f 80%);
  --card-bg: rgba(255,255,255,0.08);
  --text-main: #ffffff;
  --text-sub: #bbbbbb;
  --accent: #00e676;
  --card-border: rgba(255,255,255,0.1);
}

/* 🌞 Light Mode */
body.light-mode {
  --bg-main: linear-gradient(150deg, #f2f6fa 0%, #e8f5e9 100%);
  --card-bg: rgba(255,255,255,0.95);
  --text-main: #222;
  --text-sub: #555;
  --accent: #00bfa5;
  --card-border: rgba(0,0,0,0.1);
}

body {
  font-family: 'Prompt', sans-serif;
  margin: 0;
  min-height: 100vh;
  background: var(--bg-main);
  color: var(--text-main);
  transition: all 0.4s ease;
}

/* 🌙 Toggle */
.theme-toggle {
  position: fixed;
  top: 15px;
  right: 15px;
  background: var(--card-bg);
  border: 1px solid var(--card-border);
  color: var(--text-main);
  border-radius: 50%;
  width: 45px;
  height: 45px;
  display: flex;
  justify-content: center;
  align-items: center;
  cursor: pointer;
  box-shadow: 0 0 10px rgba(0,0,0,0.3);
  z-index: 1000;
  transition: 0.3s;
}
.theme-toggle:hover { transform: rotate(15deg); }

/* 🩺 Container */
.container {
  max-width: 1100px;
  margin: 100px auto;
  text-align: center;
}

/* Header */
h2 {
  color: var(--accent);
  text-shadow: 0 0 15px rgba(0,230,118,0.4);
}
h5 { color: var(--text-sub); margin-bottom: 30px; }

/* 🧊 Card */
.dashboard {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 25px;
}
.card-glass {
  background: var(--card-bg);
  border: 1px solid var(--card-border);
  border-radius: 18px;
  backdrop-filter: blur(12px);
  box-shadow: 0 8px 25px rgba(0,0,0,0.2);
  padding: 30px 20px;
  transition: 0.3s;
}
.card-glass:hover {
  transform: translateY(-7px);
  box-shadow: 0 10px 30px rgba(0,230,118,0.3);
}
.card-glass i {
  font-size: 40px;
  margin-bottom: 12px;
}
.card-glass h5 {
  color: var(--text-main);
  margin: 10px 0;
}
.card-glass p {
  color: var(--text-sub);
  font-size: 14px;
  min-height: 50px;
}
.card-glass a {
  text-decoration: none;
  background: linear-gradient(45deg, #00e676, #00bfa5);
  color: #000;
  padding: 8px 16px;
  border-radius: 10px;
  font-weight: bold;
  display: inline-block;
  margin-top: 8px;
  transition: 0.3s;
}
.card-glass a:hover {
  transform: scale(1.05);
  background: linear-gradient(45deg, #00c853, #1de9b6);
}

/* 🔘 Logout */
.logout-btn {
  margin-top: 50px;
  display: inline-block;
  border: 1px solid var(--accent);
  color: var(--accent);
  border-radius: 10px;
  padding: 10px 25px;
  text-decoration: none;
  transition: 0.3s;
}
.logout-btn:hover {
  background: var(--accent);
  color: #000;
}

/* Responsive */
@media (max-width: 600px) {
  .container { margin: 40px 20px; }
}
</style>
</head>
<link rel="stylesheet" href="css/theme.css">
<script src="js/theme.js"></script>

<body>
<!-- 🌙 Toggle -->
<div class="theme-toggle" onclick="toggleTheme()">
  <i class="bi bi-moon-stars"></i>
</div>
<button class="toggle-theme btn btn-sm" onclick="toggleDarkMode()">
  <i class="fa fa-moon"></i> </button>

<div class="container">
  <h2>🏥 ระบบบริหารจัดการคลินิกรักษาสัตว์ Version 1.0/2568</h2>
  <h5>คลินิก: <?=ret_clinic($_SESSION['clinic_id'],$objCon);?><br>
  <small>สิทธิ์ผู้ใช้: <?=$aRole[$_SESSION['role']]?></small></h5>

  <div class="dashboard">
    <div class="card-glass">
      <i class="fa-solid fa-dog text-success"></i>
      <h5>บันทึกข้อมูลสัตว์ส่งรักษา</h5>
      <p>บันทึกประวัติสุนัข (History Dog Records)</p>
      <a href="dog_update.php">จัดการข้อมูล</a>
    </div>

    <div class="card-glass">
      <i class="fa-solid fa-syringe text-danger"></i>
      <h5>การรักษาพยาบาล</h5>
      <p>บันทึกการรักษาและประวัติการรักษา</p>
      <a href="treatment_manage.php">จัดการข้อมูล</a>
    </div>

    <div class="card-glass">
      <i class="fa-solid fa-calendar-days text-info"></i>
      <h5>การนัดหมาย</h5>
      <p>บันทึกการนัดหมายและแจ้งเตือน</p>
      <a href="appointment_manage.php">จัดการนัดหมาย</a>
    </div>

    <div class="card-glass">
      <i class="fa-solid fa-syringe text-warning"></i>
      <h5>ประวัติการฉีดวัคซีน</h5>
      <p>บันทึกข้อมูลวัคซีนสัตว์</p>
      <a href="vaccine_manage.php">จัดการวัคซีน</a>
    </div>

    <div class="card-glass">
      <i class="fa-solid fa-shield-dog text-primary"></i>
      <h5>ประวัติการถ่ายพยาธิ</h5>
      <p>บันทึกการถ่ายพยาธิและป้องกันเห็บหมัด</p>
      <a href="deworming_manage.php">จัดการข้อมูล</a>
    </div>

    <div class="card-glass">
      <i class="fa-solid fa-flask text-light"></i>
      <h5>ผลตรวจทางห้องปฏิบัติการ</h5>
      <p>ผลตรวจเลือด ปัสสาวะ และแล็บอื่น ๆ</p>
      <a href="Lab_manage.php">จัดการผลตรวจ</a>
    </div>

    <div class="card-glass">
      <i class="fa-solid fa-stethoscope text-secondary"></i>
      <h5>การผ่าตัด / หัตถการ</h5>
      <p>บันทึกข้อมูลการผ่าตัด / หัตถการ</p>
      <a href="Surgeries_manage.php">จัดการข้อมูล</a>
    </div>

    <div class="card-glass">
      <i class="fa-solid fa-bone text-danger"></i>
      <h5>ข้อมูลโภชนาการ</h5>
      <p>บันทึกอาหารและคำแนะนำโภชนาการ</p>
      <a href="Nutrition_manage.php">จัดการข้อมูล</a>
    </div>

    <div class="card-glass">
      <i class="fa-solid fa-house-chimney text-info"></i>
      <h5>ประวัติการฝากเลี้ยง</h5>
      <p>ข้อมูลการฝากเลี้ยงและการดูแล</p>
      <a href="boarding_manage.php">จัดการข้อมูล</a>
    </div>

    <div class="card-glass">
      <i class="fa-solid fa-paperclip text-warning"></i>
      <h5>ไฟล์แนบ / เอกสาร</h5>
      <p>แนบไฟล์เอกสารหรือผลตรวจ</p>
      <a href="attachments_manage.php">จัดการเอกสาร</a>
    </div>

    <?php if($_SESSION['role']>=2): ?>
    <div class="card-glass">
      <i class="fa-solid fa-chart-column text-success"></i>
      <h5>รายงาน</h5>
      <p>รายงานข้อมูลการรักษาและการทำงานคลินิก</p>
      <a href="clinic_summary.php" target="_blank">ดูรายงาน</a>
    </div>
    <?php endif; ?>

    <?php if($_SESSION['role']==3): ?>
    <div class="card-glass">
      <i class="fa-solid fa-hospital text-info"></i>
      <h5>ข้อมูลคลินิก</h5>
      <p>เพิ่ม / แก้ไข ข้อมูลคลินิก</p>
      <a href="clinic_update.php">ตั้งค่าคลินิก</a>
    </div>

    <div class="card-glass">
      <i class="fa-solid fa-user-gear text-danger"></i>
      <h5>จัดการผู้ใช้งาน</h5>
      <p>สิทธิ์การใช้งานและการดูแลระบบ</p>
      <a href="user_update.php">ผู้ใช้งานระบบ</a>
    </div>

    <div class="card-glass">
      <i class="fa-solid fa-user-gear text-danger"></i>    
      <h5 class="card-title">💰 ระบบออกใบเสร็จ / POS</h5>
      <p>บันทึกค่ารักษา ค่ายา และออกใบเสร็จให้ลูกค้า</p>
      <a href="invoice_dashboard.php" class="btn btn-outline-secondary">  จัดการใบเสร็จ</a>
    </div>

    <div class="card-glass">
      <i class="fa-solid fa-user-gear text-danger"></i>    
      <h5 class="card-title">💰 ระบบรายการยาที่ขายโดย supplier contect</h5>
      <p>บันทึกข้อมูลยาที่นำมาจำหน่าย</p>
      <a href="purchase_dashboard.php" class="btn btn-outline-secondary">  ระบบใบสั่งซื้อยา</a>
    </div>
    <?php endif; ?>
  </div>
  
</div>

  <a href="logout.php" class="logout-btn"><i class="bi bi-box-arrow-right"></i> ออกจากระบบ</a>
</div>

<script>
function toggleTheme() {
  const body = document.body;
  body.classList.toggle('light-mode');
  const icon = document.querySelector('.theme-toggle i');
  icon.classList.toggle('bi-sun');
  icon.classList.toggle('bi-moon-stars');
  localStorage.setItem('theme', body.classList.contains('light-mode') ? 'light' : 'dark');
}

window.addEventListener('DOMContentLoaded', () => {
  const saved = localStorage.getItem('theme');
  if(saved === 'light') document.body.classList.add('light-mode');
});
</script>
</body>
</html>
