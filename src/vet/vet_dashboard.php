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
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { background-color: #f8f9fa; font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial; }
.container { max-width: 800px; margin-top: 50px; }
h2 { margin-bottom: 40px; text-align: center; }
.card-link { text-decoration: none; color: inherit; }
.card-link:hover .card { transform: translateY(-4px); box-shadow: 0 8px 20px rgba(0,0,0,0.15); }
.card { border-radius: 12px; transition: all 0.2s; box-shadow: 0 4px 12px rgba(0,0,0,0.05); text-align: center; padding: 30px 20px; }
.card i { font-size: 2rem; margin-bottom: 15px; color: #0d6efd; }
</style>
</head>
<body>
<div class="container">
    <h2>Welcome, Veterinarian!</h2>
    <div class="row g-4">
        <div class="col-md-6">
            <a href="health_records_list.php" class="card-link">
                <div class="card">
                    <i class="bi bi-file-medical"></i>
                    <h5>Health Records</h5>
                </div>
            </a>
        </div>
        <div class="col-md-6">
            <a href="breeding_records_list.php" class="card-link">
                <div class="card">
                    <i class="bi bi-heart-pulse"></i>
                    <h5>Breeding Records</h5>
                </div>
            </a>
        </div>
        <div class="col-md-6">
            <a href="../logout.php" class="card-link">
                <div class="card">
                    <i class="bi bi-box-arrow-right"></i>
                    <h5>Logout</h5>
                </div>
            </a>
        </div>
    </div>
</div>
</body>
</html>
