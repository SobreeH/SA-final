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
<style>
body { background-color: #f8f9fa; }
.card { margin-top: 30px; }
</style>
</head>
<body>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mt-4">
        <h2>Quick Sale</h2>
        <a href="farmer_sales.php" class="btn btn-secondary btn-sm">← Back to Sales</a>
    </div>

    <?php if($msg) echo "<div class='alert alert-success mt-2'>$msg</div>"; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" id="saleForm">
                <div class="mb-3">
                    <label>Customer Name</label>
                    <input type="text" name="customer_name" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Contact</label>
                    <input type="text" name="contact" class="form-control">
                </div>

                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Select Livestock</label>
                    <select name="livestock_id" id="livestockSelect" class="form-select" required>
                        <option value="">-- Select Livestock --</option>
                        <?php foreach($livestock_list as $l): ?>
                        <option value="<?= $l['livestock_id'] ?>" data-weight="<?= $l['weight'] ?>">
                            <?= htmlspecialchars($l['tag_number'].' ('.$l['type'].' - '.$l['weight'].' kg)') ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label>Price per kg (THB)</label>
                    <input type="number" step="0.01" name="price_per_kg" id="pricePerKg" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Total Price (THB)</label>
                    <input type="text" id="totalPrice" class="form-control" readonly>
                </div>

                <button type="submit" name="add_sale" class="btn btn-success w-100">Complete Sale</button>
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
