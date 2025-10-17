<?php
include '../connDB.php';
include '../session_check.php';
require_role('vet');

// Fetch filter values
$search_tag = trim($_GET['search_tag'] ?? '');
$typeFilter = trim($_GET['type'] ?? '');
$date_from = trim($_GET['date_from'] ?? '');
$date_to = trim($_GET['date_to'] ?? '');

// Build SQL with optional filters
$sql = "SELECT br.breeding_id, l.livestock_id, l.tag_number, l.type, br.date_inseminated, br.pregnancy_result
        FROM Breeding_Records br
        JOIN Livestock l ON br.livestock_id = l.livestock_id
        WHERE 1=1";

$params = [];
$types = '';

if ($search_tag !== '') {
    $sql .= " AND l.tag_number LIKE ?";
    $searchTerm = "%$search_tag%";
    $params[] = $searchTerm;
    $types .= "s";
}

if ($typeFilter !== '' && in_array($typeFilter, ['cow','goat','chicken'], true)) {
    $sql .= " AND l.type = ?";
    $params[] = $typeFilter;
    $types .= "s";
}

if ($date_from !== '' && $date_to !== '') {
    $sql .= " AND br.date_inseminated BETWEEN ? AND ?";
    $params[] = $date_from;
    $params[] = $date_to;
    $types .= "ss";
}

