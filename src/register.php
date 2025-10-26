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
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Register (Farmer)</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="../css/Register.css">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
    body {
        font-family: "Prompt", system-ui, -apple-system, Segoe UI, Roboto, "Helvetica Neue", Arial, "Noto Sans", "Liberation Sans", sans-serif;
    }

    .card-register {
        max-width: 560px;
        margin: 50px auto;
        /* padding: 28px; */
        box-shadow: 0 10px 30px rgba(0, 0, 0, .08);
        background: #fff;
    }

    .card-register h2 {
        margin-bottom: 18px;
    }
    </style>
</head>

<body
    style="background: linear-gradient(45deg, #4a7c59 25%, #5d8b6b 25%, #5d8b6b 50%, #4a7c59 50%, #4a7c59 75%, #5d8b6b 75%, #5d8b6b); background-size: 60px 60px; background-repeat: repeat;">

    <div class="relative card-register px-4 py-4 !pt-25">
        <div
            class=" absolute top-0 left-0 header flex items-center justify-center gap-3 mb-4 !text-[#262423] !rounded-none !bg-[#d0c5c0] !w-full  !border-b-3 border-b-[#a69e9a] p-3 text-center">
            <h2 class="!font-bold text-center !m-0"><i class="fa-solid fa-user-plus"></i> สมัครสมาชิก (Farmer)</h2>
        </div>
        <form action="Register.php" method="POST">

            <div class="mb-3">
                <label for="username" class="form-label text-[#69696a]">ชื่อผู้ใช้ <span
                        class="text-danger">*</span></label>
                <input type="text" name="username" class="form-control !border-[#313232] !rounded-none" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label text-[#69696a]">รหัสผ่าน <span
                        class="text-danger">*</span></label>
                <input type="password" name="password" class="form-control !border-[#313232] !rounded-none"
                    minlength="8" required>
            </div>

            <div class="mb-3">
                <label for="name" class="form-label text-[#69696a]">ชื่อ-นามสกุล (ใส่หรือไม่ก็ได้)</label>
                <input type="text" name="name" class="form-control !border-[#313232] !rounded-none">
            </div>

            <div class="mb-3">
                <label for="contact" class="form-label text-[#69696a]">เบอร์ติดต่อ (ใส่หรือไม่ก็ได้)</label>
                <input type="text" name="contact" class="form-control !border-[#313232] !rounded-none">
            </div>

            <button type="submit"
                class="btn btn-primary w-100 mt-2  hover:!bg-[#367723]  text-white !bg-[#3c8527] !border-t-5 !border-b-5 !border-t-[#52a535] !border-b-[#2a641c] !rounded-none">สมัครสมาชิก</button>
        </form>

        <a href="main.php"
            class="btn w-100 mt-3 hover:!bg-[#d9d1cd]  text-[#262423] !bg-[#d0c5c0] !border-t-5 !border-b-5 !border-t-[#ede5e2] !border-b-[#aba09c] !rounded-none">←
            กลับหน้าหลัก</a>
    </div>

</body>

</html>