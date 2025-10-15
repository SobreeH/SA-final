<?php
include '../connDB.php';
include '../session_check.php';
require_role('farmer');

// Get search/filter values
$search = trim($_GET['search'] ?? '');
$typeFilter = trim($_GET['type'] ?? '');

// Build SQL with optional filters
$sql = "
SELECT 
    l.livestock_id, l.tag_number, l.type, l.breed, l.gender, l.weight, l.status, l.image, l.date_added,
    b.pregnancy_result
FROM Livestock l
LEFT JOIN Breeding_Records b 
    ON l.livestock_id = b.livestock_id
    AND b.breeding_id = (
        SELECT MAX(b2.breeding_id) 
        FROM Breeding_Records b2 
        WHERE b2.livestock_id = l.livestock_id
    )
WHERE 1=1
";

$params = [];
$types = '';

if ($search !== '') {
    $sql .= " AND (l.tag_number LIKE ? OR l.breed LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "ss";
}

if ($typeFilter !== '' && in_array($typeFilter, ['cow','goat','chicken'], true)) {
    $sql .= " AND l.type = ?";
    $params[] = $typeFilter;
    $types .= "s";
}

$sql .= " ORDER BY l.date_added DESC";

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
h2 { margin-bottom: 1rem; }
img.livestock-img { width: 80px; height: 80px; object-fit: cover; border-radius: 6px; }
.table td, .table th { vertical-align: middle; }
.table-responsive { overflow-x: auto; }
.btn { min-width: 80px; }
@media(max-width:768px){ 
    .table-responsive { font-size: 0.9rem; }
    .btn { font-size: 0.85rem; padding: 6px 10px; }
}
</style>
</head>
<body>
<div class="container">
    <h2>Manage Livestock</h2>

    <div class="mb-3 d-flex flex-wrap gap-2">
        <a href="farmer_dashboard.php" class="btn btn-secondary">← Dashboard</a>
        <a href="farmer_livestock_insert.php" class="btn btn-success">+ Add Livestock</a>
    </div>

    <!-- Search & Filter -->
    <form class="row g-2 mb-3" method="get" action="">
        <div class="col-md-6">
            <input type="text" name="search" class="form-control" placeholder="Search by Tag or Breed" value="<?= htmlspecialchars($search) ?>" aria-label="Search by tag or breed">
        </div>
        <div class="col-md-3">
            <select name="type" class="form-select" aria-label="Filter by livestock type">
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
        <table class="table table-bordered table-striped">
            <thead class="table-light">
                <tr>
                    <th scope="col">Image</th>
                    <th scope="col">Tag</th>
                    <th scope="col">Type</th>
                    <th scope="col">Breed</th>
                    <th scope="col">Gender</th>
                    <th scope="col">Weight (kg)</th>
                    <th scope="col">Status</th>
                    <th scope="col">Pregnancy</th>
                    <th scope="col">Date Added</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if($result && $result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td>
                        <?php if($row['image']): ?>
                            <img src="<?= htmlspecialchars($row['image']); ?>" alt="Livestock Image" class="livestock-img">
                        <?php else: ?>
                            <span aria-label="No image">-</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($row['tag_number']); ?></td>
                    <td><?= ucfirst($row['type']); ?></td>
                    <td><?= htmlspecialchars($row['breed']); ?></td>
                    <td><?= ucfirst($row['gender'] ?? '-') ?></td>
                    <td><?= $row['weight'] ?? '-'; ?></td>
                    <td><?= ucfirst($row['status']); ?></td>
                    <td>
                        <?php 
                        switch($row['pregnancy_result'] ?? 'unknown') {
                            case 'pregnant': echo '<span class="text-success">Pregnant</span>'; break;
                            case 'not_pregnant': echo '<span class="text-secondary">Not Pregnant</span>'; break;
                            default: echo '<span class="text-muted">Unknown</span>'; break;
                        }
                        ?>
                    </td>
                    <td><?= $row['date_added']; ?></td>
                    <td class="d-flex gap-1 flex-wrap">
                        <a href="farmer_livestock_edit.php?id=<?= $row['livestock_id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                        <a href="farmer_livestock_delete.php?id=<?= $row['livestock_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this livestock?');">Delete</a>
                        <?php if(($row['gender'] ?? '') === 'female'): ?>
                            <a href="farmer_livestock_confirm_preg.php?id=<?= $row['livestock_id']; ?>&status=pregnant" class="btn btn-success btn-sm">Pregnant</a>
                            <a href="farmer_livestock_confirm_preg.php?id=<?= $row['livestock_id']; ?>&status=not_pregnant" class="btn btn-secondary btn-sm">Not Pregnant</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="10" class="text-center">No livestock found</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
