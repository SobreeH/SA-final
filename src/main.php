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
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
    <style>
    #heroCarousel .carousel-caption h5 {
        color: #ffffff !important;
        text-shadow: 0 2px 8px rgba(0, 0, 0, .65);
    }

    #heroCarousel .carousel-caption p {
        color: #f1f5f9 !important;
        text-shadow: 0 1px 6px rgba(0, 0, 0, .55);
        opacity: 1 !important;
    }
    </style>
</head>

<body class="bg-light">

    <!-- Navbar -->
    <nav class="fixed-top w-full z-50">
        <div class="bg-[#262423]/90 backdrop-blur-lg border-b border-white/20 shadow-2xl">
            <div class="container-fluid px-6 py-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-8">
                        <div class="icon">
                            <a class="flex items-center space-x-2 text-white hover:text-white/80 transition-all duration-300"
                                href="#">
                                <img class="h-10 w-auto object-contain" src="pictures/logo.png" alt="logo image">
                            </a>
                        </div>

                        <div class="search">
                            <form class="flex" method="get" action="">
                                <input
                                    class="px-4 py-2 rounded-l-lg bg-white/10 backdrop-blur-md border border-white/30 text-white placeholder-white/70 focus:outline-none focus:ring-2 focus:ring-[#7bc05a]/50 focus:border-[#7bc05a]/60 hover:bg-white/20 transition-all duration-300 shadow-lg w-64"
                                    type="search" name="q" placeholder="ค้นหา..."
                                    value="<?= htmlspecialchars($search) ?>">
                                <button
                                    class="px-4 py-2 rounded-r-lg bg-[#7bc05a]/20 backdrop-blur-md border border-[#7bc05a]/40 border-l-0 text-white hover:bg-[#7bc05a]/30 hover:text-white transition-all duration-300 shadow-lg"
                                    type="submit">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </button>
                            </form>
                        </div>
                        <div class="menu">
                            <ul
                                class=" flex col-12 col-lg-auto me-lg-auto mb-2 justify-content-center mb-md-0 space-x-6">
                                <li><a href="#"
                                        class="text-white nav-link px-2 hover:!text-[#7bc05a] transition-colors duration-300">Home</a>
                                </li>

                                <li><a href="#livestock-list"
                                        class="text-white nav-link px-2 hover:!text-[#7bc05a] transition-colors duration-300">livestock</a>
                                </li>

                                <li><a href="#"
                                        class="text-white nav-link px-2 hover:!text-[#7bc05a] transition-colors duration-300">About</a>
                                </li>
                            </ul>
                        </div>

                    </div>

                    <div class="flex items-center space-x-3">
                        <a href="login.php"
                            class="!text-[#7bc05a] !border-[#7bc05a] text-decoration-none !bg-transparent px-4 py-2 rounded-lg border hover:backdrop-blur-md transition-all duration-200 shadow-lg font-medium">ล็อกอิน</a>
                        <a href="register.php"
                            class="hover:!text-[#7bc05a] hover:!border-[#7bc05a] text-decoration-none px-4 py-2 rounded-lg bg-transparent border-2 border-white/40 text-white hover:backdrop-blur-md transition-all duration-200 shadow-lg font-medium">สมัครสมาชิก</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Carousel (แสดงรูปด้วย) - Full Screen -->
    <div class="w-100" style="height: 100vh; margin-top: 70px;">
        <div id="heroCarousel" class="carousel slide h-100" data-bs-ride="carousel" data-bs-interval="5000">
            <!-- Carousel Indicators -->
            <div class="carousel-indicators" style="z-index: 15;">
                <?php foreach ($heroes as $i => $l): ?>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="<?= $i ?>"
                    <?= $i===0 ? 'class="active" aria-current="true"' : '' ?> aria-label="Slide <?= $i + 1 ?>"></button>
                <?php endforeach; ?>
            </div>

            <div class="carousel-inner h-100">
                <?php foreach ($heroes as $i => $l): ?>
                <div class="carousel-item h-100 <?= $i===0 ? 'active' : '' ?>" style="position: relative;">
                    <img src="<?= htmlspecialchars($l['image_resolved']) ?>"
                        alt="<?= htmlspecialchars($l['tag_number']) ?>" class="d-block w-100 h-100"
                        style="object-fit:cover;" onerror="this.onerror=null;this.src='farmer/uploads/default.jpg';">
                    <!-- Black Overlay -->
                    <div class="position-absolute top-0 start-0 w-100 h-100"
                        style="background-color: rgba(0, 0, 0, 0.5); z-index: 1;"></div>
                    <!-- Text Content -->
                    <div class="position-absolute top-50 start-50 translate-middle text-center text-white w-100"
                        style="z-index: 10; padding: 0 20px;">
                        <h1 class="display-4 fw-bold mb-3" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.8);">
                            Discover Our Freshest Meat</h1>
                        <p class="fs-4 mb-4" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.8);">
                            <?= htmlspecialchars($l['breed']) ?>
                            <?= !empty($l['weight']) ? '| '.htmlspecialchars($l['weight']).' kg' : '' ?></p>
                        <a href="#livestock-list" class="btn btn-success btn-lg px-5 py-3">ดูรายการสัตว์</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev"
                style="z-index: 15;">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next"
                style="z-index: 15;">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </div>

    <!-- Livestock List -->
    <div class="container my-5" id="livestock-list">
        <h2 class="mb-4">รายการสัตว์พร้อมขาย</h2>
        <div class="row g-4">
            <?php while ($row = $result->fetch_assoc()): ?>
            <?php
        $img = resolve_image($row['image'] ?? '');
        
      ?>
            <div class="col-12 col-md-4 col-lg-3">
                <div class="card shadow-sm h-100 border border-indigo-600">
                    <img src="<?= htmlspecialchars($img) ?>" class="card-img-top"
                        alt="<?= htmlspecialchars($row['tag_number']) ?>" style="object-fit: cover; height: 180px;"
                        onerror="this.onerror=null;this.src='farmer/uploads/default.jpg';">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?= htmlspecialchars($row['tag_number']) ?></h5>
                        <p class="mb-1">ชนิด: <?= htmlspecialchars($row['type']) ?></p>
                        <p class="mb-1">สายพันธุ์: <?= htmlspecialchars($row['breed']) ?></p>
                        <p class="mb-3">น้ำหนัก: <?= htmlspecialchars($row['weight']) ?> กก.</p>
                        <a href="detail.php?livestock_id=<?= (int)$row['livestock_id'] ?>"
                            class="btn btn-success mt-auto">รายละเอียด</a>
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