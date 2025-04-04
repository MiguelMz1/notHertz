<?php
include "dbconn.php";

//Displaying all cars
$result = $conn->query("SELECT * FROM cars");

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
    <h2>NOT HERTZ</h2>

    <a href="addCars.php">Add a New Car</a>

    <h2>Registered Cars</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Brand</th>
            <th>Model</th>
            <th>Year</th>
            <th>License Plate</th>
            <th>Rental Rate</th>
            <th>Status</th>
            <th>Mileage</th>
            <th>Fuel Type</th>
            <th>Category ID</th>
            <th>Actions</th>
        </tr>
        
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['car_id']}</td>
                        <td>{$row['brand']}</td>
                        <td>{$row['model']}</td>
                        <td>{$row['year']}</td>
                        <td>{$row['license_plate']}</td>
                        <td>{$row['rental_rate']}</td>
                        <td>{$row['status']}</td>
                        <td>{$row['mileage']}</td>
                        <td>{$row['fuel_type']}</td>
                        <td>{$row['category_id']}</td>
                        <td>
                            <a href='editCars.php?car_id={$row['car_id']}'>Edit</a> |
                            <a href='deleteCars.php?car_id={$row['car_id']}' onclick='return confirm(\"Are you sure you want to delete this car?\");'>Delete</a>
                        </td>
                    </tr>";
            }
        } else {
            echo "<tr><td colspan='11'>No cars found</td></tr>";
        }
        ?>
    </table>
    
</body>
</html>
