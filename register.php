<?php
session_start();
include 'includes/db.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check DB connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $zip_code = trim($_POST['zip_code'] ?? '');

    // Server-side validation
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $_SESSION['error'] = "Name, email, and password fields are required!";
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

    // Optional phone validation (if provided)
    if (!empty($phone) && !preg_match('/^\+?[0-9\s\-]{7,20}$/', $phone)) {
        $_SESSION['error'] = "Invalid phone number format!";
        header("Location: register.php");
        exit();
    }

    // Optional zip code validation (if provided)
    if (!empty($zip_code) && !preg_match('/^\d{4,10}$/', $zip_code)) {
        $_SESSION['error'] = "Invalid zip code format!";
        header("Location: register.php");
        exit();
    }

    // Hash and save
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, phone, address, city, state, zip_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $name, $email, $hashed_password, $phone, $address, $city, $state, $zip_code);

      if ($stmt->execute()) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'kiranpanta9846@gmail.com';
            $mail->Password   = 'gqaoprdghaxuymat';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('kiranpanta9846@gmail.com', 'Your Site');
            $mail->addAddress($email, $name);

            $mail->isHTML(true);
            $mail->Subject = 'Welcome to Our Website!';
            $mail->Body    = "<p>Hi <strong>$name</strong>,</p><p>Thanks for registering!</p><p>Regards,<br>Your Team</p>";

            $mail->send();

            $_SESSION['success'] = "Registration successful! Welcome email sent.";
            header("Location: login.php.php");
            exit();
        } catch (Exception $e) {
            $_SESSION['error'] = "Registration succeeded, but email failed: {$mail->ErrorInfo}";
            header("Location: register.php");
            exit();
        }
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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Register</title>
    <link rel="stylesheet" href="styles/style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
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
            <input
                type="text"
                name="name"
                id="name"
                placeholder="Username"
                required
                minlength="3"
                pattern="^[a-zA-Z0-9_]{3,}$"
                title="Username must be at least 3 characters and contain only letters, numbers, and underscores."
                style="width: 100%; padding: 10px; margin-bottom: 15px; box-sizing: border-box;"
            />

            <!-- Email Field -->
            <input
                type="email"
                name="email"
                placeholder="Email"
                required
                style="width: 100%; padding: 10px; margin-bottom: 15px; box-sizing: border-box;"
            />

            <!-- Phone Field -->
            <input
                type="tel"
                name="phone"
                placeholder="Phone (optional)"
                pattern="^\+?[0-9\s\-]{7,20}$"
                title="Enter a valid phone number"
                style="width: 100%; padding: 10px; margin-bottom: 15px; box-sizing: border-box;"
            />

            <!-- Address Field -->
            <input
                type="text"
                name="address"
                placeholder="Address (optional)"
                style="width: 100%; padding: 10px; margin-bottom: 15px; box-sizing: border-box;"
            />

            <!-- City Field -->
            <input
                type="text"
                name="city"
                placeholder="City (optional)"
                style="width: 100%; padding: 10px; margin-bottom: 15px; box-sizing: border-box;"
            />

            <!-- State Field -->
            <input
                type="text"
                name="state"
                placeholder="State (optional)"
                style="width: 100%; padding: 10px; margin-bottom: 15px; box-sizing: border-box;"
            />

            <!-- Zip Code Field -->
            <input
                type="text"
                name="zip_code"
                placeholder="Zip Code (optional)"
                pattern="^\d{4,10}$"
                title="Enter a valid zip code"
                style="width: 100%; padding: 10px; margin-bottom: 15px; box-sizing: border-box;"
            />

            <!-- Password Field -->
            <div style="position: relative; width: 100%; margin-bottom: 15px;">
                <input
                    type="password"
                    name="password"
                    placeholder="Password"
                    required
                    minlength="8"
                    title="Password must be at least 8 characters."
                    id="password"
                    style="width: 100%; padding: 10px 40px 10px 10px; box-sizing: border-box;"
                />
                <i
                    id="eye-icon"
                    class="fa fa-eye"
                    onclick="togglePasswordVisibility()"
                    style="position: absolute; top: 50%; right: 10px; transform: translateY(-50%); cursor: pointer; color: #333; font-size: 18px;"
                ></i>
            </div>

            <!-- Confirm Password Field -->
            <div style="position: relative; width: 100%; margin-bottom: 15px;">
                <input
                    type="password"
                    name="confirm_password"
                    placeholder="Confirm Password"
                    required
                    minlength="8"
                    title="Password must be at least 8 characters."
                    id="confirm-password"
                    style="width: 100%; padding: 10px 40px 10px 10px; box-sizing: border-box;"
                />
                <i
                    id="confirm-eye-icon"
                    class="fa fa-eye"
                    onclick="toggleConfirmPasswordVisibility()"
                    style="position: absolute; top: 50%; right: 10px; transform: translateY(-50%); cursor: pointer; color: #333; font-size: 18px;"
                ></i>
            </div>

            <button
                type="submit"
                style="width: 100%; padding: 10px; background-color: #007bff; color: white; border: none; cursor: pointer;"
            >
                Register
            </button>
            
        </form>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById("password");
            const eyeIcon = document.getElementById("eye-icon");

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
            const confirmPasswordInput = document.getElementById("confirm-password");
            const confirmEyeIcon = document.getElementById("confirm-eye-icon");

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
