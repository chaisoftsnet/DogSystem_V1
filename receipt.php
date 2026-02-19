<?php
@session_start();
include 'dbconnect.php';

$invoice_id = intval($_GET['invoice_id']);

$inv = mysqli_fetch_assoc(mysqli_query($objCon,"
SELECT i.*, u.fullname, d.dog_name, c.clinic_name, c.address, c.phone
FROM invoices i
JOIN user u ON i.user_id=u.id
JOIN dogs d ON i.dog_id=d.dog_id
JOIN clinics c ON i.clinic_id=c.clinic_id
WHERE i.invoice_id=$invoice_id
LIMIT 1
"));

$items = mysqli_query($objCon,"
SELECT description, quantity, total_price
FROM invoice_items
WHERE invoice_id=$invoice_id
");
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>ใบเสร็จรับเงิน</title>
<style>
body{font-family:Tahoma;font-size:12px;}
.receipt{width:300px;margin:auto;}
hr{border:none;border-top:1px dashed #000;}
table{width:100%;}
td{text-align:left;}
td.r{text-align:right;}
@media print{
  button{display:none;}
}
</style>
</head>
<body>

<div class="receipt">
<h3 style="text-align:center"><?=$inv['clinic_name']?></h3>
<p style="text-align:center"><?=$inv['address']?><br>โทร <?=$inv['phone']?></p>
<hr>

เลขที่: INV-<?=$invoice_id?><br>
วันที่: <?=date('d/m/Y H:i',strtotime($inv['invoice_date']))?><br>
ลูกค้า: <?=$inv['fullname']?><br>
สุนัข: <?=$inv['dog_name']?><br>
วิธีชำระ: <?=$inv['payment_method']?>

<hr>
<table>
<?php while($i=mysqli_fetch_assoc($items)): ?>
<tr>
<td><?=$i['description']?> x<?=$i['quantity']?></td>
<td class="r"><?=number_format($i['total_price'],2)?></td>
</tr>
<?php endwhile; ?>
</table>
<hr>
<b>รวม <?=number_format($inv['total_amount'],2)?> บาท</b>

<hr>
<p style="text-align:center">ขอบคุณที่ใช้บริการ</p>
<button onclick="window.print()">🖨 พิมพ์</button>
</div>

</body>
</html>
