<?php
include_once "dbconn.php";

if (!isset($conn) || $conn->connect_error) {
    die("Database connection error.");
}

$error_message = '';
$rental = null;
$rental_id = filter_input(INPUT_GET, 'rental_id', FILTER_VALIDATE_INT);

if (!$rental_id) {
    die("Invalid Rental ID");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $form_rental_id = $_POST['rental_id']; 
    $employee_id = $_POST['employee_id'];
    $pickup_location_id = $_POST['pickup_location_id'];
    $dropoff_location_id = $_POST['dropoff_location_id'];
    $rental_start = $_POST['rental_start']; 
    $rental_end = $_POST['rental_end'];   
    $total_cost = $_POST['total_cost'];    
    $insurance_included = isset($_POST['insurance_included']) ? 1 : 0;
    $status = $_POST['status'];       

    $start_formatted = date('Y-m-d H:i:s', strtotime($rental_start));
    $end_formatted = date('Y-m-d H:i:s', strtotime($rental_end));

    $sql_update = "UPDATE rental_agreements SET
                   employee_id = ?, pickup_location_id = ?, dropoff_location_id = ?,
                   rental_start = ?, rental_end = ?, total_cost = ?, insurance_included = ?, status = ?
                   WHERE rental_id = ?";
    $stmt_update = $conn->prepare($sql_update);

    $stmt_update->bind_param("iiissdisi",
        $employee_id, $pickup_location_id, $dropoff_location_id,
        $start_formatted, $end_formatted, $total_cost, $insurance_included, $status,
        $form_rental_id 
    );
    $stmt_update->execute();
    $stmt_update->close();


    $conn->close();

    $message = "Updated!";
    header("Location: rentals.php?message=" . urlencode($message));
    exit();

} else {
  
    $stmt = $conn->prepare("SELECT * FROM rental_agreements WHERE rental_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $rental_id);
        $stmt->execute(); 
        $result = $stmt->get_result();
        $rental = $result ? $result->fetch_assoc() : null; 
        $stmt->close();
        if (!$rental) {
            die("Rental agreement not found."); 
        }
    } else {
         die("Error in query."); 
    }

    $customers = []; $cars = []; $employees = []; $locations = [];
    $result_cust = $conn->query("SELECT customer_id, first_name, last_name FROM customers ORDER BY last_name, first_name");
    if ($result_cust) { while ($row = $result_cust->fetch_assoc()) { $customers[] = $row; } $result_cust->free(); }

    $result_cars = $conn->query("SELECT car_id, brand, model, license_plate, status FROM cars ORDER BY brand, model");
    if ($result_cars) { while ($row = $result_cars->fetch_assoc()) { $cars[] = $row; } $result_cars->free(); }

    $result_emp = $conn->query("SELECT employee_id, first_name, last_name FROM employees ORDER BY last_name, first_name");
    if ($result_emp) { while ($row = $result_emp->fetch_assoc()) { $employees[] = $row; } $result_emp->free(); }

    $result_loc = $conn->query("SELECT location_id, location_name FROM locations ORDER BY location_name");
    if ($result_loc) { while ($row = $result_loc->fetch_assoc()) { $locations[] = $row; } $result_loc->free(); }

    $conn->close(); 
}

?>
 <!DOCTYPE html>
