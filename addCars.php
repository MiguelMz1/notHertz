<?php
include "dbconn.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $brand = $_POST["brand"];
    $model = $_POST["model"];
    $year = $_POST["year"];
    $license_plate = $_POST["license_plate"];
    $rental_rate = $_POST["rental_rate"];
    $mileage = $_POST["mileage"];
    $fuel_type = $_POST["fuel_type"];
    $category_id = $_POST["category_id"];

    $sql = "INSERT INTO cars (brand, model, year, license_plate, rental_rate, status, mileage, fuel_type, category_id) 
            VALUES ('$brand', '$model', '$year', '$license_plate', '$rental_rate', 'available', '$mileage', '$fuel_type', '$category_id')";

    if ($conn->query($sql) === TRUE) {
        header("Location: notHertz.php?message=Car added succesfully"); //pop not working yet; will fix this later
        exit();
        
    } else {
        echo "<p style='color: red;'>Error: " . $sql . "<br>" . $conn->error . "</p>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Not Hertz</title>
</head>
<body>
    <h2>Add a New Car</h2>
    <form method="POST" action="addCars.php">
        <label>Brand: <input type="text" name="brand" required></label><br>
        <label>Model: <input type="text" name="model" required></label><br>
        <label>Year: <input type="number" name="year" required></label><br>
        <label>License Plate: <input type="text" name="license_plate" required></label><br>
        <label>Rental Rate: <input type="number" step="0.01" name="rental_rate" required></label><br>
        <label>Mileage: <input type="number" name="mileage" required></label><br>
        <label>Fuel Type:
            <select name="fuel_type" required>
                <option value="gasoline">Gasoline</option>
                <option value="diesel">Diesel</option>
                <option value="electric">Electric</option>
                <option value="hybrid">Hybrid</option>
            </select>
        </label><br>
        <!--Category List: 1=SUV; 2=Sedan; etc..-->
        <label>Category ID: <input type="number" name="category_id" required></label><br>
        <button type="submit">Add Car</button>
    </form>
</body>
</html>
