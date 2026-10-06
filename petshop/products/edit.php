<?php
// products/edit.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

require_once '../config/database.php';

$error = '';

// Get product ID from URL
$id = $_GET['id'] ?? null;

if (!$id) {
    $_SESSION['error'] = "Invalid product ID.";
    header("Location: index.php");
    exit();
}

// Fetch product data
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
$stmt->execute(['id' => $id]);
$product = $stmt->fetch();

if (!$product) {
    $_SESSION['error'] = "Product not found.";
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $stock_quantity = trim($_POST['stock_quantity'] ?? '');

    if (empty($name) || $price === '' || $stock_quantity === '') {
        $error = "All fields are required.";
    } elseif (!is_numeric($price) || $price < 0) {
        $error = "Price must be a valid positive number.";
    } elseif (!filter_var($stock_quantity, FILTER_VALIDATE_INT) && $stock_quantity !== '0') {
        // Simple int validation that allows 0
        $error = "Stock quantity must be a valid integer.";
    } else {
        try {
            // Check if product name already exists for OTHER products
            $check = $pdo->prepare("SELECT id FROM products WHERE name = :name AND id != :id");
            $check->execute(['name' => $name, 'id' => $id]);
            
            if ($check->fetch()) {
                $error = "Product name already taken by another item.";
            } else {
                // Update logic
                $update = $pdo->prepare("UPDATE products SET name = :name, price = :price, stock_quantity = :stock_quantity WHERE id = :id");
                $update->execute([
                    'name' => $name, 
                    'price' => $price, 
                    'stock_quantity' => $stock_quantity, 
                    'id' => $id
                ]);

                $_SESSION['success'] = "Product updated successfully!";
                header("Location: index.php");
                exit();
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
} else {
    // Pre-populate form on GET request
    $name = $product['name'];
    $price = $product['price'];
    $stock_quantity = $product['stock_quantity'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <h2>Edit Product</h2>
            <div class="user-info">
                <a href="index.php" class="btn btn-secondary btn-sm">Back to Products</a>
            </div>
        </header>

        <main style="max-width: 600px; margin: 0 auto;">
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form action="edit.php?id=<?php echo $id; ?>" method="POST">
                <div class="form-group">
                    <label for="name">Product Name</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                </div>
                <div class="form-group">
                    <label for="price">Price ($)</label>
                    <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo htmlspecialchars($price); ?>" required>
                </div>
                <div class="form-group">
                    <label for="stock_quantity">Stock Quantity</label>
                    <input type="number" id="stock_quantity" name="stock_quantity" min="0" value="<?php echo htmlspecialchars($stock_quantity); ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">Update Product</button>
            </form>
        </main>
    </div>
</body>
</html>
