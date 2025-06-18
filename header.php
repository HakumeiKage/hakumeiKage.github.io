<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Ecommerce Store'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="Wstyle.css">
</head>
<body>
    <nav>
    <div class="nav-left">
        <a href="index.php" class="home-link" aria-label="Home">
            <i class="ri-home-line"></i>
            <span class="visually-hidden">Home</span>
        </a>
        <a href="purchase.php" class="brand-name">Ecommerce.</a>
    </div>
    <div class="nav-right">
        <a href="cart.php" class="cart-icon" aria-label="Shopping Cart">
            <i class="ri-shopping-bag-line"></i>
            <?php
            $cart_count = 0;
            if (isset($_SESSION['cart'])) {
                foreach ($_SESSION['cart'] as $item) {
                    $cart_count += $item['quantity'];
                }
            }
            if ($cart_count > 0): ?>
                <span class="cart-item-count"><?= $cart_count ?></span>
            <?php endif; ?>
        </a>
    </div>
</nav>