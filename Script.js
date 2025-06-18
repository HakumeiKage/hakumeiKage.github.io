const productContainer = document.querySelector(".product-list");
const isProductDetailPage = document.querySelector(".product-detail");
const isCartPage = document.querySelector(".cart");

if (productContainer) {
    displayProducts();
}else if (isProductDetailPage){
    displayProductDetail();
} else if (isCartPage) {
    displayCart();
}

// Update the displayProducts function in Script.js
function displayProducts() {
    products.forEach(product => {
        const productCard = document.createElement("div");
        productCard.classList.add("product-card");
        
        // Add data attributes for filtering/sorting
        productCard.dataset.id = product.id;
        productCard.dataset.title = product.title;
        productCard.dataset.price = product.price.replace("$", "");
        productCard.dataset.category = "laptops"; // Default category
        
        productCard.innerHTML = `
            <div class="img-box">
                <img src="${product.colors[0].mainImage}">
            </div>
            <div class="product-info">
                <h2 class="title">${product.title}</h2>
                <span class="price">${product.price}</span>
                <p class="description">${product.description.substring(0, 100)}...</p>
                <button class="btn view-details">View Details</button>
            </div>
        `;
        productContainer.appendChild(productCard);

        const imgBox = productCard.querySelector(".img-box");
        imgBox.addEventListener("click", () => {
            sessionStorage.setItem("selectedProduct", JSON.stringify(product));
            window.location.href = "product-detail.html";
        });
        
        // Add click event for the view details button
        const viewBtn = productCard.querySelector(".view-details");
        viewBtn.addEventListener("click", () => {
            sessionStorage.setItem("selectedProduct", JSON.stringify(product));
            window.location.href = "product-detail.html";
        });
    });
}
function displayProductDetail() {
    const productData = JSON.parse(sessionStorage.getItem("selectedProduct"));

    const titleEl = document.querySelector(".title");
    const priceEl = document.querySelector(".price");
    const descriptionEl = document.querySelector(".description");
    const mainImageContainer = document.querySelector(".main-img");
    const thumbnailContainer = document.querySelector(".thumbnail-list");
    const colorContainer = document.querySelector(".color-options");
    const sizeContainer = document.querySelector(".size-options");
    const addToCartBtn = document.querySelector("#add-cart-btn");

    let selectedColor = productData.colors[0];
    let selectedSize = selectedColor.sizes[0];

    function updateProductDisplay(colorData) {
        if (!colorData.sizes.includes(selectedSize)){
            selectedSize = colorData.sizes[0];
        }

        mainImageContainer.innerHTML = `<img src="${colorData.mainImage}">`;
        
        thumbnailContainer.innerHTML = "";
        const allThumbnails = [colorData.mainImage].concat(colorData.thumbnails.slice(0, 3));
        allThumbnails.forEach(thumb => {
            const img = document.createElement("img");
            img.src = thumb;

            thumbnailContainer.appendChild(img);

            img.addEventListener("click", () => {
                mainImageContainer.innerHTML = `<img src="${thumb}">`;
            });
        });

        colorContainer.innerHTML = "";
        productData.colors.forEach(color => {
            const img = document.createElement("img");
            img.src = color.mainImage;
            if (color.name === colorData.name) img.classList.add("selected");

            colorContainer.appendChild(img);

            img.addEventListener("click", () => {
                selectedColor = color;
                updateProductDisplay(color);
            });
        });

        sizeContainer.innerHTML = "";
        colorData.sizes.forEach(size => {
            const btn = document.createElement("button");
            btn.textContent = size;
            if (size === selectedSize) btn.classList.add("selected");

            sizeContainer.appendChild(btn);

            btn.addEventListener("click", () => {
                document.querySelectorAll(".size-options button").forEach(el => el.classList.remove("selected"));
                btn.classList.add("selected");
                selectedSize = size;
            });
        });
    }
     titleEl.textContent = productData.title;
     priceEl.textContent = productData.price;
     descriptionEl.textContent = productData.description;

     updateProductDisplay(selectedColor);

     addToCartBtn.addEventListener("click", () => {
        addToCart(productData, selectedColor, selectedSize);
     });
}

