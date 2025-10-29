<?php
@session_start();
require_once('dbconnect.php');

$invoice_id = $_GET['invoice_id'] ?? 0;

$sql = "SELECT i.*, u.fullname, u.tel, u.address, d.dog_name, d.dog_breed, c.clinic_name, c.address AS clinic_address, c.phone AS clinic_phone, c.email AS clinic_email
        FROM invoices i
        LEFT JOIN user u ON i.user_id = u.id
        LEFT JOIN dogs d ON i.dog_id = d.dog_id
        LEFT JOIN clinics c ON i.clinic_id = c.clinic_id
        WHERE i.invoice_id='$invoice_id'";
$q = mysqli_query($objCon, $sql);
$inv = mysqli_fetch_assoc($q);

$items = mysqli_query($objCon, "SELECT * FROM invoice_items WHERE invoice_id='$invoice_id'");
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>🧾 ใบแจ้งหนี้/ใบเสร็จรับเงิน #<?=$invoice_id?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
<style>
body { font-family: 'Prompt', sans-serif; background: #fff; color: #000; }
.invoice-box {
  max-width: 850px;
  margin: auto;
  padding: 30px;
  border: 1px solid #ccc;
  border-radius: 10px;
  background: #fff;
}
.header-logo { width: 100px; height: 100px; object-fit: contain; }
.table th, .table td { vertical-align: middle; }
.qr-box img { width: 120px; height: 120px; border: 1px solid #ccc; border-radius: 10px; }
.signature { height: 80px; border-bottom: 1px dotted #666; width: 200px; margin: auto; }
h4 { border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
@media print {
  .no-print { display: none; }
  body { background: #fff; }
}
</style>
</head>

<body>
<div class="invoice-box">
  <!-- ส่วนหัวคลินิก -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h3 class="mb-0">🏥 <?=$inv['clinic_name']?></h3>
      <small><?=$inv['clinic_address']?><br>
      โทร: <?=$inv['clinic_phone']?> | อีเมล: <?=$inv['clinic_email']?></small>
    </div>
    <div>
      <img src="images/clinic_logo.png" class="header-logo" alt="Clinic Logo">
    </div>
  </div>

  <hr>

  <!-- ข้อมูลใบแจ้งหนี้ -->
  <div class="row mb-3">
    <div class="col-6">
      <h5>📄 ใบแจ้งหนี้ / ใบเสร็จรับเงิน</h5>
      <p>
        เลขที่ใบแจ้งหนี้: <strong><?=$inv['invoice_id']?></strong><br>
        วันที่ออกบิล: <?=date('d/m/Y H:i', strtotime($inv['invoice_date']))?><br>
        สถานะ: <span class="fw-bold"><?=$inv['status']?></span><br>
        ช่องทางชำระ: <?=$inv['payment_method']?>
      </p>
    </div>
    <div class="col-6 text-end">
      <h5>👤 ลูกค้า</h5>
      <p>
        <?=$inv['fullname']?><br>
        <?=$inv['address']?><br>
        โทร: <?=$inv['tel']?><br>
        🐶 <?=$inv['dog_name']?> (<?=$inv['dog_breed']?>)
      </p>
    </div>
  </div>

  <!-- ตารางสินค้า -->
  <table class="table table-bordered">
    <thead class="table-secondary">
      <tr>
        <th>ลำดับ</th>
        <th>รายละเอียด</th>
        <th>จำนวน</th>
        <th>ราคาต่อหน่วย</th>
        <th>รวม</th>
      </tr>
    </thead>
    <tbody>
      <?php 
      $i=1; $sum=0;
      while($item = mysqli_fetch_assoc($items)){ 
        $total = $item['quantity'] * $item['unit_price'];
        $sum += $total;
      ?>
      <tr>
        <td><?=$i++?></td>
        <td><?=$item['description']?></td>
        <td><?=$item['quantity']?></td>
        <td class="text-end"><?=number_format($item['unit_price'],2)?></td>
        <td class="text-end"><?=number_format($total,2)?></td>
      </tr>
      <?php } ?>
      <tr>
        <td colspan="4" class="text-end"><strong>รวมทั้งสิ้น</strong></td>
        <td class="text-end"><strong><?=number_format($sum,2)?> บาท</strong></td>
      </tr>
    </tbody>
  </table>

  <div class="row mt-4">
    <div class="col-8">
      <h6>หมายเหตุ:</h6>
      <p><?=$inv['note'] ?: '-'?></p>
    </div>
    <div class="col-4 text-center">
      <div class="qr-box mb-2">
        <img src="images/qr_promptpay.png" alt="QR PromptPay">
      </div>
      <small>สแกนเพื่อชำระเงิน</small>
    </div>
  </div>

  <hr>

  <div class="row mt-4">
    <div class="col-6 text-center">
      <div class="signature"></div>
      <small>ลายเซ็นลูกค้า</small>
    </div>
    <div class="col-6 text-center">
      <div class="signature"></div>
      <small>ลายเซ็นสัตวแพทย์</small>
    </div>
  </div>

  <div class="text-center mt-4 no-print">
    <button onclick="window.print()" class="btn btn-success"><i class="fa fa-print"></i> พิมพ์</button>
    <a href="invoice_manage.php" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> กลับ</a>
  </div>

  <p class="text-center mt-5 small text-muted">
    *** เอกสารนี้ออกโดยระบบบริหารจัดการคลินิกรักษาสัตว์ © <?=date('Y')?> ***
  </p>
</div>
</body>
</html>
