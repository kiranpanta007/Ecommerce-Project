<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

include '../includes/db.php';

if (isset($_GET['id'])) {
    $user_id = intval($_GET['id']);

    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "User deleted successfully.";
    } else {
        $_SESSION['error'] = "Failed to delete user.";
    }
    $stmt->close();
} else {
    $_SESSION['error'] = "Invalid user ID.";
}

// Redirect back to customers page
header("Location: customers.php");
exit();
?>
