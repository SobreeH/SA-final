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

    // Unit: use custom if provided
    $unit = trim($_POST['unit_custom']) !== '' ? $conn->real_escape_string($_POST['unit_custom']) : $conn->real_escape_string($_POST['unit']);

    $farmer_id = $_SESSION['user_id'];

    $sql = "INSERT INTO Supply (supply_name, category, description, quantity, unit, updated_by)
            VALUES ('$name','$category','$description',$quantity,'$unit',$farmer_id)";
    $msg = $conn->query($sql) === TRUE ? "Supply added successfully!" : "Error: ".$conn->error;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add New Supply</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background-color: #f8f9fa; }
.container { max-width: 700px; margin-top: 30px; }
.card { margin-bottom: 20px; }
</style>
</head>
<body>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Add New Supply</h2>
        <a href="farmer_supply.php" class="btn btn-secondary btn-sm">← Back to Supplies</a>
    </div>

    <?php if($msg) echo "<div class='alert alert-success'>$msg</div>"; ?>

    <div class="card">
        <div class="card-header">Supply Information</div>
        <div class="card-body">
            <form method="POST" class="row g-3">
                <div class="col-md-6">
                    <label>Supply Name</label>
                    <input type="text" name="supply_name" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label>Category</label>
                    <select name="category" class="form-select" required>
                        <option value="feed">Feed</option>
                        <option value="medicine">Medicine</option>
                    </select>
                </div>
                <div class="col-12">
                    <label>Description</label>
                    <textarea name="description" class="form-control"></textarea>
                </div>
                <div class="col-md-4">
                    <label>Quantity</label>
                    <input type="number" step="0.01" name="quantity" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label>Unit</label>
                    <select name="unit" id="unitSelect" class="form-select">
                        <option value="kg">kg</option>
                        <option value="bottles">bottles</option>
                        <option value="bags">bags</option>
                        <option value="custom">Custom...</option>
                    </select>
                    <input type="text" name="unit_custom" id="unitCustom" class="form-control mt-1" placeholder="Enter custom unit" style="display:none;">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" name="add_supply" class="btn btn-success w-100">Add Supply</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const unitSelect = document.getElementById('unitSelect');
const unitCustom = document.getElementById('unitCustom');

unitSelect.addEventListener('change', () => {
    if (unitSelect.value === 'custom') {
        unitCustom.style.display = 'block';
        unitCustom.focus();
    } else {
        unitCustom.style.display = 'none';
    }
});
</script>
</body>
</html>
