<?php
// products/create.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

require_once '../config/database.php';

$error = '';
$productName = '';
$description = '';
$category = '';
$price = '';
$stock_quantity = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $productName = trim($_POST['productName'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $stock_quantity = trim($_POST['stock_quantity'] ?? '');

    // Validate input (no empty fields)
    if (empty($productName) || empty($description) || empty($category) || $price === '' || $stock_quantity === '') {
        $error = "All fields are required.";
    } elseif (!is_numeric($price) || $price < 0) {
        $error = "Price must be a valid positive number.";
    } elseif (!is_numeric($stock_quantity) || $stock_quantity < 0) {
        $error = "Stock must be a valid positive integer.";
    } else {
        try {
            // Check if a product with the same name already exists (optional safety check)
            $stmt = $pdo->prepare("SELECT id FROM products WHERE name = :productName");
            $stmt->execute(['productName' => $productName]);
            
            if ($stmt->fetch()) {
                $error = "A product with this name already exists.";
            } else {
                // Insert product details
                $insert = $pdo->prepare("INSERT INTO products (name, description, category, price, stock_quantity) VALUES (:name, :description, :category, :price, :stock_quantity)");
                $insert->execute([
                    'name' => $productName,
                    'description' => $description,
                    'category' => $category,
                    'price' => $price,
                    'stock_quantity' => (int)$stock_quantity
                ]);

                $_SESSION['success'] = "Product added successfully!";
                header("Location: index.php");
                exit();
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <h2>Add New Product</h2>
            <div class="user-info">
                <a href="/petshop/products/index.php" class="btn btn-secondary btn-sm">Back to Products</a>
            </div>
        </header>

        <main style="max-width: 600px; margin: 0 auto;">
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form action="create.php" method="POST">
                <div class="form-group">
                    <label for="productName">Product Name</label>
                    <input type="text" id="productName" name="productName" value="<?php echo htmlspecialchars($productName); ?>" required>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4" style="width: 100%;" required><?php echo htmlspecialchars($description); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="category">Category</label>
                    <input type="text" id="category" name="category" value="<?php echo htmlspecialchars($category); ?>" required>
                </div>
                <div class="form-group">
                    <label for="price">Price</label>
                    <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo htmlspecialchars($price); ?>" required>
                </div>
                <div class="form-group">
                    <label for="stock_quantity">Stock</label>
                    <input type="number" id="stock_quantity" name="stock_quantity" min="0" value="<?php echo htmlspecialchars($stock_quantity); ?>" required>
                </div>

                <button type="submit" class="btn btn-primary">Create Product</button>
            </form>
        </main>
    </div>
</body>
</html>
