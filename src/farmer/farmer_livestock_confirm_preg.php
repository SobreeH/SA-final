<?php
include '../connDB.php';
include '../session_check.php';
require_role('farmer');

$id = $_GET['id'] ?? null;
$status = $_GET['status'] ?? null;

if (!$id || !in_array($status, ['pregnant','not_pregnant'], true)) {
    die("Invalid request.");
}

// Insert a new breeding record with updated pregnancy result
$stmt = $conn->prepare("INSERT INTO Breeding_Records (livestock_id, date_inseminated, pregnancy_result) VALUES (?, CURDATE(), ?)");
$stmt->bind_param("is", $id, $status);
$stmt->execute();

header("Location: farmer_livestock.php");
exit;
