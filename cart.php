<?php
session_start();
include 'includes/db.php';
include 'includes/header.php';
?>

<!-- Cart Content Wrapper -->
<div style="min-height: calc(100vh - 160px); padding: 30px 20px; box-sizing: border-box; max-width: 800px; margin: auto; font-family: Arial, sans-serif;">
    <h2 style="text-align: center; margin-bottom: 20px;">CART LIST</h2>

    <?php
    if (empty($_SESSION['cart'])) {
        echo "<p style='text-align: center; font-size: 18px; color: #666; margin-top: 50px;'>Your cart is empty.</p>";
    } else {
        $total_price = 0;

        foreach ($_SESSION['cart'] as $product_id => $quantity) {
            $query = "SELECT * FROM products WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $product = $result->fetch_assoc();

                $price = isset($product['price']) ? (float)$product['price'] : 0;
                $quantity = (int)$quantity;
                $subtotal = $price * $quantity;
                $total_price += $subtotal;

                echo '
                <div style="display: flex; align-items: center; border: 1px solid #ccc; border-radius: 5px; padding: 15px; margin-bottom: 15px;">
                    <img src="assets/images/' . htmlspecialchars($product['image']) . '" alt="' . htmlspecialchars($product['name']) . '" style="width: 80px; height: 80px; object-fit: cover; border-radius: 5px; margin-right: 15px;">
                    <div style="flex-grow: 1;">
                        <h3 style="margin: 0 0 5px;">' . htmlspecialchars($product['name']) . '</h3>
                        <p style="margin: 0 0 10px;">Price: ' . CURRENCY_SYMBOL . ' <span class="item-price">' . number_format($price, 2) . '</span></p>
                        <div>
                            <label>Quantity:</label>
                            <input 
                                type="number" 
                                class="quantity-input" 
                                data-price="' . $price . '" 
                                value="' . $quantity . '" 
                                min="1" 
                                style="width: 60px; padding: 5px; margin-left: 10px;">
                        </div>
                        <p style="margin-top: 10px;">Subtotal: ' . CURRENCY_SYMBOL . ' <span class="item-subtotal">' . number_format($subtotal, 2) . '</span></p>
                    </div>
                    <a href="remove_from_cart.php?id=' . $product['id'] . '" style="color: #fff; background-color: #dc3545; padding: 8px 12px; text-decoration: none; border-radius: 4px;">Remove</a>
                </div>';
            }
        }

        // Grand total
        echo '
        <p style="text-align: right; font-size: 18px; font-weight: bold;">
            Total Price: ' . CURRENCY_SYMBOL . ' <span id="grand-total">' . number_format($total_price, 2) . '</span>
        </p>
        <div style="text-align: right; margin-top: 20px;">
            <a href="checkout.php" style="background-color: #007bff; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 4px;">Proceed to Checkout</a>
        </div>';
    }
    ?>
</div>

<!-- JavaScript for live cart price updates -->
<script>
document.querySelectorAll('.quantity-input').forEach(function(input) {
    input.addEventListener('input', function () {
        const price = parseFloat(this.dataset.price);
        const quantity = parseInt(this.value) || 0;

        // Update subtotal
        const subtotalElement = this.closest('div').nextElementSibling;
        const newSubtotal = (price * quantity).toFixed(2);
        subtotalElement.querySelector('.item-subtotal').textContent = newSubtotal;

        // Update grand total
        let grandTotal = 0;
        document.querySelectorAll('.item-subtotal').forEach(function (sub) {
            grandTotal += parseFloat(sub.textContent);
        });
        document.getElementById('grand-total').textContent = grandTotal.toFixed(2);
    });
});
</script>

<?php include 'includes/footer.php'; ?>
