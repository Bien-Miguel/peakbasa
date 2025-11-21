<?php
// ==========================================================
// == DEBUGGING: Force display of errors
// ==========================================================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// ==========================================================

session_start();
// MANDATORY: Include the data source file which contains $quiz_data
// Make sure this path is correct relative to process_quiz_fixed.php
include 'quiz_data.php'; 

// Include the database connection file (using constants defined there)
// Make sure this path is correct relative to process_quiz_fixed.php
require_once '../conn.php'; 

// Include the Progress Manager Class
// Make sure this path is correct relative to process_quiz_fixed.php
include 'ProgressManager.php'; 

// --- SESSION CHECK ---
if (!isset($_SESSION['user_id']) && !isset($_SESSION['teacher_id'])) {
    header("Location: ../Verification/login.php"); 
    exit;
}

// Determine role and user ID
$role = '';
$user_id = 0; 

if (isset($_SESSION['teacher_id'])) {
    $role = 'teacher';
} elseif (isset($_SESSION['user_id'])) {
    $role = 'student';
    $user_id = $_SESSION['user_id']; 
} else {
    header("Location: ../Verification/login.php"); 
    exit;
}

// --- TEACHER CHECK ---
if ($role === 'teacher') {
    $_SESSION['result_message'] = "Teachers cannot submit quiz answers."; 
    header("Location: ../Main/main.php"); 
    exit;
}

// 1. Receive and Validate Submission Data (Only students proceed)
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['level'], $_POST['quiz'], $_POST['quiz_type'])) {
     // Use 'result_message' consistently
    $_SESSION['result_message'] = "Invalid quiz submission detected.";
    header("Location: ../Main/main.php"); 
    exit;
}

$submitted_level = (int)$_POST['level'];
$submitted_quiz = (int)$_POST['quiz'];
$submitted_quiz_type = $_POST['quiz_type']; 

// Check if quiz_data was loaded correctly
if (!isset($quiz_data) || !is_array($quiz_data)) {
     // Critical error: quiz_data.php might be missing or corrupted
     error_log("FATAL ERROR in process_quiz: \$quiz_data is not loaded or not an array. Check include path and quiz_data.php content.");
     $_SESSION['result_message'] = "Error: Quiz configuration could not be loaded.";
     header("Location: ../Main/main.php");
     exit;
}


// Fetch the correct quiz data structure
if (!isset($quiz_data[$submitted_level][$submitted_quiz])) {
    $_SESSION['result_message'] = "Error: Quiz data not found for Level $submitted_level, Quiz $submitted_quiz."; 
    header("Location: ../Main/main.php"); 
    exit;
}

// Verify submitted quiz type matches the data source
$correct_quiz_data = $quiz_data[$submitted_level][$submitted_quiz];
if (!isset($correct_quiz_data['type']) || $correct_quiz_data['type'] !== $submitted_quiz_type) {
     $_SESSION['result_message'] = "Error: Quiz type mismatch for Level $submitted_level, Quiz $submitted_quiz.";
     header("Location: ../Main/main.php"); 
     exit;
}

// Ensure questions key exists and is an array
if (!isset($correct_quiz_data['questions']) || !is_array($correct_quiz_data['questions'])){
    $_SESSION['result_message'] = "Error: Questions not found for Level $submitted_level, Quiz $submitted_quiz.";
    header("Location: ../Main/main.php"); 
    exit;
}
$questions = $correct_quiz_data['questions'];

// Basic check if questions array is empty
if (empty($questions)) {
     $_SESSION['result_message'] = "Warning: No questions found for Level $submitted_level, Quiz $submitted_quiz.";
     // Decide if this should redirect or proceed with 0 score
     header("Location: ../Main/main.php"); 
     exit; 
}

$stars_earned = 0;
$correct_answers_count = 0;
$total_questions = count($questions);

