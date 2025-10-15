<?php
include '../connDB.php';
include '../session_check.php';
require_role('vet');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_health'])) {
    $livestock_id = $_POST['livestock_id'] ?? null;
    $treatment = trim($_POST['treatment'] ?? '');

    if (!$livestock_id || !$treatment) {
        $_SESSION['error'] = "Please fill in all required fields.";
        header("Location: health_records_list.php");
        exit();
    }

    $vet_id = $_SESSION['user_id']; // Logged-in vet

    // Insert record securely
    $stmt = $conn->prepare("INSERT INTO Health_Records (livestock_id, vet_id, treatment_date, treatment) VALUES (?, ?, CURDATE(), ?)");
    $stmt->bind_param("iis", $livestock_id, $vet_id, $treatment);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Health record added successfully!";
    } else {
        $_SESSION['error'] = "Failed to add health record: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

    header("Location: health_records_list.php");
    exit();
} else {
    // Invalid access
    header("Location: health_records_list.php");
    exit();
}
?>
