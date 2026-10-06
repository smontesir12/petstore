<?php
// users/edit.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

require_once '../config/database.php';

 $error = '';

// Get user ID from URL
 $id = $_GET['id'] ?? null;

if (!$id) {
    $_SESSION['error'] = "Invalid user ID.";
    header("Location: index.php");
    exit();
}

// Fetch user data
 $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
 $stmt->execute(['id' => $id]);
 $user = $stmt->fetch();

if (!$user) {
    $_SESSION['error'] = "User not found.";
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (empty($username) || empty($email)) {
        $error = "Username and Email are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        try {
            // Check if username/email exists for OTHER users
            $check = $pdo->prepare("SELECT id FROM users WHERE (username = :username OR email = :email) AND id != :id");
            $check->execute(['username' => $username, 'email' => $email, 'id' => $id]);
            
            if ($check->fetch()) {
                $error = "Username or Email already taken by another user.";
            } else {
                // Update logic
                if (!empty($password)) {
                    // Update with new password
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    $update = $pdo->prepare("UPDATE users SET username = :username, email = :email, password_hash = :password_hash, is_active = :is_active WHERE id = :id");
                    $update->execute([
                        'username' => $username, 'email' => $email, 'password_hash' => $password_hash, 'is_active' => $is_active, 'id' => $id
                    ]);
                } else {
                    // Update without changing password
                    $update = $pdo->prepare("UPDATE users SET username = :username, email = :email, is_active = :is_active WHERE id = :id");
                    $update->execute([
                        'username' => $username, 'email' => $email, 'is_active' => $is_active, 'id' => $id
                    ]);
                }

                $_SESSION['success'] = "User updated successfully!";
                header("Location: index.php");
                exit();
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
} else {
    // Pre-populate form on GET request
    $username = $user['username'];
    $email = $user['email'];
    $is_active = $user['is_active'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <h2>Edit User</h2>
            <div class="user-info">
                <a href="index.php" class="btn btn-secondary btn-sm">Back to Users</a>
            </div>
        </header>

        <main style="max-width: 600px; margin: 0 auto;">
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form action="edit.php?id=<?php echo $id; ?>" method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>
                <div class="form-group">
                    <label for="password">New Password (leave blank to keep current)</label>
                    <input type="password" id="password" name="password">
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_active" value="1" <?php echo ($is_active == 1) ? 'checked' : ''; ?>>
                        Account is Active
                    </label>
                </div>
                <button type="submit" class="btn btn-primary">Update User</button>
            </form>
        </main>
    </div>
</body>
</html>