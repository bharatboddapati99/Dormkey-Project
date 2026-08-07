<?php
session_start();
require_once 'config/db.php';
require_once 'config/send_otp.php';

$error = '';
$success = '';

// Check for success messages from other pages (e.g., password reset)
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

// --------------------------------------------------
// 1. SIGNUP PROCESS (WITH EMAIL OTP VERIFICATION)
// --------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signup'])) {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($name) && !empty($email) && !empty($password)) {
        
        // Step A: Check if the email address is already registered
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $error = "An account with this email address already exists.";
        } else {
            // Step B: Generate a 6-digit random OTP
            $otp = rand(100000, 999999);

            // Step C: Send the OTP to the user's inbox
            if (sendOTP($email, $otp)) {
                // Save temporary registration details & OTP in session (expires in 5 minutes)
                $_SESSION['pending_user'] = [
                    'name'     => $name,
                    'email'    => $email,
                    'password' => password_hash($password, PASSWORD_BCRYPT),
                    'otp'      => $otp,
                    'expires'  => time() + 300
                ];

                // Redirect to OTP verification page
                header("Location: verify_otp.php");
                exit();
            } else {
                $error = "Failed to send verification email. Please ensure the email address is valid and can receive mail.";
            }
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}

// --------------------------------------------------
// 2. LOGIN PROCESS
// --------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            header("Location: index.php");
            exit();
        } else {
            $error = "Invalid email address or password.";
        }
    } else {
        $error = "Please enter both email and password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DormKey - Login or Sign Up</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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

  <!-- MAIN CONTAINER -->
  <main class="main-container auth-page-container">
    <div class="auth-grid-wrapper">
      
      <!-- LEFT BRAND PROMO -->
      <div class="auth-brand-side">
        <h1 class="logo auth-big-logo">Dorm<span>Key</span></h1>
        <p class="auth-tagline">Connect with verified student housing, zero brokerage, and safe campus living.</p>
        
        <div class="auth-benefits">
          <div class="benefit-item">
            <i class="fa-solid fa-shield-halved"></i>
            <span>100% Verified Property Listings</span>
          </div>
          <div class="benefit-item">
            <i class="fa-solid fa-handshake"></i>
            <span>Direct PG Owner Contacts</span>
          </div>
          <div class="benefit-item">
            <i class="fa-solid fa-user-graduate"></i>
            <span>Trusted by 2,300+ University Students</span>
          </div>
        </div>
      </div>

      <!-- RIGHT LOGIN FORM -->
      <div class="fb-card-wrapper">
        <div class="fb-card">

          <!-- ERROR ALERT -->
          <?php if (!empty($error)): ?>
            <div style="background:#FEE2E2; color:#991B1B; border:1px solid #FCA5A5; padding:10px 14px; border-radius:6px; margin-bottom:16px; font-size:13px; font-weight:600;">
              <i class="fa-solid fa-circle-exclamation" style="margin-right:6px;"></i> <?= htmlspecialchars($error); ?>
            </div>
          <?php endif; ?>

          <!-- SUCCESS ALERT -->
          <?php if (!empty($success)): ?>
            <div style="background:#D1FAE5; color:#065F46; border:1px solid #6EE7B7; padding:10px 14px; border-radius:6px; margin-bottom:16px; font-size:13px; font-weight:600;">
              <i class="fa-solid fa-circle-check" style="margin-right:6px;"></i> <?= htmlspecialchars($success); ?>
            </div>
          <?php endif; ?>

          <!-- LOGIN FORM -->
          <form method="POST" id="loginForm">
            <div class="form-input-group">
              <input type="email" name="email" placeholder="Email address" required>
            </div>
            <div class="form-input-group">
              <input type="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit" name="login" class="btn-fb-primary">Log In</button>
            <a href="forgot_password.php" class="forgot-link">Forgotten password?</a>
            <hr class="fb-divider">
            <button type="button" class="btn-fb-green" id="openSignupBtn">Create new account</button>
          </form>

        </div>
        <p class="auth-footer-note"><strong>DormKey Housing</strong> for students across India.</p>
      </div>

    </div>
  </main>

  <!-- SIGNUP MODAL -->
  <div class="modal-overlay hidden" id="signupModal">
    <div class="modal-card wishlist-card" style="max-width: 420px;">
      <button type="button" class="btn-close-modal" id="closeSignupBtn"><i class="fa-solid fa-xmark"></i></button>
      
      <div class="signup-header" style="border-bottom: 1px solid var(--border-color); padding-bottom: 12px; margin-bottom: 16px;">
        <h2 style="font-size: 20px; color: var(--primary-dark); font-weight: 800;">Sign Up</h2>
        <p style="font-size: 13px; color: var(--text-muted);">An email verification code will be sent to your inbox.</p>
      </div>

      <form method="POST" action="auth.php">
        <div class="form-input-group" style="margin-bottom: 12px;">
          <label class="input-label" style="font-size: 12px; font-weight: 700; color: var(--primary-dark);">Full Name</label>
          <input type="text" name="name" placeholder="First and last name" required style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:6px;">
        </div>

        <div class="form-input-group" style="margin-bottom: 12px;">
          <label class="input-label" style="font-size: 12px; font-weight: 700; color: var(--primary-dark);">Email Address</label>
          <input type="email" name="email" placeholder="e.g. name@gmail.com" required style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:6px;">
        </div>

        <div class="form-input-group" style="margin-bottom: 16px;">
          <label class="input-label" style="font-size: 12px; font-weight: 700; color: var(--primary-dark);">New Password</label>
          <input type="password" name="password" placeholder="New password" required style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:6px;">
        </div>

        <button type="submit" name="signup" class="btn-fb-green full-width">Sign Up & Get OTP</button>
      </form>
    </div>
  </div>

  <!-- MODAL TOGGLE JAVASCRIPT -->
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const signupModal = document.getElementById("signupModal");
      const openSignupBtn = document.getElementById("openSignupBtn");
      const closeSignupBtn = document.getElementById("closeSignupBtn");

      if (openSignupBtn && signupModal) {
        openSignupBtn.addEventListener("click", () => signupModal.classList.remove("hidden"));
      }
      if (closeSignupBtn && signupModal) {
        closeSignupBtn.addEventListener("click", () => signupModal.classList.add("hidden"));
      }
      if (signupModal) {
        signupModal.addEventListener("click", (e) => {
          if (e.target === signupModal) signupModal.classList.add("hidden");
        });
      }
    });
  </script>

</body>
</html>