<?php
session_start();

// --- Database Connection ---
require_once '../conn.php';

$message = "";
$messageType = "";
$showForm = true;

// =========================================================
// 1. DYNAMIC ROLE CONFIGURATION
// =========================================================
$role = 'teacher';
$roleTitle = 'Teacher';
$tableName = 'teachers';
$idField = 'teacher_id';
$loginPage = 'teacher_login.php'; 

// =========================================================
// 2. RETRIEVE PENDING USER ID from SESSION
// =========================================================
$pending_user_id = $_SESSION['temp_user'] ?? null; 
$email_to_verify = ""; 

if (empty($pending_user_id)) {
    header("Location: {$loginPage}"); 
    exit;
}

// =========================================================
// NEW BLOCK: Retrieve Email Address for Display
// =========================================================
$email_stmt = $conn->prepare("SELECT email FROM {$tableName} WHERE {$idField} = ? LIMIT 1");
$email_stmt->bind_param("i", $pending_user_id);
$email_stmt->execute();
$email_result = $email_stmt->get_result();

if ($email_result->num_rows === 1) {
    $email_row = $email_result->fetch_assoc();
    $email_to_verify = $email_row['email'];
}
$email_stmt->close();

// =========================================================
// 3. POST REQUEST HANDLING (Verification Logic)
// =========================================================
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $submitted_code = trim($_POST['code'] ?? ''); 

    if (empty($submitted_code)) {
        $message = "Please enter the 6-digit login code.";
        $messageType = "error";
    } else {
        
        // Modified query to include is_banned check
        $stmt = $conn->prepare("SELECT {$idField}, username, is_banned FROM {$tableName} WHERE {$idField} = ? AND login_code = ? AND is_verified = 1 LIMIT 1");
        $stmt->bind_param("is", $pending_user_id, $submitted_code);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user) {
            // ✅ CHECK IF TEACHER IS BANNED
            if ($user['is_banned'] == 1) {
                $message = "⚠️ Your account has been banned. Please contact the administrator for assistance.";
                $messageType = "error";
                $showForm = false;
                
                // Clear the temporary session
                unset($_SESSION['temp_user']);
                unset($_SESSION['temp_email']);
                
                // Clear login code
                $update = $conn->prepare("UPDATE {$tableName} SET login_code = NULL WHERE {$idField} = ?");
                $update->bind_param("i", $pending_user_id);
                $update->execute();
                $update->close();
                
            } else {
                // Teacher is not banned, proceed with login
                
                // 1. Clear the one-time 'login_code'
                $update = $conn->prepare("UPDATE {$tableName} SET login_code = NULL WHERE {$idField} = ?");
                $update->bind_param("i", $user[$idField]);
                $update->execute();
                $update->close();

                // Set session variables
                $_SESSION['teacher_id'] = $user[$idField];
                $_SESSION['teacher_username'] = $user['username'];
                $_SESSION['role'] = $role;
                unset($_SESSION['temp_user']);
                unset($_SESSION['temp_email']);

                $message = "✅ Login successful! Redirecting to the Main Page...";
                $messageType = "success";
                $showForm = false;

                header("Refresh: 2; URL=/PeakBasa/teacher/teacher_dashboard.php");
                exit;
            }

        } else {
            $message = "❌ Invalid login code. Please check the 6-digit code sent to your email and try again.";
            $messageType = "error";
        }
        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Teacher Account - PeakBasa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f7fafc; }
        .msg { border-left-width: 4px; padding: 1rem; margin-bottom: 1.5rem; border-radius: 0.5rem; font-weight: 600; }
        .msg.error { background-color: #fee2e2; border-color: #f87171; color: #b91c1c; }
        .msg.warning { background-color: #fef3c7; border-color: #f59e0b; color: #b45309; }
        .msg.success { background-color: #d1fae5; border-color: #34d399; color: #065f46; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md bg-white p-8 md:p-10 rounded-xl shadow-2xl border border-gray-100">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-extrabold text-indigo-600 mb-2">🔒 Two-Factor Code</h1>
            <p class="text-gray-500">A security code has been sent to your email: <strong><?php echo htmlspecialchars($email_to_verify); ?></strong></p>
        </div>

        <?php if (!empty($message)): ?>
            <div class="msg <?= htmlspecialchars($messageType) ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <?php if ($showForm): ?>
            <form method="POST" class="space-y-6">
                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700 mb-1">Enter Code</label>
                    <input 
                        type="text" id="code" name="code" 
                        maxlength="8" required 
                        placeholder="e.g. 1a3b5c7d"
                        class="mt-1 block w-full text-center text-xl tracking-widest px-4 py-3 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 transition"
                    >
                </div>
                <button 
                    type="submit"
                    class="w-full py-3 px-4 rounded-lg shadow-md text-lg font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition"
                >
                    Verify & Log In
                </button>
            </form>

            <p class="mt-4 text-center text-xs text-gray-500">
                Didn't receive the email? Check your spam folder or re-login.
            </p>
            <div class="mt-4 text-center">
                <a href="<?php echo $loginPage; ?>" class="text-sm text-gray-400 hover:text-indigo-600 transition">← Back to Login</a>
            </div>
        <?php else: ?>
            <!-- Show back to login link when form is hidden due to ban -->
            <div class="text-center mt-6">
                <a href="<?php echo $loginPage; ?>" class="text-indigo-600 hover:text-indigo-800 font-medium">
                    ← Back to Login
                </a>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>