<html>
<head>
    <title>Edit Rental Agreement</title>
     <style>
        body { font-family: sans-serif; }
        label { display: block; margin-top: 10px; font-weight: bold; }
        input[type=text], input[type=number], input[type=datetime-local], select { width: 350px; padding: 5px; margin-top: 2px; margin-bottom: 8px; border: 1px solid #ccc; }
        input[type=checkbox] { width: auto; margin-right: 5px;}
        select[disabled] { background-color: #eee; }
        button { padding: 8px 15px; margin-top: 15px; }
        .error { color: red; border: 1px solid red; padding: 10px; margin-bottom: 15px; }
        .form-section { margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #eee;}
        .info { font-style: italic; color: #555; }
     </style>
</head>
<body>
    <h2>Edit Rental Agreement (ID: <?php echo htmlspecialchars($rental_id); ?>)</h2>

    <?php if ($error_message): /* This likely won't show with removed validation */ ?>
        <div class="error"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <?php if ($rental): ?>
    <form method="POST" action="editRental.php?rental_id=<?php echo htmlspecialchars($rental['rental_id']); ?>">
         <input type="hidden" name="rental_id" value="<?php echo htmlspecialchars($rental['rental_id']); ?>">
         <input type="hidden" name="customer_id" value="<?php echo htmlspecialchars($rental['customer_id']); ?>">
         <input type="hidden" name="car_id" value="<?php echo htmlspecialchars($rental['car_id']); ?>">

         <div class="form-section">
             <label>Customer:</label>
             <select disabled>
                 <option value="">-- Select Customer --</option>
                 <?php foreach ($customers as $cust): ?>
                    <option value="<?php echo $cust['customer_id']; ?>" <?php echo ($rental['customer_id'] == $cust['customer_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cust['last_name'] . ', ' . $cust['first_name']); ?>
                    </option>
                <?php endforeach; ?>
             </select>
             <p class="info">Customer cannot be changed.</p>

            <label>Car:</label>
             <select disabled>
                 <option value="">-- Select Car --</option>
                 <?php foreach ($cars as $car): ?>
                     <option value="<?php echo $car['car_id']; ?>" <?php echo ($rental['car_id'] == $car['car_id']) ? 'selected' : ''; ?>>
                          <?php echo htmlspecialchars($car['brand'] . ' ' . $car['model'] . ' (' . $car['license_plate'] . ')'); ?>
                     </option>
                 <?php endforeach; ?>
             </select>
             <p class="info">Car cannot be changed.</p>
        </div>

        <div class="form-section">
             <label for="rental_start">Rental Start:</label>
             <input type="datetime-local" id="rental_start" name="rental_start" required value="<?php echo htmlspecialchars(date('Y-m-d\TH:i', strtotime($rental['rental_start']))); ?>">

             <label for="rental_end">Rental End:</label>
             <input type="datetime-local" id="rental_end" name="rental_end" required value="<?php echo htmlspecialchars(date('Y-m-d\TH:i', strtotime($rental['rental_end']))); ?>">
        </div>

        <div class="form-section">
             <label for="pickup_location_id">Pickup Location:</label>
             <select id="pickup_location_id" name="pickup_location_id" required>
                 <option value="">-- Select Location --</option>
                  <?php foreach ($locations as $loc): ?>
                     <option value="<?php echo $loc['location_id']; ?>" <?php echo ($rental['pickup_location_id'] == $loc['location_id']) ? 'selected' : ''; ?>>
                         <?php echo htmlspecialchars($loc['location_name']); ?>
                     </option>
                 <?php endforeach; ?>
             </select>

             <label for="dropoff_location_id">Dropoff Location:</label>
             <select id="dropoff_location_id" name="dropoff_location_id" required>
                 <option value="">-- Select Location --</option>
                  <?php foreach ($locations as $loc): ?>
                     <option value="<?php echo $loc['location_id']; ?>" <?php echo ($rental['dropoff_location_id'] == $loc['location_id']) ? 'selected' : ''; ?>>
                         <?php echo htmlspecialchars($loc['location_name']); ?>
                     </option>
                 <?php endforeach; ?>
             </select>
         </div>

        <div class="form-section">
            <label for="employee_id">Handled By:</label>
            <select id="employee_id" name="employee_id" required>
                <option value="">-- Select Employee --</option>
                <?php foreach ($employees as $emp): ?>
                    <option value="<?php echo $emp['employee_id']; ?>" <?php echo ($rental['employee_id'] == $emp['employee_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($emp['last_name'] . ', ' . $emp['first_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-section">
            <label for="total_cost">Total Cost ($):</label>
            <input type="number" id="total_cost" name="total_cost" step="0.01" min="0" required value="<?php echo htmlspecialchars($rental['total_cost']); ?>">

            <label for="insurance_included">
                <input type="checkbox" id="insurance_included" name="insurance_included" value="1" <?php echo ($rental['insurance_included'] == 1) ? 'checked' : ''; ?>>
                Insurance Included
            </label>
         </div>

         <div class="form-section">
             <label for="status">Status:</label>
             <select id="status" name="status" required>
                  <option value="active" <?php echo ($rental['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                  <option value="completed" <?php echo ($rental['status'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                  <option value="cancelled" <?php echo ($rental['status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
             </select>
         </div>

        <button type="submit">Update Rental Agreement</button>
    </form>
    <?php else: ?>
        <p>Could not load rental agreement data.</p>
    <?php endif; ?>

    <p><a href="rentals.php">Back to Rental List</a></p>
</body>
</html>