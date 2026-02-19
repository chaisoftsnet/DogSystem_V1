<?php
@session_start();
include 'dbconnect.php';

if (!isset($_SESSION['clinic_id'])) {
    exit('no session');
}

$clinic_id = intval($_SESSION['clinic_id']);
$keyword = isset($_GET['q']) ? mysqli_real_escape_string($objCon,$_GET['q']) : '';

/* ===============================
   CREATE VISIT FOR OLD CUSTOMER
================================ */
if (isset($_POST['create_visit_old'])) {

    $user_id = intval($_POST['user_id']);
    $dog_id  = intval($_POST['dog_id']);

    mysqli_query($objCon,"
        INSERT INTO visits
        (clinic_id,user_id,dog_id,visit_date,status)
        VALUES
        ($clinic_id,$user_id,$dog_id,NOW(),'รอตรวจ')
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
<title>ค้นหาลูกค้าเก่า</title>

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
.box{padding:20px;border-radius:10px;max-width:700px;margin:auto;}
input{width:100%;padding:8px;margin-bottom:15px;}
.owner{border-bottom:1px solid #ddd;padding:10px 0;}
.dog{display:flex;justify-content:space-between;padding:5px 0;}
button{padding:6px 12px;border:none;border-radius:5px;cursor:pointer;}
.btn{background:#22c55e;color:#fff;}
</style>
</head>

<body>
<div class="box">

<h3>🔍 ค้นหาลูกค้าเก่า</h3>
<?php
$field   = $_GET['field'] ?? 'tel';
$keyword = $_GET['keyword'] ?? '';
?>
   <!-- Search Form -->
    <form method="GET" class="card p-3 mb-4">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-control">ค้นหาตาม</label>
                <select name="field" class="form-select">
                    <option value="tel"      <?=($field=='tel'?'selected':'')?>>เบอร์โทร</option>
                    <option value="email"    <?=($field=='email'?'selected':'')?>>อีเมล</option>
                    <option value="id_card"  <?=($field=='id_card'?'selected':'')?>>เลขบัตรประชาชน</option>
                    <option value="line_id"  <?=($field=='line_id'?'selected':'')?>>LINE ID</option>
                    <option value="address"  <?=($field=='address'?'selected':'')?>>ที่อยู่</option>
                    <option value="fullname" <?=($field=='fullname'?'selected':'')?>>ชื่อ-นามสกุล</option>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">คำค้นหา</label>
                <input type="text" name="keyword" value="<?=$keyword?>" class="form-control" placeholder="เช่น 089xxxxxxx หรือ LINE ID">
            </div>

            <div class="col-md-3">
                <label class="form-label d-none d-md-block">&nbsp;</label>
                <button class="btn btn-success w-100">ค้นหา</button>
            </div>
        </div>
    </form>


<?php
$dogs = [];
if($keyword !== ''){
    $f = mysqli_real_escape_string($objCon, $field);
    $k = mysqli_real_escape_string($objCon, $keyword);
echo $sql;  
    $sql = "
    SELECT u.id,u.fullname,u.username
    FROM user u
    WHERE u.$f LIKE '%$k%'    
    ORDER BY u.fullname
    LIMIT 10        
    ";

    $res = mysqli_query($objCon,$sql);
    while($u = mysqli_fetch_assoc($res)):
        echo "<div class='owner'>";
        echo "<strong>👤 {$u['fullname']}</strong><br>";

        $dogs = mysqli_query($objCon,"
            SELECT dog_id,dog_name
            FROM dogs
            WHERE user_id={$u['id']}
        ");
        while($d = mysqli_fetch_assoc($dogs)):
        ?>
        <div class="dog">
          🐶 <?=$d['dog_name']?>
          <form method="post" style="margin:0;">
            <input type="hidden" name="user_id" value="<?=$u['id']?>">
            <input type="hidden" name="dog_id" value="<?=$d['dog_id']?>">
            <button class="btn" name="create_visit_old">
              รับเข้าคิว
            </button>
          </form>
        </div>
        <?php
        endwhile;
        echo "</div>";
    endwhile;
}
?>

<hr>
<a href="walkin.php">➕ ลูกค้าใหม่ / เพิ่มสุนัขใหม่</a>

</div>
</body>
</html>
