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
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
</head>

<body class="bg-light !grid !place-items-center !h-dvh w-full"
    style="background: linear-gradient(45deg, #4a7c59 25%, #5d8b6b 25%, #5d8b6b 50%, #4a7c59 50%, #4a7c59 75%, #5d8b6b 75%, #5d8b6b); background-size: 60px 60px; background-repeat: repeat;">
</body>
<div class="container ">
    <div class="col-md-4 mx-auto">
        <div class="card shadow !rounded-none">
            <div
                class="card-header text-2xl font-bold text-[#262423] text-center !rounded-none !bg-[#d0c5c0] !border-b-3 border-b-[#a69e9a]">
                Login
            </div>
            <div class="card-body !p-10 ">
                <?php if(isset($_GET['error'])): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
                <?php endif; ?>

                <form method="POST" action="auth.php">
                    <div class="mb-3">
                        <label class="text-[#69696a]">Username / Email</label>
                        <input type="text" name="username"
                            class=" form-control !border-[#313232] !rounded-none !focus:outline-2 !focus:outline-offset-2 !focus:outline-violet-500 active:bg-violet-700"
                            required>
                    </div>
                    <div class="mb-3">
                        <label class="text-[#69696a]">Password</label>
                        <input type="password" name="password" class="form-control !border-[#313232] !rounded-none"
                            required>
                    </div>
                    <div class="mb-3">
                        <label class="text-[#69696a]">Role</label>
                        <select name="role" class="form-select !rounded-none !border-[#313232]" required>
                            <option value="">Select Role</option>
                            <option value="admin">Admin</option>
                            <option value="farmer">Farmer</option>
                            <option value="vet">Vet</option>
                        </select>
                    </div>
                    <button type="submit"
                        class="btn w-100 hover:!bg-[#367723]  text-white !bg-[#3c8527] !border-t-5 !border-b-5 !border-t-[#52a535] !border-b-[#2a641c] !rounded-none">Login</button>
                </form>
                <a href="main.php"
                    class="btn  w-100 mt-3 hover:!bg-[#d9d1cd]  text-[#262423] !bg-[#d0c5c0] !border-t-5 !border-b-5 !border-t-[#ede5e2] !border-b-[#aba09c] !rounded-none">Go
                    to Main Page</a>

            </div>
        </div>
    </div>
</div>
</body>

</html>