<?php
include("dbConnect.php");
header('Content-Type: application/json; charset=utf-8');

/* ===================== HELPER ===================== */
function response($ok,$msg,$extra=[]){
    echo json_encode(array_merge([
        'status'=>$ok?'success':'error',
        'message'=>$msg
    ],$extra),JSON_UNESCAPED_UNICODE);
    exit;
}

/* ===================== INPUT ===================== */
$module = $_POST['module'] ?? '';
$action = $_POST['action'] ?? '';
$id     = intval($_POST['id'] ?? 0);
$dog_id = intval($_POST['dog_id'] ?? 0);
$clinic_id = intval($_POST['clinic_id'] ?? 1);
$user_id   = intval($_POST['user_id'] ?? 1);

if(!$module || !$action){
    response(false,'ข้อมูลไม่ครบ');
}

/* ===================== MODULE CONFIG ===================== */
$MODULES = [

    'treatment' => [
        'table' => 'treatments',
        'pk'    => 'treatment_id',
        'auto'  => ['dog_id','clinic_id','user_id'],
        'fields'=> [
            'treatment_date','symptoms','diagnosis','treatment',
            'medication','doctor_name','next_appointment','note'
        ],
        'file'  => 'file_path'
    ],

    'appointment' => [
        'table' => 'appointments',
        'pk'    => 'appointment_id',
        'auto'  => ['dog_id','clinic_id'],
        'fields'=> [
            'appointment_date','description','status'
        ]
    ],

    'vaccination' => [
        'table' => 'vaccinations',
        'pk'    => 'vaccine_id',
        'auto'  => ['dog_id','clinic_id'],
        'fields'=> [
            'vaccine_name','vaccine_type','vaccine_date',
            'next_due_date','doctor_name','note'
        ]
    ],

    'lab' => [
        'table' => 'lab_results',
        'pk'    => 'lab_id',
        'auto'  => ['dog_id','clinic_id'],
        'fields'=> [
            'test_name','test_date','blood_result',
            'urine_result','note'
        ],
        'file'  => 'file_path'
    ],

       'deworming' => [
        'table' => 'dewormings',
        'pk'    => 'deworming_id',
        'required' => ['drug_name','treatment_date'],
        'auto'  => ['dog_id','clinic_id'],
        'fields'=> [
            'drug_name','treatment_date','next_due_date','note'
        ]
    ],

    'surgery' => [
        'table' => 'surgeries',
        'pk'    => 'surgery_id',
        'auto'  => ['dog_id','clinic_id'],
        'fields'=> [
            'surgery_date','surgery_type','description',
            'doctor_name','outcome','notes'
        ],
        'file'  => 'file_path'
    ],

    'nutrition' => [
        'table' => 'nutrition',
        'pk'    => 'nutrition_id',
        'required' => [],
        'auto'  => ['dog_id','clinic_id'],
        'fields'=> [
            'food','allergy','advice'
        ]
    ],

    'boarding' => [
        'table' => 'boarding',
        'pk'    => 'boarding_id',
        'required' => ['start_date','end_date'],
        'auto'  => ['dog_id','clinic_id'],
        'fields'=> [
            'start_date','end_date','symptoms','care'
        ]
    ],

    'attachment' => [
        'table' => 'attachments',
        'pk'    => 'attachment_id',
        'auto'  => ['dog_id','clinic_id'],
        'fields'=> [
            'file_type','note'
        ],
        'file'  => 'file_path'
    ]
];

if(!isset($MODULES[$module])){
    response(false,'Module ไม่ถูกต้อง');
}

$cfg = $MODULES[$module];

/* ===================== FILE UPLOAD ===================== */
$uploadedFilePath = '';

if(isset($cfg['file']) && isset($_FILES[$cfg['file']]) && $_FILES[$cfg['file']]['error']===UPLOAD_ERR_OK){

    $uploadDir = "uploads/{$module}/";
    if(!is_dir($uploadDir)){
        mkdir($uploadDir,0777,true);
    }

    $ext = strtolower(pathinfo($_FILES[$cfg['file']]['name'],PATHINFO_EXTENSION));
    $allow = ['jpg','jpeg','png','pdf'];

    if(!in_array($ext,$allow)){
        response(false,'ชนิดไฟล์ไม่ถูกต้อง');
    }

    $filename = $module.'_'.time().'_'.rand(1000,9999).'.'.$ext;
    $dest = $uploadDir.$filename;

    if(!move_uploaded_file($_FILES[$cfg['file']]['tmp_name'],$dest)){
        response(false,'อัปโหลดไฟล์ไม่สำเร็จ');
    }

    $uploadedFilePath = $dest;
}

/* ===================== ADD ===================== */
if($action==='add'){

    $cols = [];
    $vals = [];

    foreach($cfg['auto'] as $a){
        $cols[] = $a;
        $vals[] = intval($$a);
    }

    foreach($cfg['fields'] as $f){
        if(isset($_POST[$f])){
            $cols[] = $f;
            $vals[] = "'".mysqli_real_escape_string($objCon,$_POST[$f])."'";
        }
    }

    if($uploadedFilePath && isset($cfg['file'])){
        $cols[] = $cfg['file'];
        $vals[] = "'$uploadedFilePath'";
    }

    $sql = "INSERT INTO {$cfg['table']} (".implode(',',$cols).")
            VALUES (".implode(',',$vals).")";

    $ok = mysqli_query($objCon,$sql);
    //response($ok,'เพิ่มข้อมูลเรียบร้อย');
    header("Location: dog_profile_new.php?dog_id=$dog_id");    
}

/* ===================== EDIT ===================== */
if($action==='edit' && $id>0){

    $set = [];

    foreach($cfg['fields'] as $f){
        if(isset($_POST[$f])){
            $val = mysqli_real_escape_string($objCon,$_POST[$f]);
            $set[] = "$f='$val'";
        }
    }

    if($uploadedFilePath && isset($cfg['file'])){
        $set[] = "{$cfg['file']}='$uploadedFilePath'";
    }

    if(empty($set)){
        response(false,'ไม่มีข้อมูลสำหรับแก้ไข');
    }

    $sql = "UPDATE {$cfg['table']} SET ".implode(',',$set)."
            WHERE {$cfg['pk']}=$id";    
    $ok = mysqli_query($objCon,$sql);
    //response(true,'แก้ไขข้อมูลเรียบร้อย');
    header("Location: dog_profile_new.php?dog_id=$dog_id");    
}

/* ===================== DELETE ===================== */
if($action==='delete' && $id>0){

    $sql = "DELETE FROM {$cfg['table']} WHERE {$cfg['pk']}=$id";
    $ok = mysqli_query($objCon,$sql);
    response($ok,'ลบข้อมูลเรียบร้อย');
}

response(false,'Action ไม่ถูกต้อง');
