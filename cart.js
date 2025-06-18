document.addEventListener('DOMContentLoaded', function() {
    // Clear cart button
    document.getElementById('clear-cart')?.addEventListener('click', function() {
        if (confirm('Are you sure you want to clear your cart?')) {
            fetch('update_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ action: 'clear' })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                }
            });
        }
    });
    
    // Checkout button
    document.getElementById('checkout-btn')?.addEventListener('click', function() {
        window.location.href = 'paymentGateway.php';
    });
    
    // Quantity buttons
    document.querySelectorAll('.quantity-btn.minus').forEach(btn => {
        btn.addEventListener('click', function() {
            updateQuantity(this.dataset.index, -1);
        });
    });
    
    document.querySelectorAll('.quantity-btn.plus').forEach(btn => {
        btn.addEventListener('click', function() {
            updateQuantity(this.dataset.index, 1);
        });
    });
    
    // Quantity inputs
    document.querySelectorAll('.quantity input').forEach(input => {
        input.addEventListener('change', function() {
            const newQuantity = parseInt(this.value) || 1;
            updateQuantity(this.dataset.index, 0, newQuantity);
        });
    });
    
    // Remove buttons
    document.querySelectorAll('.remove').forEach(btn => {
        btn.addEventListener('click', function() {
            removeItem(this.dataset.index);
        });
    });
    
    function updateQuantity(index, change, newQuantity = null) {
        fetch('update_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ 
                action: 'update_quantity',
                index: index,
                change: change,
                newQuantity: newQuantity
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            }
        });
    }
    
    function removeItem(index) {
        fetch('update_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ 
                action: 'remove',
                index: index
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            }
        });
    }
});