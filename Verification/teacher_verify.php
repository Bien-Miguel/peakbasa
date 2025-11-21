<?php
session_start();
// --- Database Connection ---
// Assuming teacher_login_verify.php is inside a folder like 'teacher/' or 'Verification/'
require_once '../conn.php';

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // The verification code is 8 characters long (bin2hex(random_bytes(4)))
    $code = trim($_POST['code'] ?? ''); 

    if (empty($code)) {
        $message = "❌ Please enter a verification code.";
        $messageType = "error";
    } else {
        // 1. Check if the user is already verified (in case they navigated back)
        $stmt_check = $conn->prepare("SELECT is_verified FROM teachers WHERE verification_code=?");
        $stmt_check->bind_param("s", $code);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            $row = $result_check->fetch_assoc();
            if ($row['is_verified'] == 1) {
                $message = "⚠️ Your account is **already verified**. Please proceed to the Teacher Login page.";
                $messageType = "warning";
                // Redirect after success
                echo "<script>setTimeout(()=>window.location.href='teacher_login.php', 3000);</script>";
                goto skip_verification;
            }
        }
        $stmt_check->close();

        // 2. Proceed with verification if not yet verified (is_verified=0)
        $stmt = $conn->prepare("SELECT teacher_id FROM teachers WHERE verification_code=? AND is_verified=0");
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            // Update the teacher status: set is_verified=1 and clear the code
            $update = $conn->prepare("UPDATE teachers SET is_verified=1, verification_code=NULL WHERE verification_code=?");
            $update->bind_param("s", $code);
            $update->execute();

            // Success message tailored for teachers
            $message = "✅ Teacher Email verified! You can now log in to your account.";
            $messageType = "success";
            
            // Redirect after success
            echo "<script>setTimeout(()=>window.location.href='teacher_login.php', 2000);</script>";

        } else {
            // This covers: 
            // a) The code is wrong/does not exist.
            // b) The code was already used and cleared (NULL).
            $message = "❌ Invalid or expired verification code. Please check the code sent to your email and try again.";
            $messageType = "error";
        }
    }
}
skip_verification: // Label for the goto statement to bypass subsequent logic after an early exit
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Email Verification | PeakBasa</title>
    <!-- Load Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0fff4; /* Light green background to match teacher theme */
        }
        
        /* Custom styles using Tailwind colors for consistency */
        .message-box {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-top: 1.25rem;
            font-weight: 500;
        }

        .message-box.error {
            background-color: #fee2e2; border-color: #f87171; color: #b91c1c; border-left: 4px solid #f87171;
        }

        .message-box.success {
            background-color: #d1fae5; border-color: #34d399; color: #065f46; border-left: 4px solid #34d399;
        }
        
        .message-box.warning {
            background-color: #fef3c7; border-color: #fcd34d; color: #92400e; border-left: 4px solid #fcd34d;
        }

        .code-input {
            letter-spacing: 5px;
            text-align: center;
        }

        .code-input:focus {
            border-color: #10b981; /* Green focus ring */
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.4);
        }

        .btn-verify {
            background-color: #10b981;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
            transition: all 0.2s;
        }
        
        .btn-verify:hover {
            background-color: #059669;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.6);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    
    <div class="w-full max-w-md bg-white p-8 md:p-10 rounded-xl shadow-2xl border border-gray-100">
        
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="text-4xl mb-4">📧</div>
            <h2 class="text-3xl font-extrabold text-green-700 mb-2">Verify Teacher Email</h2>
            <p class="text-gray-500">
                Please enter the **8-character** code sent to your email to activate your account.
            </p>
        </div>

        <!-- Verification Form -->
        <form method="POST" action="teacher_verify.php" class="space-y-6">
            <input 
                type="text" 
                name="code" 
                placeholder="********" 
                maxlength="8" 
                required 
                autofocus
                class="code-input mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-4 transition duration-150 text-xl font-mono uppercase"
            >
            
            <button 
                type="submit" 
                class="btn-verify w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-md text-lg font-medium text-white"
            >
                Complete Verification
            </button>
        </form>

        <!-- Status Message Display -->
        <?php if (!empty($message)): ?>
            <div class="message-box <?php echo htmlspecialchars($messageType); ?>" role="alert">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <p class="mt-6 text-center text-sm text-gray-500">
            Need to return? 
            <a href="teacher_register.php" class="font-medium text-green-600 hover:text-green-500 transition duration-150">
                Back to Registration
            </a>
            or 
            <a href="teacher_login.php" class="font-medium text-green-600 hover:text-green-500 transition duration-150">
                Go to Login
            </a>
        </p>

    </div>
</body>
</html>
