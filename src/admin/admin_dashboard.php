<?php
require_once "../connDB.php";
require_once "../auth.php";

// Fetch summary data
$totalFarmers = $conn->query("SELECT COUNT(*) AS count FROM Farmer")->fetch_assoc()['count'] ?? 0;
$totalVets = $conn->query("SELECT COUNT(*) AS count FROM Veterinarian")->fetch_assoc()['count'] ?? 0;
$totalCustomers = $conn->query("SELECT COUNT(*) AS count FROM Customer")->fetch_assoc()['count'] ?? 0;
$totalLivestock = $conn->query("SELECT COUNT(*) AS count FROM Livestock")->fetch_assoc()['count'] ?? 0;

$livestockByType = $conn->query("SELECT type, COUNT(*) AS count FROM Livestock GROUP BY type");

$totalSales = $conn->query("SELECT COUNT(*) AS count FROM Sales")->fetch_assoc()['count'] ?? 0;
$totalRevenue = $conn->query("SELECT SUM(price) AS total FROM Sales")->fetch_assoc()['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: #f7f9fb; font-family: system-ui,-apple-system,"Segoe UI",Roboto,Arial; color: #1f2937; }
.container { max-width: 1100px; margin: 32px auto; }
.card-summary { border-radius: 8px; padding: 20px; text-align: center; background: #fff; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
.card-summary h5 { font-weight: 600; margin-bottom: 10px; color: #2f3640; }
.card-summary p { font-size: 1.5rem; margin: 0; color: #273c75; }
.btn-sm { font-size: 0.85rem; }
.table td, .table th { vertical-align: middle; }
</style>
</head>
<body>
<div class="container">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Admin Dashboard</h1>
        <div>
            <a href="manage_users.php" class="btn btn-primary btn-sm me-1">Manage Users</a>
            <a href="admin_reports.php" class="btn btn-success btn-sm me-1">Sales Report</a>
            <a href="../logout.php" class="btn btn-secondary btn-sm">Logout</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4 col-lg-2">
            <div class="card-summary">
                <h5>Farmers</h5>
                <p><?= $totalFarmers ?></p>
            </div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="card-summary">
                <h5>Veterinarians</h5>
                <p><?= $totalVets ?></p>
            </div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="card-summary">
                <h5>Customers</h5>
                <p><?= $totalCustomers ?></p>
            </div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="card-summary">
                <h5>Livestock</h5>
                <p><?= $totalLivestock ?></p>
            </div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="card-summary">
                <h5>Sales</h5>
                <p><?= $totalSales ?></p>
            </div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="card-summary">
                <h5>Total Revenue (THB)</h5>
                <p><?= number_format($totalRevenue, 2) ?></p>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-light fw-bold">Livestock Breakdown by Type</div>
        <div class="card-body table-responsive">
            <table class="table table-striped table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Type</th>
                        <th>Count</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $livestockByType->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars(ucfirst($row['type'])) ?></td>
                            <td><?= htmlspecialchars($row['count']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
