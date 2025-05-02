<?php
include_once "dbconn.php";

if (!isset($conn) || $conn->connect_error) {
    die("Connection failed.");
}

$customer_id = $_GET['customer_id']; 

$sql = "DELETE FROM customers WHERE customer_id = ?";
$stmt = $conn->prepare($sql);

$stmt->bind_param("i", $customer_id);
$stmt->execute();
$stmt->close();

$conn->close();

$message = "Deleted!";
header("Location: customers.php?message=" . urlencode($message));
exit();
?>