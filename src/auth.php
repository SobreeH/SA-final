<?php
include 'connDB.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $conn->real_escape_string($_POST['password']);

    // Check Farmer
    $res = $conn->query("SELECT * FROM Farmer WHERE username='$username' AND password='$password'");
    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $_SESSION['user_id'] = $row['farmer_id'];
        $_SESSION['role'] = 'farmer';
        header("Location: farmer/farmer_dashboard.php");
        exit();
    }

    // Check Vet
    $res = $conn->query("SELECT * FROM Veterinarian WHERE email='$username' AND password='$password'");
    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $_SESSION['user_id'] = $row['vet_id'];
        $_SESSION['role'] = 'vet';
        header("Location: vet/vet_dashboard.php");
        exit();
    }

    // Login failed
    header("Location: login.php?error=Invalid credentials");
}
?>