function addToCart(product, color, size) {
    let cart = JSON.parse(sessionStorage.getItem("cart")) || [];

    const existingItem = cart.find(item => item.id === product.id && item.color === color.name && item.size === size);

    if (existingItem) {
        existingItem.quantity += 1;
    }else {
        cart.push({
            id: product.id,
            title: product.title,
            price: product.price,
            Image: color.mainImage,
            color: color.name,
            size: size,
            quantity: 1
        });
    }
    
    sessionStorage.setItem("cart", JSON.stringify(cart));

    updateCartBadge();
}

function displayCart() {
    const cart = JSON.parse(sessionStorage.getItem("cart")) || [];

    const cartItemsContainer = document.querySelector(".cart-items");
    const subtotalEl =document.querySelector(".subtotal");
    const grandTotalEl = document.querySelector(".grand-total");

    cartItemsContainer.innerHTML = "";

    if (cart.length === 0) {
        cartItemsContainer.innerHTML = "<p>Your cart is empty.</p>";
        subtotalEl.textContent = "$0";
        grandTotalEl.textContent = "$0";
        return;
    }

    let subtotal = 0;

    cart.forEach((item, index) => {
        const itemTotal = parseFloat(item.price.replace("$", "")) * item.quantity;
        subtotal += itemTotal;

        const cartItem = document.createElement("div");
        cartItem.classList.add("cart-item");
        cartItem.innerHTML = `
        <div class="product">
            <img src="${item.image}">
            <div class="item-detail">
                <p>${item.title}</p>
                <div class="size-color-box">
                    <span class="size">${item.size}</span>
                    <span class="color">${item.color}</span>
                </div>
            </div>
        </div>
        <span class="price">${item.price}</span>
        <div class="quantity"><input type="number" value="${item.quantity}" min="1" data-index="${index}"></div>
        <span class="total-price">$${itemTotal}</span>
        <button class="remove" data-index="${index}"><i class="ri-close-line"></i></button>
        `;

        cartItemsContainer.appendChild(cartItem);
    });

    subtotalEl.textContent = `$${subtotal.toFixed(2)}`;
    grandTotalEl.textContent = `$${subtotal.toFixed(2)}`;

    removeCartItem();
    updateProductDisplay();
}

document.getElementById('checkout-btn')?.addEventListener('click', () => {
    window.location.href = "PaymentGateway.html";
});

function removeCartItem() {
    document.querySelectorAll(".remove").forEach(button => {
        button.addEventListener("click", function() {
            let cart =JSON.parse(sessionStorage.getItem("cart")) || [];
            const index = this.getAttribute("data-index");
            cart.splice(index, 1);
            sessionStorage.setItem("cart", JSON.stringify(cart));
            displayCart();
            updateCartBadge();
        });
    });
}

function updateCartQuantity() {
    document.querySelectorAll(".quantity input").forEach(input => {
        input.addEventListener("change", function() {
            let cart =JSON.parse(sessionStorage.getItem("cart")) || [];
            const index = this.getAttribute("data-index");
            cart[index].quantity = parseInt(this.value);
            sessionStorage.setItem("cart", JSON.stringify(cart));
            displayCart();
            updateCartBadge();
        });
    });
}

function updateCartBadge() {
    const cart =JSON.parse(sessionStorage.getItem("cart")) || [];
    const cartCount = cart.reduce((total, item) => total + item.quantity, 0);
    const badge = document.querySelector(".cart-item-count");

    if (badge) {
        if (cartCount > 0) {
            badge.textContent = cartCount;
            badge.style.display = "block";
        } else {
            badge.style.display = "none";
        }
    }
}

updateCartBadge();

