<meta name="viewport" content="width=device-width, initial-scale=1">
<title>รายงานระบบคลินิกรักษาสัตว์</title>
<link rel="stylesheet" href="css/main.css">
<link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>
<!-- Fancybox CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.css" />
<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.umd.js"></script>
<script type="text/javascript" src="js/jquery.min.js"></script>
<script type="text/javascript" src="js/LocalStorage_Dog.js"></script>
<script src="clinic_dog_loader.js"></script>
    <style>
        body { background:#f8f9fa; }
        .custom-card { 
            border-radius: 20px; 
            transition: 0.2s; 
            background: #fff;
        }
        .custom-card:hover { transform: translateY(-5px); box-shadow: 0 6px 20px rgba(0,0,0,0.15); }
        .btn-gray { 
            background:#6c757d; 
            color:#fff; 
            border:none; 
            padding:8px; 
            border-radius:10px; 
            transition:0.2s;
        }
        .btn-gray:hover { background:#5a6268; }
        @media print {
            body { background:#fff; }
            .btn-gray { display:none; } /* ซ่อนปุ่มเวลา Print */
        }
    </style>
<?php
ob_start();
session_start();
include 'navbar.php';
include 'dbconnect.php';
include 'function.php';

// ดึง clinic_id จาก session
$clinic_id = $_SESSION['clinic_id'] ?? 0;
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$report_type = $_GET['report_type'] ?? 'dog';
?>
    <title>📊 รายงานภาพรวมคลินิก</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<div class="container-fluid mt-4">
    <h2 class="text-center mb-4">📊 รายงานภาพรวมคลินิก</h2>
    <div class="row g-4">
        <?php
        // 🐶 จำนวนสัตว์
        renderSummaryCard($objCon, "dogs", "dog_id", "จำนวนสัตว์ในระบบ", "🐶", "ตัว", $clinic_id);
        // 💉 จำนวนการรักษา
        renderSummaryCard($objCon, "treatments", "treatment_id", "จำนวนการรักษา", "💉", "ครั้ง", $clinic_id);
        // 📅 การนัดหมาย
        renderSummaryCard($objCon, "appointments", "appointment_id", "การนัดหมายทั้งหมด", "📅", "รายการ", $clinic_id);
        // 💉 วัคซีน
        renderSummaryCard($objCon, "vaccinations", "vaccination_id", "ประวัติการฉีดวัคซีน", "💉", "ครั้ง", $clinic_id);
        // 💊 ถ่ายพยาธิ
        renderSummaryCard($objCon, "dewormings", "deworm_id", "การถ่ายพยาธิ/ป้องกันเห็บหมัด", "💊", "ครั้ง", $clinic_id);
        // 🔬 ผลตรวจแล็บ
        renderSummaryCard($objCon, "lab_results", "lab_id", "ผลตรวจทางห้องแล็บ", "🔬", "ครั้ง", $clinic_id);
        // 🩺 ผ่าตัด/หัตถการ
        renderSummaryCard($objCon, "surgeries", "surgery_id", "การผ่าตัด/หัตถการ", "🩺", "ครั้ง", $clinic_id);
        // 🥗 โภชนาการ
        renderSummaryCard($objCon, "nutrition", "nutrition_id", "ข้อมูลโภชนาการ", "🥗", "รายการ", $clinic_id);
        // 🏠 ฝากเลี้ยง
        renderSummaryCard($objCon, "boarding", "boarding_id", "ประวัติการฝากเลี้ยง", "🏠", "ครั้ง", $clinic_id);
        // 📎 ไฟล์แนบ
        renderSummaryCard($objCon, "attachments", "attachment_id", "ไฟล์แนบ/เอกสาร", "📎", "ไฟล์", $clinic_id);        
        ?>
    </div>
</div>
<?
function renderSummaryCard($objCon, $table, $field, $label, $icon, $unit, $clinic_id = null, $Mode = '') {
    $sql = "SELECT COUNT($field) as total FROM $table";
    if(isset($_SESSION['role']) && $_SESSION['role']==2 && $clinic_id){
        $sql .= " WHERE clinic_id=$clinic_id";
    }
    $q = mysqli_query($objCon, $sql);
    $r = mysqli_fetch_assoc($q);
    $total = number_format($r['total']);
    echo "
    <div class='col-12 col-sm-6 col-md-4 col-lg-3'>
        <div class='card p-4 mb-4 shadow-sm custom-card h-100'>
            <h5 class='mb-3'>{$icon} {$label}</h5>
            <p class='display-6 fw-bold'>{$total} {$unit}</p>
            <button class='btn-gray w-100' onclick=\"location.href='?report_type={$table}&Mode={$Mode}'\">ดูรายละเอียด</button>
        </div>
    </div>
    ";
}
?>
<br>
<?php
   include 'Report_Menu.php';
?>
<script type="text/javascript" src="js/plugins/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="js/plugins/dataTables.bootstrap.min.js"></script>
<script type="text/javascript">
    $('#DataTable').DataTable(
     {
 	 "pageLength": 10,
        "columnDefs": [ {
          "targets": 'no-sort',
          "orderable": false,
		"iDisplayLength": 300,
         }]
 
	}
	);
	//close show entries
	//$(".dataTables_length").hide(); 
</script>

</body>
</html>
