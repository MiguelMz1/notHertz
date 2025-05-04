<?php
$host = "localhost";
$username = "xxxxxxxxxxxx";
$password = "";
$database = "cccccccccc";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed.");
}

$car_id = $_GET['car_id'];
$sql = "DELETE FROM cars WHERE car_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $car_id);
$stmt->execute(); 
$stmt->close();

$conn->close();

$message = "Delete";
header("Location: notHertz.php?message=" . urlencode($message));
exit;
?>