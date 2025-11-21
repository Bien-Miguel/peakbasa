<?php
session_start();
require_once '../conn.php';

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
    // Should not happen due to the check above, but as a fallback:
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


$message = ""; // Variable to hold feedback

// 2. Check for a valid file upload
if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0) {
    
    // --- File Handling Logic ---
    $targetDir = "../uploads/profile_pics/"; // Relative path from this script's location
    // Sanitize filename slightly, though basename() helps
    $safe_basename = preg_replace("/[^a-zA-Z0-9\.\-\_]/", "", basename($_FILES["profile_pic"]["name"]));
    $fileName = time() . "_" . $safe_basename;
    $targetFile = $targetDir . $fileName;

    // Make sure folder exists
    if (!file_exists($targetDir)) {
        // Attempt to create directory recursively
        if (!mkdir($targetDir, 0777, true)) {
            $message = "Error: Failed to create upload directory. Check permissions.";
            $_SESSION['upload_message'] = $message;
            header("Location: ../Main/profilemain.php");
            exit;
        }
    }
    
    // --- Validate File Type (Basic Example) ---
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $fileType = mime_content_type($_FILES["profile_pic"]["tmp_name"]);
    if (!in_array($fileType, $allowedTypes)) {
         $message = "Error: Only JPG, PNG, and GIF files are allowed.";
         $_SESSION['upload_message'] = $message;
         header("Location: ../Main/profilemain.php");
         exit;
    }

    // --- Database Update Logic ---
    if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $targetFile)) {
        
        // Determine the correct table based on role
        $table_to_update = ($role == 'teacher') ? 'teachers' : 'users';

        // Use Prepared Statements for security
        $sql = "UPDATE $table_to_update SET photo=? WHERE username=?";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
             $message = "Database error (prepare): " . $conn->error;
        } else {
            $stmt->bind_param("ss", $fileName, $username); 

            if ($stmt->execute()) {
                $message = "Photo uploaded and profile updated successfully!";
            } else {
                $message = "Photo uploaded, but database update failed: " . $stmt->error;
            }
            $stmt->close();
        }

    } else {
        // Provide more specific error if possible
        $upload_error = $_FILES['profile_pic']['error']; // Get the specific error code
        $message = "File upload failed. Error code: {$upload_error}. Check server permissions for '$targetDir'.";
    }
} else {
    // Handle other upload errors (e.g., no file selected)
    $error_code = $_FILES['profile_pic']['error'] ?? UPLOAD_ERR_NO_FILE; // Default to no file error
    if ($error_code === UPLOAD_ERR_NO_FILE) { // UPLOAD_ERR_NO_FILE is 4
        $message = "No file was selected.";
    } elseif ($error_code === UPLOAD_ERR_INI_SIZE || $error_code === UPLOAD_ERR_FORM_SIZE) {
         $message = "Upload failed: The file is too large.";
    } elseif ($error_code !== UPLOAD_ERR_OK) { // UPLOAD_ERR_OK is 0
        $message = "Upload failed. Error code: " . $error_code;
    } else {
        // If there's no error code but file isn't set, something else is wrong
        $message = "An unexpected error occurred during upload.";
    }
}

$conn->close(); // Close connection

// 3. Store the message and redirect back
$_SESSION['upload_message'] = $message;
// Corrected Redirect Path - Assuming profilemain.php is in /Main/
header("Location: ../Main/profilemain.php"); 
exit;
?>
