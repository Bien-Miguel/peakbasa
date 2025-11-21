<?php
session_start();
// --- Database Configuration ---
// IMPORTANT: Use your actual database credentials here.

// --- 1. Validate Input ---
if (!isset($_GET['user_id']) || !isset($_GET['status'])) {
    // If parameters are missing, redirect back to the user list
    $_SESSION['admin_message'] = "Error: Missing user or status parameter.";
    header("Location: admin_user.php");
    exit;
}

$target_user_id = (int)$_GET['user_id'];
$new_status = (int)$_GET['status']; // Expected: 1 for ban, 0 for unban

// Sanity check for status value (must be 0 or 1)
if ($new_status !== 0 && $new_status !== 1) {
    $_SESSION['admin_message'] = "Error: Invalid status value.";
    header("Location: admin_user.php");
    exit;
}

require_once '../conn.php'; // Include the database connection

// --- 3. Prepare and Execute the Update Query ---
// The query updates the 'is_banned' column for the specific user ID.
$stmt = $conn->prepare("UPDATE users SET is_banned = ? WHERE user_id = ?");

$stmt->bind_param("ii", $new_status, $target_user_id); // 'ii' means two integer parameters

if ($stmt->execute()) {
    // Success. Set a status message
    $action = ($new_status == 1) ? "Banned" : "Unbanned";
    $_SESSION['admin_message'] = "User ID {$target_user_id} was successfully {$action}.";
} else {
    // Error handling
    $_SESSION['admin_message'] = "Error updating user status: " . $stmt->error;
}

$stmt->close();
$conn->close();

// --- 4. Redirect back to the Admin Dashboard ---
header("Location: admin_user.php");
exit;
?>
