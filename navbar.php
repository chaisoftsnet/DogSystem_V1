<?php
$Mode = $_REQUEST['Mode'];
if ($Mode == '') { ?>
  <link rel="stylesheet" type="text/css" href="css/darkmode.css">
<?php } else { ?>  
  <link rel="stylesheet" type="text/css" href="css/whitemode.css">  
<?php }
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'ผู้ใช้งาน';
?>

<div class="navbar" id="myNavbar">
  <!-- เมนูหลัก -->
  <a href="dashboard.php?Mode=<?=$Mode?>">🏠 หน้าหลัก</a>
  <a href="dog_update.php?Mode=<?=$Mode?>">🐶 ข้อมูลสัตว์</a>
  <a href="treatment_manage.php?Mode=<?=$Mode?>">📋 การรักษาพยาบาล</a>
  <a href="appointment_manage.php?Mode=<?=$Mode?>">📅 การนัดหมาย</a>
  <a href="vaccine_manage.php?Mode=<?=$Mode?>">💉 ฉีดวัคซีน</a>
  <a href="deworming_manage.php?Mode=<?=$Mode?>">💊 ถ่ายพยาธิ</a>
  <a href="LabResults_manage.php?Mode=<?=$Mode?>">🔬 ผลตรวจ Lab</a>
  <a href="Surgeries_manage.php?Mode=<?=$Mode?>">🩺 ผ่าตัด/หัตถการ</a>
  <a href="Nutrition_manage.php?Mode=<?=$Mode?>">🥗 โภชนาการ</a>
  <a href="boarding_manage.php?Mode=<?=$Mode?>">🏠 ฝากเลี้ยง</a>
  <a href="attachments_manage.php?Mode=<?=$Mode?>">📎 ไฟล์แนบ</a>  
  <!-- เมนูสำหรับ Clinic ขึ้นไป -->
  <?php if ($_SESSION['role'] >= 2) { ?>
    <a href="reportAll.php?report_type=dogs&Mode=<?=$Mode?>" target="_blank">📊 รายงาน</a>
  <?php } ?>

  <!-- เมนูสำหรับ Admin -->
  <?php if ($_SESSION['role'] == 3) { ?>   
    <a href="clinic_update.php?Mode=<?=$Mode?>">🏥 ข้อมูลคลินิก</a>
    <a href="user_update.php?Mode=<?=$Mode?>">👤 ผู้ใช้งาน</a>
  <?php } ?>

  <!-- ขวาสุด -->
  <div class="right">      
    <a href="logout.php">🚪 ออกจากระบบ</a>
  </div>

  <!-- ปุ่ม Toggle -->
  <a href="javascript:void(0);" class="icon" onclick="toggleNavbar()">&#9776;</a>
</div>

<script>
function toggleNavbar() {
  var x = document.getElementById("myNavbar");
  if (x.className === "navbar") {
    x.className += " responsive";
  } else {
    x.className = "navbar";
  }
}
</script>
