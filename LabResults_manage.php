<meta name="viewport" content="width=device-width, initial-scale=1">
<title>🔬 ผลตรวจทางห้องปฏิบัติการ</title>
<link rel="stylesheet" href="css/main.css">
<link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>
<!-- Fancybox CSS รูปภาพ-->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.css" />
<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.umd.js"></script>
<script type="text/javascript" src="js/jquery.min.js"></script>
<!-- Offline JS -->
<script type="text/javascript" src="js/LocalStorage_Dog.js"></script>
<script src="clinic_dog_loader.js"></script>
<?php
ob_start();
session_start();

include 'navbar.php';
include 'dbconnect.php';
include 'function.php';

// Theme/Dark Mode
$Mode = $_POST['Mode'] ?? $_GET['Mode'] ?? "DEFAULT";

// CRUD Action Mode
$ActionMode = $_POST['ActionMode'] ?? $_GET['ActionMode'] ?? "SAVE";

// lab_id สำหรับแก้ไข/ลบ
$lab_id = $_POST['lab_id'] ?? $_GET['lab_id'] ?? "";

// ตัวแปรฟอร์ม
$dog_id = $clinic_id = $test_date = $test_name = "";
$blood_result = $urine_result = $note = "";
$file_path = "";
   include 'Offline.php';
// ✅ กรณีแก้ไข ดึงข้อมูลมาแสดง
if ($ActionMode == "EDIT" && $lab_id != "") {
    $sql = "SELECT * FROM lab_results WHERE lab_id='$lab_id'";
    $qry = mysqli_query($objCon, $sql);
    if ($row = mysqli_fetch_assoc($qry)) {
        $dog_id = $row['dog_id'];
        $clinic_id = $row['clinic_id'];
        $test_date = $row['test_date'];
        $test_name = $row['test_name'];
        $blood_result = $row['blood_result'];
        $urine_result = $row['urine_result'];
        $note = $row['note'];
        $file_path = $row['file_path'];
    }
}

// ✅ กดบันทึกข้อมูล
if (isset($_POST['save'])) {
    $lab_id = $_POST['lab_id'];
    $dog_id = $_POST['dog_id'];
    $clinic_id = $_POST['clinic_id'];
    $test_date = $_POST['test_date'];
    $test_name = $_POST['test_name'];
    $blood_result = $_POST['blood_result'];
    $urine_result = $_POST['urine_result'];
    $note = $_POST['note'];

    // Upload ไฟล์
    if (!empty($_FILES['file_upload']['name'])) {
        $target_dir = "uploads/lab/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $target_file = $target_dir . time() . "_" . basename($_FILES["file_upload"]["name"]);
        move_uploaded_file($_FILES["file_upload"]["tmp_name"], $target_file);
        $file_path = $target_file;
    }

    if ($lab_id=='') {
        $sql = "INSERT INTO lab_results(dog_id, clinic_id, test_date, test_name, blood_result, urine_result, note, file_path) 
                VALUES ('$dog_id','$clinic_id','$test_date','$test_name','$blood_result','$urine_result','$note','$file_path')";
    } else {
        $sql = "UPDATE lab_results SET 
                dog_id='$dog_id',
                clinic_id='$clinic_id',
                test_date='$test_date',
                test_name='$test_name',
                blood_result='$blood_result',
                urine_result='$urine_result',
                note='$note'" . 
                ($file_path != "" ? ", file_path='$file_path'" : "") . "
                WHERE lab_id='$lab_id'";
    }
    mysqli_query($objCon, $sql);
    header("Location: LabResults_manage.php?Mode=$Mode");
    exit();
}

// ✅ ลบข้อมูล
if ($ActionMode == "DEL" && $lab_id != "") {
    mysqli_query($objCon, "DELETE FROM lab_results WHERE lab_id='$lab_id'");
    header("Location: LabResults_manage.php?Mode=$Mode");
    exit();
}
?>

