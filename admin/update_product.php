<?php
session_start();
require_once '../includes/db.php'; // Correct path

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request method.");
}

$product_id = intval($_POST['product_id']);
$name = trim($_POST['name']);
$price = trim($_POST['price']);
$image = trim($_POST['image']);
$stock = isset($_POST['stock']) ? intval($_POST['stock']) : 0;

// Input validation
if (empty($name) || empty($price) || $price <= 0 || $product_id <= 0 || $stock < 0) {
    $_SESSION['error'] = "Please fill in all required fields correctly.";
    header("Location: edit_product.php?id=" . $product_id);
    exit();
}

// Prepare the SQL statement including stock
$stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, image = ?, stock = ? WHERE id = ?");
if (!$stmt) {
    $_SESSION['error'] = "Prepare failed: " . $conn->error;
    header("Location: edit_product.php?id=" . $product_id);
    exit();
}

// Updated binding (s = string, d = double/float, i = int)
$stmt->bind_param("sdsii", $name, $price, $image, $stock, $product_id);

// Execute and handle the result
if ($stmt->execute()) {
    $_SESSION['success'] = "Product updated successfully!";
    header("Location: products.php");
    exit();
} else {
    $_SESSION['error'] = "Error updating product: " . $stmt->error;
    header("Location: edit_product.php?id=" . $product_id);
    exit();
}

$stmt->close();
$conn->close();
?>
