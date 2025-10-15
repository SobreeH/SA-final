<?php
// auth.php — handles login logic for all roles
session_start();
include 'connDB.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role = $_POST['role'] ?? '';

    // --- Hardcoded Admin Login ---
    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['user_id'] = 'admin';
        $_SESSION['role'] = 'admin';
        header("Location: admin/admin_dashboard.php");
        exit();
    }

    // --- Farmer Login ---
    if ($role === 'farmer') {
        $sql = "SELECT * FROM Farmer WHERE username='$username' AND password='$password'";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $_SESSION['user_id'] = $user['farmer_id'];
            $_SESSION['role'] = 'farmer';
            header("Location: farmer/farmer_dashboard.php");
            exit();
        }
    }

    // --- Vet Login ---
    if ($role === 'vet') {
        $sql = "SELECT * FROM Veterinarian WHERE email='$username' AND password='$password'";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $_SESSION['user_id'] = $user['vet_id'];
            $_SESSION['role'] = 'vet';
            header("Location: vet/vet_dashboard.php");
            exit();
        }
    }

    // --- If reached here: login failed ---
    header("Location: login.php?error=Invalid credentials");
    exit();
}
?>
