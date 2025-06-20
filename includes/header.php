<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'includes/db.php'; // Database connection
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyShop</title>
    <link rel="stylesheet" href="styles/style.css">
    
    <style>
        /* (Your existing styles remain the same) */
    </style>
</head>
<body>

<header>
    <div class="container">
        <div class="logo">
            <h1><a href="index.php">Mero Shopping</a></h1>
        </div>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="shop.php">Shop</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>
        </nav>
        <div class="search-bar">
            <input type="text" id="search-input" placeholder="Search products..." oninput="fetchSuggestions()" onkeydown="handleKeyDown(event)">
            <div id="suggestion-dropdown" class="suggestion-dropdown"></div>
        </div>

        <div class="user-links">
            <?php if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])): ?>
                <a href="order_history.php">Order History</a>
                <a href="profile.php">Profile</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
        </div>
        <div class="cart">
            <a href="cart.php">🛒 Cart (<?php
                $total_items = 0;
                if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
                    foreach ($_SESSION['cart'] as $item) {
                        if (is_array($item) && isset($item['quantity'])) {
                            $total_items += (int)$item['quantity'];
                        }
                        elseif (is_numeric($item)) {
                            $total_items += (int)$item;
                        }
                    }
                }
                echo $total_items;
            ?>)</a>
        </div>
    </div>
</header>

<script>
// (Your existing JavaScript remains the same)
</script>

</body>
</html>