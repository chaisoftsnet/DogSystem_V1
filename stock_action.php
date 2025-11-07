<?php
@session_start();
require_once('dbconnect.php');

$action = $_GET['action'] ?? ($_POST['action_type'] ?? '');
$id = intval($_POST['product_id'] ?? 0);
$qty = intval($_POST['quantity'] ?? 0);
$note = mysqli_real_escape_string($objCon, $_POST['note'] ?? '');
$user_id = $_SESSION['user_id'] ?? 1;
$clinic_id = $_SESSION['clinic_id'] ?? 1;

// 🟩 เพิ่มสินค้าใหม่
if ($action === 'add') {
    $name = mysqli_real_escape_string($objCon, $_POST['product_name']);
    $cat = mysqli_real_escape_string($objCon, $_POST['category']);
    $price = floatval($_POST['unit_price']);
    $reorder = intval($_POST['reorder_point']);

    $sql = "INSERT INTO products (product_name, category, unit_price, stock_qty, reorder_point, created_at)
            VALUES ('$name','$cat',$price,0,$reorder,NOW())";
    if (mysqli_query($objCon, $sql)) {
        echo "✅ เพิ่มสินค้าใหม่สำเร็จ";
    } else {
        echo "❌ เพิ่มสินค้าไม่สำเร็จ: " . mysqli_error($objCon);
    }
    exit;
}

// 🗑️ ลบสินค้า
if ($action === 'DELETE') {
    mysqli_query($objCon, "DELETE FROM products WHERE product_id=$id");
    echo "🗑️ ลบสินค้าสำเร็จ";
    exit;
}

// ✏️ ดึงข้อมูลสินค้า (สำหรับแก้ไข)
if ($action === 'FETCH') {
    $r = mysqli_fetch_assoc(mysqli_query($objCon, "SELECT * FROM products WHERE product_id=$id"));
    echo json_encode($r);
    exit;
}

// 💾 อัปเดตข้อมูลสินค้า
if ($action === 'UPDATE') {
    $name = mysqli_real_escape_string($objCon, $_POST['product_name']);
    $cat = mysqli_real_escape_string($objCon, $_POST['category']);
    $price = floatval($_POST['unit_price']);
    $reorder = intval($_POST['reorder_point']);

    $sql = "UPDATE products SET 
              product_name='$name',
              category='$cat',
              unit_price=$price,
              reorder_point=$reorder
            WHERE product_id=$id";

    if (mysqli_query($objCon, $sql)) {
        echo "✅ แก้ไขข้อมูลสินค้าสำเร็จ";
    } else {
        echo "❌ เกิดข้อผิดพลาด: " . mysqli_error($objCon);
    }
    exit;
}

// 📦 ตรวจสอบว่ามีสินค้านี้อยู่หรือไม่
$res = mysqli_query($objCon, "SELECT stock_qty FROM products WHERE product_id=$id");
if (!$res || mysqli_num_rows($res) == 0) {
    echo "❌ ไม่พบข้อมูลสินค้า (product_id: $id)";
    exit;
}
$r = mysqli_fetch_assoc($res);
$current = (int)$r['stock_qty'];

// 🔹 รับเข้า (IN)
if ($action === 'IN') {
    $new = $current + $qty;
    $type = 'IN';
    $msg = "✅ เพิ่มจำนวนสำเร็จ (+$qty)";
}

// 🔸 เบิกออก (OUT)
elseif ($action === 'OUT') {
    if ($qty > $current) {
        echo "⚠️ จำนวนที่เบิก ($qty) มากกว่าคงเหลือ ($current)";
        exit;
    }
    $new = $current - $qty;
    $type = 'OUT';
    $msg = "📉 เบิกสินค้าออกสำเร็จ (-$qty)";
}

// 🔹 อัปเดตสต็อก
mysqli_query($objCon, "UPDATE products SET stock_qty=$new WHERE product_id=$id");

// 🔸 บันทึกประวัติลงสมุดรายวัน (stock_transactions)
mysqli_query($objCon, "
    INSERT INTO stock_transactions (product_id, clinic_id, user_id, trans_type, quantity, reference_no, note, created_at)
    VALUES ($id, $clinic_id, $user_id, '$type', $qty, CONCAT('STOCK-',DATE_FORMAT(NOW(),'%Y%m%d%H%i%s')), '$note', NOW())
");

echo "💾 $msg";
?>
