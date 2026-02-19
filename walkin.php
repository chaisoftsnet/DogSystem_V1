<?php
@session_start();
include 'dbconnect.php';

/* ===============================
   BASIC SECURITY
================================ */
if (!isset($_SESSION['clinic_id'])) {
    exit('no session');
}

$clinic_id = intval($_SESSION['clinic_id']);
$step = isset($_GET['step']) ? intval($_GET['step']) : 1;

/* ===============================
   RESET WIZARD (STEP 1)
================================ */
if ($step === 1) {
    $_SESSION['walkin'] = [];
}

/* ===============================
   SAVE OWNER
================================ */
if (isset($_POST['save_owner'])) {
    $fullname = mysqli_real_escape_string($objCon, $_POST['fullname']);
    $phone    = mysqli_real_escape_string($objCon, $_POST['phone']);
    $email    = mysqli_real_escape_string($objCon, $_POST['email']);
    mysqli_query($objCon,"
        INSERT INTO user
        (username,password,fullname,tel,email,clinic_id,role)
        VALUES
        ('$phone','$phone','$fullname','$phone','$email',$clinic_id,1)
    ");

    $_SESSION['walkin']['user_id']  = mysqli_insert_id($objCon);
    $_SESSION['walkin']['fullname'] = $fullname;

    header("Location: walkin.php?step=3");
    exit;
}

/* ===============================
   SAVE DOG
================================ */
if (isset($_POST['save_dog'])) {

    if (empty($_SESSION['walkin']['user_id'])) {
        header("Location: walkin.php?step=1");
        exit;
    }

    $user_id = intval($_SESSION['walkin']['user_id']);

    $dog_name   = mysqli_real_escape_string($objCon,$_POST['dog_name']);
    $dog_gender = mysqli_real_escape_string($objCon,$_POST['dog_gender']);
    $dog_age    = intval($_POST['dog_age']);
    $dog_weight = intval($_POST['dog_weight']);
    $dog_breed  = mysqli_real_escape_string($objCon,$_POST['dog_breed']);

    /* ===== UPLOAD DOG IMAGE ===== */
    $dog_image_path = NULL;

    if (!empty($_FILES['dog_image']['name'])) {

        $upload_dir = "uploads/dogs/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $ext = strtolower(pathinfo($_FILES['dog_image']['name'], PATHINFO_EXTENSION));
        $filename = "dog_" . time() . "_" . rand(1000,9999) . "." . $ext;
        $target = $upload_dir . $filename;

        if (move_uploaded_file($_FILES['dog_image']['tmp_name'], $target)) {
            $dog_image_path = $target;
        }
    }

    mysqli_query($objCon,"
        INSERT INTO dogs
        (user_id, clinic_id,
         dog_name, dog_gender,
         dog_age, record_date,
         dog_weight, dog_breed,
         dog_image_path)
        VALUES
        ($user_id, $clinic_id,
         '$dog_name', '$dog_gender',
         $dog_age, CURDATE(),
         $dog_weight, '$dog_breed',
         ".($dog_image_path ? "'$dog_image_path'" : "NULL").")
    ");

    $_SESSION['walkin']['dog_id']   = mysqli_insert_id($objCon);
    $_SESSION['walkin']['dog_name'] = $dog_name;

    header("Location: walkin.php?step=4");
    exit;
}

/* ===============================
   CREATE VISIT (FINAL)
================================ */
if (isset($_POST['create_visit'])) {

    if (
        empty($_SESSION['walkin']['user_id']) ||
        empty($_SESSION['walkin']['dog_id'])
    ) {
        header("Location: walkin.php?step=1");
        exit;
    }

    mysqli_query($objCon,"
        INSERT INTO visits
        (clinic_id, user_id, dog_id, visit_date, status)
        VALUES
        ($clinic_id,
         {$_SESSION['walkin']['user_id']},
         {$_SESSION['walkin']['dog_id']},
         NOW(), 'รอตรวจ')
    ");

    unset($_SESSION['walkin']);

    echo "
    <script>
      if (window.parent) {
        window.parent.closeWalkin();
        if (window.parent.loadQueue) {
          window.parent.loadQueue();
        }
      }
    </script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>Walk-in</title>

<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/chsn_theme.css">

<script>
function applyTheme(){
  const theme = localStorage.getItem('theme') || 'dark';
  document.body.classList.toggle('light', theme === 'light');
}
window.onload = applyTheme;
</script>

<style>
body{font-family:'Prompt',sans-serif;padding:20px;}
.card{background:var(--card);border-radius:12px;padding:20px;}
input,select{margin-bottom:10px;}
</style>
</head>

<body>
<div class="card">

<?php if ($step === 1): ?>

<h3>🐶 รับลูกค้า Walk-in</h3>
<a href="walkin.php?step=2">👤 ลูกค้าใหม่</a>

<?php elseif ($step === 2): ?>

<h3>👤 ข้อมูลเจ้าของ</h3>
<form method="post">
ชื่อ–สกุล*
<input name="fullname" required>

เบอร์โทร*
<input name="phone" required>

Email
<input name="email">

<button name="save_owner">ถัดไป →</button>
</form>

<?php elseif ($step === 3): ?>

<h3>🐶 ข้อมูลสุนัข</h3>
<form method="post" enctype="multipart/form-data">

ชื่อสุนัข*
<input name="dog_name" required>

เพศ
<select name="dog_gender">
  <option value="ผู้">ผู้</option>
  <option value="เมีย">เมีย</option>
</select>

อายุ (ปี ณ วันที่บันทึก)*
<input type="number" name="dog_age" required>

น้ำหนัก (กก.)
<input type="number" name="dog_weight">

สายพันธุ์
<input name="dog_breed">

รูปสุนัข
<input type="file" name="dog_image" accept="image/*">

<button name="save_dog">ถัดไป →</button>
</form>

<?php elseif ($step === 4): ?>

<h3>📋 ยืนยันการรับคิว</h3>
<p>
เจ้าของ: <?=htmlspecialchars($_SESSION['walkin']['fullname'])?><br>
สุนัข: <?=htmlspecialchars($_SESSION['walkin']['dog_name'])?><br>
สถานะเริ่มต้น: รอตรวจ
</p>

<form method="post">
<button name="create_visit">✅ รับคิว</button>
</form>

<?php endif; ?>

</div>
</body>
</html>
