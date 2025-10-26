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
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
    body {
        background-size: 60px 60px;
        background-repeat: repeat;
        font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial;
        color: #1f2937;
    }

    .container {
        max-width: 1100px;
        margin: 32px auto;
    }

    /* Dark theme summary cards */
    .card-summary {
        border-radius: 0 !important;
        padding: 20px;
        text-align: center;
        background: #312f2e !important;
        border: 4px solid #3d3938 !important;
        border-bottom: 4px solid #000 !important;
        border-left: 4px solid #000 !important;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
        transition: all 0.2s;
    }

    .card-summary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(255, 255, 255, 0.1);
    }

    .card-summary h5 {
        font-weight: 600;
        margin-bottom: 10px;
        color: #94878e !important;
    }

    .card-summary p {
        font-size: 1.5rem;
        margin: 0;
        color: #7bc05a !important;
        font-weight: bold;
    }

    .btn-sm {
        font-size: 0.85rem;
    }

    .table td,
    .table th {
        vertical-align: middle;
    }

    /* Dark theme table and card styles */
    .dark-card {
        background-color: #312f2e !important;
        border: 4px solid #3d3938 !important;
        border-bottom: 4px solid #000 !important;
        border-left: 4px solid #000 !important;
        border-radius: 0 !important;
        color: #ede5e2 !important;
    }

    .dark-card .card-header {
        background-color: #d0c5c0 !important;
        color: #262423 !important;
        border-bottom: 3px solid #a69e9a !important;
        border-radius: 0 !important;
        text-align: center;
        font-weight: bold;
    }

    .dark-table {
        background-color: #0e0d0d !important;
        border: 4px solid #000 !important;
        border-bottom: 4px solid #272626 !important;
        border-right: 4px solid #272626 !important;
        border-collapse: separate !important;
        border-spacing: 0 !important;
    }

    .dark-table thead {
        background-color: #312f2e !important;
    }

    .dark-table thead th {
        background-color: #312f2e !important;
        color: #ede5e2 !important;
        border: 1px solid #272626 !important;
        border-top: 2px solid #272626 !important;
        border-bottom: 2px solid #272626 !important;
    }

    .dark-table tbody {
        background-color: #0e0d0d !important;
    }

    .dark-table tbody tr {
        background-color: #0e0d0d !important;
    }

    .dark-table tbody td {
        background-color: #0e0d0d !important;
        color: #ede5e2 !important;
        border: 1px solid #272626 !important;
    }
    </style>
</head>

<body class="!bg-[#171615]">
    <div class="container">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 text-white">Admin Dashboard</h1>
            <div>
                <a href="manage_users.php"
                    class="btn hover:!bg-[#005eea] !text-[#fff] !bg-[#0d6efd] !border-t-5 !border-b-5 !border-t-[#609ffd] !border-b-[#0052cd] !rounded-none me-1">Manage
                    Users</a>
                <a href="admin_reports.php"
                    class="btn hover:!bg-[#367723] text-white !bg-[#3c8527] !border-t-5 !border-b-5 !border-t-[#52a535] !border-b-[#2a641c] !rounded-none me-1">Sales
                    Report</a>
                <a href="../logout.php"
                    class="btn hover:!bg-[#d9d1cd] text-[#262423] !bg-[#d0c5c0] !border-t-5 !border-b-5 !border-t-[#ede5e2] !border-b-[#aba09c] !rounded-none">Logout</a>
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

        <div class="card dark-card mb-4">
            <div class="card-header">Livestock Breakdown by Type</div>
            <div class="card-body table-responsive">
                <table
                    class="table dark-table !bg-[#0e0d0d] !border-t-4 !border-r-4 !border-b-4 !border-l-4 !border-t-black !border-r-black !border-b-[#272626] !border-l-[#272626]"
                    style="background-color: #0e0d0d !important;">
                    <thead class="!bg-[#312f2e] !text-[#ede5e2]"
                        style="background-color: #312f2e !important; color: #ede5e2 !important;">
                        <tr>
                            <th style="color: #ede5e2 !important; border-color: #272626 !important;">Type</th>
                            <th style="color: #ede5e2 !important; border-color: #272626 !important;">Count</th>
                        </tr>
                    </thead>
                    <tbody class="!bg-[#0e0d0d]" style="background-color: #0e0d0d !important;">
                        <?php while($row = $livestockByType->fetch_assoc()): ?>
                        <tr class="!bg-[#0e0d0d] !text-[#ede5e2]"
                            style="background-color: #0e0d0d !important; color: #ede5e2 !important;">
                            <td style="border-color: #272626 !important; color: #ede5e2 !important;">
                                <?= htmlspecialchars(ucfirst($row['type'])) ?></td>
                            <td style="border-color: #272626 !important; color: #ede5e2 !important;">
                                <?= htmlspecialchars($row['count']) ?></td>
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