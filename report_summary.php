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

// ---------------- Title ----------------
$pdf->Cell(0,12,iconv('UTF-8','TIS-620','?? รายงานภาพรวมคลินิกรักษาสัตว์'),0,1,'C');
$pdf->Ln(10);

$pdf->Output();
?>
