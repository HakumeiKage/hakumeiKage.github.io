<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Check if user is admin
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: index.php');
    exit();
}

// Get stats for dashboard
$products_count = $conn->query("SELECT COUNT(*) FROM products")->fetch_row()[0] ?? 0;
$users_count = $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0] ?? 0;
$orders_count = $conn->query("SELECT COUNT(*) FROM orders")->fetch_row()[0] ?? 0;

// Calculate revenue
$revenue_result = $conn->query("SELECT SUM(total_amount) FROM orders");
$revenue = $revenue_result->fetch_row()[0] ?? 0;

// Get recent orders
$orders = $conn->query("SELECT * FROM orders ORDER BY order_date DESC LIMIT 5");

// Get popular products
$popular_products = $conn->query("
    SELECT p.id, p.title, p.image_path, COUNT(oi.id) as order_count
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    GROUP BY p.id
    ORDER BY order_count DESC
    LIMIT 4
");

// User registration stats
$user_stats = $conn->query("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        COUNT(*) as count
    FROM users
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY month
    ORDER BY month
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet"/>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --warning: #f72585;
            --dark: #212529;
            --light: #f8f9fa;
            --gray: #6c757d;
            --border: #dee2e6;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fb;
            color: #333;
            min-height: 100vh;
        }
        
        /* Navigation */
        nav {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            padding: 20px 40px;
            background: white;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 100;
        }
        
        .logo {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
        }
        
        .admin-menu {
            display: flex;
            gap: 25px;
        }
        
        .admin-menu a {
            color: var(--gray);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .admin-menu a:hover {
            color: var(--primary);
        }
        
        .admin-menu a.active {
            color: var(--primary);
        }
        
        .admin-menu a i {
            font-size: 18px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-info span {
            font-weight: 500;
        }
        
        .user-info .avatar {
            width: 40px;
            height: 40px;
            background: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        .logout-btn {
            background: transparent;
            border: none;
            color: var(--gray);
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .logout-btn:hover {
            color: var(--warning);
        }
        
        /* Dashboard Layout */
        .dashboard {
            padding: 120px 40px 40px;
            max-width: 1600px;
            margin: 0 auto;
        }
        
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .dashboard-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--dark);
        }
        
        .date-filter {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .date-filter select {
            padding: 8px 15px;
            border: 1px solid var(--border);
            border-radius: 6px;
            background: white;
        }
        
        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .stat-card .title {
            font-size: 16px;
            color: var(--gray);
            font-weight: 500;
        }
        
        .stat-card .icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        
        .stat-card.products .icon {
            background: rgba(67, 97, 238, 0.15);
            color: var(--primary);
        }
        
        .stat-card.users .icon {
            background: rgba(76, 201, 240, 0.15);
            color: var(--success);
        }
        
        .stat-card.orders .icon {
            background: rgba(247, 37, 133, 0.15);
            color: var(--warning);
        }
        
        .stat-card.revenue .icon {
            background: rgba(40, 167, 69, 0.15);
            color: #28a745;
        }
        
        .stat-card .value {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .stat-card .change {
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .change.positive {
            color: #28a745;
        }
        
        .change.negative {
            color: #dc3545;
        }
        
        /* Charts & Data Section */
        .data-section {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            margin-bottom: 40px;
        }
        
        @media (max-width: 1200px) {
            .data-section {
                grid-template-columns: 1fr;
            }
        }
        
        .chart-container {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        
        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .chart-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--dark);
        }
        
        .chart-actions {
            display: flex;
            gap: 10px;
        }
        
        .chart-actions button {
            background: transparent;
            border: 1px solid var(--border);
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .chart-actions button:hover {
            background: var(--light);
        }
        
        .chart-wrapper {
            height: 300px;
            position: relative;
        }
        
        /* Recent Orders */
        .recent-orders {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        
        .recent-orders .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .recent-orders .title {
            font-size: 20px;
            font-weight: 600;
            color: var(--dark);
        }
        
        .recent-orders .view-all {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }
        
        th {
            font-weight: 600;
            color: var(--gray);
            font-size: 14px;
            text-transform: uppercase;
        }
        
        tbody tr {
            transition: all 0.3s;
        }
        
        tbody tr:hover {
            background: rgba(67, 97, 238, 0.05);
        }
        
        .status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            display: inline-block;
        }
        
        .status-completed {
            background: rgba(40, 167, 69, 0.15);
            color: #28a745;
        }
        
        .status-pending {
            background: rgba(255, 193, 7, 0.15);
            color: #ffc107;
        }
        
        .status-processing {
            background: rgba(0, 123, 255, 0.15);
            color: #007bff;
        }
        
        .status-cancelled {
            background: rgba(220, 53, 69, 0.15);
            color: #dc3545;
        }
        
        .action-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }
        
        /* Popular Products */
        .popular-products {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .product-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
        }
        
        .product-image {
            height: 180px;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 1px solid var(--border);
        }
        
        .product-image img {
            max-width: 100%;
            max-height: 140px;
            object-fit: contain;
        }
        
        .product-info {
            padding: 20px;
        }
        
        .product-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--dark);
        }
        
        .product-meta {
            display: flex;
            justify-content: space-between;
        }
        
        .orders-count {
            background: var(--light);
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }
        
        /* Footer */
        .dashboard-footer {
            text-align: center;
            padding: 20px;
            color: var(--gray);
            font-size: 14px;
            border-top: 1px solid var(--border);
            margin-top: 40px;
        }
    </style>
</head>
<body>
    <nav>
        <a href="dashboard.php" class="logo">AdminPanel</a>
        
        <div class="admin-menu">
            <a href="dashboard.php" class="active"><i class="ri-dashboard-line"></i> Dashboard</a>
            <a href="product_management.php"><i class="ri-shopping-bag-line"></i> Products</a>
            <a href="#"><i class="ri-list-check"></i> Orders</a>
            <a href="#"><i class="ri-user-line"></i> Customers</a>
            <a href="#"><i class="ri-bar-chart-line"></i> Reports</a>
        </div>
        
        <div class="user-info">
            <div class="avatar"><?php echo strtoupper(substr($_SESSION['name'], 0, 1)); ?></div>
            <span><?php echo $_SESSION['name']; ?></span>
            <a href="logout.php" class="logout-btn"><i class="ri-logout-box-r-line"></i> Logout</a>
        </div>
    </nav>

    <div class="dashboard">
        <div class="dashboard-header">
            <h1 class="dashboard-title">Dashboard Overview</h1>
            <div class="date-filter">
                <select>
                    <option>Today</option>
                    <option>This Week</option>
                    <option selected>This Month</option>
                    <option>This Year</option>
                </select>
            </div>
        </div>
        
        <div class="stats-container">
            <div class="stat-card products">
                <div class="header">
                    <div class="title">Total Products</div>
                    <div class="icon"><i class="ri-box-line"></i></div>
                </div>
                <div class="value"><?php echo $products_count; ?></div>
                <div class="change positive"><i class="ri-arrow-up-line"></i> 12% from last month</div>
            </div>
            
            <div class="stat-card users">
                <div class="header">
                    <div class="title">Total Users</div>
                    <div class="icon"><i class="ri-user-line"></i></div>
                </div>
                <div class="value"><?php echo $users_count; ?></div>
                <div class="change positive"><i class="ri-arrow-up-line"></i> 5% from last month</div>
            </div>
            
            <div class="stat-card orders">
                <div class="header">
                    <div class="title">Total Orders</div>
                    <div class="icon"><i class="ri-shopping-cart-line"></i></div>
                </div>
                <div class="value"><?php echo $orders_count; ?></div>
                <div class="change positive"><i class="ri-arrow-up-line"></i> 8% from last month</div>
            </div>
            
            <div class="stat-card revenue">
                <div class="header">
                    <div class="title">Total Revenue</div>
                    <div class="icon"><i class="ri-money-dollar-circle-line"></i></div>
                </div>
                <div class="value">$<?php echo number_format($revenue, 2); ?></div>
                <div class="change positive"><i class="ri-arrow-up-line"></i> 15% from last month</div>
            </div>
        </div>
        
        <div class="data-section">
            <div class="chart-container">
                <div class="chart-header">
                    <h2 class="chart-title">Revenue Overview</h2>
                    <div class="chart-actions">
                        <button>Month</button>
                        <button>Quarter</button>
                        <button class="active">Year</button>
                    </div>
                </div>
                <div class="chart-wrapper">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
            
            <div class="recent-orders">
                <div class="header">
                    <h2 class="title">Recent Orders</h2>
                    <a href="#" class="view-all">View All</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($orders->num_rows > 0): ?>
                            <?php while ($order = $orders->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $order['id']; ?></td>
                                    <td><?php echo $order['customer_name']; ?></td>
                                    <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                    <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                    <td>
                                        <span class="status status-<?php echo $order['status']; ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                    <td><a href="order_details.php?id=<?php echo $order['id']; ?>" class="action-link">View</a></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center;">No orders found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="popular-products">
            <div class="chart-container" style="grid-column: 1 / -1;">
                <div class="chart-header">
                    <h2 class="chart-title">User Registrations (Last 6 Months)</h2>
                </div>
                <div class="chart-wrapper">
                    <canvas id="userChart"></canvas>
                </div>
            </div>
            
            <div style="grid-column: 1 / -1; margin-top: 20px;">
                <h2 class="chart-title">Popular Products</h2>
            </div>
            
            <?php if ($popular_products->num_rows > 0): ?>
                <?php while ($product = $popular_products->fetch_assoc()): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <?php if (!empty($product['image_path'])): ?>
                                <img src="<?php echo $product['image_path']; ?>" alt="<?php echo $product['title']; ?>">
                            <?php else: ?>
                                <i class="ri-image-line" style="font-size: 40px; color: #ccc;"></i>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <h3 class="product-title"><?php echo $product['title']; ?></h3>
                            <div class="product-meta">
                                <div class="orders-count"><?php echo $product['order_count']; ?> orders</div>
                                <a href="product_management.php?edit=<?php echo $product['id']; ?>" class="action-link">Edit</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px;">
                    <p>No popular products found</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="dashboard-footer">
            <p>© <?php echo date('Y'); ?> AdminPanel. All rights reserved.</p>
        </div>
    </div>
    
    <script>
        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Revenue ($)',
                    data: [12500, 19000, 18000, 22000, 19500, 24000, 26000, 23000, 28000, 29500, 32000, 35000],
                    borderColor: '#4361ee',
                    backgroundColor: 'rgba(67, 97, 238, 0.1)',
                    borderWidth: 3,
                    pointRadius: 5,
                    pointBackgroundColor: '#fff',
                    pointBorderWidth: 2,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false
                        },
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
        
        // User Registration Chart
        const userCtx = document.getElementById('userChart').getContext('2d');
        const userChart = new Chart(userCtx, {
            type: 'bar',
            data: {
                labels: [
                    <?php 
                    if ($user_stats->num_rows > 0) {
                        $labels = [];
                        while ($row = $user_stats->fetch_assoc()) {
                            $labels[] = "'" . date('M Y', strtotime($row['month'] . '-01')) . "'";
                        }
                        echo implode(', ', $labels);
                    } else {
                        echo "'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'";
                    }
                    ?>
                ],
                datasets: [{
                    label: 'New Users',
                    data: [
                        <?php 
                        if ($user_stats->num_rows > 0) {
                            $user_stats->data_seek(0); // Reset pointer
                            $data = [];
                            while ($row = $user_stats->fetch_assoc()) {
                                $data[] = $row['count'];
                            }
                            echo implode(', ', $data);
                        } else {
                            echo "45, 60, 75, 50, 85, 95";
                        }
                        ?>
                    ],
                    backgroundColor: 'rgba(76, 201, 240, 0.7)',
                    borderColor: 'rgba(76, 201, 240, 1)',
                    borderWidth: 1,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
        
        // Chart period buttons
        document.querySelectorAll('.chart-actions button').forEach(button => {
            button.addEventListener('click', function() {
                document.querySelectorAll('.chart-actions button').forEach(btn => {
                    btn.classList.remove('active');
                });
                this.classList.add('active');
            });
        });
    </script>
</body>
</html>