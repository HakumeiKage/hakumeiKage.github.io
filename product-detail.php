<?php
require_once 'db.php';
$page_title = 'Product Details';
$page_scripts = ['product-detail.js'];

if (!isset($_GET['id'])) {
    header('Location: purchase.php');
    exit();
}

$product_id = (int)$_GET['id'];
$product = null;

// Get product details
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $product = $result->fetch_assoc();
} else {
    header('Location: purchase.php');
    exit();
}

require 'header.php';
?>

<main class="product-container">
    <div class="product-gallery">
        <div class="main-image">
            <img id="main-product-image" src="<?= htmlspecialchars($product['image_path']) ?>" alt="<?= htmlspecialchars($product['title']) ?>">
        </div>
    </div>

    <div class="product-info">
        <h1 class="product-title"><?= htmlspecialchars($product['title']) ?></h1>
        <div class="product-meta">
            <div class="rating">
                <i class="ri-star-fill"></i>
                <i class="ri-star-fill"></i>
                <i class="ri-star-fill"></i>
                <i class="ri-star-fill"></i>
                <i class="ri-star-line"></i>
                <span class="rating-count">(4.8)</span>
            </div>
            <span class="product-price">$<?= number_format($product['price'], 2) ?></span>
        </div>
        
        <p class="product-description"><?= htmlspecialchars($product['description']) ?></p>
        
        <div class="product-actions">
            <div class="quantity-selector">
                <button class="quantity-btn minus">-</button>
                <input type="number" value="1" min="1" class="quantity-input">
                <button class="quantity-btn plus">+</button>
            </div>
            <button class="btn btn-primary" id="add-to-cart" data-id="<?= $product['id'] ?>">Add to Cart</button>
        </div>

        <div class="product-policy">
            <div class="policy-item">
                <i class="ri-shield-check-line"></i>
                <span>100% Original Products</span>
            </div>
            <div class="policy-item">
                <i class="ri-refresh-line"></i>
                <span>Easy 14-Day Returns</span>
            </div>
        </div>
    </div>
</main>

<?php require 'footer.php'; ?>