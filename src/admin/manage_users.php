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
    <script src="https://cdn.tailwindcss.com/4.0.0-alpha.9"></script>
    <style>
    body {
        background-color: #171615 !important;
        background-image:
            radial-gradient(circle at 1px 1px, #2a2825 1px, transparent 0),
            radial-gradient(circle at 3px 3px, #1a1917 1px, transparent 0);
        background-size: 8px 8px, 16px 16px;
        color: #ede5e2 !important;
    }

    .card {
        background-color: #312f2e !important;
        border: 2px solid #272626 !important;
        border-top: 2px solid #4a4845 !important;
        border-left: 2px solid #4a4845 !important;
        box-shadow: inset 1px 1px 0 #4a4845, inset -1px -1px 0 #0e0d0d !important;
    }

    .card-header {
        background-color: #7bc05a !important;
        color: #0e0d0d !important;
        font-weight: bold !important;
        border: 2px solid #272626 !important;
        border-top: 2px solid #8fd65f !important;
        border-left: 2px solid #8fd65f !important;
        box-shadow: inset 1px 1px 0 #8fd65f, inset -1px -1px 0 #6aa054 !important;
    }

    .table {
        background-color: #0e0d0d !important;
        color: #ede5e2 !important;
        border: 2px solid #272626 !important;
    }

    .table th {
        background-color: #272626 !important;
        color: #ede5e2 !important;
        border: 1px solid #171615 !important;
        border-top: 2px solid #4a4845 !important;
        border-left: 2px solid #4a4845 !important;
        font-weight: bold !important;
    }

    .table td {
        background-color: #0e0d0d !important;
        color: #ede5e2 !important;
        border: 1px solid #272626 !important;
    }

    .table tbody tr:hover {
        background-color: #1a1917 !important;
    }

    .form-control {
        background-color: #0e0d0d !important;
        color: #ede5e2 !important;
        border: 2px solid #272626 !important;
        border-bottom: 2px solid #4a4845 !important;
        border-right: 2px solid #4a4845 !important;
        box-shadow: inset 1px 1px 0 #171615 !important;
    }

    .form-control:focus {
        background-color: #1a1917 !important;
        border-color: #7bc05a !important;
        box-shadow: 0 0 0 0.2rem rgba(123, 192, 90, 0.25) !important;
        color: #ede5e2 !important;
    }

    .form-control::placeholder {
        color: #94878e !important;
    }

    .btn-success {
        background-color: #7bc05a !important;
        color: #0e0d0d !important;
        border: 2px solid #272626 !important;
        border-top: 2px solid #8fd65f !important;
        border-left: 2px solid #8fd65f !important;
        box-shadow: inset 1px 1px 0 #8fd65f, inset -1px -1px 0 #6aa054 !important;
        font-weight: bold !important;
    }

    .btn-primary {
        background-color: #5a9bd4 !important;
        color: #0e0d0d !important;
        border: 2px solid #272626 !important;
        border-top: 2px solid #6fb0e8 !important;
        border-left: 2px solid #6fb0e8 !important;
        box-shadow: inset 1px 1px 0 #6fb0e8, inset -1px -1px 0 #4a85b8 !important;
        font-weight: bold !important;
    }

    .btn-warning {
        background-color: #f4b942 !important;
        color: #0e0d0d !important;
        border: 2px solid #272626 !important;
        border-top: 2px solid #f7c966 !important;
        border-left: 2px solid #f7c966 !important;
        box-shadow: inset 1px 1px 0 #f7c966, inset -1px -1px 0 #d19c35 !important;
        font-weight: bold !important;
    }

    .btn-danger {
        background-color: #dc3545 !important;
        color: #ede5e2 !important;
        border: 2px solid #272626 !important;
        border-top: 2px solid #e85e6d !important;
        border-left: 2px solid #e85e6d !important;
        box-shadow: inset 1px 1px 0 #e85e6d, inset -1px -1px 0 #b02a37 !important;
        font-weight: bold !important;
    }

    .btn-secondary {
        background-color: #6c757d !important;
        color: #ede5e2 !important;
        border: 2px solid #272626 !important;
        border-top: 2px solid #868e96 !important;
        border-left: 2px solid #868e96 !important;
        box-shadow: inset 1px 1px 0 #868e96, inset -1px -1px 0 #545b62 !important;
        font-weight: bold !important;
    }

    .btn:hover {
        transform: translateY(1px) !important;
        box-shadow: none !important;
    }

    .modal-content {
        background-color: #312f2e !important;
        border: 2px solid #272626 !important;
        border-top: 2px solid #4a4845 !important;
        border-left: 2px solid #4a4845 !important;
        box-shadow: inset 1px 1px 0 #4a4845, inset -1px -1px 0 #0e0d0d !important;
    }

    .modal-header {
        background-color: #272626 !important;
        color: #ede5e2 !important;
        border-bottom: 2px solid #171615 !important;
    }

    .modal-header h5 {
        color: #ede5e2 !important;
        font-weight: bold !important;
    }

    .modal-body {
        background-color: #312f2e !important;
        color: #ede5e2 !important;
    }

    .modal-footer {
        background-color: #272626 !important;
        border-top: 2px solid #171615 !important;
    }

    h1,
    h2,
    h3,
    h4,
    h5,
    h6 {
        color: #ede5e2 !important;
    }
    </style>
</head>

<body
    style="background-color: #171615; background-image: radial-gradient(circle at 1px 1px, #2a2825 1px, transparent 0), radial-gradient(circle at 3px 3px, #1a1917 1px, transparent 0); background-size: 8px 8px, 16px 16px; color: #ede5e2;">
    <div class="container mt-4">
        <h3 class="text-center mb-4">Admin Dashboard - Manage Users</h3>
        <a href="admin_dashboard.php" class="btn btn-secondary">⬅ Back to Dashboard</a>
        <!-- FARMERS -->
        <div class="card mb-4"
            style="background-color: #312f2e; border: 2px solid #272626; border-top: 2px solid #4a4845; border-left: 2px solid #4a4845; box-shadow: inset 1px 1px 0 #4a4845, inset -1px -1px 0 #0e0d0d;">
            <div class="card-header"
                style="background-color: #7bc05a; color: #0e0d0d; font-weight: bold; border: 2px solid #272626; border-top: 2px solid #8fd65f; border-left: 2px solid #8fd65f; box-shadow: inset 1px 1px 0 #8fd65f, inset -1px -1px 0 #6aa054;">
                Farmers</div>
            <div class="card-body">
                <!-- Add Farmer -->
                <form method="POST" class="row g-2 mb-3">
                    <div class="col"><input type="text" name="name" placeholder="Name" class="form-control" required>
                    </div>
                    <div class="col"><input type="text" name="username" placeholder="Username" class="form-control"
                            required></div>
                    <div class="col"><input type="password" name="password" placeholder="Password" class="form-control"
                            required></div>
                    <div class="col"><input type="text" name="contact" placeholder="Contact" class="form-control"></div>
                    <div class="col-auto"><button type="submit" name="add_farmer" class="btn btn-success">Add</button>
                    </div>
                </form>

                <!-- List Farmers -->
                <table class="table table-bordered table-sm align-middle"
                    style="background-color: #0e0d0d; color: #ede5e2; border: 2px solid #272626;">
                    <thead>
                        <tr style="background-color: #272626; color: #ede5e2;">
                            <th
                                style="background-color: #272626; color: #ede5e2; border: 1px solid #171615; border-top: 2px solid #4a4845; border-left: 2px solid #4a4845; font-weight: bold;">
                                ID</th>
                            <th
                                style="background-color: #272626; color: #ede5e2; border: 1px solid #171615; border-top: 2px solid #4a4845; border-left: 2px solid #4a4845; font-weight: bold;">
                                Name</th>
                            <th
                                style="background-color: #272626; color: #ede5e2; border: 1px solid #171615; border-top: 2px solid #4a4845; border-left: 2px solid #4a4845; font-weight: bold;">
                                Username</th>
                            <th
                                style="background-color: #272626; color: #ede5e2; border: 1px solid #171615; border-top: 2px solid #4a4845; border-left: 2px solid #4a4845; font-weight: bold;">
                                Contact</th>
                            <th
                                style="background-color: #272626; color: #ede5e2; border: 1px solid #171615; border-top: 2px solid #4a4845; border-left: 2px solid #4a4845; font-weight: bold;">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($f = $farmers->fetch_assoc()): ?>
                        <tr style="background-color: #0e0d0d;">
                            <td style="background-color: #0e0d0d; color: #ede5e2; border: 1px solid #272626;">
                                <?= $f['farmer_id'] ?></td>
                            <td style="background-color: #0e0d0d; color: #ede5e2; border: 1px solid #272626;">
                                <?= htmlspecialchars($f['name'] ?? '') ?></td>
                            <td style="background-color: #0e0d0d; color: #ede5e2; border: 1px solid #272626;">
                                <?= htmlspecialchars($f['username'] ?? '') ?></td>
                            <td style="background-color: #0e0d0d; color: #ede5e2; border: 1px solid #272626;">
                                <?= htmlspecialchars($f['contact'] ?? '') ?></td>
                            <td style="background-color: #0e0d0d; color: #ede5e2; border: 1px solid #272626;">
                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                    data-bs-target="#editFarmer<?= $f['farmer_id'] ?>"
                                    style="background-color: #f4b942; color: #0e0d0d; border: 2px solid #272626; border-top: 2px solid #f7c966; border-left: 2px solid #f7c966; box-shadow: inset 1px 1px 0 #f7c966, inset -1px -1px 0 #d19c35; font-weight: bold;">Edit</button>
                                <a href="?delete_farmer=<?= $f['farmer_id'] ?>" class="btn btn-sm btn-danger"
                                    style="background-color: #dc3545; color: #ede5e2; border: 2px solid #272626; border-top: 2px solid #e85e6d; border-left: 2px solid #e85e6d; box-shadow: inset 1px 1px 0 #e85e6d, inset -1px -1px 0 #b02a37; font-weight: bold;">Delete</a>
                            </td>
                        </tr>

                        <!-- Edit Farmer Modal -->
                        <div class="modal fade" id="editFarmer<?= $f['farmer_id'] ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content"
                                    style="background-color: #312f2e; border: 2px solid #272626; border-top: 2px solid #4a4845; border-left: 2px solid #4a4845; box-shadow: inset 1px 1px 0 #4a4845, inset -1px -1px 0 #0e0d0d;">
                                    <form method="POST">
                                        <div class="modal-header"
                                            style="background-color: #272626; color: #ede5e2; border-bottom: 2px solid #171615;">
                                            <h5 style="color: #ede5e2; font-weight: bold;">Edit Farmer</h5>
                                        </div>
                                        <div class="modal-body" style="background-color: #312f2e; color: #ede5e2;">
                                            <input type="hidden" name="farmer_id" value="<?= $f['farmer_id'] ?>">
                                            <input type="text" name="name"
                                                value="<?= htmlspecialchars($f['name'] ?? '') ?>"
                                                class="form-control mb-2"
                                                style="background-color: #0e0d0d; color: #ede5e2; border: 2px solid #272626; border-bottom: 2px solid #4a4845; border-right: 2px solid #4a4845; box-shadow: inset 1px 1px 0 #171615;"
                                                required>
                                            <input type="text" name="username"
                                                value="<?= htmlspecialchars($f['username'] ?? '') ?>"
                                                class="form-control mb-2"
                                                style="background-color: #0e0d0d; color: #ede5e2; border: 2px solid #272626; border-bottom: 2px solid #4a4845; border-right: 2px solid #4a4845; box-shadow: inset 1px 1px 0 #171615;"
                                                required>
                                            <input type="text" name="password"
                                                value="<?= htmlspecialchars($f['password'] ?? '') ?>"
                                                class="form-control mb-2"
                                                style="background-color: #0e0d0d; color: #ede5e2; border: 2px solid #272626; border-bottom: 2px solid #4a4845; border-right: 2px solid #4a4845; box-shadow: inset 1px 1px 0 #171615;"
                                                required>
                                            <input type="text" name="contact"
                                                value="<?= htmlspecialchars($f['contact'] ?? '') ?>"
                                                class="form-control mb-2"
                                                style="background-color: #0e0d0d; color: #ede5e2; border: 2px solid #272626; border-bottom: 2px solid #4a4845; border-right: 2px solid #4a4845; box-shadow: inset 1px 1px 0 #171615;">
                                        </div>
                                        <div class="modal-footer"
                                            style="background-color: #272626; border-top: 2px solid #171615;">
                                            <button type="submit" name="update_farmer" class="btn btn-primary"
                                                style="background-color: #5a9bd4; color: #0e0d0d; border: 2px solid #272626; border-top: 2px solid #6fb0e8; border-left: 2px solid #6fb0e8; box-shadow: inset 1px 1px 0 #6fb0e8, inset -1px -1px 0 #4a85b8; font-weight: bold;">Save</button>
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
        <div class="card mb-4"
            style="background-color: #312f2e; border: 2px solid #272626; border-top: 2px solid #4a4845; border-left: 2px solid #4a4845; box-shadow: inset 1px 1px 0 #4a4845, inset -1px -1px 0 #0e0d0d;">
            <div class="card-header"
                style="background-color: #5a9bd4; color: #0e0d0d; font-weight: bold; border: 2px solid #272626; border-top: 2px solid #6fb0e8; border-left: 2px solid #6fb0e8; box-shadow: inset 1px 1px 0 #6fb0e8, inset -1px -1px 0 #4a85b8;">
                Veterinarians</div>
            <div class="card-body">
                <!-- Add Vet -->
                <form method="POST" class="row g-2 mb-3">
                    <div class="col"><input type="text" name="vet_name" placeholder="Name" class="form-control"
                            required></div>
                    <div class="col"><input type="email" name="email" placeholder="Email" class="form-control" required>
                    </div>
                    <div class="col"><input type="password" name="password" placeholder="Password" class="form-control"
                            required></div>
                    <div class="col"><input type="text" name="contact" placeholder="Contact" class="form-control"></div>
                    <div class="col-auto"><button type="submit" name="add_vet" class="btn btn-primary">Add</button>
                    </div>
                </form>

                <!-- List Vets -->
                <table class="table table-bordered table-sm align-middle"
                    style="background-color: #0e0d0d; color: #ede5e2; border: 2px solid #272626;">
                    <thead>
                        <tr style="background-color: #272626; color: #ede5e2;">
                            <th
                                style="background-color: #272626; color: #ede5e2; border: 1px solid #171615; border-top: 2px solid #4a4845; border-left: 2px solid #4a4845; font-weight: bold;">
                                ID</th>
                            <th
                                style="background-color: #272626; color: #ede5e2; border: 1px solid #171615; border-top: 2px solid #4a4845; border-left: 2px solid #4a4845; font-weight: bold;">
                                Name</th>
                            <th
                                style="background-color: #272626; color: #ede5e2; border: 1px solid #171615; border-top: 2px solid #4a4845; border-left: 2px solid #4a4845; font-weight: bold;">
                                Email</th>
                            <th
                                style="background-color: #272626; color: #ede5e2; border: 1px solid #171615; border-top: 2px solid #4a4845; border-left: 2px solid #4a4845; font-weight: bold;">
                                Contact</th>
                            <th
                                style="background-color: #272626; color: #ede5e2; border: 1px solid #171615; border-top: 2px solid #4a4845; border-left: 2px solid #4a4845; font-weight: bold;">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($v = $vets->fetch_assoc()): ?>
                        <tr style="background-color: #0e0d0d;">
                            <td style="background-color: #0e0d0d; color: #ede5e2; border: 1px solid #272626;">
                                <?= $v['vet_id'] ?></td>
                            <td style="background-color: #0e0d0d; color: #ede5e2; border: 1px solid #272626;">
                                <?= htmlspecialchars($v['vet_name'] ?? '') ?></td>
                            <td style="background-color: #0e0d0d; color: #ede5e2; border: 1px solid #272626;">
                                <?= htmlspecialchars($v['email'] ?? '') ?></td>
                            <td style="background-color: #0e0d0d; color: #ede5e2; border: 1px solid #272626;">
                                <?= htmlspecialchars($v['contact'] ?? '') ?></td>
                            <td style="background-color: #0e0d0d; color: #ede5e2; border: 1px solid #272626;">
                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                    data-bs-target="#editVet<?= $v['vet_id'] ?>"
                                    style="background-color: #f4b942; color: #0e0d0d; border: 2px solid #272626; border-top: 2px solid #f7c966; border-left: 2px solid #f7c966; box-shadow: inset 1px 1px 0 #f7c966, inset -1px -1px 0 #d19c35; font-weight: bold;">Edit</button>
                                <a href="?delete_vet=<?= $v['vet_id'] ?>" class="btn btn-sm btn-danger"
                                    style="background-color: #dc3545; color: #ede5e2; border: 2px solid #272626; border-top: 2px solid #e85e6d; border-left: 2px solid #e85e6d; box-shadow: inset 1px 1px 0 #e85e6d, inset -1px -1px 0 #b02a37; font-weight: bold;">Delete</a>
                            </td>
                        </tr>

                        <!-- Edit Vet Modal -->
                        <div class="modal fade" id="editVet<?= $v['vet_id'] ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content"
                                    style="background-color: #312f2e; border: 2px solid #272626; border-top: 2px solid #4a4845; border-left: 2px solid #4a4845; box-shadow: inset 1px 1px 0 #4a4845, inset -1px -1px 0 #0e0d0d;">
                                    <form method="POST">
                                        <div class="modal-header"
                                            style="background-color: #272626; color: #ede5e2; border-bottom: 2px solid #171615;">
                                            <h5 style="color: #ede5e2; font-weight: bold;">Edit Veterinarian</h5>
                                        </div>
                                        <div class="modal-body" style="background-color: #312f2e; color: #ede5e2;">
                                            <input type="hidden" name="vet_id" value="<?= $v['vet_id'] ?>">
                                            <input type="text" name="vet_name"
                                                value="<?= htmlspecialchars($v['vet_name'] ?? '') ?>"
                                                class="form-control mb-2"
                                                style="background-color: #0e0d0d; color: #ede5e2; border: 2px solid #272626; border-bottom: 2px solid #4a4845; border-right: 2px solid #4a4845; box-shadow: inset 1px 1px 0 #171615;"
                                                required>
                                            <input type="email" name="email"
                                                value="<?= htmlspecialchars($v['email'] ?? '') ?>"
                                                class="form-control mb-2"
                                                style="background-color: #0e0d0d; color: #ede5e2; border: 2px solid #272626; border-bottom: 2px solid #4a4845; border-right: 2px solid #4a4845; box-shadow: inset 1px 1px 0 #171615;"
                                                required>
                                            <input type="text" name="password"
                                                value="<?= htmlspecialchars($v['password'] ?? '') ?>"
                                                class="form-control mb-2"
                                                style="background-color: #0e0d0d; color: #ede5e2; border: 2px solid #272626; border-bottom: 2px solid #4a4845; border-right: 2px solid #4a4845; box-shadow: inset 1px 1px 0 #171615;"
                                                required>
                                            <input type="text" name="contact"
                                                value="<?= htmlspecialchars($v['contact'] ?? '') ?>"
                                                class="form-control mb-2"
                                                style="background-color: #0e0d0d; color: #ede5e2; border: 2px solid #272626; border-bottom: 2px solid #4a4845; border-right: 2px solid #4a4845; box-shadow: inset 1px 1px 0 #171615;">
                                        </div>
                                        <div class="modal-footer"
                                            style="background-color: #272626; border-top: 2px solid #171615;">
                                            <button type="submit" name="update_vet" class="btn btn-primary"
                                                style="background-color: #5a9bd4; color: #0e0d0d; border: 2px solid #272626; border-top: 2px solid #6fb0e8; border-left: 2px solid #6fb0e8; box-shadow: inset 1px 1px 0 #6fb0e8, inset -1px -1px 0 #4a85b8; font-weight: bold;">Save</button>
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