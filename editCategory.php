<?php
include_once "dbconn.php";

if (!isset($conn) || $conn->connect_error) {
    die("Database connection error.");
}

$category = null;
$category_id = filter_input(INPUT_GET, 'category_id', FILTER_VALIDATE_INT);

if (!$category_id) {
    die("Invalid Category ID for initial load.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $form_category_id = $_POST['category_id'];
    $category_name = $_POST["category_name"];
    $description = $_POST["description"];

    $sql = "UPDATE car_categories SET category_name = ?, description = ? WHERE category_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $category_name, $description, $form_category_id);
    $stmt->execute(); 
    $stmt->close();
    $conn->close();

    $message = "Update attempt finished.";
    header("Location: categories.php?message=" . urlencode($message));
    exit();

} else {
    $stmt = $conn->prepare("SELECT * FROM car_categories WHERE category_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $category_id);
         if ($stmt->execute()) {
             $result = $stmt->get_result();
             $category = $result ? $result->fetch_assoc() : null;
             if (!$category) { die("Category not found."); }
         } else { die("Error fetching category data.");}
        $stmt->close();
    } else { die("Error preparing fetch."); }
    $conn->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Car Category</title>
    <style>
        body { font-family: sans-serif; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type=text], textarea { width: 300px; padding: 5px; margin-bottom: 10px; border: 1px solid #ccc;}
        textarea { height: 100px; }
        button { padding: 8px 15px; }
    </style>
</head>
<body>
    <h2>Edit Car Category</h2>

    <?php if ($category): ?>
    <form method="POST" action="editCategory.php?category_id=<?php echo htmlspecialchars($category['category_id']); ?>">
         <input type="hidden" name="category_id" value="<?php echo htmlspecialchars($category['category_id']); ?>">
        <label for="category_name">Category Name:</label>
        <input type="text" id="category_name" name="category_name" required value="<?php echo htmlspecialchars($category['category_name']); ?>">
        <label for="description">Description:</label>
        <textarea id="description" name="description" required><?php echo htmlspecialchars($category['description']); ?></textarea>
        <button type="submit">Update Category</button>
    </form>
    <?php else: ?>
        <p>Could not load category data.</p>
    <?php endif; ?>
    <p><a href="categories.php">Back to Category List</a></p>
</body>
</html>