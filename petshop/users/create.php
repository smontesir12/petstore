<?php
// users/create.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

require_once '../config/database.php';

 $error = '';
 $username = '';
 $email = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validate input (no empty fields)
    if (empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        try {
            // Check if username or email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username OR email = :email");
            $stmt->execute(['username' => $username, 'email' => $email]);
            
            if ($stmt->fetch()) {
                $error = "Username or Email already exists.";
            } else {
                // Hash password and insert
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                
                $insert = $pdo->prepare("INSERT INTO users (username, email, password_hash, is_active) VALUES (:username, :email, :password_hash, 1)");
                $insert->execute([
                    'username' => $username,
                    'email' => $email,
                    'password_hash' => $password_hash
                ]);

                $_SESSION['success'] = "User added successfully!";
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
    <title>Add User</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <h2>Add New User</h2>
            <div class="user-info">
                <a href="index.php" class="btn btn-secondary btn-sm">Back to Users</a>
            </div>
        </header>

        <main style="max-width: 600px; margin: 0 auto;">
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form action="create.php" method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary">Create User</button>
            </form>
        </main>
    </div>
</body>
</html>