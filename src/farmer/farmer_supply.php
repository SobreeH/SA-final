<?php
include '../connDB.php';
include '../session_check.php';
require_role('farmer');

$msg = '';

// Handle resupply (increase quantity)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resupply_id'])) {
    $resupply_id = (int)$_POST['resupply_id'];
    $resupply_qty = (float)$_POST['resupply_qty'];
    if ($resupply_qty > 0) {
        $conn->query("UPDATE Supply SET quantity = quantity + $resupply_qty WHERE supply_id=$resupply_id AND updated_by=".$_SESSION['user_id']);
        $msg = "Supply updated successfully!";
    }
}

// Handle use supply (decrease quantity)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['use_id'])) {
    $use_id = (int)$_POST['use_id'];
    $use_qty = (float)$_POST['use_qty'];

    // Fetch current quantity
    $cur = $conn->query("SELECT quantity FROM Supply WHERE supply_id=$use_id AND updated_by=".$_SESSION['user_id']);
    if($cur && $cur->num_rows>0){
        $current_qty = $cur->fetch_assoc()['quantity'];
        if($use_qty > 0 && $use_qty <= $current_qty){
            $conn->query("UPDATE Supply SET quantity = quantity - $use_qty WHERE supply_id=$use_id AND updated_by=".$_SESSION['user_id']);
            $msg = "Supply used successfully!";
        } else {
            $msg = "Invalid quantity to use.";
        }
    }
}

// Handle search/filter
$search_name = $_GET['search_name'] ?? '';
$filter_category = $_GET['filter_category'] ?? '';

$where = [];
if ($search_name !== '') {
    $search_name_esc = $conn->real_escape_string($search_name);
    $where[] = "supply_name LIKE '%$search_name_esc%'";
}
if ($filter_category !== '') {
    $filter_category_esc = $conn->real_escape_string($filter_category);
    $where[] = "category='$filter_category_esc'";
}

$where_sql = $where ? " AND " . implode(' AND ', $where) : '';
$result = $conn->query("SELECT * FROM Supply WHERE updated_by=".$_SESSION['user_id'].$where_sql." ORDER BY supply_id DESC");

