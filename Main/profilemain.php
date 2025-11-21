<?php
session_start();
require_once '../conn.php';

// --- 1. SESSION CHECK ---
// ==================================================================
// == THIS IS THE FIX: Allow EITHER student OR teacher
// ==================================================================
if (!isset($_SESSION['user_id']) && !isset($_SESSION['teacher_id'])) {
    // If NEITHER is set, redirect to login
    header("Location: ../Verification/login.php"); 
    exit;
}

// Determine role and username for display/query purposes
if (isset($_SESSION['teacher_id'])) {
    $session_username = $_SESSION['teacher_username'] ?? 'Teacher'; // Username from teacher session
    $session_role = 'teacher';
    $session_user_id = $_SESSION['teacher_id']; // Use teacher_id if needed elsewhere
} else {
    $session_username = $_SESSION['username'] ?? 'Student'; // Username from student session
    $session_role = 'student';
    $session_user_id = $_SESSION['user_id']; // Use user_id if needed elsewhere
}
// ==================================================================
// == END FIX
// ==================================================================


// --- 2. MESSAGE HANDLING (No changes) ---
$upload_message = "";
$message_type = "";
if (isset($_SESSION['upload_message']) && !empty($_SESSION['upload_message'])) {
    $upload_message = htmlspecialchars($_SESSION['upload_message']);
    
    if (strpos($upload_message, 'successfully') !== false || strpos(strtolower($upload_message), 'uploaded') !== false) {
        $message_type = "success";
    } else {
        $message_type = "error";
    }
    
    unset($_SESSION['upload_message']);
}

// --- 3. DYNAMIC USER & TEACHER QUERY ---
// Use the $session_username and $session_role determined above
$user = null; 

if ($session_role == 'teacher') {
    // Query the teachers table using the teacher's username
    $sql = "SELECT username, email, 'teacher' as role, photo FROM teachers WHERE username=? LIMIT 1";
} else {
     // Query the users table using the student's username
    $sql = "SELECT username, email, role, photo FROM users WHERE username=? LIMIT 1";
}

$stmt = $conn->prepare($sql);
// Bind the correct username based on the session role
$stmt->bind_param("s", $session_username); 
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
} else {
    // Handle case where user might exist in session but not DB (less likely now with login checks)
     $user = ['username' => $session_username, 'role' => $session_role, 'email' => 'N/A', 'photo' => null]; // Provide defaults
}
$stmt->close(); // Close the statement here

// --- 4. FINAL CHECK & REDIRECT (Modified slightly) ---
// This check might be less critical now if login guarantees DB entry, but kept for safety
if ($user === null) { 
    // Fallback if DB query failed unexpectedly
    session_unset();
    session_destroy();
    // Redirect based on inferred role or just to a general login
    $loginPage = ($session_role == 'teacher') ? '../Verification/teacher_login.php' : '../Verification/login.php';
    header("Location: $loginPage?error=UserNotFound");
    exit;
}

// --- 5. PROFILE PICTURE LOGIC ---
// Use the $user array fetched above
$default_pic = '../ui/blank.png'; 
$profile_pic = $default_pic; 

