<?php
session_start();
require_once 'config/db.php';

$step = 1;
$user_id = null;
$error = '';
$success = '';

// STEP 1: Process Email Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['find_account'])) {
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);

    if ($email) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $_SESSION['reset_user_id'] = $user['id'];
            $step = 2;
        } else {
            $error = "No account found with that email address.";
        }
    } else {
        $error = "Please enter a valid email address.";
    }
}

// STEP 2: Process Resetting Password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $reset_id = $_SESSION['reset_user_id'] ?? null;

    if ($reset_id && $new_password && $new_password === $confirm_password) {
        $hashedPassword = password_hash($new_password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        
        if ($stmt->execute([$hashedPassword, $reset_id])) {
            unset($_SESSION['reset_user_id']);
            $_SESSION['success'] = "Password reset successfully! Please log in with your new password.";
            header("Location: auth.php");
            exit();
        } else {
            $error = "Failed to update password. Please try again.";
            $step = 2;
        }
    } else {
        $error = "Passwords do not match or fields are empty.";
        $step = 2;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DormKey - Recover Password</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<!-- NAVBAR -->
  <header class="navbar">
    <div class="nav-container">
      <a href="index.php" class="logo">Dorm<span>Key</span></a>
      <nav class="nav-links">
        <a href="index.php" class="nav-item">HOME</a>
        <a href="about.php" class="nav-item">ABOUT</a>
        <a href="help.php" class="nav-item">HELP</a>
        <a href="auth.php" class="btn-auth active">LOGIN / SIGN UP</a>
      </nav>
    </div>
  </header>

  <main class="main-container auth-page-container">
    <div class="fb-card" style="max-width: 450px; margin: 40px auto; text-align: left;">
      
      <div class="signup-header">
        <h2><i class="fa-solid fa-key" style="color: var(--accent-gold);"></i> Find Your Account</h2>
        <p>Please enter your registered email to reset your password.</p>
      </div>

      <?php if ($error): ?>
        <div style="background:#FEE2E2; color:#991B1B; padding:10px; border-radius:6px; margin-bottom:16px; font-size:13px;">
          <?= htmlspecialchars($error); ?>
        </div>
      <?php endif; ?>

      <?php if ($step === 1): ?>
        <!-- STEP 1 FORM: ENTER EMAIL -->
        <form method="POST">
          <div class="form-input-group">
            <label class="input-label">Registered Email</label>
            <input type="email" name="email" placeholder="Enter your email address" required>
          </div>
          <button type="submit" name="find_account" class="btn-fb-primary">Search Account</button>
          <a href="auth.php" class="forgot-link" style="text-align:center; margin-top:12px;">Cancel & Back to Login</a>
        </form>

      <?php elseif ($step === 2): ?>
        <!-- STEP 2 FORM: ENTER NEW PASSWORD -->
        <form method="POST">
          <div class="form-input-group">
            <label class="input-label">New Password</label>
            <input type="password" name="new_password" placeholder="Enter new password" required>
          </div>
          <div class="form-input-group">
            <label class="input-label">Confirm New Password</label>
            <input type="password" name="confirm_password" placeholder="Re-enter new password" required>
          </div>
          <button type="submit" name="reset_password" class="btn-fb-green full-width">Reset Password</button>
        </form>
      <?php endif; ?>

    </div>
  </main>

</body>
</html>