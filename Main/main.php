<?php
session_start();
require_once '../conn.php';
// NEW CHECK (Allows EITHER student OR teacher)
if (!isset($_SESSION['user_id']) && !isset($_SESSION['teacher_id'])) {
    // If NEITHER a student user_id NOR a teacher_id is set, THEN redirect to login.
    // Choose the appropriate login page to redirect to, maybe the main student one?
    header("Location: ../Verification/login.php"); 
    exit;
}

// --- IF YOU NEED THE USERNAME later on the page ---
// Determine username based on role (handle potential conflicts if student/teacher share usernames)
$is_teacher = false; // Initialize to false
$is_admin = false; // Initialize admin flag
if (isset($_SESSION['teacher_id'])) {
    $username = $_SESSION['teacher_username'] ?? 'Teacher'; 
    $user_role = 'teacher';
    $is_teacher = true; // Set flag for teacher
    // If you need the ID: $id = $_SESSION['teacher_id'];
} elseif (isset($_SESSION['user_id'])) { // Check student/admin based on user_id
    $username = $_SESSION['username'] ?? 'User'; // Default if username somehow missing 
    $user_role = $_SESSION['role'] ?? 'student'; // Get role from session
    $is_teacher = false; 
    if ($user_role === 'admin') {
         $is_admin = true; // Set admin flag
    }
     // If you need the ID: $id = $_SESSION['user_id'];
} else {
     // Fallback, should not be reached due to initial check
      header("Location: ../Verification/login.php"); 
      exit;
}


// MANDATORY: Include the Progress Manager Class
// Use absolute path for reliability
$pmPath = __DIR__ . '/../quiz/ProgressManager.php'; 
// Use require_once to prevent re-declaration issues if included elsewhere
if (file_exists($pmPath)) {
    require_once $pmPath; 
    if (!class_exists('ProgressManager')) {
        die("FATAL ERROR: Progress Manager class not found after include."); // Or handle more gracefully
    }
} else {
    die("FATAL ERROR: Progress Manager file not found at $pmPath");
}


// --- 1. Progress Retrieval (Conditional) ---
// Initialize variables with defaults (important for teacher/admin view)
$current_level = 1; 
$total_stars = 0;
$progress_percentage = 0;
$level_status_text = "N/A"; // Default for teacher/admin or error
$button_text = "▶️ Start Learning"; // Default button
$button_target = "../quiz/quiz_menu.php?level=1"; // Default target
$total_levels = 4; // Assume 4 levels total based on your map
$quizzes_per_level = 3; // Assume 3 quizzes per level
$current_level_for_display = 1; // Default level access

