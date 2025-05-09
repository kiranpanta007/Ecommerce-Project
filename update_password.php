<?php
session_start();
include 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = "Invalid CSRF token.";
        header("Location: forgot_password.php");
        exit();
    }
    unset($_SESSION['csrf_token']);

    $user_id = $_POST['user_id'];
    $token = $_POST['token'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $_SESSION['error'] = "Passwords don't match.";
        header("Location: reset_password.php?token=" . urlencode($token));
        exit();
    }

    if (strlen($new_password) < 8 || !preg_match('/[A-Z]/', $new_password) ||
        !preg_match('/[0-9]/', $new_password) || !preg_match('/[\W]/', $new_password)) {
        $_SESSION['error'] = "Password must be strong.";
        header("Location: reset_password.php?token=" . urlencode($token));
        exit();
    }

    $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expiry = NULL WHERE id = ?");
    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
    $stmt->bind_param("si", $hashed_password, $user_id);

    if ($stmt->execute()) {
        session_regenerate_id(true);
        $_SESSION['success'] = "Password updated. Log in.";
        header("Location: login.php");
        exit();
    } else {
        error_log("DB error: " . $stmt->error);
        $_SESSION['error'] = "Update failed.";
        header("Location: reset_password.php?token=" . urlencode($token));
        exit();
    }
}
?>
