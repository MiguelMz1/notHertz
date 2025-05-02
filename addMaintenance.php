<?php
include_once "dbconn.php";
$error_msg = '';
$success_msg = '';

$cars = [];
$sql_cars = "SELECT car_id, brand, model, license_plate FROM cars ORDER BY brand, model, license_plate";
$result_cars = $conn->query($sql_cars);
if ($result_cars) {
    while ($row = $result_cars->fetch_assoc()) {
        $cars[] = $row;
    }
}

$car_id = $_POST['car_id'] ?? '';
$service_date = $_POST['service_date'] ?? date('Y-m-d\TH:i');
$details = $_POST['details'] ?? '';
$cost = $_POST['cost'] ?? '';
$performed_by = $_POST['performed_by'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $car_id_int = filter_var($car_id, FILTER_VALIDATE_INT);
    $cost_float = filter_var($cost, FILTER_VALIDATE_FLOAT);

    if (!$car_id_int || empty($details) || ($cost_float === false || $cost_float < 0) || empty($performed_by) || empty($service_date)) {
        $error_msg = "All fields are required and must be valid.";
    } else {
        $service_dt = null;
        try {
            $service_dt = new DateTime($service_date);
        } catch(Exception $e){
            $error_msg = "Invalid service date format.";
        }

        if (!$error_msg && $service_dt) {
            $sql = "INSERT INTO maintenance (car_id, service_date, details, cost, performed_by)
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);

            if ($stmt) {
                $service_date_db = $service_dt->format('Y-m-d H:i:s');
                $stmt->bind_param("isids", $car_id_int, $service_date_db, $details, $cost_float, $performed_by);

                if ($stmt->execute()) {
                    $success_msg = "Maintenance record added successfully!";
                    header("Location: maintenance.php?message=" . urlencode($success_msg));
                    exit();
                } else {
                    $error_msg = "Error adding record.";
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
    <title>Add Maintenance Record</title>
     <style>
        body { font-family: sans-serif; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type=text], input[type=number], input[type=datetime-local], select, textarea { width: 300px; padding: 5px; margin-bottom: 10px; border: 1px solid #ccc; }
        textarea { height: 100px; }
        button { padding: 8px 15px; }
        .error { color: red; border: 1px solid red; padding: 10px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <h2>Add New Maintenance Record</h2>

    <?php if ($error_msg): ?>
        <div class="error"><?php echo $error_msg; ?></div>
    <?php endif; ?>

    <form method="POST" action="addMaintenance.php">

        <label for="car_id">Car:</label>
        <select id="car_id" name="car_id" required>
            <option value="">-- Select Car --</option>
            <?php foreach ($cars as $car_item): ?>
                <option value="<?php echo $car_item['car_id']; ?>" <?php echo ($car_id == $car_item['car_id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($car_item['brand'] . ' ' . $car_item['model'] . ' (' . $car_item['license_plate'] . ')'); ?>
                </option>
            <?php endforeach; ?>
             <?php if (empty($cars)) echo "<option value='' disabled>No cars found</option>"; ?>
        </select>

        <label for="service_date">Service Date:</label>
        <input type="datetime-local" id="service_date" name="service_date" required value="<?php echo htmlspecialchars($service_date); ?>">

        <label for="details">Service Details:</label>
        <textarea id="details" name="details" required><?php echo htmlspecialchars($details); ?></textarea>

        <label for="cost">Cost ($):</label>
        <input type="number" id="cost" name="cost" step="0.01" min="0" required value="<?php echo htmlspecialchars($cost); ?>">

        <label for="performed_by">Performed By:</label>
        <input type="text" id="performed_by" name="performed_by" required value="<?php echo htmlspecialchars($performed_by); ?>">

        <button type="submit">Add Record</button>
    </form>

    <p><a href="maintenance.php">Back to Maintenance Log</a></p>
</body>
</html>