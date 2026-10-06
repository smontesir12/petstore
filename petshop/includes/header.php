<?php
// includes/header.php
// Shared layout: top bar + sidebar. Opens <main>; includes/footer.php closes it.
//
// Each page must set BEFORE including this file:
//   $base        - path from the current page back to the project root ('' or '../')
//   $page_title  - <title> text
//   $active      - 'dashboard' | 'products' | 'users'  (highlights the sidebar link)
$base       = $base ?? '';
$page_title = $page_title ?? 'PetStore Dashboard';
$active     = $active ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="stylesheet" href="<?php echo $base; ?>assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Top bar (same on every page) -->
        <header class="dashboard-header">
            <h2><a href="<?php echo $base; ?>dashboard.php" class="brand">PetStore Dashboard</a></h2>
            <nav class="user-info">
                <span>Welcome, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>!</span>
                <a href="<?php echo $base; ?>logout.php" class="btn btn-danger btn-sm">Logout</a>
            </nav>
        </header>

        <div class="dashboard-body">
            <!-- Sidebar (same on every page) -->
            <aside class="sidebar">
                <nav class="navigations">
                    <a href="<?php echo $base; ?>products/index.php"
                       class="btn btn-secondary btn-sm<?php echo $active === 'products' ? ' active' : ''; ?>">Products Management</a>
                    <a href="<?php echo $base; ?>users/index.php"
                       class="btn btn-secondary btn-sm<?php echo $active === 'users' ? ' active' : ''; ?>">User Management</a>
                </nav>
            </aside>

            <!-- Only this area changes per page -->
            <main>
