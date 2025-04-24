<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'includes/db.php'; // Database connection
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyShop</title>
    <link rel="stylesheet" href="styles/style.css">
    
    <style>
        /* Style for the search bar container */
        .search-bar {
            position: relative; /* Ensure the suggestion box is positioned relative to this container */
        }

        /* Initially hide the suggestion box */
        .suggestion-dropdown {
            display: none; /* Hide by default */
            position: absolute;
            top: 100%; /* Position the suggestion box just below the input field */
            left: 0;
            width: 100%;
            max-height: 300px; /* Max height for the dropdown */
            overflow-y: auto; /* Allow scrolling if there are many suggestions */
            background-color: #fff;
            border: 1px solid #ccc;
            border-top: none; /* Remove top border */
            border-radius: 4px;
            z-index: 9999; /* Ensure the suggestions appear on top */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 5px 0; /* Add padding to the dropdown */
        }

        /* Style for the suggestion list */
        .suggestion-dropdown ul {
            list-style-type: none;
            margin: 0;
            padding: 0;
        }

        /* Style for individual suggestion items */
        .suggestion-item {
            padding: 10px;
            cursor: pointer;
            font-size: 14px;
            border-bottom: 1px solid #ddd;
            color: black; /* Set the font color to black */
        }

        /* Hover effect on suggestions */
        .suggestion-item:hover {
            background-color: #f0f0f0;
        }

        /* Style for the search input */
        #search-input {
            width: 250px;
            padding: 10px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 4px;
            position: relative;
            z-index: 10; /* Ensure the input field appears above the suggestion box */
        }
    </style>
</head>
<body>

<header>
    <div class="container">
        <div class="logo">
            <h1><a href="index.php">Mero Shopping</a></h1>
        </div>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="shop.php">Shop</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>
        </nav>
        <div class="search-bar">
            <!-- Search input, no search button -->
            <input type="text" id="search-input" placeholder="Search products..." oninput="fetchSuggestions()" onkeydown="handleKeyDown(event)">
            
            <!-- Suggestion box -->
            <div id="suggestion-dropdown" class="suggestion-dropdown"></div>
        </div>

        <div class="user-links">
            <?php if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])): ?>
                <a href="order_history.php">Order History</a>
                <a href="profile.php">Profile</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
        </div>
        <div class="cart">
            <a href="cart.php">🛒 Cart (<?php
                $total_items = 0;
                if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
                    foreach ($_SESSION['cart'] as $quantity) {
                        $total_items += $quantity;
                    }
                }
                echo $total_items;
            ?>)</a>
        </div>
    </div>
</header>

<script>
function fetchSuggestions() {
    const query = document.getElementById('search-input').value;
    const suggestionBox = document.getElementById('suggestion-dropdown');
    
    if (query.length < 2) { 
        suggestionBox.style.display = 'none'; // Hide suggestion box if input is less than 2 characters
        return;
    }

    fetch(`search_suggestions.php?query=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            let suggestions = '';
            if (data.error) {
                console.error(data.error);
                return;
            }
            
            if (data.length > 0) {
                suggestions = '<ul>';
                data.forEach(item => {
                    suggestions += `<li class="suggestion-item" onclick="selectSuggestion('${item}')">${item}</li>`;
                });
                suggestions += '</ul>';
                suggestionBox.style.display = 'block'; // Show suggestion box if suggestions are available
            } else {
                suggestionBox.style.display = 'none'; // Hide suggestion box if no results
            }
            suggestionBox.innerHTML = suggestions;
        })
        .catch(error => console.error('Error fetching suggestions:', error));
}

function selectSuggestion(value) {
    // Set the search input field to the selected suggestion
    document.getElementById('search-input').value = value;

    // Redirect to the search page with the search term in the query string
    window.location.href = `search.php?query=${encodeURIComponent(value)}`;
}

// Handle pressing Enter key to trigger the search
function handleKeyDown(event) {
    if (event.key === 'Enter') {
        const query = document.getElementById('search-input').value;
        if (query.length >= 2) { 
            window.location.href = `search.php?query=${encodeURIComponent(query)}`;
        }
    }
}
</script>

</body>
</html>
