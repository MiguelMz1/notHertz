<?php
include_once "dbconn.php";

if (!isset($conn) || $conn->connect_error) {
    die("Database connection failed.");
}

$message = $_GET['message'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
    <title>View Maintenance Records</title>
    <style>
        body { font-family: sans-serif; margin: 15px; }
        table { border-collapse: collapse; width: 100%; margin-top: 15px; margin-bottom: 20px; border: 1px solid #ccc; }
        th, td { border: 1px solid #ccc; padding: 5px; text-align: left; vertical-align: top; }
        th { background-color: #eee; }
        .message { padding: 10px; margin: 10px 0; border: 1px solid green; background-color: #e6ffe6; }
        .error { border: 1px solid red; background-color: #ffe6e6; }
        .add-link { margin-bottom: 15px; display: inline-block; padding: 5px 10px; background-color: #eee; border: 1px solid #ccc; color: black; text-decoration: none; }
        a { margin-right: 10px; text-decoration: none; }
        a.delete-link { color: red; }
        hr { margin: 20px 0; border: 0; border-top: 1px solid #ccc; }
    </style>
</head>
<body>
    <h2>Car Maintenance Information</h2>

    <?php if ($message): ?>
        <p class="message <?php echo (strpos(strtolower(urldecode($message)), 'error') !== false) ? 'error' : ''; ?>">
            <?php echo htmlspecialchars(urldecode($message)); ?>
        </p>
    <?php endif; ?>

    <a href="addMaintenance.php" class="add-link">Add New Maintenance Record</a>

    <h3>Car Service Summary</h3>
    <div style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th>Car ID</th><th>Car</th><th>License Plate</th><th>Status</th><th>Mileage</th><th>Last Service</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql_summary = "SELECT car_id, brand, model, license_plate, current_car_status, mileage, last_service_date
                                FROM CarMaintenanceSummary
                                ORDER BY (last_service_date IS NULL) DESC, last_service_date ASC, brand, model";
                $result_summary = $conn->query($sql_summary);

                if ($result_summary === false) {
                     echo "<tr><td colspan='6' style='color: red;'>Error fetching summary. Check view 'CarMaintenanceSummary'.</td></tr>";
                } elseif ($result_summary->num_rows > 0) {
                    while ($row_summary = $result_summary->fetch_assoc()) {
                        $last_service = 'Never';
                        if ($row_summary['last_service_date']) {
                            try { $last_service = (new DateTime($row_summary['last_service_date']))->format('Y-m-d'); } catch (Exception $e) { $last_service = 'Invalid Date'; }
                        }
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row_summary['car_id'] ?? '') . "</td>";
                        echo "<td>" . htmlspecialchars(($row_summary['brand'] ?? '') . ' ' . ($row_summary['model'] ?? '')) . "</td>";
                        echo "<td>" . htmlspecialchars($row_summary['license_plate'] ?? '') . "</td>";
                        echo "<td>" . htmlspecialchars($row_summary['current_car_status'] ?? '') . "</td>";
                        echo "<td>" . htmlspecialchars(isset($row_summary['mileage']) ? number_format($row_summary['mileage']) : '') . "</td>";
                        echo "<td>" . htmlspecialchars($last_service) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No cars found.</td></tr>";
                }
                 if ($result_summary && !($result_summary === false)) { $result_summary->free(); }
                ?>
            </tbody>
        </table>
    </div>
    <hr>

    <h3>Detailed Maintenance Log</h3>
    <div style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th>Maint. ID</th><th>Car (ID, Plate)</th><th>Service Date</th><th>Details</th><th>Cost ($)</th><th>Performed By</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (!$conn || $conn->connect_errno) {
                    echo "<tr><td colspan='7' style='color: red;'>Database Connection lost before loading detailed log.</td></tr>";
                } else {
                    $sql_log = "SELECT m.*, c.car_id, c.brand, c.model, c.license_plate
                                FROM maintenance m JOIN cars c ON m.car_id = c.car_id
                                ORDER BY m.service_date DESC, m.maintenance_id DESC";
                    $result_log = $conn->query($sql_log);

                     if ($result_log === false) {
                          echo "<tr><td colspan='7' style='color: red;'>Error fetching maintenance log.</td></tr>";
                    } elseif ($result_log->num_rows > 0) {
                        while ($row_log = $result_log->fetch_assoc()) {
                            $service_dt_str = 'Invalid Date';
                            try { $service_dt_str = isset($row_log['service_date']) ? (new DateTime($row_log['service_date']))->format('Y-m-d H:i') : ''; } catch (Exception $e) {}

                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row_log['maintenance_id'] ?? '') . "</td>";
                            echo "<td>" . htmlspecialchars(($row_log['brand'] ?? '') . ' ' . ($row_log['model'] ?? '')) .
                                 " (ID: " . htmlspecialchars($row_log['car_id'] ?? '') . ", " . htmlspecialchars($row_log['license_plate'] ?? '') . ")</td>";
                            echo "<td>" . htmlspecialchars($service_dt_str) . "</td>";
                            echo "<td>" . nl2br(htmlspecialchars($row_log['details'] ?? '')) . "</td>";
                            echo "<td style='text-align:right;'>" . htmlspecialchars(isset($row_log['cost']) ? number_format($row_log['cost'], 2) : '') . "</td>";
                            echo "<td>" . htmlspecialchars($row_log['performed_by'] ?? '') . "</td>";
                            echo "<td>";
                            echo "<a href='editMaintenance.php?maintenance_id=" . ($row_log['maintenance_id'] ?? '') . "'>Edit</a>";
                            echo "<a href='deleteMaintenance.php?maintenance_id=" . ($row_log['maintenance_id'] ?? '') . "' onclick='return confirm(\"Are you sure?\");' class='delete-link'>Delete</a>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7'>No maintenance records found.</td></tr>";
                    }
                     if ($result_log && !($result_log === false)) { $result_log->free(); }
                }
                ?>
            </tbody>
        </table>
    </div>

    <p><a href="index.php">Back to Main Menu</a></p>

    <?php
         if(isset($conn) && $conn && !$conn->connect_errno) {
            $conn->close();
         }
    ?>
</body>
</html>