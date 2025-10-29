<?php
@session_start();
require_once('dbconnect.php');
require_once('function.php');

$invoice_id = intval($_GET['id'] ?? 0);
if ($invoice_id <= 0) {
    die("<h3>ไม่พบข้อมูลใบแจ้งหนี้</h3>");
}

$sql = "SELECT i.*, 
        u.fullname AS owner_name, u.tel AS owner_tel, u.address AS owner_addr,
        d.dog_name, d.dog_breed, d.dog_gender,
        c.clinic_name, c.address AS clinic_addr, c.phone AS clinic_tel
        FROM invoices i
        LEFT JOIN user u ON i.user_id = u.id
        LEFT JOIN dogs d ON i.dog_id = d.dog_id
        LEFT JOIN clinics c ON i.clinic_id = c.clinic_id
        WHERE i.invoice_id = $invoice_id";
$q = mysqli_query($objCon, $sql);
$data = mysqli_fetch_assoc($q);
if (!$data) {
    die("<h3>ไม่พบข้อมูลใบแจ้งหนี้</h3>");
}

// สร้าง QR PromptPay สำหรับยอดเงิน
$promptpay_id = "0812345678"; // 🔹 กำหนด PromptPay เบอร์โทรคลินิก
$total = number_format($data['total_amount'], 2);
$qr_url = "https://promptpay.io/$promptpay_id/{$data['total_amount']}";
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>ใบแจ้งหนี้ / ใบเสร็จรับเงิน</title>
<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
<style>
body {
  font-family: 'Prompt', sans-serif;
  background: #fff;
  color: #000;
  padding: 40px;
  max-width: 800px;
  margin: auto;
}
.header {
  text-align: center;
  border-bottom: 3px solid #444;
  padding-bottom: 10px;
  margin-bottom: 20px;
}
.header img {
  width: 80px;
  border-radius: 50%;
}
h2 {
  margin: 10px 0 5px;
}
.info {
  margin-bottom: 20px;
}
table {
  width: 100%;
  border-collapse: collapse;
}
table th, table td {
  border: 1px solid #ccc;
  padding: 8px;
}
table th {
  background: #f8f8f8;
  text-align: center;
}
.total {
  text-align: right;
  font-weight: bold;
}
.qr {
  text-align: center;
  margin-top: 30px;
}
.print-btn {
  text-align: center;
  margin-top: 30px;
}
@media print {
  .print-btn { display: none; }
  body { padding: 0; margin: 0; }
}
</style>
</head>

<body>
  <div class="header">
    <img src="images/clinic_logo.png" alt="Clinic Logo">
    <h2><?= htmlspecialchars($data['clinic_name']) ?></h2>
    <p><?= htmlspecialchars($data['clinic_addr']) ?><br>
       โทร: <?= htmlspecialchars($data['clinic_tel']) ?></p>
  </div>

  <h3 style="text-align:center;">🧾 ใบแจ้งหนี้ / ใบเสร็จรับเงิน</h3>
  <div class="info">
    <b>เลขที่ใบแจ้งหนี้:</b> <?= $data['invoice_id'] ?><br>
    <b>วันที่:</b> <?= date('d/m/Y', strtotime($data['invoice_date'])) ?><br>
    <b>ลูกค้า:</b> <?= htmlspecialchars($data['owner_name']) ?><br>
    <b>เบอร์โทร:</b> <?= htmlspecialchars($data['owner_tel']) ?><br>
    <b>ที่อยู่:</b> <?= htmlspecialchars($data['owner_addr']) ?><br>
    <b>สัตว์เลี้ยง:</b> <?= htmlspecialchars($data['dog_name']) ?> (<?= htmlspecialchars($data['dog_breed']) ?>, <?= htmlspecialchars($data['dog_gender']) ?>)
  </div>

  <table>
    <thead>
      <tr>
        <th>ลำดับ</th>
        <th>รายละเอียด</th>
        <th>จำนวน</th>
        <th>ราคา/หน่วย</th>
        <th>รวม (บาท)</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td align="center">1</td>
        <td><?= htmlspecialchars($data['note'] ?: 'ค่ารักษาและบริการ') ?></td>
        <td align="center">1</td>
        <td align="right"><?= number_format($data['total_amount'], 2) ?></td>
        <td align="right"><?= number_format($data['total_amount'], 2) ?></td>
      </tr>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="4" class="total">รวมทั้งหมด</td>
        <td align="right"><b><?= number_format($data['total_amount'], 2) ?></b></td>
      </tr>
    </tfoot>
  </table>

  <div style="margin-top:20px;">
    <b>วิธีชำระเงิน:</b> <?= htmlspecialchars($data['payment_method']) ?><br>
    <b>สถานะ:</b> <?= htmlspecialchars($data['status']) ?>
  </div>

  <div class="qr">
    <p>สแกนเพื่อชำระเงินผ่าน PromptPay</p>
    <img src="<?= $qr_url ?>" width="150" alt="QR PromptPay"><br>
    <small>(PromptPay: <?= $promptpay_id ?>)</small>
  </div>

  <div class="print-btn">
    <button onclick="window.print()" style="padding:10px 20px;font-size:16px;">🖨️ พิมพ์ใบเสร็จ</button>
  </div>

  <div style="text-align:center;margin-top:20px;font-size:13px;color:#666;">
    ขอบคุณที่ใช้บริการคลินิกรักษาสัตว์ของเรา ❤️<br>
    © <?= date('Y') ?> ระบบบริหารคลินิกรักษาสัตว์ | DogClinic System
  </div>

</body>
</html>
