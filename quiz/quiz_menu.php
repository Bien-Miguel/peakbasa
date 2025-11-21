<?php
session_start();

// --- 1. SESSION CHECK ---
// ==================================================================
// == THIS IS THE FIX: Allow EITHER student OR teacher
// ==================================================================
if (!isset($_SESSION['user_id']) && !isset($_SESSION['teacher_id'])) {
    // If NEITHER is set, redirect to login
    header("Location: ../Verification/login.php"); 
    exit;
}

// Determine role and user ID for fetching progress
// Note: Teachers won't have individual progress, so user_id remains 0 for them.
if (isset($_SESSION['teacher_id'])) {
    $user_id = 0; // Teachers don't have individual progress tracked this way
    $role = 'teacher';
    // $username = $_SESSION['teacher_username'] ?? 'Teacher'; // Optional: if you need teacher username later
} elseif (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id']; // Use the student's ID
    $role = 'student';
     // $username = $_SESSION['username'] ?? 'Student'; // Optional: if you need student username later
} else {
    // Should not happen due to the check above, but as a fallback:
    header("Location: ../Verification/login.php"); 
    exit;
}
// ==================================================================
// == END FIX
// ==================================================================


// 2. Get the current level from the URL
$level = filter_input(INPUT_GET, 'level', FILTER_SANITIZE_NUMBER_INT);
$level = (int)$level > 0 ? (int)$level : 1;

// 3. Include quiz data to calculate max stars
include 'quiz_data.php'; // Make sure this path is correct

// 4. Database connection 
require_once '../conn.php';

// Note: $user_id is already set correctly above based on the role

// 5. Define the quiz types with their sequential order
$quiz_types = [
    [
        'type' => 'truefalse', 
        'file' => 'quiz_truefalse.php', 
        'name' => 'True or False', 
        'icon' => '✔️', 
        'description' => 'Decide whether the statement is correct or not.',
        'quiz_seq' => 1 
    ],
    [
        'type' => 'mcq', 
        'file' => 'quiz_mcq.php', 
        'name' => 'Multiple Choice', 
        'icon' => '📝', 
        'description' => 'Answer multiple-choice questions to test your knowledge.',
        'quiz_seq' => 2 
    ],
    [
        'type' => 'fillblank', 
        'file' => 'quiz_fillblank.php', 
        'name' => 'Fill in the Blank', 
        'icon' => '✏️', 
        'description' => 'Complete sentences with the correct word or phrase.',
        'quiz_seq' => 3
    ],
];

// 6. Calculate Max Stars for the Level
$max_stars_for_level = 0;
if (isset($quiz_data[$level])) {
    foreach ($quiz_data[$level] as $quiz_in_level) {
        if (isset($quiz_in_level['questions']) && is_array($quiz_in_level['questions'])) {
            foreach ($quiz_in_level['questions'] as $question) {
                $max_stars_for_level += (int)($question['stars'] ?? 0);
            }
        }
    }
}

// 7. Fetch User's Earned Stars for the Level (Only relevant for students)
$earned_stars_for_level = 0;
// Only query if it's a student (user_id > 0)
if ($user_id > 0) { 
    $stmt_earned = $conn->prepare("SELECT SUM(stars_earned) as total_earned FROM user_scores WHERE user_id = ? AND level = ?");
    // Check if prepare() succeeded
    if ($stmt_earned) { 
        $stmt_earned->bind_param("ii", $user_id, $level);
        $stmt_earned->execute();
        $result_earned = $stmt_earned->get_result();
        $earned_data = $result_earned->fetch_assoc();
        $stmt_earned->close();

        if ($earned_data && isset($earned_data['total_earned'])) {
            $earned_stars_for_level = (int)$earned_data['total_earned'];
        }
    } else {
        // Handle error if prepare fails, e.g., log it
        // error_log("Failed to prepare statement: " . $conn->error); 
    }
}

