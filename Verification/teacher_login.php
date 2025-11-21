<?php
/**
 * PeakBasa Teacher Login Page
 * This file handles teacher authentication and initiates 2FA via email.
 */
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../conn.php';

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
            $stmt = $conn->prepare("SELECT teacher_id, password_hash, is_verified, email FROM teachers WHERE username=? LIMIT 1");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if (!$result) {
                throw new Exception("Database query failed: " . $conn->error);
            }

            if ($result->num_rows === 1) {
                $row = $result->fetch_assoc();
                $teacher_id = $row['teacher_id'];

                if ($row['is_verified'] == 0) {
                    $message = "⚠️ Please verify your teacher email first.";
                    $messageType = "warning";
                } 
                else if (password_verify($password_plain, $row['password_hash'])) {
                    
                    $login_code = rand(100000, 999999);
                    $current_timestamp = date('Y-m-d H:i:s'); 

                    $update = $conn->prepare("UPDATE teachers SET login_code=?, login_code_timestamp=? WHERE teacher_id=?");
                    $update->bind_param("ssi", $login_code, $current_timestamp, $teacher_id);
                    $update->execute();
                    $update->close();

                    try {
                        $sender_email = 'peakbasa.website@gmail.com'; 
                        $sender_password = 'znnuuncqghttuppv'; 

                        $mail = new PHPMailer(true);
                        $mail->isSMTP();
                        $mail->Host = 'smtp.gmail.com';
                        $mail->SMTPAuth = true;
                        $mail->Username = $sender_email; 
                        $mail->Password = $sender_password;
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
                        $mail->Port = 587;
                        $mail->SMTPDebug = 0;
                        
                        $mail->setFrom($sender_email, 'PeakBasa Teacher Security');
                        $mail->addAddress($row['email']);
                        $mail->isHTML(true);
                        $mail->Subject = 'Your Two-Factor Teacher Login Code';
                        
                        // Enhanced HTML Email Template
                        $mail->Body = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Login Code</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, \'Helvetica Neue\', Arial, sans-serif; background-color: #f5f5f5;">
    <table role="presentation" style="width: 100%; border-collapse: collapse; background-color: #f5f5f5;" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                <!-- Main Container -->
                <table role="presentation" style="max-width: 600px; width: 100%; border-collapse: collapse; background-color: #ffffff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);" cellpadding="0" cellspacing="0">
                    
                    <!-- Header with Gradient -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #4CAF50 0%, #8BC34A 100%); padding: 40px 30px; text-align: center; border-radius: 16px 16px 0 0;">
                            <div style="font-size: 60px; margin-bottom: 10px;">🧑‍🏫</div>
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 700; letter-spacing: -0.5px;">PeakBasa</h1>
                            <p style="margin: 8px 0 0 0; color: rgba(255, 255, 255, 0.95); font-size: 14px; font-weight: 500;">Teacher Portal Security</p>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="margin: 0 0 20px 0; color: #333333; font-size: 24px; font-weight: 600;">Two-Factor Authentication</h2>
                            
                            <p style="margin: 0 0 25px 0; color: #666666; font-size: 16px; line-height: 1.6;">
                                Dear <strong style="color: #4CAF50;">' . htmlspecialchars($username) . '</strong>,
                            </p>
                            
                            <p style="margin: 0 0 30px 0; color: #666666; font-size: 16px; line-height: 1.6;">
                                A login attempt was detected for your teacher account. Please use the verification code below to complete your authentication:
                            </p>
                            
                            <!-- Code Box -->
                            <table role="presentation" style="width: 100%; border-collapse: collapse; margin-bottom: 30px;" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding: 30px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 12px; border: 2px dashed #4CAF50;">
                                        <div style="font-size: 14px; color: #666666; margin-bottom: 10px; font-weight: 500; letter-spacing: 0.5px;">YOUR VERIFICATION CODE</div>
                                        <div style="font-size: 42px; font-weight: 700; color: #4CAF50; letter-spacing: 8px; font-family: \'Courier New\', monospace;">' . $login_code . '</div>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Security Notice -->
                            <div style="background-color: #fff3cd; border-left: 4px solid #FFC107; padding: 15px 20px; border-radius: 8px; margin-bottom: 30px;">
                                <p style="margin: 0; color: #856404; font-size: 14px; line-height: 1.5;">
                                    <strong>🔒 Security Notice:</strong> This code will expire in 10 minutes. Never share this code with anyone, including PeakBasa staff.
                                </p>
                            </div>
                            
                            <div style="padding: 20px; background-color: #f8f9fa; border-radius: 8px; margin-bottom: 30px;">
                                <p style="margin: 0 0 10px 0; color: #666666; font-size: 14px; line-height: 1.6;">
                                    <strong style="color: #333333;">Request Details:</strong>
                                </p>
                                <table style="width: 100%; font-size: 14px; color: #666666;" cellpadding="4" cellspacing="0">
                                    <tr>
                                        <td style="padding: 4px 0;"><strong>Time:</strong></td>
                                        <td style="padding: 4px 0;">' . date('F j, Y, g:i A') . '</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 4px 0;"><strong>Account:</strong></td>
                                        <td style="padding: 4px 0;">' . htmlspecialchars($username) . '</td>
                                    </tr>
                                </table>
                            </div>
                            
                            <p style="margin: 0 0 20px 0; color: #666666; font-size: 14px; line-height: 1.6;">
                                If you didn\'t attempt to log in, please ignore this email and ensure your password is secure. Consider changing your password if you suspect unauthorized access.
                            </p>
                            
                            <div style="border-top: 2px solid #e9ecef; padding-top: 20px; margin-top: 30px;">
                                <p style="margin: 0; color: #999999; font-size: 13px; line-height: 1.5;">
                                    Best regards,<br>
                                    <strong style="color: #4CAF50;">The PeakBasa Security Team</strong>
                                </p>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 30px; text-align: center; border-radius: 0 0 16px 16px; border-top: 1px solid #e9ecef;">
                            <p style="margin: 0 0 10px 0; color: #999999; font-size: 13px; line-height: 1.5;">
                                This is an automated security message from PeakBasa Teacher Portal
                            </p>
                            <p style="margin: 0; color: #cccccc; font-size: 12px;">
                                © ' . date('Y') . ' PeakBasa. All rights reserved.
                            </p>
                        </td>
                    </tr>
                    
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';

                        // Plain text alternative
                        $mail->AltBody = "Dear Educator " . htmlspecialchars($username) . ",\n\nYour one-time teacher login authentication code is: " . $login_code . "\n\nThis code will expire in 10 minutes.\n\nDo not share this code with anyone.\n\nIf you didn't attempt to log in, please ignore this email.\n\nBest regards,\nThe PeakBasa Security Team";

                        $mail->send(); 

                        $_SESSION['temp_user'] = $teacher_id; 
                        $_SESSION['temp_email'] = $row['email'];
                        header("Location: ./teacher_login_verify.php"); 
                        exit;
                    } catch (Exception $e) {
                        if ($conn->ping()) {
                            $clear_code = $conn->prepare("UPDATE teachers SET login_code = NULL, login_code_timestamp = NULL WHERE teacher_id = ?");
                            $clear_code->bind_param("i", $teacher_id);
                            $clear_code->execute();
                            $clear_code->close();
                        }
                        $message = "❌ Login successful, but **failed to send 2FA email**. Check your SMTP setup.<br>Error: " . htmlspecialchars($e->getMessage());
                        $messageType = "error";
                    }
                } else {
                    $message = "❌ Wrong password.";
                    $messageType = "error";
                }
            } else {
                $message = "❌ Teacher account not found or credentials invalid.";
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PeakBasa - Teacher Login</title>
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
            background: linear-gradient(135deg, #4CAF50 0%, #8BC34A 100%); 
            padding: 20px;
            animation: gradientShift 15s ease infinite;
        }

        @keyframes gradientShift {
            0%, 100% { background: linear-gradient(135deg, #4CAF50 0%, #8BC34A 100%); }
            33% { background: linear-gradient(135deg, #00BCD4 0%, #009688 100%); }
            66% { background: linear-gradient(135deg, #FFC107 0%, #FF9800 100%); }
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
            color: #4CAF50;
            animation: bounce 2s ease infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .logo-text {
            font-size: 1.8rem;
            font-weight: 700;
            color: #4CAF50;
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
            border-color: #4CAF50;
            background: white;
            box-shadow: 0 0 0 4px rgba(76, 175, 80, 0.1);
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
            color: #4CAF50;
        }

        .forgot-password-link {
            text-align: right;
            margin-top: -10px;
            margin-bottom: 20px;
        }

        .forgot-password-link a {
            color: #4CAF50;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: 0.3s;
        }

        .forgot-password-link a:hover {
            color: #388E3C;
            text-decoration: underline;
        }

        input[type="submit"] {
            width: 100%;
            background: linear-gradient(135deg, #4CAF50, #388E3C); 
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
            box-shadow: 0 5px 20px rgba(76, 175, 80, 0.4);
        }

        input[type="submit"]:active {
            transform: translateY(0);
        }

        .error, .warning {
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
            animation: shake 0.5s;
        }
        
        .error {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            color: #721c24;
            border: 2px solid #f1aeb5;
        }
        
        .warning {
             background: #fff3cd;
             color: #856404;
             border: 2px solid #ffeeba;
        }

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
            color: #4CAF50;
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s;
        }

        .register-link a:hover {
            color: #388E3C;
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
            color: #4CAF50;
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
            <div class="logo-icon">🧑‍🏫</div>
            <div class="logo-text">PeakBasa</div>
            <div class="subtitle">Welcome back, Educator!</div>
        </div>

        <h2>Teacher Login</h2>

        <?php if (!empty($message)): ?>
            <div class="<?= htmlspecialchars($messageType) ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="" id="loginForm">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required 
                             placeholder="Your teacher username" autocomplete="username">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" required 
                                 placeholder="Your password" autocomplete="current-password">
                    <span class="toggle-password" onclick="togglePassword()">👁️</span>
                </div>
            </div>

            <div class="forgot-password-link">
                <a href="teacher_forgot_password.php">Forgot Password?</a>
            </div>

            <input type="submit" value="Login" id="submitBtn">
        </form>

        <div class="divider">
            <span>OR</span>
        </div>

        <div class="register-link">
            Don't have a teacher account? <a href="teacher_register.php">Register here</a>
        </div>

        <div class="back-link">
            <a href="../main/welcome.php">← Back to home</a>
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

        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            if (document.getElementById('username').value && document.getElementById('password').value) {
                submitBtn.classList.add('loading');
                submitBtn.value = 'Logging in...';
            }
        });
    </script>
</body>
</html>