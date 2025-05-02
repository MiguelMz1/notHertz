<?php
include_once "dbconn.php";
$message = $_GET['message'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Customers</title>
    <style>
        body { font-family: sans-serif; margin: 15px; }
        table { border-collapse: collapse; width: 100%; margin-top: 15px; border: 1px solid #ccc; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; white-space: nowrap; }
        th { background-color: #eee; }
        a { margin-right: 10px; text-decoration: none; }
        a.delete-link { color: red; }
        .message { padding: 10px; margin-bottom: 15px; border: 1px solid green; background-color: #e6ffe6; }
        .error { border: 1px solid red; background-color: #ffe6e6; }
        .add-link { display: inline-block; margin-bottom: 15px; padding: 8px 12px; background-color: #eee; border: 1px solid #ccc; color: black; }
    </style>
</head>
<body>
    <h2>Manage Customers</h2>

    <?php if ($message): ?>
        <p class="message <?php echo (strpos(strtolower(urldecode($message)), 'error') !== false) ? 'error' : ''; ?>">
            <?php echo htmlspecialchars(urldecode($message)); ?>
        </p>
    <?php endif; ?>

    <a href="addCustomer.php" class="add-link">Add New Customer</a>

    <div style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>License #</th>
                    <th>Address</th>
                    <th>DOB</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT customer_id, first_name, last_name, email, phone, license_number, address, date_of_birth
                        FROM customers
                        ORDER BY last_name, first_name";
                $result = $conn->query($sql);

                if ($result === false) {
                     echo "<tr><td colspan='9'>Error fetching customers.</td></tr>";
                } elseif ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['customer_id']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['first_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['last_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['phone']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['license_number']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['address']) . "</td>";
                        $dob = date_create($row['date_of_birth']);
                        echo "<td>" . ($dob ? date_format($dob, 'Y-m-d') : 'N/A') . "</td>";
                        echo "<td>";
                        echo "<a href='editCustomer.php?customer_id=" . $row['customer_id'] . "'>Edit</a>";
                        echo "<a href='rentalsByCustomer.php?customer_id=" . $row['customer_id'] . "'>Rentals</a>";
                        echo "<a href='deleteCustomer.php?customer_id=" . $row['customer_id'] . "' class='delete-link' onclick='return confirm(\"Are you sure?\");'>Delete</a>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='9'>No customers found.</td></tr>";
                }
                 if ($result && !($result === false)) { $result->free(); }
                $conn->close();
                ?>
            </tbody>
        </table>
    </div>
     <p><a href="index.php">Back to Main Menu</a></p>
</body>
</html>