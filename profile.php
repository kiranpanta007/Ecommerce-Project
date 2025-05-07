<?php
session_start();
include 'includes/db.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user data
$stmt = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($name, $email);
$stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Profile Container -->
    <div class="profile-body-container" style="max-width: 500px; margin: 40px auto; background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);">
    
    <h2 style="text-align: center; color: #333333; margin-bottom: 20px;">Welcome, <?php echo htmlspecialchars($name); ?>!</h2>
    
    <!-- Feedback Messages -->
    <!-- <?php if (isset($_SESSION['success'])): ?>
        <p style="color: green; text-align: center; margin-bottom: 20px;">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </p>
    <?php endif; ?> -->
    
    <?php if (isset($_SESSION['error'])): ?>
        <p style="color: red; text-align: center; margin-bottom: 20px;">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </p>
    <?php endif; ?>
    
    <!-- Update Profile Form -->
    <form action="update_profile.php" method="POST" style="margin-bottom: 20px;">
        <div style="margin-bottom: 15px;">
            <label for="name" style="font-weight: bold; color: #555;">Full Name</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required
                style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box;">
        </div>
        
        <div style="margin-bottom: 15px;">
            <label for="email" style="font-weight: bold; color: #555;">Email</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required
                style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box;">
        </div>
        
        <button type="submit" style="width: 100%; padding: 10px; background-color: #007bff; color: white; border: none; border-radius: 5px; font-size: 16px;">
            Update Profile
        </button>
    </form>
    
    <!-- Change Password Form -->
    <form action="change_password.php" method="POST" style="margin-bottom: 20px;">
    <div style="margin-bottom: 15px;">
        <label for="current_password" style="font-weight: bold; color: #555;">Current Password</label>
        <div style="position: relative;">
            <input type="password" id="current_password" name="current_password" required
                style="width: 100%; padding: 10px 40px 10px 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box;">
            <i id="current-password-eye" class="fa fa-eye" onclick="togglePasswordVisibility('current_password', 'current-password-eye')"
               style="position: absolute; top: 50%; right: 10px; transform: translateY(-50%); cursor: pointer; color: #333; font-size: 18px;"></i>
        </div>
    </div>
    
    <div style="margin-bottom: 15px;">
        <label for="new_password" style="font-weight: bold; color: #555;">New Password</label>
        <div style="position: relative;">
            <input type="password" id="new_password" name="new_password" minlength="8" required
                style="width: 100%; padding: 10px 40px 10px 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box;">
            <i id="new-password-eye" class="fa fa-eye" onclick="togglePasswordVisibility('new_password', 'new-password-eye')"
               style="position: absolute; top: 50%; right: 10px; transform: translateY(-50%); cursor: pointer; color: #333; font-size: 18px;"></i>
        </div>
    </div>
    
    <div style="margin-bottom: 15px;">
        <label for="confirm_new_password" style="font-weight: bold; color: #555;">Confirm New Password</label>
        <div style="position: relative;">
            <input type="password" id="confirm_new_password" name="confirm_new_password" minlength="8" required
                style="width: 100%; padding: 10px 40px 10px 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box;">
            <i id="confirm-password-eye" class="fa fa-eye" onclick="togglePasswordVisibility('confirm_new_password', 'confirm-password-eye')"
               style="position: absolute; top: 50%; right: 10px; transform: translateY(-50%); cursor: pointer; color: #333; font-size: 18px;"></i>
        </div>
    </div>
    
    <button type="submit" style="width: 100%; padding: 10px; background-color: #28a745; color: white; border: none; border-radius: 5px; font-size: 16px;">
        Change Password
    </button>
</form>



    <!-- Logout Button -->
    <div style="text-align: center; margin-top: 30px;">
        <a href="logout.php" style="color: #dc3545; text-decoration: none; font-weight: bold; font-size: 16px;">
            Logout
        </a>
    </div>
</div>


    <?php include 'includes/footer.php'; ?>

    <script>
function togglePasswordVisibility(inputId, iconId) {
    const passwordInput = document.getElementById(inputId);
    const eyeIcon = document.getElementById(iconId);

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
