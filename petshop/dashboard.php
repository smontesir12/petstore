<?php
// dashboard.php
session_start();

// Protect page: redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require_once 'config/database.php';

// Fetch Statistics & Data
try {
    // Total users
    $stmt_total = $pdo->query("SELECT COUNT(*) as total FROM users");
    $total_users = $stmt_total->fetch()['total'];

    // Active users
    $stmt_active = $pdo->query("SELECT COUNT(*) as active FROM users WHERE is_active = 1");
    $active_users = $stmt_active->fetch()['active'];

    // Inactive users
    $stmt_inactive = $pdo->query("SELECT COUNT(*) as inactive FROM users WHERE is_active = 0");
    $inactive_users = $stmt_inactive->fetch()['inactive'];

    // Category Distribution for Donut Chart
    $stmt_categories = $pdo->query("
        SELECT COALESCE(category, 'General') as category_name, SUM(stock_quantity) as total_stock 
        FROM products 
        GROUP BY COALESCE(category, 'General')
    ");
    $category_data = $stmt_categories->fetchAll(PDO::FETCH_ASSOC);

    // Top Sold Products (Fallback to stock quantity if no sales table yet)
    $stmt_top_sales = $pdo->query("
        SELECT name, price, stock_quantity 
        FROM products 
        ORDER BY price DESC 
        LIMIT 4
    ");
    $top_products = $stmt_top_sales->fetchAll();

    // Streamlined Product Table Data
    $stmt_all_products = $pdo->query("SELECT id, name, category, price, stock_quantity FROM products ORDER BY created_at DESC");
    $all_products = $stmt_all_products->fetchAll();

    // Available Products for POS Operations Tab
    $stmt_products = $pdo->query("SELECT * FROM products WHERE stock_quantity > 0 ORDER BY name ASC");
    $products = $stmt_products->fetchAll();

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

$username = htmlspecialchars($_SESSION['username']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - PetStore</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Chart.js Library for the Donut Chart -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="dashboard-container">
        <!-- Navigation Header -->
        <header class="dashboard-header">
            <h2>PetStore Dashboard</h2>
            <nav class="user-info">
                Welcome, <strong><?php echo $username; ?></strong>! 
                <a href="products/index.php" class="btn btn-secondary btn-sm">Products Management</a>
                <a href="users/index.php" class="btn btn-secondary btn-sm">User Management</a>
            </nav>
        </header>

        <div style="display:flex; gap: 20px;">
            <aside class="sidebar">
                <nav class="navigations">
                    <div class="nav-links">
                        <button type="button" class="nav-btn active" data-tab="dashboard-tab">
                            Dashboard
                        </button>
                        <button type="button" class="nav-btn" data-tab="operations-tab">
                            Operations
                        </button>
                    </div>
                    <div class="logout-container">
                        <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
                    </div>
                </nav>
            </aside>

            <main style="flex: 1;">
                <!-- TAB 1: DASHBOARD OVERVIEW -->
                <div id="dashboard-tab" class="tab-content active">
                    
                    <!-- Top Overview Stats -->
                    <div style="margin-bottom: 25px;">
                        <h3>Overview</h3>
                        <div class="stats-grid">
                            <div class="stat-card stat-total">
                                <h4>Total Users</h4>
                                <p class="stat-number"><?php echo $total_users; ?></p>
                            </div>
                            <div class="stat-card stat-active">
                                <h4>Active Users</h4>
                                <p class="stat-number"><?php echo $active_users; ?></p>
                            </div>
                            <div class="stat-card stat-inactive">
                                <h4>Inactive Users</h4>
                                <p class="stat-number"><?php echo $inactive_users; ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- 2-COLUMN SECTION: MOST SALES & DONUT CHART -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px;">
                        
                        <!-- Left Column: Most Sales Products -->
                        <div class="recent-users-section" style="margin-bottom: 0;">
                            <h3 style="margin-top: 0; margin-bottom: 15px;">Most Sold Products</h3>
                            <div style="display: flex; flex-direction: column; gap: 12px;">
                                <?php if (empty($top_products)): ?>
                                    <p style="color: #64748b; font-size: 0.9rem;">No sales data available.</p>
                                <?php else: ?>
                                    <?php foreach ($top_products as $index => $item): ?>
                                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 12px; background-color: #f8fafc; border-radius: 6px; border: 1px solid #e2e8f0;">
                                            <div>
                                                <div style="font-weight: 600; color: #1e293b;">
                                                    <?php echo ($index + 1) . '. ' . htmlspecialchars($item['name']); ?>
                                                </div>
                                                <small style="color: #64748b;">₱<?php echo number_format($item['price'], 2); ?></small>
                                            </div>
                                            <span class="status active" style="font-size: 0.8rem;">
                                                <?php echo $item['stock_quantity']; ?> Stock Left
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Right Column: Category Distribution Donut Chart -->
                        <div class="recent-users-section" style="margin-bottom: 0; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                            <h3 style="margin-top: 0; margin-bottom: 15px; width: 100%;">Category Distribution</h3>
                            <div style="width: 220px; height: 220px;">
                                <canvas id="categoryDonutChart"></canvas>
                            </div>
                        </div>

                    </div>

                    <!-- STREAMLINED PRODUCTS TABLE -->
                    <div class="recent-users-section">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                            <h3 style="margin: 0; border: none;">Product Inventory</h3>
                            <a href="products/index.php" class="btn btn-primary btn-sm" style="width: auto;">Manage Products</a>
                        </div>
                        <table class="user-table">
                            <thead>
                                <tr>
                                    <th>Product Name</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($all_products)): ?>
                                    <tr>
                                        <td colspan="5" style="text-align: center;">No products found in inventory.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($all_products as $prod): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($prod['name']); ?></strong></td>
                                            <td>
                                                <span class="status" style="background-color: #e2e8f0; color: #334155;">
                                                    <?php echo htmlspecialchars($prod['category'] ?? 'General'); ?>
                                                </span>
                                            </td>
                                            <td>₱<?php echo number_format($prod['price'], 2); ?></td>
                                            <td><?php echo $prod['stock_quantity']; ?></td>
                                            <td>
                                                <?php if ($prod['stock_quantity'] <= 0): ?>
                                                    <span class="status inactive">Out of Stock</span>
                                                <?php elseif ($prod['stock_quantity'] < 5): ?>
                                                    <span class="status inactive" style="background-color: #fef3c7; color: #92400e;">
                                                        Low Stock
                                                    </span>
                                                <?php else: ?>
                                                    <span class="status active">In Stock</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                </div>

                <!-- TAB 2: OPERATIONS CONTAINER (POS MODULE) -->
                <div id="operations-tab" class="tab-content">
                    <div class="pos-wrapper">
                        
                        <!-- Left Column: Products Showcase -->
                        <div class="pos-products-section">
                            <div class="pos-search-bar">
                                <input type="text" id="posSearch" placeholder="Search products by name..." onkeyup="filterProducts()">
                            </div>

                            <div class="product-grid" id="productGrid">
                                <?php if (empty($products)): ?>
                                    <p class="no-products">No available products in stock.</p>
                                <?php else: ?>
                                    <?php foreach ($products as $product): ?>
                                        <div class="product-card" 
                                            data-name="<?php echo strtolower(htmlspecialchars($product['name'])); ?>"
                                            data-id="<?php echo $product['id']; ?>"
                                            data-price="<?php echo $product['price']; ?>"
                                            data-stock="<?php echo $product['stock_quantity']; ?>">
                                            <div class="product-info">
                                                <h4 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h4>
                                                <p class="product-price">₱<?php echo number_format($product['price'], 2); ?></p>
                                                <span class="stock-badge <?php echo ($product['stock_quantity'] < 5) ? 'low-stock' : ''; ?>">
                                                    Stock: <strong id="stock-display-<?php echo $product['id']; ?>"><?php echo $product['stock_quantity']; ?></strong>
                                                </span>
                                            </div>
                                            <button type="button" class="btn-add-cart" onclick="addToCart(<?php echo $product['id']; ?>, '<?php echo addslashes(htmlspecialchars($product['name'])); ?>', <?php echo $product['price']; ?>, <?php echo$product['stock_quantity']; ?>)">
                                                + Add
                                            </button>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Right Column: Cart & Checkout -->
                        <div class="pos-cart-section">
                            <div class="cart-header">
                                <h3>Current Order</h3>
                                <button type="button" class="btn-clear" onclick="clearCart()">Clear All</button>
                            </div>

                            <div class="cart-items-container" id="cartItems">
                                <p class="empty-cart-msg">Cart is empty. Select products to begin.</p>
                            </div>

                            <div class="cart-summary">
                                <div class="summary-row">
                                    <span>Subtotal</span>
                                    <span id="cartSubtotal">₱0.00</span>
                                </div>
                                <div class="summary-row total-row">
                                    <span>Total Amount</span>
                                    <span id="cartTotal">₱0.00</span>
                                </div>

                                <div class="payment-section">
                                    <div class="form-group">
                                        <label for="cashReceived">Cash Received (₱)</label>
                                        <input type="number" id="cashReceived" step="0.01" placeholder="0.00" oninput="calculateChange()">
                                    </div>
                                    <div class="summary-row change-row">
                                        <span>Change</span>
                                        <span id="changeAmount">₱0.00</span>
                                    </div>
                                </div>

                                <button type="button" id="checkoutBtn" class="btn btn-checkout" onclick="processCheckout()" disabled>
                                    Complete Sale
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Scripts: Navigation, POS Logic, and Donut Chart -->
    <script>
        // Tab Navigation
        document.addEventListener('DOMContentLoaded', () => {
            const navButtons = document.querySelectorAll('.nav-btn');
            const tabContents = document.querySelectorAll('.tab-content');

            navButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const targetTab = button.getAttribute('data-tab');

                    navButtons.forEach(btn => btn.classList.remove('active'));
                    tabContents.forEach(tab => tab.classList.remove('active'));

                    button.classList.add('active');
                    document.getElementById(targetTab).classList.add('active');
                });
            });

            // Initialize Donut Chart
            initDonutChart();
        });

        // Donut Chart Initialization
        function initDonutChart() {
            const categories = <?php echo json_encode(array_column($category_data, 'category_name')); ?>;
            const stocks = <?php echo json_encode(array_column($category_data, 'total_stock')); ?>;

            const ctx = document.getElementById('categoryDonutChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: categories.length ? categories : ['No Data'],
                    datasets: [{
                        data: stocks.length ? stocks : [1],
                        backgroundColor: [
                            '#3b82f6',
                            '#10b981',
                            '#f59e0b',
                            '#ef4444',
                            '#8b5cf6',
                            '#64748b'
                        ],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                font: { size: 11 }
                            }
                        }
                    },
                    cutout: '65%'
                }
            });
        }

        // POS Cart Logic
        let cart = [];

        function addToCart(id, name, price, stock) {
            const existing = cart.find(item => item.id === id);
            if (existing) {
                if (existing.qty < stock) {
                    existing.qty += 1;
                } else {
                    alert("Maximum available stock reached!");
                }
            } else {
                cart.push({ id, name, price: parseFloat(price), qty: 1, maxStock: stock });
            }
            updateCartUI();
        }

        function updateQty(id, change) {
            const item = cart.find(i => i.id === id);
            if (!item) return;

            item.qty += change;
            if (item.qty <= 0) {
                cart = cart.filter(i => i.id !== id);
            } else if (item.qty > item.maxStock) {
                item.qty = item.maxStock;
                alert("Cannot exceed available stock!");
            }
            updateCartUI();
        }

        function clearCart() {
            cart = [];
            document.getElementById('cashReceived').value = '';
            updateCartUI();
        }

        function updateCartUI() {
            const cartContainer = document.getElementById('cartItems');
            let total = 0;

            if (cart.length === 0) {
                cartContainer.innerHTML = '<p class="empty-cart-msg">Cart is empty. Select products to begin.</p>';
                document.getElementById('cartSubtotal').textContent = '₱0.00';
                document.getElementById('cartTotal').textContent = '₱0.00';
                document.getElementById('checkoutBtn').disabled = true;
                calculateChange();
                return;
            }

            cartContainer.innerHTML = '';
            cart.forEach(item => {
                const itemTotal = item.price * item.qty;
                total += itemTotal;

                const row = document.createElement('div');
                row.className = 'cart-item';
                row.innerHTML = `
                    <div class="cart-item-details">
                        <h5>${item.name}</h5>
                        <small>₱${item.price.toFixed(2)} x ${item.qty}</small>
                    </div>
                    <div class="cart-controls">
                        <button class="qty-btn" onclick="updateQty(${item.id}, -1)">-</button>
                        <span>${item.qty}</span>
                        <button class="qty-btn" onclick="updateQty(${item.id}, 1)">+</button>
                    </div>
                `;
                cartContainer.appendChild(row);
            });

            document.getElementById('cartSubtotal').textContent = `₱${total.toFixed(2)}`;
            document.getElementById('cartTotal').textContent = `₱${total.toFixed(2)}`;
            calculateChange();
        }

        function calculateChange() {
            const total = cart.reduce((sum, i) => sum + (i.price * i.qty), 0);
            const cash = parseFloat(document.getElementById('cashReceived').value) || 0;
            const change = cash - total;

            const changeEl = document.getElementById('changeAmount');
            const checkoutBtn = document.getElementById('checkoutBtn');

            if (total > 0 && cash >= total) {
                changeEl.textContent = `₱${change.toFixed(2)}`;
                checkoutBtn.disabled = false;
            } else {
                changeEl.textContent = '₱0.00';
                checkoutBtn.disabled = true;
            }
        }

        function filterProducts() {
            const query = document.getElementById('posSearch').value.toLowerCase();
            const cards = document.querySelectorAll('.product-card');

            cards.forEach(card => {
                const name = card.getAttribute('data-name');
                card.style.display = name.includes(query) ? 'flex' : 'none';
            });
        }

        function processCheckout() {
            if (cart.length === 0) return;

            fetch('process_sale.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ cart: cart })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Sale completed successfully!');
                    cart.forEach(item => {
                        const stockEl = document.getElementById(`stock-display-${item.id}`);
                        if (stockEl) {
                            const newStock = parseInt(stockEl.textContent) - item.qty;
                            stockEl.textContent = newStock;
                        }
                    });
                    clearCart();
                } else {
                    alert('Error processing sale: ' + data.message);
                }
            })
            .catch(err => alert('Network or server error!'));
        }
    </script>
</body>
</html>