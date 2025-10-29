<?php
include("dbConnect.php");

// รับค่า dog_id
$dog_id = isset($_GET['dog_id']) ? intval($_GET['dog_id']) : 0;

$dog = null;
if ($dog_id > 0) {
    $dog = mysqli_fetch_assoc(mysqli_query($objCon, "SELECT * FROM dogs WHERE dog_id=$dog_id"));

    $treatments    = mysqli_query($objCon, "SELECT * FROM treatments WHERE dog_id=$dog_id ORDER BY treatment_date DESC");
    $appointments  = mysqli_query($objCon, "SELECT * FROM appointments WHERE dog_id=$dog_id ORDER BY appointment_date DESC");
    $vaccinations  = mysqli_query($objCon, "SELECT * FROM vaccinations WHERE dog_id=$dog_id ORDER BY vaccine_date DESC");
    $dewormings    = mysqli_query($objCon, "SELECT * FROM dewormings WHERE dog_id=$dog_id ORDER BY treatment_date DESC");
    $lab_results   = mysqli_query($objCon, "SELECT * FROM lab_results WHERE dog_id=$dog_id ORDER BY test_date DESC");
    $surgeries     = mysqli_query($objCon, "SELECT * FROM surgeries WHERE dog_id=$dog_id ORDER BY surgery_date DESC");
    $nutrition     = mysqli_query($objCon, "SELECT * FROM nutrition WHERE dog_id=$dog_id ORDER BY created_at DESC");
    $boarding      = mysqli_query($objCon, "SELECT * FROM boarding WHERE dog_id=$dog_id ORDER BY start_date DESC");
    $attachments   = mysqli_query($objCon, "SELECT * FROM attachments WHERE dog_id=$dog_id ORDER BY uploaded_at DESC");
}

