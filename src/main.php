<?php
include 'connDB.php';
session_start();

$search = $_GET['q'] ?? '';
$search = trim($search);

// ===== Query livestock (available or empty/NULL) =====
if (!empty($search)) {
    $sql = "SELECT * FROM Livestock
            WHERE (status='available' OR status IS NULL OR status = '')
              AND (tag_number LIKE ? OR type LIKE ? OR breed LIKE ?)
            ORDER BY livestock_id DESC";
    $stmt = $conn->prepare($sql);
    $like = "%$search%";
    $stmt->bind_param("sss", $like, $like, $like);
} else {
    $sql = "SELECT * FROM Livestock
            WHERE (status='available' OR status IS NULL OR status = '')
            ORDER BY livestock_id DESC";
    $stmt = $conn->prepare($sql);
}
$stmt->execute();
$result = $stmt->get_result();

// ===== Hero banner: latest 5 =====
$heroStmt = $conn->prepare("SELECT * FROM Livestock ORDER BY livestock_id DESC LIMIT 5");
$heroStmt->execute();
$heroRes = $heroStmt->get_result();

// ฟังก์ชันช่วย resolve รูป (URL หรือไฟล์ใน src/farmer/uploads/)
function resolve_image(?string $raw): string {
    if (empty($raw)) return 'farmer/uploads/default.jpg';
    $raw = trim($raw);
    if (filter_var($raw, FILTER_VALIDATE_URL)) return $raw; // URL เต็ม
    return 'farmer/uploads/' . rawurlencode(basename($raw)); // ไฟล์ในเครื่อง
}

$heroes = [];
while ($r = $heroRes->fetch_assoc()) {
    $r['image_resolved'] = resolve_image($r['image'] ?? '');
    $heroes[] = $r;
}
if (empty($heroes)) {
    $heroes[] = [
        'tag_number' => 'TAG000',
        'type' => 'ยินดีต้อนรับ',
        'breed' => 'ระบบจัดการปศุสัตว์',
        'weight' => '',
        'image_resolved' => 'farmer/uploads/default.jpg'
    ];
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>ระบบปศุสัตว์</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
  
  #heroCarousel .carousel-caption h5 {
    color: #ffffff !important;
    text-shadow: 0 2px 8px rgba(0,0,0,.65);
  }
  #heroCarousel .carousel-caption p {
    color: #f1f5f9 !important;
    text-shadow: 0 1px 6px rgba(0,0,0,.55);
    opacity: 1 !important;
  }
</style>
</head>
<body class="bg-light">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-success fixed-top">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">Livestock System</a>

    <form class="d-flex me-auto ms-3" method="get" action="">
      <input class="form-control me-2" type="search" name="q" placeholder="ค้นหา..." value="<?= htmlspecialchars($search) ?>">
      <button class="btn btn-outline-light" type="submit">ค้นหา</button>
    </form>

    <div class="d-flex">
      <a href="login.php" class="btn btn-light me-2">ล็อกอิน</a>
      <a href="register.php" class="btn btn-outline-light">สมัครสมาชิก</a>
    </div>
  </div>
</nav>

<div style="margin-top: 70px;"></div>

<!-- Hero Carousel (แสดงรูปด้วย) -->
<div class="container my-4">
  <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-inner">
      <?php foreach ($heroes as $i => $l): ?>
      <div class="carousel-item <?= $i===0 ? 'active' : '' ?>">
        <img src="<?= htmlspecialchars($l['image_resolved']) ?>"
             alt="<?= htmlspecialchars($l['tag_number']) ?>"
             class="d-block w-100"
             style="height:300px; object-fit:cover;"
             onerror="this.onerror=null;this.src='farmer/uploads/default.jpg';">
        <div class="carousel-caption d-none d-md-block">
          <h5><?= htmlspecialchars($l['tag_number']) ?> - <?= htmlspecialchars($l['type']) ?></h5>
          <p><?= htmlspecialchars($l['breed']) ?> <?= !empty($l['weight']) ? '| '.htmlspecialchars($l['weight']).' kg' : '' ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
      <span class="carousel-control-prev-icon"></span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
      <span class="carousel-control-next-icon"></span>
    </button>
  </div>
</div>

<!-- Livestock List -->
<div class="container my-5">
  <h2 class="mb-4">รายการสัตว์พร้อมขาย</h2>
  <div class="row g-4">
    <?php while ($row = $result->fetch_assoc()): ?>
      <?php
        $img = resolve_image($row['image'] ?? '');
        
      ?>
      <div class="col-12 col-md-4 col-lg-3">
        <div class="card shadow-sm h-100">
          <img src="<?= htmlspecialchars($img) ?>"
               class="card-img-top"
               alt="<?= htmlspecialchars($row['tag_number']) ?>"
               style="object-fit: cover; height: 180px;"
               onerror="this.onerror=null;this.src='farmer/uploads/default.jpg';">
          <div class="card-body d-flex flex-column">
            <h5 class="card-title"><?= htmlspecialchars($row['tag_number']) ?></h5>
            <p class="mb-1">ชนิด: <?= htmlspecialchars($row['type']) ?></p>
            <p class="mb-1">สายพันธุ์: <?= htmlspecialchars($row['breed']) ?></p>
            <p class="mb-3">น้ำหนัก: <?= htmlspecialchars($row['weight']) ?> กก.</p>
            <a href="#" class="btn btn-success mt-auto">รายละเอียด</a>
          </div>
        </div>
      </div>
    <?php endwhile; ?>
  </div>
</div>

<footer class="bg-dark text-white text-center py-3 mt-5">
  <p>&copy; <?= date("Y") ?> ระบบจัดการปศุสัตว์</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
