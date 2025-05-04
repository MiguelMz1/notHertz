<?php

$host = "localhost";
$username = "xxxxxxxxxxxx";
$password = "xxxxxxxxxxxx";
$database = "xxxxxxxxxxxx";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed.");
}

$car = null;
$car_id = filter_input(INPUT_GET, 'car_id', FILTER_VALIDATE_INT);

if (!$car_id) {
    die("Invalid Car ID");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $form_car_id = $_POST['car_id'];
    $brand = $_POST["brand"];
    $model = $_POST["model"];
    $year = $_POST["year"];
    $license_plate = $_POST["license_plate"];
    $rental_rate = $_POST["rental_rate"];
    $mileage = $_POST["mileage"];
    $fuel_type = $_POST["fuel_type"];
    $category_id = $_POST["category_id"];

    $sql = "UPDATE cars SET brand=?, model=?, year=?, license_plate=?,
            rental_rate=?, mileage=?, fuel_type=?, category_id=?
            WHERE car_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssisdisii", $brand, $model, $year, $license_plate, $rental_rate, $mileage, $fuel_type, $category_id, $form_car_id);
    $stmt->execute(); 
    $stmt->close();
    $conn->close();

    $message = "Updated";
    header("Location: notHertz.php?message=" . urlencode($message));
    exit();

} else {
    $stmt_fetch = $conn->prepare("SELECT * FROM cars WHERE car_id = ?");
    if ($stmt_fetch) {
        $stmt_fetch->bind_param("i", $car_id);
        if ($stmt_fetch->execute()) {
             $result = $stmt_fetch->get_result();
             $car = $result ? $result->fetch_assoc() : null;
             if (!$car) { die("Car not found."); }
        } else { die("Error"); }
        $stmt_fetch->close();
    } else { die("Error"); }
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Car</title>
    <style>
        body { font-family: sans-serif; }
        label { display: block; margin-bottom: 5px; }
        input, select { margin-bottom: 10px; width: 250px; padding: 5px; border: 1px solid #ccc;}
        button { padding: 8px 15px; }
    </style>
</head>
<body>
    <h2>Edit Car Details</h2>

    <?php if ($car): ?>
    <form method="POST" action="editCars.php?car_id=<?php echo htmlspecialchars($car['car_id']); ?>">
        <input type="hidden" name="car_id" value="<?php echo htmlspecialchars($car['car_id']); ?>">
        <label>Brand: <input type="text" name="brand" value="<?php echo htmlspecialchars($car['brand']); ?>" required></label>
        <label>Model: <input type="text" name="model" value="<?php echo htmlspecialchars($car['model']); ?>" required></label>
        <label>Year: <input type="number" name="year" value="<?php echo htmlspecialchars($car['year']); ?>" required></label>
        <label>License Plate: <input type="text" name="license_plate" value="<?php echo htmlspecialchars($car['license_plate']); ?>" required></label>
        <label>Rental Rate: <input type="number" step="0.01" name="rental_rate" value="<?php echo htmlspecialchars($car['rental_rate']); ?>" required></label>
        <label>Mileage: <input type="number" name="mileage" value="<?php echo htmlspecialchars($car['mileage']); ?>" required></label>
        <label>Fuel Type:
            <select name="fuel_type" required>
                <option value="gasoline" <?php echo ($car['fuel_type'] == 'gasoline') ? 'selected' : ''; ?>>Gasoline</option>
                <option value="diesel" <?php echo ($car['fuel_type'] == 'diesel') ? 'selected' : ''; ?>>Diesel</option>
                <option value="electric" <?php echo ($car['fuel_type'] == 'electric') ? 'selected' : ''; ?>>Electric</option>
                <option value="hybrid" <?php echo ($car['fuel_type'] == 'hybrid') ? 'selected' : ''; ?>>Hybrid</option>
            </select>
        </label>
        <label>Category ID: <input type="number" name="category_id" value="<?php echo htmlspecialchars($car['category_id']); ?>" required></label>
        <button type="submit">Update Car</button>
    </form>
    <?php else: ?>
        <p>Could not load car data.</p>
    <?php endif; ?>
    <p><a href="notHertz.php">Back to Main Page</a></p>
</body>
</html>