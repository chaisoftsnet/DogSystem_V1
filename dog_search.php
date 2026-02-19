<?php
session_start();
require_once 'dbconnect.php';

if(!isset($_SESSION['user_id'])){
//    header("Location: index.php");
 //   exit();
}

$field   = $_GET['field'] ?? 'tel';
$keyword = $_GET['keyword'] ?? '';

$dogs = [];

if($keyword !== ''){
    $f = mysqli_real_escape_string($objCon, $field);
    $k = mysqli_real_escape_string($objCon, $keyword);

    $sql = "
        SELECT d.*, u.fullname, u.tel, u.address, u.email, u.line_id
        FROM dogs d
        JOIN user u ON d.user_id = u.id
        WHERE u.$f LIKE '%$k%'
        ORDER BY d.dog_name ASC
    ";

    $dogs = mysqli_query($objCon, $sql);
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>ค้นหาสุนัข</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
    background-color: #f4f4f4;
    font-family: 'Prompt', sans-serif;
    transition: .25s;
}

/* Dark mode */
body.dark {
    background-color: #1a1a1a;
    color: #f1f1f1;
}
body.dark .card {
    background-color: #2a2a2a;
    color: #f1f1f1;
    border: 1px solid #333;
}
body.dark .form-control, 
body.dark .form-select {
    background-color: #333;
    color: #fff;
    border: 1px solid #555;
}
body.dark .btn-secondary {
    background-color: #444;
}

/* dog card */
.card-img-top {
    width: 120px;
    height: 120px;
    object-fit: cover;
    border-radius: 50%;
    border: 2px solid #ccc;
    margin: 0 auto;
    margin-top: 15px;
}
.card {
    transition: .2s;
    border-radius: 12px;
    text-align: center;
}
.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.2);
}
</style>
</head>

<body>

<div class="container my-4">

    <!-- Dark Mode Button -->
    <div class="text-end mb-3">
        <button id="toggleDark" class="btn btn-secondary btn-sm">สลับ Dark Mode</button>
    </div>

    <h3 class="mb-3"><i class="fa fa-search"></i> ค้นหาสุนัขตามข้อมูลเจ้าของ</h3>

    <!-- Search Form -->
    <form method="GET" class="card p-3 mb-4">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">ค้นหาตาม</label>
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

    <!-- Search Results -->
    <div class="row g-4">
    <?php
    if($keyword !== '' && $dogs && mysqli_num_rows($dogs) > 0):
        while($dog = mysqli_fetch_assoc($dogs)):
    ?>
        <div class="col-md-4">
            <div class="card shadow">
                <img src="<?=($dog['dog_image_path'] ?: 'images/no-dog.png')?>" class="card-img-top">
                <div class="card-body text-start">
                    <h5 class="card-title"><?=htmlspecialchars($dog['dog_name'])?></h5>
                    <p><b>สายพันธุ์:</b> <?=$dog['dog_breed']?></p>
                    <p><b>เจ้าของ:</b> <?=$dog['fullname']?></p>
                    <p><b>โทร:</b> <?=$dog['tel']?></p>
                    <p><b>ที่อยู่:</b> <?=$dog['address']?></p>

                    <a href="dog_profile.php?dog_id=<?=$dog['dog_id']?>" class="btn btn-primary btn-sm w-100 mt-2">
                        🐾 ดูข้อมูลสุนัข
                    </a>

                </div>
            </div>
        </div>
    <?php
        endwhile;
    elseif($keyword !== ''):
        echo "<p class='text-center text-danger mt-4'>❌ ไม่พบข้อมูลที่ตรงกับคำค้นหา</p>";
    endif;
    ?>
    </div>

    <div class="text-center mt-4">
        <a href="dog_dashboard.php" class="btn btn-secondary btn-lg">กลับ Dashboard</a>
    </div>

</div>

<script>
document.getElementById('toggleDark').onclick = function(){
    document.body.classList.toggle('dark');
};
</script>

</body>
</html>
