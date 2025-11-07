<?php
@session_start();
require_once('dbconnect.php'); // $objCon
require_once('function.php');  // ถ้ามี checkRole(), ret_clinic()
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }

// ===== Helper: role & clinic filter =====
$role = (int)($_SESSION['role'] ?? 0);
$myClinic = (int)($_SESSION['clinic_id'] ?? 0);
$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

// admin เลือกคลินิกได้, อื่นๆ ล็อกคลินิกตัวเอง
if ($role == 3) {
  $clinic_id = isset($_GET['clinic_id']) && $_GET['clinic_id'] !== '' ? (int)$_GET['clinic_id'] : $myClinic;
} else {
  $clinic_id = $myClinic;
}

// ฟังก์ชันช่วย build where ตาม clinic (กรณีฟิลด์ต่างกัน)
function clinicWhere($tableAlias, $clinic_id, $role, $fallbackCol = 'clinic_id') {
  if ($role == 3 && (int)$clinic_id === 0) {
    return "1=1"; // admin = ดูทุกคลินิก
  }
  $col = $fallbackCol;
  // ตาราง/คีย์ที่ใช้ไม่ตรงกัน สามารถแม็พเพิ่มได้ที่นี่
  // เช่น invoices: clinic_id, dogs: clinic_id, appointments: clinic_id ...
  return $tableAlias ? "$tableAlias.$col = ".(int)$clinic_id : "$col = ".(int)$clinic_id;
}

// ===== สรุปตัวเลข (KPI) =====
// รวมใบเสร็จทั้งหมด (ชำระแล้ว)
$sql_total_sales = "
  SELECT IFNULL(SUM(ii.quantity*ii.unit_price),0) AS amt
  FROM invoice_items ii
  JOIN invoices i ON i.invoice_id=ii.invoice_id
  WHERE ".clinicWhere('i', $clinic_id, $role, 'clinic_id')." 
    AND YEAR(i.invoice_date)={$year} 
    AND i.status='ชำระแล้ว'
";
$total_sales = (float)(mysqli_fetch_assoc(mysqli_query($objCon,$sql_total_sales))['amt'] ?? 0);

// ต้นทุนรวม
$sql_total_cost = "
  SELECT IFNULL(SUM(ii.quantity*p.cost_price),0) AS cst
  FROM invoice_items ii
  JOIN invoices i ON i.invoice_id=ii.invoice_id
  JOIN products p ON p.product_name=ii.description
  WHERE ".clinicWhere('i', $clinic_id, $role, 'clinic_id')." 
    AND YEAR(i.invoice_date)={$year} 
    AND i.status='ชำระแล้ว'
";
$total_cost = (float)(mysqli_fetch_assoc(mysqli_query($objCon,$sql_total_cost))['cst'] ?? 0);

// กำไรขั้นต้น
$gross_profit = $total_sales - $total_cost;

// จำนวนนัดหมายปีนี้
$sql_appt = "
  SELECT COUNT(*) AS c FROM appointments a 
  WHERE ".clinicWhere('a',$clinic_id,$role,'clinic_id')." 
    AND YEAR(a.appointment_date)={$year}
";
$total_appt = (int)(mysqli_fetch_assoc(mysqli_query($objCon,$sql_appt))['c'] ?? 0);

// จำนวนเคสการรักษา (ตาราง treatments) ปีนี้
$sql_treatment = "
  SELECT COUNT(*) AS c FROM treatments t
  WHERE ".clinicWhere('t',$clinic_id,$role,'clinic_id')." 
    AND YEAR(t.treatment_date)={$year}
";
$total_treat = (int)(mysqli_fetch_assoc(mysqli_query($objCon,$sql_treatment))['c'] ?? 0);

// จำนวนสุนัขทั้งหมดในคลินิก
$sql_dogs = "
  SELECT COUNT(*) AS c FROM dogs d 
  WHERE ".clinicWhere('d',$clinic_id,$role,'clinic_id');
$total_dogs = (int)(mysqli_fetch_assoc(mysqli_query($objCon,$sql_dogs))['c'] ?? 0);

