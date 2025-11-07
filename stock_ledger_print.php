<?php
@session_start();
require_once('dbconnect.php');
if(!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }

$product_id = $_GET['product_id'] ?? '';

if($product_id){
  // ✅ ดึงเฉพาะสินค้านี้
  $p = mysqli_fetch_assoc(mysqli_query($objCon, "SELECT * FROM products WHERE product_id=$product_id"));
  $title = "สมุดรายวันคลังยา - " . $p['product_name'];
  $sql = "SELECT t.*, p.product_name, u.fullname 
          FROM stock_transactions t
          LEFT JOIN products p ON t.product_id = p.product_id
          LEFT JOIN user u ON t.user_id = u.id
          WHERE t.product_id=$product_id
          ORDER BY t.created_at DESC";
} else {
  // ✅ ดึงทุกสินค้า
  $p = null;
  $title = "สมุดรายวันคลังยา (ทั้งหมด)";
  $sql = "SELECT t.*, p.product_name, u.fullname 
          FROM stock_transactions t
          LEFT JOIN products p ON t.product_id = p.product_id
          LEFT JOIN user u ON t.user_id = u.id
          ORDER BY p.product_name ASC, t.created_at DESC";
}

$q = mysqli_query($objCon, $sql);
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title><?=$title?></title>
<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap" rel="stylesheet">
<style>
body {
  font-family: 'Sarabun', sans-serif;
  background: #fff;
  color: #000;
  margin: 40px;
}
.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 2px solid #00bfa5;
  padding-bottom: 10px;
}
.header img { height: 70px; }
h2 {
  text-align: center;
  color: #00796b;
  margin: 10px 0 0;
}
.subtitle {
  text-align: center;
  color: #444;
  margin-bottom: 20px;
}
table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
}
th, td {
  border: 1px solid #aaa;
  padding: 8px;
  text-align: center;
}
th {
  background: linear-gradient(45deg, #00bfa5, #1de9b6);
  color: #000;
}
td { font-size: 14px; }
.product-group {
  background-color: #e0f2f1;
  font-weight: bold;
  text-align: left;
  padding: 8px;
  font-size: 15px;
}
.footer {
  margin-top: 50px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.signature {
  text-align: center;
  width: 250px;
}
.signature img { width: 150px; margin-bottom: 5px; }
.summary {
  margin-top: 30px;
  border-top: 2px solid #00bfa5;
  padding-top: 10px;
}
</style>
</head>
<body onload="window.print()">

<div class="header">
  <img src="images/clinic_logoPP.png" alt="Clinic Logo">
  <div style="text-align:right">
    <strong>คลินิกรักษาสัตว์ PPC บ้านรามอินทรา</strong><br>
    โทร: 02-123-4567 | อีเมล: info@ppcvetclinic.com
  </div>
</div>

<h2>สมุดรายวันคลังยาและวัคซีน</h2>
<p class="subtitle">
  <?=$product_id ? "รายงานเฉพาะสินค้า: <strong>{$p['product_name']}</strong>" : "รายงานรวมสินค้าทั้งหมดในระบบ"?>
</p>

<table>
  <thead>
    <tr>
      <th>#</th>
      <th>วันที่ทำรายการ</th>
      <th>ชื่อสินค้า</th>
      <th>ประเภท</th>
      <th>จำนวน</th>
      <th>ผู้ทำรายการ</th>
      <th>หมายเหตุ</th>
    </tr>
  </thead>
  <tbody>
  <?php
    $i = 1; 
    $total_in = $total_out = 0;
    $current_product = '';
    while($r = mysqli_fetch_assoc($q)){
      if(!$product_id && $r['product_name'] != $current_product){
        $current_product = $r['product_name'];
        echo "<tr><td colspan='7' class='product-group'>📦 {$current_product}</td></tr>";
      }

      $type = $r['trans_type']=='IN' ? 'รับเข้า' : 'เบิกออก';
      $color = $r['trans_type']=='IN' ? '#2e7d32' : '#c62828';

      if($r['trans_type']=='IN') $total_in += $r['quantity'];
      else $total_out += $r['quantity'];

      echo "
      <tr>
        <td>{$i}</td>
        <td>".date('d/m/Y H:i', strtotime($r['created_at']))."</td>
        <td>{$r['product_name']}</td>
        <td style='color:{$color}; font-weight:bold'>{$type}</td>
        <td>{$r['quantity']}</td>
        <td>{$r['fullname']}</td>
        <td>{$r['note']}</td>
      </tr>";
      $i++;
    }
  ?>
  </tbody>
</table>

<div class="summary">
  <strong>รวมจำนวนการรับเข้า:</strong> <?=$total_in?> รายการ  
  <br><strong>รวมจำนวนการเบิกออก:</strong> <?=$total_out?> รายการ  
  <br><strong>คงเหลือโดยประมาณ:</strong> <?=$total_in - $total_out?> หน่วย
</div>

<div class="footer">
  <div class="signature">
    <p>ลงชื่อผู้ตรวจสอบ</p>
    <img src="images/signature.png" alt="Signature"><br>
    (สัตวแพทย์ผู้รับผิดชอบ)
  </div>
  <div style="text-align:right; color:#555;">
    วันที่พิมพ์: <?=date('d/m/Y H:i')?> น.
  </div>
</div>

</body>
</html>
