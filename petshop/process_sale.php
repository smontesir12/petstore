<?php
// process_sale.php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

require_once 'config/database.php';

// Decode JSON Payload
$input = json_decode(file_get_contents('php://input'), true);
$cart = $input['cart'] ?? [];

if (empty($cart)) {
    echo json_encode(['success' => false, 'message' => 'Cart is empty.']);
    exit();
}

try {
    // Start Transaction
    $pdo->beginTransaction();

    foreach ($cart as $item) {
        $product_id = intval($item['id']);
        $qty = intval($item['qty']);

        // Check stock availability
        $stmt = $pdo->prepare("SELECT stock_quantity FROM products WHERE id = ? FOR UPDATE");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();

        if (!$product || $product['stock_quantity'] < $qty) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => "Insufficient stock for product ID {$product_id}."]);
            exit();
        }

        // Deduct Inventory (Automatic Stock Reduction)
        $updateStmt = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
        $updateStmt->execute([$qty, $product_id]);
    }

    // Commit Transaction
    $pdo->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}