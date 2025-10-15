<?php
include '../connDB.php';
include '../session_check.php';
require_role('vet');

// Fetch breeding records
$sql = "SELECT br.breeding_id, l.tag_number, l.type, br.date_inseminated, br.pregnancy_result
        FROM Breeding_Records br
        JOIN Livestock l ON br.livestock_id = l.livestock_id
        ORDER BY br.date_inseminated DESC";
$result = $conn->query($sql);

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_breeding'])) {
    $livestock_id = (int)$_POST['livestock_id'];
    $pregnancy_result = $_POST['pregnancy_result'];
    $vet_id = $_SESSION['user_id'];
    $date_inseminated = $_POST['date_inseminated'];

    $sql_insert = "INSERT INTO Breeding_Records (livestock_id, vet_id, date_inseminated, pregnancy_result)
                   VALUES ($livestock_id, $vet_id, '$date_inseminated', '$pregnancy_result')";
    $msg = $conn->query($sql_insert) === TRUE ? "Breeding record added!" : "Error: ".$conn->error;
}

// Fetch livestock for dropdown
$livestock_list = $conn->query("SELECT livestock_id, tag_number FROM Livestock WHERE status='available'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Breeding Records</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
    <h2>Breeding Records</h2>
    <?php if ($msg) echo "<div class='alert alert-info'>$msg</div>"; ?>
<div>
    <a href="vet_dashboard.php" >back</a>
</div>
    <div class="card mb-4">
        <div class="card-header">Add New Breeding Record</div>
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
                    <label>Date Inseminated</label>
                    <input type="date" name="date_inseminated" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Pregnancy Result</label>
                    <select name="pregnancy_result" class="form-select" required>
                        <option value="pregnant">Pregnant</option>
                        <option value="not_pregnant">Not Pregnant</option>
                        <option value="unknown">Unknown</option>
                    </select>
                </div>
                <button type="submit" name="add_breeding" class="btn btn-success">Add Record</button>
            </form>
        </div>
    </div>

    <h4>Existing Records</h4>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th><th>Tag Number</th><th>Type</th><th>Date Inseminated</th><th>Pregnancy Result</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['breeding_id']; ?></td>
                <td><?php echo htmlspecialchars($row['tag_number']); ?></td>
                <td><?php echo htmlspecialchars($row['type']); ?></td>
                <td><?php echo $row['date_inseminated']; ?></td>
                <td><?php echo ucfirst($row['pregnancy_result']); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
