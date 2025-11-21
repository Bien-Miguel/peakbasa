<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Check for the result message set in process_quiz.php
$message = $_SESSION['result_message'] ?? "Congratulations! You have completed all levels!";
unset($_SESSION['result_message']); // Clear the message after displaying

$username = htmlspecialchars($_SESSION['username']);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game Complete! 🏆 - PeakBasa</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body { 
            font-family: 'Poppins', sans-serif; 
            background: linear-gradient(135deg, #fcdcdcff 0%, #fdf0f0 100%);
            display: flex; 
            justify-content: center; 
            align-items: center; /* Re-centered */
            min-height: 100vh; 
            padding: 20px;
            position: relative;
            overflow: hidden; /* Re-hidden to prevent scrollbars from partial items */
        }
        
        /* --- Animated background decorations --- */
        .star-decoration {
            position: absolute;
            font-size: 24px;
            animation: float 6s ease-in-out infinite;
            opacity: 0.3;
            will-change: transform;
        }
        /* ... existing star positions ... */
        .star-1 { top: 10%; left: 15%; animation-delay: 0s; }
        .star-2 { top: 20%; right: 20%; animation-delay: -1.5s; }
        .star-3 { bottom: 15%; left: 10%; animation-delay: -3s; }
        .star-4 { bottom: 25%; right: 15%; animation-delay: -4.5s; }
        .star-5 { top: 50%; left: 5%; animation-delay: -2s; }
        .star-6 { top: 55%; right: 8%; animation-delay: -5s; }
        
        @keyframes float {
            /* ... existing keyframes ... */
            0%   { transform: translateY(0px) rotate(0deg); }
            25%  { transform: translateY(-15px) rotate(5deg); }
            50%  { transform: translateY(0px) rotate(0deg); }
            75%  { transform: translateY(15px) rotate(-5deg); }
            100% { transform: translateY(0px) rotate(0deg); }
        }
        
        .container { 
            background: #fff; 
            border-radius: 20px; 
            box-shadow: 0 8px 18px rgba(197, 34, 34, 0.15);
            padding: 50px 40px;
            text-align: center; 
            max-width: 600px;
            width: 100%;
            position: relative;
            z-index: 1;
            border: 3px solid #fdecec;
            /* margin: auto 0; -- Removed for align-items: center */
        }
        
        .trophy-icon {
            font-size: 80px;
            margin-bottom: 20px;
            animation: bounce 1s ease infinite;
        }
        
        @keyframes bounce {
            /* ... existing keyframes ... */
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        h1 { 
            color: #ec5757; 
            margin-bottom: 25px; 
            font-size: 2.5rem;
            font-weight: 800;
            text-shadow: 2px 2px 4px rgba(236, 87, 87, 0.1);
        }
        
        .message-box {
            background: linear-gradient(135deg, #fdecec 0%, #fdf6f6 100%);
            border-left: 4px solid #ec5757;
            border-radius: 12px;
            padding: 20px;
            margin: 25px 0;
            color: #7c4646;
            font-size: 1.1rem;
            line-height: 1.6;
        }
        
        .congrats-text {
            color: #7c4646;
            font-size: 1.15rem;
            margin: 20px 0 35px;
            line-height: 1.8;
            font-weight: 500;
        }
        
        .button-group {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-top: 30px;
        }
        
        .action-btn { 
            padding: 16px 30px;
            border: none; 
            border-radius: 12px; 
            font-size: 1.1rem; 
            font-weight: 700;
            cursor: pointer; 
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        
        /* ... existing button styles ... */
        .main-btn { 
            background: #ec5757;
            color: white;
        }
        
        .main-btn:hover { 
            background: #dc2626;
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(236, 87, 87, 0.4);
        }
        
        .reset-btn { 
            background: #f8f9fa;
            color: #7c4646;
            border: 2px solid #fad1d1;
        }
        
        .reset-btn:hover { 
            background: #fdecec;
            border-color: #ec5757;
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(236, 87, 87, 0.2);
        }
        
        .certificate-btn {
            background: #1e88e5;
            color: white;
        }
        .certificate-btn:hover {
            background: #1565c0;
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(30, 136, 229, 0.4);
        }
        .certificate-sent-notice {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 12px;
            border-radius: 12px;
            font-weight: 600;
            border: 2px solid #c8e6c9;
        }
        
        .mountain-icon {
            font-size: 50px;
            margin: 20px 0;
            opacity: 0.8;
        }
        
        /* ... existing confetti styles ... */
        .confetti {
            position: absolute;
            width: 8px;
            height: 16px;
            background: #ec5757;
            top: -20px; /* Start off-screen */
            opacity: 1;
            animation: confetti-fall 5s linear infinite;
        }
        
        @keyframes confetti-fall {
            0% {
                transform: translateY(0) rotate(0deg);
                opacity: 1;
            }
            100% {
                transform: translateY(110vh) rotate(720deg);
                opacity: 0;
            }
        }
        
        .confetti:nth-child(1) { left: 10%; animation-delay: 0s; }
        .confetti:nth-child(2) { left: 20%; animation-delay: -2s; background: #fad1d1; }
        .confetti:nth-child(3) { left: 30%; animation-delay: -1s; background: #fdecec; }
        .confetti:nth-child(4) { left: 40%; animation-delay: -1.5s; background: #1e88e5; }
        .confetti:nth-child(5) { left: 50%; animation-delay: -0.5s; background: #ffc107; }
        .confetti:nth-child(6) { left: 60%; animation-delay: -2.5s; background: #ec5757; }
        .confetti:nth-child(7) { left: 70%; animation-delay: -1s; background: #fad1d1; }
        .confetti:nth-child(8) { left: 80%; animation-delay: -0.2s; background: #1e88e5; }
        .confetti:nth-child(9) { left: 90%; animation-delay: -1.2s; background: #ffc107; }
        .confetti:nth-child(10) { left: 15%; animation-delay: -0.7s; background: #ec5757; }
        .confetti:nth-child(11) { left: 25%; animation-delay: -1.8s; background: #fad1d1; }
        .confetti:nth-child(12) { left: 35%; animation-delay: -0.4s; background: #1e88e5; }
        .confetti:nth-child(13) { left: 45%; animation-delay: -2.2s; background: #ffc107; }
        .confetti:nth-child(14) { left: 55%; animation-delay: -0.9s; background: #ec5757; }
        .confetti:nth-child(15) { left: 65%; animation-delay: -1.4s; background: #fad1d1; }
        
        /* ... existing modal styles ... */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }
        .modal-overlay.visible {
            opacity: 1;
            pointer-events: auto;
        }
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            max-width: 400px;
            width: 90%;
            transform: scale(0.9);
            transition: transform 0.3s ease;
        }
        .modal-overlay.visible .modal-content {
            transform: scale(1);
        }
        .modal-content h3 {
            color: #ec5757;
            font-size: 1.5rem;
            margin-bottom: 15px;
        }
        .modal-content p {
            font-size: 1.05rem;
            color: #333;
            margin: 0 0 25px 0;
            line-height: 1.6;
        }
        .modal-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        .modal-btn {
            padding: 10px 25px;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }
        .modal-btn-confirm {
            background: #ec5757;
            color: white;
        }
        .modal-btn-confirm:hover {
            background: #dc2626;
        }
        .modal-btn-cancel {
            background: #f1f1f1;
            color: #333;
        }
        .modal-btn-cancel:hover {
            background: #e0e0e0;
        }
        /* --- End Modal Styles --- */


        /*
        ==================================================================
        == EDITED: More aggressive vertical shrinking for mobile
        ==================================================================
        */
        @media (max-width: 600px) {
            body {
                padding: 15px 10px; /* Tighter padding */
                overflow: hidden; /* Prevent scroll as requested */
                display: block; /* Use block for top-alignment */
            }
            .container { 
                padding: 25px 20px; /* Less vertical padding */
                margin: 15px 0; /* Let it sit at the top */
            }
            .trophy-icon { 
                font-size: 45px; /* Smaller */
                margin-bottom: 10px; 
            }
            h1 { 
                font-size: 1.7rem; /* Smaller */
                margin-bottom: 10px; 
            }
            .message-box {
                margin: 10px 0; /* Less margin */
                padding: 12px; /* Less padding */
            }
            .mountain-icon {
                font-size: 35px; /* Smaller */
                margin: 10px 0;
            }
            .congrats-text {
                font-size: 0.95rem; /* Smaller */
                margin: 10px 0 20px; /* Less margin */
                line-height: 1.6;
            }
            .button-group {
                margin-top: 15px; /* Less margin */
                gap: 8px; /* Less gap */
            }
            .action-btn { 
                font-size: 0.95rem; 
                padding: 12px 15px; /* Less padding */
            }
            
            /* Responsive modal */
            .modal-content {
                padding: 25px;
            }
            .modal-content h3 {
                font-size: 1.3rem;
            }
            .modal-content p {
                font-size: 1rem;
            }
            .modal-buttons {
                flex-direction: column-reverse; /* Stack buttons */
            }
            .modal-btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Floating decorations -->
    <div class="star-decoration star-1">⭐</div>
    <div class="star-decoration star-2">🎉</div>
    <div class="star-decoration star-3">🏆</div>
    <div class="star-decoration star-4">⭐</div>
    <!-- ADDED more stars -->
    <div class="star-decoration star-5">✨</div>
    <div class="star-decoration star-6">🎊</div>
    
    <!-- IMPROVED: More confetti elements -->
    <!-- ... existing confetti ... -->
    <div class="confetti"></div>
    <div class="confetti"></div>
    <div class="confetti"></div>
    <div class="confetti"></div>
    <div class="confetti"></div>
    <div class="confetti"></div>
    <div class="confetti"></div>
    <div class="confetti"></div>
    <div class="confetti"></div>
    <div class="confetti"></div>
    <div class="confetti"></div>
    <div class="confetti"></div>
    <div class="confetti"></div>
    <div class="confetti"></div>
    <div class="confetti"></div>
    
    <div class="container">
        <!-- ... existing container HTML ... -->
        <div class="trophy-icon">🏆</div>
        
        <h1>Congratulations, <?php echo $username; ?>!</h1>
        
        <div class="message-box">
            <?php echo $message; ?>
        </div>
        
        <div class="mountain-icon">🏔️</div>
        
        <p class="congrats-text">
            You've reached the peak of your Filipino language journey! 
            You've conquered every challenge and earned your place at the summit. 
            <br><br>
            <strong>Mabuhay!</strong> 🎊
        </p>
        
        <div class="button-group">
            <?php if (!isset($_SESSION['certificate_sent'])): ?>
            <a href="generate_certificate.php" class="action-btn certificate-btn">
                📜 Get Your Certificate
            </a>
            <?php else: ?>
            <div class="certificate-sent-notice">
                ✅ Certificate sent to your email!
            </div>
            <?php endif; ?>
            
            <a href="../Main/main.php" class="action-btn main-btn">
                🏠 Return to Main Menu
            </a>
            
            <button type="button" class="action-btn reset-btn" onclick="openResetModal()">
                🔄 Start New Journey
            </button>
        </div>
    </div>

    <!-- ... existing modal HTML ... -->
    <div id="reset-modal" class="modal-overlay">
        <div class="modal-content">
            <h3>Start New Journey?</h3>
            <p>
                Are you sure you want to reset your progress? All your stars and
                achievements will be lost. This action cannot be undone.
            </p>
            <div class="modal-buttons">
                <button type="button" class="modal-btn modal-btn-cancel" onclick="closeResetModal()">
                    Cancel
                </button>
                <a href="reset_progress.php" class="modal-btn modal-btn-confirm">
                    Yes, Reset Progress
                </a>
            </div>
        </div>
    </div>

    <script>
        // ... existing script ...
        // --- Modal JavaScript ---
        const modal = document.getElementById('reset-modal');
        
        function openResetModal() {
            if (modal) {
                modal.classList.add('visible');
            }
        }

        function closeResetModal() {
            if (modal) {
                modal.classList.remove('visible');
            }
        }
        
        // Close modal if clicking on the overlay
        if (modal) {
            modal.addEventListener('click', function(event) {
                if (event.target === modal) {
                    closeResetModal();
                }
            });
        }
    </script>
</body>
</html>

