<?php
include_once "dbconn.php";

if (!isset($conn) || $conn->connect_error) {
    die("Connection failed.");
}

$employee_id = $_GET['employee_id']; 

$sql = "DELETE FROM employees WHERE employee_id = ?";
$stmt = $conn->prepare($sql);

$stmt->bind_param("i", $employee_id);
$stmt->execute();
$stmt->close();

$conn->close();

$message = "Deleted!";
header("Location: employees.php?message=" . urlencode($message));
exit();
?>