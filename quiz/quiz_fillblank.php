<?php
session_start();
// 1. MANDATORY: Include the data source file
include 'quiz_data.php';

// --- SESSION CHECK ---
// ==================================================================
// == THIS IS THE FIX: Allow EITHER student OR teacher
// ==================================================================
if (!isset($_SESSION['user_id']) && !isset($_SESSION['teacher_id'])) {
    // If NEITHER is set, redirect to login
    header("Location: ../Verification/login.php"); 
    exit;
}

// Determine role (optional, but good practice if logic differs later)
if (isset($_SESSION['teacher_id'])) {
    $role = 'teacher';
} elseif (isset($_SESSION['user_id'])) {
    $role = 'student';
} else {
    // Fallback just in case
    header("Location: ../Verification/login.php"); 
    exit;
}
// ==================================================================
// == END FIX
// ==================================================================


// 2. DYNAMIC FETCHING LOGIC
$current_level = isset($_GET['level']) ? (int)$_GET['level'] : 1;
$quiz_number_in_level = isset($_GET['quiz']) ? (int)$_GET['quiz'] : 3; // Default for Fillblank

// Validate that the requested quiz exists and is the correct type
if (!isset($quiz_data[$current_level][$quiz_number_in_level]) || $quiz_data[$current_level][$quiz_number_in_level]['type'] !== 'fillblank') {
    // Redirect to main menu or quiz menu if data is invalid
    header("Location: ../Main/main.php"); // Or perhaps quiz_menu_fixed.php?level=$current_level
    exit;
}