// 2. Grade the Quiz
foreach ($questions as $index => $q) {
    // Check if the current question has an answer defined
    if (!isset($q['answer'])) {
        error_log("Warning: No answer defined for question index $index in Level $submitted_level, Quiz $submitted_quiz.");
        continue; // Skip grading this question
    }
    
    $input_name = "q_" . $index;
    $user_answer = isset($_POST[$input_name]) ? trim(strtolower($_POST[$input_name])) : ''; 
    $correct_answer_raw = $q['answer']; // Get the raw answer from quiz_data
    $correct_answer = ''; // Initialize correct answer string

    // Convert boolean answers from quiz_data to strings for comparison
    if ($correct_answer_raw === true) {
        $correct_answer = 'true';
    } elseif ($correct_answer_raw === false) {
        $correct_answer = 'false';
    } else {
        // Assume it's already a string (like for fillblank)
        $correct_answer = trim(strtolower($correct_answer_raw));
    }
    
    // Check if 'stars' key exists for the question
    $stars_for_question = isset($q['stars']) ? (int)$q['stars'] : 0; // Default to 0 stars if not set

    if ($user_answer === $correct_answer) {
        $stars_earned += $stars_for_question;
        $correct_answers_count++;
    }
}


// Pass threshold: Must get at least 50% correct
$pass_threshold_ratio = 0.5; 
$is_passed = ($total_questions > 0) && (($correct_answers_count / $total_questions) >= $pass_threshold_ratio);

// Determine result message
$message = $is_passed 
    ? "Quiz Passed! You earned $stars_earned ⭐ ($correct_answers_count / $total_questions correct)."
    : "Quiz Failed. You answered $correct_answers_count / $total_questions correctly. Please review and try again.";

$_SESSION['result_message'] = $message; 

// Find the highest level defined in quiz_data
$total_levels_defined = 0;
if (is_array($quiz_data) && !empty($quiz_data)) {
    $numeric_keys = array_filter(array_keys($quiz_data), 'is_int');
    $total_levels_defined = !empty($numeric_keys) ? max($numeric_keys) : 0;
} 
if ($total_levels_defined === 0) { // Check if max level could be determined
    error_log("FATAL ERROR in process_quiz: Could not determine total levels defined in quiz_data.");
    $_SESSION['result_message'] = "Error: Could not determine game structure.";
    header("Location: ../Main/main.php");
    exit;
}


// ------------------------------------------------------------------
// 3. PROGRESS MANAGEMENT: DATABASE UPDATE AND REDIRECTION LOGIC
// ------------------------------------------------------------------
$redirect_url = "../Main/main.php"; // Default redirect

// Instantiate ProgressManager 
try {
    // Ensure constants from conn.php are available, or pass explicitly
    if (!defined('DB_HOST') || !defined('DB_USER') || !defined('DB_PASS') || !defined('DB_NAME')) {
         throw new Exception("Database constants (DB_HOST, etc.) are not defined in conn.php.");
    }
    $pm = new ProgressManager(DB_HOST, DB_USER, DB_PASS, DB_NAME); 
} catch (Exception $e) {
    error_log("Failed to instantiate ProgressManager: " . $e->getMessage());
    $_SESSION['result_message'] = "Error connecting to the progress system. Please try again later.";
    header("Location: ../Main/main.php"); 
    exit;
}

