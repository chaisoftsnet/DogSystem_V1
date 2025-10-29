<?php
@ob_start();
@session_start();
include 'dbConnect.php'; // เชื่อมต่อฐานข้อมูล
require('fpdf/fpdf_thai.php');

class PDF extends FPDF_Thai {}

function exportPDF($tableName, $title, $headers, $widths, $sql) {
    global $objCon;
    $pdf = new PDF();
    $pdf->AddFont('AngsanaNew','','angsa.php');
    $pdf->AddPage('L','A3'); // แนวนอน A3
    $pdf->SetAutoPageBreak(true,20);

    // Title
    $pdf->SetFont('AngsanaNew','',20);
    $pdf->Cell(0,12,iconv('UTF-8','TIS-620',$title),0,1,'C');
    $pdf->Ln(3);

    // Header
    $pdf->SetFont('AngsanaNew','',16);
    foreach($headers as $i => $h){
        $pdf->Cell($widths[$i],12,iconv('UTF-8','TIS-620',$h),1,0,'C');
    }
    $pdf->Ln();

    // Data
    $pdf->SetFont('AngsanaNew','',14);
    $result = mysqli_query($objCon, $sql);
    $no = 0;
    while($row = mysqli_fetch_assoc($result)){
        $no++;
        foreach($headers as $i => $h){
            $field = $fields[$i]; // ใช้ field ตามลำดับ column
            $text = isset($row[$field]) ? $row[$field] : '';
            $text = mb_substr($text,0,100); // ตัดข้อความยาว
            $pdf->Cell($widths[$i],10,iconv('UTF-8','TIS-620',$text),1,0,'L');
        }
        $pdf->Ln();
    }

    // Footer
    $pdf->SetY(-20);
    $pdf->SetFont('AngsanaNew','',14);
    $pdf->Cell(0,10,iconv('UTF-8','TIS-620','หน้าที่ '.$pdf->PageNo()),0,0,'C');

    $pdf->Output();
}

// ---------------- ตัวอย่างเรียกใช้งาน ----------------
// สำหรับ table dogs
$headers = ['ลำดับ','Dog ID','ชื่อ','สายพันธุ์','อายุ','น้ำหนัก','เพศ','ประวัติการรักษา','RFID Tag','วันที่ทำข้อมูล'];
$widths  = [15,20,50,50,20,25,20,120,35,45];
$fields= ['dog_id','dog_name','dog_breed','dog_age','dog_weight','dog_gender','dog_medical_history','created_at'];
$sql = "SELECT * FROM dogs ORDER BY dog_id ASC";
exportPDF('dogs', 'รายงานทะเบียนสุนัขทั้งหมด', $headers, $widths, $sql);
?>
