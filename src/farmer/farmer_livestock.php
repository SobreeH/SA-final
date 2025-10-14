<?php
include '../connDB.php';
include '../session_check.php';
require_role('farmer');

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_livestock'])) {
    $tag_number = $conn->real_escape_string($_POST['tag_number']);
    $type = $_POST['type'];
    $breed = $conn->real_escape_string($_POST['breed']);
    $weight = (float)$_POST['weight'];

    $sql = "INSERT INTO Livestock (tag_number, type, breed, weight) VALUES ('$tag_number','$type','$breed',$weight)";
    $msg = $conn->query($sql) === TRUE ? "Livestock added successfully!" : "Error: ".$conn->error;
}

if (isset($_GET['mark_sold'])) {
    $id = (int)$_GET['mark_sold'];
    $conn->query("UPDATE Livestock SET status='sold' WHERE livestock_id=$id");
    $msg = "Livestock marked as sold!";
}

$result = $conn->query("SELECT * FROM Livestock ORDER BY date_added DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Livestock</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
    <h2>Livestock Management</h2>
    <?php if ($msg) echo "<div class='alert alert-info'>$msg</div>"; ?>
    <div class="card mb-4">
        <div class="card-header">Add New Livestock</div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label>Tag Number</label>
                    <input type="text" name="tag_number" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Type</label>
                    <select name="type" class="form-select" required>
                        <option value="cow">Cow</option>
                        <option value="chicken">Chicken</option>
                        <option value="goat">Goat</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label>Breed</label>
                    <input type="text" name="breed" class="form-control">
                </div>
                <div class="mb-3">
                    <label>Weight (kg)</label>
                    <input type="number" step="0.01" name="weight" class="form-control">
                </div>
                <button type="submit" name="add_livestock" class="btn btn-success">Add</button>
            </form>
        </div>
    </div>

    <h4>Existing Livestock</h4>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th><th>Tag</th><th>Type</th><th>Breed</th><th>Weight</th><th>Status</th><th>Date Added</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['livestock_id']; ?></td>
                <td><?php echo htmlspecialchars($row['tag_number']); ?></td>
                <td><?php echo htmlspecialchars($row['type']); ?></td>
                <td><?php echo htmlspecialchars($row['breed']); ?></td>
                <td><?php echo $row['weight']; ?></td>
                <td><?php echo ucfirst($row['status']); ?></td>
                <td><?php echo $row['date_added']; ?></td>
                <td>
                    <?php if($row['status']=='available'): ?>
                    <a href="?mark_sold=<?php echo $row['livestock_id']; ?>" class="btn btn-sm btn-warning">Mark as Sold</a>
                    <?php else: ?>
                    <span class="text-muted">Sold</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
