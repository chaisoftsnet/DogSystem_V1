<?php
include("dbConnect.php");

/* ===================== GET DOG ===================== */
$dog_id = isset($_GET['dog_id']) ? intval($_GET['dog_id']) : 0;
$dog = null;

if ($dog_id > 0) {
    $dog = mysqli_fetch_assoc(mysqli_query($objCon,"SELECT * FROM dogs WHERE dog_id=$dog_id"));
    $treatments    = mysqli_query($objCon,"SELECT * FROM treatments WHERE dog_id=$dog_id ORDER BY treatment_date DESC");
    $appointments  = mysqli_query($objCon,"SELECT * FROM appointments WHERE dog_id=$dog_id ORDER BY appointment_date DESC");
    $vaccinations  = mysqli_query($objCon,"SELECT * FROM vaccinations WHERE dog_id=$dog_id ORDER BY vaccine_date DESC");
    $dewormings    = mysqli_query($objCon,"SELECT * FROM dewormings WHERE dog_id=$dog_id ORDER BY treatment_date DESC");
    $lab_results   = mysqli_query($objCon,"SELECT * FROM lab_results WHERE dog_id=$dog_id ORDER BY test_date DESC");
    $surgeries     = mysqli_query($objCon,"SELECT * FROM surgeries WHERE dog_id=$dog_id ORDER BY surgery_date DESC");
    $nutrition     = mysqli_query($objCon,"SELECT * FROM nutrition WHERE dog_id=$dog_id ORDER BY created_at DESC");
    $boarding      = mysqli_query($objCon,"SELECT * FROM boarding WHERE dog_id=$dog_id ORDER BY start_date DESC");
    $attachments   = mysqli_query($objCon,"SELECT * FROM attachments WHERE dog_id=$dog_id ORDER BY uploaded_at DESC");
}

/* ===================== TABLE RENDER ===================== */
function showTable($title,$result,$headers,$fields,$module,$idField){
    $dog_id = intval($_GET['dog_id']);

    echo '<div class="card mb-4">';
    echo '<div class="card-header bg-secondary text-white d-flex justify-content-between">';
    echo '<span>'.$title.'</span>';
    echo '<button class="btn btn-sm btn-light"
        onclick="openForm(
            \'form.php?module='.$module.'&action=add&dog_id='.$dog_id.'\'
        )">+ เพิ่ม</button>';
    echo '</div>';

    echo '<div class="card-body table-responsive">';
    if(mysqli_num_rows($result)>0){

        echo '<table class="table table-bordered table-sm align-middle">';
        echo '<thead class="table-light"><tr>';
        foreach($headers as $h) echo "<th>$h</th>";
        echo '<th width="140">จัดการ</th>';
        echo '</tr></thead><tbody>';

        while($row=mysqli_fetch_assoc($result)){
            echo '<tr>';
            foreach($fields as $f){
                if($f==='file_path' && !empty($row[$f])){
                    echo '<td><a href="'.$row[$f].'" target="_blank">📎 เปิดไฟล์</a></td>';
                }else{
                    echo '<td>'.htmlspecialchars($row[$f]??'').'</td>';
                }
            }

            echo '<td align="center">
                <button class="btn btn-warning btn-sm"
                    onclick="openForm(
                        \'form.php?module='.$module.'&action=edit&id='.$row[$idField].'&dog_id='.$dog_id.'\'
                    )">แก้ไข</button>

                <button class="btn btn-danger btn-sm"
                    onclick="confirmDelete(
                        \''.$module.'\',
                        '.$row[$idField].',
                        '.$dog_id.'
                    )">ลบ</button>
            </td>';

            echo '</tr>';
        }

        echo '</tbody></table>';
    }else{
        echo '<p class="text-muted mb-0">ไม่มีข้อมูล</p>';
    }
    echo '</div></div>';
}

