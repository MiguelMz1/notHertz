<?php
include_once "dbconn.php";

$rental_id = filter_input(INPUT_GET, 'rental_id', FILTER_VALIDATE_INT);
$message = '';

if (!$rental_id) {
    $message = "Error: Invalid Rental ID.";
    header("Location: rentals.php?message=" . urlencode($message));
    exit();
}

$sql = "UPDATE rental_agreements SET status = 'cancelled' WHERE rental_id = ? AND status = 'active'";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $rental_id);
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $message = "Rental cancelled successfully!";
        } else {
            $message = "Error: Rental not found or not active.";
        }
    } else {
        $message = "Error.";
    }
    $stmt->close();
} else {
    $message = "Error";
}

$conn->close();
header("Location: rentals.php?message=" . urlencode($message));
exit();
?>