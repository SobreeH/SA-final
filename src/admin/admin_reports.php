<?php
require_once "../connDB.php";
require_once "../auth.php";



// --- Fetch Farmers and Vets ---
$farmers = $conn->query("SELECT farmer_id, name, username, contact, created_at FROM Farmer ORDER BY farmer_id");
$vets = $conn->query("SELECT vet_id, vet_name, email, institution, contact, created_at FROM Veterinarian ORDER BY vet_id");

// --- Sales Filter ---
$start_date = $_GET['start_date'] ?? '';
$end_date   = $_GET['end_date'] ?? '';
$type_filter = $_GET['type'] ?? '';
$customer_filter = $_GET['customer'] ?? '';

$sql_sales = "
    SELECT 
        s.sale_id, 
        c.customer_name, 
        l.tag_number, 
        l.type, 
        s.price, 
        s.currency, 
        s.payment_status, 
        s.date_purchase
    FROM Sales s
    JOIN Customer c ON s.customer_id = c.customer_id
    JOIN Livestock l ON s.livestock_id = l.livestock_id
    WHERE 1=1
";

$params = [];
$types = '';
$values = [];

// Apply date filter
if ($start_date !== '') {
    $sql_sales .= " AND s.date_purchase >= ?";
    $types .= 's';
    $values[] = $start_date;
}
if ($end_date !== '') {
    $sql_sales .= " AND s.date_purchase <= ?";
    $types .= 's';
    $values[] = $end_date;
}

// Apply type filter
if ($type_filter !== '') {
    $sql_sales .= " AND l.type = ?";
    $types .= 's';
    $values[] = $type_filter;
}

// Apply customer filter
if ($customer_filter !== '') {
    $sql_sales .= " AND c.customer_name LIKE ?";
    $types .= 's';
    $values[] = "%$customer_filter%";
}

$sql_sales .= " ORDER BY s.date_purchase DESC";

$stmt = $conn->prepare($sql_sales);
if ($types !== '') {
    $stmt->bind_param($types, ...$values);
}
$stmt->execute();
$sales = $stmt->get_result();
?>

<!-- HTML -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Reports</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f6fa; margin:0; padding:0;}
        .container { max-width: 1000px; margin:30px auto; background:#fff; padding:20px; border-radius:8px; }
        h1,h2 { color:#2f3640; }
        table { width:100%; border-collapse: collapse; margin-top:15px;}
        th,td { border:1px solid #ccc; padding:8px; text-align:center; }
        th { background:#f1f2f6; }
        .print-btn { background:#273c75; color:white; padding:8px 16px; border:none; border-radius:4px; cursor:pointer; margin-top:10px; }
        .print-btn:hover { background:#192a56; }
        .filter-form { margin-bottom: 15px; display:flex; gap:10px; flex-wrap:wrap;}
        .filter-form input, .filter-form select { padding:6px 10px; border-radius:4px; border:1px solid #ccc; }
        .filter-form button { padding:6px 12px; border-radius:4px; border:none; background:#44bd32; color:white; cursor:pointer; }
        .filter-form button:hover { background:#4cd137; }
        @media print { .no-print{display:none;} body{background:white;} .container{box-shadow:none;margin:0;} }
    </style>
</head>
<body>
<div class="container">
    <div class="no-print" style="text-align:right;">
        <a href="admin_dashboard.php" style="text-decoration:none;color:white;background:#353b48;padding:6px 12px;border-radius:4px;">⬅ Back to Dashboard</a>
    </div>
    <h1>Admin Reports</h1>

    <!-- Sales Filter Form -->
    <div class="section">
        <h2>Sales Report Filter</h2>
        <form method="GET" class="filter-form no-print">
            <input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>" placeholder="Start Date">
            <input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>" placeholder="End Date">
            <select name="type">
                <option value="">All Types</option>
                <option value="cow" <?= $type_filter==='cow'?'selected':'' ?>>Cow</option>
                <option value="chicken" <?= $type_filter==='chicken'?'selected':'' ?>>Chicken</option>
                <option value="goat" <?= $type_filter==='goat'?'selected':'' ?>>Goat</option>
            </select>
            <input type="text" name="customer" value="<?= htmlspecialchars($customer_filter) ?>" placeholder="Customer Name">
            <button type="submit">Filter</button>
        </form>

        <!-- Sales Table -->
        <table>
            <tr>
                <th>ID</th>
                <th>Customer</th>
                <th>Livestock Tag</th>
                <th>Type</th>
                <th>Price</th>
                <th>Currency</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
            <?php while($row = $sales->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['sale_id']) ?></td>
                <td><?= htmlspecialchars($row['customer_name']) ?></td>
                <td><?= htmlspecialchars($row['tag_number']) ?></td>
                <td><?= htmlspecialchars($row['type']) ?></td>
                <td><?= htmlspecialchars($row['price']) ?></td>
                <td><?= htmlspecialchars($row['currency']) ?></td>
                <td><?= htmlspecialchars($row['payment_status']) ?></td>
                <td><?= htmlspecialchars($row['date_purchase']) ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
        <button class="print-btn no-print" onclick="window.print()">🖨 Print Sales</button>
    </div>
</div>
</body>
</html>
