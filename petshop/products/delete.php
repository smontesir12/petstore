<?php
// products/delete.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

require_once '../config/database.php';

 $id = $_GET['id'] ?? null;

if (!$id) {
    $_SESSION['error'] = "Invalid product ID.";
    header("Location: index.php");
    exit();
}

try {
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
    $stmt->execute(['id' => $id]);

    $_SESSION['success'] = "Product deleted successfully.";
} catch (PDOException $e) {
    $_SESSION['error'] = "Failed to delete product: " . $e->getMessage();
}

header("Location: index.php");
exit();
?>