<?php
include '../connDB.php';
include '../session_check.php';
require_role('farmer');

$msg = '';

// Handle new sale
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_sale'])) {
    $customer_name = $conn->real_escape_string($_POST['customer_name']);
    $contact = $conn->real_escape_string($_POST['contact']);
    $email = $conn->real_escape_string($_POST['email']);
    $livestock_id = (int)$_POST['livestock_id'];
    $price = (float)$_POST['price'];
    $currency = $conn->real_escape_string($_POST['currency']);

    // Insert or find customer
    $res = $conn->query("SELECT customer_id FROM Customer WHERE email='$email'");
    if ($res->num_rows > 0) {
        $customer_id = $res->fetch_assoc()['customer_id'];
    } else {
        $conn->query("INSERT INTO Customer (customer_name, contact, email) VALUES ('$customer_name','$contact','$email')");
        $customer_id = $conn->insert_id;
    }

    // Insert sale (POS-style, no invoice)
    $sql = "INSERT INTO Sales (customer_id, livestock_id, price, currency) 
            VALUES ($customer_id,$livestock_id,$price,'$currency')";
    if ($conn->query($sql) === TRUE) {
        $conn->query("UPDATE Livestock SET status='sold' WHERE livestock_id=$livestock_id");
        $msg = "Sale recorded successfully!";
    } else {
        $msg = "Error: ".$conn->error;
    }
}

// Fetch available livestock for selection
$livestock_list = $conn->query("SELECT livestock_id, tag_number, type FROM Livestock WHERE status='available'");

// Fetch all sales
$sales = $conn->query("SELECT s.sale_id, c.customer_name, l.tag_number, l.type, s.price, s.currency, s.date_purchase
                       FROM Sales s
                       LEFT JOIN Customer c ON s.customer_id = c.customer_id
                       JOIN Livestock l ON s.livestock_id = l.livestock_id
                       ORDER BY s.date_purchase DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Farmer POS</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body { background-color: #f8f9fa; }
    .sale-form { max-width: 600px; margin:auto; }
    @media (max-width: 768px) {
        .table-responsive { font-size: 0.9rem; }
        .btn { font-size: 0.9rem; }
    }
</style>
</head>
<body>
<div class="container mt-3 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Livestock POS</h2>
        <a href="farmer_dashboard.php" class="btn btn-primary btn-sm">← Dashboard</a>
    </div>

    <?php if($msg) echo "<div class='alert alert-success'>$msg</div>"; ?>

    <div class="card sale-form mb-4 shadow-sm">
        <div class="card-header bg-success text-white">Record New Sale</div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-2">
                    <label>Customer Name</label>
                    <input type="text" name="customer_name" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label>Contact</label>
                    <input type="text" name="contact" class="form-control">
                </div>
                <div class="mb-2">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label>Livestock</label>
                    <select name="livestock_id" class="form-select" required>
                        <?php while($row = $livestock_list->fetch_assoc()): ?>
                            <option value="<?= $row['livestock_id'] ?>">
                                <?= htmlspecialchars($row['tag_number'].' ('.$row['type'].')') ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-2">
                    <label>Price</label>
                    <input type="number" step="0.01" name="price" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label>Currency</label>
                    <input type="text" name="currency" class="form-control" value="THB">
                </div>
                <button type="submit" name="add_sale" class="btn btn-success w-100">Add Sale</button>
            </form>
        </div>
    </div>

    <h4 class="mb-2 text-center">Recent Sales</h4>
    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Livestock</th>
                    <th>Type</th>
                    <th>Price</th>
                    <th>Currency</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $sales->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['sale_id'] ?></td>
                    <td><?= htmlspecialchars($row['customer_name'] ?? 'Walk-in') ?></td>
                    <td><?= htmlspecialchars($row['tag_number']) ?></td>
                    <td><?= htmlspecialchars($row['type']) ?></td>
                    <td><?= number_format($row['price'],2) ?></td>
                    <td><?= htmlspecialchars($row['currency']) ?></td>
                    <td><?= $row['date_purchase'] ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
