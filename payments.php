<?php
include_once "dbconn.php";
$message = $_GET['message'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
    <title>View Payments</title>
    <style>
        body { font-family: sans-serif; margin: 15px;}
        table { border-collapse: collapse; width: 100%; margin-top: 15px; border: 1px solid #ccc; font-size: 0.9em; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #eee; white-space: nowrap;}
        .message { padding: 10px; margin: 10px 0; border: 1px solid green; background-color: #e6ffe6; }
        .error { border: 1px solid red; background-color: #ffe6e6; }
        .add-link { margin-bottom: 15px; display: inline-block; padding: 8px 12px; background-color: #eee; border: 1px solid #ccc; color: black; text-decoration: none;}
        a { margin-right: 10px; text-decoration: none; }
        a.delete-link { color: red; }
        .amount { text-align: right; }
    </style>
</head>
<body>
    <h2>Payment Records</h2>

    <?php if ($message): ?>
        <p class="message <?php echo (strpos(strtolower(urldecode($message)), 'error') !== false) ? 'error' : ''; ?>">
            <?php echo htmlspecialchars(urldecode($message)); ?>
        </p>
    <?php endif; ?>

    <a href="addPayment.php" class="add-link">Add New Payment</a>

    <div style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th>Pay ID</th>
                    <th>Rental ID</th>
                    <th>Customer</th>
                    <th>Payment Date</th>
                    <th>Amount ($)</th>
                    <th>Method</th>
                    <th>Trans. ID</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                 $sql = "SELECT p.*, c.first_name, c.last_name
                        FROM payments p
                        JOIN rental_agreements ra ON p.rental_id = ra.rental_id
                        JOIN customers c ON ra.customer_id = c.customer_id
                        ORDER BY p.payment_date DESC, p.payment_id DESC";
                $result = $conn->query($sql);

                if ($result === false) {
                     echo "<tr><td colspan='8' style='color: red;'>Error fetching payments.</td></tr>";
                } elseif ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $payment_dt = date('Y-m-d H:i', strtotime($row['payment_date']));
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['payment_id']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['rental_id']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($payment_dt) . "</td>";
                        echo "<td class='amount'>" . htmlspecialchars(number_format($row['amount'], 2)) . "</td>";
                        echo "<td>" . htmlspecialchars(ucfirst(str_replace('_', ' ', $row['method']))) . "</td>";
                        echo "<td>" . htmlspecialchars($row['transaction_id']) . "</td>";
                        echo "<td>";
                        echo "<a href='editPayment.php?payment_id=" . $row['payment_id'] . "'>Edit</a>";
                        echo "<a href='deletePayment.php?payment_id=" . $row['payment_id'] . "' class='delete-link' onclick='return confirm(\"Delete payment? Not recommended for tracking.\");'>Delete</a>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='8'>No payment records found.</td></tr>";
                }
                if($result) { $result->free(); }
                $conn->close();
                ?>
            </tbody>
        </table>
    </div>
     <p><a href="index.php">Back to Main Menu</a></p>
</body>
</html>