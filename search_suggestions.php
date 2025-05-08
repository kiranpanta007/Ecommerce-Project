<?php
// Include the database connection file
include('includes/db.php');

// Check if the 'query' parameter exists in the URL
if (isset($_GET['query'])) {
    // Clean the query input
    $query = strtolower(trim($_GET['query']));
    
    // SQL query to search product names and descriptions
    // Search for names or descriptions that match the query (case-insensitive)
    $sql = "SELECT name FROM products WHERE LOWER(name) LIKE ? OR LOWER(description) LIKE ? LIMIT 5";
    
    // Prepare the SQL statement
    $stmt = $conn->prepare($sql);
    $searchTerm = "%$query%"; // Add the wildcard for partial matching
    
    // Bind parameters
    $stmt->bind_param('ss', $searchTerm, $searchTerm);
    
    // Execute the statement
    $stmt->execute();
    
    // Get the result of the query
    $result = $stmt->get_result();

    // Initialize an array to hold the suggestions
    $suggestions = [];
    
    // Fetch the results and populate the suggestions array
    while ($row = $result->fetch_assoc()) {
        $suggestions[] = $row['name']; // Collect product names
    }

    // Return the suggestions as a JSON response
    echo json_encode($suggestions);
} else {
    // If no query is provided, return an empty array
    echo json_encode([]);
}

// Close the database connection
$conn->close();
?>
