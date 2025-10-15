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
</head>
<body class="bg-light">
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Recent Sales</h2>
        <a href="farmer_dashboard.php" class="btn btn-secondary btn-sm">← Dashboard</a>
        <a href="farmer_sales_add.php" class="btn btn-success btn-sm">+ Add Sale</a>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-md-4">
                    <input type="text" name="search_name" class="form-control" placeholder="Search by customer" value="<?= htmlspecialchars($search_name) ?>">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100">Search</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Livestock</th>
                        <th>Type</th>
                        <th>Price</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($sales && $sales->num_rows>0): ?>
                        <?php while($row = $sales->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['sale_id'] ?></td>
                            <td><?= htmlspecialchars($row['customer_name'] ?? 'Walk-in') ?></td>
                            <td><?= htmlspecialchars($row['tag_number']) ?></td>
                            <td><?= htmlspecialchars($row['type']) ?></td>
                            <td><?= number_format($row['price'],2) ?></td>
                            <td><?= $row['date_purchase'] ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center">No sales found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
