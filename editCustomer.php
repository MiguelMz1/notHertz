<?php
include_once "dbconn.php";

if (!isset($conn) || $conn->connect_error) {
    die("Database connection error.");
}

$customer = null;
$customer_id = filter_input(INPUT_GET, 'customer_id', FILTER_VALIDATE_INT);

if (!$customer_id) {
    die("Invalid Customer ID");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $form_customer_id = $_POST['customer_id'];
    $first_name = $_POST["first_name"];
    $last_name = $_POST["last_name"];
    $email = $_POST["email"];
    $phone = $_POST["phone"];
    $license_number = $_POST["license_number"];
    $address = $_POST["address"];
    $date_of_birth = $_POST["date_of_birth"]; 

    $sql = "UPDATE customers SET first_name = ?, last_name = ?, email = ?, phone = ?, license_number = ?, address = ?, date_of_birth = ?
            WHERE customer_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssi", $first_name, $last_name, $email, $phone, $license_number, $address, $date_of_birth, $form_customer_id);
    $stmt->execute(); 
    $stmt->close();
    $conn->close();

    $message = "Update attempt finished.";
    header("Location: customers.php?message=" . urlencode($message));
    exit();

} else {
    $stmt = $conn->prepare("SELECT * FROM customers WHERE customer_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $customer_id);
        if ($stmt->execute()) {
             $result = $stmt->get_result();
             $customer = $result ? $result->fetch_assoc() : null;
             if (!$customer) { die("Customer not found."); }
         } else { die("Error."); }
        $stmt->close();
    } else { die("Error"); }
    $conn->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Customer</title>
    <style>
        body { font-family: sans-serif; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type=text], input[type=email], input[type=tel], input[type=date], textarea { width: 300px; padding: 5px; margin-bottom: 10px; border: 1px solid #ccc;}
        textarea { height: 80px; }
        button { padding: 8px 15px; }
    </style>
</head>
<body>
    <h2>Edit Customer Details</h2>

    <?php if ($customer): ?>
    <form method="POST" action="editCustomer.php?customer_id=<?php echo htmlspecialchars($customer['customer_id']); ?>">
         <input type="hidden" name="customer_id" value="<?php echo htmlspecialchars($customer['customer_id']); ?>">
        <label for="first_name">First Name:</label>
        <input type="text" id="first_name" name="first_name" required value="<?php echo htmlspecialchars($customer['first_name']); ?>">
        <label for="last_name">Last Name:</label>
        <input type="text" id="last_name" name="last_name" required value="<?php echo htmlspecialchars($customer['last_name']); ?>">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($customer['email']); ?>">
        <label for="phone">Phone:</label>
        <input type="tel" id="phone" name="phone" required value="<?php echo htmlspecialchars($customer['phone']); ?>">
        <label for="license_number">License Number:</label>
        <input type="text" id="license_number" name="license_number" required value="<?php echo htmlspecialchars($customer['license_number']); ?>">
        <label for="address">Address:</label>
        <textarea id="address" name="address" required><?php echo htmlspecialchars($customer['address']); ?></textarea>
        <label for="date_of_birth">Date of Birth:</label>
        <input type="date" id="date_of_birth" name="date_of_birth" required value="<?php echo htmlspecialchars($customer['date_of_birth']); ?>">
        <button type="submit">Update Customer</button>
    </form>
    <?php else: ?>
        <p>Could not load customer data.</p>
    <?php endif; ?>
    <p><a href="customers.php">Back to Customer List</a></p>
</body>
</html>