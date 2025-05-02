<?php
include_once "dbconn.php";

if (!isset($conn) || $conn->connect_error) {
    die("Database connection error.");
}

$customer_id_to_check = filter_input(INPUT_GET, 'customer_id', FILTER_VALIDATE_INT);
$active_rentals = [];

if (!$customer_id_to_check) {
    die("Invalid or missing Customer ID provided.");
}

$sql = "CALL GetActiveRentalsForCustomer(?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id_to_check);
$stmt->execute(); 
$result = $stmt->get_result(); 

if ($result) { 
     while ($row = $result->fetch_assoc()) {
         $active_rentals[] = $row;
     }
    $result->free();
} 

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Active Rentals for Customer</title>
     <style>
        body { font-family: sans-serif; margin: 15px; }
        table { border-collapse: collapse; width: 100%; margin-top: 15px; border: 1px solid #ccc;}
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #eee; white-space: nowrap;}
        h2 { color: #333; }
     </style>
</head>
<body>

    <h2>Active Rentals for Customer ID(Procedure): <?php echo htmlspecialchars($customer_id_to_check); ?></h2>

    <?php if (!empty($active_rentals)): ?>
         <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Rental ID</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Brand</th>
                        <th>Model</th>
                        <th>License</th>
                        <th>Pickup</th>
                        <th>Dropoff</th>
                        <th>Cost ($)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($active_rentals as $rental):
                        $start_dt = date('Y-m-d H:i', strtotime($rental['rental_start']));
                        $end_dt = date('Y-m-d H:i', strtotime($rental['rental_end']));
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($rental['rental_id']); ?></td>
                            <td><?php echo htmlspecialchars($start_dt); ?></td>
                            <td><?php echo htmlspecialchars($end_dt); ?></td>
                            <td><?php echo htmlspecialchars($rental['car_brand']); ?></td>
                            <td><?php echo htmlspecialchars($rental['car_model']); ?></td>
                            <td><?php echo htmlspecialchars($rental['license_plate']); ?></td>
                            <td><?php echo htmlspecialchars($rental['pickup_location']); ?></td>
                            <td><?php echo htmlspecialchars($rental['dropoff_location']); ?></td>
                            <td style="text-align: right;"><?php echo htmlspecialchars(number_format($rental['total_cost'], 2)); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p>No active rentals found for this customer.</p>
    <?php endif; ?>

    <p><a href="customers.php">Back to Customer List</a></p>
    <p><a href="index.php">Back to Main Menu</a></p>

</body>
</html>