<?php
session_start();
require_once 'db.php';
$page_title = 'Payment Gateway';

if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit();
}

// Calculate cart totals
$subtotal = 0;
foreach ($_SESSION['cart'] as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$tax = $subtotal * 0.10; // 10% tax
$grand_total = $subtotal + $tax;

require 'header.php';
?>

<div class="payment-container">
    <form action="process_payment.php" method="POST" id="payment-form">
        <div class="row">
            <div class="column">
                <h3 class="title">Billing Address</h3>
                <div class="input-box">
                    <label for="fullname">Full Name</label>
                    <input type="text" id="fullname" name="fullname" placeholder="John Doe" required>
                </div>
                <div class="input-box">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="example@example.com" required>
                </div>
                <div class="input-box">
                    <label for="address">Address</label>
                    <input type="text" id="address" name="address" placeholder="123 Main St" required>
                </div>
                <div class="input-box">
                    <label for="city">City</label>
                    <input type="text" id="city" name="city" placeholder="New York" required>
                </div>
                <div class="flex">
                    <div class="input-box">
                        <label for="province">Province/State</label>
                        <input type="text" id="province" name="province" placeholder="NY" required>
                    </div>
                    <div class="input-box">
                        <label for="zip">Zip Code</label>
                        <input type="text" id="zip" name="zip" placeholder="10001" required>
                    </div>
                </div>
            </div>
            
            <div class="column">
                <h3 class="title">Payment Details</h3>
                <div class="input-box">
                    <label for="cardname">Name on Card</label>
                    <input type="text" id="cardname" name="cardname" placeholder="John Doe" required>
                </div>
                <div class="input-box">
                    <label for="cardnumber">Card Number</label>
                    <input type="text" id="cardnumber" name="cardnumber" placeholder="1111222233334444" 
                           pattern="\d{16}" title="16-digit card number" required>
                </div>
                <div class="flex">
                    <div class="input-box">
                        <label for="expmonth">Expiry Month</label>
                        <select id="expmonth" name="expmonth" required>
                            <option value="">Month</option>
                            <?php for ($i = 1; $i <= 12; $i++): ?>
                                <?php $month = str_pad($i, 2, '0', STR_PAD_LEFT); ?>
                                <option value="<?= $month ?>"><?= date('F', mktime(0, 0, 0, $i, 1)) ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="input-box">
                        <label for="expyear">Expiry Year</label>
                        <select id="expyear" name="expyear" required>
                            <option value="">Year</option>
                            <?php 
                            $currentYear = date('Y');
                            for ($i = 0; $i < 10; $i++): ?>
                                <option value="<?= substr($currentYear + $i, 2) ?>"><?= $currentYear + $i ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                <div class="input-box">
                    <label for="cvv">CVV</label>
                    <input type="text" id="cvv" name="cvv" placeholder="123" 
                           pattern="\d{3,4}" title="3 or 4 digit CVV" required>
                </div>
            </div>
        </div>
        
        <input type="hidden" name="cart_data" id="cart-data">
        <button type="submit" class="btn">Complete Payment</button>
    </form>
</div>

<script>
    // Store cart data in hidden field before submission
    document.getElementById('payment-form').addEventListener('submit', function() {
        const cartData = <?= json_encode($_SESSION['cart']) ?>;
        document.getElementById('cart-data').value = JSON.stringify(cartData);
    });
</script>

<?php require 'footer.php'; ?>