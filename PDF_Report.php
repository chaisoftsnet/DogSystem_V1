<?php
@ob_start();
@session_start();
include 'dbConnect.php';
require('fpdf/fpdf_thai.php');

class PDF extends FPDF_Thai {}

$pdf = new PDF();
$pdf->AddFont('AngsanaNew','','angsa.php');
$pdf->SetFont('AngsanaNew','',16);
$pdf->AddPage('L','A3');

// ---------------- CONFIG ----------------
$config = [

  "user" => [
    "title" => "รายงานผู้ใช้งาน",
    "sql"   => "SELECT id, username, fullname, email, clinic_id, role, created_at FROM user",
    "header"=> ['ID','USERNAME','FIRSTNAME','EMAIL','Clinic ID','Role','CREATE_DATE'],
    "width" => [15,80,160,60,25,15,40],
    "fields"=> ['id','username','fullname','email','clinic_id','role','created_at']
  ],

  "dogs" => [
    "title" => "รายงานข้อมูลสุนัขส่งรักษา",
    "sql"   => "SELECT dog_id,dog_name,dog_breed,dog_age,dog_weight,dog_gender,dog_medical_history,created_at FROM dogs",
    "header"=> ['No','ID','ชื่อ','สายพันธุ์','อายุ','น้ำหนัก','เพศ','ประวัติ','วันที่บันทึก'],
    "width" => [10,15,60,40,15,20,20,180,40],
    "fields"=> ['dog_id','dog_name','dog_breed','dog_age','dog_weight','dog_gender','dog_medical_history','created_at']
  ],

  "clinics" => [
    "title" => "รายงานคลินิก",
    "sql"   => "SELECT clinic_id, clinic_name, address, phone, email, owner_name, created_at FROM clinics",
    "header"=> ['ID','ชื่อคลินิก','ที่อยู่','โทร','อีเมล','เจ้าของ','วันที่'],
    "width" => [15,40,60,30,40,40,40],
    "fields"=> ['clinic_id','clinic_name','address','phone','email','owner_name','created_at']
  ],

  "treatments" => [
    "title" => "รายงานการรักษาพยาบาลสัตว์",
    "sql"   => "SELECT treatment_id,dog_id,treatment_date,symptoms,diagnosis,treatment,medication,doctor_name,next_appointment FROM treatments",
    "header"=> ['No','ID','Dog ID','วันที่รักษา','อาการ','การวินิจฉัย','การรักษา','ยา','สัตวแพทย์','นัดครั้งถัดไป'],
    "width" => [10,10,20,30,60,70,70,60,30,40],
    "fields"=> ['treatment_id','dog_id','treatment_date','symptoms','diagnosis','treatment','medication','doctor_name','next_appointment']
  ],

  "appointments" => [
    "title" => "รายงานการนัดหมาย",
    "sql"   => "SELECT appointment_id,dog_id,appointment_date,description,status FROM appointments",
    "header"=> ['No','ID','Dog ID','วัน-เวลา','รายละเอียด','สถานะ'],
    "width" => [10,10,20,40,290,30],
    "fields"=> ['appointment_id','dog_id','appointment_date','description','status']
  ],

  "vaccinations" => [
    "title" => "รายงานการฉีดวัคซีน",
    "sql"   => "SELECT vaccination_id,dog_id,vaccine_name,vaccine_type,vaccine_date,next_due_date,doctor_name,note FROM vaccinations",
    "header"=> ['No','ID','Dog ID','ชื่อวัคซีน','ประเภท','วันที่','ครั้งถัดไป','สัตวแพทย์','หมายเหตุ'],
    "width" => [10,10,60,60,30,30,30,70,100],
    "fields"=> ['vaccination_id','dog_id','vaccine_name','vaccine_type','vaccine_date','next_due_date','doctor_name','note']
  ],

  "dewormings" => [
    "title" => "รายงานการถ่ายพยาธิ/กันเห็บหมัด",
    "sql"   => "SELECT deworming_id,dog_id,drug_name,treatment_date,next_due_date,note FROM dewormings",
    "header"=> ['No','ID','Dog ID','ชื่อยา','วันที่ให้ยา','ครั้งถัดไป','หมายเหตุ'],
    "width" => [10,10,20,90,30,30,210],
    "fields"=> ['deworming_id','dog_id','drug_name','treatment_date','next_due_date','note']
  ],

  "lab_results" => [
    "title" => "รายงานผลแล็บ",
    "sql"   => "SELECT lab_id,dog_id,test_date,blood_result,urine_result,file_path,note FROM lab_results",
    "header"=> ['No','ID','Dog ID','วันที่ตรวจ','ผลเลือด','ผลปัสสาวะ','หมายเหตุ'],
    "width" => [10,10,20,30,110,110,110],
    "fields"=> ['lab_id','dog_id','test_date','blood_result','urine_result','note']
  ],

  "surgeries" => [
    "title" => "รายงานการผ่าตัด/หัตถการ",
    "sql"   => "SELECT surgery_id,dog_id,surgery_date,surgery_type,description,doctor_name,outcome FROM surgeries",
    "header"=> ['No','ID','Dog ID','วันที่','ประเภท','รายละเอียด','สัตวแพทย์','ผลลัพธ์'],
    "width" => [10,10,20,30,100,90,40,100],
    "fields"=> ['surgery_id','dog_id','surgery_date','surgery_type','description','doctor_name','outcome']
  ],

  "nutrition" => [
    "title" => "รายงานโภชนาการ",
    "sql"   => "SELECT nutrition_id,dog_id,food,allergy,advice FROM nutrition",
    "header"=> ['No','ID','Dog ID','อาหาร','แพ้','คำแนะนำ'],
    "width" => [10,10,20,100,130,130],
    "fields"=> ['nutrition_id','dog_id','food','allergy','advice']
  ],

  "boarding" => [
    "title" => "รายงานการฝากเลี้ยง",
    "sql"   => "SELECT boarding_id,dog_id,start_date,end_date,symptoms,care FROM boarding",
    "header"=> ['No','ID','Dog ID','รับฝาก','รับกลับ','อาการ','การดูแล'],
    "width" => [10,10,20,30,30,150,150],
    "fields"=> ['boarding_id','dog_id','start_date','end_date','symptoms','care']
  ],

  "attachments" => [
    "title" => "รายงานไฟล์แนบ/เอกสาร",
    "sql"   => "SELECT attachment_id,dog_id,file_type,file_path,note,uploaded_at FROM attachments",
    "header"=> ['No','ID','Dog ID','ประเภท','ไฟล์','หมายเหตุ','อัปโหลดเมื่อ'],
    "width" => [10,10,20,60,80,170,50],
    "fields"=> ['attachment_id','dog_id','file_type','file_path','note','uploaded_at']
  ],

];

