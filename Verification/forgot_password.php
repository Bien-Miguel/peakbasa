<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

// Assuming teacher_login_verify.php is inside a folder like 'teacher/' or 'Verification/'
require_once '../conn.php';

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE email=? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $token = bin2hex(random_bytes(50));

        $updateStmt = $conn->prepare("
            UPDATE users 
            SET reset_token=?, token_expiry=NOW() + INTERVAL 30 MINUTE 
            WHERE email=?
        ");
        $updateStmt->bind_param("ss", $token, $email);
        $updateStmt->execute();

        $resetLink = "http://localhost/peakbasa/Verification/reset_password.php?token=" . urlencode($token);

        $senderEmail = 'peakbasa.website@gmail.com';
        $senderName  = 'PeakBasa Support';
        $appPassword = 'znnuuncqghttuppv';

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = $senderEmail;
            $mail->Password   = $appPassword;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom($senderEmail, $senderName);
            $mail->addAddress($email, $user['username']);
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset - PeakBasa';
            $mail->Body    = "
                <div style='font-family: Poppins, Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
                    <div style='text-align: center; margin-bottom: 30px;'>
                        <h1 style='color: #ec5757; font-size: 2rem;'>🏔️ PeakBasa</h1>
                    </div>
                    <div style='background: #f9f9f9; padding: 30px; border-radius: 15px;'>
                        <h2 style='color: #333;'>Hi {$user['username']}!</h2>
                        <p style='color: #666; line-height: 1.6;'>
                            We received a request to reset your password. Click the button below to create a new password:
                        </p>
                        <div style='text-align: center; margin: 30px 0;'>
                            <a href='$resetLink' style='
                                background: linear-gradient(135deg, #ec5757, #c04161);
                                color: white;
                                padding: 15px 40px;
                                text-decoration: none;
                                border-radius: 12px;
                                font-weight: 600;
                                display: inline-block;
                            '>Reset Password</a>
                        </div>
                        <p style='color: #999; font-size: 0.9rem;'>
                            Or copy and paste this link:<br>
                            <a href='$resetLink' style='color: #ec5757;'>$resetLink</a>
                        </p>
                        <p style='color: #999; font-size: 0.85rem; margin-top: 20px;'>
                            ⏰ This link expires in 30 minutes.<br>
                            If you didn't request this, please ignore this email.
                        </p>
                    </div>
                    <p style='text-align: center; color: #999; font-size: 0.85rem; margin-top: 20px;'>
                        — The PeakBasa Team
                    </p>
                </div>
            ";

            $mail->send();
            $message = "Password reset link has been sent to your email! Check your inbox.";
            $messageType = "success";
        } catch (Exception $e) {
            $message = "Failed to send email. Please try again later.";
            $messageType = "error";
        }
    } else {
        $message = "If an account with that email exists, you will receive a password reset link.";
        $messageType = "success"; // Don't reveal if email exists (security)
    }

    $stmt->close();
    if (isset($updateStmt)) $updateStmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PeakBasa - Forgot Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #a3d5f7 0%, #e3f2fd 100%);
            padding: 20px;
            animation: gradientShift 15s ease infinite;
        }

        @keyframes gradientShift {
            0%, 100% { background: linear-gradient(135deg, #a3d5f7 0%, #e3f2fd 100%); }
            33% { background: linear-gradient(135deg, #ffd59e 0%, #ffe6b3 100%); }
            66% { background: linear-gradient(135deg, #f9a07f 0%, #fbc4ab 100%); }
        }

        .container {
            width: 100%;
            max-width: 500px;
            background: white;
            padding: 40px;
            border-radius: 25px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo-section {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo-icon {
            font-size: 4rem;
            margin-bottom: 10px;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .logo-text {
            font-size: 1.8rem;
            font-weight: 700;
            color: #ec5757;
            margin-bottom: 5px;
        }

        .subtitle {
            color: #666;
            font-size: 0.95rem;
        }

        h2 {
            color: #333;
            text-align: center;
            margin-bottom: 15px;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .description {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            color: #555;
            font-weight: 500;
            margin-bottom: 8px;
            font-size: 0.95rem;
        }

        input[type="email"] {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s;
            background: #fafafa;
        }

        input[type="email"]:focus {
            outline: none;
            border-color: #ec5757;
            background: white;
            box-shadow: 0 0 0 4px rgba(236, 87, 87, 0.1);
        }

        input[type="submit"] {
            width: 100%;
            background: linear-gradient(135deg, #ec5757, #c04161);
            color: white;
            border: none;
            padding: 15px;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        input[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(236, 87, 87, 0.4);
        }

        input[type="submit"]:active {
            transform: translateY(0);
        }

        .message {
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
            animation: fadeIn 0.3s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .success {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            color: #155724;
            border: 2px solid #b1dfbb;
        }

        .error {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            color: #721c24;
            border: 2px solid #f1aeb5;
        }

        .back-link {
            text-align: center;
            margin-top: 25px;
        }

        .back-link a {
            color: #ec5757;
            text-decoration: none;
            font-weight: 500;
            transition: 0.3s;
        }

        .back-link a:hover {
            color: #c04161;
            text-decoration: underline;
        }

        .info-box {
            background: #f0f8ff;
            border-left: 4px solid #ec5757;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
        }

        .info-box p {
            color: #666;
            font-size: 0.9rem;
            margin: 5px 0;
        }

        /* Loading spinner */
        .loading {
            pointer-events: none;
            opacity: 0.7;
            position: relative;
        }

        .loading::after {
            content: "";
            position: absolute;
            width: 16px;
            height: 16px;
            top: 50%;
            left: 50%;
            margin-left: -8px;
            margin-top: -8px;
            border: 3px solid #ffffff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 0.6s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        @media (max-width: 480px) {
            .container {
                padding: 30px 25px;
            }

            .logo-text {
                font-size: 1.5rem;
            }

            h2 {
                font-size: 1.3rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo-section">
            <div class="logo-icon">🔐</div>
            <div class="logo-text">PeakBasa</div>
            <div class="subtitle">Password Recovery</div>
        </div>

        <h2>Forgot Your Password?</h2>
        <p class="description">
            Don't worry! Enter your email address and we'll send you instructions to reset your password.
        </p>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="forgotForm">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required 
                       placeholder="your.email@example.com" autocomplete="email">
            </div>

            <input type="submit" value="Send Reset Link" id="submitBtn">
        </form>

        <div class="info-box">
            <p>📧 <strong>Check your inbox</strong> - The reset link will arrive shortly</p>
            <p>⏰ <strong>Link expires</strong> in 30 minutes for security</p>
            <p>❓ <strong>Didn't receive it?</strong> Check your spam folder</p>
        </div>

        <div class="back-link">
            <a href="login.php">← Back to Login</a>
        </div>
    </div>

    <script>
        // Add loading state to submit button
        document.getElementById('forgotForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.classList.add('loading');
            submitBtn.value = '';
        });

        // Add input animation
        document.querySelector('input[type="email"]').addEventListener('focus', function() {
            this.parentElement.style.transform = 'scale(1.02)';
            this.parentElement.style.transition = 'transform 0.3s';
        });
        
        document.querySelector('input[type="email"]').addEventListener('blur', function() {
            this.parentElement.style.transform = 'scale(1)';
        });
    </script>
</body>
</html>