<?php
session_start();
include 'includes/db.php';
include 'includes/header.php';

?>

<div style="min-height: calc(100vh - 160px); padding: 30px 20px; max-width: 800px; margin: auto; font-family: Arial, sans-serif;">
    <h2 style="text-align: center; margin-bottom: 20px;">CART LIST</h2>

    <?php if (empty($_SESSION['cart'])): ?>
        <p style="text-align: center; font-size: 18px; color: #666; margin-top: 50px;">Your cart is empty.</p>
    <?php else: ?>
        <form action="update_cart.php" method="POST">
            <?php
            $total_price = 0;
            foreach ($_SESSION['cart'] as $product_id => $item) {
                // $item = ['id'=>..., 'name'=>..., 'price'=>..., 'quantity'=>...]
                $stmt = $conn->prepare("SELECT stock, image FROM products WHERE id = ?");
                $stmt->bind_param("i", $product_id);
                $stmt->execute();
                $product_db = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                $price = floatval($item['price']);
                $quantity = intval($item['quantity']);
                $stock = intval($product_db['stock']);
                $image = htmlspecialchars($product_db['image'] ?? 'default.png');

                // Ensure quantity does not exceed stock for display
                $display_quantity = min($quantity, $stock);
                $subtotal = $price * $display_quantity;
                $total_price += $subtotal;
                ?>
                <div style="display: flex; align-items: center; border: 1px solid #ccc; border-radius: 5px; padding: 15px; margin-bottom: 15px;">
                    <img src="assets/images/<?= $image ?>" alt="<?= htmlspecialchars($item['name']); ?>" style="width: 80px; height: 80px; object-fit: cover; border-radius: 5px; margin-right: 15px;">
                    <div style="flex-grow: 1;">
                        <h3 style="margin: 0 0 5px;"><?= htmlspecialchars($item['name']); ?></h3>
                        <p style="margin: 0 0 10px;">Price: <?= CURRENCY_SYMBOL ?> <span class="item-price"><?= number_format($price, 2); ?></span></p>
                        <label for="qty_<?= $product_id ?>">Quantity:</label>
                        <input 
                            type="number" 
                            id="qty_<?= $product_id ?>" 
                            name="quantities[<?= $product_id ?>]" 
                            class="quantity-input" 
                            data-price="<?= $price ?>" 
                            value="<?= $display_quantity ?>" 
                            min="1" 
                            max="<?= $stock ?>" 
                            style="width: 60px; padding: 5px; margin-left: 10px;"
                        >
                        <?php if ($quantity > $stock): ?>
                            <p style="color: red; font-size: 12px;">Quantity adjusted to available stock (<?= $stock ?>).</p>
                        <?php endif; ?>
                        <p style="margin-top: 10px;">Subtotal: <?= CURRENCY_SYMBOL ?> <span class="item-subtotal"><?= number_format($subtotal, 2); ?></span></p>
                    </div>
                    <a href="remove_from_cart.php?id=<?= $product_id ?>" style="color: #fff; background-color: #dc3545; padding: 8px 12px; text-decoration: none; border-radius: 4px;">Remove</a>
                </div>
                <?php
            }
            ?>
            <p style="text-align: right; font-size: 18px; font-weight: bold;">
                Total Price: <?= CURRENCY_SYMBOL ?> <span id="grand-total"><?= number_format($total_price, 2) ?></span>
            </p>
            <div style="text-align: right; margin-top: 20px;">
                <button type="submit" style="background-color: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">Update Cart</button>
                <a href="checkout.php" style="background-color: #007bff; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 4px; margin-left: 10px;">Proceed to Checkout</a>
            </div>
        </form>

        <script>
        document.querySelectorAll('.quantity-input').forEach(function(input) {
            input.addEventListener('input', function () {
                const price = parseFloat(this.dataset.price);
                let quantity = parseInt(this.value) || 0;

                // Clamp quantity within min/max
                const min = parseInt(this.min);
                const max = parseInt(this.max);
                if (quantity < min) quantity = min;
                if (quantity > max) quantity = max;
                this.value = quantity;

                // Update subtotal
                const subtotalElement = this.closest('div').querySelector('.item-subtotal');
                const newSubtotal = (price * quantity).toFixed(2);
                subtotalElement.textContent = newSubtotal;

                // Update grand total
                let grandTotal = 0;
                document.querySelectorAll('.item-subtotal').forEach(function(sub) {
                    grandTotal += parseFloat(sub.textContent);
                });
                document.getElementById('grand-total').textContent = grandTotal.toFixed(2);
            });
        });
        </script>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
