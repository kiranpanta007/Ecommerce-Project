<?php
session_start();

// Check if product ID is provided
if (isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']); // Sanitize the input

    // Initialize cart if it doesn't exist
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Add product to cart
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += 1; // Increment quantity if product already exists
    } else {
        $_SESSION['cart'][$product_id] = 1; // Add new product to cart
    }

    // Redirect back to the product page
    header("Location: product.php?id=" . $product_id);
    exit();
} else {
    // No product ID provided
    die("Invalid product ID.");
}
?>
