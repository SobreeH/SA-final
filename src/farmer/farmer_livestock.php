<?php
include '../connDB.php';
include '../session_check.php';
require_role('farmer');

// เลือกฐานข้อมูล
$dbname = "livestockdb";
if (!$conn->select_db($dbname)) {
    echo "Error selecting database: " . htmlspecialchars($conn->error);
    exit();
}

// ฟังก์ชันช่วย
function ok_url(string $u): bool { return (bool)filter_var($u, FILTER_VALIDATE_URL); }
function err($m) { die("<div style='color:red; text-align:center; margin-top:30px;'>❌ $m</div>"); }

// เมื่อ submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tag_number = trim($_POST['tag_number'] ?? '');
    $type       = trim($_POST['type'] ?? '');
    $breed      = trim($_POST['breed'] ?? '');
    $weight     = $_POST['weight'] === '' ? null : (float)$_POST['weight'];
    $status     = trim($_POST['status'] ?? 'available');
    $imagePath  = '';

    // ตรวจค่าที่จำเป็น
    if ($tag_number === '' || !in_array($type, ['cow','goat','chicken'], true) || !in_array($status, ['available','sold'], true)) {
        err("กรอก Tag / เลือกชนิดและสถานะให้ถูกต้อง");
    }

    // --- จัดการรูปภาพ: ต้องมีอย่างน้อย 1 อย่าง (ไฟล์ หรือ URL) ---
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK && $_FILES['image_file']['name'] !== '') {
        // อัปโหลดไฟล์
        $tmp  = $_FILES['image_file']['tmp_name'];
        $name = $_FILES['image_file']['name'];
        $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp','gif'];
        if (!in_array($ext, $allowed, true)) {
            err("รองรับเฉพาะไฟล์ jpg, jpeg, png, webp, gif");
        }

        // (ทางเลือก) ตรวจขนาด/ชนิดจริง
        // if (filesize($tmp) > 2*1024*1024) err("ไฟล์ใหญ่เกิน 2MB");
        $uploadDir = __DIR__ . '/uploads/';
        if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);

        $safeTag = preg_replace('/[^A-Za-z0-9_\-]/', '_', $tag_number);
        $newFile = $safeTag . '_' . time() . '.' . $ext;
        $dest    = $uploadDir . $newFile;

        if (!move_uploaded_file($tmp, $dest)) err("อัปโหลดไฟล์ไม่สำเร็จ");
        $imagePath = 'uploads/' . $newFile; // เก็บเป็น relative path ไว้ใส่ src ได้
    } elseif (!empty($_POST['image_url'])) {
        // ใช้ URL
        $url = trim($_POST['image_url']);
        if (!ok_url($url)) err("URL รูปภาพไม่ถูกต้อง");
        $imagePath = $url;
    } else {
        err("กรุณาอัปโหลดรูปภาพ หรือใส่ URL รูปภาพอย่างน้อยหนึ่งอย่าง");
    }

    // กันซ้ำ tag_number
    $sql_check = "SELECT livestock_id FROM Livestock WHERE tag_number = ? LIMIT 1";
    $stmt = $conn->prepare($sql_check);
    $stmt->bind_param("s", $tag_number);
    $stmt->execute();
    $dup = $stmt->get_result();
    if ($dup && $dup->num_rows > 0) {
        echo "<div style='color:red; text-align:center; margin-top:30px;'>❌ มี Tag Number นี้แล้ว</div>";
        echo "<div style='text-align:center; margin-top:20px;'><a href='add_livestock.php'>[กลับไปเพิ่ม]</a></div>";
        exit();
    }
    $stmt->close();

    // INSERT ใช้คอลัมน์ image ด้วย
    if ($weight === null) {
        $sql = "INSERT INTO Livestock (tag_number, type, breed, weight, status, image)
                VALUES (?, ?, NULLIF(?, ''), NULL, ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) err("SQL Error: " . $conn->error);
        $stmt->bind_param("sssss", $tag_number, $type, $breed, $status, $imagePath);
    } else {
        $sql = "INSERT INTO Livestock (tag_number, type, breed, weight, status, image)
                VALUES (?, ?, NULLIF(?, ''), ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) err("SQL Error: " . $conn->error);
        $stmt->bind_param("sssdds", $tag_number, $type, $breed, $weight, $status, $imagePath);
    }

    if ($stmt->execute()) {
        echo "<div style='color:green; text-align:center; margin-top:30px;'>✅ เพิ่มรายการปศุสัตว์สำเร็จ</div>";
        echo "<div style='text-align:center; margin-top:20px;'><a href='manage_livestock.php'>[กลับหน้าจัดการ]</a></div>";
    } else {
        echo "<div style='color:red; text-align:center; margin-top:30px;'>❌ เพิ่มไม่สำเร็จ: " . htmlspecialchars($stmt->error) . "</div>";
        echo "<div style='text-align:center; margin-top:20px;'><a href='add_livestock.php'>[กลับไปเพิ่ม]</a></div>";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>เพิ่มปศุสัตว์</title>
  <style>
    :root{
      --bg:#f7f9fb;
      --card:#ffffff;
      --text:#1f2937;
      --muted:#6b7280;
      --primary:#16a34a;        /* เขียว */
      --primary-600:#15803d;
      --ring:#a7f3d0;
      --border:#e5e7eb;
      --danger:#ef4444;
    }
    *{box-sizing:border-box}
    html,body{height:100%}
    body{
      margin:0; font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
      background:var(--bg); color:var(--text);
    }

    .container{
      max-width:760px; margin:48px auto; padding:0 16px;
    }
    h2{margin:0 0 16px 0; font-weight:700}

    /* card */
    .form-card{
      background:var(--card);
      border:1px solid var(--border);
      border-radius:16px;
      box-shadow:0 10px 30px rgba(0,0,0,.06);
      padding:24px;
    }

    form label{
      display:block; font-weight:600; margin:14px 0 6px; color:var(--text);
    }
    input[type="text"],
    input[type="number"],
    input[type="url"],
    select,
    input[type="file"]{
      width:100%;
      border:1px solid var(--border);
      border-radius:10px;
      padding:12px 14px;
      font-size:16px; background:#fff; color:var(--text);
      transition: box-shadow .15s ease, border-color .15s ease;
    }
    input:focus, select:focus, textarea:focus{
      outline:none; border-color:var(--primary);
      box-shadow:0 0 0 4px var(--ring);
    }
    input[type="file"]{padding:10px}

    /* grid */
    .grid{display:grid; gap:14px}
    @media (min-width:640px){
      .grid-2{grid-template-columns:1fr 1fr}
      .grid-3{grid-template-columns:1fr 1fr 1fr}
    }

    /* helper text */
    .hint{color:var(--muted); font-size:.9rem; margin-top:6px}

    /* buttons */
    button[type="submit"],
    .btn{
      appearance:none; border:none; cursor:pointer;
      display:inline-flex; align-items:center; justify-content:center;
      padding:12px 16px; border-radius:12px; font-weight:700; font-size:16px;
      background:var(--primary); color:#fff; width:100%;
      transition: transform .05s ease, background .2s ease;
      margin-top:16px;
    }
    button:hover,.btn:hover{background:var(--primary-600)}
    button:active,.btn:active{transform:translateY(1px)}
    .link-back{
      display:inline-block; margin-top:12px; color:var(--primary); text-decoration:none; font-weight:600;
    }
    .link-back:hover{text-decoration:underline}

    /* alerts (ใช้กับ err() หรือข้อความสำเร็จ) */
    .alert{
      padding:12px 14px; border-radius:10px; margin-bottom:14px; font-weight:600;
    }
    .alert-danger{background:#fee2e2; color:#991b1b; border:1px solid #fecaca}
    .alert-success{background:#dcfce7; color:#14532d; border:1px solid #bbf7d0}

    /* image preview (optional ถ้าจะใส่ภายหลัง) */
    .preview{
      width:100%; height:220px; border:1px dashed var(--border); border-radius:12px;
      display:flex; align-items:center; justify-content:center; color:var(--muted); background:#fff;
    }
  </style>
</head>

<body>
<div class="container">
  <h2>เพิ่มรายการปศุสัตว์</h2>

  <!-- ตัวอย่าง alert ใช้งานได้กับข้อความจาก PHP -->
  <!-- <div class="alert alert-danger">❌ ข้อความผิดพลาด</div> -->
  <!-- <div class="alert alert-success">✅ สำเร็จ</div> -->
<div>
    <a href="farmer_dashboard.php" >back</a>
</div>
  <div class="form-card">
    <form action="farmer_livestock.php" method="post" enctype="multipart/form-data">
      <label for="tag_number">Tag Number</label>
      <input type="text" id="tag_number" name="tag_number" required>

      <div class="grid grid-2">
        <div>
          <label for="type">ชนิด</label>
          <select id="type" name="type" required>
            <option value="cow">cow</option>
            <option value="goat">goat</option>
            <option value="chicken">chicken</option>
          </select>
        </div>
        <div>
          <label for="status">สถานะ</label>
          <select id="status" name="status" required>
            <option value="available">available</option>
            <option value="sold">sold</option>
          </select>
        </div>
      </div>

      <div class="grid grid-2">
        <div>
          <label for="breed">สายพันธุ์</label>
          <input type="text" id="breed" name="breed" placeholder="ตัวอย่าง: Angus / Boer / Leghorn">
        </div>
        <div>
          <label for="weight">น้ำหนัก (กก.)</label>
          <input type="number" step="0.01" id="weight" name="weight" placeholder="เช่น 580.5">
        </div>
      </div>

      <div class="grid grid-2">
        <div>
          <label for="image_url">URL รูปภาพ (ถ้ามี)</label>
          <input type="url" id="image_url" name="image_url" placeholder="https://example.com/image.jpg">
          <div class="hint">ถ้ากรอกทั้ง URL และเลือกไฟล์ ระบบจะใช้ไฟล์อัปโหลด</div>
        </div>
        <div>
          <label for="image_file">หรืออัปโหลดรูปภาพ</label>
          <input type="file" id="image_file" name="image_file" accept="image/*">
        </div>
      </div>

      <button type="submit">เพิ่มรายการ</button>
    </form>

    <a class="link-back" href="farmer_dashboard.php">← กลับหน้าจัดการ</a>
  </div>
</div>

</body>
</html>
