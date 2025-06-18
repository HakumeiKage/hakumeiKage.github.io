<?php
session_start();
require_once 'config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Verify admin status (assuming you have an 'is_admin' column in users table)
$is_admin = false;
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT is_admin FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $is_admin = (bool)$user['is_admin'];
    }
}

if (!$is_admin) {
    header('Location: dashboard.php');
    exit();
}

// Handle form submissions
$errors = [];
$success = '';

// Add product functionality
if (isset($_POST['add_product'])) {
    $title = trim($_POST['title']);
    $price = trim($_POST['price']);
    $description = trim($_POST['description']);
    
    // Validate inputs
    if (empty($title)) {
        $errors[] = "Product title is required";
    }
    if (empty($price) || !is_numeric($price) || $price <= 0) {
        $errors[] = "Valid price is required";
    }
    if (empty($description)) {
        $errors[] = "Description is required";
    }
    
    // Handle image upload
    $image_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        // Create directory if doesn't exist
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        // Generate unique filename
        $ext = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $ext;
        $target_file = $target_dir . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_path = $target_file;
        } else {
            $errors[] = "Failed to upload image";
        }
    } else {
        $errors[] = "Product image is required";
    }
    
    // Insert product if no errors
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO products (title, price, description, image_path) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sdss", $title, $price, $description, $image_path);
        
        if ($stmt->execute()) {
            $success = "Product added successfully!";
        } else {
            $errors[] = "Error adding product: " . $stmt->error;
        }
    }
}

// Update product functionality
if (isset($_POST['update_product'])) {
    $id = (int)$_POST['id'];
    $title = trim($_POST['title']);
    $price = trim($_POST['price']);
    $description = trim($_POST['description']);
    
    // Validate inputs
    if (empty($title)) {
        $errors[] = "Product title is required";
    }
    if (empty($price) || !is_numeric($price) || $price <= 0) {
        $errors[] = "Valid price is required";
    }
    if (empty($description)) {
        $errors[] = "Description is required";
    }
    
    // Handle image upload if provided
    $image_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        // Create directory if doesn't exist
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        // Generate unique filename
        $ext = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $ext;
        $target_file = $target_dir . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_path = $target_file;
        } else {
            $errors[] = "Failed to upload image";
        }
    }
    
    // Update product if no errors
    if (empty($errors)) {
        if (!empty($image_path)) {
            // Update with new image
            $stmt = $conn->prepare("UPDATE products SET title=?, price=?, description=?, image_path=? WHERE id=?");
            $stmt->bind_param("sdssi", $title, $price, $description, $image_path, $id);
        } else {
            // Update without changing image
            $stmt = $conn->prepare("UPDATE products SET title=?, price=?, description=? WHERE id=?");
            $stmt->bind_param("sdsi", $title, $price, $description, $id);
        }
        
        if ($stmt->execute()) {
            $success = "Product updated successfully!";
        } else {
            $errors[] = "Error updating product: " . $stmt->error;
        }
    }
}

// Delete product functionality
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // First, get the image path to delete the file
    $result = $conn->query("SELECT image_path FROM products WHERE id = $id");
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        if (file_exists($product['image_path'])) {
            unlink($product['image_path']);
        }
    }
    
    // Then delete the product
    if ($conn->query("DELETE FROM products WHERE id = $id")) {
        $success = "Product deleted successfully!";
    } else {
        $errors[] = "Error deleting product: " . $conn->error;
    }
}

// Get products for display
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 5;
$offset = ($page - 1) * $perPage;

$products = $conn->query("SELECT * FROM products LIMIT $offset, $perPage");

