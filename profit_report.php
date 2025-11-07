<?php
@session_start();
require_once('dbConnect.php');
if (!isset($_SESSION['username'])) { header("Location: index.php"); exit(); }

$year = $_GET['year'] ?? date('Y');
$sql = "
SELECT 
  MONTH(i.invoice_date) AS month,
  SUM(ii.quantity * ii.unit_price) AS total_sales,
  SUM(ii.quantity * p.cost_price) AS total_cost,
  SUM(ii.quantity * (ii.unit_price - p.cost_price)) AS profit
FROM invoice_items ii
JOIN invoices i ON ii.invoice_id = i.invoice_id
JOIN products p ON ii.description = p.product_name
WHERE i.status='ชำระแล้ว' AND YEAR(i.invoice_date) = '$year'
GROUP BY MONTH(i.invoice_date)
ORDER BY month ASC
";
$res = mysqli_query($objCon, $sql);
$data = [];
while($r = mysqli_fetch_assoc($res)) { $data[] = $r; }
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>📊 รายงานกำไร/ขาดทุน</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light p-4">

<div class="container">
  <h3 class="text-center mb-4">💹 รายงานสรุปกำไร–ขาดทุน ประจำปี <?= $year ?></h3>

  <form method="get" class="text-center mb-4">
    <label>เลือกปี:</label>
    <select name="year" onchange="this.form.submit()" class="form-select d-inline-block" style="width:150px;">
      <?php for($y=date('Y')-3; $y<=date('Y'); $y++): ?>
        <option value="<?= $y ?>" <?= ($y==$year?'selected':'') ?>><?= $y+543 ?></option>
      <?php endfor; ?>
    </select>
  </form>

  <canvas id="profitChart" height="120"></canvas>
  <?php
    $months = ['ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
    $labels = []; $sales=[]; $cost=[]; $profit=[];
    foreach($data as $d){
      $labels[] = $months[$d['month']-1];
      $sales[] = $d['total_sales'];
      $cost[] = $d['total_cost'];
      $profit[] = $d['profit'];
    }
  ?>
  <script>
  const ctx = document.getElementById('profitChart');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: <?= json_encode($labels) ?>,
      datasets: [
        { label:'รายได้', data: <?= json_encode($sales) ?>, backgroundColor:'#4caf50' },
        { label:'ต้นทุน', data: <?= json_encode($cost) ?>, backgroundColor:'#81c784' },
        { label:'กำไร', data: <?= json_encode($profit) ?>, backgroundColor:'#ffb300' }
      ]
    },
    options: {
      responsive:true,
      scales: { y: { beginAtZero:true } },
      plugins: { legend:{ position:'bottom' } }
    }
  });
  </script>

  <table class="table table-bordered table-striped mt-4 text-center align-middle">
    <thead class="table-success">
      <tr>
        <th>เดือน</th>
        <th>รายได้ (บาท)</th>
        <th>ต้นทุน (บาท)</th>
        <th>กำไร (บาท)</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($data as $d): ?>
        <tr>
          <td><?= $months[$d['month']-1] ?></td>
          <td><?= number_format($d['total_sales'],2) ?></td>
          <td><?= number_format($d['total_cost'],2) ?></td>
          <td><?= number_format($d['profit'],2) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <div class="text-center mt-3">
    <a href="dashboard.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> กลับเมนูหลัก</a>
  </div>
</div>
</body>
</html>
