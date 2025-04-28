<?php
session_start();
include 'includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Server-side validation
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $_SESSION['error'] = "All fields are required!";
        header("Location: register.php");
        exit();
    }

    // Username validation (only letters, numbers, underscores, min 3 chars)
    if (!preg_match("/^[a-zA-Z0-9_]{3,}$/", $name)) {
        $_SESSION['error'] = "Username must be at least 3 characters and contain only letters, numbers, and underscores.";
        header("Location: register.php");
        exit();
    }

    // Check if username already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE name = ?");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['error'] = "Username already taken!";
        header("Location: register.php");
        exit();
    }

    // Email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format!";
        header("Location: register.php");
        exit();
    }

    // Password length
    if (strlen($password) < 8) {
        $_SESSION['error'] = "Password must be at least 8 characters!";
        header("Location: register.php");
        exit();
    }

    // Password match
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match!";
        header("Location: register.php");
        exit();
    }

    // Hash and save
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $hashed_password);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Registration successful! Please login.";
        header("Location: login.php");
        exit();
    } else {
        $_SESSION['error'] = "Something went wrong. Please try again.";
        header("Location: register.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="auth-container">
        <h2>Register</h2>
        
        <?php
        if (isset($_SESSION['error'])) {
            echo "<p class='error'>" . $_SESSION['error'] . "</p>";
            unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
            echo "<p class='success'>" . $_SESSION['success'] . "</p>";
            unset($_SESSION['success']);
        }
        ?>

        <form action="register.php" method="POST" style="max-width: 400px; margin: auto;">
            <!-- Username Field -->
            <input type="text" name="name" id="name" placeholder="Username" required minlength="3"
                   pattern="^[a-zA-Z0-9_]{3,}$"
                   title="Username must be at least 3 characters and contain only letters, numbers, and underscores."
                   style="width: 100%; padding: 10px; margin-bottom: 15px; box-sizing: border-box;">
           
            <!-- Email Field -->
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

            <!-- Confirm Password Field -->
            <div style="position: relative; width: 100%; margin-bottom: 15px;">
                <input type="password" name="confirm_password" placeholder="Confirm Password" required minlength="8"
                       title="Password must be at least 8 characters." id="confirm-password"
                       style="width: 100%; padding: 10px 40px 10px 10px; box-sizing: border-box;">
                <i id="confirm-eye-icon" class="fa fa-eye" onclick="toggleConfirmPasswordVisibility()"
                   style="position: absolute; top: 50%; right: 10px; transform: translateY(-50%); cursor: pointer; color: #333; font-size: 18px;"></i>
            </div>

            <button type="submit" style="width: 100%; padding: 10px; background-color: #007bff; color: white; border: none; cursor: pointer;">
                Register
            </button>
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

    function toggleConfirmPasswordVisibility() {
        const confirmPasswordInput = document.getElementById('confirm-password');
        const confirmEyeIcon = document.getElementById('confirm-eye-icon');

        if (confirmPasswordInput.type === "password") {
            confirmPasswordInput.type = "text";
            confirmEyeIcon.classList.remove("fa-eye");
            confirmEyeIcon.classList.add("fa-eye-slash");
        } else {
            confirmPasswordInput.type = "password";
            confirmEyeIcon.classList.remove("fa-eye-slash");
            confirmEyeIcon.classList.add("fa-eye");
        }
    }
    </script>
</body>
</html>
