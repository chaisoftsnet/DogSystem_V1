<?php
require_once('dbconnect.php');
$action = $_GET['action'] ?? '';

if ($action == 'add') {
  $name = $_POST['supplier_name'];
  $phone = $_POST['phone'];
  $email = $_POST['email'];
  $line = $_POST['line_id'];
  $address = $_POST['address'];
  $sql = "INSERT INTO suppliers (supplier_name, phone, email, line_id, address)
          VALUES ('$name','$phone','$email','$line','$address')";
  echo mysqli_query($objCon, $sql) ? "เพิ่มข้อมูลสำเร็จ ✅" : "เกิดข้อผิดพลาด ❌";
}

if ($action == 'delete') {
  $id = $_POST['id'];
  mysqli_query($objCon, "DELETE FROM suppliers WHERE supplier_id='$id'");
  echo "ลบข้อมูลเรียบร้อย ✅";
}

if ($action == 'editform') {
  $id = $_GET['id'];
  $r = mysqli_fetch_assoc(mysqli_query($objCon, "SELECT * FROM suppliers WHERE supplier_id='$id'"));
?>
  <input type="hidden" name="supplier_id" value="<?=$r['supplier_id']?>">
  <div class="row g-3">
    <div class="col-md-6"><label>ชื่อผู้จำหน่าย</label><input name="supplier_name" value="<?=$r['supplier_name']?>" class="form-control"></div>
    <div class="col-md-6"><label>โทรศัพท์</label><input name="phone" value="<?=$r['phone']?>" class="form-control"></div>
    <div class="col-md-6"><label>อีเมล</label><input name="email" value="<?=$r['email']?>" class="form-control"></div>
    <div class="col-md-6"><label>LINE ID</label><input name="line_id" value="<?=$r['line_id']?>" class="form-control"></div>
    <div class="col-12"><label>ที่อยู่</label><textarea name="address" class="form-control"><?=$r['address']?></textarea></div>
  </div>
<?php
}

if ($action == 'update') {
  $id = $_POST['supplier_id'];
  $name = $_POST['supplier_name'];
  $phone = $_POST['phone'];
  $email = $_POST['email'];
  $line = $_POST['line_id'];
  $address = $_POST['address'];
  $sql = "UPDATE suppliers SET 
          supplier_name='$name', phone='$phone', email='$email', line_id='$line', address='$address'
          WHERE supplier_id='$id'";
  echo mysqli_query($objCon, $sql) ? "อัปเดตเรียบร้อย ✅" : "เกิดข้อผิดพลาด ❌";
}
?>
