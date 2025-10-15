<?php
include '../connDB.php';
include '../session_check.php';
require_role('farmer');

$msg = '';

// Get the supply ID from query
$supply_id = (int)($_GET['id'] ?? 0);
if ($supply_id <= 0) {
    die("Invalid supply ID.");
}

// Fetch existing supply
$res = $conn->query("SELECT * FROM Supply WHERE supply_id=$supply_id AND updated_by=".$_SESSION['user_id']);
if (!$res || $res->num_rows === 0) {
    die("Supply not found or you don't have permission.");
}
$supply = $res->fetch_assoc();

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_supply'])) {
    $name = $conn->real_escape_string($_POST['supply_name']);
    $category = $_POST['category'];
    $description = $conn->real_escape_string($_POST['description']);
    $quantity = (float)$_POST['quantity'];
    $unit = $conn->real_escape_string($_POST['unit']);

    $sql = "UPDATE Supply SET
                supply_name='$name',
                category='$category',
                description='$description',
                quantity=$quantity,
                unit='$unit'
            WHERE supply_id=$supply_id AND updated_by=".$_SESSION['user_id'];

    if ($conn->query($sql) === TRUE) {
        header("Location: farmer_supply.php?msg=" . urlencode("Supply updated successfully!"));
        exit;
    } else {
        $msg = "Error updating supply: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Supply</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
    <h2>Edit Supply</h2>
    <?php if($msg) echo "<div class='alert alert-danger'>$msg</div>"; ?>

    <div class="mb-3">
        <a href="farmer_supply.php" class="btn btn-secondary btn-sm">← Back to Supplies</a>
    </div>

    <div class="card mb-4">
        <div class="card-header">Update Supply</div>
        <div class="card-body">
            <form method="POST" class="row g-3">
                <div class="col-md-6">
                    <label>Supply Name</label>
                    <input type="text" name="supply_name" class="form-control" required value="<?= htmlspecialchars($supply['supply_name']) ?>">
                </div>
                <div class="col-md-6">
                    <label>Category</label>
                    <select name="category" class="form-select" required>
                        <option value="feed" <?= $supply['category']=='feed'?'selected':'' ?>>Feed</option>
                        <option value="medicine" <?= $supply['category']=='medicine'?'selected':'' ?>>Medicine</option>
                    </select>
                </div>
                <div class="col-12">
                    <label>Description</label>
                    <textarea name="description" class="form-control"><?= htmlspecialchars($supply['description']) ?></textarea>
                </div>
                <div class="col-md-4">
                    <label>Quantity</label>
                    <input type="number" step="0.01" name="quantity" class="form-control" required value="<?= $supply['quantity'] ?>">
                </div>
                <div class="col-md-4">
                    <label>Unit</label>
                    <input type="text" name="unit" class="form-control" required value="<?= htmlspecialchars($supply['unit']) ?>">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" name="update_supply" class="btn btn-primary w-100">Update Supply</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
