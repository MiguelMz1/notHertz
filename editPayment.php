<?php
include_once "dbconn.php";

if (!isset($conn) || $conn->connect_error) {
    die("Database connection error.");
}

$payment = null;
$payment_id = filter_input(INPUT_GET, 'payment_id', FILTER_VALIDATE_INT);

if (!$payment_id) {
    die("Invalid Payment ID");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $form_payment_id = $_POST['payment_id'];
    $amount = $_POST['amount']; 
    $method = $_POST['method']; 
    $payment_date_input = $_POST['payment_date']; 

    $payment_date_db = date('Y-m-d H:i:s', strtotime($payment_date_input));

    $sql = "UPDATE payments SET payment_date = ?, amount = ?, method = ?
            WHERE payment_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdsi", $payment_date_db, $amount, $method, $form_payment_id);
    $stmt->execute(); 
    $stmt->close();
    $conn->close();

    $message = "Updated!";
    header("Location: payments.php?message=" . urlencode($message));
    exit();

} else {
    $rentals = [];
    $sql_rentals = "SELECT ra.rental_id, c.last_name, c.first_name, ra.rental_start
                    FROM rental_agreements ra JOIN customers c ON ra.customer_id = c.customer_id
                    ORDER BY ra.rental_start DESC";
    $result_rentals = $conn->query($sql_rentals);
    if ($result_rentals) { while ($row = $result_rentals->fetch_assoc()) { $rentals[] = $row; } }

    $sql_fetch = "SELECT payment_id, rental_id, payment_date, amount, method FROM payments WHERE payment_id = ?";
    $stmt_fetch = $conn->prepare($sql_fetch);
    if($stmt_fetch) {
        $stmt_fetch->bind_param("i", $payment_id);
        if ($stmt_fetch->execute()) {
            $result = $stmt_fetch->get_result();
            $payment = $result ? $result->fetch_assoc() : null;
            if (!$payment) { die("Payment record not found."); }
            $payment['payment_date'] = date('Y-m-d\TH:i', strtotime($payment['payment_date']));
        } else { die("Error fetching payment data."); }
        $stmt_fetch->close();
    } else { die("Error preparing fetch."); }
    $conn->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Payment</title>
    <style>
        body { font-family: sans-serif; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type=text], input[type=number], input[type=datetime-local], select { width: 300px; padding: 5px; margin-bottom: 10px; border: 1px solid #ccc;}
        select[disabled] { background-color: #eee; }
        button { padding: 8px 15px; }
        .info { font-style: italic; color: #555; margin-bottom: 10px; }
    </style>
</head>
<body>
    <h2>Edit Payment (ID: <?php echo htmlspecialchars($payment_id); ?>)</h2>

    <?php if ($payment): ?>
    <form method="POST" action="editPayment.php?payment_id=<?php echo htmlspecialchars($payment_id); ?>">
        <input type="hidden" name="payment_id" value="<?php echo htmlspecialchars($payment_id); ?>">
        <input type="hidden" name="rental_id" value="<?php echo htmlspecialchars($payment['rental_id']); ?>">

        <label for="rental_id_display">Rental Agreement:</label>
        <select id="rental_id_display" disabled>
            <option value="">-- Select Rental --</option>
            <?php foreach ($rentals as $rental_item):
                $start_dt_display = date('Y-m-d', strtotime($rental_item['rental_start']));
                $selected = ($payment['rental_id'] == $rental_item['rental_id']) ? 'selected' : '';
            ?>
                <option value="<?php echo $rental_item['rental_id']; ?>" <?php echo $selected; ?>>
                     <?php echo "ID: " . htmlspecialchars($rental_item['rental_id']) . " - " .
                                htmlspecialchars($rental_item['last_name'] . ', ' . $rental_item['first_name']) . " (" .
                                htmlspecialchars($start_dt_display) . ")";
                    ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p class="info">Rental Agreement cannot be changed.</p>

        <label for="amount">Amount ($):</label>
        <input type="number" id="amount" name="amount" step="0.01" min="0.01" required value="<?php echo htmlspecialchars($payment['amount']); ?>">
        <label for="payment_date">Payment Date:</label>
        <input type="datetime-local" id="payment_date" name="payment_date" required value="<?php echo htmlspecialchars($payment['payment_date']); ?>">
        <label for="method">Payment Method:</label>
        <select id="method" name="method" required>
            <option value="">-- Select Method --</option>
            <option value="credit_card" <?php echo ($payment['method'] == 'credit_card') ? 'selected' : ''; ?>>Credit Card</option>
            <option value="debit_card" <?php echo ($payment['method'] == 'debit_card') ? 'selected' : ''; ?>>Debit Card</option>
            <option value="cash" <?php echo ($payment['method'] == 'cash') ? 'selected' : ''; ?>>Cash</option>
            <option value="paypal" <?php echo ($payment['method'] == 'paypal') ? 'selected' : ''; ?>>PayPal</option>
        </select>
        <button type="submit">Update Payment</button>
    </form>
    <?php else: ?>
        <p>Could not load payment data.</p>
    <?php endif; ?>
    <p><a href="payments.php">Back to Payment List</a></p>
</body>
</html>