<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Role-based access check
function require_role($role) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] != $role) {
        header("Location: ../login.php?error=Access denied");
        exit();
    }
}
?>
