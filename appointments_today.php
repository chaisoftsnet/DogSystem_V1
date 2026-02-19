<?php
@session_start();
include 'dbconnect.php';

if (!isset($_SESSION['clinic_id'])) {
    exit('no session');
}

$clinic_id = intval($_SESSION['clinic_id']);

/* ===============================
   RECEIVE APPOINTMENT → VISIT
================================ */
if (isset($_POST['receive_appointment'])) {

    $appointment_id = intval($_POST['appointment_id']);
    $user_id = intval($_POST['user_id']);
    $dog_id  = intval($_POST['dog_id']);

    // สร้างคิว
    mysqli_query($objCon,"
        INSERT INTO visits
        (clinic_id,user_id,dog_id,visit_date,status)
        VALUES
        ($clinic_id,$user_id,$dog_id,NOW(),'รอตรวจ')
    ");

    // ปิดสถานะนัด
    mysqli_query($objCon,"
        UPDATE appointments
        SET status='เสร็จสิ้น'
        WHERE appointment_id=$appointment_id
    ");

    // ปิด popup + refresh queue
    echo "
    <script>
      if(window.parent){
        window.parent.closeWalkin();
        if(window.parent.loadQueue){
          window.parent.loadQueue();
        }
      }
    </script>
    ";
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>นัดวันนี้</title>

<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/chsn_theme.css">

<!-- ✅ Dark / Light Theme สำหรับ iframe -->
<script>
function applyTheme(){
  const theme = localStorage.getItem('theme') || 'dark';
  if(theme === 'light'){
    document.body.classList.add('light');
  }else{
    document.body.classList.remove('light');
  }
}
window.onload = applyTheme;
</script>
<style>
body{font-family:'Prompt',sans-serif;padding:20px;}
.box{padding:20px;border-radius:10px;max-width:800px;margin:auto;}
.app{border-bottom:1px solid #ddd;padding:10px 0;display:flex;justify-content:space-between;align-items:center;}
.btn{background:#3b82f6;color:#fff;border:none;padding:6px 12px;border-radius:5px;cursor:pointer;}
.time{font-weight:600;}
</style>
</head>

<body>
<div class="box">
<h3>📅 นัดวันนี้</h3>
<?php
$sql = "
SELECT a.appointment_id,a.appointment_date,
       d.dog_id,d.dog_name,
       u.id AS user_id,u.fullname
FROM appointments a
JOIN dogs d ON a.dog_id = d.dog_id
JOIN user u ON d.user_id = u.id
WHERE a.clinic_id = $clinic_id
AND DATE(a.appointment_date) = CURDATE()
AND a.status = 'รอพบแพทย์'
ORDER BY a.appointment_date
";

$res = mysqli_query($objCon,$sql);
if (mysqli_num_rows($res) == 0) {
    echo "<p>ไม่มีนัดวันนี้</p>";
}

while($r = mysqli_fetch_assoc($res)):
?>
<div class="app">
  <div>
    <span class="time">
      <?=date('H:i',strtotime($r['appointment_date']))?>
    </span>
    🐶 <?=$r['dog_name']?> —
    👤 <?=$r['fullname']?>
  </div>

  <form method="post" style="margin:0;">
    <input type="hidden" name="appointment_id" value="<?=$r['appointment_id']?>">
    <input type="hidden" name="user_id" value="<?=$r['user_id']?>">
    <input type="hidden" name="dog_id" value="<?=$r['dog_id']?>">
    <button class="btn" name="receive_appointment">
      รับเข้าคิว
    </button>
  </form>
</div>
<?php endwhile; ?>

</div>
</body>
</html>
