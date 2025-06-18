<?php
session_start();
require_once 'db.php';
$page_title = 'Shop Products';
$page_scripts = ['purchase.js'];

// Get all products from database
$products = [];
$result = $conn->query("SELECT * FROM products");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

// Get unique categories for filtering
$categories = [];
$categoryResult = $conn->query("SELECT DISTINCT category FROM products");
if ($categoryResult->num_rows > 0) {
    while ($row = $categoryResult->fetch_assoc()) {
        $categories[] = $row['category'];
    }
}

require 'header.php';
?>

<div class="purchase-container">
    <h1>Shop Computer Products</h1>
    
    <!-- Filter Section -->
    <div class="product-filters">
        <div class="filter-group">
            <label for="category-filter">Category:</label>
            <select id="category-filter">
                <option value="all">All Categories</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= htmlspecialchars($category) ?>"><?= htmlspecialchars($category) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="filter-group">
            <label for="price-filter">Price Range:</label>
            <select id="price-filter">
                <option value="all">All Prices</option>
                <option value="0-500">Under $500</option>
                <option value="500-1000">$500 - $1000</option>
                <option value="1000-2000">$1000 - $2000</option>
                <option value="2000+">$2000+</option>
            </select>
        </div>
        
        <div class="filter-group">
            <label for="search">Search:</label>
            <input type="text" id="search" placeholder="Search products...">
        </div>
    </div>
    
    <!-- Product Grid -->
    <div class="products-grid">
        <?php if (count($products) > 0): ?>
            <?php foreach ($products as $product): ?>
                <div class="product-card" 
                     data-id="<?= $product['id'] ?>" 
                     data-category="<?= htmlspecialchars($product['category']) ?>" 
                     data-price="<?= $product['price'] ?>"
                     data-title="<?= htmlspecialchars(strtolower($product['title'])) ?>">
                    <div class="product-img">
                        <?php if (!empty($product['image_path'])): ?>
                            <img src="<?= htmlspecialchars($product['image_path']) ?>" alt="<?= htmlspecialchars($product['title']) ?>">
                        <?php else: ?>
                            <div class="image-placeholder">
                                <i class="ri-computer-line"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <h3><?= htmlspecialchars($product['title']) ?></h3>
                        <div class="product-meta">
                            <span class="category"><?= htmlspecialchars($product['category']) ?></span>
                            <span class="price">$<?= number_format($product['price'], 2) ?></span>
                        </div>
                        <p class="description"><?= htmlspecialchars(substr($product['description'], 0, 100)) ?>...</p>
                        <div class="product-actions">
                            <a href="product-detail.php?id=<?= $product['id'] ?>" class="btn btn-outline">View Details</a>
                            <button class="btn btn-primary add-to-cart" data-id="<?= $product['id'] ?>">
                                <i class="ri-shopping-cart-line"></i> Add to Cart
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-products">
                <i class="ri-computer-line"></i>
                <h3>No Products Available</h3>
                <p>Check back later for new arrivals</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .purchase-container {
        padding: 100px 5% 50px;
        max-width: 1400px;
        margin: 0 auto;
    }
    
    .purchase-container h1 {
        text-align: center;
        margin-bottom: 40px;
        font-size: 2.5rem;
        color: #222;
    }
    
    .product-filters {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 40px;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 10px;
    }
    
    .filter-group {
        display: flex;
        flex-direction: column;
        flex: 1;
        min-width: 200px;
    }
    
    .filter-group label {
        margin-bottom: 8px;
        font-weight: 500;
        color: #444;
    }
    
    .filter-group select, .filter-group input {
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 8px;
        background: white;
        font-size: 1rem;
    }
    
    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 30px;
    }
    
    .product-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
    }
    
    .product-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
    }
    
    .product-img {
        height: 200px;
        background: #f8f8f8;
        display: flex;
        align-items: center;
        justify-content: center;
        border-bottom: 1px solid #eee;
    }
    
    .product-img img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }
    
    .image-placeholder {
        font-size: 60px;
        color: #ddd;
    }
    
    .product-info {
        padding: 25px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }
    
    .product-info h3 {
        font-size: 1.3rem;
        margin-bottom: 15px;
        color: #222;
    }
    
    .product-meta {
        display: flex;
        justify-content: space-between;
        margin-bottom: 15px;
    }
    
    .category {
        background: #e3f2fd;
        color: #1976d2;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.9rem;
    }
    
    .price {
        font-weight: bold;
        color: #e35f26;
        font-size: 1.2rem;
    }
    
    .description {
        color: #666;
        margin-bottom: 20px;
        flex-grow: 1;
        font-size: 0.95rem;
        line-height: 1.5;
    }
    
    .product-actions {
        display: flex;
        gap: 10px;
        margin-top: auto;
    }
    
    .btn {
        display: inline-block;
        padding: 10px 20px;
        border-radius: 8px;
        text-align: center;
        text-decoration: none;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s;
        flex: 1;
    }
    
    .btn-outline {
        background: white;
        border: 1px solid #222;
        color: #222;
    }
    
    .btn-outline:hover {
        background: #222;
        color: white;
    }
    
    .btn-primary {
        background: #e35f26;
        color: white;
        border: none;
    }
    
    .btn-primary:hover {
        background: #c14f1f;
    }
    
    .no-products {
        grid-column: 1 / -1;
        text-align: center;
        padding: 60px 20px;
    }
    
    .no-products i {
        font-size: 80px;
        color: #ddd;
        margin-bottom: 20px;
    }
    
    .no-products h3 {
        font-size: 1.8rem;
        margin-bottom: 10px;
        color: #444;
    }
    
    .no-products p {
        color: #777;
        font-size: 1.1rem;
    }
    
    @media (max-width: 768px) {
        .purchase-container {
            padding: 80px 5% 30px;
        }
        
        .product-filters {
            flex-direction: column;
        }
        
        .products-grid {
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        }
    }
