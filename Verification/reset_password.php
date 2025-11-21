<?php
// ==========================================================
// == DEBUGGING: Force display of errors & Logging
// ==========================================================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$log_file = __DIR__ . '/reset_password_errors.log'; 
ini_set('error_log', $log_file);
error_log("--- reset_password.php started --- Request Method: " . ($_SERVER['REQUEST_METHOD'] ?? 'N/A') . " ---");
if(isset($_GET['token'])) { error_log("Token received via GET: " . $_GET['token']); }
if ($_SERVER['REQUEST_METHOD'] === 'POST') { error_log("POST data received: " . print_r($_POST, true)); }
// ==========================================================

// Use session potentially for flash messages, start it
session_start(); 

// --- Database Connection ---
$connPath = __DIR__ . '/../conn.php'; // Use absolute path
if (file_exists($connPath)) {
    require_once $connPath;
    error_log("Included conn.php.");
    if (!isset($conn) || $conn->connect_error) {
        error_log("FATAL ERROR: DB connection failed or \$conn not set. Error: " . ($conn->connect_error ?? 'N/A'));
        die("Database connection error. Please contact support."); // User-friendly death
    }
     error_log("Database connection successful.");
} else {
    error_log("FATAL ERROR: ../conn.php not found at " . $connPath);
    die("System configuration error (DB Connection). Please contact support."); // User-friendly death
}

$message = "";
$messageType = "";
$validToken = false;
$token = "";
$user = null; // Holds user data if token is valid

// --- Validate Token (GET Request) ---
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    error_log("Validating token from GET...");
    
    // Check token and expiry
    // IMPORTANT: Ensure 'token_expiry' is the CORRECT column name in your 'users' table
    $stmt = $conn->prepare("SELECT user_id, username, email FROM users WHERE reset_token=? AND token_expiry > NOW() LIMIT 1");
    if (!$stmt) {
         error_log("FATAL ERROR: Prepare failed (token check): " . $conn->error);
         $message = "Database error during token validation. Please try again later.";
         $messageType = "error";
    } else {
        $stmt->bind_param("s", $token);
        if (!$stmt->execute()) {
             error_log("ERROR: Execute failed (token check): " . $stmt->error);
             $message = "Error validating reset link. Please try again.";
             $messageType = "error";
        } else {
            $result = $stmt->get_result();
            if ($result && $result->num_rows > 0) {
                $user = $result->fetch_assoc();
                $validToken = true;
                error_log("Token is valid for user ID: " . $user['user_id'] . " Username: " . $user['username']);
            } else {
                $message = "This password reset link is invalid or has expired. Please request a new one.";
                $messageType = "error";
                error_log("Token validation failed: No matching valid token found.");
            }
        }
        $stmt->close();
    }
} else {
    $message = "No reset token provided. Please use the link sent to your email.";
    $messageType = "error";
    error_log("No token provided in GET request.");
}

