<?php
session_start();
require_once 'config.php';

if (isset($_POST['register_btn'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['alerts'][] = [
            'type' => 'error',
            'message' => 'Email is already registered!'
        ];
        $_SESSION['active_form'] = 'register';
    } else {
        $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $password);
        $stmt->execute();
        
        $_SESSION['alerts'][] = [
            'type' => 'success',
            'message' => 'Registration successful'
        ];
        $_SESSION['active_form'] = 'login';
    }

    header('Location: index.php');
    exit();
}

if (isset($_POST['login_btn'])) {
    $email = $conn->real_escape_string($_POST['email']);
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->num_rows > 0 ? $result->fetch_assoc() : null;

    if ($user && password_verify($_POST['password'], $user['password'])) {
        $_SESSION['name'] = $user['name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['is_admin'] = $user['is_admin']; // ADD THIS LINE
        
        $_SESSION['alerts'][] = [
            'type' => 'success',
            'message' => 'Login successful'
        ];
    } else {
        $_SESSION['alerts'][] = [
            'type' => 'error',
            'message' => 'Incorrect email or password!'
        ];
        $_SESSION['active_form'] = 'login';
    }

    header('Location: index.php');
    exit();
}
?>