if (!empty($user['photo'])) {
    $filename = $user['photo'];

    $web_upload_path = '../uploads/profile_pics/' . $filename;
    $web_avatar_path = '../avatars/' . $filename;
    
    // Construct absolute server paths correctly
    $docRoot = rtrim($_SERVER['DOCUMENT_ROOT'], '/'); // Ensure no trailing slash
    $basePath = $docRoot . '/PeakBasa'; 
    
    $server_upload_path = $basePath . '/uploads/profile_pics/' . $filename;
    $server_avatar_path = $basePath . '/avatars/' . $filename;

    if (file_exists($server_upload_path)) {
        $profile_pic = $web_upload_path;
    } elseif (file_exists($server_avatar_path)) {
        $profile_pic = $web_avatar_path;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PeakBasa - User Profile</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #e3f2fd 0%, #fce4ec 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .profile-container {
            width: 100%;
            max-width: 900px;
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 30px;
            animation: fadeIn 0.6s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .profile-card {
            background: white;
            border-radius: 24px;
            padding: 40px 30px;
            box-shadow: 0 10px 40px rgba(236, 87, 87, 0.15);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .profile-header {
            position: relative;
            margin-bottom: 20px;
        }

        .profile-avatar {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            border: 5px solid #ec5757;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(236, 87, 87, 0.3);
            position: relative;
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-badge {
            position: absolute;
            bottom: 5px;
            right: 5px;
            background: linear-gradient(135deg, #ec5757, #ff8787);
            color: white;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            border: 3px solid white;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        .profile-name {
            font-size: 28px;
            font-weight: 700;
            color: #ec5757;
            margin: 15px 0 5px;
        }

        .profile-role {
            font-size: 16px;
            color: #78858F;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .profile-email {
            margin-top: 15px;
            padding: 12px 20px;
            background: #f8f9fa;
            border-radius: 12px;
            color: #666;
            font-size: 14px;
            width: 100%;
            word-break: break-all; /* ADDED: To prevent email overflow */
        }

        .profile-actions {
            margin-top: 25px;
            display: flex;
            gap: 12px;
            width: 100%;
        }

        .btn {
            flex: 1;
            padding: 12px 20px;
            border: none;
            border-radius: 12px;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #ec5757, #ff8787);
            color: white;
            box-shadow: 0 4px 15px rgba(236, 87, 87, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(236, 87, 87, 0.4);
        }

        .btn-secondary {
            background: white;
            color: #ec5757;
            border: 2px solid #ec5757;
        }

        .btn-secondary:hover {
            background: #ec5757;
            color: white;
        }

        .customization-panel {
            background: white;
            border-radius: 24px;
            padding: 35px;
            box-shadow: 0 10px 40px rgba(236, 87, 87, 0.15);
        }

        .panel-header {
            margin-bottom: 25px;
        }

        .panel-title {
            font-size: 24px;
            font-weight: 700;
            color: #ec5757;
            margin-bottom: 8px;
        }

        .panel-subtitle {
            font-size: 14px;
            color: #78858F;
        }

        .upload-section {
            background: linear-gradient(135deg, #fff5f5, #ffe8e8);
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 30px;
            border: 2px dashed #ec5757;
            text-align: center;
        }

        .upload-label {
            display: block;
            cursor: pointer;
            color: #ec5757;
            font-weight: 600;
            margin-bottom: 15px;
            font-size: 15px;
        }

        input[type="file"] {
            display: none;
        }

        .file-name {
            font-size: 13px;
            color: #78858F;
            margin: 10px 0;
            min-height: 20px;
            word-break: break-all; /* ADDED: To prevent file name overflow */
        }

        .upload-btn {
            background: linear-gradient(135deg, #ec5757, #ff8787);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 12px;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(236, 87, 87, 0.3);
        }

        .upload-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(236, 87, 87, 0.4);
        }

        .divider {
            height: 1px;
            background: linear-gradient(to right, transparent, #ec5757, transparent);
            margin: 25px 0;
        }

        .avatars-header {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
        }

        .avatar-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(70px, 1fr));
            gap: 12px;
            max-height: 280px;
            overflow-y: auto;
            padding: 5px;
        }

        .avatar-grid::-webkit-scrollbar {
            width: 6px;
        }

        .avatar-grid::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .avatar-grid::-webkit-scrollbar-thumb {
            background: #ec5757;
            border-radius: 10px;
        }

        .avatar-option {
            border: none;
            background: none;
            padding: 0;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .avatar-option img {
            width: 70px;
            height: 70px;
            border-radius: 16px;
            border: 3px solid transparent;
            transition: all 0.3s ease;
            object-fit: cover;
        }

        .avatar-option:hover img {
            border-color: #ec5757;
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(236, 87, 87, 0.3);
        }

        .message {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 12px;
            color: white;
            font-weight: 500;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            animation: slideIn 0.3s ease;
            z-index: 1000;
        }

        .message.success {
            background: linear-gradient(135deg, #4caf50, #66bb6a);
        }

        .message.error {
            background: linear-gradient(135deg, #f44336, #ef5350);
        }

        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* This is the media query that fixes the layout on mobile */
        @media (max-width: 768px) {
            .profile-container {
                grid-template-columns: 1fr;
            }

            .profile-actions {
                flex-direction: column;
            }

            .avatar-grid {
                grid-template-columns: repeat(auto-fill, minmax(60px, 1fr));
            }

            .avatar-option img {
                width: 60px;
                height: 60px;
            }
            
            /* ADDED: Center-align text in the customization panel on mobile */
            .customization-panel {
                text-align: center; /* Center aligns text like headers */
            }
            /* Ensure avatar grid itself doesn't center its items internally */
             .avatar-grid {
                text-align: left; /* Keep grid items aligned left */
             }
        }
        
        /* ADDED: Extra query for very small screens */
        @media (max-width: 400px) {
            body {
                padding: 10px;
            }
            .profile-card, .customization-panel {
                padding: 20px;
            }
            .profile-avatar {
                width: 120px;
                height: 120px;
            }
            .profile-name {
                font-size: 24px;
            }
            .panel-title {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <?php if (!empty($upload_message)): ?>
        <div class="message <?php echo $message_type; ?>">
            <?php echo $upload_message; ?>
        </div>
    <?php endif; ?>

    <div class="profile-container">
        <!-- Left Card: Profile Info -->
        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-avatar">
                    <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile Picture">
                </div>
                <div class="profile-badge">👤</div>
            </div>

            <!-- Display username and role from the fetched $user array -->
            <h1 class="profile-name"><?php echo htmlspecialchars($user['username']); ?></h1>
            <p class="profile-role"><?php echo ucfirst($user['role']); ?></p> 
            
            <div class="profile-email">
                 📧 <?php echo htmlspecialchars($user['email'] ?? 'Email not available'); // Use fetched email ?>
            </div>

            <div class="profile-actions">
                 <!-- Link to main.php (Progress Map) -->
                 <a href="main.php" class="btn btn-primary"> 
                     🏠 Main Menu
                 </a>
                <a href="../Verification/logout.php" class="btn btn-secondary">
                    🚪 Logout
                </a>
            </div>
        </div>

        <!-- Right Panel: Customization -->
        <div class="customization-panel">
            <div class="panel-header">
                <h2 class="panel-title">Customize Your Profile</h2>
                <p class="panel-subtitle">Upload a photo or choose an avatar</p>
            </div>

            <!-- Upload Section -->
            <div class="upload-section">
                <form action="../Profile/upload_avatar.php" method="POST" enctype="multipart/form-data" id="uploadForm">
                    <label for="fileInput" class="upload-label">    
                        📤 Click to Upload Photo
                    </label>
                    <input type="file" id="fileInput" name="profile_pic" accept="image/*" onchange="displayFileName()">
                    <div class="file-name" id="fileName"></div>
                    <button type="submit" class="upload-btn">Upload & Save</button>
                </form> 
            </div> <!-- End Upload Section -->

            <div class="divider"></div>

            <div class="avatars-header">
                Or choose a pre-made avatar:
            </div>

            <form action="../Profile/update_avatar.php" method="POST">
    
                <div class="avatar-grid">
                    
                    <button type="submit" name="avatar" value="1.png" class="avatar-option" title="Select Avatar 1">
                        <img src="https://peakbasa.site/PeakBasa/avatars/1.png" alt="Avatar 1">
                    </button>

                    <button type="submit" name="avatar" value="2.png" class="avatar-option" title="Select Avatar 2">
                        <img src="https://peakbasa.site/PeakBasa/avatars/2.png" alt="Avatar 2">
                    </button>

                    <button type="submit" name="avatar" value="3.png" class="avatar-option" title="Select Avatar 3">
                        <img src="https://peakbasa.site/PeakBasa/avatars/3.png" alt="Avatar 3">
                    </button>

                    <button type="submit" name="avatar" value="Blue-Bird.png" class="avatar-option" title="Select Avatar 4">
                        <img src="https://peakbasa.site/PeakBasa/avatars/Blue-Bird.png" alt="Avatar 4">
                    </button>
                    <!-- Add more avatar buttons as needed -->
                            
                </div>
            </form>
                
        </div> <!-- End Customization Panel -->
    </div> <!-- End Profile Container -->

    <script>
        function displayFileName() {
            const input = document.getElementById('fileInput');
            const fileNameDisplay = document.getElementById('fileName');
            
            if (input.files.length > 0) {
                fileNameDisplay.textContent = '📁 ' + input.files[0].name;
            } else {
                fileNameDisplay.textContent = '';
            }
        }

        // Auto-hide message after 5 seconds
        const message = document.querySelector('.message');
        if (message) {
            setTimeout(() => {
                message.style.animation = 'slideIn 0.3s ease reverse';
                setTimeout(() => message.remove(), 300);
            }, 5000);
        }
    </script>
</body>
</html>

