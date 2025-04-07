<?php
session_start();
include 'includes/db.php';

// Check if the coupon code is set
if (isset($_POST['coupon_code'])) {
    $coupon_code = $_POST['coupon_code'];

    // Check if the coupon exists and is active
    $query = "SELECT * FROM coupons WHERE code = ? AND active = 1 AND expiration_date > NOW()";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $coupon_code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $coupon = $result->fetch_assoc();
        
        // Validate the usage limit
        if ($coupon['used_count'] < $coupon['usage_limit']) {
            // Save coupon information in session for later use
            $_SESSION['coupon'] = $coupon;
            $_SESSION['coupon_code'] = $coupon_code;
            $_SESSION['coupon_discount'] = $coupon['discount_value'];
            $_SESSION['coupon_type'] = $coupon['discount_type'];  // percentage or fixed

            // Redirect back to checkout page with success message
            header("Location: checkout.php?coupon_success=true");
            exit();
        } else {
            $_SESSION['coupon_error'] = "This coupon has reached its usage limit.";
        }
    } else {
        $_SESSION['coupon_error'] = "Invalid or expired coupon code.";
    }

    // Close statement
    $stmt->close();
}

// Redirect to checkout page if no coupon code was applied
header("Location: checkout.php");
exit();
?>
