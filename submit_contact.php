<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'includes/db.php'; // Include your database connection

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate input
    $name = trim(mysqli_real_escape_string($conn, $_POST['name']));
    $email = trim(mysqli_real_escape_string($conn, $_POST['email']));
    $message = trim(mysqli_real_escape_string($conn, $_POST['message']));

    // Basic validation
    if (empty($name) || empty($email) || empty($message)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: contact.php");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format.";
        header("Location: contact.php");
        exit();
    }

    // Insert message into the database
    $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, message, submitted_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("sss", $name, $email, $message);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Thank you for your message! We'll get back to you soon.";
    } else {
        $_SESSION['error'] = "Something went wrong. Please try again later.";
    }

    $stmt->close();
    $conn->close();

    // Redirect back to contact page
    header("Location: contact.php");
    exit();
} else {
    // Invalid access
    header("Location: contact.php");
    exit();
}
?>
