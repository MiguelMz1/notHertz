<?php
include_once "dbconn.php";
$error_msg = '';
$success_msg = '';

$rentals = [];
$sql_rentals = "SELECT ra.rental_id, c.first_name, c.last_name, car.brand, car.model, ra.rental_start, ra.status
                FROM rental_agreements ra
                JOIN customers c ON ra.customer_id = c.customer_id
                JOIN cars car ON ra.car_id = car.car_id
                ORDER BY ra.rental_start DESC";
$result_rentals = $conn->query($sql_rentals);
if ($result_rentals) {
    while ($row = $result_rentals->fetch_assoc()) {
        $rentals[] = $row;
    }
}

$rental_id = $_POST['rental_id'] ?? '';
$amount = $_POST['amount'] ?? '';
$method = $_POST['method'] ?? '';
$payment_date = $_POST['payment_date'] ?? date('Y-m-d\TH:i');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $rental_id_int = filter_var($rental_id, FILTER_VALIDATE_INT);
    $amount_float = filter_var($amount, FILTER_VALIDATE_FLOAT);
    $valid_methods = ['credit_card', 'debit_card', 'cash', 'paypal'];

    if (!$rental_id_int || ($amount_float === false || $amount_float <= 0) || empty($method) || !in_array($method, $valid_methods) || empty($payment_date)) {
        $error_msg = "All fields are required and must be valid.";
    } else {
        $payment_dt = null;
        try {
            $payment_dt = new DateTime($payment_date);
        } catch(Exception $e){
            $error_msg = "Invalid payment date format.";
        }

        if (!$error_msg && $payment_dt) {
            $sql = "INSERT INTO payments (rental_id, payment_date, amount, method) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);

            if ($stmt) {
                $payment_date_db = $payment_dt->format('Y-m-d H:i:s');
                $stmt->bind_param("isdss", $rental_id_int, $payment_date_db, $amount_float, $method);

                if ($stmt->execute()) {
                    $success_msg = "Payment recorded successfully!";
                    header("Location: payments.php?message=" . urlencode($success_msg));
                    exit();
                } else {
                    $error_msg = "Error recording payment.";
                }
                $stmt->close();
            } else {
                $error_msg = "Error preparing request.";
            }
        }
    }
    $conn->close();
} else {
    $conn->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add New Payment</title>
     <style>
        body { font-family: sans-serif; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type=text], input[type=number], input[type=datetime-local], select { width: 350px; padding: 5px; margin-bottom: 10px; border: 1px solid #ccc; }
        button { padding: 8px 15px; }
        .error { color: red; border: 1px solid red; padding: 10px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <h2>Record New Payment</h2>

    <?php if ($error_msg): ?>
        <div class="error"><?php echo $error_msg; ?></div>
    <?php endif; ?>

    <form method="POST" action="addPayment.php">

        <label for="rental_id">Rental Agreement:</label>
        <select id="rental_id" name="rental_id" required>
            <option value="">-- Select Rental --</option>
            <?php foreach ($rentals as $rental_item):
                $start_dt_display = new DateTime($rental_item['rental_start']);
            ?>
                <option value="<?php echo $rental_item['rental_id']; ?>" <?php echo ($rental_id == $rental_item['rental_id']) ? 'selected' : ''; ?>>
                    <?php echo "ID: " . htmlspecialchars($rental_item['rental_id']) . " - " .
                               htmlspecialchars($rental_item['last_name'] . ', ' . $rental_item['first_name']) . " - " .
                               htmlspecialchars($rental_item['brand'] . ' ' . $rental_item['model']) . " (" .
                               $start_dt_display->format('Y-m-d') . ") Status: " . htmlspecialchars($rental_item['status']);
                    ?>
                </option>
            <?php endforeach; ?>
             <?php if (empty($rentals)) echo "<option value='' disabled>No rentals found</option>"; ?>
        </select>

        <label for="amount">Amount ($):</label>
        <input type="number" id="amount" name="amount" step="0.01" min="0.01" required value="<?php echo htmlspecialchars($amount); ?>">

        <label for="payment_date">Payment Date:</label>
        <input type="datetime-local" id="payment_date" name="payment_date" required value="<?php echo htmlspecialchars($payment_date); ?>">

        <label for="method">Payment Method:</label>
        <select id="method" name="method" required>
            <option value="">-- Select Method --</option>
            <option value="credit_card" <?php echo ($method == 'credit_card') ? 'selected' : ''; ?>>Credit Card</option>
            <option value="debit_card" <?php echo ($method == 'debit_card') ? 'selected' : ''; ?>>Debit Card</option>
            <option value="cash" <?php echo ($method == 'cash') ? 'selected' : ''; ?>>Cash</option>
            <option value="paypal" <?php echo ($method == 'paypal') ? 'selected' : ''; ?>>PayPal</option>
        </select>

        <button type="submit">Record Payment</button>
    </form>

    <p><a href="payments.php">Back to Payment List</a></p>
</body>
</html>