<?php
session_start();
include '../connDB.php';

// --- Authentication ---
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// --- ADD Farmer ---
if (isset($_POST['add_farmer'])) {
    $name = $_POST['name'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $contact = $_POST['contact'];
    $conn->query("INSERT INTO Farmer (name, username, password, contact) VALUES ('$name','$username','$password','$contact')");
    header("Location: manage_users.php");
    exit();
}

// --- ADD Vet ---
if (isset($_POST['add_vet'])) {
    $vet_name = $_POST['vet_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $contact = $_POST['contact'];
    $conn->query("INSERT INTO Veterinarian (vet_name, email, password, contact) VALUES ('$vet_name','$email','$password','$contact')");
    header("Location: manage_users.php");
    exit();
}

// --- DELETE Farmer ---
if (isset($_GET['delete_farmer'])) {
    $id = intval($_GET['delete_farmer']);
    $conn->query("DELETE FROM Farmer WHERE farmer_id=$id");
    header("Location: manage_users.php");
    exit();
}

// --- DELETE Vet ---
if (isset($_GET['delete_vet'])) {
    $id = intval($_GET['delete_vet']);
    $conn->query("DELETE FROM Veterinarian WHERE vet_id=$id");
    header("Location: manage_users.php");
    exit();
}

// --- UPDATE Farmer ---
if (isset($_POST['update_farmer'])) {
    $id = $_POST['farmer_id'];
    $name = $_POST['name'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $contact = $_POST['contact'];
    $conn->query("UPDATE Farmer SET name='$name', username='$username', password='$password', contact='$contact' WHERE farmer_id=$id");
    header("Location: manage_users.php");
    exit();
}

// --- UPDATE Vet ---
if (isset($_POST['update_vet'])) {
    $id = $_POST['vet_id'];
    $vet_name = $_POST['vet_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $contact = $_POST['contact'];
    $conn->query("UPDATE Veterinarian SET vet_name='$vet_name', email='$email', password='$password', contact='$contact' WHERE vet_id=$id");
    header("Location: manage_users.php");
    exit();
}

// Fetch all users
$farmers = $conn->query("SELECT * FROM Farmer");
$vets = $conn->query("SELECT * FROM Veterinarian");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Users - Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
    <h3 class="text-center mb-4">Admin Dashboard - Manage Users</h3>
    <a href="../logout.php" class="btn btn-danger mb-3">Logout</a>
    <a href="admin_dashboard.php" class="btn btn-success mb-3">Dashboard</a>

    <!-- FARMERS -->
    <div class="card mb-4">
        <div class="card-header bg-success text-white">Farmers</div>
        <div class="card-body">
            <!-- Add Farmer -->
            <form method="POST" class="row g-2 mb-3">
                <div class="col"><input type="text" name="name" placeholder="Name" class="form-control" required></div>
                <div class="col"><input type="text" name="username" placeholder="Username" class="form-control" required></div>
                <div class="col"><input type="password" name="password" placeholder="Password" class="form-control" required></div>
                <div class="col"><input type="text" name="contact" placeholder="Contact" class="form-control"></div>
                <div class="col-auto"><button type="submit" name="add_farmer" class="btn btn-success">Add</button></div>
            </form>

            <!-- List Farmers -->
            <table class="table table-bordered table-sm align-middle">
                <thead><tr class="table-light"><th>ID</th><th>Name</th><th>Username</th><th>Contact</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php while($f = $farmers->fetch_assoc()): ?>
                    <tr>
                        <td><?= $f['farmer_id'] ?></td>
                        <td><?= htmlspecialchars($f['name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($f['username'] ?? '') ?></td>
                        <td><?= htmlspecialchars($f['contact'] ?? '') ?></td>
                        <td>
                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editFarmer<?= $f['farmer_id'] ?>">Edit</button>
                            <a href="?delete_farmer=<?= $f['farmer_id'] ?>" class="btn btn-sm btn-danger">Delete</a>
                        </td>
                    </tr>

                    <!-- Edit Farmer Modal -->
                    <div class="modal fade" id="editFarmer<?= $f['farmer_id'] ?>" tabindex="-1">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <form method="POST">
                            <div class="modal-header"><h5>Edit Farmer</h5></div>
                            <div class="modal-body">
                                <input type="hidden" name="farmer_id" value="<?= $f['farmer_id'] ?>">
                                <input type="text" name="name" value="<?= htmlspecialchars($f['name'] ?? '') ?>" class="form-control mb-2" required>
                                <input type="text" name="username" value="<?= htmlspecialchars($f['username'] ?? '') ?>" class="form-control mb-2" required>
                                <input type="text" name="password" value="<?= htmlspecialchars($f['password'] ?? '') ?>" class="form-control mb-2" required>
                                <input type="text" name="contact" value="<?= htmlspecialchars($f['contact'] ?? '') ?>" class="form-control mb-2">
                            </div>
                            <div class="modal-footer">
                                <button type="submit" name="update_farmer" class="btn btn-primary">Save</button>
                            </div>
                          </form>
                        </div>
                      </div>
                    </div>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- VETS -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">Veterinarians</div>
        <div class="card-body">
            <!-- Add Vet -->
            <form method="POST" class="row g-2 mb-3">
                <div class="col"><input type="text" name="vet_name" placeholder="Name" class="form-control" required></div>
                <div class="col"><input type="email" name="email" placeholder="Email" class="form-control" required></div>
                <div class="col"><input type="password" name="password" placeholder="Password" class="form-control" required></div>
                <div class="col"><input type="text" name="contact" placeholder="Contact" class="form-control"></div>
                <div class="col-auto"><button type="submit" name="add_vet" class="btn btn-primary">Add</button></div>
            </form>

            <!-- List Vets -->
            <table class="table table-bordered table-sm align-middle">
                <thead><tr class="table-light"><th>ID</th><th>Name</th><th>Email</th><th>Contact</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php while($v = $vets->fetch_assoc()): ?>
                    <tr>
                        <td><?= $v['vet_id'] ?></td>
                        <td><?= htmlspecialchars($v['vet_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($v['email'] ?? '') ?></td>
                        <td><?= htmlspecialchars($v['contact'] ?? '') ?></td>
                        <td>
                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editVet<?= $v['vet_id'] ?>">Edit</button>
                            <a href="?delete_vet=<?= $v['vet_id'] ?>" class="btn btn-sm btn-danger">Delete</a>
                        </td>
                    </tr>

                    <!-- Edit Vet Modal -->
                    <div class="modal fade" id="editVet<?= $v['vet_id'] ?>" tabindex="-1">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <form method="POST">
                            <div class="modal-header"><h5>Edit Veterinarian</h5></div>
                            <div class="modal-body">
                                <input type="hidden" name="vet_id" value="<?= $v['vet_id'] ?>">
                                <input type="text" name="vet_name" value="<?= htmlspecialchars($v['vet_name'] ?? '') ?>" class="form-control mb-2" required>
                                <input type="email" name="email" value="<?= htmlspecialchars($v['email'] ?? '') ?>" class="form-control mb-2" required>
                                <input type="text" name="password" value="<?= htmlspecialchars($v['password'] ?? '') ?>" class="form-control mb-2" required>
                                <input type="text" name="contact" value="<?= htmlspecialchars($v['contact'] ?? '') ?>" class="form-control mb-2">
                            </div>
                            <div class="modal-footer">
                                <button type="submit" name="update_vet" class="btn btn-primary">Save</button>
                            </div>
                          </form>
                        </div>
                      </div>
                    </div>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
