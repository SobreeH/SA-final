<?php
require_once "../connDB.php";
require_once "../auth.php";



// Fetch summary data
$totalFarmers = $conn->query("SELECT COUNT(*) AS count FROM Farmer")->fetch_assoc()['count'] ?? 0;
$totalVets = $conn->query("SELECT COUNT(*) AS count FROM Veterinarian")->fetch_assoc()['count'] ?? 0;
$totalCustomers = $conn->query("SELECT COUNT(*) AS count FROM Customer")->fetch_assoc()['count'] ?? 0;
$totalLivestock = $conn->query("SELECT COUNT(*) AS count FROM Livestock")->fetch_assoc()['count'] ?? 0;

$livestockByType = $conn->query("
    SELECT type, COUNT(*) AS count 
    FROM Livestock 
    GROUP BY type
");

$totalSales = $conn->query("SELECT COUNT(*) AS count FROM Sales")->fetch_assoc()['count'] ?? 0;
$totalRevenue = $conn->query("
    SELECT SUM(price) AS total 
    FROM Sales 
    WHERE payment_status = 'paid'
")->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f6fa; margin: 0; padding: 0; }
        .container { max-width: 900px; margin: 40px auto; background: #fff; padding: 20px; border-radius: 8px; }
        h1 { text-align: center; color: #333; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 30px; }
        .card { background: #f1f2f6; padding: 15px; border-radius: 8px; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .card h3 { margin: 0; color: #2f3640; }
        .card p { font-size: 1.5em; margin: 10px 0 0; color: #273c75; }
        .logout { text-align: right; margin-bottom: 10px; }
        a.button { background: #2f3640; color: white; padding: 8px 16px; border-radius: 4px; text-decoration: none; }
        a.button:hover { background: #353b48; }
        table { width: 100%; margin-top: 20px; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
        th { background: #f1f2f6; }
    </style>
</head>
<body>

<div class="container">
    <div class="logout">
        <a href="manage_users.php" class="button">Manage Users</a>
        <a href="admin_reports.php" class="button">Sales Report</a>
        <a href="../logout.php" class="button">Logout</a>
    </div>

    <h1>Admin Dashboard Overview</h1>

    <div class="stats">
        <div class="card">
            <h3>Farmers</h3>
            <p><?= $totalFarmers ?></p>
        </div>
        <div class="card">
            <h3>Veterinarians</h3>
            <p><?= $totalVets ?></p>
        </div>
        <div class="card">
            <h3>Customers</h3>
            <p><?= $totalCustomers ?></p>
        </div>
        <div class="card">
            <h3>Livestock</h3>
            <p><?= $totalLivestock ?></p>
        </div>
        <div class="card">
            <h3>Sales</h3>
            <p><?= $totalSales ?></p>
        </div>
        <div class="card">
            <h3>Total Revenue (THB)</h3>
            <p><?= number_format($totalRevenue, 2) ?></p>
        </div>
    </div>

    <h2 style="margin-top: 40px;">Livestock Breakdown by Type</h2>
    <table>
        <tr><th>Type</th><th>Count</th></tr>
        <?php while($row = $livestockByType->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['type']) ?></td>
            <td><?= htmlspecialchars($row['count']) ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

</body>
</html>
