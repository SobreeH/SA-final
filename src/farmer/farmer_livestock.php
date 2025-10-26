<?php
include '../connDB.php';
include '../session_check.php';
require_role('farmer');

// Get search/filter values
$search = trim($_GET['search'] ?? '');
$typeFilter = trim($_GET['type'] ?? '');

// Build SQL with optional filters
$sql = "
SELECT 
    l.livestock_id, l.tag_number, l.type, l.breed, l.gender, l.weight, l.status, l.image, l.date_added,
    b.pregnancy_result
FROM Livestock l
LEFT JOIN Breeding_Records b 
    ON l.livestock_id = b.livestock_id
    AND b.breeding_id = (
        SELECT MAX(b2.breeding_id) 
        FROM Breeding_Records b2 
        WHERE b2.livestock_id = l.livestock_id
    )
WHERE 1=1
";

$params = [];
$types = '';

if ($search !== '') {
    $sql .= " AND (l.tag_number LIKE ? OR l.breed LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "ss";
}

if ($typeFilter !== '' && in_array($typeFilter, ['cow','goat','chicken'], true)) {
    $sql .= " AND l.type = ?";
    $params[] = $typeFilter;
    $types .= "s";
}

$sql .= " ORDER BY l.date_added DESC";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Livestock</title>
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

    img.livestock-img {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 6px;
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
        <h2 class="text-white">Manage Livestock</h2>

        <div class="mb-3 d-flex flex-wrap gap-2 justify-content-end">
            <a href="farmer_dashboard.php"
                class="btn hover:!bg-[#d9d1cd]  text-[#262423] !bg-[#d0c5c0] !border-t-5 !border-b-5 !border-t-[#ede5e2] !border-b-[#aba09c] !rounded-none">←
                Dashboard</a>
            <a href="farmer_livestock_insert.php"
                class="btn hover:!bg-[#367723]  text-white !bg-[#3c8527] !border-t-5 !border-b-5 !border-t-[#52a535] !border-b-[#2a641c] !rounded-none  mt-auto !rounded-none">+
                Add Livestock</a>
        </div>

        <!-- Search & Filter -->
        <form class="row g-2 mb-3" method="get" action="">
            <div class="col-md-6">
                <input type="text" name="search"
                    class="placeholder:!text-[#ede5e2] !bg-[#0e0d0d] !text-[#ede5e2] form-control !border-[#313232] !rounded-none !focus:outline-2 !focus:outline-offset-2 !focus:outline-violet-500 active:bg-violet-700"
                    placeholder="Search by Tag or Breed" value="<?= htmlspecialchars($search) ?>"
                    aria-label="Search by tag or breed">
            </div>
            <div class="col-md-3">
                <select name="type"
                    class="!bg-[#0e0d0d] !text-[#ede5e2] form-select !border-[#313232] !rounded-none !focus:outline-2 !focus:outline-offset-2 !focus:outline-violet-500 active:bg-violet-700"
                    aria-label="Filter by livestock type">
                    <option value="">All Types</option>
                    <option value="cow" <?= $typeFilter==='cow' ? 'selected' : '' ?>>Cow</option>
                    <option value="goat" <?= $typeFilter==='goat' ? 'selected' : '' ?>>Goat</option>
                    <option value="chicken" <?= $typeFilter==='chicken' ? 'selected' : '' ?>>Chicken</option>
                </select>
            </div>
            <div class="col-md-3 d-grid">
                <button
                    class="btn h-10  hover:!bg-[#005eea] !text-[#fff] !bg-[#0d6efd] !border-t-5 !border-b-5 !border-t-[#609ffd] !border-b-[#0052cd] !rounded-none hover:!bg-[#005eea] !text-[#fff] !bg-[#0d6efd] !border-t-5 !border-b-5 !border-t-[#609ffd] !border-b-[#0052cd] !rounded-none"
                    type="submit">Filter</button>
            </div>
        </form>

        <div class="table-responsive ">
            <table
                class="table dark-table !bg-[#0e0d0d] !border-t-4 !border-r-4 !border-b-4 !border-l-4 !border-t-black !border-r-black !border-b-[#272626] !border-l-[#272626]"
                style="background-color: #0e0d0d !important;">
                <thead class="!bg-[#312f2e] !text-[#ede5e2]"
                    style="background-color: #312f2e !important; color: #ede5e2 !important;">
                    <tr>
                        <th scope="col" class="!text-[#ede5e2] !border-[#272626]"
                            style="color: #ede5e2 !important; border-color: #272626 !important;">Image</th>
                        <th scope="col" class="!text-[#ede5e2] !border-[#272626]"
                            style="color: #ede5e2 !important; border-color: #272626 !important;">Tag</th>
                        <th scope="col" class="!text-[#ede5e2] !border-[#272626]"
                            style="color: #ede5e2 !important; border-color: #272626 !important;">Type</th>
                        <th scope="col" class="!text-[#ede5e2] !border-[#272626]"
                            style="color: #ede5e2 !important; border-color: #272626 !important;">Breed</th>
                        <th scope="col" class="!text-[#ede5e2] !border-[#272626]"
                            style="color: #ede5e2 !important; border-color: #272626 !important;">Gender</th>
                        <th scope="col" class="!text-[#ede5e2] !border-[#272626]"
                            style="color: #ede5e2 !important; border-color: #272626 !important;">Weight (kg)</th>
                        <th scope="col" class="!text-[#ede5e2] !border-[#272626]"
                            style="color: #ede5e2 !important; border-color: #272626 !important;">Status</th>
                        <th scope="col" class="!text-[#ede5e2] !border-[#272626]"
                            style="color: #ede5e2 !important; border-color: #272626 !important;">Pregnancy</th>
                        <th scope="col" class="!text-[#ede5e2] !border-[#272626]"
                            style="color: #ede5e2 !important; border-color: #272626 !important;">Date Added</th>
                        <th scope="col" class="!text-[#ede5e2] !border-[#272626]"
                            style="color: #ede5e2 !important; border-color: #272626 !important;">Actions</th>
                    </tr>
                </thead>
                <tbody class="!bg-[#0e0d0d]" style="background-color: #0e0d0d !important;">
                    <?php if($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr class="!bg-[#0e0d0d] !text-[#ede5e2]"
                        style="background-color: #0e0d0d !important; color: #ede5e2 !important;">
                        <td class="!border-[#272626] !text-[#ede5e2]"
                            style="border-color: #272626 !important; color: #ede5e2 !important;">
                            <?php if($row['image']): ?>
                            <img src="<?= htmlspecialchars($row['image']); ?>" alt="Livestock Image"
                                class="livestock-img">
                            <?php else: ?>
                            <span aria-label="No image">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="!border-[#272626] !text-[#ede5e2]"
                            style="border-color: #272626 !important; color: #ede5e2 !important;">
                            <?= htmlspecialchars($row['tag_number']); ?></td>
                        <td class="!border-[#272626] !text-[#ede5e2]"
                            style="border-color: #272626 !important; color: #ede5e2 !important;">
                            <?= ucfirst($row['type']); ?></td>
                        <td class="!border-[#272626] !text-[#ede5e2]"
                            style="border-color: #272626 !important; color: #ede5e2 !important;">
                            <?= htmlspecialchars($row['breed']); ?></td>
                        <td class="!border-[#272626] !text-[#ede5e2]"
                            style="border-color: #272626 !important; color: #ede5e2 !important;">
                            <?= ucfirst($row['gender'] ?? '-') ?></td>
                        <td class="!border-[#272626] !text-[#ede5e2]"
                            style="border-color: #272626 !important; color: #ede5e2 !important;">
                            <?= $row['weight'] ?? '-'; ?></td>
                        <td class="!border-[#272626] !text-[#ede5e2]"
                            style="border-color: #272626 !important; color: #ede5e2 !important;">
                            <?= ucfirst($row['status']); ?></td>
                        <td class="!border-[#272626]" style="border-color: #272626 !important;">
                            <?php 
                        switch($row['pregnancy_result'] ?? 'unknown') {
                            case 'pregnant': echo '<span class="text-success">Pregnant</span>'; break;
                            case 'not_pregnant': echo '<span class="text-secondary">Not Pregnant</span>'; break;
                            default: echo '<span class="!text-[#94878e]">Unknown</span>'; break;
                        }
                        ?>

                        </td>
                        <td class="!border-[#272626] !text-[#ede5e2]"
                            style="border-color: #272626 !important; color: #ede5e2 !important;">
                            <?= $row['date_added']; ?></td>
                        <td class="d-flex gap-1 flex-wrap !border-[#272626]" style="border-color: #272626 !important;">
                            <a href="farmer_livestock_edit.php?id=<?= $row['livestock_id']; ?>"
                                class="btn !text-xs hover:!bg-[#005eea] !text-[#fff] !bg-[#0d6efd] !border-t-4 !border-b-4 !border-t-[#609ffd] !border-b-[#0052cd] !rounded-none hover:!bg-[#005eea] !text-[#fff] !bg-[#0d6efd] !border-t-5 !border-b-5 !border-t-[#609ffd] !border-b-[#0052cd] !rounded-none">Edit</a>
                            <a href="farmer_livestock_delete.php?id=<?= $row['livestock_id']; ?>"
                                class="btn !text-xs hover:!bg-[#bb2d3b] !text-[#fff] !bg-[#db3545] !border-t-4 !border-b-4 !border-t-[#ff6f7d] !border-b-[#aa0010] !rounded-none hover:!bg-[#005eea] !text-[#fff] !bg-[#0d6efd] !border-t-5 !border-b-5 !border-t-[#609ffd] !border-b-[#0052cd] !rounded-none"
                                onclick="return confirm('Are you sure you want to delete this livestock?');">Delete</a>
                            <?php if(($row['gender'] ?? '') === 'female'): ?>
                            <a href="farmer_livestock_confirm_preg.php?id=<?= $row['livestock_id']; ?>&status=pregnant"
                                class="btn !text-[14px] hover:!bg-[#367723]  text-white !bg-[#3c8527] !border-t-5 !border-b-5 !border-t-[#52a535] !border-b-[#2a641c] !rounded-none  mt-auto !rounded-none">Pregnant</a>
                            <a href="farmer_livestock_confirm_preg.php?id=<?= $row['livestock_id']; ?>&status=not_pregnant"
                                class="btn !text-[14px] hover:!bg-[#5c636a]  text-white !bg-[#6c757d] !border-t-5 !border-b-5 !border-t-[#98a4b0] !border-b-[#4f555b] !rounded-none  mt-auto !rounded-none">Not
                                Pregnant</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php else: ?>
                    <tr class="!bg-[#0e0d0d] !text-[#ede5e2]"
                        style="background-color: #0e0d0d !important; color: #ede5e2 !important;">
                        <td colspan="10" class="text-center !border-[#272626] !text-[#ede5e2]"
                            style="border-color: #272626 !important; color: #ede5e2 !important;">No livestock found</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>