// Get total products for pagination
$totalProducts = $conn->query("SELECT COUNT(*) FROM products")->fetch_row()[0];
$totalPages = ceil($totalProducts / $perPage);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet"/>
    <link rel="stylesheet" href="Wstyle.css">
    <style>
        .product-management {
            padding: 120px 9% 30px;
        }
        .form-container, .products-list {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .form-group textarea {
            height: 100px;
        }
        .btn {
            padding: 10px 20px;
            background: #222;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #e35f26;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background: #f5f5f5;
        }
        .action-links a {
            margin-right: 10px;
            color: #333;
            text-decoration: none;
        }
        .action-links a:hover {
            text-decoration: underline;
        }
        .error {
            color: #ff0000;
            background: #ffeeee;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .success {
            color: #0abf30;
            background: #eeffee;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            gap: 5px;
        }
        .pagination a, .pagination span {
            display: inline-block;
            padding: 8px 16px;
            background: #f5f5f5;
            border-radius: 4px;
            text-decoration: none;
            color: #333;
        }
        .pagination a:hover {
            background: #ddd;
        }
        .pagination .current {
            background: #e35f26;
            color: white;
        }
        .image-preview {
            max-width: 200px;
            margin-top: 10px;
            display: none;
        }
        .image-preview.show {
            display: block;
        }
    </style>
</head>
<body>
    <nav>
        <a href="dashboard.php" class="logo">Admin Panel</a>
        <div class="user-info">
            <span>Welcome, <?php echo $_SESSION['name']; ?></span>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <div class="product-management">
        <h1>Product Management</h1>
        
        <?php 
        // Display messages
        if (!empty($errors)) {
            foreach ($errors as $error) {
                echo '<div class="error">' . $error . '</div>';
            }
        }
        if (!empty($success)) {
            echo '<div class="success">' . $success . '</div>';
        }
        ?>
        
        <div class="form-container">
            <h2><?php echo isset($_GET['edit']) ? 'Edit Product' : 'Add New Product'; ?></h2>
            <form action="product_management.php" method="POST" enctype="multipart/form-data">
                <?php
                $edit_mode = false;
                $edit_product = null;
                
                if (isset($_GET['edit'])) {
                    $edit_mode = true;
                    $id = (int)$_GET['edit'];
                    $result = $conn->query("SELECT * FROM products WHERE id = $id");
                    if ($result->num_rows > 0) {
                        $edit_product = $result->fetch_assoc();
                    }
                }
                
                if ($edit_mode && $edit_product) {
                    echo '<input type="hidden" name="id" value="' . $edit_product['id'] . '">';
                }
                ?>
                <div class="form-group">
                    <label for="title">Product Title</label>
                    <input type="text" id="title" name="title" required 
                           value="<?php echo $edit_mode ? htmlspecialchars($edit_product['title']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="price">Price</label>
                    <input type="number" step="0.01" id="price" name="price" required 
                           value="<?php echo $edit_mode ? htmlspecialchars($edit_product['price']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" required><?php 
                        echo $edit_mode ? htmlspecialchars($edit_product['description']) : ''; 
                    ?></textarea>
                </div>
                <div class="form-group">
                    <label for="image">Product Image</label>
                    <input type="file" id="image" name="image" onchange="previewImage(event)" 
                           <?php if (!$edit_mode) echo 'required'; ?>>
                    <?php if ($edit_mode && !empty($edit_product['image_path'])): ?>
                        <img src="<?php echo $edit_product['image_path']; ?>" 
                             class="image-preview show" id="imagePreview">
                    <?php else: ?>
                        <img src="" class="image-preview" id="imagePreview">
                    <?php endif; ?>
                </div>
                <button type="submit" name="<?php echo $edit_mode ? 'update_product' : 'add_product'; ?>" class="btn">
                    <?php echo $edit_mode ? 'Update Product' : 'Add Product'; ?>
                </button>
                <?php if ($edit_mode): ?>
                    <a href="product_management.php" class="btn" style="background: #666; margin-left: 10px;">
                        Cancel
                    </a>
                <?php endif; ?>
            </form>
        </div>
        
        <div class="products-list">
            <h2>Product List</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Price</th>
                        <th>Description</th>
                        <th>Image</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($products->num_rows > 0): ?>
                        <?php while ($product = $products->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $product['id']; ?></td>
                                <td><?php echo htmlspecialchars($product['title']); ?></td>
                                <td>$<?php echo number_format($product['price'], 2); ?></td>
                                <td><?php echo substr(htmlspecialchars($product['description']), 0, 50) . '...'; ?></td>
                                <td>
                                    <?php if (!empty($product['image_path'])): ?>
                                        <img src="<?php echo $product['image_path']; ?>" style="width: 60px; height: auto;">
                                    <?php endif; ?>
                                </td>
                                <td class="action-links">
                                    <a href="product_management.php?edit=<?php echo $product['id']; ?>">Edit</a>
                                    <a href="product_management.php?delete=<?php echo $product['id']; ?>" 
                                       onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center;">No products found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="current"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="product_management.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function previewImage(event) {
            const preview = document.getElementById('imagePreview');
            if (event.target.files.length > 0) {
                const src = URL.createObjectURL(event.target.files[0]);
                preview.src = src;
                preview.classList.add('show');
            }
        }
    </script>
</body>
</html>