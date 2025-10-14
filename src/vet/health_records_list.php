<?php
include '../connDB.php';
include '../session_check.php';
require_role('vet');

// Fetch health records
$sql = "SELECT hr.health_id, l.tag_number, l.type, hr.treatment_date, hr.treatment 
        FROM Health_Records hr 
        JOIN Livestock l ON hr.livestock_id = l.livestock_id
        ORDER BY hr.treatment_date DESC";
$result = $conn->query($sql);

$msg = '';
// Optional: handle adding new record
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_health'])) {
    $livestock_id = (int)$_POST['livestock_id'];
    $treatment = $conn->real_escape_string($_POST['treatment']);
    $vet_id = $_SESSION['user_id'];

    $sql_insert = "INSERT INTO Health_Records (livestock_id, vet_id, treatment) 
                   VALUES ($livestock_id, $vet_id, '$treatment')";
    $msg = $conn->query($sql_insert) === TRUE ? "Health record added!" : "Error: ".$conn->error;
}

// Fetch livestock for dropdown
$livestock_list = $conn->query("SELECT livestock_id, tag_number FROM Livestock WHERE status='available'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Health Records</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
    <h2>Health Records</h2>
    <?php if ($msg) echo "<div class='alert alert-info'>$msg</div>"; ?>

    <div class="card mb-4">
        <div class="card-header">Add New Health Record</div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label>Livestock</label>
                    <select name="livestock_id" class="form-select" required>
                        <?php while($row = $livestock_list->fetch_assoc()): ?>
                            <option value="<?php echo $row['livestock_id']; ?>"><?php echo htmlspecialchars($row['tag_number']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label>Treatment</label>
                    <textarea name="treatment" class="form-control" required></textarea>
                </div>
                <button type="submit" name="add_health" class="btn btn-success">Add Record</button>
            </form>
        </div>
    </div>

    <h4>Existing Records</h4>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th><th>Tag Number</th><th>Type</th><th>Treatment Date</th><th>Treatment</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['health_id']; ?></td>
                <td><?php echo htmlspecialchars($row['tag_number']); ?></td>
                <td><?php echo htmlspecialchars($row['type']); ?></td>
                <td><?php echo $row['treatment_date']; ?></td>
                <td><?php echo htmlspecialchars($row['treatment']); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
