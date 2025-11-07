<?php
@session_start();
require_once('dbconnect.php');

if (!isset($_SESSION['user_id'])) {
  header("Location: index.php");
  exit();
}

$id = $_GET['id'] ?? 0;
$q = mysqli_query($objCon, "SELECT * FROM products WHERE product_id='$id'");
$p = mysqli_fetch_assoc($q);
if (!$p) { die("ไม่พบข้อมูลสินค้า"); }
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>📦 รับของเข้าสต็อก</title>

<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<style>
:root {
  --bg-dark: radial-gradient(circle at top, #1b2735 0%, #090a0f 80%);
  --card-bg: rgba(255,255,255,0.08);
  --text-main: #fff;
  --text-sub: #bbb;
  --accent: #00e676;
}
body.light-mode {
  --bg-dark: linear-gradient(150deg, #f2f6fa 0%, #e8f5e9 100%);
  --card-bg: #fff;
  --text-main: #222;
  --text-sub: #555;
  --accent: #00bfa5;
}
body {
  font-family: 'Prompt', sans-serif;
  background: var(--bg-dark);
  color: var(--text-main);
  transition: all 0.4s ease;
}
.theme-toggle {
  position: fixed;
  top: 15px; right: 15px;
  background: var(--card-bg);
  border: 1px solid rgba(255,255,255,0.3);
  color: var(--text-main);
  border-radius: 50%;
  width: 45px; height: 45px;
  display: flex; justify-content: center; align-items: center;
  cursor: pointer;
  z-index: 1000;
}
.container-box {
  background: var(--card-bg);
  border-radius: 15px;
  padding: 25px;
  margin-top: 90px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.2);
  max-width: 600px;
}
.btn-main {
  background: linear-gradient(45deg, #00e676, #00bfa5);
  border: none; color: #000; font-weight: bold;
}
.btn-main:hover { opacity: 0.9; }
</style>
</head>

<body>
<div class="theme-toggle" onclick="toggleTheme()"><i class="fa fa-moon"></i></div>

<div class="container container-box">
  <h3 class="text-center mb-4"><i class="fa fa-box"></i> รับของเข้าสต็อก</h3>

  <form id="receiveForm">
    <input type="hidden" name="product_id" value="<?=$p['product_id']?>">

    <div class="mb-3">
      <label class="form-label">ชื่อสินค้า</label>
      <input type="text" class="form-control" value="<?=$p['product_name']?>" readonly>
    </div>

    <div class="mb-3">
      <label class="form-label">หมวดหมู่</label>
      <input type="text" class="form-control" value="<?=$p['category']?>" readonly>
    </div>

    <div class="mb-3">
      <label class="form-label">จำนวนคงเหลือปัจจุบัน</label>
      <input type="text" class="form-control" value="<?=$p['stock_qty']?>" readonly>
    </div>

    <div class="mb-3">
      <label class="form-label">จำนวนที่รับเข้าใหม่</label>
      <input type="number" name="add_qty" class="form-control" min="1" required>
    </div>

    <div class="text-center mt-4">
      <button type="submit" class="btn btn-main"><i class="fa fa-save"></i> บันทึกการรับของ</button>
      <a href="stock_manage.php" class="btn btn-secondary">กลับ</a>
    </div>
  </form>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script>
$('#receiveForm').submit(function(e){
  e.preventDefault();
  $.post('stock_action.php?action=receive', $(this).serialize(), function(res){
    alert(res);
    window.location='stock_manage.php';
  });
});

function toggleTheme(){
  document.body.classList.toggle('light-mode');
  const icon = document.querySelector('.theme-toggle i');
  icon.classList.toggle('fa-sun');
  icon.classList.toggle('fa-moon');
}
</script>
</body>
</html>
