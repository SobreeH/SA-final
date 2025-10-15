<?php
include '../connDB.php';
include '../session_check.php';
require_role('farmer');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Farmer Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
    <h2>Welcome, Farmer!</h2>
    <ul class="list-group mt-3">
        <li class="list-group-item"><a href="farmer_livestock.php">Manage Livestock</a></li>
        <li class="list-group-item"><a href="farmer_supply.php">Manage Supplies</a></li>
        <li class="list-group-item"><a href="farmer_sales.php">Manage Sales</a></li>
        <li class="list-group-item"><a href="../logout.php">Logout</a></li>
    </ul>
</div>
</body>
</html>
