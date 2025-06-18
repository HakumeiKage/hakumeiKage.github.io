<?php
session_start();
require_once 'db.php';

$response = ['success' => false, 'cart_count' => 0];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $product_id = (int)$data['id'];
    $quantity = (int)$data['quantity'];
    
    // Get product details
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        
        // Initialize cart if not exists
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        // Check if product already in cart
        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] == $product_id) {
                $item['quantity'] += $quantity;
                $found = true;
                break;
            }
        }
        
        // Add new item if not found
        if (!$found) {
            $_SESSION['cart'][] = [
                'id' => $product_id,
                'title' => $product['title'],
                'price' => $product['price'],
                'image' => $product['image_path'],
                'quantity' => $quantity
            ];
        }
        
        // Calculate total cart count
        $cart_count = 0;
        foreach ($_SESSION['cart'] as $item) {
            $cart_count += $item['quantity'];
        }
        
        $response = [
            'success' => true,
            'cart_count' => $cart_count
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>