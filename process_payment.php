<?php
session_start();
require_once 'config.php';

// Basic validation
$required = ['fullname', 'email', 'address', 'city', 'province', 'zip', 
             'cardname', 'cardnumber', 'expmonth', 'expyear', 'cvv'];
$errors = [];

foreach ($required as $field) {
    if (empty($_POST[$field])) {
        $errors[] = "$field is required";
    }
}

if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email format";
}

if (!preg_match('/^\d{16}$/', $_POST['cardnumber'])) {
    $errors[] = "Card number must be 16 digits";
}

if (!preg_match('/^\d{3,4}$/', $_POST['cvv'])) {
    $errors[] = "CVV must be 3 or 4 digits";
}

if (count($errors) > 0) {
    $_SESSION['payment_errors'] = $errors;
    header('Location: paymentGateway.php');
    exit();
}

// Process payment if no errors
try {
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $cartData = json_decode($_POST['cart_data'], true);
    
    // Calculate total
    $total = 0;
    foreach ($cartData as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    
    // Create order record
    $shipping_address = $_POST['address'] . ', ' . $_POST['city'] . ', ' . 
                       $_POST['province'] . ' ' . $_POST['zip'];
    
    $stmt = $conn->prepare("INSERT INTO orders 
        (user_id, customer_name, customer_email, total_amount, shipping_address) 
        VALUES (?, ?, ?, ?, ?)");
    
    $stmt->bind_param("issss",
        $userId,
        $_POST['fullname'],
        $_POST['email'],
        $total,
        $shipping_address
    );
    
    if ($stmt->execute()) {
        $orderId = $conn->insert_id;
        
        // Save order items
        foreach ($cartData as $item) {
            $itemStmt = $conn->prepare("INSERT INTO order_items 
                (order_id, product_id, quantity, price) 
                VALUES (?, ?, ?, ?)");
            
            $itemStmt->bind_param("iiid", $orderId, $item['id'], $item['quantity'], $item['price']);
            $itemStmt->execute();
        }
        
        // Clear cart
        unset($_SESSION['cart']);
        
        // Redirect to success page
        $_SESSION['order_id'] = $orderId;
        header('Location: order_success.php');
        exit();
    } else {
        throw new Exception("Failed to create order");
    }
} catch (Exception $e) {
    $_SESSION['payment_errors'] = ["Payment processing failed: " . $e->getMessage()];
    header('Location: paymentGateway.php');
    exit();
}
?>