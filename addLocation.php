<?php
include_once "dbconn.php";
$error_message = '';
$success_message = '';

$location_name = $_POST["location_name"] ?? '';
$address = $_POST["address"] ?? '';
$phone = $_POST["phone"] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (empty($location_name) || empty($address) || empty($phone)) {
        $error_message = "All fields are required.";
    } else {
        $sql = "INSERT INTO locations (location_name, address, phone) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("sss", $location_name, $address, $phone);

            if ($stmt->execute()) {
                $success_message = "Location added successfully!";
                header("Location: locations.php?message=" . urlencode($success_message));
                exit();
            } else {
                $error_message = "Error adding location";
            }
            $stmt->close();
        } else {
            $error_message = "Error preparing request.";
        }
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add New Location</title>
     <style>
        body { font-family: sans-serif; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type=text], input[type=tel], textarea { width: 300px; padding: 5px; margin-bottom: 10px; border: 1px solid #ccc; }
        textarea { height: 80px; }
        button { padding: 8px 15px; }
        .error { color: red; border: 1px solid red; padding: 10px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <h2>Add New Rental Location</h2>

    <?php if ($error_message): ?>
        <div class="error"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <form method="POST" action="addLocation.php">
        <label for="location_name">Location Name:</label>
        <input type="text" id="location_name" name="location_name" required value="<?php echo htmlspecialchars($location_name); ?>">

        <label for="address">Address:</label>
        <textarea id="address" name="address" required><?php echo htmlspecialchars($address); ?></textarea>

        <label for="phone">Phone:</label>
        <input type="tel" id="phone" name="phone" required value="<?php echo htmlspecialchars($phone); ?>">

        <button type="submit">Add Location</button>
    </form>

    <p><a href="locations.php">Back to Location List</a></p>
</body>
</html>