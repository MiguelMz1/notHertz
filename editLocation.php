<?php
include_once "dbconn.php";

if (!isset($conn) || $conn->connect_error) {
    die("Database connection error.");
}

$location = null;
$location_id = filter_input(INPUT_GET, 'location_id', FILTER_VALIDATE_INT);

if (!$location_id) {
    die("Invalid Location ID");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $form_location_id = $_POST['location_id'];
    $location_name = $_POST["location_name"];
    $address = $_POST["address"];
    $phone = $_POST["phone"];

    $sql = "UPDATE locations SET location_name = ?, address = ?, phone = ? WHERE location_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $location_name, $address, $phone, $form_location_id);
    $stmt->execute(); 
    $stmt->close();
    $conn->close();

    $message = "Updated!";
    header("Location: locations.php?message=" . urlencode($message));
    exit();

} else {
    $stmt = $conn->prepare("SELECT * FROM locations WHERE location_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $location_id);
         if ($stmt->execute()) {
             $result = $stmt->get_result();
             $location = $result ? $result->fetch_assoc() : null;
             if (!$location) { die("Location not found."); }
         } else { die("Error"); }
        $stmt->close();
    } else { die("Error"); }
    $conn->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Location</title>
    <style>
        body { font-family: sans-serif; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type=text], input[type=tel], textarea { width: 300px; padding: 5px; margin-bottom: 10px; border: 1px solid #ccc;}
        textarea { height: 80px; }
        button { padding: 8px 15px; }
    </style>
</head>
<body>
    <h2>Edit Rental Location</h2>

    <?php if ($location): ?>
    <form method="POST" action="editLocation.php?location_id=<?php echo htmlspecialchars($location['location_id']); ?>">
         <input type="hidden" name="location_id" value="<?php echo htmlspecialchars($location['location_id']); ?>">
        <label for="location_name">Location Name:</label>
        <input type="text" id="location_name" name="location_name" required value="<?php echo htmlspecialchars($location['location_name']); ?>">
        <label for="address">Address:</label>
        <textarea id="address" name="address" required><?php echo htmlspecialchars($location['address']); ?></textarea>
        <label for="phone">Phone:</label>
        <input type="tel" id="phone" name="phone" required value="<?php echo htmlspecialchars($location['phone']); ?>">
        <button type="submit">Update Location</button>
    </form>
    <?php else: ?>
        <p>Could not load location data.</p>
    <?php endif; ?>
    <p><a href="locations.php">Back to Location List</a></p>
</body>
</html>