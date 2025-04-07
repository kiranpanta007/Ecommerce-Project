<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Log in to submit a review.";
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id']);
    $user_id = $_SESSION['user_id'];
    $rating = intval($_POST['rating']);
    $review_text = trim($_POST['review_text']);

    if ($rating < 1 || $rating > 5 || empty($review_text)) {
        $_SESSION['error'] = "Invalid rating or review text.";
        header("Location: product.php?id=$product_id");
        exit;
    }

    $insertQuery = "INSERT INTO reviews (product_id, user_id, rating, review_text) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param("iiis", $product_id, $user_id, $rating, $review_text);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Review submitted.";
    } else {
        $_SESSION['error'] = "Review submission failed.";
    }

    $stmt->close();
    header("Location: product.php?id=$product_id");
    exit;
}
?>