// ---------------- MAIN ----------------
$table = $_GET['table'] ?? 'dogs';
if(!isset($config[$table])) die("ไม่พบการตั้งค่า table: $table");

$conf = $config[$table];

// ---------------- Title ----------------
$pdf->SetFont('AngsanaNew','',20);
$pdf->Cell(0,12,iconv('TIS-620','TIS-620',$conf['title']),0,1,'C');
//$pdf->Cell(0,12,iconv('TIS-620','TIS-620',$conf['sql']),0,1,'C');
$pdf->Ln(3);

// ---------------- Header ----------------
$pdf->SetFont('AngsanaNew','',16);
$pdf->SetWidths($conf['width']); // กำหนด width ให้ Row1()
$pdf->Row1(array_map(function($col){
    return iconv('TIS-620','TIS-620',$col);
}, $conf['header']));
// ---------------- Data ----------------
$pdf->SetFont('AngsanaNew','',14);

$res = mysqli_query($objCon, $conf['sql']);
$No = 0;
while($row = mysqli_fetch_assoc($res)){
    $No++;
    $dataRow = [];
    
    // เพิ่มเลขลำดับ No
    $dataRow[] = iconv('UTF-8','TIS-620',$No);
    
    // ข้อมูลแต่ละ field
    foreach($conf['fields'] as $f){
        $val = $row[$f] ?? '';
        $dataRow[] = iconv('UTF-8','TIS-620',$val);
    }    
    $pdf->Row1($dataRow);
}
$pdf->Output();
?>
