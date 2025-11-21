<?php
session_start();

// --- SESSION CHECK ---
// Combined check allows students or teachers
if (!isset($_SESSION['user_id']) && !isset($_SESSION['teacher_id'])) {
    header("Location: ../Verification/login.php"); 
    exit;
}

// Determine role (optional)
$role = isset($_SESSION['teacher_id']) ? 'teacher' : 'student';

// --- Include lesson content ---
// Use absolute path for reliability
$lessonDataPath = __DIR__ . '/lesson_data.php'; 
if (file_exists($lessonDataPath)) {
    include $lessonDataPath;
     if (!isset($lesson_data) || !is_array($lesson_data)) {
          // Log or display a more specific error if needed
          die("Error: Lesson data structure is invalid or missing in lesson_data.php.");
     }
} else {
     die("Error: Lesson data file not found at " . htmlspecialchars($lessonDataPath));
}


// --- Get lesson identifiers from URL ---
// ==================================================================
// == FIX: Changed 'topic' back to 'quiz' to match data structure and links
// ==================================================================
$level = isset($_GET['level']) ? (int)$_GET['level'] : 1;
$quiz_number = isset($_GET['quiz']) ? (int)$_GET['quiz'] : 1; 
// ==================================================================

// --- Fetch current lesson data ---
// ==================================================================
// == FIX: Removed ['topics'] from the array access
// ==================================================================
if (isset($lesson_data[$level][$quiz_number])) {
    $current_lesson = $lesson_data[$level][$quiz_number];
    $lesson_title = htmlspecialchars($current_lesson['title']);
    $lesson_sections = $current_lesson['sections'];
    
    // Determine total lessons (quizzes) in the level by counting the keys in $lesson_data[$level]
     $total_lessons_in_level = 0;
     if(isset($lesson_data[$level]) && is_array($lesson_data[$level])){
         // Count only numeric keys representing quizzes/lessons
          $numeric_keys = array_filter(array_keys($lesson_data[$level]), 'is_numeric');
          $total_lessons_in_level = count($numeric_keys);
     }
     
} else {
    $lesson_title = "Lesson Not Found";
    // Provide a more specific error message
    $lesson_sections = ['Error' => 'The lesson you requested (Level ' . $level . ', Lesson ' . $quiz_number . ') could not be found in the lesson data.'];
    $total_lessons_in_level = 0; 
    error_log("Lesson not found: Level=$level, Quiz/Lesson Number=$quiz_number requested.");
}
// ==================================================================


// --- Determine navigation links ---
$next_quiz = $quiz_number + 1;
$prev_quiz = $quiz_number - 1;

// Next link: Check if the next quiz number exists for the current level
$next_lesson_link = isset($lesson_data[$level][$next_quiz])
    ? "lesson.php?level=$level&quiz=$next_quiz" // Use 'quiz' param
    : null; 

