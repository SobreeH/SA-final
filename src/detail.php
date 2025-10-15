<?php
// detail.php — แสดงรายละเอียดสัตว์จากฐาน livestockdb พร้อมเบอร์ติดต่อ Farmer
require_once __DIR__ . '/connDB.php';
session_start();

/* ---------- Helper ---------- */
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function resolve_img(?string $raw): string{
  if (empty($raw)) return 'farmer/uploads/default.jpg';
  $raw = trim($raw);
  if (filter_var($raw, FILTER_VALIDATE_URL)) return $raw;
  return 'farmer/uploads/' . rawurlencode(basename($raw));
}

/* ---------- รับพารามิเตอร์ ---------- */
$lid = isset($_GET['livestock_id']) ? (int)$_GET['livestock_id'] : 0;
if ($lid <= 0) { http_response_code(400); echo 'Bad request'; exit; }

/* ---------- ดึงข้อมูลสัตว์ + เบอร์ติดต่อ Farmer (ถ้ามีใน Sales) ---------- */
// เลือกสัตว์
$sql = "SELECT l.livestock_id, l.tag_number, l.type, l.breed, l.weight, l.status, l.date_added, l.image
        FROM Livestock l
        WHERE l.livestock_id=? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $lid);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();
if(!$item){ http_response_code(404); echo 'ไม่พบรายการ'; exit; }

$item['image_url'] = resolve_img($item['image'] ?? '');

/* ---------- หา Farmer ผู้ดูแล/ผู้ปรับปรุงล่าสุดจากตาราง Supply.updated_by (ตัวอย่าง)
   หมายเหตุ: ในสคีมาที่ให้มายังไม่มีความสัมพันธ์ตรงว่า "สัตว์ตัวนี้เป็นของ Farmer คนไหน"
   เพื่อให้มีเบอร์ติดต่อ เราจะลองหา Farmer คนล่าสุดที่เคยอัปเดตคลัง (เป็นตัวอย่าง contact)
   ถ้าคุณมีความสัมพันธ์จริง (เช่น Livestock มีคอลัมน์ farmer_id) ให้เปลี่ยน JOIN ตามนั้นได้เลย
*/
$farmer = null;
$qFarmer = $conn->query("SELECT f.farmer_id, f.name, f.contact\n                        FROM Farmer f\n                        ORDER BY f.updated_at DESC, f.created_at DESC\n                        LIMIT 1");
if ($qFarmer && $qFarmer->num_rows){
  $farmer = $qFarmer->fetch_assoc();
}

/* ---------- ประวัติสุขภาพ / ผสมพันธุ์ ---------- */
$sqlH = "SELECT treatment_date, treatment, vet_id\n         FROM Health_Records WHERE livestock_id=?\n         ORDER BY treatment_date DESC, health_id DESC LIMIT 10";
$sh = $conn->prepare($sqlH);
$sh->bind_param('i', $lid);
$sh->execute();
$health = $sh->get_result();

