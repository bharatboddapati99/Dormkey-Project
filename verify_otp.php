<?php
session_start();

// Ensure user requested password reset first
if (!isset($_SESSION['reset_email']) || !isset($_SESSION['reset_otp'])) {
    header("Location: forgot_password.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_otp = trim($_POST['otp']);

    if (time() > $_SESSION['otp_expiry']) {
        $error = "OTP has expired. Please request a new one.";
        unset($_SESSION['reset_otp'], $_SESSION['otp_expiry']);
    } elseif ($entered_otp === $_SESSION['reset_otp']) {
        // OTP verified successfully
        $_SESSION['otp_verified'] = true;
        unset($_SESSION['reset_otp'], $_SESSION['otp_expiry']); // Clear OTP session data
        
        header("Location: reset_password.php");
        exit();
    } else {
        $error = "Invalid OTP code. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - DormKey</title>
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
        .info-banner {
            background-color: #e8f4fd;
            color: #1d6f8a;
            border: 1px solid #bce1f3;
            padding: 12px;
            border-radius: 6px;
            font-size: 14px;
            margin-bottom: 20px;
            text-align: left;
            line-height: 1.4;
        }
        .error-msg {
            color: #d9534f;
            margin-bottom: 15px;
            font-size: 14px;
        }
        input[type="text"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0 20px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 18px;
            letter-spacing: 4px;
            text-align: center;
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
    </style>
</head>
<body>

<div class="container">
    <h2>Verify OTP</h2>

    <!-- Display Spam Warning Notice -->
    <?php if (isset($_SESSION['info_msg'])): ?>
        <div class="info-banner">
            📩 <strong>Check your email:</strong> <?php echo htmlspecialchars($_SESSION['info_msg']); ?>
        </div>
        <?php unset($_SESSION['info_msg']); ?>
    <?php endif; ?>

    <p>Enter the 6-digit code sent to<br><strong><?php echo htmlspecialchars($_SESSION['reset_email']); ?></strong></p>

    <?php if ($error): ?>
        <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="text" name="otp" maxlength="6" placeholder="000000" required pattern="\d{6}" title="Please enter 6 digits">
        <button type="submit">Verify & Proceed</button>
    </form>
</div>

</body>
</html>