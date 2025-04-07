<?php
session_start();
include 'includes/db.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format.";
        header("Location: http://localhost/ecommerce_project/forgot_password.php");
        exit();
    }

    // Check if the email exists in the database
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    if (!$stmt) {
        $_SESSION['error'] = "Database error: " . $conn->error;
        header("Location: http://localhost/ecommerce_project/forgot_password.php");
        exit();
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $_SESSION['error'] = "No account found with that email.";
        header("Location: http://localhost/ecommerce_project/forgot_password.php");
        exit();
    }

    // Fetch user ID
    $stmt->bind_result($user_id);
    $stmt->fetch();

    // Generate reset token and expiry
    $token = bin2hex(random_bytes(16));
    $expiry = date("Y-m-d H:i:s", strtotime("+1 hour")); // Valid for 1 hour

    // Update the database
    $stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_expiry = ? WHERE id = ?");
    if (!$stmt) {
        $_SESSION['error'] = "Database error: " . $conn->error;
        header("Location: http://localhost/ecommerce_project/forgot_password.php");
        exit();
    }

    $stmt->bind_param("ssi", $token, $expiry, $user_id);
    
    if ($stmt->execute()) {
        $reset_link = "http://localhost/ecommerce_project/reset_password.php?token=" . $token;

        $to = $email;
        $subject = "Password Reset Request";
        $message = "Hello,\n\nClick the link to reset your password:\n$reset_link\n\nIgnore this email if you didn't request it.\n\nThanks.";
        
        $headers = "From: kiranpanta9846@gmail.com\r\n";
        $headers .= "Reply-To: kiranpanta9846@gmail.com\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        if (mail($to, $subject, $message, $headers)) {
            $_SESSION['success'] = "Password reset link sent to your email.";
        } else {
            $_SESSION['error'] = "Failed to send email. Please try again.";
        }

        header("Location: http://localhost/ecommerce_project/forgot_password.php");
        exit();

    } else {
        $_SESSION['error'] = "Failed to update database: " . $stmt->error;
        header("Location: http://localhost/ecommerce_project/forgot_password.php");
        exit();
    }
} else {
    $_SESSION['error'] = "Invalid request method.";
    header("Location: http://localhost/ecommerce_project/forgot_password.php");
    exit();
}
?>