// Only update progress if the student passed
if ($is_passed) {
    
    // 3A. Save individual quiz result and stars to the database
    $update_result = $pm->updateProgress(
        $user_id, 
        $stars_earned, 
        $submitted_level, 
        $submitted_quiz, 
        $is_passed,
        $quiz_data 
    );

    if ($update_result['status'] !== 'success' && $update_result['status'] !== 'skipped') {
         error_log("Progress Update Issue for User $user_id: " . $update_result['message']);
         $_SESSION['result_message'] .= " (Note: There was an issue saving progress.)";
    }

    // 3B. Determine Next Destination
    $next_quiz_seq = $submitted_quiz + 1;
    // Get the most up-to-date level from the session (which updateProgress should have set if level up occurred)
    $current_level_after_update = $_SESSION['current_level'] ?? $submitted_level; 

    // --- Check 1: Did the user just complete the FINAL level and FINAL quiz? ---
     // Check if submitted level was the last defined one AND there's no next quiz in THAT level
    if ($submitted_level == $total_levels_defined && !isset($quiz_data[$submitted_level][$next_quiz_seq])) {
          $_SESSION['result_message'] .= " 🏆 **You have completed all levels!**";
          $redirect_url = "game_complete.php"; // Redirect to game complete page
    
    // --- Check 2: Did the user unlock a NEW level? ---
    // Check if the current level in session is now greater than the level they just submitted for
    } elseif ($current_level_after_update > $submitted_level) {
        // Leveled up, redirect back to the main map
        $redirect_url = "../Main/main.php"; 
        // Message is already potentially updated by updateProgress to include level up info

    // --- Check 3: Is there a next QUIZ in the *current* submitted level? ---
    } elseif (isset($quiz_data[$submitted_level][$next_quiz_seq])) {
        
        $next_quiz_info = $quiz_data[$submitted_level][$next_quiz_seq];
        $next_quiz_type = $next_quiz_info['type'] ?? null;
        
        // Use the correct fixed file names
        $quiz_file = '';
        switch ($next_quiz_type) {
            case 'truefalse':
                $quiz_file = 'quiz_truefalse.php';
                break;
            case 'mcq':
                $quiz_file = 'quiz_mcq.php';
                break;
            case 'fillblank': // Ensure consistent key name
                 $quiz_file = 'quiz_fillblank.php';
                 break;
            default:
                 error_log("Unknown quiz type '$next_quiz_type' found for Level $submitted_level, Quiz $next_quiz_seq.");
                 $quiz_file = 'quiz_menu.php'; // Fallback to menu
                 $next_quiz_seq = null; // Prevent adding invalid quiz param
                 break; 
        }

        if ($next_quiz_seq !== null) {
            $redirect_url = "{$quiz_file}?level={$submitted_level}&quiz={$next_quiz_seq}";
        } else {
             $redirect_url = "quiz_menu.php?level={$submitted_level}"; // Go to menu if type unknown
        }

    // --- Check 4: Fallback (End of quizzes for the level, but not game complete or level up) ---
    } else {
        // Completed all quizzes in the level, redirect back to main map
        $redirect_url = "../Main/main.php"; 
        $_SESSION['result_message'] .= " Level $submitted_level quizzes complete.";
    }
    
// If the quiz was NOT passed
} else { 
    // Redirect back to the SAME quiz page to allow retry
    $submitted_quiz_type = $_POST['quiz_type'];
    $quiz_file = '';
     switch ($submitted_quiz_type) {
         case 'truefalse':
             $quiz_file = 'quiz_truefalse.php';
             break;
         case 'mcq':
             $quiz_file = 'quiz_mcq.php';
             break;
         case 'fillblank':
              $quiz_file = 'quiz_fillblank.php';
              break;
         default:
              // If type somehow invalid on fail, go back to menu
              $quiz_file = 'quiz_menu.php'; 
              break; 
     }
     // Include level and quiz params for the retry URL
    $redirect_url = "{$quiz_file}?level={$submitted_level}&quiz={$submitted_quiz}"; 
    // Message already set indicating failure
} 

// Clean up ProgressManager instance
unset($pm); 

// 4. FINAL REDIRECTION
// Make sure no output before this line (error reporting lines are okay)
if (!headers_sent()) {
    header("Location: {$redirect_url}");
} else {
    // Fallback if headers already sent (e.g., due to an unexpected echo or error)
    $escaped_url = htmlspecialchars($redirect_url);
    echo "<p>Headers already sent. Redirecting you to: <a href='{$escaped_url}'>Next Step</a></p>";
    echo "<script>window.location.href='{$escaped_url}';</script>";
     error_log("Headers already sent in process_quiz.php. Attempting JS redirect to: " . $redirect_url);
}
exit; // Crucial to stop script execution after header
?>

