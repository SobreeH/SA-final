<?php
include '../connDB.php';
include '../session_check.php';
require_role('farmer');

$id = (int)($_GET['id'] ?? 0);
if (!$id) die("Invalid livestock ID.");

// Delete livestock
$stmt = $conn->prepare("DELETE FROM Livestock WHERE livestock_id=?");
$stmt->bind_param("i",$id);
if($stmt->execute()){
    $stmt->close();
    header("Location: farmer_livestock.php?msg=deleted");
    exit();
}else{
    die("Error deleting livestock: ".$stmt->error);
}
?>
