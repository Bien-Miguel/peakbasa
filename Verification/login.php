<?php
/**
 * PeakBasa Student Login Page
 * This file handles student authentication and initiates 2FA via email.
 */

// 1. CRITICAL: START OUTPUT BUFFERING to prevent "headers already sent" errors
ob_start();

session_start();
// Error reporting is on for development, but display is off to prevent header failure
error_reporting(E_ALL);

// --- Database Connection ---
require_once '../conn.php';

// --- PHPMailer Class Imports ---
require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $password_plain = $_POST['password'] ?? '';

    if (empty($username) || empty($password_plain)) {
        $message = "Please enter both username and password.";
        $messageType = "error";
    } else {
        try {
            // 1. SELECT User from 'users' table (Student)
            $stmt = $conn->prepare("SELECT user_id, password_hash, is_verified, email FROM users WHERE username=? LIMIT 1");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if (!$result) {
                throw new Exception("Database query failed: " . $conn->error);
            }

            if ($result->num_rows === 1) {
                $row = $result->fetch_assoc();
                $user_id = $row['user_id']; // Using the correct column name

                if ($row['is_verified'] == 0) {
                    $message = "⚠️ Please verify your email first.";
                    $messageType = "warning";
                }
                else if (password_verify($password_plain, $row['password_hash'])) {

                    // 2. UPDATE login code
                    $login_code = rand(100000, 999999);

                    $update = $conn->prepare("UPDATE users SET login_code=? WHERE user_id=?");
                    $update->bind_param("si", $login_code, $user_id); // Changed to 'i' for integer user_id
                    $update->execute();
                    $update->close();

                    // --- PHPMailer Attempt ---
                    try {
                        // Using the credentials you provided
                        $sender_email = 'peakbasa.website@gmail.com';
                        $sender_password = 'znnuuncqghttuppv'; // Your App Password

                        $mail = new PHPMailer(true);
                        $mail->isSMTP();
                        $mail->Host = 'smtp.gmail.com';
                        $mail->SMTPAuth = true;
                        $mail->Username = $sender_email;
                        $mail->Password = $sender_password;
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port = 587;

                        $mail->SMTPDebug = 0; // 0 for production

                        // --- Email Content ---
                        $mail->setFrom($sender_email, 'PeakBasa Security');
                        $mail->addAddress($row['email']); // User's email from DB lookup

                        // ✨ Set Charset and Content Type
                        $mail->CharSet = 'UTF-8';
                        $mail->isHTML(true);

                        // ✨ New Subject Line
                        $mail->Subject = '🔒 Your PeakBasa Login Code';

                        // ✨ New HTML Email Body for 2FA Login
                        $mail->Body    = "
                            <div style='font-family: Poppins, sans-serif; max-width: 600px; margin: 20px auto; background-color: #ffffff; border: 1px solid #e0e0e0; border-radius: 12px; overflow: hidden;'>
                                <div style='background-color: #ec5757; color: white; padding: 25px 30px; text-align: center;'>
                                    <img src='cid:peakbasa_logo' alt='PeakBasa Logo' style='width: 50px; height: auto; margin-bottom: 15px;'>
                                    <h1 style='margin: 0; font-size: 24px; font-weight: 600;'>PeakBasa Login Verification</h1>
                                </div>
                                <div style='padding: 30px; color: #333333; line-height: 1.6;'>
                                    <p style='font-size: 16px; margin-bottom: 20px;'>Hi " . htmlspecialchars($username) . ",</p>
                                    <p style='font-size: 16px; margin-bottom: 25px;'>To complete your login, please use the one-time security code below:</p>
                                    
                                    <div style='background-color: #fdf0f0; border: 2px dashed #fcdcdc; border-radius: 8px; padding: 20px; text-align: center; margin-bottom: 30px;'>
                                        <p style='font-size: 16px; margin-bottom: 10px; color: #7c4646;'>Your Login Code:</p>
                                        <p style='font-size: 28px; font-weight: 700; color: #ec5757; letter-spacing: 4px; margin: 0;'>
                                            " . htmlspecialchars($login_code) . "
                                        </p>
                                    </div>
                                    
                                    <p style='font-size: 16px; margin-bottom: 25px;'>Enter this code on the verification page to access your account.</p>
                                    
                                    <div style='text-align: center;'>
                                        <a href='http://localhost/peakbasa/Verification/login_verify.php' style='display: inline-block; background-color: #ec5757; color: white; padding: 12px 25px; border-radius: 25px; text-decoration: none; font-weight: 600; font-size: 16px; transition: background-color 0.3s;' target='_blank'>
                                            Go to Verification Page
                                        </a>
                                    </div>

                                    <p style='font-size: 14px; color: #666; margin-top: 30px;'>If you did not attempt to log in, you can safely ignore this email or contact support if you suspect suspicious activity.</p>
                                </div>
                                <div style='background-color: #f8f9fa; padding: 15px 30px; text-align: center; font-size: 12px; color: #888;'>
                                    © " . date("Y") . " PeakBasa. All rights reserved.
                                </div>
                            </div>
                        ";

                        // ✨ New Plain Text Alternative Body
                         $mail->AltBody = "Hi " . $username . ",\n\nYour one-time PeakBasa login code is: " . $login_code . "\n\nEnter this code on the verification page: http://localhost/peakbasa/Verification/login_verify.php\n\nIf you did not attempt to log in, please ignore this email.\n\n© " . date("Y") . " PeakBasa.";

                        // ✨ Embed the logo image
                        try {
                            $logoPath = '../ui/Illustration17.png'; // Adjust path if needed relative to login.php
                            if (file_exists($logoPath)) {
                                 $mail->AddEmbeddedImage($logoPath, 'peakbasa_logo');
                            } else {
                                 error_log("Logo file not found at: " . $logoPath);
                            }
                        } catch (Exception $e) {
                             error_log("Error embedding logo: " . $e->getMessage());
                        }

                        // Send the email
                        $mail->send();

                        // --- Redirect Logic ---
                        $_SESSION['temp_user'] = $user_id;
                        $_SESSION['temp_email'] = $row['email'];
                        header("Location: ./login_verify.php");

                        ob_end_flush();
                        exit;

                    } catch (Exception $e) {
                        // If email fails, display error but do not redirect
                        $message = "❌ Login successful, but **failed to send 2FA email**. Check your SMTP setup.<br>Error: " . htmlspecialchars($e->getMessage()) . " | Mailer Error: " . $mail->ErrorInfo;
                        $messageType = "error";
                        error_log("PHPMailer Error during login 2FA for $username: " . $e->getMessage() . " | Mailer Error: " . $mail->ErrorInfo);
                    }
                } else {
                    $message = "❌ Wrong password.";
                    $messageType = "error";
                }
            } else {
                $message = "❌ Student account not found or credentials invalid.";
                $messageType = "error";
            }

            $stmt->close();
        } catch (Exception $e) {
            $message = "❌ A server error occurred during login. Details: " . htmlspecialchars($e->getMessage());
            $messageType = "error";
        }
    }
}

