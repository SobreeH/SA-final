<?php
include '../connDB.php';
include '../session_check.php';
require_role('farmer');

$msg = '';

// Get the supply ID from query
$supply_id = (int)($_GET['id'] ?? 0);
if ($supply_id <= 0) {
    die("Invalid supply ID.");
}

// Fetch existing supply
$res = $conn->query("SELECT * FROM Supply WHERE supply_id=$supply_id AND updated_by=".$_SESSION['user_id']);
if (!$res || $res->num_rows === 0) {
    die("Supply not found or you don't have permission.");
}
$supply = $res->fetch_assoc();

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_supply'])) {
    $name = $conn->real_escape_string($_POST['supply_name']);
    $category = $_POST['category'];
    $description = $conn->real_escape_string($_POST['description']);
    $quantity = (float)$_POST['quantity'];
    $unit = $conn->real_escape_string($_POST['unit']);

    $sql = "UPDATE Supply SET
                supply_name='$name',
                category='$category',
                description='$description',
                quantity=$quantity,
                unit='$unit'
            WHERE supply_id=$supply_id AND updated_by=".$_SESSION['user_id'];

    if ($conn->query($sql) === TRUE) {
        header("Location: farmer_supply.php?msg=" . urlencode("Supply updated successfully!"));
        exit;
    } else {
        $msg = "Error updating supply: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Supply</title>
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
        max-width: 700px;
        margin: 32px auto;
    }

    h2 {
        margin-bottom: 1rem;
    }

    /* Dark theme form styles */
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
        font-size: 1.25rem;
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

    .alert-danger {
        background-color: #db3545 !important;
        color: white !important;
        border: none !important;
        border-radius: 0 !important;
        border-top: 5px solid #ff6f7d !important;
        border-bottom: 5px solid #aa0010 !important;
    }
    </style>
</head>

<body class="!bg-[#171615]">
    <div class="container mt-4 ">
        <h2 class="text-white">Edit Supply</h2>
        <?php if($msg) echo "<div class='alert alert-danger'>$msg</div>"; ?>

        <div class="mb-3 flex justify-end">
            <a href="farmer_supply.php"
                class="btn hover:!bg-[#d9d1cd] text-[#262423] !bg-[#d0c5c0] !border-t-5 !border-b-5 !border-t-[#ede5e2] !border-b-[#aba09c] !rounded-none">←
                Back to Supplies</a>
        </div>

        <div class="card dark-card mb-4">
            <div class="card-header">Update Supply</div>
            <div class="card-body">
                <form method="POST" class="row g-3">
                    <div class="col-md-6">
                        <label class="dark-label">Supply Name</label>
                        <input type="text" name="supply_name"
                            class="form-control dark-input !border-[#313232] !rounded-none !focus:outline-2 !focus:outline-offset-2 !focus:outline-violet-500 !bg-[#0e0d0d] !text-[#ede5e2]"
                            required value="<?= htmlspecialchars($supply['supply_name']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="dark-label">Category</label>
                        <select name="category"
                            class="form-select dark-input !border-[#313232] !rounded-none !focus:outline-2 !focus:outline-offset-2 !focus:outline-violet-500 !bg-[#0e0d0d] !text-[#ede5e2]"
                            required>
                            <option value="feed" <?= $supply['category']=='feed'?'selected':'' ?>>Feed</option>
                            <option value="medicine" <?= $supply['category']=='medicine'?'selected':'' ?>>Medicine
                            </option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="dark-label">Description</label>
                        <textarea name="description"
                            class="form-control dark-input !border-[#313232] !rounded-none !focus:outline-2 !focus:outline-offset-2 !focus:outline-violet-500 !bg-[#0e0d0d] !text-[#ede5e2]"><?= htmlspecialchars($supply['description']) ?></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="dark-label">Quantity</label>
                        <input type="number" step="0.01" name="quantity"
                            class="form-control dark-input !border-[#313232] !rounded-none !focus:outline-2 !focus:outline-offset-2 !focus:outline-violet-500 !bg-[#0e0d0d] !text-[#ede5e2]"
                            required value="<?= $supply['quantity'] ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="dark-label">Unit</label>
                        <input type="text" name="unit"
                            class="form-control dark-input !border-[#313232] !rounded-none !focus:outline-2 !focus:outline-offset-2 !focus:outline-violet-500 !bg-[#0e0d0d] !text-[#ede5e2]"
                            required value="<?= htmlspecialchars($supply['unit']) ?>">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" name="update_supply"
                            class="btn w-100 hover:!bg-[#005eea] !text-[#fff] !bg-[#0d6efd] !border-t-5 !border-b-5 !border-t-[#609ffd] !border-b-[#0052cd] !rounded-none">Update
                            Supply</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>