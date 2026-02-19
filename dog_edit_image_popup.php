<?php
@session_start();
include 'dbconnect.php';

/* ===============================
   SECURITY
================================ */
if (!isset($_SESSION['clinic_id'])) {
    exit;
}

$dog_id = intval($_GET['dog_id'] ?? 0);
if ($dog_id <= 0) {
    exit;
}

/* ===============================
   LOAD DOG
================================ */
$sql = "
    SELECT dog_name, dog_image_path
    FROM dogs
    WHERE dog_id = $dog_id
";
$q = mysqli_query($objCon, $sql);
$dog = mysqli_fetch_assoc($q);
if (!$dog) {
    exit;
}

/* ===============================
   PATH CONFIG (มาตรฐานเดียวทั้งระบบ)
================================ */
$uploadDir = 'uploads/dogs/';   // โฟลเดอร์เก็บรูป
$nullImage = 'images/no-pet.png';

/* ===============================
   CURRENT IMAGE (รองรับข้อมูลเก่า)
================================ */
if (!empty($dog['dog_image_path'])) {

    // กรณี DB เก็บ path เต็ม
    if (file_exists($dog['dog_image_path'])) {
        $img = $dog['dog_image_path'];

    // กรณี DB เก็บแค่ชื่อไฟล์ (ข้อมูลเก่า)
    } elseif (file_exists($uploadDir . $dog['dog_image_path'])) {
        $img = $uploadDir . $dog['dog_image_path'];

    } else {
        $img = $nullImage;
    }

} else {
    $img = $nullImage;
}
/* ===============================
   UPDATE IMAGE (PATH เต็ม)
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!empty($_FILES['dog_image']['name'])) {

        // ลบรูปเก่า (ถ้ามี)
        if (!empty($dog['dog_image_path']) && file_exists($dog['dog_image_path'])) {
            @unlink($dog['dog_image_path']);
        }

        // สร้างชื่อไฟล์ใหม่
        $ext = strtolower(pathinfo($_FILES['dog_image']['name'], PATHINFO_EXTENSION));
        $newFile = 'dog_' . $dog_id . '_' . time() . '.' . $ext;

        $uploadDir = 'uploads/dogs/';
        move_uploaded_file(
            $_FILES['dog_image']['tmp_name'],
            $uploadDir . $newFile
        );

        // เก็บ path เต็มลง DB
        $dbPath = $uploadDir . $newFile;
        mysqli_query($objCon, "
            UPDATE dogs
            SET dog_image_path = '$dbPath'
            WHERE dog_id = $dog_id
        ");
    }

    // ✅ ส่งสัญญาณให้หน้าหลัก refresh + ปิด popup
    echo "
    <script>
        if (parent && parent.loadQueue) {
            parent.loadQueue();      // refresh รูปในหน้าหลัก
        }
        if (parent && parent.closeWalkin) {
            parent.closeWalkin();    // ปิด popup
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
<title>แก้ไขรูปสุนัข</title>
<link rel="stylesheet" href="assets/css/chsn_theme.css">
</head>

<body style="padding:30px;text-align:center;">

<h2>🖼 แก้ไขรูปสุนัข</h2>
<h3><?=htmlspecialchars($dog['dog_name'])?></h3>

<img id="preview"
     src="<?=$img?>"
     style="width:220px;height:220px;
            object-fit:cover;
            border-radius:18px;
            border:1px solid #444;
            margin-bottom:20px;">

<form method="post" enctype="multipart/form-data">

  <input type="file"
         name="dog_image"
         accept="image/*"
         onchange="previewImage(this)"
         style="margin:auto;">

  <br><br>

  <button class="btn-new">💾 บันทึกรูป</button>
  <button type="button"
          class="btn-old"
          onclick="parent.closeWalkin()">
    ❌ ปิด
  </button>
</form>

<script>
function previewImage(input){
    if (input.files && input.files[0]) {
        document.getElementById('preview').src =
            URL.createObjectURL(input.files[0]);
    }
}
</script>

</body>
</html>
