<?php
session_start();
include 'includes/db.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);

    // Server-side validation
    if (empty($name) || empty($email)) {
        $_SESSION['error'] = "All fields are required!";
        header("Location: profile.php");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format!";
        header("Location: profile.php");
        exit();
    }

    // Check if email is already used by another user
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->bind_param("si", $email, $user_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['error'] = "Email is already in use by another account!";
        $stmt->close();
        header("Location: profile.php");
        exit();
    }
    $stmt->close();

    // Update user data
    $update_stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
    $update_stmt->bind_param("ssi", $name, $email, $user_id);

    if ($update_stmt->execute()) {
        $_SESSION['success'] = "Profile updated successfully!";
    } else {
        $_SESSION['error'] = "Error updating profile. Please try again!";
    }

    $update_stmt->close();
    header("Location: profile.php");
    exit();
} else {
    // Redirect back if accessed without POST request
    header("Location: profile.php");
    exit();
}
?>
