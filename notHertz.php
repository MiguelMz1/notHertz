<?php
$host = "localhost";
$username = "";
$password = "";
$database = "";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed.");
}

$message = $_GET['message'] ?? '';
$result = $conn->query("SELECT * FROM cars ORDER BY car_id");

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>NOT Hertz</title>
    <style>
        body { font-family: sans-serif; margin: 15px; }
        table { border-collapse: collapse; width: 100%; margin-top: 15px; border: 1px solid #ccc; font-size: 0.9em; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background-color: #eee; }
        a { text-decoration: none; margin-right: 5px;}
        a.delete-link { color: red; }
        .message { padding: 10px; margin-bottom: 15px; border: 1px solid green; background-color: #e6ffe6; }
        .error { border: 1px solid red; background-color: #ffe6e6; }
        .add-link { display: inline-block; margin-bottom: 15px; padding: 8px 12px; background-color: #eee; border: 1px solid #ccc; color: black; }
    </style>
</head>
<body>
    <h2>NOT HERTZ - Car Management</h2>

     <?php if ($message): ?>
        <p class="message <?php echo (strpos(strtolower(urldecode($message)), 'error') !== false) ? 'error' : ''; ?>">
            <?php echo htmlspecialchars(urldecode($message)); ?>
        </p>
    <?php endif; ?>

    <a href="addCars.php" class="add-link">Add New Car</a>

    <h3>Registered Cars</h3>
    <div style="overflow-x:auto;">
        <table>
            <tr>
                <th>ID</th>
                <th>Brand</th>
                <th>Model</th>
                <th>Year</th>
                <th>License Plate</th>
                <th>Rate</th>
                <th>Status</th>
                <th>Mileage</th>
                <th>Fuel</th>
                <th>Cat ID</th>
                <th>Actions</th>
            </tr>
            <?php
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>" . htmlspecialchars($row['car_id']) . "</td>
                            <td>" . htmlspecialchars($row['brand']) . "</td>
                            <td>" . htmlspecialchars($row['model']) . "</td>
                            <td>" . htmlspecialchars($row['year']) . "</td>
                            <td>" . htmlspecialchars($row['license_plate']) . "</td>
                            <td>" . htmlspecialchars($row['rental_rate']) . "</td>
                            <td>" . htmlspecialchars($row['status']) . "</td>
                            <td>" . htmlspecialchars($row['mileage']) . "</td>
                            <td>" . htmlspecialchars($row['fuel_type']) . "</td>
                            <td>" . htmlspecialchars($row['category_id']) . "</td>
                            <td>
                                <a href='editCars.php?car_id={$row['car_id']}'>Edit</a>
                                <a href='deleteCars.php?car_id={$row['car_id']}' onclick='return confirm(\"Are you sure?\");' class='delete-link'>Delete</a>
                            </td>
                        </tr>";
                }
            } else {
                echo "<tr><td colspan='11'>No cars found</td></tr>";
            }
            if($result) { $result->free(); }
            ?>
        </table>
    </div>
     <p><a href="index.php">Back to Main Menu</a></p>
</body>
</html>