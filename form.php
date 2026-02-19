<?php
include("dbConnect.php");
include("_config/modules.php");
include("_core/form_template.php");

$module = $_GET['module'] ?? '';
$action = $_GET['action'] ?? 'add';
$dog_id = intval($_GET['dog_id'] ?? 0);
$id     = intval($_GET['id'] ?? 0);

if(!$module || !isset($MODULES[$module])){
    echo '<div class="alert alert-danger">Module ไม่ถูกต้อง</div>';
    exit;
}

$config = $MODULES[$module];
$data = [];

if($action==='edit' && $id>0){
    $stmt = $objCon->prepare(
        "SELECT * FROM {$config['table']} WHERE {$config['pk']}=?"
    );
    $stmt->bind_param("i",$id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc() ?? [];
}

renderForm([
    'module'=>$module,
    'action'=>$action,
    'dog_id'=>$dog_id,
    'id'=>$id,
    'config'=>$config,
    'data'=>$data
]);
