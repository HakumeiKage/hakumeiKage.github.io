<?php
require_once 'config.php';

// Create admin user function
function createAdminUser($name, $email, $password, $is_admin = 1) {
    global $conn;
    
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, is_admin) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $name, $email, $hashed_password, $is_admin);
    
    return $stmt->execute();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    $errors = [];
    
    if (empty($name)) $errors[] = "Name is required";
    if (empty($email)) $errors[] = "Email is required";
    if (empty($password)) $errors[] = "Password is required";
    if ($password !== $confirm_password) $errors[] = "Passwords do not match";
    
    if (empty($errors)) {
        if (createAdminUser($name, $email, $password)) {
            $success = "Admin user created successfully!";
        } else {
            $errors[] = "Failed to create admin user";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin User</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet"/>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .admin-container {
            width: 100%;
            max-width: 500px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        
        .admin-header {
            background: #222;
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        
        .admin-header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .admin-header p {
            opacity: 0.8;
            font-size: 16px;
        }
        
        .admin-form {
            padding: 30px;
        }
        
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #444;
        }
        
        .form-group input {
            width: 100%;
            padding: 14px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .form-group input:focus {
            border-color: #2575fc;
            box-shadow: 0 0 0 3px rgba(37, 117, 252, 0.2);
            outline: none;
        }
        
        .password-strength {
            height: 5px;
            background: #f0f0f0;
            border-radius: 5px;
            margin-top: 8px;
            overflow: hidden;
        }
        
        .strength-meter {
            height: 100%;
            width: 0;
            background: #e74c3c;
            transition: width 0.3s;
        }
        
        .btn {
            width: 100%;
            padding: 15px;
            background: #e35f26;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn:hover {
            background: #c14f1f;
            transform: translateY(-2px);
        }
        
        .btn:active {
            transform: translateY(1px);
        }
        
        .message {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .error {
            background: #ffebee;
            color: #c62828;
        }
        
        .success {
            background: #e8f5e9;
            color: #2e7d32;
        }
        
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            border-radius: 0 8px 8px 0;
            margin-bottom: 25px;
        }
        
        .info-box h3 {
            margin-bottom: 10px;
            color: #1976d2;
        }
        
        .info-box ul {
            padding-left: 20px;
        }
        
        .info-box li {
            margin-bottom: 8px;
            color: #333;
        }
        
        @media (max-width: 600px) {
            .admin-container {
                border-radius: 15px;
            }
            
            .admin-header {
                padding: 25px 15px;
            }
            
            .admin-form {
                padding: 25px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1><i class="ri-admin-line"></i> Create Admin Account</h1>
            <p>Set up your first administrator account</p>
        </div>
        
        <div class="admin-form">
            <?php if (!empty($errors)): ?>
                <div class="message error">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($success)): ?>
                <div class="message success">
                    <p><?php echo $success; ?></p>
                    <p><a href="index.php" style="color: #2e7d32; text-decoration: underline;">Go to Homepage</a></p>
                </div>
            <?php else: ?>
                <div class="info-box">
                    <h3><i class="ri-information-line"></i> Admin Privileges</h3>
                    <ul>
                        <li>Access to all administrative features</li>
                        <li>Manage products and categories</li>
                        <li>View and process orders</li>
                        <li>Access to analytics and reports</li>
                    </ul>
                </div>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" placeholder="Enter your full name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" placeholder="Enter your email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Create a strong password" required>
                        <div class="password-strength">
                            <div class="strength-meter" id="strengthMeter"></div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
                    </div>
                    
                    <button type="submit" class="btn">Create Admin Account</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Password strength indicator
        const passwordInput = document.getElementById('password');
        const strengthMeter = document.getElementById('strengthMeter');
        
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            // Length check
            if (password.length >= 8) strength += 25;
            
            // Lowercase letter check
            if (/[a-z]/.test(password)) strength += 25;
            
            // Uppercase letter check
            if (/[A-Z]/.test(password)) strength += 25;
            
            // Number or special character check
            if (/[0-9!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) strength += 25;
            
            // Update strength meter
            strengthMeter.style.width = strength + '%';
            
            // Update colors
            if (strength < 50) {
                strengthMeter.style.backgroundColor = '#e74c3c';
            } else if (strength < 75) {
                strengthMeter.style.backgroundColor = '#f39c12';
            } else {
                strengthMeter.style.backgroundColor = '#2ecc71';
            }
        });
    </script>
</body>
</html>