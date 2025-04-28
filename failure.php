<?php
include 'includes/header.php';
?>

<div class="checkout-container" style="max-width: 800px; margin: 50px auto; background-color: #ffffff; padding: 40px 60px; border-radius: 10px; box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1); text-align: center;">
    <h2 class="checkout-title" style="text-align: center; font-size: 28px; font-weight: 600; color: #333; margin-bottom: 20px;">Payment Failed</h2>
    <p style="font-size: 18px; color: #555; margin-bottom: 30px;">Unfortunately, your payment could not be processed. Please try again.</p>
    <a href="checkout.php" class="btn" style="display: inline-block; background-color: #dc3545; color: #fff; text-decoration: none; padding: 15px 30px; font-size: 16px; font-weight: 600; border-radius: 5px; transition: background-color 0.3s ease;">
        Retry Payment
    </a>
</div>

<?php include 'includes/footer.php'; ?>