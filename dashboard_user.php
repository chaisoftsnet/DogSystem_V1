<?php
@session_start();
include 'dbconnect.php';
include 'function.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$fullname = ret_user_fullname($user_id, $objCon);

// ดึงข้อมูลสุนัขของเจ้าของ
$dogs = mysqli_query($objCon, "SELECT * FROM dogs WHERE user_id='$user_id' ORDER BY dog_name ASC");

// === สถิติย่อย ===
$qDogCount = mysqli_query($objCon, "SELECT COUNT(*) AS total_dogs FROM dogs WHERE user_id='$user_id'");
$total_dogs = mysqli_fetch_assoc($qDogCount)['total_dogs'] ?? 0;

$qTreatCount = mysqli_query($objCon, "
    SELECT COUNT(*) AS total_treat 
    FROM treatments 
    WHERE dog_id IN (SELECT dog_id FROM dogs WHERE user_id='$user_id')
");
$total_treat = mysqli_fetch_assoc($qTreatCount)['total_treat'] ?? 0;

$qVaccineCount = mysqli_query($objCon, "
    SELECT COUNT(*) AS total_vac 
    FROM vaccinations 
    WHERE dog_id IN (SELECT dog_id FROM dogs WHERE user_id='$user_id')
");
$total_vaccine = mysqli_fetch_assoc($qVaccineCount)['total_vac'] ?? 0;

// === สถิติเพื่อกราฟ ===
$chart_treat = [];
$chart_vaccine = [];
for ($m = 1; $m <= 12; $m++) {
    $chart_treat[$m] = 0;
    $chart_vaccine[$m] = 0;
}

// การรักษารายเดือน
$qTreatMonth = mysqli_query($objCon, "
    SELECT MONTH(treatment_date) AS m, COUNT(*) AS c
    FROM treatments 
    WHERE dog_id IN (SELECT dog_id FROM dogs WHERE user_id='$user_id')
    GROUP BY MONTH(treatment_date)
");
while ($r = mysqli_fetch_assoc($qTreatMonth)) {
    $chart_treat[$r['m']] = (int)$r['c'];
}

// การฉีดวัคซีนรายเดือน
$qVacMonth = mysqli_query($objCon, "
    SELECT MONTH(vaccine_date) AS m, COUNT(*) AS c
    FROM vaccinations 
    WHERE dog_id IN (SELECT dog_id FROM dogs WHERE user_id='$user_id')
    GROUP BY MONTH(vaccine_date)
");
while ($r = mysqli_fetch_assoc($qVacMonth)) {
    $chart_vaccine[$r['m']] = (int)$r['c'];
}

?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>🐶 สัตว์เลี้ยงของฉัน | Dog Clinic</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
body {
  background-color: #f9f9f9;
  transition: background 0.3s, color 0.3s;
}
.dark-mode {
  background-color: #121212;
  color: #f1f1f1;
}
.container {
  max-width: 1100px;
}
.dog-card {
  border-radius: 20px;
  transition: transform 0.3s, box-shadow 0.3s;
  text-align: center;
}
.dog-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}
.dog-img {
  width: 120px;
  height: 120px;
  object-fit: cover;
  border-radius: 50%;
  border: 3px solid #ccc;
  margin-top: 15px;
}
.btn-add {
  border-radius: 50px;
}
.toggle-mode {
  position: fixed;
  top: 15px;
  right: 15px;
}
.stat-card {
  border-radius: 15px;
  text-align: center;
  padding: 20px;
  color: white;
  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}
.stat-card i {
  font-size: 30px;
  margin-bottom: 10px;
}
.bg1 { background: linear-gradient(135deg, #42a5f5, #478ed1); }
.bg2 { background: linear-gradient(135deg, #66bb6a, #388e3c); }
.bg3 { background: linear-gradient(135deg, #ffb74d, #f57c00); }
.chart-container {
  margin-top: 40px;
  padding: 20px;
  background: white;
  border-radius: 20px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
.dark-mode .chart-container {
  background: #1e1e1e;
}
</style>
</head>
<body>

<!-- 🌙 ปุ่ม Toggle Dark Mode -->
<button class="btn btn-outline-secondary btn-sm toggle-mode" onclick="toggleDarkMode()">
  <i class="fa fa-moon"></i> สลับโหมด
</button>

<div class="container py-4">
  <h3 class="text-center mb-3">🐾 สัตว์เลี้ยงของคุณ: <?= htmlspecialchars($fullname) ?></h3>
  <div class="text-center mb-4 text-muted">เข้าสู่ระบบในชื่อ: <strong><?= htmlspecialchars($username) ?></strong></div>

  <!-- 🔹 สถิติย่อย -->
  <div class="row text-center mb-4">
    <div class="col-md-4 mb-3">
      <div class="stat-card bg1">
        <i class="fa-solid fa-dog"></i>
        <h4><?= $total_dogs ?></h4>
        <p>จำนวนสุนัขทั้งหมด</p>
      </div>
    </div>
    <div class="col-md-4 mb-3">
      <div class="stat-card bg2">
        <i class="fa-solid fa-stethoscope"></i>
        <h4><?= $total_treat ?></h4>
        <p>จำนวนการรักษา</p>
      </div>
    </div>
    <div class="col-md-4 mb-3">
      <div class="stat-card bg3">
        <i class="fa-solid fa-syringe"></i>
        <h4><?= $total_vaccine ?></h4>
        <p>จำนวนการฉีดวัคซีน</p>
      </div>
    </div>
  </div>

  <!-- 🔹 กราฟสรุป -->
  <div class="chart-container">
    <h5 class="text-center mb-3"><i class="fa fa-chart-column"></i> สถิติการรักษาและฉีดวัคซีนรายเดือน</h5>
    <canvas id="chartDog"></canvas>
  </div>

  <!-- 🔹 ปุ่มเพิ่ม -->
  <div class="text-center mt-4 mb-4">
    <a href="dog_update.php" class="btn btn-success btn-add px-4">
      <i class="fa fa-plus-circle"></i> เพิ่มสุนัขใหม่
    </a>
  </div>

  <!-- 🔹 รายการ Card -->
  <div class="row justify-content-center g-4">
    <?php if (mysqli_num_rows($dogs) > 0): ?>
        <?php while($dog = mysqli_fetch_assoc($dogs)): ?>
        <div class="col-sm-6 col-md-4 col-lg-3">
          <div class="card dog-card shadow-sm">
            <div class="text-center">
              <?php if(!empty($dog['dog_image_path'])): ?>
                  <img src="<?= htmlspecialchars($dog['dog_image_path']) ?>" class="dog-img">
              <?php else: ?>
                  <img src="images/no-dog.png" class="dog-img">
              <?php endif; ?>
            </div>
            <div class="card-body">
              <h5 class="card-title mb-1"><?= htmlspecialchars($dog['dog_name']) ?></h5>
              <p class="card-text small text-muted mb-1">สายพันธุ์: <?= htmlspecialchars($dog['dog_breed']) ?: '-' ?></p>
              <p class="card-text small text-muted mb-1">อายุ: <?= $dog['dog_age'] ?> ปี | เพศ: <?= htmlspecialchars($dog['dog_gender']) ?></p>
              <p class="card-text small text-muted mb-1">น้ำหนัก: <?= $dog['dog_weight'] ?> กก.</p>
              <div class="d-flex justify-content-center mt-3">
                <a href="dog_profile.php?dog_id=<?= $dog['dog_id'] ?>" class="btn btn-primary btn-sm">
                  <i class="fa fa-file-medical"></i> ดูประวัติเต็ม
                </a>
              </div>
            </div>
          </div>
        </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="col-12 text-center text-muted">
          <p>🐕 ยังไม่มีข้อมูลสุนัขของคุณ</p>
        </div>
    <?php endif; ?>
  </div>

  <div class="text-center mt-5">
    <a href="logout.php" class="btn btn-outline-danger px-4"><i class="fa fa-sign-out"></i> ออกจากระบบ</a>
  </div>
</div>

<script>
function toggleDarkMode() {
  document.body.classList.toggle('dark-mode');
}

const ctx = document.getElementById('chartDog');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'],
        datasets: [
            {
                label: 'การรักษา',
                data: <?= json_encode(array_values($chart_treat)) ?>,
                borderColor: '#42a5f5',
                tension: 0.3,
                fill: false
            },
            {
                label: 'การฉีดวัคซีน',
                data: <?= json_encode(array_values($chart_vaccine)) ?>,
                borderColor: '#ffb74d',
                tension: 0.3,
                fill: false
            }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: { y: { beginAtZero: true } }
    }
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
