<?php
session_start();
require_once 'db.php';

// Check if user is admin
$is_admin = false;
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT is_admin FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $is_admin = (bool)$user['is_admin'];
    }
}

// Redirect non-admins
if (!$is_admin) {
    header('Location: dashboard.php');
    exit();
}

// Get order ID from URL
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch order details
$order = null;
$order_items = [];
$customer = null;

if ($order_id > 0) {
    // Get order information
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $order = $result->fetch_assoc();
        
        // Get order items
        $stmt = $conn->prepare("SELECT oi.*, p.title, p.image_path 
                               FROM order_items oi 
                               JOIN products p ON oi.product_id = p.id 
                               WHERE oi.order_id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $order_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Get customer information
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $order['user_id']);
        $stmt->execute();
        $customer = $stmt->get_result()->fetch_assoc();
    }
}

// Handle form submissions
$success = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update order status
    if (isset($_POST['update_status'])) {
        $new_status = $_POST['status'];
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $order_id);
        
        if ($stmt->execute()) {
            $success = "Order status updated successfully!";
            // Refresh order data
            $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            $order = $stmt->get_result()->fetch_assoc();
        } else {
            $errors[] = "Failed to update order status: " . $stmt->error;
        }
    }
    
    // Update order items
    if (isset($_POST['update_items'])) {
        foreach ($_POST['items'] as $item_id => $quantity) {
            $quantity = (int)$quantity;
            if ($quantity > 0) {
                $stmt = $conn->prepare("UPDATE order_items SET quantity = ? WHERE id = ?");
                $stmt->bind_param("ii", $quantity, $item_id);
                $stmt->execute();
            }
        }
        
        // Recalculate order total
        $stmt = $conn->prepare("SELECT SUM(price * quantity) AS new_total 
                               FROM order_items 
                               WHERE order_id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $new_total = $stmt->get_result()->fetch_assoc()['new_total'];
        
        $stmt = $conn->prepare("UPDATE orders SET total_amount = ? WHERE id = ?");
        $stmt->bind_param("di", $new_total, $order_id);
        $stmt->execute();
        
        $success = "Order items updated successfully!";
        
        // Refresh order and items
        $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();
        
        $stmt = $conn->prepare("SELECT oi.*, p.title, p.image_path 
                               FROM order_items oi 
                               JOIN products p ON oi.product_id = p.id 
                               WHERE oi.order_id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $order_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    // Delete order
    if (isset($_POST['delete_order'])) {
        // Delete order items first
        $stmt = $conn->prepare("DELETE FROM order_items WHERE order_id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        
        // Delete order
        $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->bind_param("i", $order_id);
        
        if ($stmt->execute()) {
            header('Location: orders.php');
            exit();
        } else {
            $errors[] = "Failed to delete order: " . $stmt->error;
        }
    }
    
    // Add tracking info
    if (isset($_POST['add_tracking'])) {
        $tracking_number = $_POST['tracking_number'];
        $carrier = $_POST['carrier'];
        
        $stmt = $conn->prepare("UPDATE orders SET tracking_number = ?, carrier = ? WHERE id = ?");
        $stmt->bind_param("ssi", $tracking_number, $carrier, $order_id);
        
        if ($stmt->execute()) {
            $success = "Tracking information updated successfully!";
            // Refresh order data
            $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            $order = $stmt->get_result()->fetch_assoc();
        } else {
            $errors[] = "Failed to update tracking information: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --warning: #f72585;
            --danger: #dc3545;
            --dark: #212529;
            --light: #f8f9fa;
            --gray: #6c757d;
            --border: #dee2e6;
            --sidebar-width: 260px;
            --header-height: 70px;
            --transition: all 0.3s ease;
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
            transition: var(--transition);
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
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .logout-btn:hover {
            color: var(--warning);
        }
        
        /* Order Management */
        .order-management {
            padding: 120px 40px 40px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--dark);
        }
        
        .back-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: var(--light);
            border-radius: 6px;
            text-decoration: none;
            color: var(--gray);
            font-weight: 500;
            transition: var(--transition);
        }
        
        .back-btn:hover {
            background: #e9ecef;
        }
        
        .order-container {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 30px;
        }
        
        @media (max-width: 992px) {
            .order-container {
                grid-template-columns: 1fr;
            }
        }
        
        .order-details-card, .order-actions-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .section-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border);
        }
        
        .order-info {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
        }
        
        .info-label {
            font-size: 14px;
            color: var(--gray);
            margin-bottom: 5px;
        }
        
        .info-value {
            font-weight: 500;
            font-size: 16px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }
        
        .status-pending {
            background: rgba(255, 193, 7, 0.15);
            color: #ffc107;
        }
        
        .status-processing {
            background: rgba(0, 123, 255, 0.15);
            color: #007bff;
        }
        
        .status-shipped {
            background: rgba(40, 167, 69, 0.15);
            color: #28a745;
        }
        
        .status-delivered {
            background: rgba(108, 117, 125, 0.15);
            color: #6c757d;
        }
        
        .status-cancelled {
            background: rgba(220, 53, 69, 0.15);
            color: #dc3545;
        }
        
        .customer-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
        }
        
        .customer-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .customer-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.4rem;
        }
        
        .customer-name {
            font-weight: 600;
            font-size: 18px;
        }
        
        .customer-email {
            color: var(--gray);
            font-size: 14px;
        }
        
        .customer-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .detail-item {
            margin-bottom: 10px;
        }
        
        .detail-label {
            font-size: 14px;
            color: var(--gray);
            margin-bottom: 3px;
        }
        
        .detail-value {
            font-weight: 500;
        }
        
        .order-items {
            margin-top: 25px;
        }
        
        .order-item {
            display: flex;
            padding: 15px 0;
            border-bottom: 1px solid var(--border);
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .item-image {
            width: 80px;
            height: 80px;
            background: #f8f9fa;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            overflow: hidden;
        }
        
        .item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .item-info {
            flex: 1;
        }
        
        .item-name {
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .item-meta {
            display: flex;
            justify-content: space-between;
            color: var(--gray);
            font-size: 14px;
        }
        
        .item-quantity {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .item-quantity input {
            width: 60px;
            padding: 8px;
            border: 1px solid var(--border);
            border-radius: 6px;
            text-align: center;
        }
        
        .order-summary {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
        }
        
        .summary-row.total {
            font-weight: 700;
            font-size: 18px;
            padding-top: 12px;
            margin-top: 12px;
            border-top: 1px solid var(--border);
        }
        
        .status-form {
            margin-bottom: 25px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .form-group select, 
        .form-group input {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: white;
            font-size: 16px;
        }
        
        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .btn {
            padding: 12px 20px;
            border-radius: 8px;
            border: none;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-align: center;
            font-size: 16px;
        }
        
        .btn-block {
            width: 100%;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--primary-dark);
        }
        
        .btn-outline {
            background: transparent;
            border: 1px solid var(--border);
            color: var(--dark);
        }
        
        .btn-outline:hover {
            background: var(--light);
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background: #218838;
        }
        
        .btn-danger {
            background: var(--danger);
            color: white;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
        }
        
        .alert-success {
            background: rgba(40, 167, 69, 0.15);
            color: #28a745;
            border: 1px solid rgba(40, 167, 69, 0.2);
        }
        
        .alert-danger {
            background: rgba(220, 53, 69, 0.15);
            color: var(--danger);
            border: 1px solid rgba(220, 53, 69, 0.2);
        }
        
        .no-order {
            text-align: center;
            padding: 50px 20px;
        }
        
        .no-order i {
            font-size: 60px;
            color: #dee2e6;
            margin-bottom: 20px;
        }
        
        .no-order h3 {
            font-size: 24px;
            margin-bottom: 15px;
            color: var(--dark);
        }
        
        .no-order p {
            color: var(--gray);
            margin-bottom: 30px;
        }
        
        .tracking-form {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
        }
    </style>
</head>
<body>
    <nav>
        <a href="dashboard.php" class="logo">AdminPanel</a>
        
        <div class="admin-menu">
            <a href="dashboard.php"><i class="ri-dashboard-line"></i> Dashboard</a>
            <a href="product_management.php"><i class="ri-shopping-bag-line"></i> Products</a>
            <a href="orders.php" class="active"><i class="ri-list-check"></i> Orders</a>
            <a href="#"><i class="ri-user-line"></i> Customers</a>
            <a href="#"><i class="ri-bar-chart-line"></i> Reports</a>
        </div>
        
        <div class="user-info">
            <div class="avatar"><?php echo isset($_SESSION['name']) ? strtoupper(substr($_SESSION['name'], 0, 1)) : 'A'; ?></div>
            <span><?php echo $_SESSION['name'] ?? 'Admin User'; ?></span>
            <a href="logout.php" class="logout-btn"><i class="ri-logout-box-r-line"></i> Logout</a>
        </div>
    </nav>

    <div class="order-management">
        <div class="order-header">
            <h1 class="page-title">Order Details</h1>
            <a href="orders.php" class="back-btn">
                <i class="ri-arrow-left-line"></i> Back to Orders
            </a>
        </div>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <strong>Error:</strong>
                <?php foreach ($errors as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($order): ?>
            <div class="order-container">
                <!-- Order Details -->
                <div>
                    <div class="order-details-card">
                        <h2 class="section-title">Order Information</h2>
                        
                        <div class="order-info">
                            <div class="info-item">
                                <span class="info-label">Order ID</span>
                                <span class="info-value">#<?php echo $order['id']; ?></span>
                            </div>
                            
                            <div class="info-item">
                                <span class="info-label">Order Date</span>
                                <span class="info-value"><?php echo date('M d, Y', strtotime($order['order_date'])); ?></span>
                            </div>
                            
                            <div class="info-item">
                                <span class="info-label">Status</span>
                                <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                                    <?php echo $order['status']; ?>
                                </span>
                            </div>
                            
                            <div class="info-item">
                                <span class="info-label">Payment Method</span>
                                <span class="info-value">Credit Card</span>
                            </div>
                            
                            <div class="info-item">
                                <span class="info-label">Payment Status</span>
                                <span class="info-value">Completed</span>
                            </div>
                            
                            <div class="info-item">
                                <span class="info-label">Total Amount</span>
                                <span class="info-value">$<?php echo number_format($order['total_amount'], 2); ?></span>
                            </div>
                        </div>
                        
                        <h2 class="section-title">Customer Information</h2>
                        
                        <div class="customer-card">
                            <div class="customer-header">
                                <div class="customer-avatar">
                                    <?php echo isset($customer['name']) ? strtoupper(substr($customer['name'], 0, 1)) : 'C'; ?>
                                </div>
                                <div>
                                    <div class="customer-name"><?php echo $customer['name'] ?? 'Customer Name'; ?></div>
                                    <div class="customer-email"><?php echo $customer['email'] ?? 'customer@example.com'; ?></div>
                                </div>
                            </div>
                            
                            <div class="customer-details">
                                <div class="detail-item">
                                    <div class="detail-label">Phone</div>
                                    <div class="detail-value"><?php echo $customer['phone'] ?? '(555) 123-4567'; ?></div>
                                </div>
                                
                                <div class="detail-item">
                                    <div class="detail-label">Address</div>
                                    <div class="detail-value"><?php echo $order['shipping_address']; ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <h2 class="section-title">Order Items</h2>
                        
                        <form method="POST">
                            <div class="order-items">
                                <?php foreach ($order_items as $item): ?>
                                    <div class="order-item">
                                        <div class="item-image">
                                            <?php if (!empty($item['image_path'])): ?>
                                                <img src="<?php echo $item['image_path']; ?>" alt="<?php echo $item['title']; ?>">
                                            <?php else: ?>
                                                <i class="ri-shopping-bag-line" style="font-size: 24px; color: var(--gray);"></i>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="item-info">
                                            <div class="item-name"><?php echo $item['title']; ?></div>
                                            
                                            <div class="item-meta">
                                                <div class="item-price">$<?php echo number_format($item['price'], 2); ?></div>
                                                
                                                <div class="item-quantity">
                                                    <span>Quantity:</span>
                                                    <input type="number" name="items[<?php echo $item['id']; ?>]" 
                                                           value="<?php echo $item['quantity']; ?>" min="1">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="order-summary">
                                <div class="summary-row">
                                    <span>Subtotal:</span>
                                    <span>$<?php echo number_format($order['total_amount'] * 0.8, 2); ?></span>
                                </div>
                                
                                <div class="summary-row">
                                    <span>Shipping:</span>
                                    <span>$<?php echo number_format($order['total_amount'] * 0.05, 2); ?></span>
                                </div>
                                
                                <div class="summary-row">
                                    <span>Tax:</span>
                                    <span>$<?php echo number_format($order['total_amount'] * 0.15, 2); ?></span>
                                </div>
                                
                                <div class="summary-row total">
                                    <span>Total:</span>
                                    <span>$<?php echo number_format($order['total_amount'], 2); ?></span>
                                </div>
                            </div>
                            
                            <button type="submit" name="update_items" class="btn btn-primary btn-block">
                                <i class="ri-refresh-line"></i> Update Order Items
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Order Actions -->
                <div>
                    <div class="order-actions-card">
                        <h2 class="section-title">Order Actions</h2>
                        
                        <form method="POST">
                            <div class="status-form">
                                <div class="form-group">
                                    <label for="order-status">Update Order Status</label>
                                    <select id="order-status" name="status" class="status-select">
                                        <option value="Pending" <?php echo $order['status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="Processing" <?php echo $order['status'] === 'Processing' ? 'selected' : ''; ?>>Processing</option>
                                        <option value="Shipped" <?php echo $order['status'] === 'Shipped' ? 'selected' : ''; ?>>Shipped</option>
                                        <option value="Delivered" <?php echo $order['status'] === 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                                        <option value="Cancelled" <?php echo $order['status'] === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                </div>
                                
                                <button type="submit" name="update_status" class="btn btn-primary btn-block">
                                    <i class="ri-check-line"></i> Update Status
                                </button>
                            </div>
                        </form>
                        
                        <div class="action-buttons">
                            <button class="btn btn-success">
                                <i class="ri-printer-line"></i> Print Invoice
                            </button>
                            
                            <button class="btn btn-outline">
                                <i class="ri-mail-line"></i> Email Customer
                            </button>
                            
                            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this order?');">
                                <button type="submit" name="delete_order" class="btn btn-danger btn-block">
                                    <i class="ri-delete-bin-line"></i> Delete Order
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="order-actions-card">
                        <h2 class="section-title">Shipping Information</h2>
                        
                        <div class="detail-item">
                            <div class="detail-label">Shipping Address</div>
                            <div class="detail-value"><?php echo $order['shipping_address']; ?></div>
                        </div>
                        
                        <div class="detail-item">
                            <div class="detail-label">Tracking Number</div>
                            <div class="detail-value"><?php echo $order['tracking_number'] ?? 'Not assigned'; ?></div>
                        </div>
                        
                        <div class="detail-item">
                            <div class="detail-label">Carrier</div>
                            <div class="detail-value"><?php echo $order['carrier'] ?? 'Not specified'; ?></div>
                        </div>
                        
                        <div class="detail-item">
                            <div class="detail-label">Estimated Delivery</div>
                            <div class="detail-value"><?php 
                                if ($order['status'] === 'Shipped') {
                                    echo date('M d, Y', strtotime('+3 days'));
                                } else {
                                    echo 'Not shipped yet';
                                }
                            ?></div>
                        </div>
                        
                        <form method="POST" class="tracking-form">
                            <div class="form-group">
                                <label for="tracking-number">Tracking Number</label>
                                <input type="text" id="tracking-number" name="tracking_number" 
                                       value="<?php echo $order['tracking_number'] ?? ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="carrier">Carrier</label>
                                <select id="carrier" name="carrier">
                                    <option value="">Select Carrier</option>
                                    <option value="UPS" <?php echo isset($order['carrier']) && $order['carrier'] === 'UPS' ? 'selected' : ''; ?>>UPS</option>
                                    <option value="FedEx" <?php echo isset($order['carrier']) && $order['carrier'] === 'FedEx' ? 'selected' : ''; ?>>FedEx</option>
                                    <option value="USPS" <?php echo isset($order['carrier']) && $order['carrier'] === 'USPS' ? 'selected' : ''; ?>>USPS</option>
                                    <option value="DHL" <?php echo isset($order['carrier']) && $order['carrier'] === 'DHL' ? 'selected' : ''; ?>>DHL</option>
                                </select>
                            </div>
                            
                            <button type="submit" name="add_tracking" class="btn btn-primary btn-block">
                                <i class="ri-truck-line"></i> Update Tracking Info
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="no-order">
                <i class="ri-shopping-bag-line"></i>
                <h3>Order Not Found</h3>
                <p>Sorry, we couldn't find the order you're looking for.</p>
                <a href="orders.php" class="btn btn-primary">
                    <i class="ri-arrow-left-line"></i> Back to Orders
                </a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>