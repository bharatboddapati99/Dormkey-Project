<?php
session_start();
require 'config/db.php';

// Block access if OTP was not verified
if (!isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true || !isset($_SESSION['reset_email'])) {
    header("Location: forgot_password.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password     = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (strlen($new_password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Hash the new password securely
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        $email           = $_SESSION['reset_email'];

        // Update database
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->execute([$hashed_password, $email]);

        // Clean up reset session variables
        unset($_SESSION['reset_email'], $_SESSION['otp_verified']);

        // Redirect to login with success message
        header("Location: login.php?reset=success");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Set New Password - DormKey</title>
</head>
<body>
    <h2>Set New Password</h2>

    <?php if ($error): ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="password" name="password" placeholder="New Password" required><br><br>
        <input type="password" name="confirm_password" placeholder="Confirm New Password" required><br><br>
        <button type="submit">Update Password</button>
    </form>
</body>
</html>