<?php
include_once "dbconn.php";
$error_message = '';
$success_message = '';

$first_name = $_POST["first_name"] ?? '';
$last_name = $_POST["last_name"] ?? '';
$role = $_POST["role"] ?? '';
$email = $_POST["email"] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (empty($first_name) || empty($last_name) || empty($role) || empty($email)) {
        $error_message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } else {
        $sql = "INSERT INTO employees (first_name, last_name, role, email) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("ssss", $first_name, $last_name, $role, $email);

            if ($stmt->execute()) {
                $success_message = "Employee added successfully!";
                header("Location: employees.php?message=" . urlencode($success_message));
                exit();
            } else {
                $error_message = "Error adding employee.";
            }
            $stmt->close();
        } else {
            $error_message = "Error preparing request.";
        }
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add New Employee</title>
     <style>
        body { font-family: sans-serif; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type=text], input[type=email] { width: 300px; padding: 5px; margin-bottom: 10px; border: 1px solid #ccc; }
        button { padding: 8px 15px; }
        .error { color: red; border: 1px solid red; padding: 10px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <h2>Add New Employee</h2>

    <?php if ($error_message): ?>
        <div class="error"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <form method="POST" action="addEmployee.php">
        <label for="first_name">First Name:</label>
        <input type="text" id="first_name" name="first_name" required value="<?php echo htmlspecialchars($first_name); ?>">

        <label for="last_name">Last Name:</label>
        <input type="text" id="last_name" name="last_name" required value="<?php echo htmlspecialchars($last_name); ?>">

        <label for="role">Role:</label>
        <input type="text" id="role" name="role" required value="<?php echo htmlspecialchars($role); ?>">

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($email); ?>">

        <button type="submit">Add Employee</button>
    </form>

    <p><a href="employees.php">Back to Employee List</a></p>
</body>
</html>