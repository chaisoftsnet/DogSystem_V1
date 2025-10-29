<?php
@session_start();
require_once('dbconnect.php');

// ตรวจสอบการเรียกใช้งาน
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch($action) {

  // ==========================================
  // 🔹 เพิ่มใบแจ้งหนี้ใหม่
  // ==========================================
  case 'add':
    $clinic_id = $_SESSION['clinic_id'];
    $user_id   = $_POST['user_id'];
    $dog_id    = $_POST['dog_id'];
    $payment   = $_POST['payment_method'];
    $note      = $_POST['note'] ?? '';

    // คำนวณยอดรวม
    $total = 0;
    foreach($_POST['qty'] as $i => $q) {
      $total += floatval($q) * floatval($_POST['price'][$i]);
    }

    // 1️⃣ เพิ่มลงตาราง invoices
    $sql = "INSERT INTO invoices (clinic_id, user_id, dog_id, total_amount, payment_method, note)
            VALUES ('$clinic_id','$user_id','$dog_id','$total','$payment','$note')";
    mysqli_query($objCon, $sql);
    $invoice_id = mysqli_insert_id($objCon);

    // 2️⃣ เพิ่มรายละเอียดสินค้า/บริการ
    foreach($_POST['qty'] as $i => $q) {
      $pid = $_POST['product_id'][$i];
      $desc = '';
      $unit_price = floatval($_POST['price'][$i]);
      $qty = floatval($_POST['qty'][$i]);

      // ดึงชื่อสินค้า
      $pq = mysqli_query($objCon, "SELECT product_name FROM products WHERE product_id='$pid'");
      if($row = mysqli_fetch_assoc($pq)) $desc = $row['product_name'];

      $sql_item = "INSERT INTO invoice_items (invoice_id, description, quantity, unit_price)
                   VALUES ('$invoice_id', '$desc', '$qty', '$unit_price')";
      mysqli_query($objCon, $sql_item);
    }

    echo "✅ บันทึกใบแจ้งหนี้เรียบร้อยแล้ว (Invoice ID: $invoice_id)";
    break;

  // ==========================================
  // 🔹 ลบใบแจ้งหนี้
  // ==========================================
  case 'delete':
    $id = $_POST['id'];
    mysqli_query($objCon, "DELETE FROM invoice_items WHERE invoice_id='$id'");
    mysqli_query($objCon, "DELETE FROM invoices WHERE invoice_id='$id'");
    echo "🗑️ ลบใบแจ้งหนี้เรียบร้อยแล้ว";
    break;

  // ==========================================
  // 🔹 ดึงฟอร์มแก้ไข (AJAX)
  // ==========================================
  case 'editform':
    $id = $_GET['id'];
    $q = mysqli_query($objCon, "SELECT * FROM invoices WHERE invoice_id='$id'");
    $r = mysqli_fetch_assoc($q);
?>
    <input type="hidden" name="invoice_id" value="<?=$r['invoice_id']?>">

    <div class="row g-3">
      <div class="col-md-4">
        <label>สถานะ</label>
        <select name="status" class="form-select">
          <option value="รอชำระ" <?=$r['status']=='รอชำระ'?'selected':''?>>รอชำระ</option>
          <option value="ชำระแล้ว" <?=$r['status']=='ชำระแล้ว'?'selected':''?>>ชำระแล้ว</option>
          <option value="ยกเลิก" <?=$r['status']=='ยกเลิก'?'selected':''?>>ยกเลิก</option>
        </select>
      </div>
      <div class="col-md-4">
        <label>วิธีชำระเงิน</label>
        <select name="payment_method" class="form-select">
          <option value="เงินสด" <?=$r['payment_method']=='เงินสด'?'selected':''?>>เงินสด</option>
          <option value="โอน" <?=$r['payment_method']=='โอน'?'selected':''?>>โอน</option>
          <option value="บัตรเครดิต" <?=$r['payment_method']=='บัตรเครดิต'?'selected':''?>>บัตรเครดิต</option>
          <option value="PromptPay" <?=$r['payment_method']=='PromptPay'?'selected':''?>>PromptPay</option>
        </select>
      </div>
      <div class="col-md-12">
        <label>หมายเหตุ</label>
        <textarea name="note" class="form-control"><?=$r['note']?></textarea>
      </div>
    </div>
<?php
    break;

  // ==========================================
  // 🔹 อัปเดตสถานะ/ข้อมูลบิล
  // ==========================================
  case 'update':
    $id = $_POST['invoice_id'];
    $status = $_POST['status'];
    $payment = $_POST['payment_method'];
    $note = $_POST['note'];

    $sql = "UPDATE invoices 
            SET status='$status', payment_method='$payment', note='$note'
            WHERE invoice_id='$id'";
    mysqli_query($objCon, $sql);
    echo "✅ อัปเดตข้อมูลเรียบร้อยแล้ว";
    break;

  // ==========================================
  // 🔹 ดึงข้อมูลทั้งหมด (ใช้ใน invoice_manage.php)
  // ==========================================
  case 'list':
    $q = mysqli_query($objCon, "
      SELECT i.*, u.fullname AS owner, d.dog_name
      FROM invoices i
      LEFT JOIN user u ON i.user_id = u.id
      LEFT JOIN dogs d ON i.dog_id = d.dog_id
      ORDER BY i.invoice_date DESC
    ");
    $data = [];
    while($r = mysqli_fetch_assoc($q)) $data[] = $r;
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    break;

  default:
    echo "❌ ไม่พบคำสั่งที่ร้องขอ";
}
?>
