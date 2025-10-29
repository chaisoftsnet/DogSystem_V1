<?php
include("dbConnect.php");

// รับค่า dog_id จาก GET
$dog_id = isset($_GET['dog_id']) ? intval($_GET['dog_id']) : 0;

$dog = null;
$treatments = [];
$appointments = [];

if ($dog_id > 0) {
    // ดึงข้อมูลสุนัข
    $sql = "SELECT * FROM dogs WHERE dog_id = $dog_id";
    $result = mysqli_query($objCon, $sql);
    $dog = mysqli_fetch_assoc($result);

    // ดึงข้อมูลการรักษา
    $sql = "SELECT * FROM treatments WHERE dog_id = $dog_id ORDER BY treatment_date DESC";
    $result = mysqli_query($objCon, $sql);
    while ($row = mysqli_fetch_assoc($result)) {
        $treatments[] = $row;
    }

    // ดึงข้อมูลการนัดหมาย
    $sql = "SELECT * FROM appointments WHERE dog_id = $dog_id ORDER BY appointment_date DESC";
    $result = mysqli_query($objCon, $sql);
    while ($row = mysqli_fetch_assoc($result)) {
        $appointments[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ทะเบียนประวัติสุนัข</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container my-4">

    <h2 class="mb-4">📖 ทะเบียนประวัติสุนัข</h2>

    <!-- ฟอร์มค้นหา -->
    <form method="get" class="row g-3 mb-4">
        <div class="col-auto">
            <input type="number" name="dog_id" class="form-control" placeholder="กรอกรหัสสุนัข" required>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary">ค้นหา</button>
        </div>
    </form>

    <?php if ($dog): ?>
        <!-- ข้อมูลพื้นฐาน -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">ข้อมูลสุนัข</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <?php if ($dog['dog_image_path']): ?>
                            <img src="<?= htmlspecialchars($dog['dog_image_path']) ?>" class="img-fluid rounded">
                        <?php else: ?>
                            <div class="text-muted">ไม่มีรูปภาพ</div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-9">
                        <p><b>รหัสสุนัข:</b> <?= $dog['dog_id'] ?></p>
                        <p><b>ชื่อ:</b> <?= htmlspecialchars($dog['dog_name']) ?></p>
                        <p><b>สายพันธุ์:</b> <?= htmlspecialchars($dog['dog_breed']) ?></p>
                        <p><b>อายุ:</b> <?= $dog['dog_age'] ?> ปี</p>
                        <p><b>น้ำหนัก:</b> <?= $dog['dog_weight'] ?> กก.</p>
                        <p><b>เพศ:</b> <?= htmlspecialchars($dog['dog_gender']) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ประวัติการรักษา -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">ประวัติการรักษา</div>
            <div class="card-body">
                <?php if ($treatments): ?>
                    <table class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>วันที่</th>
                            <th>อาการ</th>
                            <th>การวินิจฉัย</th>
                            <th>การรักษา</th>
                            <th>ยา/เวชภัณฑ์</th>
                            <th>สัตวแพทย์</th>
                            <th>นัดครั้งถัดไป</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($treatments as $t): ?>
                            <tr>
                                <td><?= $t['treatment_date'] ?></td>
                                <td><?= nl2br(htmlspecialchars($t['symptoms'])) ?></td>
                                <td><?= nl2br(htmlspecialchars($t['diagnosis'])) ?></td>
                                <td><?= nl2br(htmlspecialchars($t['treatment'])) ?></td>
                                <td><?= nl2br(htmlspecialchars($t['medication'])) ?></td>
                                <td><?= htmlspecialchars($t['doctor_name']) ?></td>
                                <td><?= $t['next_appointment'] ?: '-' ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="text-muted">ไม่มีประวัติการรักษา</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- การนัดหมาย -->
        <div class="card mb-4">
            <div class="card-header bg-warning">การนัดหมาย</div>
            <div class="card-body">
                <?php if ($appointments): ?>
                    <table class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>วันและเวลา</th>
                            <th>เหตุผล</th>
                            <th>สถานะ</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($appointments as $a): ?>
                            <tr>
                                <td><?= $a['appointment_date'] ?></td>
                                <td><?= nl2br(htmlspecialchars($a['description'])) ?></td>
                                <td><?= $a['status'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="text-muted">ไม่มีข้อมูลการนัดหมาย</div>
                <?php endif; ?>
            </div>
        </div>
    <?php elseif ($dog_id): ?>
        <div class="alert alert-danger">❌ ไม่พบข้อมูลสุนัขในระบบ</div>
    <?php endif; ?>

</div>
</body>
</html>
