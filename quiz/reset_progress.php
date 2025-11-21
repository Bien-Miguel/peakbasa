<?php
session_start();
include 'ProgressManager.php'; 

// Check if user is logged in
if (!isset($_SESSION['username'], $_SESSION['user_id'])) { 
    header("Location: ../Verification/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
require_once '../conn.php';  
$pm = new ProgressManager($conn);

// Execute the reset operation
$reset_result = $pm->resetProgress($user_id);

// Set a message and redirect
if ($reset_result['status'] === 'success') {
    $_SESSION['message'] = "✅ Your learning progress has been completely reset. Welcome back to Level 1!";
    // Reset session variable for map display
    $_SESSION['unlocked_level'] = 1; 
} else {
    $_SESSION['message'] = "❌ Error during reset: " . $reset_result['message'];
}

header("Location: ../Main/main.php");
exit;
?>