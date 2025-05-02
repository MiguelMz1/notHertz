<?php
include_once "dbconn.php";

if (!isset($conn) || $conn->connect_error) {
    die("Database connection error for report.");
}

$report_data = [
    'total_completed_rentals' => 'N/A',
    'total_revenue' => 'N/A',
    'active_rentals' => 'N/A',
    'available_cars' => 'N/A',
    'total_customers' => 'N/A',
    'total_cars' => 'N/A'
];
$query_errors = [];

$sql_completed = "SELECT COUNT(*) AS total FROM rental_agreements WHERE status = 'completed'";
$result = $conn->query($sql_completed);
if ($result) { $report_data['total_completed_rentals'] = $result->fetch_assoc()['total'] ?? 0; $result->free(); }
else { $query_errors[] = "Completed rentals query failed."; }

$sql_revenue = "SELECT SUM(total_cost) AS total FROM rental_agreements WHERE status = 'completed'";
$result = $conn->query($sql_revenue);
if ($result) { $report_data['total_revenue'] = $result->fetch_assoc()['total'] ?? 0.00; $result->free(); }
else { $query_errors[] = "Revenue query failed."; }

$sql_active = "SELECT COUNT(*) AS total FROM rental_agreements WHERE status = 'active'";
$result = $conn->query($sql_active);
if ($result) { $report_data['active_rentals'] = $result->fetch_assoc()['total'] ?? 0; $result->free(); }
else { $query_errors[] = "Active rentals query failed."; }

$sql_avail_cars = "SELECT COUNT(*) AS total FROM cars WHERE status = 'available'";
$result = $conn->query($sql_avail_cars);
if ($result) { $report_data['available_cars'] = $result->fetch_assoc()['total'] ?? 0; $result->free(); }
else { $query_errors[] = "Available cars query failed."; }

$sql_cust = "SELECT COUNT(*) AS total FROM customers";
$result = $conn->query($sql_cust);
if ($result) { $report_data['total_customers'] = $result->fetch_assoc()['total'] ?? 0; $result->free(); }
else { $query_errors[] = "Customers query failed."; }

$sql_cars_total = "SELECT COUNT(*) AS total FROM cars";
$result = $conn->query($sql_cars_total);
if ($result) { $report_data['total_cars'] = $result->fetch_assoc()['total'] ?? 0; $result->free(); }
else { $query_errors[] = "Total cars query failed."; }

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Rental Summary Report</title>
    <style>
        body { font-family: sans-serif; margin: 20px; }
        .report-container { border: 1px solid #ccc; padding: 20px; max-width: 500px; margin: auto; }
        h2 { text-align: center; margin-bottom: 20px; }
        .report-item { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px dotted #eee; }
        .report-item:last-child { border-bottom: none; }
        .report-label { color: #333; }
        .report-value { font-weight: bold; }
        .error { color: red; border: 1px solid red; padding: 10px; margin-bottom: 15px; }
        .back-link { display: block; text-align: center; margin-top: 20px; }
    </style>
</head>
<body>

    <div class="report-container">
        <h2>Rental Business Summary</h2>

        <?php if (!empty($query_errors)): ?>
            <div class="error">Could not generate full report. Errors occurred.</div>
        <?php endif; ?>

        <div class="report-item">
            <span class="report-label">Completed Rentals:</span>
            <span class="report-value"><?php echo is_numeric($report_data['total_completed_rentals']) ? number_format($report_data['total_completed_rentals']) : 'Error'; ?></span>
        </div>
        <div class="report-item">
            <span class="report-label">Total Revenue:</span>
            <span class="report-value">$<?php echo is_numeric($report_data['total_revenue']) ? number_format($report_data['total_revenue'], 2) : 'Error'; ?></span>
        </div>
        <div class="report-item">
            <span class="report-label">Active Rentals:</span>
            <span class="report-value"><?php echo is_numeric($report_data['active_rentals']) ? number_format($report_data['active_rentals']) : 'Error'; ?></span>
        </div>
        <div class="report-item">
            <span class="report-label">Available Cars:</span>
            <span class="report-value"><?php echo is_numeric($report_data['available_cars']) ? number_format($report_data['available_cars']) : 'Error'; ?></span>
        </div>
        <div class="report-item">
            <span class="report-label">Total Cars:</span>
            <span class="report-value"><?php echo is_numeric($report_data['total_cars']) ? number_format($report_data['total_cars']) : 'Error'; ?></span>
        </div>
        <div class="report-item">
            <span class="report-label">Total Customers:</span>
            <span class="report-value"><?php echo is_numeric($report_data['total_customers']) ? number_format($report_data['total_customers']) : 'Error'; ?></span>
        </div>
    </div>

    <p class="back-link"><a href="index.php">Back to Main Menu</a></p>

</body>
</html>