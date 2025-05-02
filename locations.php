<?php
include_once "dbconn.php";

if (!$conn) {
    die("Database connection failed.");
}
$message = $_GET['message'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Rental Locations</title>
    <style>
        body { font-family: sans-serif; margin: 15px;}
        table { border-collapse: collapse; width: 100%; margin-top: 15px; border: 1px solid #ccc;}
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #eee; }
        a { margin-right: 10px; text-decoration: none; }
         a.delete-link { color: red; }
        .message { padding: 10px; margin-bottom: 15px; border: 1px solid green; background-color: #e6ffe6; }
        .error { border: 1px solid red; background-color: #ffe6e6; }
        .add-link { display: inline-block; margin-bottom: 15px; padding: 8px 12px; background-color: #eee; border: 1px solid #ccc; color: black; }
    </style>
</head>
<body>
    <h2>Manage Rental Locations</h2>

    <?php if ($message): ?>
        <p class="message <?php echo (strpos(strtolower(urldecode($message)), 'error') !== false) ? 'error' : ''; ?>">
            <?php echo htmlspecialchars(urldecode($message)); ?>
        </p>
    <?php endif; ?>

    <a href="addLocation.php" class="add-link">Add New Location</a>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Location Name</th>
                <th>Address</th>
                <th>Phone</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT location_id, location_name, address, phone FROM locations ORDER BY location_name";
            $result = $conn->query($sql);

            if ($result === false) {
                echo "<tr><td colspan='5' style='color: red;'>SQL Error fetching locations.</td></tr>";
            } elseif ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['location_id']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['location_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['address']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['phone']) . "</td>";
                    echo "<td>";
                    echo "<a href='editLocation.php?location_id=" . $row['location_id'] . "'>Edit</a>";
                    echo "<a href='deleteLocation.php?location_id=" . $row['location_id'] . "' onclick='return confirm(\"Are you sure?\");' class='delete-link'>Delete</a>";
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No locations found.</td></tr>";
            }

            if ($result && !($result === false)) {
                 $result->free();
            }
            $conn->close();
            ?>
        </tbody>
    </table>
     <p><a href="index.php">Back to Main Menu</a></p>
</body>
</html>