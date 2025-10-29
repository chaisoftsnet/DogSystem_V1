<?
$url="http://183.88.236.186:2454/dogsyswindows/";
date_default_timezone_set('Asia/Bangkok');
date('H:i:s');  
//include 'CHK_OS.PHP';

$Copy_right=$_SESSION["strCopy_right"];
$Department=$_SESSION["strDepartment"];
$Address=$_SESSION["strAddress"];
$CpUser=$_SESSION["strCpUser"];

$aSecurity=Array('บุคคลทั่วไป','ระดับเจ้าหน้าที่','ระดับผู้บริหาร','ระดับผู้ดูแลระบบ');
$aSTYLE=Array('','สีดำ','สีฟ้า','สีแดง');
$aBoostrap=Array("","label label-primary pull-right","label label-warning pull-right","label label-success pull-right","label label-info pull-right","label label-default pull-right");
$System_='(Clinic Dog System)';
$System='ระบบบริหารจัดการคลินิกรักษาสัตว์:';
$fMenu=Array('','นำออก-วัสดุคงคลัง(Stock Out)','นำเข้า-วัสดุคงคลัง(Stock In)','ยอดคงเหลือ-วัสดุคงคลัง(Stock Balance)','บัญชีคุมรายการวัสดุคงคลังคงเหลือ','ใบแจ้งราคาสถิติ/ราคาตลาด','บันทึกใบคำขอเบิกพัสดุ');

$aStatus=Array('No','Yes');
$d=Array(0,31,28,31,30,31,30,31,31,30,31,30,31);
$ys=date('Y');
$aMonth=Array('ประจำปี','มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน','กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม');
$bMonth=Array('เดือน','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.');

$ms_thChart=Array('แท่ง','วงกลม','โดนัท');
$ms_swfChart=Array('MsColumn3D.swf','MsLine.swf','MsArea.swf');

$s_thChart=Array('แท่ง 3D','วงกลม 3D','วงกลม 2D','โนนัท 3D','เส้น','พืนที่');
$s_swfChart=Array('Column3D.swf','Pie3D.swf','Pie2D.swf','Doughnut3D.swf','Line.swf','Area2D.swf');
$aLIFETIME=Array('รายการนี้ ห้ามยืม!!','รายการนี้คุณสามารถยืมได้');
$bLIFETIME=Array('คลิ๊กเพื่อขอยืม','รายการนี้คุณได้ยื่นสิทธิ์การขอยืมแล้ว');

$abgColor=Array("#336699","#818181","#4791C5","#818181");
$bbgColor=Array("#336699","336699","#4791C5","#5D5D5D");
$cbgColor=Array("#336699","#e8e8e8","#FFFFFF","#ECECEC");//สีพื้นหน้าจอ
$dbgColor=Array("#336699","#f5f5f5","#ECECEC","#f5f5f5");//สี table เวลา Mouse Over
$aColor=Array('','#000000','#B21319','#336699','#B21319','Black','#C4970F','Maroon');
$bColor=Array('','#000000','#B21319','#336699','#0000ff','Maroon','#009900','#C4970F');
$aSTRET=Array('','ทั้งแปลง','บางส่วน');
$delColor=Array('#336699','#000000','#B21319','#999900','#0000ff','#ffffea','#009900','#660099');

$aColor=Array('#F7464A','#46BFBD','#FDB45C','#336699','#0000ff','Maroon','#009900','#C4970F');
$bColor=Array('#FF5A5E','#5AD3D1','#FFC870','#999900','#0000ff','#ffffea','#009900','#660099');

$ms_thChart=Array("แท่ง","วงกลม","โดนัท");
$ms_swfChart=Array("MsColumn3D.swf","MsLine.swf","MsArea.swf");

$s_thChart=Array("แท่ง 3D","วงกลม 3D","วงกลม 2D","โนนัท 3D","เส้น","พืนที่");
$s_swfChart=Array("Column3D.swf","Pie3D.swf","Pie2D.swf","Doughnut3D.swf","Line.swf","Area2D.swf");
$aChart=Array("Bar","Donut");
$sbColor=Array("#F0F0F8","");
?>