<?php
// includes/db.php
$servername = "localhost";
$username = "root";
$password = "";
$database = "project_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// Currency symbol
if (!defined('CURRENCY_SYMBOL')) {
    define('CURRENCY_SYMBOL', 'NRS');
}

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CSRF token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Define helper functions only once
if (!function_exists('escape_data')) {
    function escape_data($data, $conn) {
        return $conn->real_escape_string(trim($data));
    }
}

if (!function_exists('execute_query')) {
    function execute_query($conn, $sql, $params = [], $types = '') {
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            return false;
        }

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        return $stmt;
    }
}


