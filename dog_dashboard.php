<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dashboard สุนัขของคุณ</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
/* รูปสุนัขวงกลม */
.card-img-top {
    width: 120px;
    height: 120px;
    object-fit: cover;
    border-radius: 50%;
    border: 2px solid #ccc;
    margin: 0 auto;
    margin-top: 15px;
}

/* การ์ด */
.card {
    transition: transform 0.2s, box-shadow 0.2s;
    border-radius: 10px;
    text-align: center;
    padding-bottom: 10px;
}
.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.2);
}

/* Dark Mode */
body.dark {
    background-color: #1a1a1a; /* พื้นเทาเข้ม อ่านตัวอักษรขาวง่าย */
    color: #f1f1f1;
}
body.dark .card {
    background-color: #2a2a2a; /* การ์ดแยกจากพื้นหลังชัดเจน */
    color: #f1f1f1;
    border: 1px solid #333;
}
body.dark .card-body {
    color: #f1f1f1;
}

/* Dark Mode สำหรับข้อมูลเจ้าของ */
body.dark .owner-info h3 {
    color: #b4adadff; /* ชื่อเจ้าของเด่น */
}
body.dark .owner-info p {
    color: #d5cdcdff; /* ข้อมูลอื่น ๆ อ่านง่าย */
}

/* ปุ่ม Dark Mode */
body.dark .btn-primary { background-color: #0dcaf0; color: #000; }
body.dark .btn-secondary { background-color: #444; color: #fff; }

</style>
</head>

<?php
session_start();
include 'dbconnect.php';
include 'function.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// ดึงข้อมูลเจ้าของ
$user_id = $_SESSION['user_id'];
$owner = mysqli_fetch_assoc(mysqli_query($objCon, "SELECT * FROM user WHERE id=$user_id"));

// ดึงข้อมูลหมาของเจ้าของ
$dogs = [];
$result = mysqli_query($objCon, "SELECT * FROM dogs WHERE user_id=$user_id ORDER BY dog_name ASC");
while ($row = mysqli_fetch_assoc($result)) {
    $dogs[] = $row;
}
?>
<div class="container my-4 text-center">

    <!-- ปุ่ม Toggle Dark Mode -->
    <div class="mb-3 text-end">
        <button id="toggleDark" class="btn btn-secondary btn-sm">สลับ Dark Mode</button>
    </div>

    <!-- ข้อมูลเจ้าของ -->
    <div class="mb-4 owner-info">
        <h3>เจ้าของ: <?= htmlspecialchars($owner['fullname']) ?></h3>
        <p>โทร: <?= htmlspecialchars($owner['tel'] ?? '-') ?> | ที่อยู่: <?= htmlspecialchars($owner['address'] ?? '-') ?> | Email: <?= htmlspecialchars($owner['email'] ?? '-') ?> | Line: <?= htmlspecialchars($owner['line_id'] ?? '-') ?> | บัตรประชาชน: <?= htmlspecialchars($owner['id_card'] ?? '-') ?></p>
    </div>

    <!-- Card ของหมา -->
    <div class="d-flex flex-wrap justify-content-center gap-4">
        <?php if(count($dogs) == 0): ?>
            <p class="text-muted">คุณยังไม่มีสุนัขในระบบ</p>
        <?php else: ?>
            <?php foreach ($dogs as $dog): ?>
                <div class="card shadow-sm" style="width: 18rem;">
                    <img src="<?= htmlspecialchars($dog['dog_image_path'] ?: 'images/no-dog.png') ?>" class="card-img-top">
                    <div class="card-body text-start">
                        <h5 class="card-title"><?= htmlspecialchars($dog['dog_name']) ?></h5>
                        <p class="mb-1"><b>สายพันธุ์:</b> <?= htmlspecialchars($dog['dog_breed'] ?? '-') ?></p>
                        <p class="mb-1"><b>อายุ:</b> <?= $dog['dog_age'] ?? '-' ?> ปี</p>
                        <p class="mb-1"><b>น้ำหนัก:</b> <?= $dog['dog_weight'] ?? '-' ?> กก.</p>
                        <p class="mb-1"><b>เพศ:</b> <?= htmlspecialchars($dog['dog_gender'] ?? '-') ?></p>
                        <div class="d-flex justify-content-center mt-3">
                            <a href="dog_profile.php?dog_id=<?= $dog['dog_id'] ?>" class="btn btn-primary btn-sm">🐶 ดูประวัติเต็ม</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- ปุ่มออกจากระบบ -->
    <div class="mt-4">
        <a href="logout.php" class="btn btn-secondary btn-lg">ออกจากระบบ</a>
    </div>

</div>

<script>
const toggleBtn = document.getElementById('toggleDark');
toggleBtn.addEventListener('click', () => {
    document.body.classList.toggle('dark');
});
</script>
</body>
</html>
