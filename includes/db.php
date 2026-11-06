<?php
// Database connection parameters
$host = "localhost";
$username = "root";
$password = ""; // Default XAMPP password is empty
$database = "etudedoc";

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");
?>