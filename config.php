<?php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'users_db';  // Use your existing database name

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die('Connection failed: '. $conn->connect_error);
}
?>