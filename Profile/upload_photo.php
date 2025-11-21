<?php
session_start();
// 1. Use your standard connection file
require_once '../conn.php';

// 2. Check for both username AND role
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: /peakbasa/Main/login.php");
    exit;
}

// 3. Get session data
$username = $_SESSION['username'];
$role = $_SESSION['role'];
$message = ""; // Variable to hold feedback

// 4. Check for a valid file upload
if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0) {
    
    // --- File Handling Logic (This was correct) ---
    // Assumes this file is in 'Profile' and 'uploads' is also in 'Profile'
    $targetDir = "../uploads/profile_pics/"; 
    $fileName = time() . "_" . basename($_FILES["profile_pic"]["name"]);
    $targetFile = $targetDir . $fileName;

    // Make sure folder exists
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    // --- 5. Database Update Logic (THE FIX) ---
    if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $targetFile)) {
        
        // ⭐️ --- START OF THE FIX --- ⭐️
        
        $table_to_update = '';
        
        // Check the role and set the correct table name
        if ($role == 'teacher') {
            $table_to_update = 'teachers';
        } else {
            $table_to_update = 'users';
        }

        // ⭐️ --- SECURITY FIX (Prepared Statement) --- ⭐️
        // We use a variable for the table name (which is safe, since we set it)
        // We use placeholders (?) for the user-supplied data to prevent SQL injection
        
        $sql = "UPDATE $table_to_update SET photo=? WHERE username=?";
        
        $stmt = $conn->prepare($sql);
        // "ss" means we are binding two strings (String, String)
        $stmt->bind_param("ss", $fileName, $username); 

        if ($stmt->execute()) {
            $message = "Photo uploaded and profile updated successfully!";
        } else {
            $message = "Photo uploaded, but database update failed: " . $stmt->error;
        }
        $stmt->close();
        
        // ⭐️ --- END OF THE FIX --- ⭐️

    } else {
        $message = "File upload failed. Check folder permissions for 'uploads/profile_pics'.";
    }
} else {
    // Handle other upload errors (e.g., no file selected)
    $error_code = $_FILES['profile_pic']['error'] ?? 'UNKNOWN';
    if ($error_code === 4) {
        $message = "No file was selected.";
    } elseif ($error_code !== 0) {
        $message = "Upload failed. Error code: " . $error_code;
    }
}

// 6. Store the message and redirect back
$_SESSION['upload_message'] = $message;
header("Location: /peakbasa/Main/profilemain.php");
exit;
?>