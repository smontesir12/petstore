<?php
// index.php
session_start();

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

 $error = '';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Trim whitespace from inputs to prevent accidental space errors
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Validate input (no empty fields)
    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        try {
            require_once 'config/database.php';
            
            // Prepared statement to prevent SQL injection
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch();

            // Verify user exists and password is correct
            if ($user && password_verify($password, $user['password_hash'])) {
                // Check if account is active
                if ($user['is_active'] == 1) {
                    // Session management: create session upon successful login
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    
                    // Redirect to dashboard after login
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $error = "Your account is inactive. Please contact the administrator.";
                }
            } else {
                // Show error for invalid credentials
                $error = "Invalid username or password.";
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
    <title>PetStore Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <h2>PetStore Management System</h2>
        <p>Please login to continue</p>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="index.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <!-- Added value attribute to preserve username on failed login -->
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username ?? ''); ?>" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
    </div>
</body>
</html>