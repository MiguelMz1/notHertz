<?php
include "dbconn.php";

if (isset($_GET['car_id'])) {
    $car_id = $_GET['car_id'];

    $sql = "DELETE FROM cars WHERE car_id = $car_id";
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color: green;'>Car deleted successfully!</p>";
    } else {
        echo "<p style='color: red;'>Error deleting car: " . $conn->error . "</p>";
    }
}

$conn->close();

header("Location: notHertz.php"); 
exit;
?>
