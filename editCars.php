<?php
include "dbconn.php";

if (isset($_GET['car_id'])) {
    $car_id = $_GET['car_id'];
    $result = $conn->query("SELECT * FROM cars WHERE car_id = $car_id");
    $car = $result->fetch_assoc();

    if (!$car) {
        echo "Car not found.";
        exit;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $brand = $_POST["brand"];
    $model = $_POST["model"];
    $year = $_POST["year"];
    $license_plate = $_POST["license_plate"];
    $rental_rate = $_POST["rental_rate"];
    $mileage = $_POST["mileage"];
    $fuel_type = $_POST["fuel_type"];
    $category_id = $_POST["category_id"];

    $sql = "UPDATE cars SET brand='$brand', model='$model', year='$year', license_plate='$license_plate', 
            rental_rate='$rental_rate', mileage='$mileage', fuel_type='$fuel_type', category_id='$category_id' 
            WHERE car_id = $car_id";

    if ($conn->query($sql) === TRUE) {
        header("Location: notHertz.php?message=Car updated succesfully"); //popup not working yet; will fix later
        exit();
    } else {
        echo "<p style='color: red;'>Error updating car: " . $conn->error . "</p>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NOT Hertz</title>
</head>
<body>
    <h2>Edit Car</h2>
    <form method="POST" action="editCars.php?car_id=<?php echo $car['car_id']; ?>">
        <label>Brand: <input type="text" name="brand" value="<?php echo $car['brand']; ?>" required></label><br>
        <label>Model: <input type="text" name="model" value="<?php echo $car['model']; ?>" required></label><br>
        <label>Year: <input type="number" name="year" value="<?php echo $car['year']; ?>" required></label><br>
        <label>License Plate: <input type="text" name="license_plate" value="<?php echo $car['license_plate']; ?>" required></label><br>
        <label>Rental Rate: <input type="number" step="0.01" name="rental_rate" value="<?php echo $car['rental_rate']; ?>" required></label><br>
        <label>Mileage: <input type="number" name="mileage" value="<?php echo $car['mileage']; ?>" required></label><br>
        <label>Fuel Type:
            <select name="fuel_type" required>
                <option value="gasoline" <?php echo $car['fuel_type'] == 'gasoline' ? 'selected' : ''; ?>>Gasoline</option>
                <option value="diesel" <?php echo $car['fuel_type'] == 'diesel' ? 'selected' : ''; ?>>Diesel</option>
                <option value="electric" <?php echo $car['fuel_type'] == 'electric' ? 'selected' : ''; ?>>Electric</option>
                <option value="hybrid" <?php echo $car['fuel_type'] == 'hybrid' ? 'selected' : ''; ?>>Hybrid</option>
            </select>
        </label><br>
        <!--Category List: 1=SUV; 2=Sedan; etc..-->
        <label>Category ID: <input type="number" name="category_id" value="<?php echo $car['category_id']; ?>" required></label><br>
        <button type="submit">Update Car</button>
    </form>
</body>
</html>
