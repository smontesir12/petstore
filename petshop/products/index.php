<?php
// users/index.php
session_start();

// Protect page: redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

require_once '../config/database.php';

// Fetch all products
 $stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
 $products = $stmt->fetchAll();

// Capture flash messages
 $success_msg = $_SESSION['success'] ?? '';
 $error_msg = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products Management</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <h2>Product Management</h2>
            <div class="user-info">
                Welcome, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>! 
                <a href="../dashboard.php" class="btn btn-secondary btn-sm">Dashboard</a>
                <a href="../products/index.php" class="btn btn-secondary btn-sm">Products Management</a>
                <a href="../users/index.php" class="btn btn-secondary btn-sm">User Management</a>
            </div>
        </header>

        <main>
            <?php if ($success_msg): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success_msg); ?></div>
            <?php endif; ?>
            
            <?php if ($error_msg): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error_msg); ?></div>
            <?php endif; ?>

            <div style="margin-bottom: 20px;">
                <a href="create.php" class="btn btn-primary">+ Add New Products</a>
            </div>

            <table class="user-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Products</th>
                        <th>Description</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center;">No products found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?php echo $product['id']; ?></td>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><?php echo htmlspecialchars($product['description']); ?></td>
                                <td><?php echo htmlspecialchars($product['category']); ?></td>
                                <td><?php echo htmlspecialchars($product['price']); ?></td>
                                <td><?php echo htmlspecialchars($product['stock_quantity']); ?></td>
                                <td><?php echo date('M d, Y g:i A', strtotime($product['created_at'])); ?></td>
                                <td>
                                    <a href="edit.php?id=<?php echo $product['id']; ?>" class="btn btn-secondary btn-sm">Edit</a>
                                    <a href="delete.php?id=<?php echo $product['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>