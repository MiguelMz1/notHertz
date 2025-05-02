<?php
include_once "dbconn.php";

$categories = [];
$result_cat = $conn->query("SELECT category_name FROM car_categories ORDER BY category_name");
if ($result_cat) {
    while ($row = $result_cat->fetch_assoc()) {
        $categories[] = $row['category_name'];
    }
    $result_cat->free();
}

$selected_category = trim($_GET['category_name'] ?? '');
$cars_found = [];
$error_msg = '';

if (!empty($selected_category)) {
    $sql = "CALL FindCarsByCategory(?)"; //function 1!
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s", $selected_category);

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $cars_found[] = $row;
                }
                $result->free();
            } else {
                $error_msg = "Error";
            }
        } else {
            $error_msg = "Error";
        }
        $stmt->close();
    } else {
        $error_msg = "Error";
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Cars By Category</title>
     <style>
        body { font-family: sans-serif; margin: 15px; }
        table { border-collapse: collapse; width: 100%; margin-top: 15px; border: 1px solid #ccc; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #eee; }
        label { font-weight: bold; margin-right: 5px; }
        select, button { padding: 8px; margin-right: 10px; }
        .filter-form { margin-bottom: 20px; padding: 15px; background-color: #f9f9f9; border: 1px solid #ddd; }
        .error { color: red; margin-top: 10px; }
    </style>
</head>
<body>
    <h2>Cars By Category (Function)</h2>

    <div class="filter-form">
        <form method="GET" action="carsByCategory.php">
            <label for="category_name">Select Category:</label>
            <select id="category_name" name="category_name" required>
                <option value="">-- Choose Category --</option>
                <?php foreach ($categories as $cat_name): ?>
                    <option value="<?php echo htmlspecialchars($cat_name); ?>" <?php echo ($selected_category == $cat_name) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat_name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">View Cars</button>
        </form>
    </div>

    <?php if ($error_msg): ?>
        <p class="error"><?php echo $error_msg; ?></p>
    <?php endif; ?>

    <?php if (!empty($selected_category) && empty($error_msg)): ?>
        <h3>Cars in Category: <?php echo htmlspecialchars($selected_category); ?></h3>
        <?php if (!empty($cars_found)): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Brand</th>
                        <th>Model</th>
                        <th>Year</th>
                        <th>License Plate</th>
                        <th>Rate ($)</th>
                        <th>Status</th>
                        <th>Mileage</th>
                        <th>Fuel Type</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cars_found as $car): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($car['car_id']); ?></td>
                            <td><?php echo htmlspecialchars($car['brand']); ?></td>
                            <td><?php echo htmlspecialchars($car['model']); ?></td>
                            <td><?php echo htmlspecialchars($car['year']); ?></td>
                            <td><?php echo htmlspecialchars($car['license_plate']); ?></td>
                            <td><?php echo htmlspecialchars(number_format($car['rental_rate'], 2)); ?></td>
                            <td><?php echo htmlspecialchars($car['status']); ?></td>
                            <td><?php echo htmlspecialchars(number_format($car['mileage'])); ?></td>
                            <td><?php echo htmlspecialchars($car['fuel_type']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No cars found in "<?php echo htmlspecialchars($selected_category); ?>".</p>
        <?php endif; ?>
    <?php elseif (empty($selected_category) && empty($error_msg)): ?>
        <p>Please select a category.</p>
    <?php endif; ?>

    <p><a href="index.php">Back to Main Menu</a></p>

</body>
</html>