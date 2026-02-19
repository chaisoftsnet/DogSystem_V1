<?php
@session_start();
include 'dbconnect.php';
include 'function.php';

/* ===============================
   SECURITY
================================ */
if (!isset($_SESSION['clinic_id'])) {
    exit('no session');
}

$clinic_id = $_SESSION['clinic_id'];
$user_id   = $_SESSION['id'];

$invoice_id = intval($_GET['invoice_id']);

/* ===============================
   LOAD INVOICE
================================ */
$inv = mysqli_query($objCon,"
  SELECT *
  FROM invoices
  WHERE invoice_id=$invoice_id
    AND clinic_id=$clinic_id
    AND status='รอชำระ'
  LIMIT 1
");
$invoice = mysqli_fetch_assoc($inv);
if(!$invoice){
    exit('ไม่พบใบแจ้งหนี้');
}

/* ===============================
   LOAD ITEMS
================================ */
$items_rs = mysqli_query($objCon,"
  SELECT *
  FROM invoice_items
  WHERE invoice_id=$invoice_id
");

$total = 0;
$items = [];
while($row = mysqli_fetch_assoc($items_rs)){
    $items[] = $row;
    $total  += $row['total_price'];
}

/* ===============================
   CONFIRM PAYMENT
================================ */
if(isset($_POST['confirm_payment'])){

    $method = $_POST['payment_method'];

    /* ===============================
       AUTO CUT STOCK
    ================================ */
    foreach($items as $row){

        // หา product จากชื่อรายการ
        $p = mysqli_fetch_assoc(mysqli_query($objCon,"
          SELECT product_id, stock_qty
          FROM products
          WHERE product_name='".mysqli_real_escape_string($objCon,$row['description'])."'
          LIMIT 1
        "));

        if(!$p) continue; // ไม่ใช่สินค้า (เช่น ค่าบริการ)

        // ตัด stock
        mysqli_query($objCon,"
          UPDATE products
          SET stock_qty = stock_qty - {$row['quantity']}
          WHERE product_id = {$p['product_id']}
        ");

        // บันทึก Stock Ledger
        mysqli_query($objCon,"
          INSERT INTO stock_transactions
          (product_id, clinic_id, user_id,
           trans_type, quantity, reference_no, note)
          VALUES
          ({$p['product_id']},
           $clinic_id,
           $user_id,
           'OUT',
           {$row['quantity']},
           'INV-$invoice_id',
           'ขายยา/เวชภัณฑ์')
        ");
    }

    /* ===============================
       UPDATE INVOICE
    ================================ */
    mysqli_query($objCon,"
      UPDATE invoices
      SET status='ชำระแล้ว',
          payment_method='$method',
          total_amount=$total
      WHERE invoice_id=$invoice_id
    ");

    /* ===============================
       CLOSE VISIT
    ================================ */
    mysqli_query($objCon,"
      UPDATE visits
      SET status='เสร็จสิ้น'
      WHERE clinic_id=$clinic_id
        AND dog_id={$invoice['dog_id']}
        AND status='รอชำระเงิน'
    ");

    /* ===============================
       CLOSE POPUP
    ================================ */
echo "<script>
  if(window.parent){
    // เปิดใบเสร็จ (แท็บใหม่ / thermal / pdf)
    window.open(
      'receipt.php?invoice_id=$invoice_id',
      '_blank'
    );

    // ปิด popup cashier
    window.parent.closeWalkin();
    window.parent.loadQueue && window.parent.loadQueue();
  }
</script>";
exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>Cashier</title>

<link rel="stylesheet" href="assets/css/chsn_theme.css">
<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">

<script>
function applyTheme(){
  const theme = localStorage.getItem('theme') || 'dark';
  document.body.classList.toggle('light', theme==='light');
}
window.onload = applyTheme;
</script>

<style>
body{font-family:'Prompt',sans-serif;padding:20px;}
.card{
  background:var(--card);
  border-radius:18px;
  padding:20px;
  color:var(--text);
}
table{
  width:100%;
  border-collapse:collapse;
  margin-top:10px;
}
th,td{
  padding:10px;
  border-bottom:1px solid var(--border);
}
th{text-align:left;}
td.num{text-align:right;}
.total{
  font-size:18px;
  font-weight:600;
}
.pay-method label{
  display:block;
  margin-bottom:8px;
  cursor:pointer;
}
.btn-confirm{
  margin-top:15px;
  background:#22c55e;
  color:#fff;
  border:none;
  padding:12px 22px;
  font-size:16px;
  border-radius:12px;
  cursor:pointer;
}
</style>
</head>

<body>

<div class="card">

<h3>💳 ชำระเงิน</h3>

<table>
<thead>
<tr>
  <th>รายการ</th>
  <th class="num">จำนวน</th>
  <th class="num">ราคา</th>
</tr>
</thead>
<tbody>
<?php foreach($items as $i): ?>
<tr>
  <td><?=$i['description']?></td>
  <td class="num"><?=$i['quantity']?></td>
  <td class="num"><?=number_format($i['total_price'],2)?></td>
</tr>
<?php endforeach; ?>
</tbody>
<tfoot>
<tr>
  <td colspan="2" class="total">รวม</td>
  <td class="num total"><?=number_format($total,2)?></td>
</tr>
</tfoot>
</table>

<form method="post" class="pay-method">
<h4>วิธีชำระเงิน</h4>

<label>
  <input type="radio" name="payment_method" value="เงินสด" checked>
  เงินสด
</label>
<label>
  <input type="radio" name="payment_method" value="โอน">
  โอน
</label>
<label>
  <input type="radio" name="payment_method" value="PromptPay">
  PromptPay
</label>
<label>
  <input type="radio" name="payment_method" value="บัตรเครดิต">
  บัตรเครดิต
</label>

<button class="btn-confirm" name="confirm_payment">
  ✅ ยืนยันชำระเงิน
</button>
</form>

</div>

</body>
</html>
