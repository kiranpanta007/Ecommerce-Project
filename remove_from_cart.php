<?php
session_start();

// Check if product ID is provided
if (isset($_GET['id'])) {
    $product_id = intval($_GET['id']); // Sanitize the input

    // Remove product from cart
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
    }

    // Redirect back to the cart page
    header("Location: cart.php");
    exit();
} else {
    // No product ID provided
    die("Invalid product ID.");
}
?>