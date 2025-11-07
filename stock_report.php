<?php
@session_start();
require_once('dbconnect.php');
if(!isset($_SESSION['user_id'])) {
  header("Location: index.php");
  exit();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>📊 รายงานสรุปคลังยาและวัคซีน</title>

<!-- Font / Bootstrap -->
<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<style>
body {
  font-family: 'Prompt', sans-serif;
  background: #0f2027;  /* fallback for old browsers */
  background: linear-gradient(150deg, #203a43, #2c5364);
  color: #fff;
  padding: 30px;
  transition: 0.3s;
}
.container-box {
  background: rgba(255, 255, 255, 0.08);
  padding: 25px;
  border-radius: 15px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.3);
}
h2 {
  color: #00e676;
  text-shadow: 0 0 8px rgba(0,230,118,0.3);
}
.table thead {
  background-color: rgba(255,255,255,0.15);
}
.table td, .table th {
  vertical-align: middle;
  color: #fff;
}
.status-ok { color: #00e676; font-weight: 600; }
.status-low { color: #ff5252; font-weight: 600; }

.print-btn {
  position: fixed;
  top: 20px;
  right: 20px;
  background: linear-gradient(45deg, #00e676, #00bfa5);
  border: none;
  color: #000;
  font-weight: bold;
  border-radius: 8px;
  padding: 10px 16px;
}
.print-btn:hover { opacity: 0.9; }

@media print {
  .print-btn, .filter-row { display: none; }
  body { background: #fff; color: #000; }
  .container-box { background: #fff; color: #000; box-shadow: none; }
  .table thead { background: #e0e0e0; color: #000; }
}
</style>
</head>

<body>
<button class="print-btn" onclick="window.print()">
  <i class="fa fa-print"></i> พิมพ์รายงาน
</button>

<div class="container container-box">
  <div class="text-center mb-4">
    <img src="images/clinic_logo.png" width="80" alt="Clinic Logo" class="mb-3">
    <h2>📋 รายงานสรุปคลังยาและวัคซีน</h2>
    <p>คลินิกรักษาสัตว์ PPC บ้านรามอินทรา</p>
    <hr style="border-top:1px solid #00e676;opacity:0.7;">
  </div>

  <!-- 🔹 ตัวกรอง -->
  <div class="row filter-row mb-4">
    <div class="col-md-4">
      <label>กรองตามหมวดหมู่:</label>
      <select id="filterCategory" class="form-select">
        <option value="">-- แสดงทั้งหมด --</option>
        <option value="ยา">ยา</option>
        <option value="วัคซีน">วัคซีน</option>
        <option value="บริการ">บริการ</option>
        <option value="อื่นๆ">อื่นๆ</option>
      </select>
    </div>
  </div>

  <table class="table table-bordered table-hover text-center align-middle" id="stockTable">
    <thead>
      <tr>
        <th>#</th>
        <th>ชื่อสินค้า</th>
        <th>หมวดหมู่</th>
        <th>ราคาขาย (บาท)</th>
        <th>คงเหลือ</th>
        <th>จุดสั่งซื้อซ้ำ</th>
        <th>สถานะ</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $sql = "SELECT * FROM products ORDER BY category, product_name ASC";
      $q = mysqli_query($objCon, $sql);
      $i = 1;
      while($r = mysqli_fetch_assoc($q)) {
        $low = ($r['stock_qty'] <= $r['reorder_point']);
        $status = $low ? "<span class='status-low'>⚠ เหลือน้อย</span>" : "<span class='status-ok'>✔ เพียงพอ</span>";
        echo "
          <tr data-cat='{$r['category']}'>
            <td>{$i}</td>
            <td>{$r['product_name']}</td>
            <td>{$r['category']}</td>
            <td>".number_format($r['unit_price'],2)."</td>
            <td>{$r['stock_qty']}</td>
            <td>{$r['reorder_point']}</td>
            <td>{$status}</td>
          </tr>
        ";
        $i++;
      }
      ?>
    </tbody>
  </table>

  <div class="text-end mt-4">
    <small>จัดทำโดย: ทีมพัฒนา PPC บ้านรามอินทรา | วันที่พิมพ์: <?=date('d/m/Y H:i')?> น.</small>
  </div>
</div>

<script>
document.getElementById('filterCategory').addEventListener('change', function(){
  const cat = this.value.toLowerCase();
  document.querySelectorAll('#stockTable tbody tr').forEach(tr=>{
    const c = tr.getAttribute('data-cat').toLowerCase();
    tr.style.display = (!cat || c === cat) ? '' : 'none';
  });
});
</script>
</body>
</html>
