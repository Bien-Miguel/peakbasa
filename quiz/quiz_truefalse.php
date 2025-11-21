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
    // $user_id = 0; // Or keep teacher ID if needed: $_SESSION['teacher_id']
} elseif (isset($_SESSION['user_id'])) {
    $role = 'student';
    // $user_id = $_SESSION['user_id'];
} else {
    // Fallback just in case
    header("Location: ../Verification/login.php"); 
    exit;
}
// ==================================================================
// == END FIX
// ==================================================================


// 2. DYNAMIC FETCHING LOGIC (Using GET params)
$current_level = isset($_GET['level']) ? (int)$_GET['level'] : 1;
$quiz_number_in_level = isset($_GET['quiz']) ? (int)$_GET['quiz'] : 1; // Default for T/F

// Validate that the requested quiz exists and is the correct type
if (!isset($quiz_data[$current_level][$quiz_number_in_level]) || $quiz_data[$current_level][$quiz_number_in_level]['type'] !== 'truefalse') {
    // Redirect to main menu or quiz menu if data is invalid
    // echo "Debug: Invalid quiz data or type mismatch. Level=$current_level, QuizNum=$quiz_number_in_level, ExpectedType=truefalse"; // Temporary Debug
    // if(isset($quiz_data[$current_level][$quiz_number_in_level]['type'])) { echo ", ActualType=".$quiz_data[$current_level][$quiz_number_in_level]['type']; } // Temp Debug
    // exit; // Temporary Debug
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
<title>Level <?php echo $current_level; ?> Quiz <?php echo $quiz_number_in_level; ?> (True/False)</title>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');
    
    * { box-sizing: border-box; }
    
    body { 
        font-family:'Poppins', sans-serif; 
        background: linear-gradient(135deg, #fcdcdcff 0%, #fdf0f0 100%); 
        padding:20px; 
        display: flex; justify-content: center; align-items: center; 
        min-height: 100vh; margin: 0;
    }
    .quiz-container { 
        max-width:650px; width: 100%; margin:20px auto; 
        background:#fff; padding:30px; border-radius:20px; 
        box-shadow:0 8px 18px rgba(197, 34, 34, 0.15); 
        text-align:center; position: relative;
    }
    .exit-btn {
        position: absolute; top: 20px; right: 20px; background: #ef4444; color: white;
        border: none; border-radius: 8px; padding: 8px 16px; cursor: pointer;
        font-size: 14px; font-weight: 600; transition: 0.3s; z-index: 10;
    }
    .exit-btn:hover { background: #dc2626; transform: translateY(-2px); }
    
    h2 { 
        color:#ec5757; font-size: 28px; margin-bottom: 25px;
        border-bottom: 2px solid #fdecec; padding-bottom: 10px; margin-top: 0;
    }
    .quiz-info {
        font-size: 18px; color: #7c4646; font-weight: 600; margin-bottom: 30px;
    }
    .question-wrapper {
        text-align: left; margin-bottom: 30px; padding: 20px 0; /* Increased padding */
        border-top: 1px dashed #fcdcdc; display: none;
    }
    .question-wrapper.active-question { display: block; }
        
    .question-text {
        font-size: 1.15rem; font-weight: 600; color: #7c4646; margin-bottom: 20px; /* Increased margin */
    }
    .tof-option-group {
        display: flex; gap: 15px; justify-content: center; margin-top: 10px;
    }
    .tof-btn { 
        flex: 1; background:#fbf9f9ff; border: 2px solid #fad1d1; color:#7c4646; 
        padding:15px; margin:0; border-radius:12px; cursor:pointer; 
        font-size:18px; font-weight: 600; transition: 0.3s;
        display: flex; align-items: center; justify-content: center; /* Center content */
    }
    .tof-btn:hover { 
        border-color: #ec5757; background: #fdecec; transform: translateY(-2px);
    }
    .tof-btn input[type="radio"] {
        margin-right: 8px; transform: scale(1.2); accent-color: #ec5757;
    }

    /* Button Feedback Styles */
    .tof-option-group.answered .tof-btn {
        cursor: default; opacity: 0.7;
    }
    .tof-option-group.answered .tof-btn:hover {
        border-color: #fad1d1; background: #fbf9f9ff; transform: none; /* Reset hover */
    }
    .tof-btn.selected {
        opacity: 1;
    }
    .tof-btn.correct {
        border-color: #28a745; background-color: #e9f7ec; opacity: 1;
    }
    .tof-btn.incorrect {
        border-color: #dc3545; background-color: #fdecea; opacity: 1;
    }
    .tof-btn.reveal-correct {
         border: 3px dashed #28a745; opacity: 1;
    }
    .tof-option-group.answered input[type="radio"] {
        pointer-events: none;
    }
    .feedback-box { /* Container for text feedback */
        margin-top: 15px; padding: 10px; border-radius: 8px;
        font-weight: 600; font-size: 0.95rem; min-height: 1.5em; /* Reserve space */
        text-align: center; /* Center feedback text */
    }
    .feedback-box.correct { background-color: #e9f7ec; color: #28a745; }
    .feedback-box.incorrect { background-color: #fdecea; color: #dc3545; }
    
    .navigation-buttons {
        display: flex; 
        justify-content: flex-end; 
        margin-top: 40px;
        gap: 15px; /* Add gap */
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
         .tof-option-group { flex-direction: column; gap: 10px; } /* Stack buttons vertically */
         .tof-btn { font-size: 1rem; padding: 12px; }
         .navigation-buttons { margin-top: 30px; justify-content: center; } /* Center buttons */
         .nav-btn, .submit-btn { padding: 12px 20px; font-size: 1rem; flex-grow: 1; min-width: 130px; }
     }
</style>
</head>
<body>
    <div class="quiz-container">
        <button class="exit-btn" onclick="window.location.href = '../Main/main.php'">✕ Exit</button>
        <h2>⚡ Level <?php echo $current_level; ?>: True or False Challenge ⚡</h2>
        
        <div class="quiz-info">
            <span id="question-counter">Question 1 of <?php echo count($questions); ?></span> | Max Stars: <?php echo $total_stars_available; ?> ⭐
        </div>

        <!-- Action points to process_quiz_fixed.php -->
        <form action="process_quiz.php" method="POST" id="quiz-form">
            <input type="hidden" name="level" value="<?php echo $current_level; ?>">
            <input type="hidden" name="quiz" value="<?php echo $quiz_number_in_level; ?>">
            <input type="hidden" name="quiz_type" value="truefalse">
            <input type="hidden" name="total_stars_available" value="<?php echo $total_stars_available; ?>">

            <?php foreach ($questions as $index => $q): 
                // Convert PHP boolean/string to 'true'/'false' string for data attribute
                 $correctAnswerString = ($q['answer'] === true || strtolower(trim($q['answer'])) === 'true') ? 'true' : 'false';
            ?>
                <div class="question-wrapper <?php if ($index === 0) echo 'active-question'; ?>" 
                     data-answer="<?php echo $correctAnswerString; ?>"> 
                    <p class="question-text">
                        <?php echo ($index + 1) . ". " . htmlspecialchars($q['q']); ?>
                        <span style="font-size: 0.8em; font-weight: 400; color: #a31616;"> (Reward: <?php echo $q['stars']; ?> ⭐)</span>
                    </p>
                    
                    <div class="tof-option-group">
                        <label class="tof-btn" data-option-value="true">
                            <input type="radio" name="q_<?php echo $index; ?>" value="true" required>
                            True ✅
                        </label>
                        <label class="tof-btn" data-option-value="false">
                            <input type="radio" name="q_<?php echo $index; ?>" value="false" required>
                            False ❌
                        </label>
                    </div>
                    <div class="feedback-box"></div>
                </div>
            <?php endforeach; ?>

            <div class="navigation-buttons">
                <button type="button" id="next-btn" class="nav-btn">Next ➡️</button>
                <button type="submit" id="submit-btn" class="submit-btn hidden">
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
    // Identical JS from previous version, handles T/F feedback and navigation
    document.addEventListener('DOMContentLoaded', function() {
        const questions = document.querySelectorAll('.question-wrapper');
        const nextBtn = document.getElementById('next-btn');
        const submitBtn = document.getElementById('submit-btn');
        const questionCounter = document.getElementById('question-counter');
        
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
                    const optionGroup = q.querySelector('.tof-option-group');
                    const feedbackBox = q.querySelector('.feedback-box');
                    if (optionGroup) {
                        optionGroup.classList.remove('answered');
                        optionGroup.querySelectorAll('.tof-btn').forEach(lbl => {
                            lbl.classList.remove('selected', 'correct', 'incorrect', 'reveal-correct');
                        });
                    }
                    if (feedbackBox) {
                        feedbackBox.innerHTML = '';
                        feedbackBox.className = 'feedback-box'; // Reset classes
                    }
                }
            });
            questionCounter.textContent = `Question ${currentQuestionIndex + 1} of ${totalQuestions}`;
            nextBtn.classList.toggle('hidden', currentQuestionIndex === totalQuestions - 1);
            submitBtn.classList.toggle('hidden', currentQuestionIndex !== totalQuestions - 1);
        }

        function getSelectedRadio(questionWrapper) {
            return questionWrapper.querySelector('input[type="radio"]:checked');
        }

        function isCurrentQuestionAnswered() {
            return getSelectedRadio(questions[currentQuestionIndex]) !== null;
        }
        
        function checkAnswerTF() {
            const currentQuestion = questions[currentQuestionIndex];
            const selectedRadio = getSelectedRadio(currentQuestion);
            const optionGroup = currentQuestion.querySelector('.tof-option-group');
            const feedbackBox = currentQuestion.querySelector('.feedback-box');
            const correctAnswer = currentQuestion.getAttribute('data-answer'); 
            
            optionGroup.classList.remove('answered');
            optionGroup.querySelectorAll('.tof-btn').forEach(lbl => {
                lbl.classList.remove('selected', 'correct', 'incorrect', 'reveal-correct');
            });
            feedbackBox.innerHTML = '';
            feedbackBox.className = 'feedback-box';

            if (!selectedRadio) { return false; } 

            const userAnswer = selectedRadio.value; 
            const selectedLabel = selectedRadio.closest('.tof-btn');
            
            optionGroup.classList.add('answered'); 
            selectedLabel.classList.add('selected');

            if (userAnswer === correctAnswer) {
                selectedLabel.classList.add('correct');
                feedbackBox.classList.add('correct');
                feedbackBox.textContent = '✅ Correct!';
                return true;
            } else {
                selectedLabel.classList.add('incorrect');
                feedbackBox.classList.add('incorrect');
                const correctAnswerText = correctAnswer === 'true' ? 'True ✅' : 'False ❌';
                feedbackBox.innerHTML = `❌ Incorrect. The correct answer is: <strong>${correctAnswerText}</strong>`;
                
                const correctLabel = optionGroup.querySelector(`.tof-btn[data-option-value="${correctAnswer}"]`);
                if (correctLabel) {
                    correctLabel.classList.add('reveal-correct');
                }
                return false;
            }
        }
        
        nextBtn.addEventListener('click', function() {
            if (isPausedOnIncorrect) {
                if (currentQuestionIndex < totalQuestions - 1) {
                    currentQuestionIndex++;
                    updateQuizState(); 
                }
                return; 
            }
            
            if (!isCurrentQuestionAnswered()) {
                typeof showCustomAlert === 'function' ? showCustomAlert('Please select True or False.') : alert('Please select True or False.');
                return; 
            }

            const isCorrect = checkAnswerTF(); 

            if (isCorrect) {
                if (currentQuestionIndex < totalQuestions - 1) {
                    setTimeout(() => {
                        currentQuestionIndex++;
                        updateQuizState();
                    }, 500); 
                }
            } else {
                isPausedOnIncorrect = true;
                nextBtn.textContent = 'Continue ➡️'; 
            }
        });

        submitBtn.addEventListener('click', function(event) {
             if (!isCurrentQuestionAnswered()) {
                 event.preventDefault(); 
                 typeof showCustomAlert === 'function' ? showCustomAlert('Please answer the last question.') : alert('Please answer the last question.');
                 return; 
             }
            
             const isCorrect = checkAnswerTF(); 
             
             if (!isCorrect && !isPausedOnIncorrect) { 
                  event.preventDefault();
                  isPausedOnIncorrect = true; 
             } else {
                  isPausedOnIncorrect = false; 
             }
        });

        // --- Initial State ---
        updateQuizState();
    });
    </script>
</body>
</html>