// Previous link: Check if previous quiz exists or link back to map
$prev_lesson_link = ($prev_quiz > 0 && isset($lesson_data[$level][$prev_quiz]))
    ? "lesson.php?level=$level&quiz=$prev_quiz" // Use 'quiz' param
    : "main.php"; // Link back to the corrected map page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lesson_title; ?> | PeakBasa</title> 
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* --- Styles remain the same as previous version --- */
        body {
            font-family: 'Poppins', sans-serif;
            background: #f5f9ff;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .header {
            display: flex;
            align-items: center;
            gap: 15px;
            background: #ec5757;
            color: white;
            padding: 15px 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
             position: sticky; /* Make header sticky */
             top: 0;
             z-index: 100; /* Ensure header is above content */
        }

        .header a {
             display: flex; /* Make logo link behave like a block */
             align-items: center;
        }

        .header img {
            width: 45px;
            height: 45px;
            border-radius: 8px;
        }

        .header h1 {
            margin: 0;
            font-size: 1.6rem;
            font-weight: 700;
        }

        .lesson-container {
            max-width: 900px;
            background: white;
            margin: 30px auto; /* Reduced top margin */
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            padding: 40px;
        }

        .lesson-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px; /* Add padding below */
             border-bottom: 1px solid #f0f0f0; /* Add a subtle separator */
        }

        .lesson-header h2 {
            font-size: 2rem;
            color: #ec5757;
            margin-bottom: 10px;
        }

        .lesson-header p {
            color: #777;
            font-weight: 500;
             font-size: 0.95rem; /* Slightly smaller */
        }

        .lesson-section {
            background: #fffafa;
            border-left: 6px solid #ec5757;
            border-radius: 10px;
            padding: 20px 25px; /* Adjust padding */
            margin-bottom: 25px;
             box-shadow: 0 3px 8px rgba(0,0,0,0.05); /* Add subtle shadow */
        }

        .lesson-section h3 {
            margin-top: 0;
            color: #e64a4a; /* Slightly darker red for heading */
            border-bottom: 1px dashed #ffbaba; /* Lighter dashed line */
            padding-bottom: 8px; /* More padding below heading */
            font-size: 1.4rem;
             margin-bottom: 15px; /* Space below heading */
        }

        .lesson-section p {
            line-height: 1.7; /* Increase line height */
            color: #444;
            margin-bottom: 15px;
             font-size: 0.95rem; /* Slightly smaller body text */
        }
         .lesson-section p:last-child {
              margin-bottom: 0; /* Remove margin from last paragraph */
         }


        ul.vocabulary-list {
            list-style: none;
            padding: 0;
            margin-top: 10px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 12px; /* Increased gap */
        }

        .vocabulary-item {
            display: flex;
             align-items: center; /* Vertically align items */
            justify-content: space-between;
            background: #fff;
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 12px 15px; /* Adjust padding */
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
             transition: transform 0.2s ease; /* Add hover effect */
        }
         .vocabulary-item:hover {
              transform: translateY(-2px); /* Slight lift on hover */
         }

        .vocabulary-item strong {
            color: #ec5757;
             font-weight: 600; /* Bolder */
             margin-right: 10px; /* Space between words */
        }

        .vocabulary-item span {
            color: #555; /* Darker grey for definition */
            font-style: normal; /* Remove italics maybe? */
             text-align: right; /* Align definition to the right */
        }

        /* Error specific style */
         .lesson-section p.error-message {
              color: #dc3545; 
              font-weight: bold; 
              background-color: #fdecea; 
              padding: 10px; 
              border-radius: 5px;
         }


        /* Navigation Buttons */
        .nav-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #f0f0f0;
        }

        .nav-btn {
            background: linear-gradient(135deg, #ec5757, #ff8787); /* Gradient background */
            color: white;
            border: none;
            border-radius: 10px;
            padding: 12px 25px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease; /* Smooth transition */
            box-shadow: 0 4px 15px rgba(236, 87, 87, 0.3);
            text-decoration: none; 
            display: inline-flex; /* Use inline-flex for alignment */
             align-items: center;
             gap: 8px; /* Space between text and arrow */
            text-align: center; 
        }

        .nav-btn:hover {
             background: linear-gradient(135deg, #d94545, #ec5757); /* Darker gradient */
            transform: translateY(-2px);
             box-shadow: 0 6px 20px rgba(236, 87, 87, 0.4);
        }
        
         .nav-btn:active {
             transform: translateY(0);
             box-shadow: 0 4px 15px rgba(236, 87, 87, 0.3);
         }


        .nav-btn.secondary {
             background: #f8f9fa; /* Light grey background */
             color: #555; /* Dark grey text */
             border: 2px solid #e9ecef; /* Light border */
             box-shadow: none;
        }

        .nav-btn.secondary:hover {
            background: #e9ecef; /* Darker grey on hover */
             border-color: #dee2e6;
             color: #333;
             transform: translateY(-2px); /* Keep lift */
             box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
         .nav-btn.secondary:active {
              transform: translateY(0);
              box-shadow: none;
         }

        .nav-buttons > *:only-child {
            margin-left: auto; /* Push single button right */
        }


        /* Responsive Adjustments */
        
         /* Tablet */
        @media (max-width: 1024px) {
            .lesson-container {
                margin: 30px 20px;
                padding: 35px;
            }
            
            .lesson-header h2 { font-size: 1.8rem; }
            
            ul.vocabulary-list { grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); }
        }

        /* Mobile */
        @media (max-width: 768px) {
            .header {
                padding: 12px 15px;
                gap: 10px;
            }
            .header h1 { font-size: 1.2rem; line-height: 1.2; } /* Adjusted size */
            .header img { width: 35px; height: 35px; }
            
            .lesson-container { margin: 20px 10px; padding: 25px 15px; border-radius: 12px; }
            .lesson-header h2 { font-size: 1.5rem; line-height: 1.3; } /* Adjusted size */
            .lesson-header p { font-size: 0.9rem; } /* Adjusted size */
            .lesson-section { padding: 15px; margin-bottom: 20px; }
            .lesson-section h3 { font-size: 1.2rem; } /* Adjusted size */
            .lesson-section p { font-size: 0.9rem; } /* Adjusted size */
            
            ul.vocabulary-list { grid-template-columns: 1fr; gap: 10px; }
            .vocabulary-item { padding: 10px 12px; }
            .vocabulary-item strong { font-size: 0.95rem; } /* Adjusted size */
            .vocabulary-item span { font-size: 0.9rem; } /* Adjusted size */
            
            .nav-buttons { flex-direction: column; gap: 10px; margin-top: 30px; }
            .nav-btn { width: 100%; padding: 12px 20px; font-size: 0.95rem; } /* Full width */
            .nav-buttons > *:only-child { margin-left: 0; } /* Remove margin for single button */
             /* Ensure secondary button (Previous) comes first visually if stacked */
             .nav-btn.secondary { order: -1; } 
        }

        /* Small mobile */
        @media (max-width: 480px) {
            .header h1 { font-size: 1.1rem; }
            .header img { width: 32px; height: 32px; }
            .lesson-container { margin: 15px 8px; padding: 20px 12px; }
            .lesson-header h2 { font-size: 1.3rem; }
            .lesson-header p { font-size: 0.85rem; }
            .lesson-section { padding: 12px; }
            .lesson-section h3 { font-size: 1.1rem; }
            .lesson-section p { font-size: 0.85rem; }
            .vocabulary-item strong { font-size: 0.9rem; }
            .vocabulary-item span { font-size: 0.85rem; }
            .nav-btn { padding: 12px 15px; font-size: 0.9rem; }
        }

    </style>
</head>
<body>
    <div class="header">
         <!-- Use the corrected main page link -->
        <a href="main.php"><img src="../ui/Illustration17.png" alt="PeakBasa Logo"></a> 
        <h1>PeakBasa Learning Module</h1>
    </div>

    <div class="lesson-container">
        <div class="lesson-header">
            <h2><?php echo $lesson_title; ?></h2>
             <!-- Updated Lesson Number Display -->
            <p>Level <?php echo $level; ?> 
                <?php if ($total_lessons_in_level > 0): ?>
                    • Lesson <?php echo $quiz_number; ?> of <?php echo $total_lessons_in_level; ?>
                <?php else: ?>
                     <!-- Display if total couldn't be determined but lesson was found -->
                     • Lesson <?php echo $quiz_number; ?> 
                <?php endif; ?>
             </p> 
         </div>

        <?php foreach ($lesson_sections as $section_title => $content): ?>
            <div class="lesson-section">
                <h3><?php echo htmlspecialchars($section_title); ?></h3>
                <?php if ($section_title === 'Vocabulary' && is_array($content)): // Specific check for Vocabulary ?>
                    <ul class="vocabulary-list">
                        <?php foreach ($content as $key => $value): ?>
                            <li class="vocabulary-item">
                                <strong><?php echo htmlspecialchars($key); ?></strong>
                                <span><?php echo htmlspecialchars($value); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                 <?php elseif ($section_title === 'Key Vocabulary' && is_array($content)): // Handle 'Key Vocabulary' structure ?>
                      <ul class="vocabulary-list">
                          <?php foreach ($content as $key => $value): ?>
                              <li class="vocabulary-item">
                                  <strong><?php echo htmlspecialchars($key); ?></strong>
                                  <span><?php echo htmlspecialchars($value); ?></span>
                              </li>
                          <?php endforeach; ?>
                      </ul>
                 <?php elseif ($section_title === 'Key Concept: Antonyms' && is_string($content)): // Handle specific string sections ?>
                      <p><?php echo nl2br(htmlspecialchars($content)); ?></p>
                 <?php elseif ($section_title === 'Key Phrase: Mahal Kita' && is_array($content)): // Handle array sections that aren't vocabulary ?>
                      <?php foreach ($content as $key => $value): ?>
                           <p><strong><?php echo htmlspecialchars($key); ?>:</strong> <?php echo htmlspecialchars($value); ?></p>
                      <?php endforeach; ?>
                <?php elseif ($section_title === 'Error' && !is_array($content)): // Handle lesson not found error ?>
                     <p class="error-message"><?php echo htmlspecialchars($content); ?></p> 
                <?php elseif (is_string($content)): // Handle other regular text content ?>
                    <p><?php echo nl2br(htmlspecialchars($content)); ?></p>
                <?php elseif (is_array($content)): // Generic handling for other unexpected array content ?>
                     <p><i>(This section contains structured data - display might vary)</i></p>
                     <ul>
                          <?php foreach ($content as $key => $value): ?>
                               <li><strong><?php echo htmlspecialchars($key); ?>:</strong> <?php echo htmlspecialchars(is_array($value) ? json_encode($value) : $value); ?></li>
                          <?php endforeach; ?>
                     </ul>
                <?php else: // Handle completely unexpected content format ?>
                     <p><i>Content for this section is not available in the expected format.</i></p>
                     <?php error_log("Unexpected content format for '$section_title' in Level $level, Quiz $quiz_number: " . gettype($content)); ?>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <div class="nav-buttons">
            <?php if ($prev_lesson_link): ?>
                <a href="<?php echo htmlspecialchars($prev_lesson_link); ?>" class="nav-btn secondary">← Previous</a>
            <?php endif; ?>

            <?php if ($next_lesson_link): ?>
                 <a href="<?php echo htmlspecialchars($next_lesson_link); ?>" class="nav-btn">Next Lesson →</a>
            <?php else: ?>
                 <a href="main.php" class="nav-btn">Back to Menu 🗺️</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

