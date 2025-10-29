<?php
@session_start();
require_once('dbconnect.php');

$year = $_GET['year'] ?? date('Y');
$month = $_GET['month'] ?? '';
$condition = "YEAR(appointment_date)='$year'";
if($month != '') $condition .= " AND MONTH(appointment_date)='$month'";

$sql = "SELECT a.*, d.dog_name, c.clinic_name
        FROM appointments a
        LEFT JOIN dogs d ON a.dog_id = d.dog_id
        LEFT JOIN clinics c ON a.clinic_id = c.clinic_id
        WHERE $condition
        ORDER BY appointment_date DESC";
$result = mysqli_query($objCon, $sql);

// แปลงชื่อเดือน
$months = ["","มกราคม","กุมภาพันธ์","มีนาคม","เมษายน","พฤษภาคม","มิถุนายน",
           "กรกฎาคม","สิงหาคม","กันยายน","ตุลาคม","พฤศจิกายน","ธันวาคม"];
$monthName = $month ? $months[intval($month)] : "ทั้งหมด";
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>รายงานการนัดหมาย</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
@media print {
  @page { size: A4; margin: 15mm; }
}
body {
  font-family: "TH SarabunPSK", sans-serif;
  background: #fff;
  color: #000;
  padding: 20px;
}
h2 {
  text-align: center;
  font-weight: bold;
  margin-bottom: 10px;
}
h5 {
  text-align: center;
  color: #666;
  margin-bottom: 30px;
}
table {
  width: 100%;
  border-collapse: collapse;
  font-size: 16px;
}
th, td {
  border: 1px solid #ccc;
  padding: 8px;
  text-align: center;
}
th {
  background: #f2f2f2;
}
.footer {
  text-align: right;
  margin-top: 30px;
  font-size: 14px;
  color: #666;
}
.logo {
  width: 80px;
  position: absolute;
  top: 20px;
  left: 20px;
}
</style>
</head>

<body>
<img src="images/logo_clinic.png" class="logo" alt="Clinic Logo">
<h2>รายงานการนัดหมายประจำ <?= $monthName ?> <?= $year+543 ?></h2>
<h5>ระบบบริหารจัดการคลินิกรักษาสัตว์</h5>

<table>
  <thead>
    <tr>
      <th>#</th>
      <th>ชื่อสุนัข</th>
      <th>คลินิก</th>
      <th>วันและเวลานัด</th>
      <th>รายละเอียด</th>
      <th>สถานะ</th>
    </tr>
  </thead>
  <tbody>
  <?php
  $i=1; 
  while($r = mysqli_fetch_assoc($result)){
    echo "<tr>
      <td>{$i}</td>
      <td>{$r['dog_name']}</td>
      <td>{$r['clinic_name']}</td>
      <td>".date("d/m/Y H:i",strtotime($r['appointment_date']))."</td>
      <td>{$r['description']}</td>
      <td>{$r['status']}</td>
    </tr>";
    $i++;
  }
  ?>
  </tbody>
</table>

<div class="footer">
  วันที่พิมพ์: <?=date("d/m/Y H:i")?><br>
  ผู้พิมพ์: <?=htmlspecialchars($_SESSION['fullname'] ?? 'Admin')?>
</div>

<script>
window.print();
</script>
</body>
</html>
