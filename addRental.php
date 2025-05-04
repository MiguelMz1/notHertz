<?php
include_once "dbconn.php";

if (!isset($conn) || $conn->connect_error) {
    die("Database connection error.");
}

$error_message = '';
$success_message = '';

$customers = []; $cars_available = []; $employees = []; $locations = [];
$result_cust = $conn->query("SELECT customer_id, first_name, last_name FROM customers ORDER BY last_name, first_name");
if ($result_cust) { while ($row = $result_cust->fetch_assoc()) { $customers[] = $row; } $result_cust->free(); }

$result_cars = $conn->query("SELECT car_id, brand, model, license_plate, rental_rate FROM AvailableCarsDetails ORDER BY brand, model");
if ($result_cars) { while ($row = $result_cars->fetch_assoc()) { $cars_available[] = $row; } $result_cars->free(); }

$result_emp = $conn->query("SELECT employee_id, first_name, last_name FROM employees ORDER BY last_name, first_name");
if ($result_emp) { while ($row = $result_emp->fetch_assoc()) { $employees[] = $row; } $result_emp->free(); }

$result_loc = $conn->query("SELECT location_id, location_name FROM locations ORDER BY location_name");
if ($result_loc) { while ($row = $result_loc->fetch_assoc()) { $locations[] = $row; } $result_loc->free(); }

$customer_id = $_POST['customer_id'] ?? '';
$car_id = $_POST['car_id'] ?? '';
$employee_id = $_POST['employee_id'] ?? '';
$pickup_location_id = $_POST['pickup_location_id'] ?? '';
$dropoff_location_id = $_POST['dropoff_location_id'] ?? '';
$rental_start = $_POST['rental_start'] ?? '';
$rental_end = $_POST['rental_end'] ?? '';
$insurance_included = isset($_POST['insurance_included']) ? 1 : 0;


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $customer_id_int = filter_var($customer_id, FILTER_VALIDATE_INT);
    $car_id_int = filter_var($car_id, FILTER_VALIDATE_INT);
    $employee_id_int = filter_var($employee_id, FILTER_VALIDATE_INT);
    $pickup_location_id_int = filter_var($pickup_location_id, FILTER_VALIDATE_INT);
    $dropoff_location_id_int = filter_var($dropoff_location_id, FILTER_VALIDATE_INT);

    if (!$customer_id_int || !$car_id_int || !$employee_id_int || !$pickup_location_id_int || !$dropoff_location_id_int || empty($rental_start) || empty($rental_end)) {
        $error_message = "All fields must be selected/filled correctly.";
        goto display_form; 
    }

    $start_dt = null; $end_dt = null; $start_formatted = null; $end_formatted = null;
    try {
        $start_dt = new DateTime($rental_start);
        $start_formatted = $start_dt->format('Y-m-d H:i:s');
    } catch (Exception $e) {
        $error_message = "Invalid start date format.";
        goto display_form;
    }
    try {
        $end_dt = new DateTime($rental_end);
        $end_formatted = $end_dt->format('Y-m-d H:i:s');
    } catch (Exception $e) {
        $error_message = "Invalid end date format.";
        goto display_form;
    }
    if ($end_dt <= $start_dt) {
        $error_message = "End date must be after start date.";
        goto display_form;
    }

    $total_cost_calculated = 0.00;
    $sql_cost = "SELECT CalculateRentalCost(?, ?, ?) AS calculated_cost";
    $stmt_cost = $conn->prepare($sql_cost);
    if (!$stmt_cost) {
        $error_message = "Error";
        goto display_form;
    }
    $stmt_cost->bind_param("iss", $car_id_int, $start_formatted, $end_formatted);
    if (!$stmt_cost->execute()) {
        $error_message = "Error";
        $stmt_cost->close();
        goto display_form;
    }
    $result_cost = $stmt_cost->get_result();
    $row_cost = $result_cost ? $result_cost->fetch_assoc() : null;
    $total_cost_calculated = $row_cost['calculated_cost'] ?? 0.00; 
    if($result_cost) $result_cost->free();
    $stmt_cost->close();

    $sql_insert = "INSERT INTO rental_agreements
                   (customer_id, car_id, employee_id, pickup_location_id, dropoff_location_id,
                    rental_start, rental_end, total_cost, insurance_included, status)
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')";
    $stmt_insert = $conn->prepare($sql_insert);
    if (!$stmt_insert) {
         $error_message = "Error preparing insert statement.";
         goto display_form;
    }
    $stmt_insert->bind_param("iiiiissdi",
        $customer_id_int, $car_id_int, $employee_id_int, $pickup_location_id_int, $dropoff_location_id_int,
        $start_formatted, $end_formatted, $total_cost_calculated, $insurance_included
    );
    if (!$stmt_insert->execute()) {
        $error_message = "Error saving rental agreement.";
        $stmt_insert->close();
        goto display_form;
    }
    $stmt_insert->close();

    $success_message = "Rental created! Cost: $" . number_format($total_cost_calculated, 2);
    header("Location: rentals.php?message=" . urlencode($success_message));
    exit();

    display_form:
    $conn->close(); 

} 


 if ($_SERVER["REQUEST_METHOD"] != "POST" || !empty($error_message)) {
    if (!isset($conn) || !$conn || $conn->connect_errno) {
         @include_once "dbconn.php";
         if (!$conn || $conn->connect_error) {
              if (empty($error_message)) {
                  $error_message = "Critical Error: Database unavailable.";
              } else {
                   $error_message .= "<br/>Critical Error: Database unavailable.";
              }
         }
    }
 }

