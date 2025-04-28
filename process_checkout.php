<?php
// Start the session to access cart data
session_start();

// Include the database connection
include 'includes/db.php';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate form data
    $user_id = $_SESSION['user_id']; // Get user ID from session
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $address = htmlspecialchars($_POST['address']);
    $payment_method = htmlspecialchars($_POST['payment']);
    $transaction_uuid = bin2hex(random_bytes(16)); // Generate transaction UUID

    // Check if the cart is empty
    if (empty($_SESSION['cart'])) {
        header("Location: cart.php");
        exit();
    }

    // Calculate total price
    $total_price = 0;
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $query = "SELECT price FROM products WHERE id = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $product = $result->fetch_assoc();
            $total_price += $product['price'] * $quantity;
        } else {
            die("Product not found with ID: " . $product_id);
        }
        
        $stmt->close();
    }

    // Save the order to the database with user_id and status
    $query = "INSERT INTO orders (user_id, customer_name, customer_email, customer_address, payment_method, total_price, status, transaction_uuid) VALUES (?, ?, ?, ?, ?, ?, 'Pending', ?)";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("Prepare failed (order insertion): " . $conn->error);
    }
    
    $stmt->bind_param("isssdss", $user_id, $name, $email, $address, $payment_method, $total_price, $transaction_uuid);

    if ($stmt->execute()) {
        $order_id = $stmt->insert_id;

        // Save order items to the database
        foreach ($_SESSION['cart'] as $product_id => $quantity) {
            $query = "INSERT INTO order_items (order_id, product_id, quantity) VALUES (?, ?, ?)";
            $stmt_items = $conn->prepare($query);
            
            if (!$stmt_items) {
                die("Prepare failed (order items): " . $conn->error);
            }
            
            $stmt_items->bind_param("iii", $order_id, $product_id, $quantity);
            
            if (!$stmt_items->execute()) {
                die("Execute failed (order items): " . $stmt_items->error);
            } 
            
            $stmt_items->close();
        }

        // Clear the cart
        unset($_SESSION['cart']);

        // Redirect to order confirmation
        header("Location: order_confirmation.php?order_id=" . $order_id);
        exit();

    } else {
        die("Order insertion failed: " . $stmt->error);
    }

} else {
    // Redirect to cart page if form was not submitted
    header("Location: cart.php");
    exit();
}
?>