// ฟังก์ชันแสดงตารางพร้อมลิงก์ไฟล์
function showTable($title, $result, $headers, $fields, $linkField='') {
    echo '<div class="card mb-4">';
    echo '<div class="card-header bg-secondary text-white">'.$title.'</div>';
    echo '<div class="card-body table-responsive">';
    if(mysqli_num_rows($result) > 0){
        echo '<table class="table table-bordered table-striped">';
        echo '<thead><tr>';
        foreach($headers as $h){ echo "<th>$h</th>"; }
        echo '</tr></thead><tbody>';
        while($row = mysqli_fetch_assoc($result)){
            echo '<tr>';
            foreach($fields as $f){
                $val = htmlspecialchars($row[$f] ?? '');
                if($f === $linkField && !empty($row[$f])){
                    $filename = basename($row[$f]);
                    $val = "<a href='".htmlspecialchars($row[$f])."' target='_blank'>$filename</a>";
                }
                echo "<td>$val</td>";
            }
            echo '</tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<span class="text-muted">ไม่มีข้อมูล</span>';
    }
    echo '</div></div>';
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
        <button type="submit" class="btn btn-primary">ค้นหา</button>
    </div>
</form>

<?php if ($dog): ?>
<div class="mb-3">
    <button class="btn btn-success" onclick="window.print()">🖨️ พิมพ์ประวัติ</button>
    <button class="btn btn-primary" onclick="exportPDF()">📄 Export PDF</button>
</div>

<div class="card mb-4">
    <div class="card-header bg-primary text-white">🐶 ข้อมูลสุนัข</div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
            <?php if ($dog['dog_image_path']): ?>
                <img src="<?=htmlspecialchars($dog['dog_image_path'])?>" class="img-fluid rounded">
            <?php else: ?>
                <span class="text-muted">ไม่มีรูปภาพ</span>
            <?php endif; ?>
            </div>
            <div class="col-md-9">
                <p><b>รหัส:</b> <?=$dog['dog_id']?></p>
                <p><b>ชื่อ:</b> <?=htmlspecialchars($dog['dog_name'])?></p>
                <p><b>สายพันธุ์:</b> <?=htmlspecialchars($dog['dog_breed'])?></p>
                <p><b>อายุ:</b> <?=$dog['dog_age']?> ปี</p>
                <p><b>น้ำหนัก:</b> <?=$dog['dog_weight']?> กก.</p>
                <p><b>เพศ:</b> <?=htmlspecialchars($dog['dog_gender'])?></p>
            </div>
        </div>
    </div>
</div>

<?php
showTable("ประวัติการรักษา",$treatments,
    ["วันที่","อาการ","การวินิจฉัย","การรักษา","ยา","สัตวแพทย์","วันนัดถัดไป"],
    ["treatment_date","symptoms","diagnosis","treatment","medication","doctor_name","next_appointment"]);

showTable("การนัดหมาย",$appointments,
    ["วันเวลา","เหตุผล","สถานะ"],
    ["appointment_date","description","status"]);

showTable("ประวัติการฉีดวัคซีน",$vaccinations,
    ["ชื่อวัคซีน","ประเภท","วันที่ฉีด","วันนัดถัดไป","สัตวแพทย์","หมายเหตุ"],
    ["vaccine_name","vaccine_type","vaccine_date","next_due_date","doctor_name","note"]);

showTable("ประวัติการถ่ายพยาธิ / ป้องกันเห็บหมัด",$dewormings,
    ["ชื่อยา","วันที่ให้","วันนัดถัดไป","หมายเหตุ"],
    ["drug_name","treatment_date","next_due_date","note"]);

showTable("ผลตรวจทางห้องปฏิบัติการ",$lab_results,
    ["วันที่ตรวจ","ผลเลือด","ผลปัสสาวะ","ไฟล์","หมายเหตุ"],
    ["test_date","blood_result","urine_result","file_path","note"], 'file_path');

showTable("การผ่าตัด / หัตถการ",$surgeries,
    ["วันที่","ประเภทการผ่าตัด","รายละเอียด","สัตวแพทย์","ผลลัพธ์","การดูแลหลังผ่าตัด"],
    ["surgery_date","surgery_type","description","doctor_name","outcome","notes"]);

showTable("ข้อมูลโภชนาการ",$nutrition,
    ["อาหาร","แพ้อาหาร","คำแนะนำ"],
    ["food","allergy","advice"]);

showTable("ประวัติการฝากเลี้ยง",$boarding,
    ["วันที่รับฝาก","วันที่รับกลับ","อาการ","การดูแล"],
    ["start_date","end_date","symptoms","care"]);

showTable("ไฟล์แนบ / เอกสาร",$attachments,
    ["ประเภท","ไฟล์","หมายเหตุ"],
    ["file_type","file_path","note"], 'file_path');
?>

<?php elseif ($dog_id): ?>
<div class="alert alert-danger">❌ ไม่พบข้อมูลสุนัข</div>
<?php endif; ?>

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script><script>
function exportPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('p', 'pt', 'a4');
    const pdfWidth = doc.internal.pageSize.getWidth();
    const pdfHeight = doc.internal.pageSize.getHeight();
    let margin = 20; // ขอบกระดาษ
    let yOffset = margin;

    const tables = document.querySelectorAll('.card'); // ทุก card คือส่วนที่จับ
    let promises = [];

    tables.forEach((table, index) => {
        promises.push(html2canvas(table).then(canvas => {
            const imgData = canvas.toDataURL('image/png');
            const imgProps = doc.getImageProperties(imgData);
            const imgWidth = pdfWidth - margin*2;
            const imgHeight = (imgProps.height * imgWidth) / imgProps.width;

            // ถ้าเกินหน้ากระดาษ ให้ขึ้นหน้าใหม่
            if (yOffset + imgHeight > pdfHeight - margin) {
                doc.addPage();
                yOffset = margin;
            }

            doc.addImage(imgData, 'PNG', margin, yOffset, imgWidth, imgHeight);
            yOffset += imgHeight + 10; // เว้นวรรคระหว่างตาราง
        }));
    });

    Promise.all(promises).then(() => {
        doc.save('dog_history.pdf');
    });
}
</script>


<style>
@media print { button { display: none; } }
</style>
</body>
</html>
