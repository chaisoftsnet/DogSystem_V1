<?php
@session_start();
require_once('dbconnect.php');

$action = $_GET['action'] ?? '';

switch ($action) {

  // ✅ เพิ่มสินค้า / บริการใหม่
  case 'add':
    $name = mysqli_real_escape_string($objCon, $_POST['product_name']);
    $category = mysqli_real_escape_string($objCon, $_POST['category']);
    $price = floatval($_POST['unit_price']);

    $sql = "INSERT INTO products (product_name, category, unit_price) 
            VALUES ('$name', '$category', '$price')";
    if (mysqli_query($objCon, $sql)) {
      echo "✅ เพิ่มข้อมูลสินค้าเรียบร้อยแล้ว";
    } else {
      echo "❌ เกิดข้อผิดพลาด: " . mysqli_error($objCon);
    }
    break;

  // ✏️ โหลดฟอร์มแก้ไข
  case 'editform':
    $id = intval($_GET['id']);
    $sql = "SELECT * FROM products WHERE product_id=$id";
    $result = mysqli_query($objCon, $sql);
    $r = mysqli_fetch_assoc($result);
    ?>

    <div class="mb-3">
      <label>ชื่อสินค้า / บริการ</label>
      <input type="hidden" name="product_id" value="<?=$r['product_id']?>">
      <input type="text" name="product_name" class="form-control" value="<?=htmlspecialchars($r['product_name'])?>" required>
    </div>

    <div class="mb-3">
      <label>หมวดหมู่</label>
      <select name="category" class="form-select">
        <option value="ยา" <?=$r['category']=='ยา'?'selected':''?>>ยา</option>
        <option value="วัคซีน" <?=$r['category']=='วัคซีน'?'selected':''?>>วัคซีน</option>
        <option value="บริการ" <?=$r['category']=='บริการ'?'selected':''?>>บริการ</option>
        <option value="อื่นๆ" <?=$r['category']=='อื่นๆ'?'selected':''?>>อื่นๆ</option>
      </select>
    </div>

    <div class="mb-3">
      <label>ราคาต่อหน่วย (บาท)</label>
      <input type="number" step="0.01" name="unit_price" class="form-control" value="<?=$r['unit_price']?>" required>
    </div>
    <?php
    break;

  // ✅ อัปเดตข้อมูล
  case 'update':
    $id = intval($_POST['product_id']);
    $name = mysqli_real_escape_string($objCon, $_POST['product_name']);
    $category = mysqli_real_escape_string($objCon, $_POST['category']);
    $price = floatval($_POST['unit_price']);

    $sql = "UPDATE products 
            SET product_name='$name', category='$category', unit_price='$price'
            WHERE product_id=$id";

    if (mysqli_query($objCon, $sql)) {
      echo "✅ อัปเดตข้อมูลเรียบร้อยแล้ว";
    } else {
      echo "❌ ไม่สามารถอัปเดตข้อมูลได้: " . mysqli_error($objCon);
    }
    break;

  // ❌ ลบข้อมูล
  case 'delete':
    $id = intval($_POST['id']);
    $sql = "DELETE FROM products WHERE product_id=$id";
    if (mysqli_query($objCon, $sql)) {
      echo "✅ ลบข้อมูลเรียบร้อยแล้ว";
    } else {
      echo "❌ เกิดข้อผิดพลาด: " . mysqli_error($objCon);
    }
    break;

  default:
    echo "⚠️ ไม่พบคำสั่งที่ถูกต้อง";
}
?>
