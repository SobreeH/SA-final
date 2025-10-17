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
        s.date_purchase
    FROM Sales s
    LEFT JOIN Customer c ON s.customer_id = c.customer_id
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

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com/4.0.0-alpha.9"></script>
    <style>
    body {
        background-color: #171615 !important;
        background-image:
            radial-gradient(circle at 1px 1px, #2a2825 1px, transparent 0),
            radial-gradient(circle at 3px 3px, #1a1917 1px, transparent 0);
        background-size: 8px 8px, 16px 16px;
        color: #ede5e2 !important;
        font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial;
    }

    .container {
        max-width: 1100px;
        margin: 40px auto;
        background-color: #312f2e !important;
        border: 2px solid #272626 !important;
        border-top: 2px solid #4a4845 !important;
        border-left: 2px solid #4a4845 !important;
        box-shadow: inset 1px 1px 0 #4a4845, inset -1px -1px 0 #0e0d0d !important;
        padding: 20px;
        border-radius: 0;
    }

    h1,
    h2 {
        color: #ede5e2 !important;
        font-weight: bold !important;
    }

    .table {
        background-color: #0e0d0d !important;
        color: #ede5e2 !important;
        border: 2px solid #272626 !important;
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }

    .table th {
        background-color: #272626 !important;
        color: #ede5e2 !important;
        border: 1px solid #171615 !important;
        border-top: 2px solid #4a4845 !important;
        border-left: 2px solid #4a4845 !important;
        font-weight: bold !important;
        padding: 8px;
        text-align: center;
    }

    .table td {
        background-color: #0e0d0d !important;
        color: #ede5e2 !important;
        border: 1px solid #272626 !important;
        padding: 8px;
        text-align: center;
    }

    .table tbody tr:hover {
        background-color: #1a1917 !important;
    }

    .filter-form {
        margin-bottom: 15px;
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .filter-form input,
    .filter-form select {
        background-color: #0e0d0d !important;
        color: #ede5e2 !important;
        border: 2px solid #272626 !important;
        border-bottom: 2px solid #4a4845 !important;
        border-right: 2px solid #4a4845 !important;
        box-shadow: inset 1px 1px 0 #171615 !important;
        padding: 6px 10px;
        border-radius: 0;
    }

    .filter-form input:focus,
    .filter-form select:focus {
        background-color: #1a1917 !important;
        border-color: #7bc05a !important;
        box-shadow: 0 0 0 0.2rem rgba(123, 192, 90, 0.25) !important;
        color: #ede5e2 !important;
        outline: none;
    }

    .filter-form input::placeholder {
        color: #94878e !important;
    }

    .filter-form option {
        background-color: #0e0d0d !important;
        color: #ede5e2 !important;
    }

    .filter-form button {
        background-color: #7bc05a !important;
        color: #0e0d0d !important;
        border: 2px solid #272626 !important;
        border-top: 2px solid #8fd65f !important;
        border-left: 2px solid #8fd65f !important;
        box-shadow: inset 1px 1px 0 #8fd65f, inset -1px -1px 0 #6aa054 !important;
        font-weight: bold !important;
        padding: 6px 12px;
        border-radius: 0;
        cursor: pointer;
    }

    .filter-form button:hover {
        transform: translateY(1px) !important;
        box-shadow: none !important;
    }

    .btn-secondary {
        background-color: #6c757d !important;
        color: #ede5e2 !important;
        border: 2px solid #272626 !important;
        border-top: 2px solid #868e96 !important;
        border-left: 2px solid #868e96 !important;
        box-shadow: inset 1px 1px 0 #868e96, inset -1px -1px 0 #545b62 !important;
        font-weight: bold !important;
    }

    .btn-primary {
        background-color: #5a9bd4 !important;
        color: #0e0d0d !important;
        border: 2px solid #272626 !important;
        border-top: 2px solid #6fb0e8 !important;
        border-left: 2px solid #6fb0e8 !important;
        box-shadow: inset 1px 1px 0 #6fb0e8, inset -1px -1px 0 #4a85b8 !important;
        font-weight: bold !important;
    }

    .btn:hover {
        transform: translateY(1px) !important;
        box-shadow: none !important;
    }

    .no-print {
        display: inline-block;
    }

    @media print {
        .no-print {
            display: none;
        }

        body {
            background: white !important;
            background-image: none !important;
            color: black !important;
        }

        .container {
            background: white !important;
            border: none !important;
            box-shadow: none !important;
            margin: 0;
        }

        .table,
        .table th,
        .table td {
            background: white !important;
            color: black !important;
            border: 1px solid black !important;
        }

        h1,
        h2 {
            color: black !important;
        }
    }
    </style>
</head>

<body
    style="background-color: #171615; background-image: radial-gradient(circle at 1px 1px, #2a2825 1px, transparent 0), radial-gradient(circle at 3px 3px, #1a1917 1px, transparent 0); background-size: 8px 8px, 16px 16px; color: #ede5e2;">
    <div class="container"
        style="background-color: #312f2e; border: 2px solid #272626; border-top: 2px solid #4a4845; border-left: 2px solid #4a4845; box-shadow: inset 1px 1px 0 #4a4845, inset -1px -1px 0 #0e0d0d;">
        <div class="d-flex justify-content-between mb-3 no-print">
            <a href="admin_dashboard.php" class="btn btn-secondary"
                style="background-color: #6c757d; color: #ede5e2; border: 2px solid #272626; border-top: 2px solid #868e96; border-left: 2px solid #868e96; box-shadow: inset 1px 1px 0 #868e96, inset -1px -1px 0 #545b62; font-weight: bold;">⬅
                Back to Dashboard</a>
            <button class="btn btn-primary" onclick="window.print()"
                style="background-color: #5a9bd4; color: #0e0d0d; border: 2px solid #272626; border-top: 2px solid #6fb0e8; border-left: 2px solid #6fb0e8; box-shadow: inset 1px 1px 0 #6fb0e8, inset -1px -1px 0 #4a85b8; font-weight: bold;">🖨
                Print Sales</button>
        </div>

        <h1>Sales Reports</h1>

        <!-- Sales Filter Form -->
        <form method="GET" class="filter-form no-print">
            <input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>" placeholder="Start Date"
                style="background-color: #0e0d0d; color: #ede5e2; border: 2px solid #272626; border-bottom: 2px solid #4a4845; border-right: 2px solid #4a4845; box-shadow: inset 1px 1px 0 #171615;">
            <input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>" placeholder="End Date"
                style="background-color: #0e0d0d; color: #ede5e2; border: 2px solid #272626; border-bottom: 2px solid #4a4845; border-right: 2px solid #4a4845; box-shadow: inset 1px 1px 0 #171615;">
            <select name="type"
                style="background-color: #0e0d0d; color: #ede5e2; border: 2px solid #272626; border-bottom: 2px solid #4a4845; border-right: 2px solid #4a4845; box-shadow: inset 1px 1px 0 #171615;">
                <option value="" style="background-color: #0e0d0d; color: #ede5e2;">All Types</option>
                <option value="cow" <?= $type_filter==='cow'?'selected':'' ?>
                    style="background-color: #0e0d0d; color: #ede5e2;">Cow</option>
                <option value="chicken" <?= $type_filter==='chicken'?'selected':'' ?>
                    style="background-color: #0e0d0d; color: #ede5e2;">Chicken</option>
                <option value="goat" <?= $type_filter==='goat'?'selected':'' ?>
                    style="background-color: #0e0d0d; color: #ede5e2;">Goat</option>
            </select>
            <input type="text" name="customer" value="<?= htmlspecialchars($customer_filter) ?>"
                placeholder="Customer Name"
                style="background-color: #0e0d0d; color: #ede5e2; border: 2px solid #272626; border-bottom: 2px solid #4a4845; border-right: 2px solid #4a4845; box-shadow: inset 1px 1px 0 #171615;">
            <button type="submit"
                style="background-color: #7bc05a; color: #0e0d0d; border: 2px solid #272626; border-top: 2px solid #8fd65f; border-left: 2px solid #8fd65f; box-shadow: inset 1px 1px 0 #8fd65f, inset -1px -1px 0 #6aa054; font-weight: bold;">Filter</button>
        </form>

        <!-- Sales Table -->
        <div class="table-responsive">
            <table class="table table-striped table-bordered"
                style="background-color: #0e0d0d; color: #ede5e2; border: 2px solid #272626;">
                <thead>
                    <tr style="background-color: #272626; color: #ede5e2;">
                        <th
                            style="background-color: #272626; color: #ede5e2; border: 1px solid #171615; border-top: 2px solid #4a4845; border-left: 2px solid #4a4845; font-weight: bold;">
                            ID</th>
                        <th
                            style="background-color: #272626; color: #ede5e2; border: 1px solid #171615; border-top: 2px solid #4a4845; border-left: 2px solid #4a4845; font-weight: bold;">
                            Customer</th>
                        <th
                            style="background-color: #272626; color: #ede5e2; border: 1px solid #171615; border-top: 2px solid #4a4845; border-left: 2px solid #4a4845; font-weight: bold;">
                            Livestock Tag</th>
                        <th
                            style="background-color: #272626; color: #ede5e2; border: 1px solid #171615; border-top: 2px solid #4a4845; border-left: 2px solid #4a4845; font-weight: bold;">
                            Type</th>
                        <th
                            style="background-color: #272626; color: #ede5e2; border: 1px solid #171615; border-top: 2px solid #4a4845; border-left: 2px solid #4a4845; font-weight: bold;">
                            Price</th>
                        <th
                            style="background-color: #272626; color: #ede5e2; border: 1px solid #171615; border-top: 2px solid #4a4845; border-left: 2px solid #4a4845; font-weight: bold;">
                            Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($sales && $sales->num_rows>0): ?>
                    <?php while($row = $sales->fetch_assoc()): ?>
                    <tr style="background-color: #0e0d0d;">
                        <td style="background-color: #0e0d0d; color: #ede5e2; border: 1px solid #272626;">
                            <?= htmlspecialchars($row['sale_id']) ?></td>
                        <td style="background-color: #0e0d0d; color: #ede5e2; border: 1px solid #272626;">
                            <?= htmlspecialchars($row['customer_name'] ?? 'Walk-in') ?></td>
                        <td style="background-color: #0e0d0d; color: #ede5e2; border: 1px solid #272626;">
                            <?= htmlspecialchars($row['tag_number']) ?></td>
                        <td style="background-color: #0e0d0d; color: #ede5e2; border: 1px solid #272626;">
                            <?= htmlspecialchars(ucfirst($row['type'])) ?></td>
                        <td style="background-color: #0e0d0d; color: #ede5e2; border: 1px solid #272626;">
                            <?= number_format($row['price'],2) ?></td>
                        <td style="background-color: #0e0d0d; color: #ede5e2; border: 1px solid #272626;">
                            <?= htmlspecialchars($row['date_purchase']) ?></td>
                    </tr>
                    <?php endwhile; ?>
                    <?php else: ?>
                    <tr style="background-color: #0e0d0d;">
                        <td colspan="6" class="text-center"
                            style="background-color: #0e0d0d; color: #ede5e2; border: 1px solid #272626;">No sales
                            records found</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>