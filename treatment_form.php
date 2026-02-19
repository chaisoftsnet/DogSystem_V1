<?php
include("dbConnect.php");

$action = $_GET['action'] ?? 'add';
$dog_id = intval($_GET['dog_id'] ?? 0);
$id     = intval($_GET['id'] ?? 0);

$data = [];

if ($action === 'edit') {
    $rs = mysqli_query($objCon, "SELECT * FROM treatments WHERE treatment_id=$id");
    $data = mysqli_fetch_assoc($rs);
}

$updateFile = 'treatment_update.php';
$moduleName = 'treatment';

$fields = [
    ['name'=>'treatment_date','label'=>'วันที่รักษา','type'=>'date','col'=>4],
    ['name'=>'symptoms','label'=>'อาการ','type'=>'text','col'=>8],
    ['name'=>'diagnosis','label'=>'การวินิจฉัย','type'=>'textarea'],
    ['name'=>'treatment','label'=>'การรักษา','type'=>'textarea'],
    ['name'=>'medication','label'=>'ยา','type'=>'text','col'=>6],
    ['name'=>'doctor_name','label'=>'สัตวแพทย์','type'=>'text','col'=>6],
    ['name'=>'next_appointment','label'=>'วันนัดถัดไป','type'=>'date','col'=>4]
];

require "_core/form_template.php";
