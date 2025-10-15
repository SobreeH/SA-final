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
<style>
body { background-color: #f8f9fa; font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial; }
.container { max-width: 600px; margin-top: 50px; }
h2 { margin-bottom: 30px; }
.list-group-item a { text-decoration: none; color: #212529; }
.list-group-item a:hover { color: #fff; background-color: #0d6efd; border-radius: 8px; padding: 8px 12px; display: block; }
.card { border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
</style>
</head>
<body>
<div class="container">
    <div class="card p-4">
        <h2 class="text-center">Welcome, Farmer!</h2>
        <ul class="list-group list-group-flush mt-4">
            <li class="list-group-item"><a href="farmer_livestock.php">Manage Livestock</a></li>
            <li class="list-group-item"><a href="farmer_supply.php">Manage Supplies</a></li>
            <li class="list-group-item"><a href="farmer_sales.php">Manage Sales</a></li>
            <li class="list-group-item"><a href="../logout.php">Logout</a></li>
        </ul>
    </div>
</div>
</body>
</html>
