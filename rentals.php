<?php
include_once "dbconn.php";
$message = $_GET['message'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Rental Agreements</title>
    <style>
        body { font-family: sans-serif; margin: 15px; }
        table { border-collapse: collapse; width: 100%; margin-top: 15px; margin-bottom: 25px; border: 1px solid #ccc; font-size: 0.9em; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; vertical-align: top; }
        th { background-color: #eee; white-space: nowrap; }
        td { white-space: normal; }
        a { margin-right: 8px; white-space: nowrap; text-decoration: none;}
        a.cancel-link { color: orange; }
        a.edit-link { color: blue; }
        .message { padding: 10px; margin-bottom: 15px; border: 1px solid green; background-color: #e6ffe6;}
        .error { border: 1px solid red; background-color: #ffe6e6; }
        .add-link { display: inline-block; margin-bottom: 15px; padding: 8px 12px; background-color: #eee; border: 1px solid #ccc; color: black; }
        .status-active { color: green; font-weight: bold; }
        .status-completed { color: blue; }
        .status-cancelled { color: red; text-decoration: line-through; }
        hr { margin: 30px 0; border: 0; border-top: 1px solid #ccc; }
    </style>
</head>
<body>
    <h1>Manage Rental Agreements</h1>

    <?php if ($message): ?>
        <p class="message <?php echo (strpos(strtolower(urldecode($message)), 'error') !== false) ? 'error' : ''; ?>">
            <?php echo htmlspecialchars(urldecode($message)); ?>
        </p>
    <?php endif; ?>

    <a href="addRental.php" class="add-link">Add New Rental Agreement</a>

    <hr>
    <h2>Active Rentals</h2>
    <div style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Phone</th>
                    <th>Car</th>
                    <th>Employee</th>
                    <th>Pickup</th>
                    <th>Dropoff</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Cost ($)</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql_active = "SELECT * FROM CurrentRentalsSummary ORDER BY rental_start DESC"; //CurrentRentalsSummary view to clean the code 
                $result_active = $conn->query($sql_active);

                if ($result_active === false) {
                    echo "<tr><td colspan='11' style='color: red;'>Error</td></tr>";
                } elseif ($result_active->num_rows > 0) {
                    while ($row = $result_active->fetch_assoc()) {
                        $start_dt = date('Y-m-d H:i', strtotime($row['rental_start']));
                        $end_dt = date('Y-m-d H:i', strtotime($row['rental_end']));
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['rental_id']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['customer_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['customer_phone']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['car_details']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['employee_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['pickup_location']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['dropoff_location']) . "</td>";
                        echo "<td>" . htmlspecialchars($start_dt) . "</td>";
                        echo "<td>" . htmlspecialchars($end_dt) . "</td>";
                        echo "<td style='text-align: right;'>" . htmlspecialchars(number_format($row['total_cost'], 2)) . "</td>";
                        echo "<td>";
                        echo "<a href='editRental.php?rental_id=" . $row['rental_id'] . "' class='edit-link'>Edit</a>";
                        echo "<a href='cancelRental.php?rental_id=" . $row['rental_id'] . "' onclick='return confirm(\"Cancel this rental?\");' class='cancel-link'>Cancel</a>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='11'>No active rentals found.</td></tr>";
                }
                 if ($result_active && !($result_active === false)) { $result_active->free(); }
                ?>
            </tbody>
        </table>
    </div>

    <hr>
    <h2>Rental History</h2>
     <div style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Phone</th>
                    <th>Car</th>
                    <th>Employee</th>
                    <th>Pickup</th>
                    <th>Dropoff</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Cost ($)</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                 <?php
                $sql_past = "SELECT * FROM PastRentalsSummary ORDER BY rental_end DESC, rental_start DESC"; //PastRentalsSummary view to clean the code 
                $result_past = $conn->query($sql_past);

                if ($result_past === false) {
                    echo "<tr><td colspan='12' style='color: red;'>Error.</td></tr>";
                } elseif ($result_past->num_rows > 0) {
                    while ($row = $result_past->fetch_assoc()) {
                        $start_dt = date('Y-m-d H:i', strtotime($row['rental_start']));
                        $end_dt = date('Y-m-d H:i', strtotime($row['rental_end']));
                        $status_class = 'status-' . strtolower($row['status']);

                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['rental_id']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['customer_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['customer_phone']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['car_details']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['employee_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['pickup_location']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['dropoff_location']) . "</td>";
                        echo "<td>" . htmlspecialchars($start_dt) . "</td>";
                        echo "<td>" . htmlspecialchars($end_dt) . "</td>";
                        echo "<td style='text-align: right;'>" . htmlspecialchars(number_format($row['total_cost'], 2)) . "</td>";
                        echo "<td class='" . $status_class . "'>" . htmlspecialchars(ucfirst($row['status'])) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='12'>No rental history found.</td></tr>";
                }
                if ($result_past && !($result_past === false)) { $result_past->free(); }
                $conn->close(); 
                ?>
            </tbody>
        </table>
    </div>

     <p><a href="index.php">Back to Main Menu</a></p>
</body>
</html>