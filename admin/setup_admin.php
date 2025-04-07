<?php
include '../includes/db.php';

$admin_name = "Admin";
$admin_email = "admin@example.com";
$admin_password = password_hash("admin123", PASSWORD_DEFAULT); // Securely hash password

$stmt = $conn->prepare("INSERT INTO admins (name, email, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $admin_name, $admin_email, $admin_password);

if ($stmt->execute()) {
    echo "Admin user created successfully!";
} else {
    echo "Error creating admin: " . $stmt->error;
}
?>
