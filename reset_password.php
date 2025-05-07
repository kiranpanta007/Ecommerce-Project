<?php
session_start();
include 'includes/db.php';

$token = $_GET['token'] ?? '';

if (empty($token) || !ctype_xdigit($token)) {
    $_SESSION['error'] = "Invalid token.";
    header("Location: forgot_password.php");
    exit();
}

$stmt = $conn->prepare("SELECT id, reset_expiry FROM users WHERE reset_token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows == 0) {
    $_SESSION['error'] = "Invalid or expired token.";
    header("Location: forgot_password.php");
    exit();
}

$stmt->bind_result($user_id, $expiry);
$stmt->fetch();

if (strtotime($expiry) < time()) {
    $_SESSION['error'] = "Token expired.";
    header("Location: forgot_password.php");
    exit();
}

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="auth-container">
        <h2>Reset Password</h2>
        <form action="update_password.php" method="POST">
            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="password" name="new_password" placeholder="New Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <button type="submit">Update Password</button>
        </form>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
