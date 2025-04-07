<?php
// Start the session to access cart data
session_start();

// Include the database connection
include 'includes/db.php';

// Include the header
include 'includes/header.php';
?>

<!-- Cart Content -->
<div class="cart">
    <h2>CART LIST</h2>

    <?php
    // Check if the cart is empty
    if (empty($_SESSION['cart'])) {
        echo "<p>Your cart is empty.</p>";
    } else {
        // Initialize total price
        $total_price = 0;

        // Loop through each item in the cart
        foreach ($_SESSION['cart'] as $product_id => $quantity) {
            // Fetch product details from the database
            $query = "SELECT * FROM products WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $product = $result->fetch_assoc();

                // Calculate subtotal for this item
                $price = isset($product['price']) ? (float)$product['price'] : 0; // Ensure price is a float
                $quantity = (int)$quantity; // Ensure quantity is an integer
                $subtotal = $price * $quantity;
                $total_price += $subtotal;
                
                // Display the cart item
                echo '
                <div class="cart-item">
                    <img src="assets/images/' . htmlspecialchars($product['image']) . '" alt="' . htmlspecialchars($product['name']) . '">
                    <div class="item-details">
                        <h3>' . htmlspecialchars($product['name']) . '</h3>
                         <p>' . CURRENCY_SYMBOL . ' ' . number_format($product['price'], 2) . '</p>
                        <div class="quantity">
                            <label for="quantity' . $product['id'] . '">Quantity:</label>
                            <input type="number" id="quantity' . $product['id'] . '" name="quantity' . $product['id'] . '" value="' . $quantity . '" min="1">
                        </div>
                    </div>
                    <a href="remove_from_cart.php?id=' . $product['id'] . '" class="btn-remove">Remove</a>
                </div>';
            }
        }

        // Display the total price and checkout button
        echo '
        <p class="total-price">Total Price: ' . CURRENCY_SYMBOL . ' ' . number_format($total_price, 2) . '</p>
        <a href="checkout.php" class="btn-checkout">Proceed to Checkout</a>';
    }
    ?>
</div>

<?php
// Include the footer
include 'includes/footer.php';
?>