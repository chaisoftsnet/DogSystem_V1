<meta name="viewport" content="width=device-width, initial-scale=1">
<title>ระบบบริหารจัดการคลินิกรักษาสัตว์</title>
<link rel="stylesheet" href="css/main.css">
<link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
<link href="http://asset.opsmoac.go.th/style/FontThaisarabun.css" type="text/css" rel="stylesheet">
</head>
<body>
<!-- Fancybox CSS รูปภาพ-->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.css" />
<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.umd.js"></script>
<script type="text/javascript" src="js/jquery.min.js"></script>
<!-- Offline JS -->
<script type="text/javascript" src="js/LocalStorage_Dog.js"></script>
<script src="clinic_dog_loader.js"></script>

<?@ob_start();?>
<?session_start();?>
<?php include 'dbConnect.php'; ?>
<?php include 'VarArray.php'; ?>
<?php include 'FUNCTION.PHP'; ?>
<?
	$ST=$_REQUEST["ST"];
	$OnOff=$_REQUEST["OnOff"];
	$menubar=$_REQUEST["menubar"];
	$submenu=$_REQUEST["submenu"];
	$GROUP_ID=$_REQUEST["GROUP_ID"];	
	$CLASS_ID=$_REQUEST["CLASS_ID"];		
	

$Mode = $_REQUEST["Mode"];
$menubar = $_REQUEST["menubar"];
$submenu = $_REQUEST["submenu"];
$clinic_id = $_SESSION["clinic_id"];
$role = $_SESSION["role"];

$Mode = $_REQUEST["Mode"];
$menubar = $_REQUEST["menubar"];
$submenu = $_REQUEST["submenu"];
$clinic_id = $_SESSION["clinic_id"];
$role = $_SESSION["role"];

// เมนูหลัก
$aMenubar = Array(
  "", 
  "ข้อมูลสัตว์", 
  "การรักษาพยาบาล", 
  "การนัดหมาย", 
  "วัคซีน", 
  "ถ่ายพยาธิ", 
  "Lab", 
  "ผ่าตัด", 
  "โภชนาการ", 
  "ฝากเลี้ยง", 
  "ไฟล์แนบ", 
  "รายงาน", 
  "ออกจากระบบ"
);

// ไฟล์ที่เมนูชี้ไป
$aUrl = Array(
  "",
  "dogs_manage.php",
  "treatment_manage.php",
  "appointment_manage.php",
  "vaccine_manage.php",
  "deworming_manage.php",
  "LabResults_manage.php",
  "Surgeries_manage.php",
  "Nutrition_manage.php",
  "boarding_manage.php",
  "attachments_manage.php",
  "reportAll.php",
  "logout.php"
);
?>
	
<div class="panel panel-primary" >
<div class="panel-heading"><h3 class="panel-title"><?=$System?></div>
<div class="panel-body">


 <ul class="nav nav-tabs"> 
<?For ($i=0;$i<=count($aMenubar)-1;$i++){?> 
<?If($menubar==$i){?> 
		<li class='active'><a  href="<?=$aUrl[$i]?>?GROUP_ID=1&CLASS_ID=1&menubar=<?=$i?>&Status=<?=$i?>&OnOff=<?=$OnOff?>"><b><?=$aMenubar[$i]?></b></a></li>
	<?}Else{?>
		<li><a  href="<?=$aUrl[$i]?>?GROUP_ID=1&CLASS_ID=1&menubar=<?=$i?>&Status=<?=$i?>&OnOff=<?=$OnOff?>"><b><?=$aMenubar[$i]?></b></a></li>
<?}?>
<?}?>
</ul><br />
<br />
