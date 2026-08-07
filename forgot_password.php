<?php
session_start();
require 'config/db.php'; // Adjust path to your DB connection
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
        $_SESSION['otp_expiry'] = time() + (10 * 60); // Valid for 10 minutes

        // 3. Send OTP using PHPMailer
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'your-email@gmail.com'; // Your Gmail address
            $mail->Password   = 'your-app-password';   // Gmail App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('your-email@gmail.com', 'DormKey Security');
            $mail->addAddress($email, $user['first_name']);

            $mail->isHTML(true);
            $mail->Subject = 'DormKey - Password Reset OTP';
            $mail->Body    = "<h3>Password Reset Request</h3>
                              <p>Your OTP for resetting your DormKey account password is: <b>{$otp}</b></p>
                              <p>This OTP will expire in 10 minutes. If you did not request this, please ignore this email.</p>";

            $mail->send();

            $mail->send();

            // Set the spam notification message in session
            $_SESSION['info_msg'] = "An OTP has been sent to your email. If you don't see it in your Inbox, please check your Spam or Junk folder.";

            // Redirect to OTP verification page
            header("Location: verify_otp.php");
            exit();

            // 4. Redirect to OTP verification page
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