$conn->close();

// CRITICAL: If no redirect occurred, output the buffered content (the HTML page)
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PeakBasa - Login</title>
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
            max-width: 450px;
            background: white;
            padding: 40px;
            border-radius: 25px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            animation: slideIn 0.5s ease-out;
            position: relative;
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
            animation: bounce 2s ease infinite;
        }

        @keyframes bounce {
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
            margin-bottom: 30px;
            font-size: 1.5rem;
            font-weight: 600;
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

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s;
            background: #fafafa;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #ec5757;
            background: white;
            box-shadow: 0 0 0 4px rgba(236, 87, 87, 0.1);
        }

        .password-wrapper {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 1.2rem;
            color: #999;
            user-select: none;
        }

        .toggle-password:hover {
            color: #ec5757;
        }

        .forgot-password-link {
            text-align: right;
            margin-top: -10px;
            margin-bottom: 20px;
        }

        .forgot-password-link a {
            color: #ec5757;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: 0.3s;
        }

        .forgot-password-link a:hover {
            color: #c04161;
            text-decoration: underline;
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

        /* ✨ Corrected: Use .message and specific type classes */
        .message {
             padding: 15px;
             border-radius: 12px;
             margin-bottom: 20px;
             text-align: center;
             font-weight: 500;
             animation: fadeIn 0.3s ease-out; /* Added fade in */
        }
        .message.error {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            color: #721c24;
            border: 2px solid #f1aeb5;
             animation: shake 0.5s; /* Keep shake for error */
        }
         .message.warning { /* Added warning style */
            background: #fff3cd;
            color: #856404;
            border: 2px solid #ffeeba;
         }
         .message.success { /* Added success style */
             background: #d1e7dd;
             color: #0f5132;
             border: 2px solid #badbcc;
         }

         /* Added fade in animation */
         @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }


        .register-link {
            text-align: center;
            margin-top: 25px;
            font-size: 0.95rem;
            color: #666;
        }

        .register-link a {
            color: #ec5757;
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s;
        }

        .register-link a:hover {
            color: #c04161;
            text-decoration: underline;
        }

        .back-link {
            text-align: center;
            margin-top: 15px;
        }

        .back-link a {
            color: #999;
            text-decoration: none;
            font-size: 0.9rem;
            transition: 0.3s;
        }

        .back-link a:hover {
            color: #ec5757;
        }

        .divider {
            text-align: center;
            margin: 20px 0;
            position: relative;
        }

        .divider::before {
            content: "";
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e0e0e0;
        }

        .divider span {
            background: white;
            padding: 0 15px;
            position: relative;
            color: #999;
            font-size: 0.9rem;
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
            <div class="logo-icon">🏔️</div>
            <div class="logo-text">PeakBasa</div>
            <div class="subtitle">Welcome back!</div>
        </div>

        <h2>Login to Your Account</h2>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo htmlspecialchars($messageType); ?>">
                 <?php echo $message; // Display message (already includes HTML potentially) ?>
            </div>
        <?php endif; ?>


        <form method="post" action="" id="loginForm">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required
                       placeholder="Enter your username" autocomplete="username">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" required
                           placeholder="Enter your password" autocomplete="current-password">
                    <span class="toggle-password" onclick="togglePassword()">👁️</span>
                </div>
            </div>

            <div class="forgot-password-link">
                <a href="forgot_password.php">Forgot Password?</a>
            </div>

            <input type="submit" value="Login" id="submitBtn">
        </form>

        <div class="divider">
            <span>OR</span>
        </div>

        <div class="register-link">
            Don't have an account? <a href="../Verification/register.php">Register here</a>
        </div>

        <div class="back-link">
            <a href="../Main/welcome.php">← Back to home</a>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.querySelector('.toggle-password');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.textContent = '🙈';
            } else {
                passwordInput.type = 'password';
                toggleIcon.textContent = '👁️';
            }
        }

        // Add loading state to submit button
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            // Check form validity before adding loading state
            if (this.checkValidity()) {
                 submitBtn.classList.add('loading');
                 submitBtn.value = '';
            }
        });

        // Add input animation
        document.querySelectorAll('input[type="text"], input[type="password"]').forEach(input => {
            input.addEventListener('focus', function() {
                // Check if parentElement exists before trying to access style
                if (this.parentElement) {
                     this.parentElement.style.transform = 'scale(1.02)';
                     this.parentElement.style.transition = 'transform 0.3s';
                }
            });

            input.addEventListener('blur', function() {
                if (this.parentElement) {
                     this.parentElement.style.transform = 'scale(1)';
                 }
            });
        });
    </script>
</body>
</html>