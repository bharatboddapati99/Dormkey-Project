<?php
session_start();
require 'config/db.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    // 1. Check if email exists in Database
    $stmt = $pdo->prepare("SELECT id, first_name FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // 2. Generate 6-digit OTP and expiration (10 minutes)
        $otp = sprintf("%06d", mt_rand(100000, 999999));
        $_SESSION['reset_email'] = $email;
        $_SESSION['reset_otp'] = $otp;
        $_SESSION['otp_expiry'] = time() + (10 * 60);

        // 3. Send OTP using PHPMailer
        $mail = new PHPMailer(true);

        try {
            // Enable detailed debug logging if an error occurs
            $mail->SMTPDebug   = 2; 
            $mail->Debugoutput = 'html';

            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'dormkey7@gmail.com'; // REPLACE WITH YOUR GMAIL
            $mail->Password   = 'affrimzjoaffmqtp';     // REPLACE WITH YOUR 16-CHAR APP PASSWORD (NO SPACES)
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 2525;
            $mail->Timeout    = 30; // 10-second connection timeout limit

            // Bypass SSL certificate verification delays on Railway
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer'       => false,
                    'verify_peer_name'  => false,
                    'allow_self_signed' => true
                )
            );

            $mail->setFrom('dormkey7@gmail.com', 'DormKey Security');
            $mail->addAddress($email, $user['first_name']);

            $mail->isHTML(true);
            $mail->Subject = 'DormKey - Password Reset OTP';
            $mail->Body    = "<h3>Password Reset Request</h3>
                              <p>Your OTP for resetting your DormKey account password is: <b>{$otp}</b></p>
                              <p>This OTP will expire in 10 minutes. If you did not request this, please ignore this email.</p>";

            // Executed ONLY ONCE to prevent hangs
            $mail->send();

            $_SESSION['info_msg'] = "An OTP has been sent to your email. If you don't see it in your Inbox, please check your Spam or Junk folder.";

            // Redirect to OTP verification page
            header("Location: verify_otp.php");
            exit();

        } catch (Exception $e) {
            $error = "Failed to send OTP email. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        $error = "No account found with that email address.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - DormKey</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
            text-align: center;
        }
        .error-msg {
            color: #d9534f;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            font-size: 14px;
        }
        input[type="email"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0 20px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 15px;
        }
        button {
            width: 100%;
            background-color: #007bff;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .back-link {
            display: block;
            margin-top: 15px;
            color: #6c757d;
            text-decoration: none;
            font-size: 14px;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Forgot Password</h2>
    <p>Enter your registered email address to receive a 6-digit verification OTP.</p>

    <?php if (!empty($error)): ?>
        <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="forgot_password.php">
        <input type="email" name="email" placeholder="Enter your email address" required>
        <button type="submit">Send Reset OTP</button>
    </form>

    <a href="auth.php" class="back-link">← Back to Login</a>
</div>

</body>
</html>