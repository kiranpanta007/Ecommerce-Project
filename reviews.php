<?php
// Include database connection
include 'includes/db.php';
session_start();

// Check if product ID is provided in the URL
if (isset($_GET['id'])) {
    $product_id = intval($_GET['id']); // Sanitize input
    
    // Fetch product details
    $query = "SELECT * FROM products WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        die("Product not found.");
    }
} else {
    die("Invalid product ID.");
}

// Fetch reviews
$reviewQuery = "SELECT r.rating, r.review_text, r.review_date, u.name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id = ? ORDER BY r.review_date DESC";
$reviewStmt = $conn->prepare($reviewQuery);
$reviewStmt->bind_param("i", $product_id);
$reviewStmt->execute();
$reviewResult = $reviewStmt->get_result();

// Include header
include 'includes/header.php';
?>

<style>
/* Reviews Page Styling */
#reviews-container {
    max-width: 800px;
    margin: 50px auto;
    padding: 25px;
    background-color: #f9f9f9;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

#reviews-container h1 {
    text-align: center;
    color: #333;
    font-size: 26px;
    margin-bottom: 20px;
}

.review-item {
    border-bottom: 1px solid #ddd;
    padding: 15px 0;
}

.review-item:last-child {
    border-bottom: none;
}

.review-header {
    display: flex;
    justify-content: space-between;
    font-weight: 600;
}

.review-rating {
    color: #f39c12;
}

.review-text {
    margin: 10px 0;
    color: #555;
}

.review-date {
    font-size: 0.9em;
    color: #999;
}

#submit-review {
    margin-top: 40px;
    padding-top: 20px;
    border-top: 2px solid #ccc;
}

#submit-review h2 {
    font-size: 22px;
}

#submit-review label {
    margin-top: 10px;
    display: block;
}

#submit-review textarea,
#submit-review select {
    width: 100%;
    padding: 8px;
    margin-top: 5px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

#submit-review button {
    background-color: #007bff;
    color: white;
    border: none;
    padding: 12px 20px;
    margin-top: 15px;
    border-radius: 5px;
    cursor: pointer;
}

#submit-review button:hover {
    background-color: #0056b3;
}

.alert {
    padding: 15px;
    border-radius: 4px;
    margin-bottom: 20px;
}

.alert.success {
    background-color: #d4edda;
    color: #155724;
}

.alert.error {
    background-color: #f8d7da;
    color: #721c24;
}

@media (max-width: 600px) {
    #reviews-container {
        padding: 15px;
    }
}
</style>

<div id="reviews-container">
    <h1>Reviews for "<?= htmlspecialchars($product['name']); ?>"</h1>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php elseif (isset($_SESSION['error'])): ?>
        <div class="alert error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <?php if ($reviewResult->num_rows > 0): ?>
        <?php while ($review = $reviewResult->fetch_assoc()): ?>
            <div class="review-item">
                <div class="review-header">
                    <span><?= htmlspecialchars($review['name']); ?></span>
                    <span class="review-rating"><?= str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']); ?></span>
                </div>
                <p class="review-text"><?= nl2br(htmlspecialchars($review['review_text'])); ?></p>
                <small class="review-date">Reviewed on <?= date("F j, Y", strtotime($review['review_date'])); ?></small>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No reviews yet. Be the first to review!</p>
    <?php endif; ?>

    <section id="submit-review">
        <h2>Write a Review</h2>
        <?php if (isset($_SESSION['user_id'])): ?>
            <form action="submit_review.php" method="POST">
                <input type="hidden" name="product_id" value="<?= $product_id; ?>">
                
                <label for="rating">Rating</label>
                <select id="rating" name="rating" required>
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                        <option value="<?= $i; ?>"><?= str_repeat('★', $i) . str_repeat('☆', 5 - $i); ?></option>
                    <?php endfor; ?>
                </select>
                
                <label for="review_text">Your Review</label>
                <textarea id="review_text" name="review_text" rows="4" required></textarea>
                
                <button type="submit">Submit Review</button>
            </form>
        <?php else: ?>
            <p>Please <a href="login.php">log in</a> to write a review.</p>
        <?php endif; ?>
    </section>
</div>

<?php
$reviewStmt->close();
include 'includes/footer.php';
?>
