<?php
include_once "dbconn.php";

if (!isset($conn) || $conn->connect_error) {
    die("Connection failed.");
}

$payment_id = $_GET['payment_id']; 

$sql = "DELETE FROM payments WHERE payment_id = ?";
$stmt = $conn->prepare($sql);

$stmt->bind_param("i", $payment_id);
$stmt->execute();
$stmt->close();

$conn->close();

$message = "Deleted!";
header("Location: payments.php?message=" . urlencode($message));
exit();
?>