<?php
include_once "dbconn.php";

if (!isset($conn) || $conn->connect_error) {
    die("Connection failed.");
}

$category_id = $_GET['category_id'];

$sql = "DELETE FROM car_categories WHERE category_id = ?";
$stmt = $conn->prepare($sql);

$stmt->bind_param("i", $category_id);
$stmt->execute(); 
$stmt->close();

$conn->close();

$message = "Delete";
header("Location: categories.php?message=" . urlencode($message));
exit();
?>