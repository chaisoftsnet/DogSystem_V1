<?php
@ob_start();
@session_start();
require_once('dbConnect.php');
require_once('function.php');

// รับค่าจากฟอร์ม
$username = trim($_POST['username']);
$password = trim($_POST['password']);
$Mode = $_POST['Mode'] ?? '';

// เข้ารหัส password (MD5)
$password_enc = md5($password);

// ตรวจสอบชื่อผู้ใช้
$strSQL = "SELECT * FROM user WHERE username = '".mysqli_real_escape_string($objCon, $username)."' LIMIT 1";
$objQuery = mysqli_query($objCon, $strSQL);

if ($objQuery && mysqli_num_rows($objQuery) > 0) {
    $user = mysqli_fetch_assoc($objQuery);

    // ตรวจสอบรหัสผ่าน
    if ($password_enc === $user['password']) {
        // เก็บ session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['clinic_id'] = $user['clinic_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['fullname'] = $user['fullname'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['Mode'] = $Mode;

        // ✅ ตรวจสอบ role เพื่อเลือกหน้า dashboard
        switch ($user['role']) {
            case 1: // ลูกค้า
                header("Location: dog_dashboard.php");
                break;
            case 2: // เจ้าหน้าที่คลินิก
            case 3: // ผู้ดูแลระบบ
                header("Location: dashboard.php");
                break;
            default:
                echo "<script>alert('ไม่พบสิทธิ์ผู้ใช้งานที่กำหนด'); window.location='index.php';</script>";
        }
        exit();

    } else {
        echo "<script>alert('❌ รหัสผ่านไม่ถูกต้อง'); window.location='index.php';</script>";
        exit();
    }

} else {
    echo "<script>alert('❌ ไม่พบชื่อผู้ใช้นี้ในระบบ'); window.location='index.php';</script>";
    exit();
}
?>