// Product Detail Page Functionality
if (document.querySelector('.product-container')) {
    const productData = JSON.parse(sessionStorage.getItem('selectedProduct'));
    const mainImage = document.getElementById('main-product-image');
    const thumbnailContainer = document.querySelector('.thumbnail-container');
    const colorOptionsContainer = document.querySelector('.color-options .options-container');
    const sizeOptionsContainer = document.querySelector('.size-options .options-container');
    const quantityInput = document.querySelector('.quantity-input');
    const addToCartBtn = document.getElementById('add-to-cart');
    
    // Set basic product info
    document.querySelector('.product-title').textContent = productData.title;
    document.querySelector('.product-price').textContent = productData.price;
    document.querySelector('.product-description').textContent = productData.description;
    
    let selectedColor = productData.colors[0];
    let selectedSize = selectedColor.sizes[0];
    let selectedQuantity = 1;
    
    // Display color options
    productData.colors.forEach(color => {
        const colorOption = document.createElement('div');
        colorOption.className = `color-option ${color.name === selectedColor.name ? 'selected' : ''}`;
        colorOption.style.backgroundColor = getColorHex(color.name);
        colorOption.title = color.name;
        
        colorOption.addEventListener('click', () => {
            document.querySelectorAll('.color-option').forEach(opt => opt.classList.remove('selected'));
            colorOption.classList.add('selected');
            selectedColor = color;
            updateMainImage(color.mainImage);
            updateThumbnails(color);
            updateSizeOptions(color.sizes);
        });
        
        colorOptionsContainer.appendChild(colorOption);
    });
    
    // Display size options for initial color
    updateSizeOptions(selectedColor.sizes);
    
    // Set main image and thumbnails for initial color
    updateMainImage(selectedColor.mainImage);
    updateThumbnails(selectedColor);
    
    // Quantity controls
    document.querySelector('.quantity-btn.minus').addEventListener('click', () => {
        if (selectedQuantity > 1) {
            selectedQuantity--;
            quantityInput.value = selectedQuantity;
        }
    });
    
    document.querySelector('.quantity-btn.plus').addEventListener('click', () => {
        selectedQuantity++;
        quantityInput.value = selectedQuantity;
    });
    
    quantityInput.addEventListener('change', () => {
        selectedQuantity = Math.max(1, parseInt(quantityInput.value) || 1);
        quantityInput.value = selectedQuantity;
    });
    
    // Add to cart functionality
    addToCartBtn.addEventListener('click', () => {
        addToCart(productData, selectedColor, selectedSize, selectedQuantity);
        alert('Product added to cart!');
    });
    
    // Helper functions
    function updateMainImage(src) {
        mainImage.src = src;
        mainImage.alt = productData.title;
    }
    
    function updateThumbnails(color) {
        thumbnailContainer.innerHTML = '';
        const allImages = [color.mainImage, ...color.thumbnails];
        
        allImages.forEach((imgSrc, index) => {
            const thumb = document.createElement('img');
            thumb.src = imgSrc;
            thumb.alt = `${productData.title} - ${color.name} ${index + 1}`;
            thumb.className = index === 0 ? 'selected' : '';
            
            thumb.addEventListener('click', () => {
                document.querySelectorAll('.thumbnail-container img').forEach(t => t.classList.remove('selected'));
                thumb.classList.add('selected');
                updateMainImage(imgSrc);
            });
            
            thumbnailContainer.appendChild(thumb);
        });
    }
    
    function updateSizeOptions(sizes) {
        sizeOptionsContainer.innerHTML = '';
        selectedSize = sizes[0]; // Reset to first size
        
        sizes.forEach(size => {
            const sizeOption = document.createElement('div');
            sizeOption.className = `size-option ${size === selectedSize ? 'selected' : ''}`;
            sizeOption.textContent = size;
            
            sizeOption.addEventListener('click', () => {
                document.querySelectorAll('.size-option').forEach(opt => opt.classList.remove('selected'));
                sizeOption.classList.add('selected');
                selectedSize = size;
            });
            
            sizeOptionsContainer.appendChild(sizeOption);
        });
    }
    
    function getColorHex(colorName) {
        // Simple mapping of color names to hex values
        const colorMap = {
            'Grey': '#808080',
            'Blue': '#0000ff',
            'Red': '#ff0000',
            'White': '#ffffff',
            'Gold': '#ffd700',
            'Space Gray': '#717378',
            'Black': '#000000',
            'Light Blue': '#add8e6',
            'Green': '#008000',
            'Silver': '#c0c0c0'
        };
        return colorMap[colorName] || '#cccccc';
    }
}

// Cart and other existing functions remain the same...