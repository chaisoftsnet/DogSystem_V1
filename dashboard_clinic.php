<?php
@session_start();
include 'dbconnect.php';
include 'function.php';
$aRole = ['คนทั่วไป','ลูกค้า','เจ้าหน้าที่คลินิก','ผู้ดูแลระบบ'];
$Mode = $_REQUEST["Mode"] ?? '';
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>🏥 ระบบคลินิกรักษาสัตว์</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
body {
  background-color: #f8f9fa;
  transition: background 0.3s, color 0.3s;
}
.dark-mode {
  background-color: #121212;
  color: #f1f1f1;
}
.card-menu {
  border-radius: 1rem;
  text-align: center;
  transition: transform 0.2s, box-shadow 0.3s;
  cursor: pointer;
}
.card-menu:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 25px rgba(0,0,0,0.2);
}
.card-menu i {
  font-size: 40px;
  margin-bottom: 10px;
}
.btn-outline-mode {
  border: 1px solid #aaa;
  color: inherit;
}
.mode-toggle {
  position: fixed;
  top: 15px;
  right: 15px;
}
</style>
</head>
<body>

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">🐾 คลินิกรักษาสัตว์: <?= ret_clinic($_SESSION['clinic_id'],$objCon) ?></h3>
    <button class="btn btn-outline-mode btn-sm mode-toggle" onclick="toggleDarkMode()"><i class="fa fa-moon"></i> สลับโหมด</button>
  </div>
  <h6 class="text-center text-muted mb-4">
    (สิทธิ์ผู้ใช้: <?=$aRole[$_SESSION['role']]?>)
  </h6>

  <div class="row g-4 justify-content-center">
    <!-- 🐶 -->
    <div class="col-sm-6 col-md-4 col-lg-3">
      <div class="card card-menu p-4 shadow-sm">
        <i class="fa-solid fa-dog text-primary"></i>
        <h5>บันทึกข้อมูลสัตว์ส่งรักษา</h5>
        <p class="text-muted small">บันทึกทะเบียนสัตว์เลี้ยง</p>
        <a href="dog_update.php?Mode=<?=$Mode?>" class="btn btn-outline-secondary btn-sm">เข้าสู่ระบบ</a>
      </div>
    </div>

    <!-- 💉 -->
    <div class="col-sm-6 col-md-4 col-lg-3">
      <div class="card card-menu p-4 shadow-sm">
        <i class="fa-solid fa-syringe text-success"></i>
        <h5>ประวัติการรักษา</h5>
        <p class="text-muted small">การรักษาพยาบาลสัตว์</p>
        <a href="treatment_manage.php?Mode=<?=$Mode?>" class="btn btn-outline-secondary btn-sm">เข้าสู่ระบบ</a>
      </div>
    </div>

    <!-- 📅 -->
    <div class="col-sm-6 col-md-4 col-lg-3">
      <div class="card card-menu p-4 shadow-sm">
        <i class="fa-solid fa-calendar-days text-info"></i>
        <h5>การนัดหมาย</h5>
        <p class="text-muted small">บันทึกการนัดหมายและแจ้งเตือน</p>
        <a href="appointment_manage.php?Mode=<?=$Mode?>" class="btn btn-outline-secondary btn-sm">เข้าสู่ระบบ</a>
      </div>
    </div>

    <!-- 💊 -->
    <div class="col-sm-6 col-md-4 col-lg-3">
      <div class="card card-menu p-4 shadow-sm">
        <i class="fa-solid fa-pills text-danger"></i>
        <h5>ประวัติการฉีดวัคซีน</h5>
        <p class="text-muted small">ข้อมูลวัคซีนและการป้องกัน</p>
        <a href="vaccine_manage.php?Mode=<?=$Mode?>" class="btn btn-outline-secondary btn-sm">เข้าสู่ระบบ</a>
      </div>
    </div>

    <!-- 🧬 -->
    <div class="col-sm-6 col-md-4 col-lg-3">
      <div class="card card-menu p-4 shadow-sm">
        <i class="fa-solid fa-dna text-warning"></i>
        <h5>ผลตรวจทางห้องแล็บ</h5>
        <p class="text-muted small">ผลเลือด, ปัสสาวะ, เอกสาร</p>
        <a href="LabResults_manage.php?Mode=<?=$Mode?>" class="btn btn-outline-secondary btn-sm">เข้าสู่ระบบ</a>
      </div>
    </div>

    <!-- 🩺 -->
    <div class="col-sm-6 col-md-4 col-lg-3">
      <div class="card card-menu p-4 shadow-sm">
        <i class="fa-solid fa-stethoscope text-secondary"></i>
        <h5>การผ่าตัด / หัตถการ</h5>
        <p class="text-muted small">ข้อมูลหัตถการและผลลัพธ์</p>
        <a href="Surgeries_manage.php?Mode=<?=$Mode?>" class="btn btn-outline-secondary btn-sm">เข้าสู่ระบบ</a>
      </div>
    </div>

    <!-- 🍖 -->
    <div class="col-sm-6 col-md-4 col-lg-3">
      <div class="card card-menu p-4 shadow-sm">
        <i class="fa-solid fa-bone text-danger"></i>
        <h5>โภชนาการสัตว์</h5>
        <p class="text-muted small">บันทึกอาหารและคำแนะนำ</p>
        <a href="Nutrition_manage.php?Mode=<?=$Mode?>" class="btn btn-outline-secondary btn-sm">เข้าสู่ระบบ</a>
      </div>
    </div>

    <!-- 🏠 -->
    <div class="col-sm-6 col-md-4 col-lg-3">
      <div class="card card-menu p-4 shadow-sm">
        <i class="fa-solid fa-house-chimney text-info"></i>
        <h5>ประวัติฝากเลี้ยง</h5>
        <p class="text-muted small">การฝากเลี้ยงและการดูแล</p>
        <a href="boarding_manage.php?Mode=<?=$Mode?>" class="btn btn-outline-secondary btn-sm">เข้าสู่ระบบ</a>
      </div>
    </div>

    <!-- 📎 -->
    <div class="col-sm-6 col-md-4 col-lg-3">
      <div class="card card-menu p-4 shadow-sm">
        <i class="fa-solid fa-paperclip text-dark"></i>
        <h5>ไฟล์แนบ / เอกสาร</h5>
        <p class="text-muted small">แนบไฟล์ผลตรวจหรือรูปภาพ</p>
        <a href="attachments_manage.php?Mode=<?=$Mode?>" class="btn btn-outline-secondary btn-sm">เข้าสู่ระบบ</a>
      </div>
    </div>

    <!-- 🔹 เฉพาะเจ้าหน้าที่คลินิก -->
    <?php if($_SESSION['role']>=2): ?>
    <div class="col-sm-6 col-md-4 col-lg-3">
      <div class="card card-menu p-4 shadow-sm">
        <i class="fa-solid fa-chart-column text-primary"></i>
        <h5>รายงาน</h5>
        <p class="text-muted small">รายงานภาพรวมของคลินิก</p>
        <a href="reportAll.php?report_type=dogs&Mode=<?=$Mode?>" target="_blank" class="btn btn-outline-secondary btn-sm">ดูรายงาน</a>
      </div>
    </div>
    <?php endif; ?>

    <!-- 🔹 เฉพาะผู้ดูแลระบบ -->
    <?php if($_SESSION['role']==3): ?>
    <div class="col-sm-6 col-md-4 col-lg-3">
      <div class="card card-menu p-4 shadow-sm">
        <i class="fa-solid fa-hospital text-success"></i>
        <h5>ข้อมูลคลินิก</h5>
        <p class="text-muted small">ตั้งค่าคลินิกและผู้ดูแล</p>
        <a href="clinic_update.php?Mode=<?=$Mode?>" class="btn btn-outline-secondary btn-sm">ตั้งค่า</a>
      </div>
    </div>

    <div class="col-sm-6 col-md-4 col-lg-3">
      <div class="card card-menu p-4 shadow-sm">
        <i class="fa-solid fa-user-gear text-danger"></i>
        <h5>ผู้ใช้งานระบบ</h5>
        <p class="text-muted small">เพิ่ม/แก้ไข/ลบ ผู้ใช้งานระบบ</p>
        <a href="user_update.php?Mode=<?=$Mode?>" class="btn btn-outline-secondary btn-sm">จัดการ</a>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <div class="text-center mt-5">
    <a href="logout.php" class="btn btn-outline-danger btn-lg"><i class="fa fa-sign-out"></i> ออกจากระบบ</a>
  </div>
</div>

<script>
function toggleDarkMode() {
  document.body.classList.toggle('dark-mode');
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
