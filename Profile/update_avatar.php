<?php
session_start();
require_once '../conn.php'; // Correct path if conn.php is in /peakbasa/

// --- 1. SESSION CHECK ---
// ==================================================================
// == THIS IS THE FIX: Allow EITHER student OR teacher
// ==================================================================
if (!isset($_SESSION['user_id']) && !isset($_SESSION['teacher_id'])) {
    // If NEITHER is set, redirect to login
    header("Location: ../Verification/login.php"); 
    exit;
}

// Determine role and username based on session
if (isset($_SESSION['teacher_id'])) {
    $username = $_SESSION['teacher_username'] ?? null; 
    $role = 'teacher';
} elseif (isset($_SESSION['user_id'])) {
    $username = $_SESSION['username'] ?? null; 
    $role = 'student';
} else {
    // Fallback
    header("Location: ../Verification/login.php"); 
    exit;
}

// Ensure username is actually set
if (empty($username)) {
     $_SESSION['upload_message'] = "Error: User session invalid.";
     header("Location: ../Main/profilemain.php"); // Redirect back to profile
     exit;
}
// ==================================================================
// == END FIX
// ==================================================================


// 2. Get data from the form
$avatar_filename = $_POST['avatar'] ?? ''; // e.g., "1.png"

// 3. Validate that an avatar filename was actually received
if (empty($avatar_filename)) {
    $_SESSION['upload_message'] = "No avatar selected.";
    header("Location: ../Main/profilemain.php"); // Correct redirect path
    exit;
}

// --- Basic Filename Sanitization (Allow alphanumeric, dot, hyphen, underscore) ---
if (!preg_match('/^[a-zA-Z0-9.\-\_]+$/', $avatar_filename)) {
    $_SESSION['upload_message'] = "Invalid avatar filename format.";
    header("Location: ../Main/profilemain.php"); 
    exit;
}
// You might add a check here to ensure $avatar_filename actually exists in ../avatars/

// 4. Determine which table to update based on role
$table_to_update = ($role == 'teacher') ? 'teachers' : 'users';

// 5. Securely update the database
$sql = "UPDATE $table_to_update SET photo = ? WHERE username = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
     $_SESSION['upload_message'] = "Database error (prepare): " . $conn->error;
} else {
    $stmt->bind_param("ss", $avatar_filename, $username);

    if ($stmt->execute()) {
        $_SESSION['upload_message'] = "Avatar updated successfully!";
    } else {
        $_SESSION['upload_message'] = "Error updating avatar: " . $stmt->error;
    }
    $stmt->close();
}

$conn->close();

// 6. Redirect back to the profile page
header("Location: ../Main/profilemain.php"); // Correct redirect path
exit;
?>
