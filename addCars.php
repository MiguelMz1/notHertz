<?php

$host = "localhost";
$username = "xxxxxxxxxxxx";
$password = "xxxxxxxxxxxx";
$database = "xxxxxxxxxxxx";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed.");
}

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $brand = $_POST["brand"] ?? '';
    $model = $_POST["model"] ?? '';
    $year = $_POST["year"] ?? '';
    $license_plate = $_POST["license_plate"] ?? '';
    $rental_rate = $_POST["rental_rate"] ?? '';
    $mileage = $_POST["mileage"] ?? '';
    $fuel_type = $_POST["fuel_type"] ?? '';
    $category_id = $_POST["category_id"] ?? '';

    if (empty($brand) || empty($model) || empty($year) || empty($license_plate) || empty($rental_rate) || empty($mileage) || empty($fuel_type) || empty($category_id)) {
        $error_message = "Please fill in all fields.";
    } else {
        $sql = "INSERT INTO cars (brand, model, year, license_plate, rental_rate, status, mileage, fuel_type, category_id)
                VALUES (?, ?, ?, ?, ?, 'available', ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ssisdisi", $brand, $model, $year, $license_plate, $rental_rate, $mileage, $fuel_type, $category_id);
            if ($stmt->execute()) {
                header("Location: notHertz.php?message=Car+added");
                exit();
            } else {
                $error_message = "Error adding car.";
            }
            $stmt->close();
        } else {
            $error_message = "Error preparing request.";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add New Car</title>
    <style>
        body { font-family: sans-serif; }
        label { display: block; margin-bottom: 5px; }
        input, select { margin-bottom: 10px; width: 250px; padding: 5px; border: 1px solid #ccc; }
        button { padding: 8px 15px; }
        .error { color: red; margin-bottom: 10px; }
    </style>
</head>
<body>
    <h2>Add a New Car</h2>

    <?php if ($error_message): ?>
        <p class="error"><?php echo $error_message; ?></p>
    <?php endif; ?>

    <form method="POST" action="addCars.php">
        <label>Brand: <input type="text" name="brand" required value="<?php echo htmlspecialchars($_POST['brand'] ?? ''); ?>"></label>
        <label>Model: <input type="text" name="model" required value="<?php echo htmlspecialchars($_POST['model'] ?? ''); ?>"></label>
        <label>Year: <input type="number" name="year" required value="<?php echo htmlspecialchars($_POST['year'] ?? ''); ?>"></label>
        <label>License Plate: <input type="text" name="license_plate" required value="<?php echo htmlspecialchars($_POST['license_plate'] ?? ''); ?>"></label>
        <label>Rental Rate: <input type="number" step="0.01" name="rental_rate" required value="<?php echo htmlspecialchars($_POST['rental_rate'] ?? ''); ?>"></label>
        <label>Mileage: <input type="number" name="mileage" required value="<?php echo htmlspecialchars($_POST['mileage'] ?? ''); ?>"></label>
        <label>Fuel Type:
            <select name="fuel_type" required>
                <option value="gasoline" <?php echo (($_POST['fuel_type'] ?? '') == 'gasoline') ? 'selected' : ''; ?>>Gasoline</option>
                <option value="diesel" <?php echo (($_POST['fuel_type'] ?? '') == 'diesel') ? 'selected' : ''; ?>>Diesel</option>
                <option value="electric" <?php echo (($_POST['fuel_type'] ?? '') == 'electric') ? 'selected' : ''; ?>>Electric</option>
                <option value="hybrid" <?php echo (($_POST['fuel_type'] ?? '') == 'hybrid') ? 'selected' : ''; ?>>Hybrid</option>
            </select>
        </label>
        <label>Category ID: <input type="number" name="category_id" required value="<?php echo htmlspecialchars($_POST['category_id'] ?? ''); ?>"></label>
        <button type="submit">Add Car</button>
    </form>
    <p><a href="notHertz.php">Back to Main Page</a></p>
</body>
</html>