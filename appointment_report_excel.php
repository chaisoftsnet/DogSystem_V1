<?php
@session_start();
require_once('dbconnect.php');

// โหลด PHPSpreadsheet
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// รับค่าปีและเดือน
$year = $_GET['year'] ?? date('Y');
$month = $_GET['month'] ?? '';

$condition = "YEAR(appointment_date)='$year'";
if ($month != '') $condition .= " AND MONTH(appointment_date)='$month'";

$sql = "SELECT a.*, d.dog_name, c.clinic_name
        FROM appointments a
        LEFT JOIN dogs d ON a.dog_id = d.dog_id
        LEFT JOIN clinics c ON a.clinic_id = c.clinic_id
        WHERE $condition
        ORDER BY appointment_date DESC";
$result = mysqli_query($objCon, $sql);

$months = ["","มกราคม","กุมภาพันธ์","มีนาคม","เมษายน","พฤษภาคม","มิถุนายน",
           "กรกฎาคม","สิงหาคม","กันยายน","ตุลาคม","พฤศจิกายน","ธันวาคม"];
$monthName = $month ? $months[intval($month)] : "ทั้งหมด";

// 🔹 สร้าง Sheet ใหม่
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Appointment Report');

// 🔹 หัวรายงาน
$sheet->setCellValue('A1', 'รายงานการนัดหมายประจำเดือน '.$monthName.' '.$year);
$sheet->mergeCells('A1:F1');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
$sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

// 🔹 หัวตาราง
$headers = ['#','ชื่อสุนัข','คลินิก','วันและเวลานัด','รายละเอียด','สถานะ'];
$col = 'A';
foreach ($headers as $h) {
    $sheet->setCellValue($col.'3', $h);
    $sheet->getStyle($col.'3')->getFont()->setBold(true);
    $sheet->getStyle($col.'3')->getAlignment()->setHorizontal('center');
    $col++;
}

// 🔹 เติมข้อมูล
$row = 4;
$i = 1;
while($r = mysqli_fetch_assoc($result)){
  $sheet->setCellValue("A{$row}", $i);
  $sheet->setCellValue("B{$row}", $r['dog_name']);
  $sheet->setCellValue("C{$row}", $r['clinic_name']);
  $sheet->setCellValue("D{$row}", date("d/m/Y H:i", strtotime($r['appointment_date'])));
  $sheet->setCellValue("E{$row}", $r['description']);
  $sheet->setCellValue("F{$row}", $r['status']);
  $row++; $i++;
}

// 🔹 จัดความกว้างอัตโนมัติ
foreach (range('A','F') as $columnID) {
  $sheet->getColumnDimension($columnID)->setAutoSize(true);
}

// 🔹 กรอบเส้นตาราง
$sheet->getStyle('A3:F'.($row-1))
      ->getBorders()->getAllBorders()
      ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

// 🔹 Export ออกเป็นไฟล์ Excel
$filename = "Appointment_Report_{$year}_{$month}.xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$filename\"");
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>
