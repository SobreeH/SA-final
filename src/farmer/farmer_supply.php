<?php
include '../connDB.php';
include '../session_check.php';
require_role('farmer');

$msg = '';

// Handle resupply (increase quantity)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resupply_id'])) {
    $resupply_id = (int)$_POST['resupply_id'];
    $resupply_qty = (float)$_POST['resupply_qty'];
    if ($resupply_qty > 0) {
        $conn->query("UPDATE Supply SET quantity = quantity + $resupply_qty WHERE supply_id=$resupply_id AND updated_by=".$_SESSION['user_id']);
        $msg = "Supply updated successfully!";
    }
}

// Handle use supply (decrease quantity)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['use_id'])) {
    $use_id = (int)$_POST['use_id'];
    $use_qty = (float)$_POST['use_qty'];

    // Fetch current quantity
    $cur = $conn->query("SELECT quantity FROM Supply WHERE supply_id=$use_id AND updated_by=".$_SESSION['user_id']);
    if($cur && $cur->num_rows>0){
        $current_qty = $cur->fetch_assoc()['quantity'];
        if($use_qty > 0 && $use_qty <= $current_qty){
            $conn->query("UPDATE Supply SET quantity = quantity - $use_qty WHERE supply_id=$use_id AND updated_by=".$_SESSION['user_id']);
            $msg = "Supply used successfully!";
        } else {
            $msg = "Invalid quantity to use.";
        }
    }
}

// Handle search/filter
$search_name = $_GET['search_name'] ?? '';
$filter_category = $_GET['filter_category'] ?? '';

$where = [];
if ($search_name !== '') {
    $search_name_esc = $conn->real_escape_string($search_name);
    $where[] = "supply_name LIKE '%$search_name_esc%'";
}
if ($filter_category !== '') {
    $filter_category_esc = $conn->real_escape_string($filter_category);
    $where[] = "category='$filter_category_esc'";
}

$where_sql = $where ? " AND " . implode(' AND ', $where) : '';
$result = $conn->query("SELECT * FROM Supply WHERE updated_by=".$_SESSION['user_id'].$where_sql." ORDER BY supply_id DESC");

// Handle delete action
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    $conn->query("DELETE FROM Supply WHERE supply_id=$delete_id AND updated_by=".$_SESSION['user_id']);
    header("Location: farmer_supply.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Farmer Supplies</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background-color: #f8f9fa; }
.container { max-width: 1000px; margin-top: 30px; }
.card { margin-bottom: 20px; }
.table td, .table th { vertical-align: middle; }
.btn-sm { font-size: 0.8rem; padding: 4px 8px; }
</style>
</head>
<body>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Manage Supplies</h2>
        <div>
            <a href="farmer_dashboard.php" class="btn btn-secondary btn-sm">← Dashboard</a>
            <a href="farmer_supply_add.php" class="btn btn-success btn-sm">+ Add New Supply</a>
        </div>
    </div>

    <?php if($msg) echo "<div class='alert alert-success'>$msg</div>"; ?>

    <!-- Search / Filter -->
    <div class="card mb-3">
        <div class="card-header">Search / Filter Supplies</div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-6">
                    <input type="text" name="search_name" placeholder="Search by name" class="form-control" value="<?= htmlspecialchars($search_name) ?>">
                </div>
                <div class="col-md-4">
                    <select name="filter_category" class="form-select">
                        <option value="">All Categories</option>
                        <option value="feed" <?= $filter_category==='feed'?'selected':'' ?>>Feed</option>
                        <option value="medicine" <?= $filter_category==='medicine'?'selected':'' ?>>Medicine</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Supplies Table -->
    <div class="card">
        <div class="card-header">Your Supplies</div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th><th>Name</th><th>Category</th><th>Description</th><th>Qty</th><th>Unit</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($result && $result->num_rows>0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['supply_id'] ?></td>
                            <td><?= htmlspecialchars($row['supply_name']) ?></td>
                            <td><?= ucfirst($row['category']) ?></td>
                            <td><?= htmlspecialchars($row['description']) ?></td>
                            <td><?= $row['quantity'] ?></td>
                            <td><?= htmlspecialchars($row['unit']) ?></td>
                            <td>
                                <!-- Resupply Form -->
                                <form method="POST" class="d-flex mb-1">
                                    <input type="hidden" name="resupply_id" value="<?= $row['supply_id'] ?>">
                                    <input type="number" step="0.01" name="resupply_qty" class="form-control form-control-sm me-1" placeholder="+Qty" required>
                                    <button type="submit" class="btn btn-success btn-sm">Add</button>
                                </form>
                                <!-- Use Supply Form -->
                                <form method="POST" class="d-flex mb-1">
                                    <input type="hidden" name="use_id" value="<?= $row['supply_id'] ?>">
                                    <input type="number" step="0.01" max="<?= $row['quantity'] ?>" name="use_qty" class="form-control form-control-sm me-1" placeholder="-Qty" required>
                                    <button type="submit" class="btn btn-warning btn-sm">Use</button>
                                </form>
                                <!-- Edit/Delete -->
                                <div class="mt-1">
                                    <a href="farmer_supply_edit.php?id=<?= $row['supply_id'] ?>" class="btn btn-primary btn-sm mt-1">Edit</a>
                                    <a href="farmer_supply.php?delete_id=<?= $row['supply_id'] ?>" class="btn btn-danger btn-sm mt-1" onclick="return confirm('Are you sure you want to delete this supply?');">Delete</a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center">No supplies found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
