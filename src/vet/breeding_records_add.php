<?php
include '../connDB.php';
include '../session_check.php';
require_role('vet');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_breeding'])) {
    $livestock_id = $_POST['livestock_id'] ?? null;
    $date_inseminated = $_POST['date_inseminated'] ?? '';
    $pregnancy_result = $_POST['pregnancy_result'] ?? 'unknown';

    if (!$livestock_id || !$date_inseminated) {
        $_SESSION['error'] = "Please fill in all required fields.";
        header("Location: breeding_records_list.php");
        exit();
    }

    $vet_id = $_SESSION['user_id']; // Logged-in vet

    // Insert record securely
    $stmt = $conn->prepare("INSERT INTO Breeding_Records (livestock_id, vet_id, date_inseminated, pregnancy_result, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("iiss", $livestock_id, $vet_id, $date_inseminated, $pregnancy_result);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Breeding record added successfully!";
    } else {
        $_SESSION['error'] = "Failed to add breeding record: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

    header("Location: breeding_records_list.php");
    exit();
} else {
    // Invalid access
    header("Location: breeding_records_list.php");
    exit();
}
?>