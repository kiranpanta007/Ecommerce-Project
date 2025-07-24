<?php
// test_connection.php
require_once 'includes/db.php';

// Test query
$result = $conn->query("SELECT 1");
if ($result) {
    echo "Database connection is working properly!";
} else {
    echo "Database connection error: " . $conn->error;
}

// Test session and CSRF
echo "<br>CSRF Token: " . $_SESSION['csrf_token'];
?>