$sqlB = "SELECT date_inseminated, pregnancy_result, vet_id\n         FROM Breeding_Records WHERE livestock_id=?\n         ORDER BY date_inseminated DESC, breeding_id DESC LIMIT 10";
$sb = $conn->prepare($sqlB);
$sb->bind_param('i', $lid);
$sb->execute();
$breed = $sb->get_result();
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>รายละเอียด: <?= h($item['tag_number']) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
  body{ background:#f7f8fa; }
  .card{ border:0; box-shadow:0 10px 25px rgba(0,0,0,.06); border-radius:18px; }
  .hero-img{ height:360px; object-fit:cover; border-radius:16px; width:100%; filter:brightness(.92) contrast(1.05); }
  .meta dt{ color:#64748b; font-weight:600; }
  .meta dd{ color:#0f172a; margin-bottom:.65rem; }
  .badge-soft{ background:rgba(16,185,129,.12); color:#065f46; border:1px solid rgba(16,185,129,.25); }
  .timeline li{ padding:.5rem 0; border-left:3px solid #e2e8f0; margin-left:.6rem; padding-left:1rem; }
  .timeline li::marker{ content:''; }
  .timeline .date{ font-size:.9rem; color:#64748b; }
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-success">
  <div class="container">
    <a class="navbar-brand fw-bold" href="main.php">Livestock System</a>
  </div>
</nav>
<main class="container py-4">
  <div class="row g-4">
    <div class="col-12 col-lg-6">
      <img src="<?= h($item['image_url']) ?>" alt="<?= h($item['tag_number']) ?>" class="hero-img"
           onerror="this.onerror=null;this.src='farmer/uploads/default.jpg';">
    </div>
    <div class="col-12 col-lg-6">
      <div class="card p-4 h-100">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <h2 class="h4 mb-0">แท็ก: <?= h($item['tag_number']) ?></h2>
          <span class="badge <?= ($item['status']==='available' ? 'bg-success' : 'bg-secondary') ?>"><?= h($item['status']) ?></span>
        </div>
        <dl class="row meta mt-2">
          <dt class="col-sm-4">ชนิด</dt><dd class="col-sm-8 text-capitalize"><?= h($item['type']) ?></dd>
          <dt class="col-sm-4">สายพันธุ์</dt><dd class="col-sm-8"><?= h($item['breed']) ?></dd>
          <dt class="col-sm-4">น้ำหนัก</dt><dd class="col-sm-8"><?= h($item['weight']) ?> กก.</dd>
          <dt class="col-sm-4">เพิ่มเมื่อ</dt><dd class="col-sm-8"><?= h($item['date_added']) ?></dd>
        </dl>

        <?php if($farmer): ?>
          <div class="alert alert-success d-flex align-items-center" role="alert">
            <i class="bi bi-telephone-forward me-2"></i>
            <div>
              ติดต่อผู้ดูแลฟาร์ม: <strong><?= h($farmer['name'] ?: 'Farmer') ?></strong>
              <div>เบอร์โทร: <a href="tel:<?= h($farmer['contact']) ?>" class="link-dark fw-semibold"><?= h($farmer['contact']) ?></a></div>
            </div>
          </div>
        <?php else: ?>
          <div class="alert alert-secondary" role="alert">ยังไม่มีข้อมูลเบอร์ติดต่อ Farmer</div>
        <?php endif; ?>

        <div class="d-flex gap-2">
          <a href="main.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> กลับ</a>
          <?php if($farmer && !empty($farmer['contact'])): ?>
            <a class="btn btn-success" href="tel:<?= h($farmer['contact']) ?>">
              <i class="bi bi-telephone"></i> โทรติดต่อ
            </a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-4 mt-1">
    <div class="col-12 col-lg-6">
      <div class="card p-4">
        <h3 class="h5 mb-3">ประวัติสุขภาพ (ล่าสุด)</h3>
        <ul class="timeline list-unstyled mb-0">
          <?php if ($health->num_rows===0): ?>
            <li>ยังไม่มีบันทึก</li>
          <?php else: while($hrow=$health->fetch_assoc()): ?>
            <li>
              <div class="date"><?= h($hrow['treatment_date']) ?></div>
              <div><?= h($hrow['treatment']) ?></div>
              <?php if(!empty($hrow['vet_id'])): ?><div class="text-muted">(Vet ID: <?= h($hrow['vet_id']) ?>)</div><?php endif; ?>
            </li>
          <?php endwhile; endif; ?>
        </ul>
      </div>
    </div>
    <div class="col-12 col-lg-6">
      <div class="card p-4">
        <h3 class="h5 mb-3">บันทึกผสมพันธุ์ (ล่าสุด)</h3>
        <ul class="timeline list-unstyled mb-0">
          <?php if ($breed->num_rows===0): ?>
            <li>ยังไม่มีบันทึก</li>
          <?php else: while($brow=$breed->fetch_assoc()): ?>
            <li>
              <div class="date"><?= h($brow['date_inseminated']) ?: '—' ?></div>
              <div>ผลการตั้งท้อง: <span class="badge badge-soft rounded-pill text-uppercase"><?= h($brow['pregnancy_result']) ?></span></div>
              <?php if(!empty($brow['vet_id'])): ?><div class="text-muted">(Vet ID: <?= h($brow['vet_id']) ?>)</div><?php endif; ?>
            </li>
          <?php endwhile; endif; ?>
        </ul>
      </div>
    </div>
  </div>
</main>
<footer class="text-center py-4 text-muted small">© <?= date('Y') ?> Livestock System</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