// 8. Calculate Remaining Stars
$remaining_stars = max(0, $max_stars_for_level - $earned_stars_for_level);


// 9. Function to check if a quiz has been completed (Using stars_earned > 0)
function isQuizCompleted($conn, $user_id, $level, $quiz_seq) {
    // If it's a teacher (user_id 0), they haven't completed anything individually
    if ($user_id <= 0) return false; 
    
    $stmt = $conn->prepare("SELECT stars_earned FROM user_scores WHERE user_id = ? AND level = ? AND quiz_number = ?");
     // Check if prepare() succeeded
    if (!$stmt) return false; 
    
    $stmt->bind_param("iii", $user_id, $level, $quiz_seq);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();
    
    return isset($data['stars_earned']) && $data['stars_earned'] > 0;
}

// 10. Function to check if a quiz is unlocked
function isQuizUnlocked($conn, $user_id, $level, $quiz_seq) {
    // Teachers can access any quiz
    if ($user_id <= 0) return true; 

    if ($quiz_seq == 1) {
        return true;
    }
    $previous_quiz_seq = $quiz_seq - 1;
    return isQuizCompleted($conn, $user_id, $level, $previous_quiz_seq);
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose Quiz Type - Level <?php echo $level; ?> - PeakBasa</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');
        
        body { 
            font-family: 'Poppins', sans-serif; 
            background: #e3f2fd; 
            padding: 20px; 
            margin: 0; 
        }
        .menu-container {
            max-width: 800px; 
            margin: auto; 
            background: #fff; 
            padding: 40px; 
            border-radius: 15px; 
            text-align: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        h2 { 
            color: #ec5757; 
            margin-top: 0; 
            margin-bottom: 15px; /* Reduced bottom margin */
        }
        
        .level-progress-info {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 30px; /* Space before quiz types */
            text-align: center;
            font-size: 1rem;
            color: #495057;
        }
        .level-progress-info strong {
            color: #ec5757;
            font-weight: 700;
        }
        .level-progress-info span {
            margin: 0 8px; /* Add some spacing around the values */
        }
        
        .quiz-types {
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        .quiz-card {
            background: #f8f9fa; 
            padding: 25px; 
            border-radius: 12px;
            cursor: pointer; 
            transition: 0.3s; 
            border: 2px solid transparent;
            position: relative;
            /* Add min-height to prevent weird resizing if lock message appears */
             min-height: 150px; 
             display: flex; /* Use flexbox for alignment */
             flex-direction: column; /* Stack items vertically */
             justify-content: center; /* Center content vertically */
        }
        .quiz-card:hover {
            transform: translateY(-5px);
            border-color: #ec5757;
            box-shadow: 0 8px 25px rgba(236,87,87,0.1);
        }
        .quiz-card h3 { 
            color: #333; 
            margin: 10px 0; 
            font-size: 1.1rem; /* Slightly adjust size */
        }
        .quiz-card p { 
            color: #666; 
            font-size: 0.9rem; 
            margin-bottom: 5px; /* Add small bottom margin */
             flex-grow: 1; /* Allow description to take space */
        }
        
        /* Locked quiz styles */
        .quiz-card.locked {
            background: #e9ecef; 
            cursor: not-allowed;
            opacity: 0.6;
        }
        .quiz-card.locked:hover {
            transform: none;
            border-color: transparent;
            box-shadow: none;
        }
        .quiz-card.locked::before {
            content: '🔒';
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 1.5rem;
        }
        .quiz-card.locked h3,
        .quiz-card.locked p {
            color: #999;
        }
        
        /* Completed quiz styles */
        .quiz-card.completed::after {
            content: '✅';
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 1.5rem;
        }
         /* Prevent completed checkmark from overlapping lock icon if somehow both apply */
        .quiz-card.locked.completed::after {
             display: none; 
        }
        
        .back-btn {
            display: inline-block;
            margin-top: 30px;
            padding: 10px 20px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            text-decoration: none;
            color: #333;
            transition: 0.3s;
        }
        .back-btn:hover {
            background: #e9ecef;
        }
        
        .lock-message {
            color: #999;
            font-size: 0.8rem; /* Make it slightly smaller */
            margin-top: auto; /* Push to bottom within flex container */
            padding-top: 10px; /* Add space above */
            font-style: italic;
            line-height: 1.3;
        }

        @media (max-width: 600px) {
            body {
                padding: 10px; 
            }
            .menu-container {
                padding: 20px; 
                margin: 10px auto; 
            }
            h2 {
                font-size: 1.5rem; 
                margin-bottom: 15px; /* Adjusted margin */
            }
            .level-progress-info {
                padding: 10px 15px;
                font-size: 0.9rem;
                margin-bottom: 20px;
            }
            .quiz-types {
                gap: 15px; 
            }
            .quiz-card {
                padding: 20px; 
                min-height: 140px; /* Adjust min-height for mobile */
            }
             .quiz-card h3 {
                  font-size: 1rem;
             }
             .quiz-card p {
                  font-size: 0.85rem;
             }
            .back-btn {
                margin-top: 20px;
                padding: 12px; 
                width: 100%; 
                box-sizing: border-box; 
            }
        }
    </style>
</head>
<body>
    <div class="menu-container">
        <h2>Choose Your Quiz Type for Level <?php echo $level; ?></h2>

        <!-- Display progress only for students -->
        <?php if ($role == 'student'): ?>
            <div class="level-progress-info">
                You've earned <span><strong><?php echo $earned_stars_for_level; ?></strong> / <?php echo $max_stars_for_level; ?> ⭐</span> for this level.
                <br>
                Collect <span><strong><?php echo $remaining_stars; ?></strong> more stars</span> to max out! ✨
            </div>
        <?php endif; ?>

        <div class="quiz-types">
            
            <?php foreach ($quiz_types as $quiz): ?>
            <?php 
                $is_unlocked = isQuizUnlocked($conn, $user_id, $level, $quiz['quiz_seq']);
                // Completion check only matters for students regarding the checkmark
                $is_completed = ($role == 'student') ? isQuizCompleted($conn, $user_id, $level, $quiz['quiz_seq']) : false; 
                
                // Determine card class
                $card_class = 'quiz-card';
                if (!$is_unlocked) {
                    $card_class .= ' locked';
                } elseif ($is_completed) { // Only add completed if it's unlocked AND completed
                    $card_class .= ' completed';
                }
                
                // Construct the dynamic URL - Teachers & Students use the same URL structure
                $params = [
                    'level' => $level,
                    'quiz' => $quiz['quiz_seq'],
                    'type' => $quiz['type']
                ];
                // Use the correct quiz file name based on role or context if needed
                // Assuming teachers and students use the same quiz files for now.
                $quiz_file = $quiz['file']; 
                $url = $quiz_file . '?' . http_build_query($params);
                
                // Onclick handler
                $onclick_attr = $is_unlocked ? "onclick=\"window.location.href='" . htmlspecialchars($url) . "'\"" : "";
            ?>
            <div class="<?php echo $card_class; ?>" <?php echo $onclick_attr; ?>>
                <h3><?php echo $quiz['icon'] . " " . htmlspecialchars($quiz['name']); ?></h3>
                <p><?php echo htmlspecialchars($quiz['description']); ?></p>
                <?php if (!$is_unlocked && $role == 'student'): // Show lock message only to students ?>
                    <p class="lock-message">Complete the previous quiz with at least 1 ⭐ to unlock</p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            
        </div>
        
        <a href="../Main/main.php" class="back-btn">⬅️ Back to Map</a>
    </div>
</body>
</html>
<?php
// Close connection only if it was successfully opened
if ($conn) {
    $conn->close();
}
?>
