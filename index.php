<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>🐶 ระบบบริหารจัดการคลินิกรักษาสัตว์</title>

<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<?session_start();?>
<style>
/* 🌌 พื้นหลัง */
body {
  margin: 0;
  padding: 0;
  height: 100vh;
  font-family: 'Prompt', sans-serif;
  background: radial-gradient(circle at top, #1b2735 0%, #090a0f 80%);
  display: flex;
  justify-content: center;
  align-items: center;
  overflow: hidden;
}

/* 💎 กล่อง Login แบบกระจก */
.login-box {
  background: rgba(255, 255, 255, 0.08);
  border-radius: 16px;
  backdrop-filter: blur(12px);
  box-shadow: 0 4px 25px rgba(0, 0, 0, 0.3);
  padding: 40px 35px;
  text-align: center;
  width: 350px;
  color: #fff;
  animation: fadeIn 1.2s ease;
  border: 1px solid rgba(255,255,255,0.1);
}

/* ✨ Animation */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

/* 🐾 หัวข้อ */
.login-box h4 {
  font-size: 20px;
  font-weight: 600;
  color: #00e676;
  margin-bottom: 25px;
  text-shadow: 0 0 10px rgba(0,230,118,0.3);
}

/* 🔽 ช่องกรอก */
.login-box select,
.login-box input {
  width: 100%;
  padding: 10px 12px;
  margin: 8px 0;
  border-radius: 8px;
  border: none;
  outline: none;
  background: rgba(255,255,255,0.1);
  color: #fff;
  font-size: 15px;
  transition: all 0.3s;
}
.login-box input:focus, 
.login-box select:focus {
  background: rgba(255,255,255,0.2);
  box-shadow: 0 0 10px rgba(0,230,118,0.3);
}

/* 🔘 ปุ่ม */
.login-box button {
  width: 100%;
  padding: 10px;
  margin-top: 15px;
  border-radius: 8px;
  border: none;
  background: linear-gradient(45deg, #00e676, #00bfa5);
  color: #000;
  font-weight: bold;
  cursor: pointer;
  transition: 0.3s;
}
.login-box button:hover {
  transform: translateY(-2px);
  background: linear-gradient(45deg, #00c853, #1de9b6);
}

/* 🧾 ลิงก์สมัคร */
.register-link {
  font-size: 14px;
  margin-top: 15px;
}
.register-link a {
  color: #00e676;
  text-decoration: none;
  transition: 0.2s;
}
.register-link a:hover { text-decoration: underline; }

/* 🌙 ปุ่มหน้าหลัก */
.login-box .btn-home {
  display: inline-block;
  margin-top: 15px;
  font-size: 14px;
  color: #ccc;
  text-decoration: none;
}
.login-box .btn-home:hover { color: #00e676; }

/* 📜 Footer */
.footer {
  margin-top: 20px;
  font-size: 12px;
  color: #aaa;
}

/* 🎇 เอฟเฟกต์วงกลมพื้นหลัง */
.circle {
  position: absolute;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(0,230,118,0.2), transparent);
  animation: float 10s infinite ease-in-out alternate;
}
.circle:nth-child(1) { width: 300px; height: 300px; top: 10%; left: 15%; animation-delay: 0s; }
.circle:nth-child(2) { width: 200px; height: 200px; bottom: 10%; right: 20%; animation-delay: 2s; }
.circle:nth-child(3) { width: 150px; height: 150px; top: 60%; left: 70%; animation-delay: 4s; }

@keyframes float {
  from { transform: translateY(0px) scale(1); opacity: 0.8; }
  to { transform: translateY(-30px) scale(1.1); opacity: 0.5; }
}
</style>
</head>

<body>
  <div class="circle"></div>
  <div class="circle"></div>
  <div class="circle"></div>

  <div class="login-box">
    <h4><i class="bi bi-hospital"></i> ระบบบริหารคลินิกรักษาสัตว์ V.1</h4>
    <form action="login_process.php" method="post">
      <select name="username" required>
        <option value="">-- เลือกชื่อผู้ใช้ --</option>
        <option value="admin">admin</option>
        <option value="manager_brand_2">manager_brand_2</option>
        <option value="user_brand_2_1">user_brand_2_1</option>
      </select>

      <input type="password" name="password" placeholder="🔑 รหัสผ่าน" value="1111" required>
      <button type="submit">เข้าสู่ระบบ</button>

      <div class="register-link">
        ยังไม่มีบัญชี? <a href="register.php">สมัครสมาชิกที่นี่</a>
      </div>

      <a href="http://chaisofts.thddns.net:81/App_ppc/index.php" class="btn-home">
        <i class="bi bi-arrow-left"></i> กลับหน้าหลัก
      </a>
    </form>
    <div class="footer">© <?=date("Y")?> ระบบคลินิกรักษาสัตว์ Version 1.0 / 2568</div>
  </div>
</body>
</html>
