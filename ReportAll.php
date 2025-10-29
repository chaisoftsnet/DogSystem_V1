<?php
@ob_start();
@session_start();
include 'dbconnect.php'; // ต้องคืนตัวแปร $objCon เป็น mysqli connection
include 'function.php';  // ถ้ามีฟังก์ชันช่วยเหลือ (opt_clinic, ret_clinic ฯลฯ)

// เริ่มค่าเริ่มต้น
$Mode = $_GET['Mode'] ?? '';
$report_type = $_GET['report_type'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$clinic_id = $_SESSION['clinic_id'] ?? null;

// รายการเมนู (เพิ่ม/แก้ได้ง่าย)
$reportMenu = [
    "dogs" => ["icon"=>"🐶","label"=>"รายงานข้อมูลสัตว์","btn"=>"primary"],
    "treatments" => ["icon"=>"💉","label"=>"รายงานการรักษาพยาบาล","btn"=>"success"],
    "appointments" => ["icon"=>"📅","label"=>"รายงานการนัดหมาย","btn"=>"warning"],
    "vaccinations" => ["icon"=>"💉","label"=>"รายงานการฉีดวัคซีน","btn"=>"info"],
    "dewormings" => ["icon"=>"💊","label"=>"รายงานถ่ายพยาธิ/กันเห็บ","btn"=>"secondary"],
    "lab_results" => ["icon"=>"🔬","label"=>"รายงานผลแล็บ","btn"=>"dark"],
    "surgeries" => ["icon"=>"🩺","label"=>"รายงานผ่าตัด/หัตถการ","btn"=>"danger"],
    "nutrition" => ["icon"=>"🥗","label"=>"รายงานโภชนาการ","btn"=>"light"],
    "boarding" => ["icon"=>"🏠","label"=>"รายงานฝากเลี้ยง","btn"=>"muted"],
    "attachments" => ["icon"=>"📎","label"=>"รายงานไฟล์แนบ","btn"=>"outline-primary"],
];

// ฟังก์ชันแสดงเมนูปุ่มด้านบน
function renderReportMenu($menu, $Mode, $report_type) {
    echo '<h3 class="mb-4 text-center">📊 รายงานระบบคลินิกรักษาสัตว์</h3>';
    echo '<div class="row g-2 mb-3">';

    foreach($menu as $key=>$m){
        $active = ($report_type==$key) ? "border-3" : "";
        echo '<div class="col-6 col-md-4 col-lg-3">';
        echo '<a href="?report_type='.$key.'&Mode='.$Mode.'" class="btn btn-outline-'.$m['btn'].' w-100 text-start py-3 '.$active.'">';
        echo '<div class="d-flex justify-content-between align-items-center">';
        echo '<div><strong style="font-size:1.05rem">'.$m['icon'].' '.$m['label'].'</strong></div>';
        echo '<div><i class="fa fa-arrow-right"></i></div>';
        echo '</div></a>';
        echo '</div>';
    }
    echo '</div>';
}

// ฟังก์ชันแสดงฟอร์มเลือกช่วงวันที่
function renderSearchForm($Mode, $start_date, $end_date, $report_type) {
    echo '<form method="GET" class="row g-2 align-items-end mb-4">';
    echo '<div class="col-auto"><label class="form-label mb-0">จากวันที่</label><input type="date" name="start_date" class="form-control" value="'.htmlspecialchars($start_date).'"></div>';
    echo '<div class="col-auto"><label class="form-label mb-0">ถึงวันที่</label><input type="date" name="end_date" class="form-control" value="'.htmlspecialchars($end_date).'"></div>';
    echo '<input type="hidden" name="Mode" value="'.htmlspecialchars($Mode).'">';
    echo '<input type="hidden" name="report_type" value="'.htmlspecialchars($report_type).'">';
    echo '<div class="col-auto"><button class="btn btn-primary">ค้นหา</button></div>';
    echo '</form>';
}

// ฟังก์ชันแสดงสรุป (summary card) — ปุ่มดูรายละเอียดส่ง report_type
function renderSummaryCard($objCon, $table, $label, $icon, $unit, $clinic_id=null) {
    $sql = "SELECT COUNT(*) as total FROM `$table`";
    if(isset($_SESSION['role']) && $_SESSION['role']==2 && $clinic_id){
        $sql .= " WHERE clinic_id=".intval($clinic_id);
    }
    $res = mysqli_query($objCon, $sql);
    $r = mysqli_fetch_assoc($res);
    $total = number_format($r['total'] ?? 0);

    echo '<div class="col-12 col-sm-6 col-md-4 col-lg-3">';
    echo '<div class="card p-3 mb-3 h-100">';
    echo '<div class="d-flex justify-content-between align-items-start">';
    echo '<div><h6 class="mb-1">'.$icon.' '.$label.'</h6><div class="text-muted small">รวมทั้งหมด</div></div>';
    echo '<div class="display-6 fw-bold text-primary">'.$total.'</div>';
    echo '</div>';
    echo '<div class="mt-3"><a href="?report_type='.$table.'&Mode='.htmlspecialchars($_GET["Mode"] ?? '').'" class="btn btn-outline-dark w-100">ดูรายละเอียด</a></div>';
    echo '</div></div>';
}

// ฟังก์ชันแสดงรายละเอียดรายงาน (table content)
function renderReportContent($objCon, $report_type, $start_date, $end_date, $clinic_id) {
    // ตั้ง SQL ตาม table
    $sql = "";
    switch($report_type) {
        case 'dogs':
            $sql = "SELECT dog_id, dog_name, dog_breed, dog_age, dog_weight, dog_gender, created_at FROM dogs WHERE 1=1";
            if($_SESSION['role']==2 && $clinic_id) $sql .= " AND clinic_id=".intval($clinic_id);
            if($start_date && $end_date) $sql .= " AND DATE(created_at) BETWEEN '$start_date' AND '$end_date'";
            $sql .= " ORDER BY created_at DESC";
            $headers = ['รหัส','ชื่อ','สายพันธุ์','อายุ','น้ำหนัก','เพศ','วันที่บันทึก'];
            break;

        case 'treatments':
            $sql = "SELECT t.treatment_date, d.dog_name, t.symptoms, t.treatment, t.doctor_name FROM treatments t LEFT JOIN dogs d ON t.dog_id=d.dog_id WHERE 1=1";
            if($_SESSION['role']==2 && $clinic_id) $sql .= " AND t.clinic_id=".intval($clinic_id);
            if($start_date && $end_date) $sql .= " AND t.treatment_date BETWEEN '$start_date' AND '$end_date'";
            $sql .= " ORDER BY t.treatment_date DESC";
            $headers = ['วันที่','สัตว์','อาการ','การรักษา','สัตวแพทย์'];
            break;

        case 'appointments':
            $sql = "SELECT a.appointment_date, d.dog_name, a.description, a.status FROM appointments a LEFT JOIN dogs d ON a.dog_id=d.dog_id WHERE 1=1";
            if($_SESSION['role']==2 && $clinic_id) $sql .= " AND a.clinic_id=".intval($clinic_id);
            if($start_date && $end_date) $sql .= " AND DATE(a.appointment_date) BETWEEN '$start_date' AND '$end_date'";
            $sql .= " ORDER BY a.appointment_date DESC";
            $headers = ['วันที่-เวลา','ชื่อสัตว์','วัตถุประสงค์ / รายละเอียด','สถานะ'];
            break;

        case 'vaccinations':
            $sql = "SELECT v.vaccine_date, d.dog_name, v.vaccine_name, v.vaccine_type, v.doctor_name FROM vaccinations v LEFT JOIN dogs d ON v.dog_id=d.dog_id WHERE 1=1";
            if($_SESSION['role']==2 && $clinic_id) $sql .= " AND v.clinic_id=".intval($clinic_id);
            if($start_date && $end_date) $sql .= " AND v.vaccine_date BETWEEN '$start_date' AND '$end_date'";
            $sql .= " ORDER BY v.vaccine_date DESC";
            $headers = ['วันที่','สัตว์','ชื่อวัคซีน','ประเภท','สัตวแพทย์'];
            break;

        case 'dewormings':
            $sql = "SELECT de.treatment_date, d.dog_name, de.drug_name, de.next_due_date FROM dewormings de LEFT JOIN dogs d ON de.dog_id=d.dog_id WHERE 1=1";
            if($_SESSION['role']==2 && $clinic_id) $sql .= " AND de.clinic_id=".intval($clinic_id);
            if($start_date && $end_date) $sql .= " AND de.treatment_date BETWEEN '$start_date' AND '$end_date'";
            $sql .= " ORDER BY de.treatment_date DESC";
            $headers = ['วันที่','สัตว์','ชื่อยา','วันนัดถัดไป'];
            break;

        case 'lab_results':
            $sql = "SELECT l.test_date, d.dog_name, l.blood_result, l.urine_result, l.file_path FROM lab_results l LEFT JOIN dogs d ON l.dog_id=d.dog_id WHERE 1=1";
            if($_SESSION['role']==2 && $clinic_id) $sql .= " AND l.clinic_id=".intval($clinic_id);
            if($start_date && $end_date) $sql .= " AND l.test_date BETWEEN '$start_date' AND '$end_date'";
            $sql .= " ORDER BY l.test_date DESC";
            $headers = ['วันที่','สัตว์','ผลเลือด','ผลปัสสาวะ','ไฟล์'];
            break;

        case 'surgeries':
            $sql = "SELECT s.surgery_date, d.dog_name, s.surgery_type, s.description, s.doctor_name FROM surgeries s LEFT JOIN dogs d ON s.dog_id=d.dog_id WHERE 1=1";
            if($_SESSION['role']==2 && $clinic_id) $sql .= " AND s.clinic_id=".intval($clinic_id);
            if($start_date && $end_date) $sql .= " AND s.surgery_date BETWEEN '$start_date' AND '$end_date'";
            $sql .= " ORDER BY s.surgery_date DESC";
            $headers = ['วันที่','สัตว์','ประเภท','รายละเอียด','สัตวแพทย์'];
            break;

        case 'nutrition':
            $sql = "SELECT n.created_at, d.dog_name, n.food, n.allergy, n.advice FROM nutrition n LEFT JOIN dogs d ON n.dog_id=d.dog_id WHERE 1=1";
            if($_SESSION['role']==2 && $clinic_id) $sql .= " AND n.clinic_id=".intval($clinic_id);
            $sql .= " ORDER BY n.created_at DESC";
            $headers = ['วันที่บันทึก','สัตว์','อาหาร','แพ้','คำแนะนำ'];
            break;

        case 'boarding':
            $sql = "SELECT b.start_date, b.end_date, d.dog_name, b.symptoms, b.care FROM boarding b LEFT JOIN dogs d ON b.dog_id=d.dog_id WHERE 1=1";
            if($_SESSION['role']==2 && $clinic_id) $sql .= " AND b.clinic_id=".intval($clinic_id);
            if($start_date && $end_date) $sql .= " AND b.start_date BETWEEN '$start_date' AND '$end_date'";
            $sql .= " ORDER BY b.start_date DESC";
            $headers = ['วันที่รับฝาก','วันที่รับกลับ','สัตว์','อาการ','การดูแล'];
            break;

        case 'attachments':
            $sql = "SELECT a.uploaded_at, d.dog_name, a.file_type, a.file_path, a.note FROM attachments a LEFT JOIN dogs d ON a.dog_id=d.dog_id WHERE 1=1";
            if($_SESSION['role']==2 && $clinic_id) $sql .= " AND a.clinic_id=".intval($clinic_id);
            if($start_date && $end_date) $sql .= " AND DATE(a.uploaded_at) BETWEEN '$start_date' AND '$end_date'";
            $sql .= " ORDER BY a.uploaded_at DESC";
            $headers = ['วันที่อัปโหลด','สัตว์','ประเภทไฟล์','ไฟล์','หมายเหตุ'];
            break;

        default:
            echo "<p class='text-center text-muted'>⚠️ กรุณาเลือกประเภทรายงานด้านบน</p>";
            return;
    }

    // ดึงข้อมูล
    $q = mysqli_query($objCon, $sql);
    echo '<div class="card p-3 mb-4">';
    echo '<div class="table-responsive">';
    echo '<table id="reportTable" class="table table-striped table-bordered table-sm" style="width:100%">';
    // header
    echo '<thead><tr>';
    foreach($headers as $h) echo "<th>".htmlspecialchars($h)."</th>";
    echo '</tr></thead><tbody>';
    // rows
    while($row = mysqli_fetch_assoc($q)){
        echo '<tr>';
        // แต่ละ case อาจต้องแปลงคอลัมน์ (ไฟล์ให้ลิงก์)
        switch($report_type){
            case 'dogs':
                echo "<td>{$row['dog_id']}</td>";
                echo "<td>".htmlspecialchars($row['dog_name'])."</td>";
                echo "<td>".htmlspecialchars($row['dog_breed'])."</td>";
                echo "<td>".htmlspecialchars($row['dog_age'])."</td>";
                echo "<td>".htmlspecialchars($row['dog_weight'])."</td>";
                echo "<td>".htmlspecialchars($row['dog_gender'])."</td>";
                echo "<td>".htmlspecialchars($row['created_at'])."</td>";
                break;
            case 'treatments':
                echo "<td>{$row['treatment_date']}</td>";
                echo "<td>".htmlspecialchars($row['dog_name'])."</td>";
                echo "<td>".nl2br(htmlspecialchars($row['symptoms']))."</td>";
                echo "<td>".nl2br(htmlspecialchars($row['treatment']))."</td>";
                echo "<td>".htmlspecialchars($row['doctor_name'])."</td>";
                break;
            case 'appointments':
                echo "<td>{$row['appointment_date']}</td>";
                echo "<td>".htmlspecialchars($row['dog_name'])."</td>";
                echo "<td>".nl2br(htmlspecialchars($row['description']))."</td>";
                echo "<td>".htmlspecialchars($row['status'])."</td>";
                break;
            case 'vaccinations':
                echo "<td>{$row['vaccine_date']}</td>";
                echo "<td>".htmlspecialchars($row['dog_name'])."</td>";
                echo "<td>".htmlspecialchars($row['vaccine_name'])."</td>";
                echo "<td>".htmlspecialchars($row['vaccine_type'])."</td>";
                echo "<td>".htmlspecialchars($row['doctor_name'])."</td>";
                break;
            case 'dewormings':
                echo "<td>{$row['treatment_date']}</td>";
                echo "<td>".htmlspecialchars($row['dog_name'])."</td>";
                echo "<td>".htmlspecialchars($row['drug_name'])."</td>";
                echo "<td>".htmlspecialchars($row['next_due_date'])."</td>";
                break;
            case 'lab_results':
                echo "<td>{$row['test_date']}</td>";
                echo "<td>".htmlspecialchars($row['dog_name'])."</td>";
                echo "<td>".nl2br(htmlspecialchars($row['blood_result']))."</td>";
                echo "<td>".nl2br(htmlspecialchars($row['urine_result']))."</td>";
                if(!empty($row['file_path'])){
                    $fp = htmlspecialchars($row['file_path']);
                    $fname = basename($fp);
                    echo "<td><a href=\"$fp\" target=\"_blank\">$fname</a></td>";
                } else {
                    echo "<td>-</td>";
                }
                break;
            case 'surgeries':
                echo "<td>{$row['surgery_date']}</td>";
                echo "<td>".htmlspecialchars($row['dog_name'])."</td>";
                echo "<td>".htmlspecialchars($row['surgery_type'])."</td>";
                echo "<td>".nl2br(htmlspecialchars($row['description']))."</td>";
                echo "<td>".htmlspecialchars($row['doctor_name'])."</td>";
                break;
            case 'nutrition':
                echo "<td>{$row['created_at']}</td>";
                echo "<td>".htmlspecialchars($row['dog_name'])."</td>";
                echo "<td>".nl2br(htmlspecialchars($row['food']))."</td>";
                echo "<td>".nl2br(htmlspecialchars($row['allergy']))."</td>";
                echo "<td>".nl2br(htmlspecialchars($row['advice']))."</td>";
                break;
            case 'boarding':
                echo "<td>{$row['start_date']}</td>";
                echo "<td>{$row['end_date']}</td>";
                echo "<td>".htmlspecialchars($row['dog_name'])."</td>";
                echo "<td>".nl2br(htmlspecialchars($row['symptoms']))."</td>";
                echo "<td>".nl2br(htmlspecialchars($row['care']))."</td>";
                break;
            case 'attachments':
                echo "<td>{$row['uploaded_at']}</td>";
                echo "<td>".htmlspecialchars($row['dog_name'])."</td>";
                echo "<td>".htmlspecialchars($row['file_type'])."</td>";
                $fp = htmlspecialchars($row['file_path']);
                $fname = $fp ? basename($fp) : '-';
                if($fp) echo "<td><a href=\"$fp\" target=\"_blank\">$fname</a></td>"; else echo "<td>-</td>";
                echo "<td>".nl2br(htmlspecialchars($row['note']))."</td>";
                break;
        }
        echo '</tr>';
    }
    echo '</tbody></table></div></div>';
    // DataTable script (init in footer)
}
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <title>รายงานระบบคลินิก</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- FontAwesome (optional) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- DataTables CSS + Buttons -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    .custom-card { min-height: 120px; }
  </style>
</head>
<link rel="stylesheet" href="css/theme.css">
<script src="js/theme.js"></script>
<body>
    
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    
    <div>
      <span class="toggle-dark" onclick="toggleDarkMode()"> <i class="bi bi-moon-stars"></i> / <i class="bi bi-brightness-high"></i></span>
      <a href="dashboard.php" class="btn btn-secondary btn-sm ms-2"><i class="fa fa-home"></i> กลับหน้าหลัก</a>
    </div>
  </div>

<div class="container mt-4">
  <?php renderReportMenu($reportMenu, $Mode, $report_type); ?>
  <?php renderSearchForm($Mode, $start_date, $end_date, $report_type); ?>

  <div class="row">
    <?php
    // แสดง summary cards — ยกเว้นบางตารางถ้าต้องการจะไม่ใส่
    $summaryTables = ['dogs'=>'สัตว์','treatments'=>'การรักษา','appointments'=>'นัดหมาย','vaccinations'=>'วัคซีน','dewormings'=>'ถ่ายพยาธิ','lab_results'=>'ผลแล็บ','surgeries'=>'ผ่าตัด','nutrition'=>'โภชนาการ','boarding'=>'ฝากเลี้ยง','attachments'=>'ไฟล์'];
    foreach($summaryTables as $t => $label) {
        renderSummaryCard($objCon, $t, $label, ($t==='dogs'?'🐶':'📋'), '', $clinic_id);
    }
    ?>
  </div>

  <hr>
  <!-- ปุ่ม Export ทั้งหน้า: ถ้าต้องการ export รายงานรายละเอียด ให้ใช้ DataTables buttons -->
  <?php
  if($report_type) {
      renderReportContent($objCon, $report_type, $start_date, $end_date, $clinic_id);
  } else {
      echo "<p class='text-center text-muted'>เลือกประเภทรายงานด้านบนเพื่อดูรายละเอียด</p>";
  }
  ?>
</div>

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables + Buttons + PDF/Excel libs -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

<script>
$(document).ready(function(){
    // ถ้ามีตารางรายละเอียด (id=reportTable) ให้ initialize DataTable พร้อมปุ่ม
    if($('#reportTable').length){
        $('#reportTable').DataTable({
            dom: 'Bfrtip',
            pageLength: 25,
            ordering: true,
            buttons: [
                { extend: 'excelHtml5', title: $('title').text() },
                { extend: 'csvHtml5', title: $('title').text() },
                { extend: 'pdfHtml5', title: $('title').text(),
                  orientation: 'landscape', pageSize: 'A4',
                  download: 'open'
                },
                { extend: 'print', title: $('title').text() }
            ],
            // ปรับ column width อัตโนมัติ
            responsive: true,
        });
    }
});
</script>
</body>
</html>
