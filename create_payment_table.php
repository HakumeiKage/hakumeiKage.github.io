<?php
// create_payment_table.php
require_once 'config.php';

$sql = "CREATE TABLE IF NOT EXISTS payment_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    card_name VARCHAR(255) NOT NULL,
    card_number VARCHAR(20) NOT NULL,
    expiry_month VARCHAR(10) NOT NULL,
    expiry_year VARCHAR(4) NOT NULL,
    cvv VARCHAR(4) NOT NULL,
    billing_address TEXT NOT NULL,
    city VARCHAR(100) NOT NULL,
    province VARCHAR(100) NOT NULL,
    zip_code VARCHAR(10) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
)";

if ($conn->query($sql) {
    echo "Payment details table created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}
?>