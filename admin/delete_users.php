<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

include '../includes/db.php';

// Validate 'id' parameter
if (isset($_GET['id'])) {
    $user_id = intval($_GET['id']); // Sanitize input

    // Prepare the delete statement
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        $stmt->close();
        header("Location: users.php?msg=User deleted successfully");
        exit();
    } else {
        $stmt->close();
        header("Location: users.php?error=Failed to delete user");
        exit();
    }
} else {
    header("Location: users.php?error=Invalid user ID");
    exit();
}
?>
