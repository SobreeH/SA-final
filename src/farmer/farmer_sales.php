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

    // Generate invoice number
    $invoice_number = "INV".time().rand(100,999);

    // Insert sale
    $sql = "INSERT INTO Sales (customer_id, livestock_id, price, currency, invoice_number) 
            VALUES ($customer_id,$livestock_id,$price,'$currency','$invoice_number')";
    if ($conn->query($sql) === TRUE) {
        $conn->query("UPDATE Livestock SET status='sold' WHERE livestock_id=$livestock_id");
        $msg = "Sale recorded successfully!";
    } else {
        $msg = "Error: ".$conn->error;
    }
}

// Fetch available livestock for selection
$livestock_list = $conn->query("SELECT livestock_id, tag_number FROM Livestock WHERE status='available'");

// Fetch all sales
$sales = $conn->query("SELECT s.sale_id, c.customer_name, l.tag_number, s.price, s.currency, s.payment_status, s.date_purchase
                       FROM Sales s
                       JOIN Customer c ON s.customer_id = c.customer_id
                       JOIN Livestock l ON s.livestock_id = l.livestock_id
                       ORDER BY s.date_purchase DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Farmer Sales</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
    <h2>Record Livestock Sale</h2>
    <?php if($msg) echo "<div class='alert alert-info'>$msg</div>"; ?>

    <div class="card mb-4">
        <div class="card-header">Add New Sale</div>
        <div class="card-body">
            <form method="POST">
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
                    <label>Livestock</label>
                    <select name="livestock_id" class="form-select" required>
                        <?php while($row = $livestock_list->fetch_assoc()): ?>
                        <option value="<?php echo $row['livestock_id']; ?>"><?php echo htmlspecialchars($row['tag_number']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label>Price</label>
                    <input type="number" step="0.01" name="price" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Currency</label>
                    <input type="text" name="currency" class="form-control" value="THB">
                </div>
                <button type="submit" name="add_sale" class="btn btn-success">Add Sale</button>
            </form>
        </div>
    </div>

    <h4>All Sales</h4>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th><th>Customer</th><th>Livestock</th><th>Price</th><th>Currency</th><th>Status</th><th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $sales->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['sale_id']; ?></td>
                <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                <td><?php echo htmlspecialchars($row['tag_number']); ?></td>
                <td><?php echo $row['price']; ?></td>
                <td><?php echo htmlspecialchars($row['currency']); ?></td>
                <td><?php echo ucfirst($row['payment_status']); ?></td>
                <td><?php echo $row['date_purchase']; ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>