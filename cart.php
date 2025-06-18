<?php
session_start();
require_once 'db.php';
$page_title = 'Shopping Cart';
$page_scripts = ['cart.js'];

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$cart = $_SESSION['cart'];
$subtotal = 0;
$tax_rate = 0.10; // 10% tax

// Calculate totals
foreach ($cart as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$tax = $subtotal * $tax_rate;
$grand_total = $subtotal + $tax;

require 'header.php';
?>

<div class="cart-container">
    <h1>Your Shopping Cart</h1>
    
    <?php if (count($cart) === 0): ?>
        <div class="cart-empty-state">
            <i class="ri-shopping-cart-line"></i>
            <h3>Your cart is empty</h3>
            <p>Looks like you haven't added any items to your cart yet</p>
            <button class="continue-shopping" onclick="window.location.href='purchase.php'">
                Continue Shopping
            </button>
        </div>
    <?php else: ?>
        <div class="cart-content">
            <div class="cart-header">
                <span class="header-product">Product</span>
                <span class="header-price">Price</span>
                <span class="header-quantity">Quantity</span>
                <span class="header-total">Total</span>
                <span class="header-action">Action</span>
            </div>

            <div class="cart-items">
                <?php foreach ($cart as $index => $item): ?>
                    <?php 
                    $item_total = $item['price'] * $item['quantity'];
                    $subtotal += $item_total;
                    ?>
                    <div class="cart-item" data-index="<?= $index ?>">
                        <div class="product">
                            <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['title']) ?>">
                            <div class="item-detail">
                                <p class="product-title"><?= htmlspecialchars($item['title']) ?></p>
                            </div>
                        </div>
                        <span class="price">$<?= number_format($item['price'], 2) ?></span>
                        <div class="quantity">
                            <button class="quantity-btn minus" data-index="<?= $index ?>">-</button>
                            <input type="number" value="<?= $item['quantity'] ?>" min="1" data-index="<?= $index ?>">
                            <button class="quantity-btn plus" data-index="<?= $index ?>">+</button>
                        </div>
                        <span class="total-price">$<?= number_format($item_total, 2) ?></span>
                        <button class="remove" data-index="<?= $index ?>">
                            <i class="ri-close-line"></i>
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="cart-actions">
                <button class="btn btn-outline" onclick="window.location.href='purchase.php'">
                    <i class="ri-arrow-left-line"></i> Continue Shopping
                </button>
                <button class="btn btn-outline" id="clear-cart">
                    <i class="ri-delete-bin-line"></i> Clear Cart
                </button>
            </div>

            <div class="cart-total">
                <h3>Order Summary</h3>
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span class="subtotal">$<?= number_format($subtotal, 2) ?></span>
                </div>
                <div class="summary-row">
                    <span>Shipping</span>
                    <span>Free</span>
                </div>
                <div class="summary-row">
                    <span>Tax</span>
                    <span class="tax">$<?= number_format($tax, 2) ?></span>
                </div>
                <div class="summary-row total">
                    <span>Total</span>
                    <span class="grand-total">$<?= number_format($grand_total, 2) ?></span>
                </div>
                <button class="btn btn-primary" id="checkout-btn">
                    Proceed to Checkout <i class="ri-arrow-right-line"></i>
                </button>
            </div>
        </div>
    <?php endif; // THIS WAS MISSING ?>
</div>

<?php require 'footer.php'; ?>