<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['order_id'])) {
    header('Location: purchase.php');
    exit();
}

$order_id = $_SESSION['order_id'];
unset($_SESSION['order_id']);

// Get order details
$order = null;
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $order = $result->fetch_assoc();
} else {
    header('Location: purchase.php');
    exit();
}

require 'header.php';
?>

<div class="order-success">
    <h1>Order Confirmed!</h1>
    <i class="ri-checkbox-circle-fill"></i>
    <p>Thank you for your order. Your order ID is: <strong>#<?= $order['id'] ?></strong></p>
    <p>A confirmation email has been sent to <?= $order['customer_email'] ?></p>
    <div class="order-summary">
        <h3>Order Summary</h3>
        <p>Total Amount: <strong>$<?= number_format($order['total_amount'], 2) ?></strong></p>
        <p>Shipping Address: <?= $order['shipping_address'] ?></p>
    </div>
    <a href="purchase.php" class="btn">Continue Shopping</a>
</div>

<style>
    .order-success {
        text-align: center;
        padding: 100px 20px;
    }
    
    .order-success i {
        font-size: 80px;
        color: #4caf50;
        margin: 20px 0;
    }
    
    .order-success h1 {
        font-size: 2.5rem;
        margin-bottom: 20px;
    }
    
    .order-success p {
        font-size: 1.2rem;
        margin-bottom: 10px;
    }
    
    .order-summary {
        background: #f9f9f9;
        border-radius: 8px;
        padding: 20px;
        max-width: 500px;
        margin: 30px auto;
        text-align: left;
    }
    
    .order-summary h3 {
        margin-bottom: 15px;
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
    }
    
    .btn {
        display: inline-block;
        padding: 12px 30px;
        background: #e35f26;
        color: white;
        border-radius: 4px;
        text-decoration: none;
        font-size: 1.1rem;
        margin-top: 20px;
    }
</style>

<?php require 'footer.php'; ?>