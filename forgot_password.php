<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="auth-container">
        <h2>Forgot Password</h2>
        <?php
            if (isset($_SESSION['error'])) {
                echo "<p class='error'>" . $_SESSION['error'] . "</p>";
                unset($_SESSION['error']);
            }
            if (isset($_SESSION['success'])) {
                echo "<p class='success'>" . $_SESSION['success'] . "</p>";
                unset($_SESSION['success']);
            }
        ?>
        <form action="send_reset_link.php" method="POST" style="max-width: 400px; margin: auto;">
    <input type="email" name="email" placeholder="Enter your email" required
           style="width: 100%; padding: 10px; margin-bottom: 15px; box-sizing: border-box;">

    <button type="submit" style="width: 100%; padding: 10px; background-color: #007bff; color: white; border: none; cursor: pointer;">
        Send Reset Link
    </button>
    
    <p style="text-align: center; margin-top: 10px;">
        <a href="login.php" style="color: #007bff; text-decoration: none; font-size: 14px;"
           onmouseover="this.style.textDecoration='none';" onmouseout="this.style.textDecoration='none';">
            Back to Login
        </a>
    </p>
</form>

    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
