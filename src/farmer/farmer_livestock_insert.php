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
function err($m) { die("<div class='alert alert-danger'>❌ $m</div>"); }

// เมื่อ submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tag_number = trim($_POST['tag_number'] ?? '');
    $type       = trim($_POST['type'] ?? '');
    $breed      = trim($_POST['breed'] ?? '');
    $weight     = $_POST['weight'] === '' ? null : (float)$_POST['weight'];
    $status     = trim($_POST['status'] ?? 'available');
    $imagePath  = '';

    if ($tag_number === '' || !in_array($type, ['cow','goat','chicken'], true) || !in_array($status, ['available','sold'], true)) {
        err("กรอก Tag / เลือกชนิดและสถานะให้ถูกต้อง");
    }

    // จัดการรูปภาพ
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK && $_FILES['image_file']['name'] !== '') {
        $tmp  = $_FILES['image_file']['tmp_name'];
        $name = $_FILES['image_file']['name'];
        $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp','gif'];
        if (!in_array($ext, $allowed, true)) err("รองรับเฉพาะไฟล์ jpg, jpeg, png, webp, gif");

        $uploadDir = __DIR__ . '/uploads/';
        if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);

        $safeTag = preg_replace('/[^A-Za-z0-9_\-]/', '_', $tag_number);
        $newFile = $safeTag . '_' . time() . '.' . $ext;
        $dest    = $uploadDir . $newFile;

        if (!move_uploaded_file($tmp, $dest)) err("อัปโหลดไฟล์ไม่สำเร็จ");
        $imagePath = 'uploads/' . $newFile;
    } elseif (!empty($_POST['image_url'])) {
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
        echo "<div class='alert alert-danger'>❌ มี Tag Number นี้แล้ว</div>";
        echo "<div style='text-align:center; margin-top:20px;'><a href='farmer_livestock.php'>[กลับไปเพิ่ม]</a></div>";
        exit();
    }
    $stmt->close();

    // INSERT
    if ($weight === null) {
        $sql = "INSERT INTO Livestock (tag_number, type, breed, weight, status, image)
                VALUES (?, ?, NULLIF(?, ''), NULL, ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) err("Prepare failed: " . $conn->error);
        $stmt->bind_param("sssss", $tag_number, $type, $breed, $status, $imagePath);
    } else {
        $sql = "INSERT INTO Livestock (tag_number, type, breed, weight, status, image)
                VALUES (?, ?, NULLIF(?, ''), ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) err("Prepare failed: " . $conn->error);
          $stmt->bind_param("sssiss", $tag_number, $type, $breed, $weight, $status, $imagePath);
    }

    if ($stmt->execute()) {
    // redirect to manage livestock page
    header("Location: farmer_livestock.php");
    exit();
} else {
    echo "<div class='alert alert-danger'>❌ เพิ่มไม่สำเร็จ: " . htmlspecialchars($stmt->error) . "</div>";
}

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>เพิ่มปศุสัตว์</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
:root{
  --bg:#f7f9fb; --card:#fff; --text:#1f2937; --muted:#6b7280;
  --primary:#16a34a; --primary-600:#15803d; --ring:#a7f3d0; --border:#e5e7eb; --danger:#ef4444;
}
*{box-sizing:border-box}
body{margin:0;font-family:system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial;background:var(--bg);color:var(--text);}
.container{max-width:760px;margin:48px auto;padding:0 16px;}
h2{margin:0 0 16px 0;font-weight:700;}
.form-card{background:var(--card);border:1px solid var(--border);border-radius:16px;box-shadow:0 10px 30px rgba(0,0,0,.06);padding:24px;}
form label{display:block;font-weight:600;margin:14px 0 6px;color:var(--text);}
input[type=text], input[type=number], input[type=url], select, input[type=file]{width:100%;border:1px solid var(--border);border-radius:10px;padding:12px 14px;font-size:16px;background:#fff;color:var(--text);transition: box-shadow .15s ease,border-color .15s ease;}
input:focus, select:focus{outline:none;border-color:var(--primary);box-shadow:0 0 0 4px var(--ring);}
input[type=file]{padding:10px;}
.grid{display:grid;gap:14px;}
@media (min-width:640px){.grid-2{grid-template-columns:1fr 1fr}.grid-3{grid-template-columns:1fr 1fr 1fr}}
@media (max-width:640px){.grid-2,.grid-3{grid-template-columns:1fr !important;} button[type=submit]{font-size:1rem;padding:10px;}}
button[type=submit]{appearance:none;border:none;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;padding:12px 16px;border-radius:12px;font-weight:700;font-size:16px;background:var(--primary);color:#fff;width:100%;transition: transform .05s ease, background .2s ease;margin-top:16px;}
button:hover{background:var(--primary-600);} button:active{transform:translateY(1px);}
.link-back{display:inline-block;margin-top:12px;color:var(--primary);text-decoration:none;font-weight:600;}
.link-back:hover{text-decoration:underline;}
.alert{padding:12px 14px;border-radius:10px;margin-bottom:14px;font-weight:600;}
.alert-danger{background:#fee2e2;color:#991b1b;border:1px solid #fecaca;}
.alert-success{background:#dcfce7;color:#14532d;border:1px solid #bbf7d0;}
.preview{width:100%;height:220px;border:1px dashed var(--border);border-radius:12px;display:flex;align-items:center;justify-content:center;color:var(--muted);background:#fff;margin-top:6px;background-size:cover;background-position:center;}
#dashboardBtn{position:fixed;top:10px;left:10px;background:var(--primary);color:#fff;padding:10px 14px;border-radius:8px;text-decoration:none;z-index:1000;}
</style>
</head>
<body>
<a id="dashboardBtn" href="farmer_dashboard.php">Dashboard</a>
<div class="container">
  <h2>เพิ่มรายการปศุสัตว์</h2>
  <div class="form-card">
    <form action="farmer_livestock_insert.php" method="post" enctype="multipart/form-data">
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

      <div class="preview" id="imagePreview">Preview</div>

      <button type="submit">เพิ่มรายการ</button>
    </form>
  </div>
</div>

<script>
// Auto-generate Tag Number
const typeSelect = document.getElementById('type');
const tagInput = document.getElementById('tag_number');
let counters = { cow: 1, chicken: 1, goat: 1 };

function updateTag(){
    const prefixMap = { cow:'M', chicken:'C', goat:'G' };
    const type = typeSelect.value;
    const num = String(counters[type]).padStart(3,'0');
    tagInput.value = `TAG${prefixMap[type]}${num}`;
}
typeSelect.addEventListener('change', updateTag);
updateTag(); // initial

// Image preview
const fileInput = document.getElementById('image_file');
const urlInput = document.getElementById('image_url');
const preview = document.getElementById('imagePreview');

function showPreview(src){
    preview.style.backgroundImage = `url(${src})`;
    preview.textContent = '';
}

fileInput.addEventListener('change', ()=>{
    const file = fileInput.files[0];
    if(file) showPreview(URL.createObjectURL(file));
});

urlInput.addEventListener('input', ()=>{
    if(urlInput.value) showPreview(urlInput.value);
});
</script>
</body>
</html>