<div class="container mt-4">
    <div class="card p-4 mb-4 shadow-sm">
        <h3 class="text-center">
            🔬 ผลการตรวจทางห้องปฏิบัติการ
            <sup><font color="red"><?php if($lab_id!='') echo "(lab_id=$lab_id)"; ?></font></sup>
        </h3>

        <form method="POST" action="LabResults_manage.php" enctype="multipart/form-data" class="card p-4 shadow-sm">
            <div class="row g-3">
                <?php if ($_SESSION['role'] == 3) { ?>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">เลือกคลินิก</label>
                        <select name="clinic_id" id="clinic_id" class="form-control">
                            <option value="">-- เลือกคลินิก --</option>
                            <?php opt_clinic($clinic_id, $objCon); ?>
                        </select>
                    </div>
                <?php } ?>

                <div class="col-md-4">
                    <label class="form-label fw-bold">เลือกสุนัข</label>
                    <select name="dog_id" id="dog_id" class="form-control" required>
                        <option value="">-- เลือกสุนัข --</option>
                        <?php
                        if($clinic_id != ""){
                            $dogs = mysqli_query($objCon, "SELECT * FROM dogs WHERE clinic_id='$clinic_id'");
                            while ($dog = mysqli_fetch_assoc($dogs)) { ?>
                                <option value="<?= $dog['dog_id'] ?>" <?= $dog_id == $dog['dog_id'] ? 'selected' : '' ?>>
                                    <?= $dog['dog_name'] ?>
                                </option>
                        <?php }} ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">วันที่ตรวจ</label>
                    <input type="date" name="test_date" class="form-control" value="<?= $test_date ?>" required>
                </div>
            </div>

            <div class="mb-3 mt-3">
                <label class="form-label fw-bold">ชื่อการตรวจ</label>
                <input type="text" name="test_name" class="form-control" value="<?= $test_name ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">ผลเลือด</label>
                <textarea name="blood_result" class="form-control" rows="2"><?= $blood_result ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">ผลปัสสาวะ</label>
                <textarea name="urine_result" class="form-control" rows="2"><?= $urine_result ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">อัปโหลดไฟล์แล็บ / X-ray / Ultrasound</label>
                <input type="file" name="file_upload" class="form-control">
                <?php if($file_path != "") { ?>
                    <a href="<?= $file_path ?>" target="_blank">📂 ดูไฟล์เดิม</a>
                <?php } ?>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">หมายเหตุ</label>
                <textarea name="note" class="form-control" rows="2"><?= $note ?></textarea>
            </div>

            <div class="text-center mt-4">
                <input type="hidden" name="ActionMode" value="<?= $ActionMode ?>"><br>
                <input type="hidden" name="Mode" value="<?= $Mode ?>">                
                <input type="hidden" name="lab_id" value="<?= $lab_id ?>">
                <button type="submit" name="save" class="btn btn-primary px-4"><?= $lab_id ? "อัปเดตข้อมูล" : "บันทึกข้อมูล" ?></button>
            </div>
        </form>
    </div>

  <!-- ตาราง -->
  <div class="card p-4 mb-4 shadow-sm">
    <div class="mb-3 text-center">    
    <a href="PDF_Report.php?table=lab_results" class="btn btn-success" target="_blank"><i class="fa fa-file-pdf-o"></i> Export PDF</a>    
    <button class="btn btn-primary" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
    <button class="btn btn-warning" onclick="likeReport()"><i class="fa fa-thumbs-up"></i> Like</button>
    </div>             
    <table class="table table-bordered table-responsive-sm" id="DataTable">
    <thead class="thead-light">
            <thead>
                <tr>
                    <th>วันที่ตรวจ</th>
                    <th>ชื่อการตรวจ</th>
                    <th>สุนัข</th>
                    <th>ไฟล์</th>
                    <th>หมายเหตุ</th>
                    <th>แก้ไข</th>
                    <th>ลบ</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($_SESSION['role'] <= 2) {
                    $strSQL = "SELECT a.*, d.dog_name FROM lab_results a 
                               LEFT JOIN dogs d ON a.dog_id = d.dog_id 
                               WHERE a.clinic_id = '$clinic_id' 
                               ORDER BY a.test_date DESC";           
                } else {
                    $strSQL = "SELECT a.*, d.dog_name FROM lab_results a 
                               LEFT JOIN dogs d ON a.dog_id = d.dog_id 
                               ORDER BY a.test_date DESC";
                }
                $query = mysqli_query($objCon, $strSQL);      
                while($row=mysqli_fetch_assoc($query)) { ?>
                    <tr>
                        <td><?= $row['test_date'] ?></td>
                        <td><?= $row['test_name'] ?></td>
                        <td><?= $row['dog_name'] ?></td>
                        <td>
                            <?php if($row['file_path']){ ?>
                                <a href="<?= $row['file_path'] ?>" target="_blank">📂 ไฟล์</a>
                            <?php } ?>
                        </td>
                        <td><?= $row['note'] ?></td>
                        <td align="center"><a href="LabResults_manage.php?ActionMode=EDIT&lab_id=<?= $row['lab_id'] ?>&Mode=<?=$Mode?>" class="btn btn-sm btn-info"><i class="fa fa-edit"></i></a></td>
                        <td align="center"><a href="LabResults_manage.php?ActionMode=DEL&lab_id=<?= $row['lab_id'] ?>&Mode=<?=$Mode?>" class="btn btn-sm btn-danger" onclick="return confirm('คุณแน่ใจว่าจะลบข้อมูลนี้?')"><i class="fa fa-trash"></i></a></td>                        
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<script type="text/javascript" src="js/plugins/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="js/plugins/dataTables.bootstrap.min.js"></script>
<script>
$('#DataTable').DataTable({
    "pageLength": 10,
    "order": [[0,'desc']]
});
</script>
</body>
</html>
