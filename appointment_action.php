<?php
@session_start();
require_once('dbconnect.php');

// ตรวจสอบสิทธิ์ก่อน
if (!isset($_SESSION['user_id'])) {
    die("Session expired. Please login again.");
}

$action = $_GET['action'] ?? '';
$response = "";

// 🟢 เพิ่มข้อมูลใหม่
if ($action == 'add') {
    $clinic_id = ($_SESSION['role'] == 3) ? $_POST['clinic_id'] : $_SESSION['clinic_id'];
    $dog_id = mysqli_real_escape_string($objCon, $_POST['dog_id']);
    $appointment_date = mysqli_real_escape_string($objCon, $_POST['appointment_date']);
    $description = mysqli_real_escape_string($objCon, $_POST['description']);

    $sql = "INSERT INTO appointments (clinic_id, dog_id, appointment_date, description, status)
            VALUES ('$clinic_id', '$dog_id', '$appointment_date', '$description', 'รอพบแพทย์')";
    if (mysqli_query($objCon, $sql)) {
        $response = "✅ เพิ่มการนัดหมายเรียบร้อยแล้ว";
    } else {
        $response = "❌ เกิดข้อผิดพลาด: " . mysqli_error($objCon);
    }
    echo $response;
    exit();
}

// 🟡 ดึงข้อมูลเพื่อแก้ไข
if ($action == 'editform') {
    $id = intval($_GET['id']);
    $q = mysqli_query($objCon, "SELECT * FROM appointments WHERE appointment_id = $id");
    $r = mysqli_fetch_assoc($q);
    if (!$r) { echo "<p class='text-danger'>ไม่พบข้อมูล</p>"; exit(); }
?>
    <div class="row g-3">
        <input type="hidden" name="appointment_id" value="<?=$r['appointment_id']?>">

        <div class="col-md-6">
            <label>วันและเวลานัด</label>
            <input type="datetime-local" name="appointment_date" value="<?=date('Y-m-d\TH:i', strtotime($r['appointment_date']))?>" class="form-control" required>
        </div>

        <div class="col-md-6">
            <label>เลือกสุนัข</label>
            <select name="dog_id" class="form-select" required>
                <?php
                $d = mysqli_query($objCon, "SELECT dog_id, dog_name FROM dogs ORDER BY dog_name");
                while ($dog = mysqli_fetch_assoc($d)) {
                    $sel = ($dog['dog_id'] == $r['dog_id']) ? "selected" : "";
                    echo "<option value='{$dog['dog_id']}' $sel>{$dog['dog_name']}</option>";
                }
                ?>
            </select>
        </div>

        <?php if($_SESSION['role'] == 3) { ?>
        <div class="col-md-12">
            <label>เลือกคลินิก</label>
            <select name="clinic_id" class="form-select">
                <?php
                $c = mysqli_query($objCon, "SELECT * FROM clinics ORDER BY clinic_name");
                while ($cl = mysqli_fetch_assoc($c)) {
                    $sel = ($cl['clinic_id'] == $r['clinic_id']) ? "selected" : "";
                    echo "<option value='{$cl['clinic_id']}' $sel>{$cl['clinic_name']}</option>";
                }
                ?>
            </select>
        </div>
        <?php } ?>

        <div class="col-12">
            <label>รายละเอียด</label>
            <textarea name="description" class="form-control"><?=$r['description']?></textarea>
        </div>

        <div class="col-md-6">
            <label>สถานะ</label>
            <select name="status" class="form-select">
                <option value="รอพบแพทย์" <?=$r['status']=="รอพบแพทย์"?"selected":""?>>รอพบแพทย์</option>
                <option value="เสร็จสิ้น" <?=$r['status']=="เสร็จสิ้น"?"selected":""?>>เสร็จสิ้น</option>
                <option value="ยกเลิก" <?=$r['status']=="ยกเลิก"?"selected":""?>>ยกเลิก</option>
            </select>
        </div>
    </div>
<?php
    exit();
}

// 🔵 อัปเดตข้อมูล
if ($action == 'update') {
    $id = intval($_POST['appointment_id']);
    $appointment_date = mysqli_real_escape_string($objCon, $_POST['appointment_date']);
    $dog_id = mysqli_real_escape_string($objCon, $_POST['dog_id']);
    $description = mysqli_real_escape_string($objCon, $_POST['description']);
    $status = mysqli_real_escape_string($objCon, $_POST['status']);

    $clinic_id = ($_SESSION['role'] == 3) 
        ? mysqli_real_escape_string($objCon, $_POST['clinic_id'])
        : $_SESSION['clinic_id'];

    $sql = "UPDATE appointments SET 
            appointment_date='$appointment_date',
            dog_id='$dog_id',
            clinic_id='$clinic_id',
            description='$description',
            status='$status'
            WHERE appointment_id=$id";

    if (mysqli_query($objCon, $sql)) {
        $response = "✅ แก้ไขข้อมูลเรียบร้อยแล้ว";
    } else {
        $response = "❌ ไม่สามารถอัปเดตข้อมูลได้: " . mysqli_error($objCon);
    }
    echo $response;
    exit();
}

// 🔴 ลบข้อมูล
if ($action == 'delete') {
    $id = intval($_POST['id']);
    $sql = "DELETE FROM appointments WHERE appointment_id = $id";
    if (mysqli_query($objCon, $sql)) {
        echo "🗑️ ลบข้อมูลเรียบร้อยแล้ว";
    } else {
        echo "❌ ลบข้อมูลไม่สำเร็จ: " . mysqli_error($objCon);
    }
    exit();
}
?>
