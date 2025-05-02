<?php
include_once "dbconn.php";
$message = $_GET['message'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Categories</title>
    <style>
        body { font-family: sans-serif; margin: 15px; }
        table { border-collapse: collapse; width: 100%; margin-top: 15px; border: 1px solid #ccc; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #eee; }
        a { margin-right: 10px; text-decoration: none; }
        a.delete-link { color: red; }
        .message { padding: 10px; margin-bottom: 15px; border: 1px solid green; background-color: #e6ffe6; }
        .error { border: 1px solid red; background-color: #ffe6e6; }
        .add-link { display: inline-block; margin-bottom: 15px; padding: 8px 12px; background-color: #eee; border: 1px solid #ccc; color: black; }
    </style>
</head>
<body>
    <h2>Manage Categories</h2>

    <?php if ($message): ?>
        <p class="message <?php echo (strpos(strtolower(urldecode($message)), 'error') !== false) ? 'error' : ''; ?>">
            <?php echo htmlspecialchars(urldecode($message)); ?>
        </p>
    <?php endif; ?>

    <a href="addCategory.php" class="add-link">Add New Category</a>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Category Name</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT category_id, category_name, description FROM car_categories ORDER BY category_name";
            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['category_id']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['category_name']) . "</td>";
                    echo "<td>" . nl2br(htmlspecialchars($row['description'])) . "</td>";
                    echo "<td>";
                    echo "<a href='editCategory.php?category_id=" . $row['category_id'] . "'>Edit</a>";
                    echo "<a href='deleteCategory.php?category_id=" . $row['category_id'] . "' onclick='return confirm(\"Are you sure?\");' class='delete-link'>Delete</a>";
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='4'>No car categories found.</td></tr>";
            }
            $conn->close();
            ?>
        </tbody>
    </table>
     <p><a href="index.php">Back to Main Menu</a></p>
</body>
</html>