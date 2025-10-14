<?php
include '../connDB.php';
include '../session_check.php';
require_role('vet');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Vet Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
    <h2>Welcome, Veterinarian!</h2>
    <ul class="list-group mt-3">
        <li class="list-group-item"><a href="health_records_list.php">Health Records</a></li>
        <li class="list-group-item"><a href="breeding_records_list.php">Breeding Records</a></li>
        <li class="list-group-item"><a href="../logout.php">Logout</a></li>
    </ul>
</div>
</body>
</html>
