<?php
@session_start();
require_once('dbconnect.php');

if (!isset($_SESSION['user_id'])) {
  header("Location: index.php");
  exit();
}

$year = $_GET['year'] ?? date('Y');

// 🔹 รวมยอดขายรายเดือน
$sql = "SELECT MONTH(invoice_date) AS m, SUM(total_amount) AS total
        FROM invoices
        WHERE YEAR(invoice_date) = '$year' AND status != 'ยกเลิก'
        GROUP BY MONTH(invoice_date)";
$q = mysqli_query($objCon, $sql);
$monthly = array_fill(1, 12, 0);
while ($r = mysqli_fetch_assoc($q)) {
  $monthly[intval($r['m'])] = floatval($r['total']);
}

// 🔹 รวมยอดทั้งหมด
$total_year = array_sum($monthly);
$invoice_count = mysqli_num_rows(mysqli_query($objCon, "SELECT * FROM invoices WHERE YEAR(invoice_date) = '$year'"));
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>📈 รายงานสรุปยอดใบแจ้งหนี้ <?= $year ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
<style>
body {
  font-family: 'Prompt', sans-serif;
  background-color: #f8f9fa;
  color: #212529;
  transition: background 0.3s, color 0.3s;
}
.dark-mode {
  background-color: #121212;
  color: #f1f1f1;
}
.card {
  border-radius: 16px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
.toggle-dark {
  cursor: pointer;
  font-size: 20px;
  float: right;
  color: #00bfa5;
}
</style>
</head>

<body>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>💰 รายงานสรุปรายได้คลินิก ปี <?= $year ?></h3>
    <i class="bi bi-moon toggle-dark" onclick="toggleDarkMode()"></i>
  </div>

  <div class="row g-4">
    <div class="col-md-4">
      <div class="card text-center p-4 bg-light">
        <h4>ยอดรวมทั้งปี</h4>
        <h2 class="text-success"><?= number_format($total_year, 2) ?> ฿</h2>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card text-center p-4 bg-light">
        <h4>จำนวนใบแจ้งหนี้</h4>
        <h2 class="text-info"><?= $invoice_count ?> ใบ</h2>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card text-center p-4 bg-light">
        <h4>เฉลี่ยต่อเดือน</h4>
        <h2 class="text-warning"><?= number_format($total_year / 12, 2) ?> ฿</h2>
      </div>
    </div>
  </div>

  <div class="card mt-4 p-4">
    <canvas id="chartMonthly"></canvas>
  </div>

  <div class="card mt-4 p-4">
    <h5>📋 รายการใบแจ้งหนี้ทั้งหมดในปี <?= $year ?></h5>
    <table class="table table-striped table-bordered mt-3">
      <thead class="table-dark">
        <tr>
          <th>#</th>
          <th>วันที่</th>
          <th>ลูกค้า</th>
          <th>สุนัข</th>
          <th>ยอดรวม (บาท)</th>
          <th>สถานะ</th>
          <th>วิธีชำระเงิน</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $i = 1;
        $q2 = mysqli_query($objCon, 
          "SELECT i.*, u.fullname AS owner, d.dog_name
           FROM invoices i
           LEFT JOIN user u ON i.user_id = u.id
           LEFT JOIN dogs d ON i.dog_id = d.dog_id
           WHERE YEAR(invoice_date) = '$year'
           ORDER BY invoice_date DESC"
        );
        while($r = mysqli_fetch_assoc($q2)){
          echo "<tr>
            <td>{$i}</td>
            <td>".date('d/m/Y', strtotime($r['invoice_date']))."</td>
            <td>{$r['owner']}</td>
            <td>{$r['dog_name']}</td>
            <td align='right'>".number_format($r['total_amount'],2)."</td>
            <td>{$r['status']}</td>
            <td>{$r['payment_method']}</td>
          </tr>";
          $i++;
        }
        ?>
      </tbody>
    </table>
  </div>

  <div class="text-center mt-4">
    <a href="dashboard.php" class="btn btn-secondary btn-lg"><i class="bi bi-house"></i> กลับหน้าหลัก</a>
  </div>
</div>

<script>
// ✅ สร้างกราฟรายเดือน
const ctx = document.getElementById('chartMonthly');
const chart = new Chart(ctx, {
  type: 'bar',
  data: {
    labels: ['ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'],
    datasets: [{
      label: 'ยอดขายรายเดือน (บาท)',
      data: <?= json_encode(array_values($monthly)) ?>,
      borderWidth: 1,
      backgroundColor: '#00c853'
    }]
  },
  options: {
    responsive: true,
    scales: {
      y: { beginAtZero: true }
    }
  }
});

function toggleDarkMode(){
  document.body.classList.toggle('dark-mode');
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</body>
</html>
