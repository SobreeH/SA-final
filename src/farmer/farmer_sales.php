<?php
include '../connDB.php';
include '../session_check.php';
require_role('farmer');

// Fetch sales with optional search
$search_name = $_GET['search_name'] ?? '';
$where = [];
if ($search_name !== '') {
    $search_name_esc = $conn->real_escape_string($search_name);
    $where[] = "c.customer_name LIKE '%$search_name_esc%'";
}
$where_sql = $where ? "WHERE ".implode(' AND ', $where) : '';

$sales = $conn->query("
    SELECT s.sale_id, c.customer_name, l.tag_number, l.type, s.price, s.currency, s.date_purchase
    FROM Sales s
    LEFT JOIN Customer c ON s.customer_id = c.customer_id
    JOIN Livestock l ON s.livestock_id = l.livestock_id
    $where_sql
    ORDER BY s.date_purchase DESC
");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Farmer Sales</title>
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
        max-width: 960px;
        margin: 32px auto;
    }

    h2 {
        margin-bottom: 1rem;
    }

    /* Dark theme card styles */
    .dark-card {
        background-color: #312f2e !important;
        border: 4px solid #3d3938 !important;
        border-bottom: 4px solid #000 !important;
        border-left: 4px solid #000 !important;
        border-radius: 0 !important;
        color: #ede5e2 !important;
    }

    /* Dark theme table styles */
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

    .dark-input {
        background-color: #0e0d0d !important;
        color: #ede5e2 !important;
        border: 1px solid #313232 !important;
        border-radius: 0 !important;
    }

    .dark-input:focus {
        outline: 2px solid #8b5cf6 !important;
        outline-offset: 2px !important;
        border-color: #8b5cf6 !important;
        box-shadow: none !important;
    }
    </style>
</head>

<body class="!bg-[#171615]">
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="text-white">Recent Sales</h2>
            <div class="d-flex gap-2">
                <a href="farmer_dashboard.php"
                    class="btn hover:!bg-[#d9d1cd] text-[#262423] !bg-[#d0c5c0] !border-t-5 !border-b-5 !border-t-[#ede5e2] !border-b-[#aba09c] !rounded-none">←
                    Dashboard</a>
                <a href="farmer_sales_add.php"
                    class="btn hover:!bg-[#367723] text-white !bg-[#3c8527] !border-t-5 !border-b-5 !border-t-[#52a535] !border-b-[#2a641c] !rounded-none">+
                    Add Sale</a>
            </div>
        </div>

        <div class="card dark-card mb-3">
            <div class="card-body">
                <form method="GET" class="row g-2">
                    <div class="col-md-4">
                        <input type="text" name="search_name"
                            class="h-100 placeholder:!text-white form-control dark-input !border-[#313232] !rounded-none !focus:outline-2 !focus:outline-offset-2 !focus:outline-violet-500 !bg-[#0e0d0d] !text-[#ede5e2]"
                            placeholder="Search by customer" value="<?= htmlspecialchars($search_name) ?>">
                    </div>
                    <div class="col-md-2">
                        <button
                            class="btn w-100 hover:!bg-[#005eea] !text-[#fff] !bg-[#0d6efd] !border-t-5 !border-b-5 !border-t-[#609ffd] !border-b-[#0052cd] !rounded-none">Search</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card dark-card">
            <div class="card-body table-responsive">
                <table
                    class="table dark-table !bg-[#0e0d0d] !border-t-4 !border-r-4 !border-b-4 !border-l-4 !border-t-black !border-r-black !border-b-[#272626] !border-l-[#272626]"
                    style="background-color: #0e0d0d !important;">
                    <thead class="!bg-[#312f2e] !text-[#ede5e2]"
                        style="background-color: #312f2e !important; color: #ede5e2 !important;">
                        <tr>
                            <th style="color: #ede5e2 !important; border-color: #272626 !important;">ID</th>
                            <th style="color: #ede5e2 !important; border-color: #272626 !important;">Customer</th>
                            <th style="color: #ede5e2 !important; border-color: #272626 !important;">Livestock</th>
                            <th style="color: #ede5e2 !important; border-color: #272626 !important;">Type</th>
                            <th style="color: #ede5e2 !important; border-color: #272626 !important;">Price</th>
                            <th style="color: #ede5e2 !important; border-color: #272626 !important;">Date</th>
                        </tr>
                    </thead>
                    <tbody class="!bg-[#0e0d0d]" style="background-color: #0e0d0d !important;">
                        <?php if($sales && $sales->num_rows>0): ?>
                        <?php while($row = $sales->fetch_assoc()): ?>
                        <tr class="!bg-[#0e0d0d] !text-[#ede5e2]"
                            style="background-color: #0e0d0d !important; color: #ede5e2 !important;">
                            <td style="border-color: #272626 !important; color: #ede5e2 !important;">
                                <?= $row['sale_id'] ?></td>
                            <td style="border-color: #272626 !important; color: #ede5e2 !important;">
                                <?= htmlspecialchars($row['customer_name'] ?? 'Walk-in') ?></td>
                            <td style="border-color: #272626 !important; color: #ede5e2 !important;">
                                <?= htmlspecialchars($row['tag_number']) ?></td>
                            <td style="border-color: #272626 !important; color: #ede5e2 !important;">
                                <?= htmlspecialchars($row['type']) ?></td>
                            <td style="border-color: #272626 !important; color: #ede5e2 !important;">
                                <?= number_format($row['price'],2) ?></td>
                            <td style="border-color: #272626 !important; color: #ede5e2 !important;">
                                <?= $row['date_purchase'] ?></td>
                        </tr>
                        <?php endwhile; ?>
                        <?php else: ?>
                        <tr class="!bg-[#0e0d0d] !text-[#ede5e2]"
                            style="background-color: #0e0d0d !important; color: #ede5e2 !important;">
                            <td colspan="6" class="text-center"
                                style="border-color: #272626 !important; color: #ede5e2 !important;">No sales found</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>

</html>