</style>

<script>
    // Filter products based on selections
    document.addEventListener('DOMContentLoaded', function() {
        const categoryFilter = document.getElementById('category-filter');
        const priceFilter = document.getElementById('price-filter');
        const searchInput = document.getElementById('search');
        const productCards = document.querySelectorAll('.product-card');
        
        // Add event listeners to filters
        categoryFilter.addEventListener('change', filterProducts);
        priceFilter.addEventListener('change', filterProducts);
        searchInput.addEventListener('input', filterProducts);
        
        // Add to cart functionality
        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.dataset.id;
                
                fetch('add_to_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id: productId,
                        quantity: 1
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message
                        const alert = document.createElement('div');
                        alert.className = 'cart-alert success';
                        alert.innerHTML = `
                            <i class="ri-checkbox-circle-fill"></i>
                            <span>Product added to cart!</span>
                        `;
                        document.body.appendChild(alert);
                        
                        // Remove after 3 seconds
                        setTimeout(() => {
                            alert.remove();
                        }, 3000);
                        
                        // Update cart count
                        const cartCount = document.querySelector('.cart-item-count');
                        if (cartCount) {
                            cartCount.textContent = data.cart_count;
                            cartCount.style.display = 'block';
                        }
                    } else {
                        alert('Error adding product to cart');
                    }
                });
            });
        });
        
        function filterProducts() {
            const selectedCategory = categoryFilter.value;
            const selectedPrice = priceFilter.value;
            const searchTerm = searchInput.value.toLowerCase();
            
            productCards.forEach(card => {
                const category = card.dataset.category;
                const price = parseFloat(card.dataset.price);
                const title = card.dataset.title;
                
                // Check category
                const categoryMatch = selectedCategory === 'all' || category === selectedCategory;
                
                // Check price
                let priceMatch = false;
                if (selectedPrice === 'all') {
                    priceMatch = true;
                } else if (selectedPrice === '0-500') {
                    priceMatch = price < 500;
                } else if (selectedPrice === '500-1000') {
                    priceMatch = price >= 500 && price < 1000;
                } else if (selectedPrice === '1000-2000') {
                    priceMatch = price >= 1000 && price < 2000;
                } else if (selectedPrice === '2000+') {
                    priceMatch = price >= 2000;
                }
                
                // Check search term
                const searchMatch = title.includes(searchTerm);
                
                // Show/hide based on filters
                if (categoryMatch && priceMatch && searchMatch) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        }
    });
</script>

<?php require 'footer.php'; ?>