// --- Handle New Password Submission (POST Request) ---
// Only process if the token was initially valid (checked via GET)
if ($_SERVER["REQUEST_METHOD"] == "POST" && $validToken && $user) { // Added check for $user
    error_log("Processing POST request for password update...");
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Re-fetch token from hidden input for security (or could pass via session)
    $posted_token = $_POST['token'] ?? ''; // Assuming you add a hidden input field for the token in the form

     // Double-check the token from POST matches the one validated via GET
     if ($posted_token !== $token) {
          $message = "Token mismatch during submission. Please try again.";
          $messageType = "error";
          error_log("ERROR: Token mismatch between GET ($token) and POST ($posted_token)");
          $validToken = false; // Invalidate the process
     } 
     elseif ($password === $confirmPassword) {
        if (strlen($password) >= 6) {
            error_log("Passwords match and meet length requirement. Hashing new password...");
            // Hash the new password
            $newPasswordHash = password_hash($password, PASSWORD_DEFAULT);
             if ($newPasswordHash === false) {
                  $message = "Error creating password hash. Please try again.";
                  $messageType = "error";
                  error_log("FATAL ERROR: password_hash() failed.");
             } else {
                  error_log("Password hashed successfully. Preparing database update...");
                  // Update database: Set new hash, clear token and expiry
                  // IMPORTANT: Ensure 'password_hash' and 'token_expiry' are CORRECT column names
                  $updateStmt = $conn->prepare("
                      UPDATE users 
                      SET password_hash=?, reset_token=NULL, token_expiry=NULL 
                      WHERE reset_token=? AND user_id=? 
                  "); 
                  // Added user_id check for extra safety

                  if (!$updateStmt) {
                       $message = "Database error preparing update. Please try again.";
                       $messageType = "error";
                       error_log("FATAL ERROR: Prepare failed (password update): " . $conn->error);
                  } else {
                      $updateStmt->bind_param("ssi", $newPasswordHash, $token, $user['user_id']); // Bind hash, token, and user_id

                      if ($updateStmt->execute()) {
                          if ($updateStmt->affected_rows > 0) {
                               $message = "Password successfully reset! You can now log in with your new password.";
                               $messageType = "success";
                               error_log("Password successfully updated for user ID: " . $user['user_id']);
                               // Invalidate token for this request to prevent re-displaying form
                               $validToken = false; 
                               // Optionally: Redirect to login page after a delay
                               // header("Refresh: 5; URL=login.php"); 
                          } else {
                               $message = "Failed to update password. The reset link might have already been used or expired. Please request a new link.";
                               $messageType = "error";
                               error_log("ERROR: Password update query executed but affected 0 rows for user ID: " . $user['user_id'] . " Token: " . $token);
                          }
                      } else {
                          $message = "Failed to execute password update. Please try again.";
                          $messageType = "error";
                          error_log("ERROR: Execute failed (password update): " . $updateStmt->error);
                      }
                      $updateStmt->close();
                  }
             }
        } else {
            $message = "Password must be at least 6 characters long.";
            $messageType = "error";
            error_log("Password length requirement not met.");
        }
    } else {
        $message = "Passwords do not match!";
        $messageType = "error";
        error_log("Password confirmation mismatch.");
    }
} elseif ($_SERVER["REQUEST_METHOD"] == "POST" && !$validToken) {
     // Handle POST attempt with an invalid/expired token from the start
     $message = "Your reset link is invalid or expired. Cannot process request.";
     $messageType = "error";
     error_log("POST received, but token was not valid initially.");
}

// Close DB connection only if it's still open
if ($conn && !$conn->connect_error) {
    $conn->close();
    error_log("Database connection closed.");
}
?>  
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PeakBasa - Reset Password</title>
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
            animation: rotate 4s ease-in-out infinite;
        }

        @keyframes rotate {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(-10deg); }
            75% { transform: rotate(10deg); }
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

        .user-info {
            background: #f0f8ff;
            border-left: 4px solid #ec5757;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 25px;
        }

        .user-info p {
            color: #555;
            font-size: 0.9rem;
            margin: 0;
        }

        .user-info strong {
            color: #ec5757;
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

        input[type="password"] {
            width: 100%;
            padding: 14px 45px 14px 18px; /* Added right padding for icon */
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s;
            background: #fafafa;
        }

        input[type="password"]:focus,
        input[type="text"]:focus { /* Added text type for when toggled */
            outline: none;
            border-color: #ec5757;
            background: white;
            box-shadow: 0 0 0 4px rgba(236, 87, 87, 0.1);
             /* Removed transform scale as it might cause layout shifts */
        }
        
        /* Apply similar styles to text input when password is toggled */
        input[type="text"]#password, 
        input[type="text"]#confirm_password {
             width: 100%;
             padding: 14px 45px 14px 18px; 
             border: 2px solid #e0e0e0;
             border-radius: 12px;
             font-size: 1rem;
             transition: all 0.3s;
             background: #fafafa;
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
             z-index: 2; /* Ensure icon is clickable */
        }

        .toggle-password:hover {
            color: #ec5757;
        }

        .password-strength {
            margin-top: 8px;
            font-size: 0.85rem;
            color: #666;
        }

        .strength-bar {
            height: 4px;
            border-radius: 2px;
            margin-top: 5px;
            background: #e0e0e0;
            overflow: hidden;
        }

        .strength-fill {
            height: 100%;
            width: 0%;
            transition: all 0.3s;
            background: #ccc;
        }

        .strength-weak { width: 33%; background: #f44336; }
        .strength-medium { width: 66%; background: #ff9800; }
        .strength-strong { width: 100%; background: #4caf50; }

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
            margin-top: 10px;
        }

        input[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(236, 87, 87, 0.4);
        }

        input[type="submit"]:active {
            transform: translateY(0);
        }

        input[type="submit"]:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
            box-shadow: none; /* Remove shadow when disabled */
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

        .error-container {
            text-align: center;
        }

        .error-container .logo-icon {
            font-size: 5rem;
        }

        .error-container .btn-primary {
            display: inline-block;
            background: linear-gradient(135deg, #ec5757, #c04161);
            color: white;
            padding: 12px 30px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            margin-top: 20px;
            transition: all 0.3s;
        }

        .error-container .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(236, 87, 87, 0.4);
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
        <?php if ($messageType === "error" && !$validToken): ?>
            <!-- Error State: Invalid or Expired Token (Show only error message and links) -->
            <div class="error-container">
                <div class="logo-section">
                    <div class="logo-icon">⚠️</div>
                    <div class="logo-text">PeakBasa</div>
                </div>
                <h2>Invalid Link</h2>
                <div class="message error">
                    <?php echo htmlspecialchars($message); ?>
                </div>
                <a href="forgot_password.php" class="btn-primary">Request New Link</a>
                <div class="back-link">
                    <a href="login.php">← Back to Login</a>
                </div>
            </div>
        
        <?php elseif ($messageType === "success" && !$validToken): ?>
             <!-- Success State: Password Reset Successfully -->
             <div class="logo-section">
                  <div class="logo-icon">✅</div>
                  <div class="logo-text">PeakBasa</div>
             </div>
             <h2>Password Reset Successful!</h2>
             <div class="message success">
                  <?php echo htmlspecialchars($message); ?>
             </div>
              <div class="back-link" style="margin-top: 30px;">
                   <a href="login.php" class="btn-primary" style="display:inline-block; padding: 12px 30px;">Proceed to Login</a>
              </div>

        <?php elseif ($validToken): ?>
            <!-- Valid Token - Show Reset Form -->
            <div class="logo-section">
                <div class="logo-icon">🔑</div>
                <div class="logo-text">PeakBasa</div>
                <div class="subtitle">Create New Password</div>
            </div>

            <h2>Reset Your Password</h2>
            <p class="description">
                Enter a strong new password for your account.
            </p>

            <?php if ($user): ?>
            <div class="user-info">
                <p>Resetting password for: <strong><?php echo htmlspecialchars($user['username']); ?></strong></p>
            </div>
            <?php endif; ?>

            <!-- Show general error messages related to form submission here -->
            <?php if (!empty($message) && $messageType === "error"): ?>
                <div class="message error">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="reset_password.php?token=<?php echo htmlspecialchars($token); ?>" id="resetForm">
                 <!-- ADDED: Hidden token field for POST verification -->
                 <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                 
                <div class="form-group">
                    <label for="password">New Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" required 
                               placeholder="Enter new password" minlength="6">
                        <span class="toggle-password" onclick="togglePassword('password', this)">👁️</span>
                    </div>
                    <div class="password-strength">
                        <span id="strengthText">Password strength</span>
                        <div class="strength-bar">
                            <div class="strength-fill" id="strengthBar"></div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="confirm_password" name="confirm_password" required 
                               placeholder="Confirm new password" minlength="6">
                        <span class="toggle-password" onclick="togglePassword('confirm_password', this)">👁️</span>
                    </div>
                </div>

                <input type="submit" value="Update Password" id="submitBtn">
            </form>

            <div class="back-link">
                <a href="login.php">← Back to Login</a>
            </div>
        <?php else: ?>
             <!-- Fallback: Should not technically be reachable if logic is sound -->
             <div class="error-container">
                 <div class="logo-section">
                     <div class="logo-icon">❓</div>
                     <div class="logo-text">PeakBasa</div>
                 </div>
                 <h2>Unexpected State</h2>
                 <div class="message error">
                      An unexpected error occurred. Please try requesting a password reset again.
                 </div>
                 <a href="forgot_password.php" class="btn-primary">Request New Link</a>
                  <div class="back-link">
                     <a href="login.php">← Back to Login</a>
                  </div>
             </div>
        <?php endif; ?>
    </div>

    <script>
        function togglePassword(inputId, icon) {
            const passwordInput = document.getElementById(inputId);
            
            if (!passwordInput) return; // Add check if element exists

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.textContent = '🙈';
            } else {
                passwordInput.type = 'password';
                icon.textContent = '👁️';
            }
        }

        // Password strength checker
        const passwordInput = document.getElementById('password');
        const strengthBar = document.getElementById('strengthBar');
        const strengthText = document.getElementById('strengthText');

        if (passwordInput && strengthBar && strengthText) { // Check elements exist
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                let strength = 0;
                let feedbackText = 'Weak password'; // Default text
                let strengthClass = 'strength-weak'; // Default class
                
                if (password.length >= 6) strength++;
                if (password.length >= 10) strength++;
                if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
                if (/\d/.test(password)) strength++;
                if (/[^a-zA-Z0-9\s]/.test(password)) strength++; // Check for non-alphanumeric (excluding space)
                
                // Determine strength based on score
                if (strength <= 2) {
                     strengthClass = 'strength-weak';
                     feedbackText = password.length < 6 ? 'Password too short (min 6 chars)' : 'Weak password';
                } else if (strength <= 3) {
                     strengthClass = 'strength-medium';
                     feedbackText = 'Medium strength';
                } else {
                     strengthClass = 'strength-strong';
                     feedbackText = 'Strong password!';
                }
                
                // Handle empty password case
                if (password.length === 0) {
                     strengthClass = ''; // No bar
                     feedbackText = 'Password strength'; // Reset text
                }

                // Update UI
                strengthBar.className = 'strength-fill ' + strengthClass;
                strengthText.textContent = feedbackText;
            });
        }

        // Password match validation
        const confirmInput = document.getElementById('confirm_password');
        const submitBtn = document.getElementById('submitBtn');
        
        if (confirmInput && passwordInput && submitBtn) { // Check elements exist
            function validateMatch() {
                 let match = true; // Assume match initially
                if (confirmInput.value && passwordInput.value !== confirmInput.value) {
                    confirmInput.style.borderColor = '#f44336'; // Red border
                     match = false;
                } else {
                    confirmInput.style.borderColor = '#e0e0e0'; // Default border
                     // Check if it's not empty before changing to green
                     if (confirmInput.value.length > 0 && passwordInput.value === confirmInput.value) {
                          confirmInput.style.borderColor = '#4caf50'; // Green border for match
                     }
                }
                 // Enable/disable submit button based on match (and potentially strength later)
                 // submitBtn.disabled = !match || passwordInput.value.length < 6; 
                 return match;
            }
            
            confirmInput.addEventListener('input', validateMatch);
            passwordInput.addEventListener('input', validateMatch); // Also validate when main password changes
        }

        // Add loading state to submit button
        const resetForm = document.getElementById('resetForm');
        if (resetForm && submitBtn) { // Check elements exist
            resetForm.addEventListener('submit', function(e) {
                 // Final check before submitting
                 if (passwordInput.value !== confirmInput.value) {
                      e.preventDefault(); // Stop submission
                      // Set message or rely on border color
                       if (typeof showCustomAlert === 'function') { // Use custom alert if defined
                            showCustomAlert('Passwords do not match!');
                       } else {
                            alert('Passwords do not match!');
                       }
                      return false;
                 }
                 if (passwordInput.value.length < 6) {
                      e.preventDefault(); // Stop submission
                       if (typeof showCustomAlert === 'function') {
                            showCustomAlert('Password must be at least 6 characters long.');
                       } else {
                            alert('Password must be at least 6 characters long.');
                       }
                       return false;
                 }
                 
                // If validation passes, show loading
                submitBtn.classList.add('loading');
                submitBtn.value = '';
                submitBtn.disabled = true; // Disable while loading
            });
        }

    </script>
</body>
</html>
