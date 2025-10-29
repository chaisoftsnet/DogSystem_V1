<?php
@session_start();
require_once('dbconnect.php');

if (!isset($_SESSION['user_id'])) {
  header("Location: index.php");
  exit();
}

// รับค่าปี / เดือน
$year = $_GET['year'] ?? date('Y');
$month = $_GET['month'] ?? date('m');

// สร้างช่วงวันที่
$start_date = "$year-$month-01";
$end_date = date("Y-m-t", strtotime($start_date));

// ดึงข้อมูลรายงาน
$sql = "
  SELECT i.*, u.fullname, d.dog_name, c.clinic_name
  FROM invoices i
  LEFT JOIN user u ON i.user_id = u.id
  LEFT JOIN dogs d ON i.dog_id = d.dog_id
  LEFT JOIN clinics c ON i.clinic_id = c.clinic_id
  WHERE DATE(i.invoice_date) BETWEEN '$start_date' AND '$end_date'
  ORDER BY i.invoice_date DESC";
$q = mysqli_query($objCon, $sql);

// รวมยอดตามวัน
$chartData = [];
$chartSQL = "
  SELECT DATE(invoice_date) AS d, SUM(total_amount) AS total
  FROM invoices
  WHERE DATE(invoice_date) BETWEEN '$start_date' AND '$end_date'
  GROUP BY DATE(invoice_date)
  ORDER BY d ASC";
$cq = mysqli_query($objCon, $chartSQL);
while($r=mysqli_fetch_assoc($cq)){
  $chartData[] = ["date"=>$r['d'], "total"=>$r['total']];
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>📊 รายงานรายได้ | ระบบคลินิกรักษาสัตว์</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
body {
  background-color: #f8f9fa;
  transition: background 0.3s, color 0.3s;
  font-family: 'Prompt', sans-serif;
}
.dark-mode {
  background-color: #121212;
  color: #f1f1f1;
}
.card {
  border-radius: 15px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}
.table td, .table th { vertical-align: middle; }
.btn-add {
  background: linear-gradient(45deg, #007bff, #00b4d8);
  color: #fff;
  border: none;
}
.btn-add:hover { opacity: 0.9; }
.toggle-dark {
  cursor: pointer;
  float: right;
  color: #007bff;
  font-size: 1.2rem;
}
</style>
</head>
<link rel="stylesheet" href="css/theme.css">
<script src="js/theme.js"></script>
<body>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>📊 รายงานรายได้ประจำเดือน</h3>
    <div>
      <i class="fa fa-moon toggle-dark me-3" onclick="toggleDarkMode()"></i>
      <a href="invoice_dashboard.php" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> กลับหน้าหลัก</a>
    </div>
  </div>

  <!-- ฟอร์มเลือกเดือน/ปี -->
  <form method="get" class="row g-3 mb-4">
    <div class="col-md-3">
      <label>เดือน</label>
      <select name="month" class="form-select">
        <?php
          for($m=1;$m<=12;$m++){
            $val = sprintf("%02d",$m);
            $sel = ($val==$month)?'selected':'';
            echo "<option value='$val' $sel>".date('F', mktime(0,0,0,$m,1))."</option>";
          }
        ?>
      </select>
    </div>
    <div class="col-md-3">
      <label>ปี</label>
      <select name="year" class="form-select">
        <?php
          for($y=date('Y')-3;$y<=date('Y')+1;$y++){
            $sel = ($y==$year)?'selected':'';
            echo "<option value='$y' $sel>$y</option>";
          }
        ?>
      </select>
    </div>
    <div class="col-md-3 align-self-end">
      <button class="btn btn-primary"><i class="fa fa-search"></i> แสดงรายงาน</button>
    </div>
  </form>

  <!-- กราฟรายได้ -->
  <div class="card p-3 mb-4">
    <h5>💰 กราฟรายได้ประจำวันที่</h5>
    <canvas id="revenueChart" height="120"></canvas>
  </div>

  <!-- ตารางสรุปใบแจ้งหนี้ -->
  <div class="card p-3">
    <h5>📄 รายการใบแจ้งหนี้ทั้งหมด</h5>
    <table class="table table-bordered text-center align-middle mt-3">
      <thead class="table-dark">
        <tr>
          <th>#</th>
          <th>วันที่</th>
          <th>ลูกค้า</th>
          <th>สุนัข</th>
          <th>ยอดรวม</th>
          <th>สถานะ</th>
          <th>ช่องทางชำระ</th>
          <th>จัดการ</th>
        </tr>
      </thead>
      <tbody>
        <?php 
        $i=1; $sumTotal=0;
        while($r=mysqli_fetch_assoc($q)){
          $sumTotal += $r['total_amount'];
          echo "
          <tr>
            <td>$i</td>
            <td>".date('d/m/Y H:i', strtotime($r['invoice_date']))."</td>
            <td>{$r['fullname']}</td>
            <td>{$r['dog_name']}</td>
            <td class='text-end'>".number_format($r['total_amount'],2)."</td>
            <td>{$r['status']}</td>
            <td>{$r['payment_method']}</td>
            <td>
              <a href='invoice_print.php?invoice_id={$r['invoice_id']}' target='_blank' class='btn btn-sm btn-outline-success'><i class='fa fa-print'></i> พิมพ์</a>
            </td>
          </tr>";
          $i++;
        }
        ?>
      </tbody>
      <tfoot>
        <tr class="table-secondary fw-bold">
          <td colspan="4" class="text-end">รวมทั้งหมด</td>
          <td class="text-end"><?=number_format($sumTotal,2)?></td>
          <td colspan="3"></td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

<script>
function toggleDarkMode(){ document.body.classList.toggle('dark-mode'); }

// สร้างกราฟ Chart.js
const ctx = document.getElementById('revenueChart');
const chartData = <?=json_encode($chartData)?>;

new Chart(ctx, {
  type: 'bar',
  data: {
    labels: chartData.map(e => e.date),
    datasets: [{
      label: 'รายได้ (บาท)',
      data: chartData.map(e => e.total),
      borderWidth: 1,
      backgroundColor: 'rgba(40, 167, 69, 0.6)',
      borderColor: '#28a745'
    }]
  },
  options: {
    scales: {
      y: { beginAtZero: true, ticks: { stepSize: 100 } }
    },
    plugins: { legend: { display: false } }
  }
});
</script>
</body>
</html>
