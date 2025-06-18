function filterProducts() {
    const category = document.getElementById('category').value;
    const sort = document.getElementById('sort').value;
    
    let url = 'purchase.php?';
    if (category !== 'all') url += `category=${category}&`;
    if (sort !== 'default') url += `sort=${sort}`;
    
    window.location.href = url;
}