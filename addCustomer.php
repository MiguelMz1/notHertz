<?php
include_once "dbconn.php";
$error_message = '';
$success_message = '';

$first_name = $_POST["first_name"] ?? '';
$last_name = $_POST["last_name"] ?? '';
$email = $_POST["email"] ?? '';
$phone = $_POST["phone"] ?? '';
$license_number = $_POST["license_number"] ?? '';
$address = $_POST["address"] ?? '';
$date_of_birth = $_POST["date_of_birth"] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (empty($first_name) || empty($last_name) || empty($email) || empty($phone) || empty($license_number) || empty($address) || empty($date_of_birth)) {
        $error_message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } else {
        $d = DateTime::createFromFormat('Y-m-d', $date_of_birth);
        if (!$d || $d->format('Y-m-d') !== $date_of_birth) {
            $error_message = "Invalid date format. Use YYYY-MM-DD.";
        } else {
            $sql = "INSERT INTO customers (first_name, last_name, email, phone, license_number, address, date_of_birth)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);

            if ($stmt) {
                $stmt->bind_param("sssssss", $first_name, $last_name, $email, $phone, $license_number, $address, $date_of_birth);

                if ($stmt->execute()) {
                    $success_message = "Customer added successfully!";
                    header("Location: customers.php?message=" . urlencode($success_message));
                    exit();
                } else {
                    $error_message = "Error adding customer.";
                }
                $stmt->close();
            } else {
                $error_message = "Error preparing request.";
            }
        }
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add New Customer</title>
     <style>
        body { font-family: sans-serif; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type=text], input[type=email], input[type=tel], input[type=date], textarea { width: 300px; padding: 5px; margin-bottom: 10px; border: 1px solid #ccc; }
        textarea { height: 80px; }
        button { padding: 8px 15px; }
        .error { color: red; border: 1px solid red; padding: 10px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <h2>Add New Customer</h2>

    <?php if ($error_message): ?>
        <div class="error"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <form method="POST" action="addCustomer.php">
        <label for="first_name">First Name:</label>
        <input type="text" id="first_name" name="first_name" required value="<?php echo htmlspecialchars($first_name); ?>">

        <label for="last_name">Last Name:</label>
        <input type="text" id="last_name" name="last_name" required value="<?php echo htmlspecialchars($last_name); ?>">

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($email); ?>">

        <label for="phone">Phone:</label>
        <input type="tel" id="phone" name="phone" required value="<?php echo htmlspecialchars($phone); ?>">

        <label for="license_number">License Number:</label>
        <input type="text" id="license_number" name="license_number" required value="<?php echo htmlspecialchars($license_number); ?>">

        <label for="address">Address:</label>
        <textarea id="address" name="address" required><?php echo htmlspecialchars($address); ?></textarea>

        <label for="date_of_birth">Date of Birth:</label>
        <input type="date" id="date_of_birth" name="date_of_birth" required value="<?php echo htmlspecialchars($date_of_birth); ?>" placeholder="YYYY-MM-DD">

        <button type="submit">Add Customer</button>
    </form>

    <p><a href="customers.php">Back to Customer List</a></p>
</body>
</html>