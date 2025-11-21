<?php
session_start();

// --- PHPMailer Class Imports ---
// These MUST be at the top level (global scope)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// --- Database Connection ---
// Assuming teacher_login_verify.php is inside a folder like 'teacher/' or 'Verification/'
require_once '../conn.php';

$message = "";
$messageType = "";

// --- Teacher Specific Role Configuration ---
$roleTitle = 'Teacher'; // 'Teacher'
$roleIcon = '🧑‍🏫';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password_plain = $_POST['password'] ?? '';
    $full_name = trim($_POST['full_name'] ?? ''); // Using full_name for teachers

    // Basic validation
    if ($username === '' || $email === '' || $password_plain === '' || $full_name === '') {
        $message = "Please fill all required fields.";
        $messageType = "error";
    } else {
        // Hash the password for storage in the 'password_hash' column
        $password_hash = password_hash($password_plain, PASSWORD_DEFAULT);

        // Check for duplicates in the 'teachers' table
        $check = $conn->prepare("SELECT teacher_id FROM teachers WHERE username = ? OR email = ?");
        $check->bind_param("ss", $username, $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $message = "⚠️ Username or email already taken.";
            $messageType = "error";
        } else {
            // ✅ Insert new teacher into the 'teachers' table
            $stmt = $conn->prepare("INSERT INTO teachers (username, email, password_hash, full_name, is_verified) VALUES (?, ?, ?, ?, 0)"); // is_verified is 0 (false) initially
            $stmt->bind_param("ssss", $username, $email, $password_hash, $full_name);

            if ($stmt->execute()) {
                // Get the newly inserted teacher_id
                $teacher_id = $conn->insert_id;

                // Generate random verification code (8 characters long as per schema)
                $verification_code = bin2hex(random_bytes(4));

                // Save verification code to database
                $update = $conn->prepare("UPDATE teachers SET verification_code=? WHERE teacher_id=?");
                $update->bind_param("si", $verification_code, $teacher_id);
                $update->execute();

                // --- PHPMailer Integration (Using GMAIL via App Password) ---
                try {
                    // Include the main Composer autoloader (assuming vendor/autoload.php is available)
                    require '../vendor/autoload.php'; 

                    $mail = new PHPMailer(true); // Enable exceptions

                    // Configure SMTP for Gmail
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com'; 
                    $mail->SMTPAuth = true;
                    
                    $sender_email = 'peakbasa.website@gmail.com'; 
                    $sender_password = 'znnuuncqghttuppv'; // Your App Password
                    
                    $mail->Username = $sender_email; 
                    $mail->Password = $sender_password;
                    
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
                    $mail->Port = 587;
                    $mail->SMTPDebug = 0; // 0 for production

                    // --- Email Content ---
                    $mail->setFrom($sender_email, 'PeakBasa Verification');
                    $mail->addAddress($email); // Send to the teacher's email

                    // ✨ Set Charset and Content Type
                    $mail->CharSet = 'UTF-8';
                    $mail->isHTML(true);                                  
                    
                    // ✨ New Subject Line
                    $mail->Subject = '🧑‍🏫 Welcome, Educator! Verify Your PeakBasa Teacher Account';

                    // ✨ New HTML Email Body for Teacher Verification
                    $mail->Body    = "
                        <div style='font-family: Poppins, sans-serif; max-width: 600px; margin: 20px auto; background-color: #ffffff; border: 1px solid #e0e0e0; border-radius: 12px; overflow: hidden;'>
                            <div style='background-color: #10b981; color: white; padding: 25px 30px; text-align: center;'> 
                                <img src='cid:peakbasa_logo' alt='PeakBasa Logo' style='width: 50px; height: auto; margin-bottom: 15px;'>
                                <h1 style='margin: 0; font-size: 24px; font-weight: 600;'>Welcome, Educator!</h1>
                            </div>
                            <div style='padding: 30px; color: #333333; line-height: 1.6;'>
                                <p style='font-size: 16px; margin-bottom: 20px;'>Hi " . htmlspecialchars($full_name) . " (" . htmlspecialchars($username) . "),</p>
                                <p style='font-size: 16px; margin-bottom: 25px;'>Thank you for registering as a Teacher on PeakBasa! To activate your account and access your dashboard, please use the verification code below:</p>
                                
                                <div style='background-color: #f0fdf4; border: 2px dashed #a7f3d0; border-radius: 8px; padding: 20px; text-align: center; margin-bottom: 30px;'> 
                                    <p style='font-size: 28px; font-weight: 700; color: #059669; letter-spacing: 4px; margin: 0;'>
                                        " . htmlspecialchars($verification_code) . "
                                    </p>
                                </div>
                                
                                <p style='font-size: 16px; margin-bottom: 25px;'>Enter this code on the teacher verification page:</p>
                                
                                <div style='text-align: center;'>
                                    <a href='http://localhost/peakbasa/Verification/teacher_verify.php' style='display: inline-block; background-color: #10b981; color: white; padding: 12px 25px; border-radius: 25px; text-decoration: none; font-weight: 600; font-size: 16px; transition: background-color 0.3s;' target='_blank'>
                                        Go to Teacher Verification
                                    </a>
                                </div>

                                <p style='font-size: 14px; color: #666; margin-top: 30px;'>If you didn't create this account, you can safely ignore this email.</p>
                            </div>
                            <div style='background-color: #f8f9fa; padding: 15px 30px; text-align: center; font-size: 12px; color: #888;'>
                                © " . date("Y") . " PeakBasa. All rights reserved.
                            </div>
                        </div>
                    ";

                    // ✨ New Plain Text Alternative Body
                    $mail->AltBody = "Hi " . $full_name . " (" . $username . "),\n\nWelcome to PeakBasa!\n\nYour teacher verification code is: " . $verification_code . "\n\nEnter this code on the teacher verification page: http://localhost/peakbasa/Verification/teacher_verify.php\n\nIf you didn't create this account, please ignore this email.\n\n© " . date("Y") . " PeakBasa.";

                    // ✨ Embed the logo image
                    try {
                        $logoPath = '../ui/Illustration17.png'; // Adjust path if needed relative to teacher_register.php
                        if (file_exists($logoPath)) {
                             $mail->AddEmbeddedImage($logoPath, 'peakbasa_logo');
                        } else {
                             error_log("[Teacher Register] Logo file not found at: " . $logoPath); 
                        }
                    } catch (Exception $e) {
                         error_log("[Teacher Register] Error embedding logo: " . $e->getMessage()); 
                    }

                    // Send the email
                    if ($mail->send()) {
                        $_SESSION['pending_email'] = $email;
                        header("Location: teacher_verify.php"); 
                        exit;
                    } else {
                        // Should be caught below, but added defensively
                        $message = "⚠️ Registration successful, but email failed. Error: " . $mail->ErrorInfo;
                        $messageType = "error";                        
                    }

                } catch (Exception $e) {
                    // Mail failed, but registration succeeded. Inform the user.
                    $message = "⚠️ Registration successful. However, the verification email could not be sent. Error: " . $e->getMessage() . " | Mailer Error: " . $mail->ErrorInfo;
                    $messageType = "error";
                    error_log("PHPMailer Error during teacher registration for $email: " . $e->getMessage() . " | Mailer Error: " . $mail->ErrorInfo);
                }
                // --- End PHPMailer Integration ---

            } else {
                $message = "❌ Registration failed: " . $stmt->error;
                $messageType = "error";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PeakBasa - Register as <?php echo $roleTitle; ?></title>
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
            /* --- NEW Teacher Background --- */
            background: linear-gradient(135deg, #a7f3d0 0%, #e0f2fe 100%);
            padding: 20px;
            animation: gradientShift 15s ease infinite;
        }

        /* --- NEW Teacher Animation --- */
        @keyframes gradientShift {
            0%, 100% { background: linear-gradient(135deg, #a7f3d0 0%, #e0f2fe 100%); } /* light green/blue */
            33% { background: linear-gradient(135deg, #a5f3fc 0%, #e0e7ff 100%); } /* light cyan/indigo */
            66% { background: linear-gradient(135deg, #bbf7d0 0%, #f0f9ff 100%); } /* light green/sky */
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
            /* --- NEW Teacher Color --- */
            color: #059669; /* Dark Green */
            margin-bottom: 5px;
        }

        .role-badge {
            display: inline-block;
            /* --- NEW Teacher Color --- */
            background: linear-gradient(135deg, #10b981, #059669); /* Green Gradient */
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-top: 10px;
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
        input[type="email"],
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
        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            /* --- NEW Teacher Color --- */
            border-color: #10b981; /* Green */
            background: white;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1); /* Green Shadow */
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
            /* --- NEW Teacher Color --- */
            color: #10b981;
        }

        input[type="submit"] {
            width: 100%;
            /* --- NEW Teacher Color --- */
            background: linear-gradient(135deg, #10b981, #059669); /* Green Gradient */
            color: white;
            border: none;
            padding: 15px;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }

        input[type="submit"]:hover {
            transform: translateY(-2px);
            /* --- NEW Teacher Color --- */
            box-shadow: 0 5px 20px rgba(16, 185, 129, 0.4); /* Green Shadow */
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

        .login-link {
            text-align: center;
            margin-top: 25px;
            font-size: 0.95rem;
            color: #666;
        }

        .login-link a {
            /* --- NEW Teacher Color --- */
            color: #10b981;
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s;
        }

        .login-link a:hover {
            /* --- NEW Teacher Color --- */
            color: #059669;
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
            /* --- NEW Teacher Color --- */
            color: #10b981;
        }

        /* Loading spinner for submit button */
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
            <div class="logo-icon"><?php echo $roleIcon; ?></div>
            <div class="logo-text">PeakBasa</div>
            <div class="role-badge">Register as <?php echo $roleTitle; ?></div>
        </div>

        <h2>Create Your Account</h2>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="teacher_register.php" id="registerForm">
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" required 
                       placeholder="Your full name" autocomplete="name"
                       value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required 
                       placeholder="Choose a username" autocomplete="username"
                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required 
                       placeholder="your.email@example.com" autocomplete="email"
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" required 
                           placeholder="Create a strong password" autocomplete="new-password">
                    <span class="toggle-password" onclick="togglePassword()">👁️</span>
                </div>
            </div>
            
            <input type="submit" value="Create Account" id="submitBtn">
        </form>

        <div class="login-link">
            Already have an account? <a href="teacher_login.php">Login here</a>
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
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.classList.add('loading');
            submitBtn.value = '';
        });

        // Add input animation
        document.querySelectorAll('input[type="text"], input[type="email"], input[type="password"]').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
                this.parentElement.style.transition = 'transform 0.3s';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });
    </script>
</body>
</html>