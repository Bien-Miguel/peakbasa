<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

require_once '../conn.php';

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $target_role = 'teacher'; 

    // FIXED: Querying the correct 'teachers' table with a strict role comparison.
    $stmt = $conn->prepare("SELECT * FROM teachers WHERE email=? AND role=? LIMIT 1");
    $stmt->bind_param("ss", $email, $target_role);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        $token = bin2hex(random_bytes(50));
    
        // FIXED: Updating the 'teachers' table
        $updateStmt = $conn->prepare("
            UPDATE teachers 
            SET reset_token=?, token_expiry=NOW() + INTERVAL 30 MINUTE 
            WHERE email=? AND role=?
        ");
        $updateStmt->bind_param("sss", $token, $email, $target_role);
        $updateStmt->execute();
    
        // Use the dedicated teacher reset script
        $resetLink = "http://localhost/peakbasa/Verification/teacher_reset_password.php?token=" . urlencode($token);
    
        $senderEmail = 'tisoyangelo31@gmail.com';
        $senderName  = 'PeakBasa Support';
        $appPassword = 'fxuigaugthijwaxl';
    
        $mail = new PHPMailer(true);
        try {
            // PHPMailer configuration
            $mail->isSMTP();
            $mail->Host      = 'smtp.gmail.com';
            $mail->SMTPAuth  = true;
            $mail->Username  = $senderEmail;
            $mail->Password  = $appPassword;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port      = 587;

            $mail->setFrom($senderEmail, $senderName);
            $mail->addAddress($email, $user['username']);
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset - PeakBasa (Teacher)';
            $mail->Body    = "
                <div style='font-family: Poppins, Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
                    <div style='text-align: center; margin-bottom: 30px;'>
                        <h1 style='color: #ec5757; font-size: 2rem;'>🏔️ PeakBasa (Teacher Portal)</h1>
                    </div>
                    <p>
                        We received a request to reset your password for your **TEACHER** account (Username: {$user['username']}). Click the button below to create a new password:
                    </p>
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='$resetLink' style='background: linear-gradient(135deg, #ec5757, #c04161); color: white; padding: 15px 40px; text-decoration: none; border-radius: 12px; font-weight: 600; display: inline-block;'>Reset Teacher Password</a>
                    </div>
                </div>
            "; 

            $mail->send();
            $message = "✅ Password reset link has been sent to your email! Check your inbox.";
            $messageType = "success";
        } catch (Exception $e) {
            $message = "❌ Failed to send email. Mailer Error: " . $mail->ErrorInfo;
            $messageType = "error";
        }

    } else {
        // Security principle: show the same message whether the account was found or not.
        $message = "If a Teacher account with that email exists, you will receive a password reset link.";
        $messageType = "success"; 
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
    <title>Teacher Forgot Password</title>
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f7f9fc; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .form-container { background-color: #fff; padding: 40px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); width: 100%; max-width: 400px; }
        h1 { text-align: center; color: #333; margin-bottom: 30px; font-size: 2rem; }
        .input-group { margin-bottom: 25px; }
        label { display: block; margin-bottom: 8px; font-weight: 500; color: #555; }
        input[type="email"] { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; font-size: 1rem; transition: border-color 0.3s; }
        input[type="email"]:focus { border-color: #ec5757; outline: none; }
        .submit-btn { width: 100%; padding: 14px; background: linear-gradient(135deg, #ec5757, #c04161); color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 1.1rem; font-weight: 600; transition: opacity 0.3s; }
        .submit-btn:hover { opacity: 0.9; }
        
        /* Message Styling */
        .message { padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-weight: 500; }
        .message.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>Teacher Password Recovery</h1>
        
        <!-- MESSAGE DISPLAY BLOCK -->
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        <!-- END MESSAGE DISPLAY BLOCK -->

        <form method="POST" action="">
            <div class="input-group">
                <label for="email">Enter your Teacher Email Address</label>
                <input type="email" id="email" name="email" required placeholder="e.g., teacher@school.edu">
            </div>
            <button type="submit" class="submit-btn">Send Reset Link</button>
        </form>
        <p style="text-align: center; margin-top: 20px;">
            <a href="teacher_login.php" style="color: #ec5757; text-decoration: none;">Back to Login</a>
        </p>
    </div>
</body>
</html>
