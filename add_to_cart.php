<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/stock_functions.php'; // include your stock helper functions

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id']);
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

    // Validate inputs
    if ($product_id <= 0 || $quantity <= 0) {
        $_SESSION['error'] = "Invalid product or quantity.";
        header("Location: product.php?id=$product_id");
        exit();
    }

    // Fetch product details including current stock
    $stmt = $conn->prepare("SELECT id, name, price, stock FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$product) {
        $_SESSION['error'] = "Product not found.";
        header("Location: shop.php");
        exit();
    }

    // Check if requested quantity is available
    if ($quantity > $product['stock']) {
        $_SESSION['error'] = "Only {$product['stock']} item(s) available in stock.";
        header("Location: product.php?id=$product_id");
        exit();
    }

    // Initialize cart if not exists
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // If product already in cart, calculate total quantity to ensure no oversell
    $newQuantity = $quantity;
    if (isset($_SESSION['cart'][$product_id])) {
        $newQuantity += $_SESSION['cart'][$product_id]['quantity'];
        if ($newQuantity > $product['stock']) {
            $_SESSION['error'] = "You already have {$_SESSION['cart'][$product_id]['quantity']} in cart. Only {$product['stock']} item(s) available.";
            header("Location: product.php?id=$product_id");
            exit();
        }
    }

    // Add/update cart item
    $_SESSION['cart'][$product_id] = [
        'id' => $product_id,
        'name' => $product['name'],
        'price' => $product['price'],
        'quantity' => $newQuantity
    ];

    $_SESSION['success'] = "Product added to cart!";
    header("Location: product.php?id=$product_id");
    exit();
}

// Default fallback
$_SESSION['error'] = "Failed to add product to cart.";
header("Location: shop.php");
exit();
