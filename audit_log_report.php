<?php
@session_start();
require_once('dbConnect.php');
require_once('function.php');
checkRole(3);
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>📋 รายงานกิจกรรมผู้ใช้งาน</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
<div class="container">
  <h3 class="text-center mb-4">📋 รายงานกิจกรรมผู้ใช้งาน (Audit Log)</h3>

  <table class="table table-bordered table-striped align-middle text-center">
    <thead class="table-dark">
      <tr>
        <th>เวลา</th>
        <th>ผู้ใช้งาน</th>
        <th>การกระทำ</th>
        <th>หน้า</th>
        <th>รายละเอียด</th>
        <th>IP Address</th>
      </tr>
    </thead>
    <tbody>
    <?php
      $res = mysqli_query($objCon, "
        SELECT l.*, u.fullname 
        FROM audit_logs l 
        JOIN user u ON l.user_id = u.id 
        ORDER BY l.log_id DESC LIMIT 100
      ");
      while($r = mysqli_fetch_assoc($res)) {
        echo "
        <tr>
          <td>{$r['created_at']}</td>
          <td>{$r['fullname']}</td>
          <td>{$r['action']}</td>
          <td>{$r['page']}</td>
          <td>{$r['details']}</td>
          <td>{$r['ip_address']}</td>
        </tr>";
      }
    ?>
    </tbody>
  </table>
</div>
</body>
</html>