// ONLY fetch individual progress if it's a STUDENT
if ($user_role === 'student' && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id']; // Use the student's ID

    // Use database constants from conn.php if defined

try {

        $dbHost = "localhost";
        $dbUser = "u967494580_peak";  // Check your conn.php if this is different
        $dbPass = "YOUR_DATABASE_PASSWORD"; // ⚠️ REPLACE THIS with your actual DB password from conn.php
        $dbName = "u967494580_peak";


        $pm = new ProgressManager($dbHost, $dbUser, $dbPass, $dbName);
        $progress = $pm->getUserProgress($user_id);
        $progress = $pm->getUserProgress($user_id);

        $current_level = $progress['current_level']; // This is the highest level the student has *access* to
        $total_stars = $progress['total_stars'];
        
        // --- Re-calculate metrics based on fetched student data ---
        $total_possible_quizzes = $total_levels * $quizzes_per_level;
        
        // Calculate completed quizzes based on the level BEFORE the current one
        $completed_levels = max(0, $current_level - 1);
        $total_quizzes_completed = $completed_levels * $quizzes_per_level; 
        
        $progress_percentage = ($total_possible_quizzes > 0) ? round(($total_quizzes_completed / $total_possible_quizzes) * 100) : 0;

        $level_status_text = "$completed_levels/$total_levels";

        // --- Determine Button ---
        $quiz_menu_path = "../quiz/quiz_menu.php"; // Use fixed quiz menu link
        if ($current_level > $total_levels) {
            // Student has completed all levels (current_level might be 5)
            $button_text = "🏆 All Levels Complete!";
            $button_target = "#"; 
            $current_level_for_display = $total_levels + 1; // Set effective level beyond max for unlocking logic
        } else {
            // Student is on a valid level
             $button_text = "▶️ Continue Level $current_level";
             $button_target = "{$quiz_menu_path}?level={$current_level}";
             $current_level_for_display = $current_level; // Use actual level for unlocking
        }
        
        unset($pm); // Clean up
        
    } catch (Exception $e) {
        error_log("Failed to get student progress: " . $e->getMessage());
        // Keep default values, maybe set an error message in session?
         $_SESSION['result_message'] = "Error fetching your progress.";
         // Set defaults again just in case
         $current_level = 1; $total_stars = 0; $progress_percentage = 0; $level_status_text = "Error";
         $button_text = "Error"; $button_target = "#"; $current_level_for_display = 1;
    }
} else if ($is_teacher || $is_admin) { // Teacher or Admin View
     // Set defaults or specific views for teacher/admin
     $level_status_text = "N/A";
     $total_stars = "N/A";
     $progress_percentage = "N/A";
     $button_text = ($is_admin) ? "🔑 Admin Area" : "📚 View Quizzes"; // Different button for admin?
     // Point admin to admin messages, teacher to quiz menu
     $button_target = ($is_admin) ? "../adminPage/admin_messages.php" : "../quiz/quiz_menu.php?level=1"; 
     $current_level_for_display = $total_levels + 1; // Unlock all levels for teacher/admin
     
     // Include conn.php for teacher quick view or admin checks if needed
     if (!isset($conn) || !$conn instanceof mysqli || $conn->connect_error) { 
         $connPath = __DIR__ . '/../conn.php';
          if(file_exists($connPath)){
               require_once $connPath;
               if (!isset($conn) || !$conn instanceof mysqli || $conn->connect_error) {
                    error_log("Failed to establish DB connection for teacher/admin view in main.php");
                    $conn = null; // Ensure conn is null if connection failed
               }
          } else {
                error_log("conn.php not found for teacher/admin view in main.php");
                $conn = null;
          }
     }

     // Fetch Teacher Quick View Data (Only if teacher)
     $total_students = 0;
     $class_avg_score = 0;
     if ($is_teacher && $conn) { // Check connection object exists and is valid
         $sql_quickview = "
             SELECT 
                 COUNT(DISTINCT u.user_id) AS student_count,
                 ROUND(AVG(us.stars_earned), 2) AS class_average
             FROM users u
             LEFT JOIN user_scores us ON u.user_id = us.user_id
             WHERE u.role = 'student'
         ";
         $result_quickview = $conn->query($sql_quickview);
         if ($result_quickview && $result_quickview->num_rows > 0) {
             $quickview_data = $result_quickview->fetch_assoc();
             $total_students = $quickview_data['student_count'];
             $class_avg_score = $quickview_data['class_average'] ?? 0;
         } else if ($conn->error) {
              error_log("Teacher Quick View Query Error: " . $conn->error);
         }
         // Don't close $conn here if conn.php keeps it open
     } elseif ($is_teacher && !$conn) {
          error_log("Teacher Quick View: Database connection not available.");
     }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>PeakBasa - Level Menu</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* --- Styles remain the same as previous version --- */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to bottom, #a3d5f7, #e3f2fd);
            margin: 0;
            overflow-x: hidden;
            min-height: 100vh;
        }

        .header-section {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1001;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo-toggle {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
            transition: 0.3s;
        }

        .logo-toggle:hover {
            transform: scale(1.05);
        }

        .logo-toggle img {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }

        .header-title {
            color: #ec5757;
            font-size: 1.8rem;
            font-weight: bold;
            margin: 0;
            text-shadow: 0 1px 3px rgba(0,0,0,0.1);
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        .header-title.hidden {
            opacity: 0;
            visibility: hidden;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: -300px;
            width: 300px;
            height: 100vh;
            background: #fff;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            transition: left 0.3s ease;
            z-index: 999;
            padding-top: 20px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }

        .sidebar.active {
            left: 0;
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
            text-align: center;
        }

        .sidebar-header h2 {
            color: #ec5757;
            margin: 0 0 10px 0;
            font-size: 1.5rem;
        }

        .sidebar-header p {
            color: #666;
            margin: 0;
        }

        .nav-links {
            padding: 20px 0;
            flex-grow: 1;
        }

        .nav-links a {
            display: block;
            padding: 15px 25px;
            color: #333;
            text-decoration: none;
            font-weight: 500;
            transition: 0.3s;
            border-left: 3px solid transparent;
        }

        /* Style for active link */
        .nav-links a.active-link {
             background: #fdf0f0; /* Light pink background */
             border-left-color: #ec5757; /* Red border */
             color: #ec5757; /* Red text */
             font-weight: 600; /* Slightly bolder */
        }


        .nav-links a:hover:not(.active-link) { /* Hover effect only if not active */
            background: #f5f5f5;
            border-left-color: #ec5757;
            color: #ec5757;
        }

        .logout-section {
            margin-top: auto;
            border-top: 1px solid #eee;
            padding: 15px;
        }

        .logout-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 12px 20px;
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .logout-btn:hover {
            background: linear-gradient(135deg, #c0392b, #a93226);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(231, 76, 60, 0.4);
        }

        .logout-btn:active {
            transform: translateY(0);
        }

        /* Left Side Panel */
        .left-form-panel {
            position: fixed;
            left: 20px;
            top: 100px;
            width: 340px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.12);
            padding: 30px;
            z-index: 100;
        }

        .left-form-panel h3 {
            color: #ec5757;
            margin: 0 0 25px 0;
            font-size: 1.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .progress-stats {
            margin-bottom: 25px;
        }

        .stat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .stat-item:last-child {
            border-bottom: none;
        }

        .stat-label {
            color: #666;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .stat-value {
            color: #ec5757;
            font-weight: 700;
            font-size: 1.2rem;
        }
        /* Specific value style for N/A */
        .stat-value.na {
             color: #999;
             font-style: italic;
             font-weight: 500;
        }


        .progress-bar-container {
            margin: 25px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 12px;
        }

        .progress-bar-label {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 0.95rem;
            color: #666;
            font-weight: 500;
        }
        
        /* Style for N/A percentage */
         .progress-bar-label span.na {
             font-style: italic;
             color: #999;
        }

        .progress-bar {
            width: 100%;
            height: 14px;
            background: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #ec5757, #ff8787);
            border-radius: 10px;
            transition: width 0.5s ease;
        }

        /* --- Teacher Quick View Styles --- */
        .teacher-quick-view {
            margin: 30px 0 0 0;
            padding-top: 25px;
            border-top: 2px solid #eee;
        }
        .teacher-quick-view h3 {
            font-size: 1.4rem; /* Slightly smaller */
            margin-bottom: 20px;
        }
        .teacher-quick-view .stat-item {
            padding: 14px 0;
        }
        .teacher-quick-view .stat-value {
            color: #1e88e5; /* Different color for teacher stats */
        }

        .quick-actions {
            margin-top: 25px;
        }

        .action-btn {
            width: 100%;
            padding: 14px;
            margin-bottom: 12px;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
            color: inherit;
        }
        
        .action-btn:last-child {
            margin-bottom: 0;
        }

        .action-btn.primary {
            background: linear-gradient(135deg, #ec5757, #ff8787);
            color: white;
        }
         /* Disabled state for primary button */
         .action-btn.primary[href="#"] {
             opacity: 0.6;
             cursor: not-allowed;
             pointer-events: none; /* Prevent click */
              box-shadow: none; /* Remove shadow when disabled */
         }
         .action-btn.primary[href="#"]:hover {
              transform: none; /* Remove hover effect when disabled */
         }

        .action-btn.primary:hover:not([href="#"]) { /* Only hover if not disabled */
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(236, 87, 87, 0.4);
        }

        .action-btn.secondary {
            background: #f8f9fa;
            color: #333;
            border: 2px solid #e9ecef;
        }

        .action-btn.secondary:hover {
            background: #e9ecef;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        /* Main content */
        .main-content {
            margin-left: 390px;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            flex-direction: column; /* Stack children vertically */
            align-items: center;
            justify-content: flex-start; /* Align from top */
            padding-top: 80px;
        }


        /* Map Container */
        .map-container {
            position: relative;
            width: 100%;
            max-width: 900px;
            height: 0;
            padding-bottom: 66.67%; /* Aspect ratio for the map image */
            margin: 20px auto 0; /* Adjusted margin */
        }

        /* Map Background */
        .map-background {
            position: absolute;
            width: 100%;
            height: 100%;
            object-fit: contain;
            z-index: 1;
            top: 0;
            left: 0;
        }

        /* Custom Connection Lines */
        .path-line {
            position: absolute;
            background: linear-gradient(90deg,
                #8bc34a 0%, #8bc34a 25%, transparent 25%, transparent 50%,
                #8bc34a 50%, #8bc34a 75%, transparent 75%, transparent 100%
            );
            background-size: 20px 100%;
            height: 4px; z-index: 5; opacity: 0.8;
            transform-origin: left center; pointer-events: none;
        }
        #path1 { bottom: 17%; left: 42%; width: 38%; transform: rotate(-38deg); }
        #path2 { bottom: 50%; left: 30%; width: 44%; transform: rotate(15deg); }
        #path3 { top: 50%; left: 30%; width: 38%; transform: rotate(-43deg); }

        /* Level Buttons */
        .level-button {
            position: absolute; width: 10%; padding-bottom: 10%;
            border-radius: 50%; border: 4px solid white; cursor: pointer;
            font-size: clamp(1.5rem, 2.5vw, 2.5rem); display: flex;
            align-items: center; justify-content: center; background: white;
            box-shadow: 0 6px 20px rgba(0,0,0,0.2); transition: all 0.3s ease;
            z-index: 10; text-decoration: none; color: #333;
        }
        .level-button span {
            position: absolute; top: 50%; left: 50%;
            transform: translate(-50%, -50%);
        }
        .level-button:hover:not(.locked) { /* Prevent hover effect on locked buttons */
            transform: scale(1.15) translateY(-5px);
            box-shadow: 0 10px 30px rgba(236, 87, 87, 0.4);
        }
        .level-button.locked {
            opacity: 0.4; cursor: not-allowed; background: #e9ecef;
            filter: grayscale(100%); pointer-events: none;
        }
        /* Level Positioning */
        #level1 { bottom: 12%; left: 37%; }
        #level2 { bottom: 35%; left: 68%; }
        #level3 { top: 45%; left: 25%; }
        #level4 { top: 20%; right: 35%; } /* Adjusted slightly right */

        /* Overlay */
        .overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5); z-index: 998; opacity: 0;
            visibility: hidden; transition: 0.3s;
        }
        .overlay.active { opacity: 1; visibility: visible; }

        /* Result Message */
        .result-message {
             padding: 15px; margin: 0 auto 20px; 
             max-width: 600px; border-radius: 8px; text-align: center;
             font-weight: bold; border: 1px solid;
             position: relative; 
             top: auto; left: auto; transform: none; 
             z-index: 1002; width: 100%; /* Make full width relative to parent */
             opacity: 1; transition: opacity 0.5s ease, max-height 0.5s ease, margin 0.5s ease, padding 0.5s ease;
             max-height: 100px; 
             overflow: hidden;
        }
        .result-message.hidden {
             opacity: 0; max-height: 0; /* Collapse when hidden */
             margin-top: 0; margin-bottom: 0; padding-top: 0; padding-bottom: 0;
             border: none; /* Hide border when collapsed */
             pointer-events: none;
        }
        .result-message.success {
             background-color: #d4edda; border-color: #c3e6cb; color: #155724;
        }
        .result-message.error {
             background-color: #f8d7da; border-color: #f5c6cb; color: #721c24;
        }

        /* Tablet and Mobile Responsive */
        @media (max-width: 1200px) {
            .main-content {
                 margin-left: 0; padding: 20px; display: block; padding-top: 80px; /* Reset display */
            }
            .left-form-panel {
                 position: relative; 
                 width: 90%; max-width: 400px; margin: 20px auto 30px; 
                 top: 0; left: 0; 
                 z-index: 100; 
            }
             /* Remove bottom margin from header on larger mobile screens */
             .header-section { margin-bottom: 0; } 

             .map-container { max-width: 700px; margin-top: 20px; } 
        }

        @media (max-width: 768px) {
            .header-section { top: 15px; left: 15px; }
            .header-title { font-size: 1.5rem; }
            .logo-toggle img { width: 40px; height: 40px; }
            .sidebar { width: 280px; left: -280px; }

            .left-form-panel {
                 width: calc(100% - 30px);
                 margin: 20px auto 20px; 
                 padding: 20px;
            }
            .left-form-panel h3 { font-size: 1.3rem; }
            .stat-label { font-size: 0.9rem; }
            .stat-value { font-size: 1.1rem; }
            .action-btn { font-size: 0.95rem; padding: 12px; }

            .main-content { padding: 15px 10px; padding-top: 80px; } 
            .map-container { max-width: 100%; width: 100%; padding-bottom: 100%; } /* Adjust map aspect ratio */
            .level-button { width: 12%; padding-bottom: 12%; border-width: 3px; }
            
             /* Ensure result message flows correctly */
             .result-message { width: 100%; margin-left: auto; margin-right: auto; }

            .path-line { height: 3px; }
            
            /* Recalculate path positions for smaller map */
             #path1 { bottom: 17%; left: 42%; width: 40%; transform: rotate(-40deg); }
             #path2 { bottom: 50%; left: 28%; width: 46%; transform: rotate(18deg); }
             #path3 { top: 48%; left: 30%; width: 40%; transform: rotate(-45deg); }
            /* Recalculate level positions */
             #level1 { bottom: 12%; left: 35%; }
             #level2 { bottom: 35%; left: 70%; }
             #level3 { top: 45%; left: 22%; }
             #level4 { top: 18%; left: 60%; } /* Use left instead of right */

        }

        @media (max-width: 480px) {
            .map-container { padding-bottom: 120%; }
            .level-button { width: 14%; padding-bottom: 14%; border-width: 2px; }
            .path-line { height: 2px; }
            
             /* Fine-tune path positions for very small map */
             #path1 { bottom: 17%; left: 42%; width: 42%; transform: rotate(-42deg); }
             #path2 { bottom: 50%; left: 26%; width: 48%; transform: rotate(20deg); }
             #path3 { top: 47%; left: 28%; width: 42%; transform: rotate(-48deg); }
            /* Fine-tune level positions */
             #level1 { bottom: 11%; left: 33%; }
             #level2 { bottom: 34%; left: 72%; }
             #level3 { top: 46%; left: 19%; }
             #level4 { top: 16%; left: 62%; } 
        }

    </style>
</head>
<body>
    <div class="header-section">
        <button class="logo-toggle" onclick="toggleSidebar()" aria-label="Toggle menu">
            <img src="../ui/Illustration17.png" alt="PeakBasa Logo">
        </button>
        <h1 class="header-title">PeakBasa</h1>
    </div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2>Navigation</h2>
            <!-- Display correct username -->
            <p>Hello, <b><?php echo htmlspecialchars($username); ?></b>!</p> 
        </div>

        <div class="nav-links">
            <?php $currentPage = basename($_SERVER['PHP_SELF']); ?>
            <a href="lesson.php" class="<?php echo ($currentPage == 'lesson.php') ? 'active-link' : ''; ?>">📖 Lessons</a>
            <a href="main.php" class="<?php echo ($currentPage == 'main.php' || $currentPage == 'main_centered.php') ? 'active-link' : ''; ?>">🏆 Progress Map</a>
            <a href="profilemain.php" class="<?php echo ($currentPage == 'profilemain.php' || $currentPage == 'profilemain.php') ? 'active-link' : ''; ?>">👤 Profile</a>

            <?php if ($is_teacher): ?>
                <a href="../teacher/teacher_dashboard.php" class="<?php echo ($currentPage == 'teacher_dashboard.php') ? 'active-link' : ''; ?>">📊 Teacher Dashboard</a> 
            <?php endif; ?>
            
            <?php 
            // Check if current user is admin
            if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): 
            ?>
                <!-- Use ../ to go up one directory from main/ folder -->
                <a href="../adminPage/admin_messages.php" class="<?php echo ($currentPage == 'admin_messages.php') ? 'active-link' : ''; ?>">📧 Admin Messages</a>
                
                <a href="../adminPage/admin_user.php" class="<?php echo ($currentPage == 'admin_user.php') ? 'active-link' : ''; ?>">👥 User Management</a>
            <?php endif; ?>
        </div>
        <div class="logout-section">
            <a href="../Verification/logout.php" class="logout-btn">
                <span>🚪</span>
                <span>Logout</span>
            </a>
        </div>
    </div>
    <!-- Overlay -->
    <div class="overlay" id="overlay" onclick="closeSidebar()"></div>

     <!-- Main Content Area -->
     <div class="main-content">
         
         <!-- Result Message (Moved inside main-content for better flow) -->
         <?php if (isset($_SESSION['result_message'])): ?>
             <div class="result-message <?php echo strpos($_SESSION['result_message'], 'Failed') !== false || strpos($_SESSION['result_message'], 'Error') !== false ? 'error' : 'success'; ?>">
                 <?php
                 echo $_SESSION['result_message'];
                 unset($_SESSION['result_message']); // Clear message after display
                 ?>
             </div>
         <?php endif; ?>

         <!-- Left Panel -->
         <div class="left-form-panel">
             <!-- Use appropriate title based on role -->
             <h3><?php echo $is_admin ? '🔑 Admin Overview' : ($is_teacher ? '🧑‍🏫 Teacher Overview' : '📊 Your Progress'); ?></h3> 

             <?php if ($user_role === 'student'): // Show student progress only ?>
             <div class="progress-stats">
                 <div class="stat-item">
                     <span class="stat-label">🎯 Levels Completed</span>
                     <span class="stat-value"><?php echo htmlspecialchars($level_status_text); ?></span>
                 </div>
                 <div class="stat-item">
                     <span class="stat-label">⭐ Total Stars</span>
                     <span class="stat-value <?php echo ($total_stars === 'N/A') ? 'na' : ''; ?>">
                          <?php echo htmlspecialchars($total_stars); ?>
                     </span>
                 </div>
             </div>

             <div class="progress-bar-container">
                 <div class="progress-bar-label">
                     <span>Overall Progress</span>
                     <span class="<?php echo ($progress_percentage === 'N/A') ? 'na' : ''; ?>">
                          <strong><?php echo ($progress_percentage === 'N/A') ? 'N/A' : htmlspecialchars($progress_percentage) . '%'; ?></strong>
                     </span>
                 </div>
                 <div class="progress-bar">
                      <div class="progress-bar-fill" style="width: <?php echo is_numeric($progress_percentage) ? $progress_percentage : 0; ?>%"></div>
                 </div>
             </div>
             <?php endif; ?>

             <?php if ($is_teacher): // Show Teacher Quick View only ?>
             <div class="teacher-quick-view">
                 <h3>👨‍🏫 Quick Dashboard</h3>
                 <div class="stat-item">
                     <span class="stat-label">👥 Total Students</span>
                     <span class="stat-value"><?php echo $total_students; ?></span>
                 </div>
                 <div class="stat-item">
                     <span class="stat-label">🏆 Class Average</span>
                     <span class="stat-value"><?php echo $class_avg_score; ?> ⭐</span>
                 </div>
             </div>
             <?php endif; ?>
             
             <?php if ($is_admin): // Show Admin Quick View (Placeholder) ?>
              <div class="teacher-quick-view"> 
                  <h3>🔑 Admin Info</h3>
                   <div class="stat-item">
                       <span class="stat-label">⚙️ Role</span>
                       <span class="stat-value" style="color: #6a0dad;">Administrator</span> 
                   </div>
                   
                   <div class="stat-item">
                        <span class="stat-label">📧 Messages</span>
                         <span class="stat-value"><a href="../adminPage/admin_messages.php" style="color: #1e88e5;">View Inbox</a></span> 
                    </div>

              </div>
             <?php endif; ?>
             
              <div class="quick-actions">
                  <!-- Continue/Start/View Button -->
                  <a href="<?php echo htmlspecialchars($button_target); ?>" 
                     class="action-btn primary <?php echo ($button_target == '#') ? 'disabled' : ''; ?>" 
                     <?php echo ($button_target == '#') ? 'aria-disabled="true"' : ''; ?>>
                      <?php echo htmlspecialchars($button_text); ?>
                  </a>
                  
                  <!-- Take a Quiz Button (Show for Students and Teachers, hide for Admin?) -->
                   <?php if ($user_role === 'student' || $is_teacher): ?>
                       <?php
                            // Determine the target level for the "Take a Quiz" button
                            $takeQuizLevel = ($user_role === 'student') ? $current_level : 1; // Student continues, teacher starts at 1
                            if ($takeQuizLevel > $total_levels) $takeQuizLevel = $total_levels; // Don't go beyond max level
                            $takeQuizTarget = "../quiz/quiz_menu.php?level=" . $takeQuizLevel;
                       ?>
                       <a href="<?php echo htmlspecialchars($takeQuizTarget); ?>" class="action-btn secondary">
                           📝 Take a Quiz
                       </a>
                   <?php endif; ?>

                  <a href="profilemain.php" class="action-btn secondary">
                      👤 View Profile
                  </a>
                  
                  <?php if ($is_teacher): ?>
                  <a href="teacher_dashboard.php" class="action-btn secondary">
                      📊 View Full Dashboard
                  </a>
                  <?php endif; ?>
                                    
                    <?php if ($is_admin): // Admin specific buttons ?>
                        <a href="../adminPage/admin_messages.php" class="action-btn secondary">
                            📧 View Messages
                        </a>
                        <a href="../adminPage/admin_user.php" class="action-btn secondary" style="border-color: #dc3545; color: #dc3545;"> 
                            🚫 Manage Users
                        </a>
                    <?php endif; ?>
                  </div>
          </div>

          <!-- Map (Right side on desktop, below panel on mobile) -->
          <div class="map-container">
              <img src="../ui/LevelDesign.png" class="map-background" alt="Level Map">

              <div class="path-line" id="path1"></div>
              <div class="path-line" id="path2"></div>
              <div class="path-line" id="path3"></div>

              <!-- Level 1 Button -->
               <!-- Teachers/Admins always see level 1 unlocked -->
              <a href="../quiz/quiz_menu.php?level=1" 
                 class="level-button" 
                 id="level1" 
                 aria-label="Level 1">
                  <span>🌱</span>
              </a>

              <!-- Level 2 Button -->
              <a href="<?php echo ($is_teacher || $is_admin || $current_level_for_display > 1) ? "../quiz/quiz_menu.php?level=2" : "#"; ?>"
                 class="level-button <?php echo ($is_teacher || $is_admin || $current_level_for_display > 1) ? '' : 'locked'; ?>" 
                 id="level2" 
                 aria-label="Level 2">
                  <span>🌿</span>
              </a>

              <!-- Level 3 Button -->
              <a href="<?php echo ($is_teacher || $is_admin || $current_level_for_display > 2) ? "../quiz/quiz_menu.php?level=3" : "#"; ?>"
                 class="level-button <?php echo ($is_teacher || $is_admin || $current_level_for_display > 2) ? '' : 'locked'; ?>" 
                 id="level3" 
                 aria-label="Level 3">
                  <span>🌳</span>
              </a>

              <!-- Level 4 Button -->
              <a href="<?php echo ($is_teacher || $is_admin || $current_level_for_display > 3) ? "../quiz/quiz_menu.php?level=4" : "#"; ?>"
                 class="level-button <?php echo ($is_teacher || $is_admin || $current_level_for_display > 3) ? '' : 'locked'; ?>" 
                 id="level4" 
                 aria-label="Level 4">
                  <span>🏔️</span>
              </a>
          </div>
      </div>


    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            const headerTitle = document.querySelector('.header-title');

            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
             // Only hide title if sidebar is fully active (avoids flicker on close)
            headerTitle.classList.toggle('hidden', sidebar.classList.contains('active')); 
        }

        function closeSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            const headerTitle = document.querySelector('.header-title');
            
            // Check if elements exist before manipulating
            if (sidebar) sidebar.classList.remove('active');
            if (overlay) overlay.classList.remove('active');
            if (headerTitle) headerTitle.classList.remove('hidden'); // Always show title when closing
        }

        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const headerSection = document.querySelector('.header-section');
            const overlay = document.getElementById('overlay'); // Need overlay ref here too

            // Check if elements exist and if sidebar is active before closing
            if (sidebar && headerSection && overlay && 
                sidebar.classList.contains('active') &&
                !sidebar.contains(event.target) &&
                !headerSection.contains(event.target)) {
                 closeSidebar();
            }
        });

        document.addEventListener('keydown', function(e) {
            const sidebar = document.getElementById('sidebar');
            // Check if sidebar exists
            if (sidebar && e.key === 'Escape' && sidebar.classList.contains('active')) {
                closeSidebar();
            }
        });

        // Auto-hide result message
        document.addEventListener('DOMContentLoaded', function() {
            const resultMessage = document.querySelector('.result-message');
            if (resultMessage && !resultMessage.classList.contains('hidden')) { // Check if not already hidden
                 // Set initial visible state styles (if needed, though default is visible)
                 resultMessage.style.opacity = '1';
                 resultMessage.style.maxHeight = '100px'; // Ensure it's initially visible
                 
                setTimeout(() => {
                    resultMessage.style.opacity = '0';
                     resultMessage.classList.add('hidden'); // Add hidden class to trigger collapse styles

                     // Optionally remove from DOM after transition
                     setTimeout(() => {
                          if (resultMessage.parentNode) { // Check if still in DOM
                               resultMessage.parentNode.removeChild(resultMessage);
                          }
                     }, 500); // Wait for transition to finish
                }, 5000); // 5 seconds
            }
        });
    </script>
</body>
</html>

