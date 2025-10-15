<?php
// Register.php — สมัครสมาชิก Farmer (livestockdb) 
include 'connDB.php'; // ต้องสร้างตัวแปร $conn (mysqli) ให้พร้อมใช้งาน

// ปรับ charset ให้ชัด
if (isset($conn) && $conn instanceof mysqli) {
    @$conn->set_charset('utf8mb4');
}

// ส่งฟอร์มมา
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // เลือกฐานข้อมูล livestockdb
    if ($conn->select_db("livestockdb") === true) {

        // รับค่าจากฟอร์ม (กัน XSS ตอนแสดงผลภายหลัง — ที่ DB ใช้ prepared แยก)
        $username = trim($_POST['username'] ?? '');
        $password_raw = (string)($_POST['password'] ?? '');
        $name     = trim($_POST['name'] ?? '');
        $contact  = trim($_POST['contact'] ?? '');

        // ตรวจค่าบังคับ
        if ($username === '') {
            echo "<center>❌ กรุณากรอกชื่อผู้ใช้</center>";
            echo "<center><a href='Register.php'>[กลับไปหน้าสมัครสมาชิก]</a></center>";
            exit();
        }
        if (strlen($password_raw) < 8) {
            echo "<center>❌ รหัสผ่านต้องยาวอย่างน้อย 8 ตัวอักษร</center>";
            echo "<center><a href='Register.php'>[กลับไปหน้าสมัครสมาชิก]</a></center>";
            exit();
        }

        // ตรวจซ้ำ username
        $sql_check = "SELECT farmer_id FROM Farmer WHERE username = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("s", $username);
        $stmt_check->execute();
        $res = $stmt_check->get_result();
        if ($res && $res->num_rows > 0) {
            echo "<center>❌ มี username นี้ในระบบแล้ว</center>";
            echo "<center><a href='Register.php'>[กลับไปหน้าสมัครสมาชิก]</a></center>";
            exit();
        }
        $stmt_check->close();

        // เตรียม INSERT แบบ “ใส่เฉพาะคอลัมน์ที่มีค่า”
        // ตาราง Farmer: username (จำเป็น), password (จำเป็น), name (ออปชัน), contact (ออปชัน)
        $cols = ['username', 'password'];
        $params = [$username, password_hash($password_raw, PASSWORD_DEFAULT)];
        $types  = 'ss';

        if ($name !== '') {
            $cols[] = 'name';
            $params[] = $name;
            $types .= 's';
        }
        if ($contact !== '') {
            $cols[] = 'contact';
            $params[] = $contact;
            $types .= 's';
        }

        // สร้าง Placeholder ตามจำนวนคอลัมน์
        $placeholders = implode(',', array_fill(0, count($cols), '?'));
        $sql = "INSERT INTO Farmer (" . implode(',', $cols) . ") VALUES ($placeholders)";
        $stmt = $conn->prepare($sql);

        // bind_param แบบไดนามิก
        $bind = [];
        $bind[] = $types;
        foreach ($params as $k => $v) {
            $bind[] = &$params[$k]; // ต้องอ้างอิง
        }
        call_user_func_array([$stmt, 'bind_param'], $bind);

        if ($stmt->execute()) {
            $stmt->close();
            // สำเร็จ → เด้งไปหน้า Login
            header("Location: Login.php");
            exit();
        } else {
            echo "<center>❌ เกิดข้อผิดพลาด: " . htmlspecialchars($stmt->error, ENT_QUOTES, 'UTF-8') . "</center>";
            $stmt->close();
        }

    } else {
        echo "<center>❌ ไม่สามารถเลือกฐานข้อมูล livestockdb ได้</center>";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Register (Farmer)</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <link rel="stylesheet" href="../css/Register.css">
  <style>
    body { font-family: "Prompt", system-ui, -apple-system, Segoe UI, Roboto, "Helvetica Neue", Arial, "Noto Sans", "Liberation Sans", sans-serif; }
    .card-register { max-width: 560px; margin: 48px auto; padding: 28px; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,.08); background: #fff; }
    .card-register h2 { margin-bottom: 18px; }
  </style>
</head>
<body>

  <div class="card-register">
    <h2><i class="fa-solid fa-user-plus"></i> สมัครสมาชิก (Farmer)</h2>
    <form action="Register.php" method="POST">

      <div class="mb-3">
        <label for="username" class="form-label">ชื่อผู้ใช้ <span class="text-danger">*</span></label>
        <input type="text" name="username" class="form-control" placeholder="Username" required>
      </div>

      <div class="mb-3">
        <label for="password" class="form-label">รหัสผ่าน <span class="text-danger">*</span></label>
        <input type="password" name="password" class="form-control" placeholder="Password อย่างน้อย 8 ตัว" minlength="8" required>
      </div>

      <div class="mb-3">
        <label for="name" class="form-label">ชื่อ-นามสกุล (ใส่หรือไม่ก็ได้)</label>
        <input type="text" name="name" class="form-control" placeholder="ชื่อ-นามสกุล">
      </div>

      <div class="mb-3">
        <label for="contact" class="form-label">เบอร์ติดต่อ (ใส่หรือไม่ก็ได้)</label>
        <input type="text" name="contact" class="form-control" placeholder="เบอร์โทร">
      </div>

      <button type="submit" class="btn btn-primary w-100 mt-2">สมัครสมาชิก</button>
    </form>

    <a href="../main/h.php" class="btn btn-outline-primary w-100 mt-3">← กลับหน้าหลัก</a>
  </div>

</body>
</html>
