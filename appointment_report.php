<?php
@session_start();
require_once('dbconnect.php');

if (!isset($_SESSION['user_id'])) {
  header("Location: index.php");
  exit();
}

// รับค่าปีและเดือนจากฟอร์ม
$year  = $_GET['year'] ?? date('Y');
$month = $_GET['month'] ?? '';

$condition = "YEAR(appointment_date)='$year'";
if ($month != '') $condition .= " AND MONTH(appointment_date)='$month'";

// ดึงข้อมูลสรุปยอด
$sqlSummary = "
  SELECT 
    COUNT(*) AS total,
    SUM(CASE WHEN status='รอพบแพทย์' THEN 1 ELSE 0 END) AS pending,
    SUM(CASE WHEN status='เสร็จสิ้น' THEN 1 ELSE 0 END) AS done,
    SUM(CASE WHEN status='ยกเลิก' THEN 1 ELSE 0 END) AS cancelled
  FROM appointments
  WHERE $condition
";
$summary = mysqli_fetch_assoc(mysqli_query($objCon, $sqlSummary));

// ดึงข้อมูลกราฟรายเดือน
$sqlChart = "
  SELECT MONTH(appointment_date) AS m, COUNT(*) AS total
  FROM appointments
  WHERE YEAR(appointment_date)='$year'
  GROUP BY MONTH(appointment_date)
";
$resultChart = mysqli_query($objCon, $sqlChart);
$chartData = array_fill(1, 12, 0);
while($r = mysqli_fetch_assoc($resultChart)) $chartData[$r['m']] = $r['total'];

// ดึงข้อมูลตาราง
$sql = "
  SELECT a.*, d.dog_name, c.clinic_name
  FROM appointments a
  LEFT JOIN dogs d ON a.dog_id = d.dog_id
  LEFT JOIN clinics c ON a.clinic_id = c.clinic_id
  WHERE $condition
  ORDER BY a.appointment_date DESC
";
$result = mysqli_query($objCon, $sql);
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>📅 รายงานการนัดหมายสัตว์ | Appointment Report</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
body {
  background-color: #f8f9fa;
  transition: background 0.3s, color 0.3s;
  font-family: "Prompt", sans-serif;
}
.dark-mode {
  background-color: #121212;
  color: #f1f1f1;
}
.card {
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.05);
}
.btn-mode {
  float: right;
  background: none;
  border: none;
  font-size: 20px;
  cursor: pointer;
  color: #198754;
}
.card h4 { font-weight: bold; font-size: 28px; }
.card p { margin-bottom: 0; }
</style>
</head>

<body>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>📅 รายงานการนัดหมายสัตว์</h3>
    <button class="btn-mode" onclick="toggleDarkMode()"><i class="fa fa-moon"></i></button>
  </div>

  <!-- ฟอร์มเลือกปีและเดือน -->
  <form method="get" class="row g-3 mb-4 align-items-end">
    <div class="col-md-2">
      <label class="form-label">ปี</label>
      <select name="year" class="form-select">
        <?php 
          $currentYear = date('Y');
          for($y = $currentYear; $y >= $currentYear - 5; $y--) {
            $sel = ($y == $year) ? 'selected' : '';
            echo "<option value='$y' $sel>".($y + 543)."</option>";
          }
        ?>
      </select>
    </div>

    <div class="col-md-2">
      <label class="form-label">เดือน</label>
      <select name="month" class="form-select">
        <option value="">-- ทั้งหมด --</option>
        <?php 
          $months = ["","มกราคม","กุมภาพันธ์","มีนาคม","เมษายน","พฤษภาคม","มิถุนายน",
                     "กรกฎาคม","สิงหาคม","กันยายน","ตุลาคม","พฤศจิกายน","ธันวาคม"];
          for($m=1;$m<=12;$m++){
            $sel = ($m == $month) ? 'selected' : '';
            echo "<option value='$m' $sel>$months[$m]</option>";
          }
        ?>
      </select>
    </div>

    <div class="col-md-4">
      <button type="submit" class="btn btn-success">
        <i class="fa fa-search"></i> ค้นหา
      </button>
      <a href="appointment_report_print.php?year=<?= $year ?>&month=<?= $month ?>" target="_blank" class="btn btn-outline-primary ms-2">
        <i class="fa fa-print"></i> พิมพ์รายงาน
      </a>
      <a href="appointment_report_excel.php?year=<?= $year ?>&month=<?= $month ?>" class="btn btn-outline-success ms-2">
        <i class="fa fa-file-excel"></i> Export Excel
      </a>
    </div>
  </form>

  <!-- แสดงสรุปยอด -->
  <div class="row text-center mb-4">
    <div class="col-md-3 col-sm-6 mb-2">
      <div class="card bg-primary text-white p-3 shadow-sm">
        <h4><?= $summary['total'] ?? 0 ?></h4>
        <p>รวมทั้งหมด</p>
      </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-2">
      <div class="card bg-warning text-dark p-3 shadow-sm">
        <h4><?= $summary['pending'] ?? 0 ?></h4>
        <p>รอพบแพทย์</p>
      </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-2">
      <div class="card bg-success text-white p-3 shadow-sm">
        <h4><?= $summary['done'] ?? 0 ?></h4>
        <p>เสร็จสิ้น</p>
      </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-2">
      <div class="card bg-danger text-white p-3 shadow-sm">
        <h4><?= $summary['cancelled'] ?? 0 ?></h4>
        <p>ยกเลิก</p>
      </div>
    </div>
  </div>

  <!-- กราฟสรุปรายเดือน -->
  <div class="card p-4 mb-4">
    <h5 class="mb-3"><i class="fa fa-chart-column"></i> กราฟสรุปรายเดือน ปี <?= $year+543 ?></h5>
    <canvas id="chartMonth" height="100"></canvas>
  </div>

  <!-- ตารางข้อมูล -->
  <div class="card p-4">
    <h5 class="mb-3"><i class="fa fa-list"></i> รายการนัดหมาย</h5>
    <table class="table table-striped table-bordered text-center">
      <thead class="table-dark">
        <tr>
          <th>#</th>
          <th>ชื่อสุนัข</th>
          <th>คลินิก</th>
          <th>วันนัดหมาย</th>
          <th>รายละเอียด</th>
          <th>สถานะ</th>
        </tr>
      </thead>
      <tbody>
      <?php
        $i = 1;
        while($row = mysqli_fetch_assoc($result)) {
          echo "
          <tr>
            <td>{$i}</td>
            <td>{$row['dog_name']}</td>
            <td>{$row['clinic_name']}</td>
            <td>".date('d/m/Y H:i', strtotime($row['appointment_date']))."</td>
            <td>{$row['description']}</td>
            <td>
              <span class='badge bg-".
              ($row['status']=='รอพบแพทย์'?'warning':($row['status']=='เสร็จสิ้น'?'success':'danger')).
              "'>{$row['status']}</span>
            </td>
          </tr>";
          $i++;
        }
      ?>
      </tbody>
    </table>
  </div>
</div>

<script>
const ctx = document.getElementById('chartMonth');
new Chart(ctx, {
  type: 'bar',
  data: {
    labels: ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'],
    datasets: [{
      label: 'จำนวนการนัดหมาย',
      data: <?= json_encode(array_values($chartData)) ?>,
      borderWidth: 1
    }]
  },
  options: { scales: { y: { beginAtZero: true } } }
});

function toggleDarkMode(){
  document.body.classList.toggle('dark-mode');
}
</script>
</body>
</html>
