<?php
include '../connDB.php';
include '../session_check.php';
require_role('farmer');

$msg = '';

// Handle new supply addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_supply'])) {
    $name = $conn->real_escape_string($_POST['supply_name']);
    $category = $_POST['category'];
    $description = $conn->real_escape_string($_POST['description']);
    $quantity = (float)$_POST['quantity'];

    // Unit: use custom if provided
    $unit = trim($_POST['unit_custom']) !== '' ? $conn->real_escape_string($_POST['unit_custom']) : $conn->real_escape_string($_POST['unit']);

    $farmer_id = $_SESSION['user_id'];

    $sql = "INSERT INTO Supply (supply_name, category, description, quantity, unit, updated_by)
            VALUES ('$name','$category','$description',$quantity,'$unit',$farmer_id)";
    $msg = $conn->query($sql) === TRUE ? "Supply added successfully!" : "Error: ".$conn->error;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add New Supply</title>
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

    .alert-success {
        background-color: #3c8527 !important;
        color: white !important;
        border: none !important;
        border-radius: 0 !important;
        border-top: 5px solid #52a535 !important;
        border-bottom: 5px solid #2a641c !important;
    }
    </style>
</head>

<body class="!bg-[#171615]">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="text-white">Add New Supply</h2>
            <a href="farmer_supply.php"
                class="btn hover:!bg-[#d9d1cd] text-[#262423] !bg-[#d0c5c0] !border-t-5 !border-b-5 !border-t-[#ede5e2] !border-b-[#aba09c] !rounded-none">←
                Back to Supplies</a>
        </div>

        <?php if($msg) echo "<div class='alert alert-success'>$msg</div>"; ?>

        <div class="card dark-card">
            <div class="card-header">Supply Information</div>
            <div class="card-body">
                <form method="POST" class="row g-3">
                    <div class="col-md-6">
                        <label class="dark-label">Supply Name</label>
                        <input type="text" name="supply_name"
                            class="form-control dark-input !border-[#313232] !rounded-none !focus:outline-2 !focus:outline-offset-2 !focus:outline-violet-500 !bg-[#0e0d0d] !text-[#ede5e2]"
                            required>
                    </div>
                    <div class="col-md-6">
                        <label class="dark-label">Category</label>
                        <select name="category"
                            class="form-select dark-input !border-[#313232] !rounded-none !focus:outline-2 !focus:outline-offset-2 !focus:outline-violet-500 !bg-[#0e0d0d] !text-[#ede5e2]"
                            required>
                            <option value="feed">Feed</option>
                            <option value="medicine">Medicine</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="dark-label">Description</label>
                        <textarea name="description"
                            class="form-control dark-input !border-[#313232] !rounded-none !focus:outline-2 !focus:outline-offset-2 !focus:outline-violet-500 !bg-[#0e0d0d] !text-[#ede5e2]"></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="dark-label">Quantity</label>
                        <input type="number" step="0.01" name="quantity"
                            class="form-control dark-input !border-[#313232] !rounded-none !focus:outline-2 !focus:outline-offset-2 !focus:outline-violet-500 !bg-[#0e0d0d] !text-[#ede5e2]"
                            required>
                    </div>
                    <div class="col-md-4">
                        <label class="dark-label">Unit</label>
                        <select name="unit" id="unitSelect"
                            class="form-select dark-input !border-[#313232] !rounded-none !focus:outline-2 !focus:outline-offset-2 !focus:outline-violet-500 !bg-[#0e0d0d] !text-[#ede5e2]">
                            <option value="kg">kg</option>
                            <option value="bottles">bottles</option>
                            <option value="bags">bags</option>
                            <option value="custom">Custom...</option>
                        </select>
                        <input type="text" name="unit_custom" id="unitCustom"
                            class="form-control dark-input mt-1 !border-[#313232] !rounded-none !focus:outline-2 !focus:outline-offset-2 !focus:outline-violet-500 !bg-[#0e0d0d] !text-[#ede5e2]"
                            placeholder="Enter custom unit" style="display:none;">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" name="add_supply"
                            class="btn w-100 hover:!bg-[#367723] text-white !bg-[#3c8527] !border-t-5 !border-b-5 !border-t-[#52a535] !border-b-[#2a641c] !rounded-none">Add
                            Supply</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    const unitSelect = document.getElementById('unitSelect');
    const unitCustom = document.getElementById('unitCustom');

    unitSelect.addEventListener('change', () => {
        if (unitSelect.value === 'custom') {
            unitCustom.style.display = 'block';
            unitCustom.focus();
        } else {
            unitCustom.style.display = 'none';
        }
    });
    </script>
</body>

</html>