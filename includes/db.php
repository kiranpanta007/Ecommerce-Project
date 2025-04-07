<?php
$servername = "localhost"; 
$username = "root";        
$password = "";            
$database = "project_db";   

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}


// Define currency symbol if not already defined
if (!defined('CURRENCY_SYMBOL')) {
    define('CURRENCY_SYMBOL', 'NRS');
}

?>