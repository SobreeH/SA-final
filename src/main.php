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

<body class="!bg-[#171615]">

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

                                <li><a href="#what-we-believe"
                                        class="text-white nav-link px-2 hover:!text-[#7bc05a] transition-colors duration-300">What
                                        We Believe</a>
                                </li>

                                <li><a href="#About-us"
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
                        <a href="#livestock-list"
                            class="btn btn-success btn-lg px-5 py-3 hover:!bg-[#367723] btn text-white !bg-[#3c8527] !border-t-5 !border-b-5 !border-t-[#52a535] !border-b-[#2a641c] mt-auto !rounded-none">ดูรายการสัตว์</a>
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
    <div class="container my-5 text-white" id="livestock-list">
        <h2 class="mb-4">รายการสัตว์พร้อมขาย</h2>
        <div class="row g-4">
            <?php while ($row = $result->fetch_assoc()): ?>
            <?php
        $img = resolve_image($row['image'] ?? '');
        
      ?>
            <div class="col-12 col-md-4 col-lg-3 ">
                <div
                    class="card h-100 !rounded-none !border-t-4 !border-r-4 !border-b-4 !border-l-4 !border-t-[#3d3938] !border-r-[#3d3938] !border-b-[#000] !border-l-[#000]">
                    <img src="<?= htmlspecialchars($img) ?>" class="card-img-top"
                        alt="<?= htmlspecialchars($row['tag_number']) ?>" style="object-fit: cover; height: 180px;"
                        onerror="this.onerror=null;this.src='farmer/uploads/default.jpg';">
                    <div class="card-body d-flex flex-column !bg-[#312f2e] text-white">
                        <h5 class="card-title"><?= htmlspecialchars($row['tag_number']) ?></h5>
                        <p class="mb-1">ชนิด: <?= htmlspecialchars($row['type']) ?></p>
                        <p class="mb-1">สายพันธุ์: <?= htmlspecialchars($row['breed']) ?></p>
                        <p class="mb-3">น้ำหนัก: <?= htmlspecialchars($row['weight']) ?> กก.</p>
                        <a href="detail.php?livestock_id=<?= (int)$row['livestock_id'] ?>"
                            class="btn hover:!bg-[#367723]  text-white !bg-[#3c8527] !border-t-5 !border-b-5 !border-t-[#52a535] !border-b-[#2a641c] !rounded-none  mt-auto !rounded-none">รายละเอียด</a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- What We Believe Section -->
    <section id="what-we-believe" class="py-20 my-20 relative overflow-hidden min-h-[60vh]"
        style="background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('https://images.unsplash.com/photo-1500595046743-cd271d694d30?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2074&q=80') center/cover;">
        <div class="container mx-auto px-6 h-full flex items-center justify-center">
            <div class="text-center text-white max-w-4xl">
                <h2 class="text-6xl font-bold mb-6 text-white text-shadow-[2px_2px_4px_rgba(0,0,0,0.8)]">
                    What We Believe
                </h2>
                <p class="text-xl mb-6 leading-relaxed text-shadow-[1px_1px_3px_rgba(0,0,0,0.7)]">
                    "We focus on quality products by ensuring our animals are healthy and happy.
                    This commitment reflects in the superior taste and tenderness of our meat."
                </p>
                <p class="text-base mb-6 italic text-shadow-[1px_1px_2px_rgba(0,0,0,0.6)]">
                    "We believe in simple, sustainable farming practices, and when we do this well,
                    people can taste the difference."
                </p>
                <p class="text-[#7bc05a] font-bold text-base text-shadow-[1px_1px_2px_rgba(0,0,0,0.8)]">
                    - Booker Livestock
                </p>
            </div>
        </div>

        <!-- Decorative grass overlay at bottom -->
        <div class="absolute bottom-0 left-0 w-full h-30 z-10"
            style="background: linear-gradient(to top, rgba(76, 175, 80, 0.3), transparent);">
        </div>
    </section>



    <!-- About Us Section -->
    <section
        class=" mx-15 my-20 py-20 bg-[#0e0d0d] !border-t-4 !border-r-4 !border-b-4 !border-l-4 !border-t-black !border-r-black !border-b-[#272626] !border-l-[#272626] "
        id="About-us">
        <div class="container mx-auto px-6">
            <h1 class="text-9xl font-bold !text-[#7bc05a] !mb-10">Booker Livestock</h1>
            <div class=" grid lg:grid-cols-2 gap-12 items-center ">
                <!-- Left Column - Content -->
                <div class="space-y-6 ">
                    <p class="text-lg  leading-relaxed !text-[#ede5e2]">
                        เราคือกลุ่มผู้เชี่ยวชาญด้านการจัดการปศุสัตว์ที่มีประสบการณ์กว่า 15 ปี
                        ในการเลี้ยงดู ดูแล และจัดจำหน่ายสัตว์คุณภาพสูง
                    </p>

                    <div class="grid grid-cols-2 gap-6 py-6">
                        <div class="text-center">
                            <h3 class="text-4xl font-bold !text-[#ede5e2] mb-2">500+</h3>
                            <p class="!text-[#d0c5c0]">สัตว์ที่ดูแล</p>
                        </div>
                        <div class="text-center">
                            <h3 class="text-4xl font-bold !text-[#ede5e2] mb-2">15+</h3>
                            <p class="!text-[#d0c5c0]">ปีประสบการณ์</p>
                        </div>
                        <div class="text-center">
                            <h3 class="text-4xl font-bold !text-[#ede5e2] mb-2">100%</h3>
                            <p class="!text-[#d0c5c0]">คุณภาพธรรมชาติ</p>
                        </div>
                        <div class="text-center">
                            <h3 class="text-4xl font-bold !text-[#ede5e2] mb-2">1000+</h3>
                            <p class="!text-[#d0c5c0]">ลูกค้าที่พึงพอใจ</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <h4 class="text-xl font-bold !text-[#ede5e2] ">บริการของเรา</h4>
                        <ul class="space-y-3">
                            <li class="flex items-center space-x-3">
                                <i class="fa-solid fa-check text-[#7bc05a] text-lg"></i>
                                <span class="!text-[#d0c5c0]">การเลี้ยงดูสัตว์แบบธรรมชาติ</span>
                            </li>
                            <li class="flex items-center space-x-3">
                                <i class="fa-solid fa-check text-[#7bc05a] text-lg"></i>
                                <span class="!text-[#d0c5c0]">ตรวจสุขภาพสัตว์อย่างสม่ำเสมอ</span>
                            </li>
                            <li class="flex items-center space-x-3">
                                <i class="fa-solid fa-check text-[#7bc05a] text-lg"></i>
                                <span class="!text-[#d0c5c0]">จำหน่ายเนื้อสดคุณภาพสูง</span>
                            </li>

                        </ul>
                    </div>

                </div>

                <!-- Right Column - Image -->
                <div class="relative">
                    <img src="https://images.unsplash.com/photo-1560493676-04071c5f467b?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2074&q=80"
                        alt="Farm and Livestock" class="w-full h-96 object-cover rounded-2xl shadow-2xl">

                    <!-- Overlay Badge -->
                    <div class="absolute top-4 right-4">
                        <div
                            class="bg-[#7bc05a] text-white px-4 py-2 rounded-full shadow-lg flex items-center space-x-2">
                            <i class="fa-solid fa-award"></i>
                            <span class="text-sm font-bold">มาตรฐานสูง</span>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <footer class="bg-dark text-white text-center py-3 mt-5">
        <p>&copy; <?= date("Y") ?> ระบบจัดการปศุสัตว์</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>