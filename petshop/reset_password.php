<?php
// reset_password.php

// Include the database connection
require_once 'config/database.php';

// Define the target user and the new password
 $target_username = 'admin';
 $new_password = 'niggers';

try {
    // 1. Hash the new password securely using PHP's default algorithm (Bcrypt)
    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);

    // 2. Prepare the UPDATE statement to prevent SQL injection
    $stmt = $pdo->prepare("UPDATE users SET password_hash = :password_hash WHERE username = :username");
    
    // 3. Execute the query with the bound parameters
    $stmt->execute([
        'password_hash' => $new_hash,
        'username' => $target_username
    ]);

    // 4. Check if the row was actually updated
    if ($stmt->rowCount() > 0) {
        echo "<div style='font-family: sans-serif; padding: 20px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 5px; width: 400px; margin: 50px auto; text-align: center;'>
                <h3>Success!</h3>
                <p>Password for user '<strong>" . htmlspecialchars($target_username) . "</strong>' has been updated.</p>
                <p>New password is: <strong>" . htmlspecialchars($new_password) . "</strong></p>
              </div>";
    } else {
        echo "<div style='font-family: sans-serif; padding: 20px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 5px; width: 400px; margin: 50px auto; text-align: center;'>
                <h3>No Changes Made</h3>
                <p>User '<strong>" . htmlspecialchars($target_username) . "</strong>' not found, or the password was already the same.</p>
              </div>";
    }
} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage();
}
?>