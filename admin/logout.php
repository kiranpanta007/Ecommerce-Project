<?php
// Start the session
session_start();

// Clear all admin session variables
$admin_vars = ['admin_logged_in', 'admin_id', 'admin_name', 'admin_email'];
foreach ($admin_vars as $var) {
    unset($_SESSION[$var]);
}

// Regenerate session ID
session_regenerate_id(true);

// Destroy session
session_destroy();

// Clear session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Set success message and redirect
$_SESSION['message'] = "You have been successfully logged out.";
header("Location: login.php");
exit();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Logging Out...</title>
    <style>
        .logout-message {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            padding: 20px;
            background: #2ecc71;
            color: white;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
            font-family: Arial, sans-serif;
        }
    </style>
</head>
<body>
    <div class="logout-message">
        Logging you out securely...
    </div>
    <script>
        // Optional: Add a loading animation
        setTimeout(function() {
            document.querySelector('.logout-message').textContent = "Redirecting to login...";
        }, 1000);
    </script>
</body>
</html>