<?php
include '../connDB.php';
include '../session_check.php';
require_role('vet');

// Fetch filter values
$search_tag = trim($_GET['search_tag'] ?? '');
$typeFilter = trim($_GET['type'] ?? '');
$date_from = trim($_GET['date_from'] ?? '');
$date_to = trim($_GET['date_to'] ?? '');

// Build SQL with optional filters
$sql = "SELECT br.breeding_id, l.livestock_id, l.tag_number, l.type, br.date_inseminated, br.pregnancy_result
        FROM Breeding_Records br
        JOIN Livestock l ON br.livestock_id = l.livestock_id
        WHERE 1=1";

$params = [];
$types = '';

if ($search_tag !== '') {
    $sql .= " AND l.tag_number LIKE ?";
    $searchTerm = "%$search_tag%";
    $params[] = $searchTerm;
    $types .= "s";
}

if ($typeFilter !== '' && in_array($typeFilter, ['cow','goat','chicken'], true)) {
    $sql .= " AND l.type = ?";
    $params[] = $typeFilter;
    $types .= "s";
}

if ($date_from !== '' && $date_to !== '') {
    $sql .= " AND br.date_inseminated BETWEEN ? AND ?";
    $params[] = $date_from;
    $params[] = $date_to;
    $types .= "ss";
}

$sql .= " ORDER BY br.date_inseminated DESC";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Fetch livestock for modal dropdown
$livestock_list = $conn->query("SELECT livestock_id, tag_number, type FROM Livestock WHERE status='available'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Breeding Records</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: #f7f9fb; font-family: system-ui,-apple-system,"Segoe UI",Roboto,Arial; color: #1f2937; }
.container { max-width: 960px; margin: 32px auto; }
.btn-sm { font-size: 0.8rem; padding: 4px 8px; }
.table td, .table th { vertical-align: middle; }
</style>
</head>
<body>
<div class="container">
    <h2>Breeding Records</h2>
    <div class="mb-3 d-flex justify-content-between">
        <div>
            <a href="vet_dashboard.php" class="btn btn-secondary btn-sm">← Dashboard</a>
        </div>
        <div>
            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addBreedingModal">+ Add Breeding Record</button>
        </div>
    </div>

    <!-- Filter Form -->
    <form class="row g-2 mb-3" method="get">
        <div class="col-md-4">
            <input type="text" name="search_tag" class="form-control" placeholder="Search by Tag Number" value="<?= htmlspecialchars($search_tag) ?>">
        </div>
        <div class="col-md-2">
            <select name="type" class="form-select">
                <option value="">All Types</option>
                <option value="cow" <?= $typeFilter==='cow' ? 'selected' : '' ?>>Cow</option>
                <option value="goat" <?= $typeFilter==='goat' ? 'selected' : '' ?>>Goat</option>
                <option value="chicken" <?= $typeFilter==='chicken' ? 'selected' : '' ?>>Chicken</option>
            </select>
        </div>
        <div class="col-md-2">
            <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($date_from) ?>" placeholder="From">
        </div>
        <div class="col-md-2">
            <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($date_to) ?>" placeholder="To">
        </div>
        <div class="col-md-2 d-grid">
            <button class="btn btn-primary">Filter</button>
        </div>
    </form>

    <!-- Breeding Records Table -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Tag</th>
                    <th>Type</th>
                    <th>Date Inseminated</th>
                    <th>Pregnancy Result</th>
                </tr>
            </thead>
            <tbody>
                <?php if($result && $result->num_rows>0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['breeding_id'] ?></td>
                        <td><?= htmlspecialchars($row['tag_number']) ?></td>
                        <td><?= ucfirst($row['type']) ?></td>
                        <td><?= $row['date_inseminated'] ?></td>
                        <td><?= ucfirst($row['pregnancy_result']) ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center">No breeding records found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Breeding Record Modal -->
<div class="modal fade" id="addBreedingModal" tabindex="-1" aria-labelledby="addBreedingModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="breeding_records_add.php">
        <div class="modal-header">
          <h5 class="modal-title" id="addBreedingModalLabel">Add Breeding Record</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <label>Livestock</label>
                <select name="livestock_id" class="form-select" required>
                    <?php
                    $livestock_list = $conn->query("SELECT livestock_id, tag_number, type FROM Livestock WHERE status='available'");
                    while($row = $livestock_list->fetch_assoc()): ?>
                        <option value="<?= $row['livestock_id'] ?>">
                            <?= htmlspecialchars($row['tag_number'] . " (" . ucfirst($row['type']) . ")") ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-3">
                <label>Date Inseminated</label>
                <input type="date" name="date_inseminated" class="form-control" required>
            </div>
            <input type="hidden" name="pregnancy_result" value="unknown">
        </div>
        <div class="modal-footer">
          <button type="submit" name="add_breeding" class="btn btn-success w-100">Add Record</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
