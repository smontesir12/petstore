<?php
// debug_login.php
require_once 'config/database.php';

 $username_to_test = 'admin';
 $password_to_test = 'password123';

echo "<h3>Debugging Login...</h3>";

try {
    // 1. Fetch the user
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute(['username' => $username_to_test]);
    $user = $stmt->fetch();

    echo "<pre>";
    if ($user) {
        echo "✅ Step 1: User '" . $username_to_test . "' found in database.\n";
        echo "Database Hash: " . $user['password_hash'] . "\n";
        echo "Hash Length: " . strlen($user['password_hash']) . " (Should be 60)\n";
        echo "Is Active: " . $user['is_active'] . " (Should be 1)\n\n";

        // 2. Verify password
        echo "Step 2: Verifying password '" . $password_to_test . "' against hash...\n";
        if (password_verify($password_to_test, $user['password_hash'])) {
            echo "✅ Step 2: Password verification SUCCESSFUL!\n";
        } else {
            echo "❌ Step 2: Password verification FAILED. The hash does not match.\n";
        }
    } else {
        echo "❌ Step 1: User '" . $username_to_test . "' NOT found in database.";
    }
    echo "</pre>";
} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage();
}
?>