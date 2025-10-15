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
$sql = "SELECT hr.health_id, l.livestock_id, l.tag_number, l.type, hr.treatment_date, hr.treatment 
        FROM Health_Records hr 
        JOIN Livestock l ON hr.livestock_id = l.livestock_id
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
    $sql .= " AND hr.treatment_date BETWEEN ? AND ?";
    $params[] = $date_from;
    $params[] = $date_to;
    $types .= "ss";
}

$sql .= " ORDER BY hr.treatment_date DESC";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Fetch livestock for modal dropdown
$livestock_list = $conn->query("SELECT livestock_id, tag_number FROM Livestock WHERE status='available'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Health Records</title>
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
    <h2>Health Records</h2>
    <div class="mb-3 d-flex justify-content-between">
        <div>
            <a href="vet_dashboard.php" class="btn btn-secondary btn-sm">← Dashboard</a>
        </div>
        <div>
            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addHealthModal">+ Add Health Record</button>
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

    <!-- Health Records Table -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Tag</th>
                    <th>Type</th>
                    <th>Treatment Date</th>
                    <th>Treatment</th>
                </tr>
            </thead>
            <tbody>
                <?php if($result && $result->num_rows>0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['health_id'] ?></td>
                        <td><?= htmlspecialchars($row['tag_number']) ?></td>
                        <td><?= ucfirst($row['type']) ?></td>
                        <td><?= $row['treatment_date'] ?></td>
                        <td><?= htmlspecialchars($row['treatment']) ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center">No health records found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Health Record Modal -->
<div class="modal fade" id="addHealthModal" tabindex="-1" aria-labelledby="addHealthModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="health_records_add.php">
        <div class="modal-header">
          <h5 class="modal-title" id="addHealthModalLabel">Add Health Record</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <label>Livestock</label>
                <select name="livestock_id" class="form-select" required>
    <?php 
    // Re-fetch the livestock list if it was consumed earlier
    $livestock_list = $conn->query("SELECT livestock_id, tag_number, type FROM Livestock WHERE status='available'");
    while($row = $livestock_list->fetch_assoc()): ?>
        <option value="<?= $row['livestock_id'] ?>">
            <?= htmlspecialchars($row['tag_number'] . " (" . ucfirst($row['type']) . ")") ?>
        </option>
    <?php endwhile; ?>
</select>

            </div>
            <div class="mb-3">
                <label>Treatment</label>
                <textarea name="treatment" class="form-control" required></textarea>
            </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="add_health" class="btn btn-success w-100">Add Record</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
