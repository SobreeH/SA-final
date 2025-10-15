<?php
include '../connDB.php';
include '../session_check.php';
require_role('farmer');

$msg = '';

// Handle new supply addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_supply'])) {
    $name = $conn->real_escape_string($_POST['supply_name']);
    $category = $_POST['category'];
    $description = $conn->real_escape_string($_POST['description']);
    $quantity = (float)$_POST['quantity'];
    $unit = $conn->real_escape_string($_POST['unit']);
    $reorder = (float)$_POST['reorder_level'];
    $farmer_id = $_SESSION['user_id'];

    $sql = "INSERT INTO Supply (supply_name, category, description, quantity, unit, reorder_level, updated_by)
            VALUES ('$name','$category','$description',$quantity,'$unit',$reorder,$farmer_id)";
    $msg = $conn->query($sql) === TRUE ? "Supply added successfully!" : "Error: ".$conn->error;
}

// Fetch all supplies for this farmer
$result = $conn->query("SELECT * FROM Supply WHERE updated_by=".$_SESSION['user_id']." ORDER BY supply_id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Farmer Supplies</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
    <h2>Manage Supplies</h2>
    <?php if($msg) echo "<div class='alert alert-info'>$msg</div>"; ?>

    <div>
    <a href="farmer_dashboard.php" >back</a>
</div>
    <div class="card mb-4">
        <div class="card-header">Add New Supply</div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label>Supply Name</label>
                    <input type="text" name="supply_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Category</label>
                    <select name="category" class="form-select" required>
                        <option value="feed">Feed</option>
                        <option value="medicine">Medicine</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label>Description</label>
                    <textarea name="description" class="form-control"></textarea>
                </div>
                <div class="mb-3">
                    <label>Quantity</label>
                    <input type="number" step="0.01" name="quantity" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Unit</label>
                    <input type="text" name="unit" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Reorder Level</label>
                    <input type="number" step="0.01" name="reorder_level" class="form-control" required>
                </div>
                <button type="submit" name="add_supply" class="btn btn-success">Add Supply</button>
            </form>
        </div>
    </div>

    <h4>Your Supplies</h4>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th><th>Name</th><th>Category</th><th>Description</th><th>Qty</th><th>Unit</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['supply_id']; ?></td>
                <td><?php echo htmlspecialchars($row['supply_name']); ?></td>
                <td><?php echo ucfirst($row['category']); ?></td>
                <td><?php echo htmlspecialchars($row['description']); ?></td>
                <td><?php echo $row['quantity']; ?></td>
                <td><?php echo htmlspecialchars($row['unit']); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