/* ===================== AJAX SECTION ===================== */
//$title,$result,$headers,$fields,$module,$idField
if(isset($_GET['ajax'])){
    switch($_GET['ajax']){
        case 'treatment':
            showTable("ประวัติการรักษา",$treatments,
                ["วันที่","อาการ","การวินิจฉัย","การรักษา","ยา","สัตวแพทย์","วันนัดถัดไป"],
                ["treatment_date","symptoms","diagnosis","treatment","medication","doctor_name","next_appointment"],
                "treatment","treatment_id"); break;

        case 'appointment':
            showTable("การนัดหมาย",$appointments,
                ["วันเวลา","เหตุผล","สถานะ"],
                ["appointment_date","description","status"],
                "appointment","appointment_id"); break;

        case 'vaccination':
            showTable("วัคซีน",$vaccinations,
                ["ชื่อวัคซีน","ประเภท","วันที่ฉีด","วันนัดถัดไป","สัตวแพทย์","หมายเหตุ"],
                ["vaccine_name","vaccine_type","vaccine_date","next_due_date","doctor_name","note"],
                "vaccination","vaccine_id"); break;

        case 'lab':
            showTable("ผลแล็บ",$lab_results,
                ["วันที่ตรวจ","ผลเลือด","ผลปัสสาวะ","ไฟล์","หมายเหตุ"],
                ["test_date","blood_result","urine_result","file_path","note"],
                "lab","lab_id"); break;

        case 'deworming':
            showTable("ถ่ายพยาธิ / เห็บหมัด",$dewormings,
                ["ชื่อยา","วันที่ให้","วันนัดถัดไป","หมายเหตุ"],
                ["drug_name","treatment_date","next_due_date","note"],
                "deworming","deworming_id"); break;

        case 'surgery':
            showTable("ผ่าตัด / หัตถการ",$surgeries,
                ["วันที่","ประเภท","รายละเอียด","สัตวแพทย์","ผลลัพธ์","ดูแลหลังผ่าตัด"],
                ["surgery_date","surgery_type","description","doctor_name","outcome","notes"],
                "surgery","surgery_id"); break;

        case 'nutrition':
            showTable("โภชนาการ",$nutrition,
                ["อาหาร","แพ้อาหาร","คำแนะนำ"],
                ["food","allergy","advice"],
                "nutrition","nutrition_id"); break;

        case 'boarding':
            showTable("ฝากเลี้ยง",$boarding,
                ["วันที่รับฝาก","วันที่รับกลับ","อาการ","การดูแล"],
                ["start_date","end_date","symptoms","care"],
                "boarding","boarding_id"); break;

        case 'attachment':
            showTable("เอกสาร",$attachments,
                ["ประเภท","ไฟล์","หมายเหตุ"],
                ["file_type","file_path","note"],
                "attachment","attachment_id"); break;
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>ทะเบียนประวัติสุนัข</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
<div class="container my-4">

<h2 class="mb-4">📖 ทะเบียนประวัติสุนัข</h2>

<form method="get" class="row g-3 mb-4">
    <div class="col-auto">
        <input type="number" name="dog_id" class="form-control" placeholder="กรอกรหัสสุนัข" required>
    </div>
    <div class="col-auto">
        <button class="btn btn-primary">ค้นหา</button>
    </div>
</form>

<?php if($dog): ?>
<div class="card mb-4">
<div class="card-header bg-primary text-white">🐶 ข้อมูลสุนัข</div>
<div class="card-body row">
<div class="col-md-3">
<?php if($dog['dog_image_path']): ?>
<img src="<?=htmlspecialchars($dog['dog_image_path'])?>" class="img-fluid rounded">
<?php endif; ?>
</div>
<div class="col-md-9">
<p><b>ชื่อ:</b> <?=htmlspecialchars($dog['dog_name'])?></p>
<p><b>สายพันธุ์:</b> <?=htmlspecialchars($dog['dog_breed'])?></p>
<p><b>อายุ:</b> <?=$dog['dog_age']?> ปี</p>
<p><b>น้ำหนัก:</b> <?=$dog['dog_weight']?> กก.</p>
<p><b>เพศ:</b> <?=htmlspecialchars($dog['dog_gender'])?></p>
</div>
</div>
</div>

<div id="section-treatment"><?php showTable("ประวัติการรักษา",$treatments,
["วันที่","อาการ","การวินิจฉัย","การรักษา","ยา","สัตวแพทย์","วันนัดถัดไป"],
["treatment_date","symptoms","diagnosis","treatment","medication","doctor_name","next_appointment"],
"treatment","treatment_id"); ?></div>

<div id="section-appointment"><?php showTable("การนัดหมาย",$appointments,
["วันเวลา","เหตุผล","สถานะ"],
["appointment_date","description","status"],
"appointment","appointment_id"); ?></div>

<div id="section-vaccination"><?php showTable("วัคซีน",$vaccinations,
["ชื่อวัคซีน","ประเภท","วันที่ฉีด","วันนัดถัดไป","สัตวแพทย์","หมายเหตุ"],
["vaccine_name","vaccine_type","vaccine_date","next_due_date","doctor_name","note"],
"vaccination","vaccine_id"); ?></div>

<div id="section-lab"><?php showTable("ผลแล็บ",$lab_results,
["วันที่ตรวจ","ผลเลือด","ผลปัสสาวะ","ไฟล์","หมายเหตุ"],
["test_date","blood_result","urine_result","file_path","note"],
"lab","lab_id"); ?></div>

<div id="section-deworming"><?php showTable("ถ่ายพยาธิ / เห็บหมัด",$dewormings,
["ชื่อยา","วันที่ให้","วันนัดถัดไป","หมายเหตุ"],
["drug_name","treatment_date","next_due_date","note"],
"deworming","deworming_id"); ?></div>

<div id="section-surgery"><?php showTable("ผ่าตัด / หัตถการ",$surgeries,
["วันที่","ประเภท","รายละเอียด","สัตวแพทย์","ผลลัพธ์","ดูแลหลังผ่าตัด"],
["surgery_date","surgery_type","description","doctor_name","outcome","notes"],
"surgery","surgery_id"); ?></div>

<div id="section-nutrition"><?php showTable("โภชนาการ",$nutrition,
["อาหาร","แพ้อาหาร","คำแนะนำ"],
["food","allergy","advice"],
"nutrition","nutrition_id"); ?></div>

<div id="section-boarding"><?php showTable("ฝากเลี้ยง",$boarding,
["วันที่รับฝาก","วันที่รับกลับ","อาการ","การดูแล"],
["start_date","end_date","symptoms","care"],
"boarding","boarding_id"); ?></div>

<div id="section-attachment"><?php showTable("เอกสาร",$attachments,
["ประเภท","ไฟล์","หมายเหตุ"],
["file_type","file_path","note"],
"attachment","attachment_id"); ?></div>

<?php endif; ?>
</div>
<script>
const MODULE_LABELS = {
    treatment   : 'ประวัติการรักษา',
    appointment : 'การนัดหมาย',
    vaccination : 'วัคซีน',
    deworming   : 'ถ่ายพยาธิ',
    lab         : 'ผลตรวจแล็บ',
    surgery     : 'ผ่าตัด / หัตถการ',
    nutrition   : 'โภชนาการ',
    boarding    : 'ฝากเลี้ยง',
    attachment  : 'ไฟล์แนบ'
};
</script>

<!-- MODAL -->
<div class="modal fade" id="crudModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="crudModalTitle">จัดการข้อมูล</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body" id="modalContent"></div>

    </div>
  </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function openForm(url){
     // ดึงค่า module / action จาก URL
    const params = new URLSearchParams(url.split('?')[1] || '');
    const module = params.get('module');
    const action = params.get('action');

    // สร้าง title
    let title = 'จัดการข้อมูล';
    if(module && MODULE_LABELS[module]){
        title = (action === 'edit' ? 'แก้ไข' : 'เพิ่ม') + ' : ' + MODULE_LABELS[module];
    }

    // ตั้ง title ให้ modal
    document.getElementById('crudModalTitle').innerText = title;

    // โหลดฟอร์ม
    const modal = new bootstrap.Modal(document.getElementById('crudModal'));
    document.getElementById('modalContent').innerHTML = 'กำลังโหลด...';

    fetch(url)
        .then(r => r.text())
        .then(html => {
            document.getElementById('modalContent').innerHTML = html;
        });

    modal.show();
}

function reloadSection(module){
    fetch('dog_profile_new.php?dog_id=<?=$dog_id?>&ajax='+module)
        .then(r=>r.text())
        .then(html=>{
            document.getElementById('section-'+module).innerHTML=html;
        });
}

function confirmDelete(module, id, dog_id){
    Swal.fire({
        title:'ยืนยันการลบ',
        icon:'warning',
        showCancelButton:true,
        confirmButtonText:'ลบ'
    }).then(r=>{
        if(r.isConfirmed){

            const fd = new FormData();
            fd.append('module', module);
            fd.append('action', 'delete');
            fd.append('id', id);
            fd.append('dog_id', dog_id);

            fetch('update.php', {
                method: 'POST',
                body: fd
            })
            .then(r=>r.json())
            .then(d=>{
                if(d.status==='success'){
                    Swal.fire({
                        toast:true,
                        position:'top-end',
                        icon:'success',
                        title:d.message,
                        showConfirmButton:false,
                        timer:2000
                    });
                    reloadSection(module);
                } else {
                    Swal.fire('ผิดพลาด', d.message, 'error');
                }
            });
        }
    });
}

</script>
</body>
</html>
