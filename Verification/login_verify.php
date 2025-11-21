<?php
session_start();
require_once '../conn.php';

$message = "";
$messageType = "";
$user_id = $_SESSION['temp_user'] ?? null;
$showForm = true;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $code = trim($_POST['code'] ?? '');

    if (!$user_id) {
        $message = "❌ Session expired. Please log in again.";
        $messageType = "error";
        $showForm = false;
    } else if (empty($code)) {
        $message = "Please enter the 6-digit code.";
        $messageType = "warning";
    } else {
        // Modified query to include is_banned check
        $stmt = $conn->prepare("SELECT user_id, username, role, is_banned FROM users WHERE user_id=? AND login_code=?");
        
        $stmt->bind_param("is", $user_id, $code);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            
            // ✅ CHECK IF USER IS BANNED
            if ($row['is_banned'] == 1) {
                $message = "⚠️ Your account has been banned. Please contact support for assistance.";
                $messageType = "error";
                $showForm = false;
                
                // Clear the temporary session
                unset($_SESSION['temp_user']);
                
                // Clear login code
                $update_code = $conn->prepare("UPDATE users SET login_code=NULL WHERE user_id=?");
                $update_code->bind_param("i", $user_id);
                $update_code->execute();
                
            } else {
                // User is not banned, proceed with login
                $verified_user_id = $row['user_id'];

                // Set final session variables
                $_SESSION['user_id'] = $verified_user_id;
                $_SESSION['username'] = $row['username'];
                $_SESSION['role'] = $row['role'];
                unset($_SESSION['temp_user']);

                // Clear login code
                $update_code = $conn->prepare("UPDATE users SET login_code=NULL WHERE user_id=?");
                $update_code->bind_param("i", $verified_user_id);
                $update_code->execute();

                $message = "✅ Login verified! Redirecting to main page...";
                $messageType = "success";
                $showForm = false;
                
                // Redirect after a short delay
                echo "<script>setTimeout(()=>window.location.href='../Main/main.php', 1500);</script>";
            }

        } else {
            $message = "❌ Invalid or expired code. Please try again or re-login.";
            $messageType = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f7fafc; 
        }
        .message-box.error {
            background-color: #fee2e2; border-color: #f87171; color: #b91c1c; 
        }
        .message-box.warning {
            background-color: #fef3c7; border-color: #f59e0b; color: #b45309;
        }
        .message-box.success {
            background-color: #d1fae5; border-color: #34d399; color: #065f46; 
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md bg-white p-8 md:p-10 rounded-xl shadow-2xl border border-gray-100">
        
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-extrabold text-indigo-600 mb-2">
                🔒 Two-Factor Code
            </h1>
            <p class="text-gray-500">
                A 6-digit security code has been sent to your email.
            </p>
        </div>

        <!-- Status Message -->
        <?php if (!empty($message)): ?>
            <?php
                $bg_color = '';
                if ($messageType === 'error') $bg_color = 'message-box error';
                if ($messageType === 'warning') $bg_color = 'message-box warning';
                if ($messageType === 'success') $bg_color = 'message-box success';
            ?>
            <div class="p-4 mb-6 rounded-lg border-l-4 font-semibold text-sm <?php echo $bg_color; ?>" role="alert">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Verification Form -->
        <?php if ($showForm): ?>
            <form method="POST" action="login_verify.php" class="space-y-6">

                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700 mb-1">Enter Code</label>
                    <input 
                        type="text" 
                        id="code" 
                        name="code" 
                        placeholder="e.g., 123456"
                        required 
                        maxlength="6"
                        class="mt-1 block w-full text-center text-xl tracking-widest px-4 py-3 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 transition duration-150"
                    >
                </div>

                <button 
                    type="submit" 
                    class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-md text-lg font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out"
                >
                    Verify & Log In
                </button>
            </form>
            
            <p class="mt-4 text-center text-xs text-gray-500">
                If the email hasn't arrived, ensure you check your spam folder.
            </p>
        <?php else: ?>
            <!-- Show back to login link when form is hidden -->
            <div class="text-center">
                <a href="login.php" class="text-indigo-600 hover:text-indigo-800 font-medium">
                    ← Back to Login
                </a>
            </div>
        <?php endif; ?>

    </div>

</body>
</html>