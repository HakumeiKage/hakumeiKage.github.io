<?php
session_start();

$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    switch ($data['action']) {
        case 'update_quantity':
            $index = (int)$data['index'];
            if (isset($_SESSION['cart'][$index])) {
                if (isset($data['newQuantity'])) {
                    $_SESSION['cart'][$index]['quantity'] = max(1, (int)$data['newQuantity']);
                } else {
                    $change = (int)$data['change'];
                    $_SESSION['cart'][$index]['quantity'] = max(1, $_SESSION['cart'][$index]['quantity'] + $change);
                }
                $response['success'] = true;
            }
            break;
            
        case 'remove':
            $index = (int)$data['index'];
            if (isset($_SESSION['cart'][$index])) {
                array_splice($_SESSION['cart'], $index, 1);
                $response['success'] = true;
            }
            break;
            
        case 'clear':
            $_SESSION['cart'] = [];
            $response['success'] = true;
            break;
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>