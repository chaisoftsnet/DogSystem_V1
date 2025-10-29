<?php
// รับค่าจากปุ่มการ์ด
$report_type = isset($_GET['report_type']) ? $_GET['report_type'] : "";
$Mode = isset($_GET['Mode']) ? $_GET['Mode'] : "list";
$clinic_id = isset($_SESSION['clinic_id']) ? $_SESSION['clinic_id'] : null;

// Mapping: รายการตาราง + คำอธิบาย
$tables = [
    "dogs"   => ["field" => "dog_id", "label" => "🐶 สุนัขส่งรักษา"],    
    "treatments"   => ["field" => "treatment_id", "label" => "📋 ประวัติการรักษา"],
    "appointments" => ["field" => "appointment_id", "label" => "📅 การนัดหมาย"],
    "vaccinations" => ["field" => "vaccination_id", "label" => "💉 การฉีดวัคซีน"],
    "dewormings"   => ["field" => "deworm_id", "label" => "💊 ถ่ายพยาธิ/กันเห็บหมัด"],
    "lab_results"  => ["field" => "lab_id", "label" => "🔬 ผลตรวจแล็บ"],
    "surgeries"    => ["field" => "surgery_id", "label" => "🩺 การผ่าตัด/หัตถการ"],
    "nutrition"    => ["field" => "nutrition_id", "label" => "🥗 โภชนาการ/อาหาร"],
    "boarding"     => ["field" => "boarding_id", "label" => "🏠 ฝากเลี้ยง"],
    "attachments"  => ["field" => "attachment_id", "label" => "📎 ไฟล์แนบ/เอกสาร"]
];

// ตรวจสอบว่าตารางที่ส่งมาถูกต้อง
if (!array_key_exists($report_type, $tables)) {
    echo "<h3>❌ ไม่พบข้อมูลที่ต้องการแสดง</h3>";
    exit;
}
// ดึงข้อมูล
$field = $tables[$report_type]["field"];
$label = $tables[$report_type]["label"];
$sql = "SELECT * FROM $report_type";
if (isset($_SESSION['role']) && $_SESSION['role'] == 2 && $clinic_id) {
    $sql .= " WHERE clinic_id=$clinic_id";
}
$q = mysqli_query($objCon, $sql);
?>

<div class="container-fluid mt-4">
  <h2 class="mb-4"><?php echo $label; ?></h2>  
  <div class="table-responsive">
    <table class="table table-striped table-bordered">
      <thead class="table-dark">
        <tr>
          <?php
          // สร้างหัวตารางจากชื่อฟิลด์
          $fields = mysqli_fetch_fields($q);
          foreach ($fields as $f) {
              echo "<th>".$f->name."</th>";
          }
          ?>
        </tr>
      </thead>
      
        <?php
        // แสดงข้อมูล
        mysqli_data_seek($q, 0);
        while ($row = mysqli_fetch_assoc($q)) {
            echo "<tr>";
            foreach ($row as $val) {
                echo "<td>".htmlspecialchars($val)."</td>";
            }
            echo "</tr>";
        }
        ?>      
    </table>
  </div>


