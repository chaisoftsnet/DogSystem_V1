<?php
@session_start();
include 'dbconnect.php';
include 'function.php';

if (!isset($_SESSION['clinic_id'])) exit('no session');

$visit_id   = (int)$_GET['visit_id'];
$clinic_id  = (int)$_SESSION['clinic_id'];
$doctor     = $_SESSION['fullname'];
$active_tab     = $_POST['tab'] ?? $_GET['tab'] ?? 'treat';
$active_service = $_POST['service_type'] ?? $_GET['service_type'] ?? 'treatment';

/* ===== DELETE TREATMENT ===== */
if(isset($_GET['delete_treatment_id'])){
  $file_sql = " DELETE FROM treatments
    WHERE treatment_id=".(int)$_GET['delete_treatment_id']."
    AND visit_id=$visit_id";
    echo  $file_sql;
  mysqli_query($objCon,$file_sql);
}

/* ===============================
   LOAD VISIT
================================ */
$visit = mysqli_fetch_assoc(mysqli_query($objCon,"
SELECT v.visit_id,v.visit_date,v.status,
       d.dog_id,d.dog_name,
       u.id AS user_id,u.fullname
FROM visits v
JOIN dogs d ON v.dog_id=d.dog_id
JOIN user u ON v.user_id=u.id
WHERE v.visit_id=$visit_id AND v.clinic_id=$clinic_id
LIMIT 1
"));
if(!$visit) exit('ไม่พบเคส');

/* ===============================
   PREPARE INVOICE
================================ */
$inv = mysqli_fetch_assoc(mysqli_query($objCon,"
SELECT invoice_id FROM invoices
WHERE clinic_id=$clinic_id
AND dog_id={$visit['dog_id']}
AND status='รอชำระ'
LIMIT 1
"));
if($inv){
  $invoice_id = $inv['invoice_id'];
}else{
  mysqli_query($objCon,"
    INSERT INTO invoices (clinic_id,user_id,dog_id)
    VALUES ($clinic_id,{$visit['user_id']},{$visit['dog_id']})
  ");
  $invoice_id = mysqli_insert_id($objCon);
}

/* ===============================
   AUTO STATUS
================================ */
if($visit['status']=='รอตรวจ'){
  mysqli_query($objCon,"
    UPDATE visits SET status='กำลังรักษา'
    WHERE visit_id=$visit_id
  ");
}

/* ===== DELETE TREATMENT ===== */
if(isset($_GET['delete_treatment_id'])){
  mysqli_query($objCon,"
    DELETE FROM treatments
    WHERE treatment_id=".(int)$_GET['delete_treatment_id']."
    AND visit_id=$visit_id
  ");
}
/* ===== DELETE VACCINE ======= */
if(isset($_GET['delete_vaccine_id'])){
  mysqli_query($objCon,"
    DELETE FROM vaccinations
    WHERE vaccine_id=".(int)$_GET['delete_vaccine_id']."
    AND clinic_id=$clinic_id
  ");
}
/* ===== DELETE DEWORMING ======= */
if(isset($_GET['delete_deworming_id'])){
  mysqli_query($objCon,"
    DELETE FROM dewormings
    WHERE deworming_id=".(int)$_GET['delete_deworming_id']."
    AND clinic_id=$clinic_id
  ");
}
/* ===== DELETE LAB ======= */
if(isset($_GET['delete_lab_id'])){
  mysqli_query($objCon,"
    DELETE FROM lab_results
    WHERE lab_id=".(int)$_GET['delete_lab_id']."
    AND clinic_id=$clinic_id
  ");
}
/* ===== DELETE SURGERY ======= */
if(isset($_GET['delete_surgery_id'])){
  mysqli_query($objCon,"
    DELETE FROM surgeries
    WHERE surgery_id=".(int)$_GET['delete_surgery_id']."
    AND clinic_id=$clinic_id
  ");
}


/* ===============================
   SAVE SERVICE
================================ */

if(isset($_POST['save_service'])){
  $service = $_POST['service_type'];
  switch($service){
case 'treatment':  
  
/* ===== SAVE TREATMENT ===== */
if(isset($_POST['save_service']) && $_POST['service_type']=='treatment'){

  /* ===== FILE UPLOAD ===== */
  $file_path = null;
  if(!empty($_FILES['attachment']['name'])){
    $dir = "uploads/treatments/";
    if(!is_dir($dir)) mkdir($dir,0777,true);
    $ext = pathinfo($_FILES['attachment']['name'],PATHINFO_EXTENSION);
    $filename = "TR_{$visit_id}_".time().".".$ext;
    if(move_uploaded_file($_FILES['attachment']['tmp_name'],$dir.$filename)){
      $file_path = $filename;
    }
  }

  if(!empty($_POST['treatment_id'])){
    /* ===== UPDATE ===== */
   $file_sql = $file_path ? ", file_path='$file_path'" : "";
    $sql = "UPDATE treatments SET
      symptoms='".mysqli_real_escape_string($objCon,$_POST['symptoms'])."',
      diagnosis='".mysqli_real_escape_string($objCon,$_POST['diagnosis'])."',
      treatment='".mysqli_real_escape_string($objCon,$_POST['treatment'])."',
      medication='".mysqli_real_escape_string($objCon,$_POST['medication'])."',
      next_appointment=".(!empty($_POST['next_appointment'])?"'".$_POST['next_appointment']."'":"NULL").",
      file_type='".mysqli_real_escape_string($objCon,$_POST['file_type'])."',
      note='".mysqli_real_escape_string($objCon,$_POST['note_'])."'
      $file_sql
      WHERE treatment_id=".(int)$_POST['treatment_id']."
      AND visit_id=$visit_id";
    mysqli_query($objCon,$sql);

  }else{
    /* ===== INSERT ===== */
    mysqli_query($objCon,"
      INSERT INTO treatments
      (visit_id,clinic_id,dog_id,user_id,
       treatment_date,
       symptoms,diagnosis,treatment,medication,
       doctor_name,next_appointment,
       file_type,file_path,note)
      VALUES
      ($visit_id,$clinic_id,
       {$visit['dog_id']},{$visit['user_id']},
       CURDATE(),
       '".mysqli_real_escape_string($objCon,$_POST['symptoms'])."',
       '".mysqli_real_escape_string($objCon,$_POST['diagnosis'])."',
       '".mysqli_real_escape_string($objCon,$_POST['treatment'])."',
       '".mysqli_real_escape_string($objCon,$_POST['medication'])."',
       '$doctor',
       ".(!empty($_POST['next_appointment'])?"'".$_POST['next_appointment']."'":"NULL").",
       '".mysqli_real_escape_string($objCon,$_POST['file_type'])."',
       ".($file_path?"'$file_path'":"NULL").",
       '".mysqli_real_escape_string($objCon,$_POST['note'])."'
      )
    ");
  }
}
break;

case 'vaccination':
  if(!empty($_POST['edit_vaccine_id'])){
    /* ===============================
       UPDATE VACCINATION
    ================================ */
    
    $vaccine_sql= "UPDATE vaccinations SET
        vaccine_name='".mysqli_real_escape_string($objCon,$_POST['vaccine_name'])."',
        vaccine_type='".mysqli_real_escape_string($objCon,$_POST['vaccine_type'])."',
        vaccine_date=".(!empty($_POST['vaccine_date'])?"'".$_POST['vaccine_date']."'":"NULL").",
        next_due_date=".(!empty($_POST['next_due_date_'])?"'".$_POST['next_due_date_']."'":"NULL").",
        doctor_name='$doctor',
        note='".mysqli_real_escape_string($objCon,$_POST['note_v'])."'
      WHERE vaccine_id=".(int)$_POST['edit_vaccine_id']."
      AND clinic_id=$clinic_id
    ";    
    mysqli_query($objCon,$vaccine_sql);

  }else{
    /* ===============================
       INSERT VACCINATION
    ================================ */
    mysqli_query($objCon,"
      INSERT INTO vaccinations
      (dog_id,clinic_id,vaccine_name,vaccine_type,
       vaccine_date,next_due_date,doctor_name,note)
      VALUES
      ({$visit['dog_id']},$clinic_id,
       '".mysqli_real_escape_string($objCon,$_POST['vaccine_name'])."',
       '".mysqli_real_escape_string($objCon,$_POST['vaccine_type'])."',
       ".(!empty($_POST['vaccine_date'])?"'".$_POST['vaccine_date']."'":"NULL").",
       CURDATE(),
       ".(!empty($_POST['next_due_date_'])?"'".$_POST['next_due_date_']."'":"NULL").",
       '$doctor',
       '".mysqli_real_escape_string($objCon,$_POST['note_v'])."'
      )
    ");
  }
break;

case 'deworming':
  if(!empty($_POST['edit_deworming_id'])){
    /* ===== UPDATE DEWORMING ===== */
    mysqli_query($objCon,"
      UPDATE dewormings SET
        drug_name='".mysqli_real_escape_string($objCon,$_POST['drug_name'])."',
        treatment_date='".mysqli_real_escape_string($objCon,$_POST['treatment_date'])."',
        next_due_date=".(!empty($_POST['next_due_date'])?"'".$_POST['next_due_date']."'":"NULL").",
        note='".mysqli_real_escape_string($objCon,$_POST['note_dw'])."'
      WHERE deworming_id=".(int)$_POST['edit_deworming_id']."
      AND clinic_id=$clinic_id
    ");

  }else{
    /* ===== INSERT DEWORMING ===== */
    mysqli_query($objCon,"
      INSERT INTO dewormings
      (dog_id,clinic_id,drug_name,treatment_date,next_due_date,note)
      VALUES
      ({$visit['dog_id']},$clinic_id,
       '".mysqli_real_escape_string($objCon,$_POST['drug_name'])."',
       '".mysqli_real_escape_string($objCon,$_POST['treatment_date'])."',
       ".(!empty($_POST['next_due_date'])?"'".$_POST['next_due_date']."'":"NULL").",
       '".mysqli_real_escape_string($objCon,$_POST['note_dw'])."'
      )
    ");
  }
break;

case 'lab':
  /* ===== FILE UPLOAD (LAB FILE) ===== */
  $file_path = null;
  if(!empty($_FILES['lab_file']['name'])){
    $dir = "uploads/labs/";
    if(!is_dir($dir)) mkdir($dir,0777,true);

    $ext = pathinfo($_FILES['lab_file']['name'], PATHINFO_EXTENSION);
    $filename = "LAB_{$visit_id}_".time().".".$ext;

    if(move_uploaded_file($_FILES['lab_file']['tmp_name'],$dir.$filename)){
      $file_path = $filename;
    }
  }

  if(!empty($_POST['lab_id'])){
    /* ===== UPDATE LAB ===== */
    $file_sql = $file_path ? ", file_path='$file_path'" : "";

    mysqli_query($objCon,"
      UPDATE lab_results SET
        test_name='".mysqli_real_escape_string($objCon,$_POST['test_name'])."',
        test_date='".mysqli_real_escape_string($objCon,$_POST['test_date'])."',
        blood_result='".mysqli_real_escape_string($objCon,$_POST['blood_result'])."',
        urine_result='".mysqli_real_escape_string($objCon,$_POST['urine_result'])."',
        note='".mysqli_real_escape_string($objCon,$_POST['note_lab'])."'
        $file_sql
      WHERE lab_id=".(int)$_POST['lab_id']."
      AND clinic_id=$clinic_id
    ");

  }else{
    /* ===== INSERT LAB ===== */
    mysqli_query($objCon,"
      INSERT INTO lab_results
      (dog_id,clinic_id,test_name,test_date,blood_result,urine_result,file_path,note)
      VALUES
      ({$visit['dog_id']},$clinic_id,
       '".mysqli_real_escape_string($objCon,$_POST['test_name'])."',
       '".mysqli_real_escape_string($objCon,$_POST['test_date'])."',
       '".mysqli_real_escape_string($objCon,$_POST['blood_result'])."',
       '".mysqli_real_escape_string($objCon,$_POST['urine_result'])."',
       ".($file_path?"'$file_path'":"NULL").",
       '".mysqli_real_escape_string($objCon,$_POST['note_lab'])."'
      )
    ");
  }
break;

    case 'surgery':

  /* ===== FILE UPLOAD (SURGERY) ===== */
  $file_path = null;
  if(!empty($_FILES['surgery_file']['name'])){
    $dir = "uploads/surgeries/";
    if(!is_dir($dir)) mkdir($dir,0777,true);

    $ext = pathinfo($_FILES['surgery_file']['name'], PATHINFO_EXTENSION);
    $filename = "SUR_{$visit_id}_".time().".".$ext;

    if(move_uploaded_file($_FILES['surgery_file']['tmp_name'],$dir.$filename)){
      $file_path = $filename;
    }
  }

  if(!empty($_POST['edit_surgery_id'])){
    /* ===== UPDATE SURGERY ===== */
    $file_sql = $file_path ? ", file_path='$file_path'" : "";

    mysqli_query($objCon,"
      UPDATE surgeries SET
        surgery_date='".mysqli_real_escape_string($objCon,$_POST['surgery_date'])."',
        surgery_type='".mysqli_real_escape_string($objCon,$_POST['surgery_type'])."',
        description='".mysqli_real_escape_string($objCon,$_POST['description'])."',
        doctor_name='".mysqli_real_escape_string($objCon,$_POST['doctor_name'])."',
        outcome='".mysqli_real_escape_string($objCon,$_POST['outcome'])."',
        notes='".mysqli_real_escape_string($objCon,$_POST['notes_sg'])."'
        $file_sql
      WHERE surgery_id=".(int)$_POST['edit_surgery_id']."
      AND clinic_id=$clinic_id
    ");

  }else{
    /* ===== INSERT SURGERY ===== */
    mysqli_query($objCon,"
      INSERT INTO surgeries
      (clinic_id,dog_id,surgery_date,surgery_type,
       description,doctor_name,outcome,notes,file_path)
      VALUES
      ($clinic_id,{$visit['dog_id']},
       '".mysqli_real_escape_string($objCon,$_POST['surgery_date'])."',
       '".mysqli_real_escape_string($objCon,$_POST['surgery_type'])."',
       '".mysqli_real_escape_string($objCon,$_POST['description'])."',
       '".mysqli_real_escape_string($objCon,$_POST['doctor_name'])."',
       '".mysqli_real_escape_string($objCon,$_POST['outcome'])."',
       '".mysqli_real_escape_string($objCon,$_POST['notes_sg'])."',
       ".($file_path?"'$file_path'":"NULL")."
      )
    ");
  }
break;

  }
}

/* ===============================
   ADD INVOICE ITEM
================================ */
if(isset($_POST['add_item'])){
  mysqli_query($objCon,"
    INSERT INTO invoice_items
    (invoice_id,description,quantity,unit_price)
    VALUES
    ($invoice_id,
     '".mysqli_real_escape_string($objCon,$_POST['description'])."',
     ".floatval($_POST['quantity']).",
     ".floatval($_POST['unit_price']).")
  ");
  $active_tab = 'summary';
}

/* ===============================
   SEND TO CASHIER
================================ */
if(isset($_POST['send_payment'])){
  mysqli_query($objCon,"
    UPDATE visits SET status='รอชำระเงิน'
    WHERE visit_id=$visit_id
  ");
  echo "<script>
    if(window.parent){
      window.parent.openCashier($invoice_id);
    }
  </script>";
  exit;
}

/* ===============================
   LOAD PRODUCTS
================================ */
$products = mysqli_query($objCon,"
SELECT product_name,unit_price FROM products
ORDER BY category,product_name
");
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>Visit Summary</title>
<link rel="stylesheet" href="assets/css/chsn_theme.css">
<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">

<script>
function applyTheme(){
  const t=localStorage.getItem('theme')||'dark';
  document.body.classList.toggle('light',t==='light');
}

function openTab(tab){
  document.querySelectorAll('.tab,.tab-content')
    .forEach(e=>e.classList.remove('active'));

  document.getElementById(tab).classList.add('active');
  document.querySelector('[data-tab="'+tab+'"]').classList.add('active');

  document.getElementById('tab').value = tab;
}

function showService(service){

  // ซ่อนทุก service-form
  document.querySelectorAll('.service-form')
    .forEach(d => d.style.display = 'none');

  // แสดง service ที่เลือก
  document.getElementById(service).style.display = 'block';

  // set hidden input
  document.getElementById('service_type').value = service;

  // จัด active ให้ปุ่ม
  document.querySelectorAll('.service-option-bar button')
    .forEach(btn => btn.classList.remove('active'));

  const activeBtn = document.querySelector(
    '.service-option-bar button[data-service="'+service+'"]'
  );
  if(activeBtn) activeBtn.classList.add('active');
}

// โหลดหน้าแล้วจำ service เดิม
document.addEventListener('DOMContentLoaded', function () {
  showService('<?=$active_service?>');
});

document.addEventListener('DOMContentLoaded',()=>{
  applyTheme();
  openTab('<?=$active_tab?>');
  showService('<?=$active_service?>');
});
</script>
<style>
body{font-family:Prompt;padding:20px;}
.card{background:var(--card);padding:20px;border-radius:16px;color:var(--text-main);}
.service-option-bar{display:flex;gap:8px;margin-bottom:15px;}
.service-option-bar button{background:#1e293b;color:#fff;border:none;padding:8px 14px;border-radius:20px;}
.tab-bar{display:flex;gap:8px;margin:20px 0;}
.tab{background:#334155;color:#fff;border:none;padding:6px 14px;border-radius:20px;}
.tab.active{background:#2563eb;}
.tab-content{display:none;}
.tab-content.active{display:block;}
input,textarea,select{width:100%;margin-bottom:10px;background:#111827;color:#fff;border:1px solid #374151;border-radius:6px;padding:8px;}
.btn-save{background:#22c55e;color:#fff;}
.btn-pay{background:#f59e0b;color:#000;}
.invoice-table{width:100%;border-collapse:collapse;}
.invoice-table td,.invoice-table th{padding:8px;border-bottom:1px solid var(--border);}
.invoice-total td{border-top:2px solid var(--border);font-weight:bold;}
table.datatable {
  border-collapse: collapse;
  font-size: 14px;
}

table.datatable th,
table.datatable td {
  padding: 8px;
}

table.datatable tr:hover {
  background: rgba(255,255,255,0.05);
}

a.btn-edit { color:#22c55e; }
a.btn-delete { color:#ef4444; }
.service-option-bar button {
  background:#1e293b;
  color:#cbd5f5;
  border:none;
  padding:8px 14px;
  border-radius:20px;
  cursor:pointer;
  transition: all .2s;
}

.service-option-bar button:hover {
  background:#334155;
}

.service-option-bar button.active {
  background:#2563eb;
  color:#fff;
  box-shadow: 0 0 0 2px rgba(37,99,235,.4);
}

</style>
</head>

<body>
<div class="card">
<h3>🐶 เปิดเคสที่ <?=$visit_id?>: <?=$visit['dog_name']?></h3>
<div class="tab-bar">
  <button class="tab" data-tab="treat" onclick="openTab('treat')">🩺 การรักษา</button>
  <button class="tab" data-tab="charge" onclick="openTab('charge')">💊 ยา / ค่าใช้จ่าย</button>
  <button class="tab" data-tab="summary" onclick="openTab('summary')">🧾 สรุปบิล</button>
</div>

<!-- TAB TREAT -->
<div id="treat" class="tab-content">
<div class="service-option-bar">
<button type="button" data-service="treatment"
            onclick="showService('treatment')">🩺 รักษา</button>
    <button type="button" data-service="vaccination"
            onclick="showService('vaccination')">💉 วัคซีน</button>
    <button type="button" data-service="deworming"
            onclick="showService('deworming')">💊 ถ่ายพยาธิ</button>
    <button type="button" data-service="lab"
            onclick="showService('lab')">🔬 แล็บ</button>
    <button type="button" data-service="surgery"
            onclick="showService('surgery')">🩺 ผ่าตัด</button>
</div>

<form method="post" enctype="multipart/form-data">  
  <input type="hidden" name="tab" id="tab" value="<?=$active_tab?>">
  <input type="hidden" name="service_type" id="service_type" value="<?=$active_service?>">
  <input type="hidden" name="visit_id" value="<?=$visit_id?>">
  
<!-- 1.TREATMENT  -->
<div id="treatment" class="service-form">
  <h4>📜 รายการการรักษา </h4>
  <?include 'treatment_list.php'?>  
  <h4>📜 แบบฟอร์มการรักษา </h4>
  <?php
  $edit_tr = null;
  if(isset($_GET['edit_treatment_id'])){
    $edit_tr = mysqli_fetch_assoc(mysqli_query($objCon,"
    SELECT * FROM treatments
    WHERE treatment_id=".(int)$_GET['edit_treatment_id']."
    AND visit_id=$visit_id
  "));
  }
  ?> 
  <input type="hidden" name="treatment_id" value="<?php echo $_GET['edit_treatment_id']; ?>"> 
  <textarea name="symptoms" placeholder="อาการที่พบ"><?php echo $edit_tr ? $edit_tr['symptoms'] : ''; ?></textarea>
  <textarea name="diagnosis" placeholder="การวินิจฉัย"><?php echo $edit_tr ? $edit_tr['diagnosis'] : ''; ?></textarea>
  <textarea name="treatment" placeholder="รายละเอียดการรักษา"><?php echo $edit_tr ? $edit_tr['treatment'] : ''; ?></textarea>
  <textarea name="medication" placeholder="ยา / เวชภัณฑ์"><?php echo $edit_tr ? $edit_tr['medication'] : ''; ?></textarea>
  <input type="date" name="next_appointment" value="<?php echo $edit_tr ? $edit_tr['next_appointment'] : ''; ?>" placeholder="วันนัดถัดไป">
  <label>แนบไฟล์ใหม่ (ถ้ามี)</label>
  <input type="file" name="attachment" accept="image/*,application/pdf">
  <select name="file_type">
  <option value="ใบเสร็จ" <?=($edit_tr && $edit_tr['file_type']=='ใบเสร็จ'?'selected':'')?>>
    ใบเสร็จ
  </option>
  <option value="ใบรับรองแพทย์" <?=($edit_tr && $edit_tr['file_type']=='ใบรับรองแพทย์'?'selected':'')?>>
    ใบรับรองแพทย์
  </option>
  <option value="โอนกรรมสิทธิ์" <?=($edit_tr && $edit_tr['file_type']=='โอนกรรมสิทธิ์'?'selected':'')?>>
    โอนกรรมสิทธิ์
  </option>
  <option value="อื่นๆ" <?=(!$edit_tr || $edit_tr['file_type']=='อื่นๆ'?'selected':'')?>>
    อื่นๆ
  </option>
</select>
  <input name="note_" placeholder="หมายเหตุ" value="<?php echo $edit_tr ? $edit_tr['note'] : ''; ?>">
 </div>

<!-- 2.VACCINE -->
<div id="vaccination" class="service-form" style="display:none">
  <h4>📜 รายการการฉีดวัคซีน</h4>
  <?include 'vaccination_list.php'?>  
  <h4>📜 แบบฟอร์มการฉีดวัคซีน  </h4>
<?php
$edit_vaccine = null;
if(isset($_GET['edit_vaccine_id'])){
  $edit_vaccine = mysqli_fetch_assoc(mysqli_query($objCon,"
    SELECT * FROM vaccinations
    WHERE vaccine_id=".(int)$_GET['edit_vaccine_id']."
    AND clinic_id=$clinic_id
  "));
}
?>
<input type="hidden" name="edit_vaccine_id" value="<?php echo $_GET['edit_vaccine_id']; ?>"> 
<input name="vaccine_name" placeholder="ชื่อวัคซีน" value="<?php echo $edit_vaccine ? $edit_vaccine['vaccine_name'] : ''; ?>">
<input name="vaccine_type" placeholder="ประเภทวัคซีน" value="<?php echo $edit_vaccine ? $edit_vaccine['vaccine_type'] : ''; ?>">
<input title='วันที่ฉีดวัคซีน' type="date" placeholder="วันที่ฉีดวัคซีน" name="vaccine_date" value="<?php echo $edit_vaccine ? $edit_vaccine['vaccine_date'] : ''; ?>">
<input title='วันนัดถัดไป' type="date" placeholder="วันนัดถัดไป" name="next_due_date_" value="<?php echo $edit_vaccine ? $edit_vaccine['next_due_date'] : ''; ?>">
<textarea name="doctor_name" placeholder="ชื่อหมอผู้ฉีด"><?php echo $edit_vaccine ? $edit_vaccine['doctor_name'] : ''; ?></textarea>
<textarea name="note_v" placeholder="หมายเหตุ"><?php echo $edit_vaccine ? $edit_vaccine['note'] : ''; ?></textarea>
</div>

<!-- 3.DEWORM -->
<div id="deworming" class="service-form" style="display:none">
  <h4>📜 รายการถ่ายพยาธิ</h4>
  <?include 'deworming_list.php'?>  
  <h4>📜 แบบฟอร์มการถ่ายพยาธิ</h4>
<?php
$edit_dw = null;
if(isset($_GET['edit_deworming_id'])){
  $edit_dw = mysqli_fetch_assoc(mysqli_query($objCon,"
    SELECT *
    FROM dewormings
    WHERE deworming_id=".(int)$_GET['edit_deworming_id']."
    AND clinic_id=$clinic_id
  "));
}
?>
<input type="hidden" name="edit_deworming_id" value="<?=$edit_dw['deworming_id'] ?? ''?>">
<input name="drug_name"
       placeholder="ชื่อยาพยาธิ"
       value="<?=$edit_dw['drug_name'] ?? ''?>">
<input type="date" title="วันที่ให้ยา"
       name="treatment_date"
       value="<?=$edit_dw['treatment_date'] ?? date('Y-m-d')?>">
<input type="date" title="วันครบถัดไป"
       name="next_due_date"
       value="<?=$edit_dw['next_due_date'] ?? ''?>">
<textarea name="note_dw"
          placeholder="หมายเหตุ"><?=$edit_dw['note'] ?? ''?></textarea>
</div>

<!-- 4.LAB -->
<div id="lab" class="service-form" style="display:none">
  <h4>📜 รายการผลตรวจทางห้องปฏิบัติการ (Lab)</h4>
  <?include 'lab_list.php'?>  
  <h4>📜 แบบฟอร์มผลตรวจทางห้องปฏิบัติการ (Lab)</h4>
<?php
$edit_lab = null;
if(isset($_GET['edit_lab_id'])){
  $edit_lab = mysqli_fetch_assoc(mysqli_query($objCon,"
    SELECT *
    FROM lab_results
    WHERE lab_id=".(int)$_GET['edit_lab_id']."
    AND clinic_id=$clinic_id
  "));
}
?>
<input type="hidden" name="edit_lab_id" value="<?=$edit_lab['lab_id'] ?? ''?>">
<input name="test_name"
       placeholder="ชื่อการตรวจ"
       value="<?=$edit_lab['test_name'] ?? ''?>">
<input type="date"
       name="test_date"
       value="<?=$edit_lab['test_date'] ?? date('Y-m-d')?>">
<textarea name="blood_result"
          placeholder="ผลเลือด"><?=$edit_lab['blood_result'] ?? ''?></textarea>
<textarea name="urine_result"
          placeholder="ผลปัสสาวะ"><?=$edit_lab['urine_result'] ?? ''?></textarea>
<label>แนบไฟล์แล็บ / X-ray / Ultrasound</label>
<input type="file" name="lab_file" accept="image/*,application/pdf">
<textarea name="note_lab"
          placeholder="หมายเหตุ"><?=$edit_lab['note'] ?? ''?></textarea>
</div>

<!-- 5.SURGERY -->
<div id="surgery" class="service-form" style="display:none">
  <h4>📜 รายการผ่าตัด/หัตถการ</h4>
  <?include 'surgery_list.php'?>  
  <h4>📜 แบบฟอร์มผ่าตัด/หัตถการ</h4>
<?php
$edit_surgery = null;
if(isset($_GET['edit_surgery_id'])){
  $edit_surgery = mysqli_fetch_assoc(mysqli_query($objCon,"
    SELECT *
    FROM surgeries
    WHERE surgery_id=".(int)$_GET['edit_surgery_id']."
    AND clinic_id=$clinic_id
  "));
}
?>
<input type="hidden" name="edit_surgery_id" value="<?=$edit_surgery['surgery_id'] ?? ''?>">
<input type="date" title="วันที่ผ่าตัด"
       name="surgery_date"
       value="<?=$edit_surgery['surgery_date'] ?? date('Y-m-d')?>">
<input name="surgery_type"
       placeholder="ประเภทการผ่าตัด"
       value="<?=$edit_surgery['surgery_type'] ?? ''?>">
<input name="doctor_name"
       placeholder="สัตวแพทย์"
       value="<?=$edit_surgery['doctor_name'] ?? $doctor?>">
<textarea name="description"
          placeholder="รายละเอียด"><?=$edit_surgery['description'] ?? ''?></textarea>
<textarea name="outcome"
          placeholder="ผลจากการผ่าตัด"><?=$edit_surgery['outcome'] ?? ''?></textarea>
<textarea name="notes_sg"
          placeholder="หมายเหตุ"><?=$edit_surgery['notes'] ?? ''?></textarea>
<label>แนบไฟล์ผ่าตัด</label>
<input type="file" name="surgery_file" accept="image/*,application/pdf">
</div>

<center><button class="btn-save" name="save_service">💾 บันทึกบริการ</button></center>
</form>
</div>

<!-- TAB CHARGE -->
<div id="charge" class="tab-content">  
<form method="post">
<select onchange="desc.value=this.value;price.value=this.selectedOptions[0].dataset.price;">
<option value="">-- เลือกรายการ --</option>
<?php while($p=mysqli_fetch_assoc($products)): ?>
<option value="<?=$p['product_name']?>" data-price="<?=$p['unit_price']?>">
<?=$p['product_name']?> (<?=$p['unit_price']?>)
</option>
<?php endwhile; ?>
</select>
<input id="desc" name="description">
<input type="number" name="quantity" value="1" step="0.5">
<input id="price" name="unit_price" step="0.01">
<input type="hidden" name="tab" value="summary">
<button name="add_item">➕ เพิ่ม</button>
</form>
</div>

<!-- TAB SUMMARY -->
<div id="summary" class="tab-content">
<?php
$items=mysqli_query($objCon,"
SELECT item_id,description,quantity,total_price
FROM invoice_items WHERE invoice_id=$invoice_id
");
$total=0;
?>
<table class="invoice-table">
<?php while($i=mysqli_fetch_assoc($items)):
$total+=$i['total_price']; ?>
<tr>
<td><?=$i['description']?></td>
<td><?=$i['quantity']?></td>
<td><?=number_format($i['total_price'],2)?></td>
<td>
<form method="post">
<input type="hidden" name="item_id" value="<?=$i['item_id']?>">
<input type="hidden" name="tab" value="summary">
<button name="delete_item">🗑</button>
</form>
</td>
</tr>
<?php endwhile; ?>
<tr class="invoice-total">
<td colspan="2">รวม</td>
<td><?=number_format($total,2)?></td>
<td></td>
</tr>
</table>

<form method="post">
<button class="btn-pay" name="send_payment">
💳 ส่งไปชำระเงิน
</button>
</form>
</div>

</div>
</body>
</html>
