<?php
// users/index.php
session_start();

// Protect page: redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

require_once '../config/database.php';

// Fetch all users
 $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
 $users = $stmt->fetchAll();

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
    <title>User Management</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <h2>User Management</h2>
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
                <a href="create.php" class="btn btn-primary">+ Add New User</a>
            </div>

            <table class="user-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center;">No users found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <?php if ($user['is_active'] == 1): ?>
                                        <span class="status active">Active</span>
                                    <?php else: ?>
                                        <span class="status inactive">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M d, Y g:i A', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <a href="edit.php?id=<?php echo $user['id']; ?>" class="btn btn-secondary btn-sm">Edit</a>
                                    
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <a href="delete.php?id=<?php echo $user['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                                    <?php else: ?>
                                        <span class="btn btn-disabled btn-sm">Delete</span>
                                    <?php endif; ?>
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