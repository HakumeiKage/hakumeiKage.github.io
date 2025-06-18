document.addEventListener('DOMContentLoaded', function() {
    const addToCartBtn = document.getElementById('add-to-cart');
    const quantityInput = document.querySelector('.quantity-input');
    const minusBtn = document.querySelector('.quantity-btn.minus');
    const plusBtn = document.querySelector('.quantity-btn.plus');
    
    let quantity = 1;
    
    minusBtn.addEventListener('click', () => {
        if (quantity > 1) {
            quantity--;
            quantityInput.value = quantity;
        }
    });
    
    plusBtn.addEventListener('click', () => {
        quantity++;
        quantityInput.value = quantity;
    });
    
    quantityInput.addEventListener('change', () => {
        quantity = Math.max(1, parseInt(quantityInput.value) || 1);
        quantityInput.value = quantity;
    });
    
    addToCartBtn.addEventListener('click', () => {
        const productId = addToCartBtn.dataset.id;
        
        // Send AJAX request to add to cart
        fetch('add_to_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id: productId,
                quantity: quantity
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Product added to cart!');
                // Update cart count
                document.querySelector('.cart-item-count').textContent = data.cart_count;
                document.querySelector('.cart-item-count').style.display = 'block';
            } else {
                alert('Error adding product to cart');
            }
        });
    });
});