<?php
include_once "dbconn.php";

if (!isset($conn) || $conn->connect_error) {
    die("Database connection error.");
}

$maint = null;
$maintenance_id = filter_input(INPUT_GET, 'maintenance_id', FILTER_VALIDATE_INT);

if (!$maintenance_id) {
    die("Invalid Maintenance ID");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $form_maint_id = $_POST['maintenance_id'];
    $car_id = $_POST['car_id']; 
    $service_date_input = $_POST['service_date']; 
    $details = $_POST['details'];
    $cost = $_POST['cost']; 
    $performed_by = $_POST['performed_by'];

    $service_date_db = date('Y-m-d H:i:s', strtotime($service_date_input));

    $sql = "UPDATE maintenance SET car_id = ?, service_date = ?, details = ?, cost = ?, performed_by = ?
            WHERE maintenance_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issdsi", $car_id, $service_date_db, $details, $cost, $performed_by, $form_maint_id);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    $message = "Updated!";
    header("Location: maintenance.php?message=" . urlencode($message));
    exit();

} else {
    $cars = [];
    $sql_cars = "SELECT car_id, brand, model, license_plate FROM cars ORDER BY brand, model, license_plate";
    $result_cars = $conn->query($sql_cars);
    if ($result_cars) { while ($row = $result_cars->fetch_assoc()) { $cars[] = $row; } }

    $sql_fetch = "SELECT * FROM maintenance WHERE maintenance_id = ?";
    $stmt_fetch = $conn->prepare($sql_fetch);
    if($stmt_fetch) {
        $stmt_fetch->bind_param("i", $maintenance_id);
        if ($stmt_fetch->execute()) {
            $result = $stmt_fetch->get_result();
            $maint = $result ? $result->fetch_assoc() : null;
            if (!$maint) { die("Maintenance record not found."); }
            $maint['service_date'] = date('Y-m-d\TH:i', strtotime($maint['service_date']));
        } else { die("Error fetching maintenance data."); }
        $stmt_fetch->close();
    } else { die("Error preparing fetch."); }
    $conn->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Maintenance Record</title>
    <style>
        body { font-family: sans-serif; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type=text], input[type=number], input[type=datetime-local], select, textarea { width: 300px; padding: 5px; margin-bottom: 10px; border: 1px solid #ccc;}
        textarea { height: 100px; }
        button { padding: 8px 15px; }
    </style>
</head>
<body>
    <h2>Edit Maintenance Record (ID: <?php echo htmlspecialchars($maintenance_id); ?>)</h2>

    <?php if ($maint): ?>
    <form method="POST" action="editMaintenance.php?maintenance_id=<?php echo htmlspecialchars($maint['maintenance_id']); ?>">
        <input type="hidden" name="maintenance_id" value="<?php echo htmlspecialchars($maint['maintenance_id']); ?>">
        <label for="car_id">Car:</label>
        <select id="car_id" name="car_id" required>
            <option value="">-- Select Car --</option>
             <?php foreach ($cars as $car): ?>
                <option value="<?php echo $car['car_id']; ?>" <?php echo ($maint['car_id'] == $car['car_id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($car['brand'] . ' ' . $car['model'] . ' (' . $car['license_plate'] . ')'); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <label for="service_date">Service Date:</label>
        <input type="datetime-local" id="service_date" name="service_date" required value="<?php echo htmlspecialchars($maint['service_date']); ?>">
        <label for="details">Service Details:</label>
        <textarea id="details" name="details" required><?php echo htmlspecialchars($maint['details']); ?></textarea>
        <label for="cost">Cost ($):</label>
        <input type="number" id="cost" name="cost" step="0.01" min="0" required value="<?php echo htmlspecialchars($maint['cost']); ?>">
        <label for="performed_by">Performed By:</label>
        <input type="text" id="performed_by" name="performed_by" required value="<?php echo htmlspecialchars($maint['performed_by']); ?>">
        <button type="submit">Update Record</button>
    </form>
    <?php else: ?>
        <p>Could not load maintenance data.</p>
    <?php endif; ?>
    <p><a href="maintenance.php">Back to Maintenance Log</a></p>
</body>
</html>