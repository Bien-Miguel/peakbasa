<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Get the level and quiz number from the URL
$level = isset($_GET['level']) ? (int)$_GET['level'] : 1;
$quiz_number_in_level = isset($_GET['quiz']) ? (int)$_GET['quiz'] : 1;

// Define the three quiz types for the cycle
$quiz_types = [
    1 => 'quiz_truefalse.php', // Quiz 1 (and 4, 7...)
    2 => 'quiz_mcq.php',       // Quiz 2 (and 5, 8...)
    3 => 'quiz_fillbank.php',  // Quiz 3 (and 6, 9...)
];

// Determine the type of quiz based on its sequence (1, 2, or 3)
$quiz_type_key = ($quiz_number_in_level - 1) % 3 + 1;
$target_page = $quiz_types[$quiz_type_key];

// If Level 4, always go to the final challenge
if ($level === 4) {
    $target_page = 'final_challenge.php';
}

// Ensure the page exists before redirecting
if (isset($target_page)) {
    // Pass the level and quiz number to the final quiz page
    $redirect_url = $target_page . "?level=" . $level . "&quiz=" . $quiz_number_in_level;
    header("Location: " . $redirect_url);
    exit;
} else {
    // Fallback if something goes wrong with parameters
    header("Location: main.php");
    exit;
}

?>
