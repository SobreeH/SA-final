<?php
session_start();
if (isset($_SESSION['role'])) {
    // Redirect if already logged in
    switch ($_SESSION['role']) {
        case 'admin': header("Location: admin/admin_dashboard.php"); exit();
        case 'farmer': header("Location: farmer/farmer_dashboard.php"); exit();
        case 'vet': header("Location: vet/vet_dashboard.php"); exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Livestock System - Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="col-md-4 mx-auto">
        <div class="card shadow">
            <div class="card-header bg-primary text-white text-center">Login</div>
            <div class="card-body">
                <?php if(isset($_GET['error'])): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
                <?php endif; ?>

                <form method="POST" action="auth.php">
                    <div class="mb-3">
                        <label>Username / Email</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Role</label>
                        <select name="role" class="form-select" required>
                            <option value="">Select Role</option>
                            <option value="admin">Admin</option>
                            <option value="farmer">Farmer</option>
                            <option value="vet">Vet</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