// Handle delete action
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    $conn->query("DELETE FROM Supply WHERE supply_id=$delete_id AND updated_by=".$_SESSION['user_id']);
    header("Location: farmer_supply.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Farmer Supplies</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <style>
    body {
        background: #f7f9fb;
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

    .card {
        margin-bottom: 20px;
    }

    .table td,
    .table th {
        vertical-align: middle;
    }

    .table-responsive {
        overflow-x: auto;
    }

    .btn {
        min-width: 80px;
    }

    .btn-sm {
        font-size: 0.8rem;
        padding: 4px 8px;
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
        border-left: 1px solid #272626 !important;
        border-right: 1px solid #272626 !important;
        border-top: 1px solid #272626 !important;
        border-bottom: 1px solid #272626 !important;
    }

    /* Dark theme card styles */
    .dark-card {
        background-color: #312f2e !important;
        border: 4px solid #000 !important;
        border-bottom: 4px solid #272626 !important;
        border-right: 4px solid #272626 !important;
    }

    .dark-card .card-header {
        background-color: #3d3938 !important;
        color: #ede5e2 !important;
        border-bottom: 2px solid #272626 !important;
    }

    .dark-card .card-body {
        background-color: #312f2e !important;
        color: #ede5e2 !important;
    }

    @media(max-width:768px) {
        .table-responsive {
            font-size: 0.9rem;
        }

        .btn {
            font-size: 0.85rem;
            padding: 6px 10px;
        }
    }
    </style>
</head>

<body class="!bg-[#171615]">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="text-white mb-5">Manage Supplies</h2>
            <div>
                <a href="farmer_dashboard.php" class="btn btn hover:!bg-[#d9d1cd] text-[#262423] !bg-[#d0c5c0]
                    !border-t-5 !border-b-5 !border-t-[#ede5e2] !border-b-[#aba09c] !rounded-none">← Dashboard</a>
                <a href="farmer_supply_add.php"
                    class="btn hover:!bg-[#367723]  text-white !bg-[#3c8527] !border-t-5 !border-b-5 !border-t-[#52a535] !border-b-[#2a641c] !rounded-none  mt-auto !rounded-none">+
                    Add New Supply</a>
            </div>
        </div>

        <?php if($msg) echo "<div class='alert alert-success'>$msg</div>"; ?>

        <!-- Search / Filter -->
        <form class="row g-2 mb-3" method="get" action="">
            <div class="col-md-6">
                <input type="text" name="search_name"
                    class="placeholder:!text-white form-control !border-[#313232] !rounded-none !focus:outline-2 !focus:outline-offset-2 !focus:outline-violet-500 active:bg-violet-700 !bg-[#0e0d0d] !text-[#ede5e2]"
                    placeholder="Search by name" value="<?= htmlspecialchars($search_name) ?>"
                    aria-label="Search by name">
            </div>
            <div class="col-md-3">
                <select name="filter_category"
                    class="form-select !border-[#313232] !rounded-none !focus:outline-2 !focus:outline-offset-2 !focus:outline-violet-500 active:bg-violet-700 !bg-[#0e0d0d] !text-[#ede5e2]"
                    aria-label="Filter by category">
                    <option value="">All Categories</option>
                    <option value="feed" <?= $filter_category==='feed'?'selected':'' ?>>Feed</option>
                    <option value="medicine" <?= $filter_category==='medicine'?'selected':'' ?>>Medicine</option>
                </select>
            </div>
            <div class="col-md-3 d-grid">
                <button
                    class="btn h-10 hover:!bg-[#005eea] !text-[#fff] !bg-[#0d6efd] !border-t-5 !border-b-5 !border-t-[#609ffd] !border-b-[#0052cd] !rounded-none"
                    type="submit">Filter</button>
            </div>
        </form>

        <!-- Supplies Table -->
        <div class="table-responsive">
            <table
                class="table dark-table !bg-[#0e0d0d] !border-t-4 !border-r-4 !border-b-4 !border-l-4 !border-t-black !border-r-black !border-b-[#272626] !border-l-[#272626]"
                style="background-color: #0e0d0d !important;">
                <thead class="!bg-[#312f2e] !text-[#ede5e2]"
                    style="background-color: #312f2e !important; color: #ede5e2 !important;">
                    <tr>
                        <th scope="col" class="!text-[#ede5e2] !border-[#272626]"
                            style="color: #ede5e2 !important; border-color: #272626 !important;">ID</th>
                        <th scope="col" class="!text-[#ede5e2] !border-[#272626]"
                            style="color: #ede5e2 !important; border-color: #272626 !important;">Name</th>
                        <th scope="col" class="!text-[#ede5e2] !border-[#272626]"
                            style="color: #ede5e2 !important; border-color: #272626 !important;">Category</th>
                        <th scope="col" class="!text-[#ede5e2] !border-[#272626]"
                            style="color: #ede5e2 !important; border-color: #272626 !important;">Description</th>
                        <th scope="col" class="!text-[#ede5e2] !border-[#272626]"
                            style="color: #ede5e2 !important; border-color: #272626 !important;">Qty</th>
                        <th scope="col" class="!text-[#ede5e2] !border-[#272626]"
                            style="color: #ede5e2 !important; border-color: #272626 !important;">Unit</th>
                        <th scope="col" class="!text-[#ede5e2] !border-[#272626]"
                            style="color: #ede5e2 !important; border-color: #272626 !important;">Actions</th>
                    </tr>
                </thead>
                <tbody class="!bg-[#0e0d0d]" style="background-color: #0e0d0d !important;">
                    <?php if($result && $result->num_rows>0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr class="!bg-[#0e0d0d] !text-[#ede5e2]"
                        style="background-color: #0e0d0d !important; color: #ede5e2 !important;">
                        <td class="!border-[#272626] !text-[#ede5e2]"
                            style="border-color: #272626 !important; color: #ede5e2 !important;">
                            <?= $row['supply_id'] ?></td>
                        <td class="!border-[#272626] !text-[#ede5e2]"
                            style="border-color: #272626 !important; color: #ede5e2 !important;">
                            <?= htmlspecialchars($row['supply_name']) ?></td>
                        <td class="!border-[#272626] !text-[#ede5e2]"
                            style="border-color: #272626 !important; color: #ede5e2 !important;">
                            <?= ucfirst($row['category']) ?></td>
                        <td class="!border-[#272626] !text-[#ede5e2]"
                            style="border-color: #272626 !important; color: #ede5e2 !important;">
                            <?= htmlspecialchars($row['description']) ?></td>
                        <td class="!border-[#272626] !text-[#ede5e2]"
                            style="border-color: #272626 !important; color: #ede5e2 !important;"><?= $row['quantity'] ?>
                        </td>
                        <td class="!border-[#272626] !text-[#ede5e2]"
                            style="border-color: #272626 !important; color: #ede5e2 !important;">
                            <?= htmlspecialchars($row['unit']) ?></td>
                        <td class="!border-[#272626]" style="border-color: #272626 !important;">
                            <!-- Resupply Form -->
                            <form method="POST" class="d-flex mb-1">
                                <input type="hidden" name="resupply_id" value="<?= $row['supply_id'] ?>">
                                <input type="number" step="0.01" name="resupply_qty"
                                    class="placeholder:!text-white form-control form-control-sm me-1 !border-[#313232] !rounded-none !bg-[#0e0d0d] !text-[#ede5e2]"
                                    placeholder="+Qty" required>
                                <button type="submit"
                                    class="btn btn-sm hover:!bg-[#367723] text-white !bg-[#3c8527] !border-t-3 !border-b-3 !border-t-[#52a535] !border-b-[#2a641c] !rounded-none">Add</button>
                            </form>
                            <!-- Use Supply Form -->
                            <form method="POST" class="d-flex mb-1">
                                <input type="hidden" name="use_id" value="<?= $row['supply_id'] ?>">
                                <input type="number" step="0.01" max="<?= $row['quantity'] ?>" name="use_qty"
                                    class="placeholder:!text-white form-control form-control-sm me-1 !border-[#313232] !rounded-none !bg-[#0e0d0d] !text-[#ede5e2]"
                                    placeholder="-Qty" required>
                                <button type="submit"
                                    class="btn btn-sm hover:!bg-[#f39c12] text-white !bg-[#f1c40f] !border-t-3 !border-b-3 !border-t-[#f7dc6f] !border-b-[#d68910] !rounded-none">Use</button>
                            </form>
                            <!-- Edit/Delete -->
                            <div class="mt-1 d-flex gap-1 flex-wrap">
                                <a href="farmer_supply_edit.php?id=<?= $row['supply_id'] ?>"
                                    class="btn btn-sm hover:!bg-[#005eea] !text-[#fff] !bg-[#0d6efd] !border-t-3 !border-b-3 !border-t-[#609ffd] !border-b-[#0052cd] !rounded-none">Edit</a>
                                <a href="farmer_supply.php?delete_id=<?= $row['supply_id'] ?>"
                                    class="btn btn-sm hover:!bg-[#bb2d3b] !text-[#fff] !bg-[#db3545] !border-t-3 !border-b-3 !border-t-[#ff6f7d] !border-b-[#aa0010] !rounded-none"
                                    onclick="return confirm('Are you sure you want to delete this supply?');">Delete</a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php else: ?>
                    <tr class="!bg-[#0e0d0d] !text-[#ede5e2]"
                        style="background-color: #0e0d0d !important; color: #ede5e2 !important;">
                        <td colspan="7" class="text-center !border-[#272626] !text-[#ede5e2]"
                            style="border-color: #272626 !important; color: #ede5e2 !important;">No supplies found</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>