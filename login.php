<?php
session_start();
include 'includes/db.php';

// Enable error reporting for debugging (Disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Server-side validation
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "All fields are required!";
        header("Location: login.php");
        exit(); 
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format!";
        header("Location: login.php");
        exit();
    }

    // Check if email exists in the database
    $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
    if (!$stmt) {
        die("Database query failed: " . $conn->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $name, $hashed_password);
    $stmt->fetch();

    if ($stmt->num_rows > 0) {
        if (password_verify($password, $hashed_password)) {

            // Successful login
                    // Successful login
    $_SESSION['user_id'] = $id;
    $_SESSION['user_name'] = $name;
    $_SESSION['user_email'] = $email; // Add this line to store user email in session
    header("Location: profile.php");
    exit();

        } else {
            // Invalid password
            $_SESSION['error'] = "Invalid email or password!";
        }
    } else {
        // Email not found
        $_SESSION['error'] = "No user found with this email!";
    }

    $stmt->close();
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="auth-container">
        <h2>Login</h2>
        <?php
            if (isset($_SESSION['error'])) {
                echo "<p class='error'>" . $_SESSION['error'] . "</p>";
                unset($_SESSION['error']);
            }
        ?>

<form action="login.php" method="POST" style="max-width: 400px; margin: auto;">
    <input type="email" name="email" placeholder="Email" required
           style="width: 100%; padding: 10px; margin-bottom: 15px; box-sizing: border-box;">

    <!-- Password Field -->
    <div style="position: relative; width: 100%; margin-bottom: 15px;">
        <input type="password" name="password" placeholder="Password" required minlength="8"
               title="Password must be at least 8 characters." id="password"
               style="width: 100%; padding: 10px 40px 10px 10px; box-sizing: border-box;">
        <i id="eye-icon" class="fa fa-eye" onclick="togglePasswordVisibility()"
           style="position: absolute; top: 50%; right: 10px; transform: translateY(-50%); cursor: pointer; color: #333; font-size: 18px;"></i>
    </div>

    <button type="submit" style="width: 100%; padding: 10px; background-color: #007bff; color: white; border: none; cursor: pointer;">
        Login
    </button>
    
    <p style="text-align: center; margin-top: 10px;">
    <a href="forgot_password.php" style="color: #007bff; text-decoration: none; font-size: 14px;">
        Forgot Password?
    </a>
    </p>
</form>


    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
function togglePasswordVisibility() {
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eye-icon');

    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        eyeIcon.classList.remove("fa-eye");
        eyeIcon.classList.add("fa-eye-slash");
    } else {
        passwordInput.type = "password";
        eyeIcon.classList.remove("fa-eye-slash");
        eyeIcon.classList.add("fa-eye");
    }
}
</script>

</body>
</html>