?>
<!DOCTYPE html>
<html>
<head>
    <title>Add New Rental Agreement</title>
     <style>
        body { font-family: sans-serif; }
        label { display: block; margin-top: 10px; font-weight: bold; }
        input[type=text], input[type=number], input[type=datetime-local], select { width: 350px; padding: 5px; margin-top: 2px; margin-bottom: 8px; border: 1px solid #ccc; }
        input[type=checkbox] { width: auto; margin-right: 5px;}
        button { margin-top: 15px; padding: 8px 15px; }
        .error { color: red; border: 1px solid red; padding: 10px; margin-bottom: 15px; }
        .form-section { margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #eee;}
        .info { font-style: italic; color: #555; font-size: 0.9em; margin-top: 5px;}
    </style>
</head>
<body>
    <h2>Add New Rental Agreement</h2>

    <?php
    if (!empty($error_message)) {
        echo '<div class="error">' . $error_message . '</div>';
    }
    ?>

    <form method="POST" action="addRental.php">

        <div class="form-section">
            <label for="customer_id">Customer:</label>
            <select id="customer_id" name="customer_id" required>
                <option value="">-- Select Customer --</option>
                <?php if (isset($customers)) : foreach ($customers as $cust): ?>
                    <option value="<?php echo $cust['customer_id']; ?>" <?php echo ($customer_id == $cust['customer_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cust['last_name'] . ', ' . $cust['first_name'] . ' (ID: ' . $cust['customer_id'] . ')'); ?>
                    </option>
                <?php endforeach; endif; ?>
            </select>
        </div>

        <div class="form-section">
            <label for="car_id">Available Car:</label>
            <select id="car_id" name="car_id" required>
                <option value="">-- Select Car --</option>
                <?php if (isset($cars_available)): foreach ($cars_available as $car): ?>
                    <option value="<?php echo $car['car_id']; ?>" <?php echo ($car_id == $car['car_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($car['brand'] . ' ' . $car['model'] . ' (' . $car['license_plate'] . ') - $' . number_format($car['rental_rate'], 2) . '/day'); ?>
                    </option>
                <?php endforeach; endif; ?>
                 <?php if (empty($cars_available)) echo "<option value='' disabled>No cars available</option>"; ?>
            </select>
        </div>

        <div class="form-section">
             <label for="rental_start">Rental Start:</label>
             <input type="datetime-local" id="rental_start" name="rental_start" required value="<?php echo htmlspecialchars($rental_start); ?>">

             <label for="rental_end">Rental End:</label>
             <input type="datetime-local" id="rental_end" name="rental_end" required value="<?php echo htmlspecialchars($rental_end); ?>">
        </div>

         <div class="form-section">
             <label for="pickup_location_id">Pickup Location:</label>
             <select id="pickup_location_id" name="pickup_location_id" required>
                 <option value="">-- Select Location --</option>
                 <?php if(isset($locations)): foreach ($locations as $loc): ?>
                     <option value="<?php echo $loc['location_id']; ?>" <?php echo ($pickup_location_id == $loc['location_id']) ? 'selected' : ''; ?>>
                         <?php echo htmlspecialchars($loc['location_name']); ?>
                     </option>
                 <?php endforeach; endif; ?>
             </select>

             <label for="dropoff_location_id">Dropoff Location:</label>
             <select id="dropoff_location_id" name="dropoff_location_id" required>
                 <option value="">-- Select Location --</option>
                 <?php if(isset($locations)): foreach ($locations as $loc): ?>
                     <option value="<?php echo $loc['location_id']; ?>" <?php echo ($dropoff_location_id == $loc['location_id']) ? 'selected' : ''; ?>>
                         <?php echo htmlspecialchars($loc['location_name']); ?>
                     </option>
                 <?php endforeach; endif; ?>
             </select>
         </div>

        <div class="form-section">
            <label for="employee_id">Handled By:</label>
            <select id="employee_id" name="employee_id" required>
                <option value="">-- Select Employee --</option>
                <?php if(isset($employees)): foreach ($employees as $emp): ?>
                    <option value="<?php echo $emp['employee_id']; ?>" <?php echo ($employee_id == $emp['employee_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($emp['last_name'] . ', ' . $emp['first_name']); ?>
                    </option>
                <?php endforeach; endif; ?>
            </select>
        </div>

         <div class="form-section">
            <p class="info">Total cost calculated automatically.</p>
            <label for="insurance_included">
                <input type="checkbox" id="insurance_included" name="insurance_included" value="1" <?php echo ($insurance_included == 1) ? 'checked' : ''; ?>>
                Insurance Included
            </label>
         </div>

        <button type="submit">Create Rental Agreement</button>
    </form>

    <p><a href="rentals.php">Back to Rental List</a></p>

    <?php
        if ($_SERVER["REQUEST_METHOD"] != "POST" && isset($conn) && $conn && !$conn->connect_errno) {
            $conn->close();
        }
    ?>
</body>
</html>