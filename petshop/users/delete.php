<?php
// users/delete.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

require_once '../config/database.php';

 $id = $_GET['id'] ?? null;

if (!$id) {
    $_SESSION['error'] = "Invalid user ID.";
    header("Location: index.php");
    exit();
}

// Prevent self-deletion
if ($id == $_SESSION['user_id']) {
    $_SESSION['error'] = "You cannot delete your own account!";
    header("Location: index.php");
    exit();
}

try {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
    $stmt->execute(['id' => $id]);

    $_SESSION['success'] = "User deleted successfully.";
} catch (PDOException $e) {
    $_SESSION['error'] = "Failed to delete user: " . $e->getMessage();
}

header("Location: index.php");
exit();
?>