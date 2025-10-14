<?php
include 'connDB.php';

// Fetch all available livestock
$sql = "SELECT livestock_id, tag_number, type, breed, weight, date_added 
        FROM Livestock 
        WHERE status='available'
        ORDER BY date_added DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Available Livestock</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4">
    <h2>Available Livestock</h2>
    <p>Browse the livestock currently available for sale.</p>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tag Number</th>
                <th>Type</th>
                <th>Breed</th>
                <th>Weight (kg)</th>
                <th>Date Added</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['livestock_id']; ?></td>
                    <td><?php echo htmlspecialchars($row['tag_number']); ?></td>
                    <td><?php echo htmlspecialchars($row['type']); ?></td>
                    <td><?php echo htmlspecialchars($row['breed']); ?></td>
                    <td><?php echo $row['weight']; ?></td>
                    <td><?php echo $row['date_added']; ?></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center">No livestock available at the moment.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <a href="login.php" class="btn btn-primary">Login as Farmer or Vet</a>
</div>

</body>
</html>
