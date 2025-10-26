<?php
include '../connDB.php';
include '../session_check.php';
require_role('farmer');

$msg = '';

// Fetch all available livestock
$livestock_res = $conn->query("SELECT livestock_id, tag_number, type, weight FROM Livestock WHERE status='available'");
$livestock_list = [];
while($row = $livestock_res->fetch_assoc()) $livestock_list[] = $row;

// Handle new sale
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_sale'])) {
    $customer_name = $conn->real_escape_string($_POST['customer_name']);
    $contact = $conn->real_escape_string($_POST['contact']);
    $email = $conn->real_escape_string($_POST['email']);
    $livestock_id = (int)$_POST['livestock_id'];
    $price_per_kg = (float)$_POST['price_per_kg'];
    $currency = "THB";

    // Get livestock weight
    $res = $conn->query("SELECT weight FROM Livestock WHERE livestock_id=$livestock_id");
    $weight = $res->fetch_assoc()['weight'];

    $total_price = $weight * $price_per_kg;

    // Insert or find customer
    $res = $conn->query("SELECT customer_id FROM Customer WHERE email='$email'");
    if ($res->num_rows > 0) {
        $customer_id = $res->fetch_assoc()['customer_id'];
    } else {
        $conn->query("INSERT INTO Customer (customer_name, contact, email) VALUES ('$customer_name','$contact','$email')");
        $customer_id = $conn->insert_id;
    }

    // Insert sale
    $sql = "INSERT INTO Sales (customer_id, livestock_id, price, currency) 
            VALUES ($customer_id,$livestock_id,$total_price,'$currency')";
    if ($conn->query($sql) === TRUE) {
        $conn->query("UPDATE Livestock SET status='sold' WHERE livestock_id=$livestock_id");
        $msg = "Sale recorded successfully! Total: ".number_format($total_price,2)." $currency";
    } else {
        $msg = "Error: ".$conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Quick Sale</title>
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
        margin-top: 30px;
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
        <div class="d-flex justify-content-between align-items-center mt-4">
            <h2 class="text-white">Quick Sale</h2>
            <a href="farmer_sales.php"
                class="btn hover:!bg-[#d9d1cd] text-[#262423] !bg-[#d0c5c0] !border-t-5 !border-b-5 !border-t-[#ede5e2] !border-b-[#aba09c] !rounded-none">←
                Back to Sales</a>
        </div>

        <?php if($msg) echo "<div class='alert alert-success mt-2'>$msg</div>"; ?>

        <div class="card dark-card shadow-sm">
            <div class="card-body">
                <form method="POST" id="saleForm">
                    <div class="mb-3">
                        <label class="dark-label">Customer Name</label>
                        <input type="text" name="customer_name"
                            class="form-control dark-input !border-[#313232] !rounded-none !focus:outline-2 !focus:outline-offset-2 !focus:outline-violet-500 !bg-[#0e0d0d] !text-[#ede5e2]"
                            required>
                    </div>

                    <div class="mb-3">
                        <label class="dark-label">Contact</label>
                        <input type="text" name="contact"
                            class="form-control dark-input !border-[#313232] !rounded-none !focus:outline-2 !focus:outline-offset-2 !focus:outline-violet-500 !bg-[#0e0d0d] !text-[#ede5e2]">
                    </div>

                    <div class="mb-3">
                        <label class="dark-label">Email</label>
                        <input type="email" name="email"
                            class="form-control dark-input !border-[#313232] !rounded-none !focus:outline-2 !focus:outline-offset-2 !focus:outline-violet-500 !bg-[#0e0d0d] !text-[#ede5e2]"
                            required>
                    </div>

                    <div class="mb-3">
                        <label class="dark-label">Select Livestock</label>
                        <select name="livestock_id" id="livestockSelect"
                            class="form-select dark-input !border-[#313232] !rounded-none !focus:outline-2 !focus:outline-offset-2 !focus:outline-violet-500 !bg-[#0e0d0d] !text-[#ede5e2]"
                            required>
                            <option value="">-- Select Livestock --</option>
                            <?php foreach($livestock_list as $l): ?>
                            <option value="<?= $l['livestock_id'] ?>" data-weight="<?= $l['weight'] ?>">
                                <?= htmlspecialchars($l['tag_number'].' ('.$l['type'].' - '.$l['weight'].' kg)') ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="dark-label">Price per kg (THB)</label>
                        <input type="number" step="0.01" name="price_per_kg" id="pricePerKg"
                            class="form-control dark-input !border-[#313232] !rounded-none !focus:outline-2 !focus:outline-offset-2 !focus:outline-violet-500 !bg-[#0e0d0d] !text-[#ede5e2]"
                            required>
                    </div>

                    <div class="mb-3">
                        <label class="dark-label">Total Price (THB)</label>
                        <input type="text" id="totalPrice"
                            class="form-control dark-input !border-[#313232] !rounded-none !bg-[#0e0d0d] !text-[#ede5e2]"
                            readonly>
                    </div>

                    <button type="submit" name="add_sale"
                        class="btn w-100 hover:!bg-[#367723] text-white !bg-[#3c8527] !border-t-5 !border-b-5 !border-t-[#52a535] !border-b-[#2a641c] !rounded-none">Complete
                        Sale</button>
                </form>
            </div>
        </div>
    </div>

    <script>
    const livestockSelect = document.getElementById('livestockSelect');
    const priceInput = document.getElementById('pricePerKg');
    const totalPriceInput = document.getElementById('totalPrice');

    function updateTotal() {
        const livestock = livestockSelect.selectedOptions[0];
        const weight = livestock ? parseFloat(livestock.dataset.weight) : 0;
        const pricePerKg = parseFloat(priceInput.value) || 0;
        totalPriceInput.value = (weight * pricePerKg).toFixed(2);
    }

    livestockSelect.addEventListener('change', updateTotal);
    priceInput.addEventListener('input', updateTotal);
    </script>
</body>

</html>