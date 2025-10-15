<?php
include '../connDB.php';
include '../session_check.php';
require_role('farmer');

// Get search/filter values
$search = trim($_GET['search'] ?? '');
$typeFilter = trim($_GET['type'] ?? '');

// Build SQL with optional filters
$sql = "SELECT livestock_id, tag_number, type, breed, weight, status, image, date_added 
        FROM Livestock WHERE 1=1";

$params = [];
$types = '';

if ($search !== '') {
    $sql .= " AND (tag_number LIKE ? OR breed LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "ss";
}

if ($typeFilter !== '' && in_array($typeFilter, ['cow','goat','chicken'], true)) {
    $sql .= " AND type = ?";
    $params[] = $typeFilter;
    $types .= "s";
}

$sql .= " ORDER BY date_added DESC";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Livestock</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: #f7f9fb; font-family: system-ui,-apple-system,"Segoe UI",Roboto,Arial; color: #1f2937; }
.container { max-width: 960px; margin: 32px auto; }
img.livestock-img { width: 80px; height: 80px; object-fit: cover; border-radius: 8px; }
@media(max-width:768px){ .table-responsive { font-size: 0.9rem; } }
.btn-sm { font-size: 0.8rem; padding: 4px 8px; }
</style>
</head>
<body>
<div class="container">
    <h2>Manage Livestock</h2>
    <div class="mb-3">
        <a href="farmer_dashboard.php" class="btn btn-secondary btn-sm">← Dashboard</a>
        <a href="farmer_livestock_insert.php" class="btn btn-success btn-sm">+ Add Livestock</a>
    </div>

    <!-- Search & Filter Form -->
    <form class="row g-2 mb-3" method="get" action="">
        <div class="col-md-6">
            <input type="text" name="search" class="form-control" placeholder="Search by Tag or Breed" value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-3">
            <select name="type" class="form-select">
                <option value="">All Types</option>
                <option value="cow" <?= $typeFilter==='cow' ? 'selected' : '' ?>>Cow</option>
                <option value="goat" <?= $typeFilter==='goat' ? 'selected' : '' ?>>Goat</option>
                <option value="chicken" <?= $typeFilter==='chicken' ? 'selected' : '' ?>>Chicken</option>
            </select>
        </div>
        <div class="col-md-3 d-grid">
            <button class="btn btn-primary" type="submit">Filter</button>
        </div>
    </form>

    <div class="table-responsive">
    <table class="table table-bordered table-striped align-middle">
        <thead class="table-light">
            <tr>
                <th>Image</th>
                <th>Tag</th>
                <th>Type</th>
                <th>Breed</th>
                <th>Weight (kg)</th>
                <th>Status</th>
                <th>Date Added</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td>
                    <?php if($row['image']): ?>
                        <img src="<?= htmlspecialchars($row['image']); ?>" class="livestock-img">
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($row['tag_number']); ?></td>
                <td><?= ucfirst($row['type']); ?></td>
                <td><?= htmlspecialchars($row['breed']); ?></td>
                <td><?= $row['weight'] ?? '-'; ?></td>
                <td><?= ucfirst($row['status']); ?></td>
                <td><?= $row['date_added']; ?></td>
                <td>
                    <a href="farmer_livestock_edit.php?id=<?= $row['livestock_id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                    <a href="farmer_livestock_delete.php?id=<?= $row['livestock_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?');">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="8" class="text-center">No livestock found</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>
</body>
</html>
