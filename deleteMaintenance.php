<?php
include_once "dbconn.php";

if (!isset($conn) || $conn->connect_error) {
    die("Connection failed.");
}

$maintenance_id = $_GET['maintenance_id'];

$sql = "DELETE FROM maintenance WHERE maintenance_id = ?";
$stmt = $conn->prepare($sql);

$stmt->bind_param("i", $maintenance_id);
$stmt->execute();
$stmt->close();

$conn->close();

$message = "Deleted!";
header("Location: maintenance.php?message=" . urlencode($message));
exit();
?>