<?php
// **-file dashboard_FrontDesk.php -**//
@session_start();
include 'dbconnect.php';
include 'function.php';

/* ===============================
   SECURITY
================================ */
if (!isset($_SESSION['id'])) {
    // header("Location: login.php");
    // exit;
}

if ($_SESSION['role'] == 'doctor') {
    header("Location: doctor.php");
    exit;
}

$clinic_id = $_SESSION['clinic_id'];
$user_name = $_SESSION['fullname'];

/* ===============================
   DATE (default = today)
================================ */
$queue_date = $_GET['date'] ?? date('Y-m-d'); // ★
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>Dashboard | หน้าร้านคลินิก</title>

<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/chsn_theme.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
/* ===============================
   QUEUE LOAD (by date)
================================ */
let queueDate = '<?=$queue_date?>'; // ★

function loadQueue(){
    fetch('ajax_queue_by_date.php?date=' + queueDate) // ★
      .then(res => res.text())
      .then(html => {
          document.getElementById('queueBody').innerHTML = html;
      });
}

document.addEventListener("DOMContentLoaded", loadQueue);

/* ===============================
   THEME
================================ */
function applyTheme(){
  const theme = localStorage.getItem('theme') || 'dark';
  if(theme === 'light'){
    document.body.classList.add('light');
    document.getElementById('themeBtn').innerText = '☀️ Light';
  }else{
    document.body.classList.remove('light');
    document.getElementById('themeBtn').innerText = '🌙 Dark';
  }
}

function toggleTheme(){
  if(document.body.classList.contains('light')){
    localStorage.setItem('theme','dark');
  }else{
    localStorage.setItem('theme','light');
  }
  applyTheme();
}

document.addEventListener("DOMContentLoaded",applyTheme);
</script>
</head>

<body>

<!-- ===============================
     TOP BAR
================================ -->
<div class="topbar">
  <div class="logo topbar-text">
    🐶 ระบบคลินิกสุนัข | คลินิก: <?=ret_clinic($clinic_id,$objCon);?>
  </div>

  <div class="topbar-text" style="display:flex;align-items:center;gap:10px;">
    <?=$user_name?>
    <button class="theme-btn" onclick="toggleTheme()" id="themeBtn">
      🌙 Dark
    </button>
  </div>
</div>

<div class="container">

<!-- ===============================
     ACTION BAR
================================ -->
<div class="action-bar"><br>
  <button class="btn-new" onclick="openWalkin()">🚶‍♂️ Walk-in</button>
  <button class="btn-old" onclick="openOldCustomer()">🔍 ลูกค้าเก่า</button>
  <button class="btn-app" onclick="openAppointments()">📅 นัดวันนี้</button>
  <button class="btn-app" onclick="location.href='index.php'">🚪 ออกจากระบบ</button>
</div>

<!-- ===============================
     DATE FILTER (เล็ก กระชับ)
================================ -->
<form method="get" style="margin-top:20px;display:flex;align-items:center;gap:8px;">
  <label>📅 วันที่</label>
  <input type="date"
         name="date"
         value="<?=$queue_date?>"
         style="width:150px;padding:4px 6px;"> <!-- ★ ไม่กว้าง -->
  <button class="btn-app">ค้นหา</button>
</form>

<!-- ===============================
     QUEUE TABLE
================================ -->
<h3 style="margin-top:20px;">
📋 คิววันที่ <?=date('d/m/Y',strtotime($queue_date))?>
</h3>

<table class="queue-table">
<thead>
<tr>
  <th>ลำดับ</th>
  <th>เวลา</th>
  <th>รุป</th>
  <th>สุนัข</th>  
  <th>เจ้าของ</th>
  <th>สถานะ</th>
  <th>ดำเนินการ</th>
</tr>
</thead>
<tbody id="queueBody">
  <tr><td colspan="5">กำลังโหลดข้อมูล...</td></tr>
</tbody>
</table>

<div class="footer-note">
✔ หน้านี้สำหรับเจ้าหน้าที่หน้าร้านเท่านั้น<br>
✔ ไม่มีสิทธิ์บันทึกการรักษา
</div>
</div>

<!-- ===============================
     WALK-IN MODAL (เดิม)
================================ -->
<div id="walkinModal"
     style="display:none;
            position:fixed;
            top:0;left:0;
            width:100%;height:100%;
            background:rgba(0,0,0,0.65);
            z-index:99999;">
<div class="popup-card"
     style="
        width:90%;
        max-width:720px;
        height:90%;
        margin:3% auto;
        overflow:hidden;
        position:relative;
        border-radius:20px;
     ">

<button onclick="closeWalkin()"
  style="position:absolute;top:12px;right:16px;
         background:none;border:none;
         font-size:22px;
         color:#e5e7eb;
         cursor:pointer;">
  ✕
</button>

<iframe id="walkinFrame"
  src=""
  style="width:100%;height:100%;border:none;">
</iframe>

</div>
</div>

<!-- ===============================
     JS CONTROL
================================ -->
<script>
function openWalkin(){ //ลูกค้าใหม่
    document.getElementById('walkinFrame').src = 'walkin.php?step=2';
    document.getElementById('walkinModal').style.display = 'block';
}
function openOldCustomer(){ //ลูกค้าเก่า
    document.getElementById('walkinFrame').src = 'search_customer.php';
    document.getElementById('walkinModal').style.display = 'block';
}
function openAppointments(){ //นัดวันนี้
    document.getElementById('walkinFrame').src = 'appointments_today.php';
    document.getElementById('walkinModal').style.display = 'block';
}
function openVisitPopup(visit_id){ //เปิดเคส
    document.getElementById('walkinFrame').src = 'visit_summary.php?visit_id=' + visit_id;
    document.getElementById('walkinModal').style.display = 'block';
}

function openCashier(invoice_id){ //เปิดหน้าชำระเงิน
  document.getElementById('walkinFrame').src = 'cashier.php?invoice_id=' + invoice_id;
  document.getElementById('walkinModal').style.display = 'block';
}

function openReceipt(invoice_id){ //เปิดหน้าพิมพ์ใบเสร็จ
  window.open(
    'receipt.php?invoice_id=' + invoice_id,
    '_blank'
  );
}
function openInvoicePrint(invoice_id){ //เปิดหน้าพิมพ์ใบเสร็จ
  window.open(
    'invoice_print.php?invoice_id=' + invoice_id,
    '_blank'
  );
}
document.addEventListener('keydown', function(e){ //ปิด popup ด้วยปุ่ม ESC
  if(e.key === 'Escape'){
    closeWalkin();
  }
});
function closeWalkin(){ //ปิด popup
    document.getElementById('walkinModal').style.display = 'none';
    document.getElementById('walkinFrame').src = '';
}
function openDogImagePopup(dog_id){
    document.getElementById('walkinFrame').src =
        'dog_edit_image_popup.php?dog_id=' + dog_id;
    document.getElementById('walkinModal').style.display = 'block';
}
</script>

</body>
</html>
