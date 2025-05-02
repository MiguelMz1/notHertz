<?php
include_once "dbconn.php";
$error_message = '';
$success_message = '';

$category_name = '';
$description = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category_name = trim($_POST["category_name"] ?? '');
    $description = trim($_POST["description"] ?? '');

    if (empty($category_name) || empty($description)) {
        $error_message = "Both fields are required.";
    } else {
        $sql = "INSERT INTO car_categories (category_name, description) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("ss", $category_name, $description);

            if ($stmt->execute()) {
                $success_message = "Category added successfully!";
                header("Location: categories.php?message=" . urlencode($success_message));
                exit();
            } else {
                 $error_message = "Error saving category";
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
    <title>Add New Car Category</title>
     <style>
        body { font-family: sans-serif; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type=text], textarea { width: 300px; padding: 5px; margin-bottom: 10px; border: 1px solid #ccc; }
        textarea { height: 80px; }
        button { padding: 8px 15px; }
        .error { color: red; border: 1px solid red; padding: 10px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <h2>Add New Car Category</h2>

    <?php if ($error_message): ?>
        <div class="error"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <form method="POST" action="addCategory.php">
        <label for="category_name">Category Name:</label>
        <input type="text" id="category_name" name="category_name" required value="<?php echo htmlspecialchars($category_name); ?>">

        <label for="description">Description:</label>
        <textarea id="description" name="description" required><?php echo htmlspecialchars($description); ?></textarea>

        <button type="submit">Add Category</button>
    </form>

    <p><a href="categories.php">Back to Category List</a></p>
</body>
</html>