<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require_once __DIR__ . '/../vendor/autoload.php';

function sendOTP($recipientEmail, $otpCode) {
    $mail = new PHPMailer(true);

    try {
        // Server settings (Gmail SMTP)
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        
        // --- YOUR GMAIL CREDENTIALS ---
        $mail->Username   = 'dormkey7@gmail.com'; // <--- PUT YOUR REAL GMAIL ADDRESS HERE
        $mail->Password   = 'lsfibkwhgbqvuvne';             // Your 16-character App Password
        
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Bypass local SSL certificate check (Required for XAMPP/localhost)
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true
            )
        );

        // Sender & Recipient
        $mail->setFrom('dormkey7@gmail.com', 'DormKey Support'); // <--- SAME GMAIL ADDRESS HERE
        $mail->addAddress($recipientEmail);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your DormKey Verification Code';
        $mail->Body    = "
            <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #E2E8F0; border-radius: 8px; max-width: 450px;'>
                <h2 style='color: #1E293B; margin-top: 0;'>Welcome to DormKey!</h2>
                <p style='color: #475569; font-size: 14px;'>Use the code below to verify your email address and complete registration:</p>
                <div style='background: #F1F5F9; text-align: center; padding: 15px; border-radius: 6px; margin: 20px 0;'>
                    <h1 style='color: #059669; letter-spacing: 6px; margin: 0; font-size: 32px;'>{$otpCode}</h1>
                </div>
                <p style='color: #64748B; font-size: 12px;'>This code is valid for 5 minutes. If you did not create a DormKey account, please ignore this email.</p>
            </div>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>