$sql .= " ORDER BY br.date_inseminated DESC";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Fetch livestock for modal dropdown
$livestock_list = $conn->query("SELECT livestock_id, tag_number, type FROM Livestock WHERE status='available'");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Breeding Records</title>
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

    .btn-sm {
        font-size: 0.8rem;
        padding: 4px 8px;
    }

    .table td,
    .table th {
        vertical-align: middle;
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

    /* Dark theme modal styles */
    .dark-modal .modal-content {
        background-color: #312f2e !important;
        border: 4px solid #3d3938 !important;
        border-bottom: 4px solid #000 !important;
        border-left: 4px solid #000 !important;
        border-radius: 0 !important;
        color: #ede5e2 !important;
    }

    .dark-modal .modal-header {
        background-color: #d0c5c0 !important;
        color: #262423 !important;
        border-bottom: 3px solid #a69e9a !important;
        border-radius: 0 !important;
    }

    .dark-modal .modal-body {
        background-color: #312f2e !important;
        color: #ede5e2 !important;
    }

    .dark-modal .modal-footer {
        background-color: #312f2e !important;
        border-top: 2px solid #272626 !important;
        border-radius: 0 !important;
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

    .dark-label {
        color: #94878e !important;
        font-weight: 500;
        margin-bottom: 0.5rem;
    }
    </style>
</head>

<body class="!bg-[#171615]">
    <div class="container">
        <h2 class="text-white">Breeding Records</h2>
        <div class="mb-3 d-flex justify-content-end gap-4">
            <div>
                <a href="vet_dashboard.php"
                    class="btn hover:!bg-[#d9d1cd] text-[#262423] !bg-[#d0c5c0] !border-t-5 !border-b-5 !border-t-[#ede5e2] !border-b-[#aba09c] !rounded-none">←
                    Dashboard</a>
            </div>
            <div>
                <button
                    class="btn hover:!bg-[#367723] text-white !bg-[#3c8527] !border-t-5 !border-b-5 !border-t-[#52a535] !border-b-[#2a641c] !rounded-none"
                    data-bs-toggle="modal" data-bs-target="#addBreedingModal">+ Add Breeding Record</button>
            </div>
        </div>

        <!-- Filter Form -->
        <form class="row g-2 mb-3" method="get">
            <div class="col-md-4">
                <input type="text" name="search_tag"
                    class="placeholder:!text-white h-100 form-control dark-input !border-[#313232] !rounded-none !focus:outline-2 !focus:outline-offset-2 !focus:outline-violet-500 !bg-[#0e0d0d] !text-[#ede5e2]"
                    placeholder="Search by Tag Number" value="<?= htmlspecialchars($search_tag) ?>">
            </div>
            <div class="col-md-2">
                <select name="type"
                    class="h-100 form-select dark-input !border-[#313232] !rounded-none !focus:outline-2 !focus:outline-offset-2 !focus:outline-violet-500 !bg-[#0e0d0d] !text-[#ede5e2]">
                    <option value="">All Types</option>
                    <option value="cow" <?= $typeFilter==='cow' ? 'selected' : '' ?>>Cow</option>
                    <option value="goat" <?= $typeFilter==='goat' ? 'selected' : '' ?>>Goat</option>
                    <option value="chicken" <?= $typeFilter==='chicken' ? 'selected' : '' ?>>Chicken</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="date_from"
                    class="h-100 form-control dark-input !border-[#313232] !rounded-none !focus:outline-2 !focus:outline-offset-2 !focus:outline-violet-500 !bg-[#0e0d0d] !text-[#ede5e2]"
                    value="<?= htmlspecialchars($date_from) ?>" placeholder="From">
            </div>
            <div class="col-md-2">
                <input type="date" name="date_to"
                    class="h-100 form-control dark-input !border-[#313232] !rounded-none !focus:outline-2 !focus:outline-offset-2 !focus:outline-violet-500 !bg-[#0e0d0d] !text-[#ede5e2]"
                    value="<?= htmlspecialchars($date_to) ?>" placeholder="To">
            </div>
            <div class="col-md-2 d-grid">
                <button
                    class="btn hover:!bg-[#005eea] !text-[#fff] !bg-[#0d6efd] !border-t-5 !border-b-5 !border-t-[#609ffd] !border-b-[#0052cd] !rounded-none">Filter</button>
            </div>
        </form>

        <!-- Breeding Records Table -->
        <div class="table-responsive">
            <table
                class="table dark-table !bg-[#0e0d0d] !border-t-4 !border-r-4 !border-b-4 !border-l-4 !border-t-black !border-r-black !border-b-[#272626] !border-l-[#272626]"
                style="background-color: #0e0d0d !important;">
                <thead class="!bg-[#312f2e] !text-[#ede5e2]"
                    style="background-color: #312f2e !important; color: #ede5e2 !important;">
                    <tr>
                        <th style="color: #ede5e2 !important; border-color: #272626 !important;">ID</th>
                        <th style="color: #ede5e2 !important; border-color: #272626 !important;">Tag</th>
                        <th style="color: #ede5e2 !important; border-color: #272626 !important;">Type</th>
                        <th style="color: #ede5e2 !important; border-color: #272626 !important;">Date Inseminated</th>
                        <th style="color: #ede5e2 !important; border-color: #272626 !important;">Pregnancy Result</th>
                    </tr>
                </thead>
                <tbody class="!bg-[#0e0d0d]" style="background-color: #0e0d0d !important;">
                    <?php if($result && $result->num_rows>0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr class="!bg-[#0e0d0d] !text-[#ede5e2]"
                        style="background-color: #0e0d0d !important; color: #ede5e2 !important;">
                        <td style="border-color: #272626 !important; color: #ede5e2 !important;">
                            <?= $row['breeding_id'] ?></td>
                        <td style="border-color: #272626 !important; color: #ede5e2 !important;">
                            <?= htmlspecialchars($row['tag_number']) ?></td>
                        <td style="border-color: #272626 !important; color: #ede5e2 !important;">
                            <?= ucfirst($row['type']) ?></td>
                        <td style="border-color: #272626 !important; color: #ede5e2 !important;">
                            <?= $row['date_inseminated'] ?></td>
                        <td style="border-color: #272626 !important; color: #ede5e2 !important;">
                            <?= ucfirst($row['pregnancy_result']) ?></td>
                    </tr>
                    <?php endwhile; ?>
                    <?php else: ?>
                    <tr class="!bg-[#0e0d0d] !text-[#ede5e2]"
                        style="background-color: #0e0d0d !important; color: #ede5e2 !important;">
                        <td colspan="5" class="text-center"
                            style="border-color: #272626 !important; color: #ede5e2 !important;">No breeding records
                            found</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Breeding Record Modal -->
    <div class="modal fade dark-modal" id="addBreedingModal" tabindex="-1" aria-labelledby="addBreedingModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="breeding_records_add.php">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addBreedingModalLabel">Add Breeding Record</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="dark-label">Livestock</label>
                            <select name="livestock_id"
                                class="form-select dark-input !border-[#313232] !rounded-none !focus:outline-2 !focus:outline-offset-2 !focus:outline-violet-500 !bg-[#0e0d0d] !text-[#ede5e2]"
                                required>
                                <?php
                    $livestock_list = $conn->query("SELECT livestock_id, tag_number, type FROM Livestock WHERE status='available'");
                    while($row = $livestock_list->fetch_assoc()): ?>
                                <option value="<?= $row['livestock_id'] ?>">
                                    <?= htmlspecialchars($row['tag_number'] . " (" . ucfirst($row['type']) . ")") ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="dark-label">Date Inseminated</label>
                            <input type="date" name="date_inseminated"
                                class="form-control dark-input !border-[#313232] !rounded-none !focus:outline-2 !focus:outline-offset-2 !focus:outline-violet-500 !bg-[#0e0d0d] !text-[#ede5e2]"
                                required>
                        </div>
                        <input type="hidden" name="pregnancy_result" value="unknown">
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="add_breeding"
                            class="btn w-100 hover:!bg-[#367723] text-white !bg-[#3c8527] !border-t-5 !border-b-5 !border-t-[#52a535] !border-b-[#2a641c] !rounded-none">Add
                            Record</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>