<?php
include_once "dbconn.php";

if (!isset($conn) || $conn->connect_error) {
    die("Database connection error.");
}

$employee = null;
$employee_id = filter_input(INPUT_GET, 'employee_id', FILTER_VALIDATE_INT);

if (!$employee_id) {
    die("Invalid Employee ID.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $form_employee_id = $_POST['employee_id'];
    $first_name = $_POST["first_name"];
    $last_name = $_POST["last_name"];
    $role = $_POST["role"];
    $email = $_POST["email"]; 

    $sql = "UPDATE employees SET first_name = ?, last_name = ?, role = ?, email = ? WHERE employee_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $first_name, $last_name, $role, $email, $form_employee_id);
    $stmt->execute(); 
    $stmt->close();
    $conn->close();

    $message = "Updated!";
    header("Location: employees.php?message=" . urlencode($message));
    exit();

} else {
    $stmt = $conn->prepare("SELECT * FROM employees WHERE employee_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $employee_id);
        if ($stmt->execute()) {
             $result = $stmt->get_result();
             $employee = $result ? $result->fetch_assoc() : null;
             if (!$employee) { die("Employee not found."); }
         } else { die("Error."); }
        $stmt->close();
    } else { die("Error ."); }
    $conn->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Employee</title>
    <style>
        body { font-family: sans-serif; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type=text], input[type=email] { width: 300px; padding: 5px; margin-bottom: 10px; border: 1px solid #ccc;}
        button { padding: 8px 15px; }
    </style>
</head>
<body>
    <h2>Edit Employee Details</h2>

    <?php if ($employee): ?>
    <form method="POST" action="editEmployee.php?employee_id=<?php echo htmlspecialchars($employee['employee_id']); ?>">
         <input type="hidden" name="employee_id" value="<?php echo htmlspecialchars($employee['employee_id']); ?>">
        <label for="first_name">First Name:</label>
        <input type="text" id="first_name" name="first_name" required value="<?php echo htmlspecialchars($employee['first_name']); ?>">
        <label for="last_name">Last Name:</label>
        <input type="text" id="last_name" name="last_name" required value="<?php echo htmlspecialchars($employee['last_name']); ?>">
        <label for="role">Role:</label>
        <input type="text" id="role" name="role" required value="<?php echo htmlspecialchars($employee['role']); ?>">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($employee['email']); ?>">
        <button type="submit">Update Employee</button>
    </form>
    <?php else: ?>
        <p>Could not load employee data.</p>
    <?php endif; ?>
    <p><a href="employees.php">Back to Employee List</a></p>
</body>
</html>