$quiz_info = $quiz_data[$current_level][$quiz_number_in_level];
$questions = $quiz_info['questions'];
$total_stars_available = array_sum(array_column($questions, 'stars'));
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Level <?php echo $current_level; ?> Quiz <?php echo $quiz_number_in_level; ?> (Fill-in-the-Blank)</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');
        
        * { box-sizing: border-box; }

        body { 
            font-family:'Poppins', sans-serif; 
            background: linear-gradient(135deg, #fcdcdcff 0%, #fdf0f0 100%); 
            padding:20px; display: flex; justify-content: center;
            align-items: center; min-height: 100vh; margin: 0;
        }
        .quiz-container { 
            max-width:700px; background:#fff; padding:30px; border-radius:20px; 
            box-shadow:0 8px 18px rgba(197, 34, 34, 0.15); 
            text-align:center; position: relative; width: 100%; 
        }
        .exit-btn {
            position: absolute; top: 20px; right: 20px; background: #ef4444; color: white;
            border: none; border-radius: 8px; padding: 8px 16px; cursor: pointer;
            font-size: 14px; font-weight: 600; transition: 0.3s; z-index: 10;
        }
        .exit-btn:hover { background: #dc2626; transform: translateY(-2px); }
        
        h2 { color:#ec5757; font-size: 28px; margin-top: 0; margin-bottom: 25px; }
        .quiz-info {
            font-size: 18px; color: #7c4646; font-weight: 600; margin-bottom: 30px;
            border-bottom: 2px solid #fdecec; padding-bottom: 10px;
        }
        .question-wrapper {
            text-align: left; margin-bottom: 30px; padding: 20px 0;
            border-top: 1px dashed #fcdcdc; display: none;
        }
        .question-wrapper.active-question { display: block; }
        
        .question-text {
            font-size: 1.15rem; font-weight: 600; color: #7c4646; margin-bottom: 15px;
        }
        .answer-area { position: relative; margin-top: 10px; }
        
        input[type=text] { 
            padding: 12px; border: 2px solid #ec5757; border-radius: 10px; 
            width: 100%; font-size:16px; font-weight: 500;
            transition: border-color 0.3s, background-color 0.3s; 
        }
        input[type=text]:focus {
            border-color: #dc2626; outline: none;
            box-shadow: 0 0 5px rgba(236, 87, 87, 0.5);
        }

        /* --- Input Feedback Styles --- */
        .feedback-message {
            display: block; margin-top: 8px; font-size: 0.9rem;
            font-weight: 600; min-height: 1.2em; /* Reserve space */
            text-align: left; /* Align feedback text left */
        }
        input[type=text].correct { border-color: #28a745; background-color: #e9f7ec; }
        input[type=text].incorrect { border-color: #dc3545; background-color: #fdecea; }
        .feedback-message.correct { color: #28a745; }
        .feedback-message.incorrect { color: #dc3545; }

        /* --- Navigation Button Styles --- */
        .navigation-buttons { 
            display: flex; 
            justify-content: flex-end; 
            margin-top: 40px; 
            gap: 15px; 
        }
        .nav-btn { /* Style for Next button */
            padding: 15px 30px; background: #6c757d; color: #fff; border: none;
            border-radius: 12px; font-size: 18px; font-weight: 700; cursor: pointer; transition: 0.3s;
        }
        .nav-btn:hover { background: #5a6268; transform: translateY(-2px); }
        
        .submit-btn.hidden { display: none; } /* Keep this */
        
        .submit-btn {
            padding: 15px 30px; background: #ec5757; color: #fff; border: none;
            border-radius: 12px; font-size: 18px; font-weight: 700; cursor: pointer;
            transition: 0.3s; box-shadow: 0 5px 15px rgba(197, 34, 34, 0.4);
        }
        .submit-btn:hover { background: #dc2626; transform: translateY(-2px); box-shadow: 0 7px 18px rgba(197, 34, 34, 0.5); }

        /* --- Modal Styles --- */
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5);
            display: flex; justify-content: center; align-items: center; z-index: 1000;
            opacity: 0; pointer-events: none; transition: opacity 0.3s ease;
        }
        .modal-overlay.visible { opacity: 1; pointer-events: auto; }
        .modal-content {
            background: white; padding: 25px; border-radius: 15px; text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3); max-width: 300px; width: 90%;
            transform: scale(0.9); transition: transform 0.3s ease;
        }
        .modal-overlay.visible .modal-content { transform: scale(1); }
        .modal-content p { font-size: 1rem; color: #333; margin: 0 0 20px 0; }
        .modal-close-btn {
            background: #ec5757; color: white; border: none; border-radius: 8px;
            padding: 10px 20px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: 0.3s;
        }
        .modal-close-btn:hover { background: #dc2626; }

        /* --- Media Queries --- */
        @media (max-width: 600px) {
            body { padding: 10px; }
            .quiz-container { padding: 20px; }
            h2 { font-size: 1.5rem; margin-top: 30px; }
            .exit-btn { top: 10px; right: 10px; padding: 6px 12px; font-size: 12px; }
            .quiz-info { font-size: 1rem; }
            .question-text { font-size: 1.1rem; }
            .navigation-buttons { margin-top: 20px; justify-content: center; /* Center buttons */ }
            .nav-btn, .submit-btn { padding: 12px 20px; font-size: 1rem; flex-grow: 1; min-width: 130px; }
            .submit-btn { order: 3; } /* Ensure submit is last if wrapped */
        }
    </style>
</head>
<body>
    <div class="quiz-container">
        <button class="exit-btn" onclick="window.location.href = '../Main/main.php'">✕ Exit</button>
        <h2>✍️ Level <?php echo $current_level; ?>: Fill-in-the-Blank ✍️</h2>
        
        <div class="quiz-info">
            <span id="question-counter">Question 1 of <?php echo count($questions); ?></span> | Max Stars: <?php echo $total_stars_available; ?> ⭐
        </div>

        <form action="process_quiz.php" method="POST" id="quiz-form">
            <input type="hidden" name="level" value="<?php echo $current_level; ?>">
            <input type="hidden" name="quiz" value="<?php echo $quiz_number_in_level; ?>">
            <input type="hidden" name="quiz_type" value="fillblank">
            <input type="hidden" name="total_stars_available" value="<?php echo $total_stars_available; ?>">

            <?php foreach ($questions as $index => $q): ?>
                <div class="question-wrapper <?php if ($index === 0) echo 'active-question'; ?>" 
                    data-answer="<?php echo htmlspecialchars(strtolower(trim($q['answer']))); ?>">
                    <label for="q_<?php echo $index; ?>" class="question-text">
                        <?php echo ($index + 1) . ". " . htmlspecialchars($q['q']); ?>
                        <span style="font-size: 0.8em; font-weight: 400; color: #a31616;"> (Reward: <?php echo $q['stars']; ?> ⭐)</span>
                    </label>
                    
                    <div class="answer-area">
                        <input 
                            type="text" 
                            name="q_<?php echo $index; ?>" 
                            id="q_<?php echo $index; ?>" 
                            placeholder="Type the missing Filipino word..." 
                            required
                            autocomplete="off" 
                            spellcheck="false"
                        >
                        <span class="feedback-message"></span>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="navigation-buttons">
                 <button type="button" id="next-btn" class="nav-btn">Next ➡️</button>
                <button type="submit" id="submit-btn" class="submit-btn hidden" >
                    Submit All Answers
                </button>
            </div>
        </form>
    </div>

    <div id="alert-modal" class="modal-overlay">
        <div class="modal-content">
            <p id="alert-message"></p>
            <button id="alert-close-btn" class="modal-close-btn">OK</button>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const questions = document.querySelectorAll('.question-wrapper');
    const nextBtn = document.getElementById('next-btn');
    const submitBtn = document.getElementById('submit-btn');
    const questionCounter = document.getElementById('question-counter');
    const form = document.getElementById('quiz-form'); // Get form element

    // --- Custom Alert ---
    const alertModal = document.getElementById('alert-modal');
    const alertMessage = document.getElementById('alert-message');
    const alertCloseBtn = document.getElementById('alert-close-btn');
    function showCustomAlert(message) {
        alertMessage.textContent = message;
        alertModal.classList.add('visible');
    }
    alertCloseBtn.addEventListener('click', () => alertModal.classList.remove('visible'));
    // --- End Custom Alert ---

    let currentQuestionIndex = 0;
    const totalQuestions = questions.length;
    let isPausedOnIncorrect = false; 

    function updateQuizState(clearFeedback = true) { 
        if (clearFeedback) {
             isPausedOnIncorrect = false; 
             nextBtn.textContent = 'Next ➡️'; 
        }

        questions.forEach((q, index) => {
            q.classList.toggle('active-question', index === currentQuestionIndex);
             if (clearFeedback) {
                 const input = q.querySelector('input[type="text"]');
                 const feedback = q.querySelector('.feedback-message');
                 if (input) {
                     input.classList.remove('correct', 'incorrect');
                     // Don't clear value here, let user see their answer
                 }
                 if (feedback) {
                     feedback.innerHTML = ''; 
                     feedback.className = 'feedback-message';
                 }
             }
        });
        questionCounter.textContent = `Question ${currentQuestionIndex + 1} of ${totalQuestions}`;
        nextBtn.classList.toggle('hidden', currentQuestionIndex === totalQuestions - 1);
        submitBtn.classList.toggle('hidden', currentQuestionIndex !== totalQuestions - 1);
        
        const activeInput = questions[currentQuestionIndex].querySelector('input[type="text"]');
        if (activeInput && clearFeedback) { 
             setTimeout(() => activeInput.focus(), 0); 
        }
    }

    function isCurrentQuestionAnswered() {
        const currentInput = questions[currentQuestionIndex].querySelector('input[type="text"]');
        return currentInput.value.trim() !== '';
    }

    function checkAnswer(inputElement) {
        const userAnswer = inputElement.value.trim().toLowerCase();
        const correctAnswer = inputElement.closest('.question-wrapper').getAttribute('data-answer'); // Get from parent wrapper
        const feedbackSpan = inputElement.nextElementSibling; 

        inputElement.classList.remove('correct', 'incorrect');
        feedbackSpan.classList.remove('correct', 'incorrect');
        feedbackSpan.innerHTML = ''; 

        if (userAnswer === '') {
             // Should be caught by isCurrentQuestionAnswered, but safety check
             feedbackSpan.textContent = ''; 
             return false; 
        }

        if (userAnswer === correctAnswer) {
            inputElement.classList.add('correct');
            feedbackSpan.classList.add('correct');
            feedbackSpan.textContent = '✅ Correct!';
            return true; // Correct
        } else {
            inputElement.classList.add('incorrect');
            feedbackSpan.classList.add('incorrect');
            feedbackSpan.innerHTML = `❌ Incorrect. The correct answer is: <strong>${correctAnswer}</strong>`;
            return false; // Incorrect
        }
    }

    // --- Event Listeners ---
    nextBtn.addEventListener('click', function() {
        const currentInput = questions[currentQuestionIndex].querySelector('input[type="text"]');
        
        if (isPausedOnIncorrect) {
            if (currentQuestionIndex < totalQuestions - 1) {
                currentQuestionIndex++;
                updateQuizState(); // Clear feedback by default
            }
            return; 
        }
        
        if (!isCurrentQuestionAnswered()) {
            showCustomAlert('Please type an answer before proceeding.');
            return; 
        }

        const isCorrect = checkAnswer(currentInput); 

        if (isCorrect) {
            if (currentQuestionIndex < totalQuestions - 1) {
                 // Slight delay to see feedback
                 setTimeout(() => {
                     currentQuestionIndex++;
                     updateQuizState(); // Clear feedback by default
                 }, 500); 
            }
        } else {
            isPausedOnIncorrect = true;
            nextBtn.textContent = 'Continue ➡️'; 
            updateQuizState(false); // Keep feedback
        }
    });


    submitBtn.addEventListener('click', function(event) {
        const lastInput = questions[totalQuestions - 1].querySelector('input[type="text"]');

        if (!isCurrentQuestionAnswered()) {
             event.preventDefault(); 
             showCustomAlert('Please answer the last question before submitting.');
             return; 
         }

        // Check the answer one last time
        const isCorrect = checkAnswer(lastInput); 

        // If incorrect, prevent immediate submission only ONCE
        if (!isCorrect && !isPausedOnIncorrect) { 
             event.preventDefault();
             isPausedOnIncorrect = true; 
             updateQuizState(false); // Keep feedback visible
        } else {
             isPausedOnIncorrect = false; // Allow submission
             // Explicitly remove incorrect class before submitting, just in case
             lastInput.classList.remove('incorrect'); 
             lastInput.nextElementSibling.classList.remove('incorrect');
             lastInput.nextElementSibling.innerHTML = ''; // Clear feedback message text
        }
    });
    
    // --- Allow Enter key to trigger Next/Submit ---
    form.addEventListener('keydown', function(event) {
         if (event.key === 'Enter') {
             event.preventDefault(); // Prevent default form submission on Enter
             const isActiveInput = event.target.tagName === 'INPUT' && event.target.closest('.question-wrapper.active-question');
             if (isActiveInput) {
                  if (currentQuestionIndex === totalQuestions - 1) {
                       // If on the last question, simulate Submit button click
                       submitBtn.click();
                  } else {
                       // Otherwise, simulate Next button click
                       nextBtn.click();
                  }
             }
         }
    });

    // --- Initial State ---
    updateQuizState();
});
</script>
</body>
</html>
