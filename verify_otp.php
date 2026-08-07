<?php
session_start();
require_once 'config/db.php';

$error = '';

if (!isset($_SESSION['pending_user'])) {
    header("Location: auth.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify'])) {
    $userOtp = trim($_POST['otp'] ?? '');
    $pending = $_SESSION['pending_user'];

    if (time() > $pending['expires']) {
        $error = "OTP code has expired. Please try signing up again.";
        unset($_SESSION['pending_user']);
    } elseif ($userOtp == $pending['otp']) {
        // OTP matches! Insert user into database
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        if ($stmt->execute([$pending['name'], $pending['email'], $pending['password']])) {
            $_SESSION['user_id']   = $pdo->lastInsertId();
            $_SESSION['user_name'] = $pending['name'];

            unset($_SESSION['pending_user']);
            header("Location: index.php");
            exit();
        } else {
            $error = "Database error. Registration failed.";
        }
    } else {
        $error = "Invalid OTP code. Please check your inbox and try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DormKey - Email Verification</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body style="display: flex; justify-content: center; align-items: center; min-height: 100vh; background: #F3F4F6;">

  <div class="fb-card" style="width: 100%; max-width: 400px; text-align: center; padding: 30px;">
    <h1 class="logo" style="margin-bottom: 10px;">Dorm<span>Key</span></h1>
    <h2 style="font-size: 20px; font-weight: 800; color: var(--primary-dark); margin-bottom: 6px;">Verify Your Email</h2>
    <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 20px;">
      We sent a 6-digit OTP code to <br>
      <strong style="color: var(--primary-dark);"><?= htmlspecialchars($_SESSION['pending_user']['email']); ?></strong>
    </p>

    <?php if ($error): ?>
      <div style="background:#FEE2E2; color:#991B1B; border:1px solid #FCA5A5; padding:10px; border-radius:6px; margin-bottom:16px; font-size:13px; font-weight:600;">
        <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error); ?>
      </div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-input-group" style="margin-bottom: 16px;">
        <input type="text" name="otp" placeholder="Enter 6-Digit OTP" maxlength="6" required style="text-align: center; letter-spacing: 6px; font-size: 20px; font-weight: 700; padding: 12px;">
      </div>
      <button type="submit" name="verify" class="btn-fb-green full-width" style="padding: 12px; font-weight: 700;">Verify & Complete Registration</button>
    </form>

    <p style="margin-top: 15px; font-size: 12px; color: var(--text-muted);">
      Didn't receive the email? <a href="auth.php" style="color: var(--accent-gold); font-weight: 700;">Try signing up again</a>.
    </p>
  </div>

</body>
</html>