// ===== กราฟรายเดือน: รายได้ / ต้นทุน / กำไร =====
$sql_monthly = "
  SELECT 
    MONTH(i.invoice_date) AS m,
    SUM(ii.quantity*ii.unit_price) AS sales,
    SUM(ii.quantity*p.cost_price) AS cost,
    SUM(ii.quantity*(ii.unit_price - p.cost_price)) AS profit
  FROM invoice_items ii
  JOIN invoices i ON i.invoice_id=ii.invoice_id
  JOIN products p ON p.product_name=ii.description
  WHERE ".clinicWhere('i',$clinic_id,$role,'clinic_id')." 
    AND YEAR(i.invoice_date)={$year}
    AND i.status='ชำระแล้ว'
  GROUP BY MONTH(i.invoice_date)
  ORDER BY m
";
$resMonthly = mysqli_query($objCon,$sql_monthly);
$labels=[]; $sales=[]; $cost=[]; $profit=[];
$THMonths=['ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
$map = [];
while($r=mysqli_fetch_assoc($resMonthly)){ $map[(int)$r['m']]=$r; }
for($i=1;$i<=12;$i++){
  $labels[]=$THMonths[$i-1];
  $sales[] = isset($map[$i]) ? (float)$map[$i]['sales'] : 0;
  $cost[]  = isset($map[$i]) ? (float)$map[$i]['cost']  : 0;
  $profit[]= isset($map[$i]) ? (float)$map[$i]['profit']: 0;
}

// ===== กราฟ: การให้บริการทางการแพทย์รายเดือน (treatments / surgeries / vaccinations) =====
function monthlyCount($objCon,$clinic_id,$role,$year,$table,$dateCol,$alias){
  $sql="
    SELECT MONTH($alias.$dateCol) AS m, COUNT(*) AS c
    FROM $table $alias
    WHERE ".clinicWhere($alias,$clinic_id,$role,'clinic_id')."
      AND YEAR($alias.$dateCol)={$year}
    GROUP BY MONTH($alias.$dateCol)
    ORDER BY m";
  $res=mysqli_query($objCon,$sql);
  $out=array_fill(1,12,0);
  while($r=mysqli_fetch_assoc($res)){ $out[(int)$r['m']] = (int)$r['c']; }
  return array_values($out);
}
$lineTreat = monthlyCount($objCon,$clinic_id,$role,$year,'treatments','treatment_date','t');
$lineSurg  = monthlyCount($objCon,$clinic_id,$role,$year,'surgeries','surgery_date','s');
$lineVac   = monthlyCount($objCon,$clinic_id,$role,$year,'vaccinations','vaccine_date','v');

// ===== ล่าสุด 10 รายการนัดหมาย (แสดงในตารางย่อ) =====
$sql_last_appt="
  SELECT a.appointment_id, a.appointment_date, a.description, a.status,
         d.dog_name
  FROM appointments a
  LEFT JOIN dogs d ON d.dog_id=a.dog_id
  WHERE ".clinicWhere('a',$clinic_id,$role,'clinic_id')."
  ORDER BY a.appointment_date DESC
  LIMIT 10
";
$resLastAppt=mysqli_query($objCon,$sql_last_appt);

// สำหรับแอดมิน: รายชื่อคลินิก
$clinics=[];
if($role==3){
  $cq = mysqli_query($objCon,"SELECT clinic_id, clinic_name FROM clinics ORDER BY clinic_name");
  while($c = mysqli_fetch_assoc($cq)) $clinics[]=$c;
}
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>📊 Clinic Summary Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
:root{
  --bg: #0b1220;
  --card: rgba(255,255,255,0.08);
  --text: #ffffff;
  --sub: #bac2d6;
  --accent: #00e676;
  --border: rgba(255,255,255,0.12);
}
body.light{
  --bg: #f5f8fd;
  --card: #ffffff;
  --text: #1b1e28;
  --sub: #51607a;
  --accent: #00bfa5;
  --border: rgba(0,0,0,0.08);
}
body{
  background: radial-gradient(circle at top, #1b2735 0%, #090a0f 80%);
  color: var(--text);
  min-height:100vh;
}
.container-wrap{max-width:1200px; margin:60px auto;}
.card-glass{
  background: var(--card);
  border:1px solid var(--border);
  border-radius:18px; backdrop-filter: blur(10px);
  box-shadow: 0 10px 30px rgba(0,0,0,0.25);
}
.kpi{
  display:flex; gap:18px; flex-wrap:wrap;
}
.kpi .item{
  flex:1 1 240px; padding:18px 16px;
  background: var(--card); border:1px solid var(--border); border-radius:16px;
}
.kpi .item h4{ margin:0; font-weight:700; }
.kpi .item p{ margin:6px 0 0; color: var(--sub); }
.toolbar{
  display:flex; gap:12px; align-items:center; justify-content:space-between;
}
select, .btn, .form-select{ border-radius:10px; }
.theme-toggle{
  border:1px solid var(--border); color:var(--text); background:var(--card);
}
.table thead{ background:#0ea5e9; color:#fff; }
.table{ color:var(--text); }
.table tbody tr{ background: transparent; }
.table td, .table th{ border-color: var(--border) !important; }
a.btn-outline-light{ border-color: var(--border); color: var(--text); }
a.btn-outline-light:hover{ background: var(--accent); color:#000; }
</style>
</head>
<body>

<div class="container-wrap">
  <div class="toolbar mb-3">
    <div>
      <h3 class="mb-0">📊 สรุปภาพรวมคลินิก</h3>
      <div class="text-secondary small">คลินิก: 
        <?php 
          if($role==3){
            echo ($clinic_id?htmlspecialchars(ret_clinic($clinic_id,$objCon)):'ทุกคลินิก');
          }else{
            echo htmlspecialchars(ret_clinic($clinic_id,$objCon));
          }
        ?>
        · ปี <?= htmlspecialchars($year+543) ?>
      </div>
    </div>
    <div class="d-flex align-items-center gap-2">
      <form id="filterForm" method="get" class="d-flex gap-2">
        <?php if($role==3): ?>
          <select name="clinic_id" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="0" <?= $clinic_id==0?'selected':'' ?>>ทุกคลินิก</option>
            <?php foreach($clinics as $c): ?>
              <option value="<?= $c['clinic_id']?>" <?= $clinic_id==$c['clinic_id']?'selected':'' ?>>
                <?= htmlspecialchars($c['clinic_name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        <?php endif; ?>
        <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
          <?php for($y=date('Y')-3; $y<=date('Y')+1; $y++): ?>
            <option value="<?= $y?>" <?= $y==$year?'selected':'' ?>><?= $y+543 ?></option>
          <?php endfor; ?>
        </select>
        <a href="dashboard.php" class="btn btn-outline-light btn-sm"><i class="fa fa-home"></i> กลับหลัก</a>
        <button type="button" class="btn theme-toggle btn-sm" onclick="toggleTheme()">
          <i class="fa fa-moon"></i>
        </button>
        <button type="button" class="btn btn-outline-light btn-sm" onclick="window.print()">
          <i class="fa fa-print"></i> พิมพ์
        </button>
      </form>
    </div>
  </div>

  <!-- KPIs -->
  <div class="kpi mb-4">
    <div class="item">
      <h4>฿ <?= number_format($total_sales,2) ?></h4>
      <p>รายได้รวม (ชำระแล้ว) ปี <?= $year+543 ?></p>
    </div>
    <div class="item">
      <h4>฿ <?= number_format($total_cost,2) ?></h4>
      <p>ต้นทุนรวม (ตามราคาทุนสินค้า)</p>
    </div>
    <div class="item">
      <h4>฿ <?= number_format($gross_profit,2) ?></h4>
      <p>กำไรขั้นต้น (รายได้ - ต้นทุน)</p>
    </div>
    <div class="item">
      <h4><?= number_format($total_appt) ?></h4>
      <p>จำนวนนัดหมาย ปี <?= $year+543 ?></p>
    </div>
    <div class="item">
      <h4><?= number_format($total_treat) ?></h4>
      <p>เคสการรักษา ปี <?= $year+543 ?></p>
    </div>
    <div class="item">
      <h4><?= number_format($total_dogs) ?></h4>
      <p>สุนัขทั้งหมดในคลินิก</p>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-lg-7">
      <div class="card-glass p-3">
        <h5 class="mb-3">💹 รายได้-ต้นทุน-กำไร รายเดือน (<?= $year+543 ?>)</h5>
        <canvas id="barRevenue" height="140"></canvas>
      </div>
    </div>
    <div class="col-lg-5">
      <div class="card-glass p-3">
        <h5 class="mb-3">🩺 ภาพรวมงานรักษา รายเดือน</h5>
        <canvas id="lineService" height="140"></canvas>
      </div>
    </div>
  </div>

  <div class="card-glass p-3 mt-3">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <h5 class="mb-0">📅 นัดหมายล่าสุด</h5>
      <a class="btn btn-outline-light btn-sm" href="appointment_manage.php">
        จัดการนัดหมาย
      </a>
    </div>
    <div class="table-responsive">
      <table class="table table-bordered align-middle text-center">
        <thead>
          <tr>
            <th>วันที่นัด</th>
            <th>ชื่อสุนัข</th>
            <th>รายละเอียด</th>
            <th>สถานะ</th>
          </tr>
        </thead>
        <tbody>
          <?php if(mysqli_num_rows($resLastAppt)>0): ?>
            <?php while($a=mysqli_fetch_assoc($resLastAppt)): ?>
              <tr>
                <td><?= htmlspecialchars($a['appointment_date']) ?></td>
                <td><?= htmlspecialchars($a['dog_name'] ?? '-') ?></td>
                <td class="text-start"><?= htmlspecialchars($a['description'] ?? '-') ?></td>
                <td>
                  <?php 
                    $badge = 'secondary';
                    if($a['status']=='รอพบแพทย์') $badge='warning';
                    if($a['status']=='เสร็จสิ้น') $badge='success';
                    if($a['status']=='ยกเลิก') $badge='danger';
                  ?>
                  <span class="badge bg-<?= $badge ?>"><?= htmlspecialchars($a['status']) ?></span>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="4" class="text-center text-secondary">ยังไม่มีรายการ</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
const savedTheme = localStorage.getItem('theme') || 'dark';
if(savedTheme==='light'){ document.body.classList.add('light'); }
function toggleTheme(){
  document.body.classList.toggle('light');
  localStorage.setItem('theme', document.body.classList.contains('light') ? 'light' : 'dark');
}

const labels = <?= json_encode($labels) ?>;
const sales  = <?= json_encode($sales) ?>;
const cost   = <?= json_encode($cost) ?>;
const profit = <?= json_encode($profit) ?>;

new Chart(document.getElementById('barRevenue'), {
  type: 'bar',
  data: {
    labels,
    datasets: [
      { label:'รายได้', data: sales, backgroundColor:'#22c55e' },
      { label:'ต้นทุน', data: cost,  backgroundColor:'#86efac' },
      { label:'กำไร',  data: profit, backgroundColor:'#f59e0b' }
    ]
  },
  options: {
    responsive:true,
    plugins:{ legend:{ position:'bottom' }},
    scales:{ y:{ beginAtZero:true } }
  }
});

new Chart(document.getElementById('lineService'), {
  type: 'line',
  data: {
    labels,
    datasets: [
      { label:'รักษา', data: <?= json_encode($lineTreat) ?>, borderColor:'#38bdf8', backgroundColor:'rgba(56,189,248,.2)', fill:true, tension:.3 },
      { label:'ผ่าตัด', data: <?= json_encode($lineSurg) ?>,  borderColor:'#a78bfa', backgroundColor:'rgba(167,139,250,.2)', fill:true, tension=.3 },
      { label:'วัคซีน', data: <?= json_encode($lineVac) ?>,   borderColor:'#34d399', backgroundColor:'rgba(52,211,153,.2)', fill:true, tension=.3 }
    ]
  },
  options:{
    responsive:true,
    plugins:{ legend:{ position:'bottom' }},
    scales:{ y:{ beginAtZero:true } }
  }
});
</script>

</body>
</html>
