<?php
// Initialize Session
session_start();

// --- Include your database connection file ---
require_once '../conn.php'; // Uses DB_HOST, DB_USER, DB_PASS, DB_NAME from this file

// --- PHPMailer Class Imports ---
require '../vendor/autoload.php'; // Adjust path if needed
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// --- Email Configuration ---
$sender_email = 'peakbasa.website@gmail.com'; 
$sender_password = 'znnuuncqghttuppv'; // Your App Password
$recipient_email = 'peakbasa.website@gmail.com'; // Destination email

// Default feedback
$feedback_message = "";
$feedback_type = "error"; 

// Check if the form was submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Sanitize and validate form data
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? ''); 
    $message_body = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($message_body)) {
        $feedback_message = "All fields are required. Please go back and fill them in.";
        goto set_feedback_and_redirect; 
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $feedback_message = "Invalid email format provided. Please check your email address.";
        goto set_feedback_and_redirect;
    }

    // --- Database Interaction ---
    // The connection ($conn) is already established by conn.php
    if ($conn) { // Check if $conn exists and is valid
        try {
            // 2. Prepare the SQL statement
            $sql = "INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            
            if ($stmt === false) {
                error_log("SQL prepare failed: " . $conn->error);
                throw new Exception("Database error during preparation.");
            }
            
            // 3. Bind parameters and execute
            $stmt->bind_param("sss", $name, $email, $message_body);
            
            if (!$stmt->execute()) {
                 error_log("SQL execute failed: " . $stmt->error);
                 throw new Exception("Failed to save message to the database.");
            }
            
            $stmt->close();
            // Database part successful

        } catch (Exception $e) {
            $feedback_message = "There was an issue saving your message. Please try again later.";
            // Optionally log $e->getMessage() for admin
            goto set_feedback_and_redirect; // Jump to redirect
        }
        // No need to close $conn here if conn.php doesn't close it globally
    } else {
        $feedback_message = "Database connection object not found. Please contact admin.";
        goto set_feedback_and_redirect;
    }


    // --- Email Sending Logic ---
    try {
        $mail = new PHPMailer(true);

        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $sender_email; 
        $mail->Password = $sender_password;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
        $mail->Port = 587;
        $mail->SMTPDebug = 0; // 0 for production, 2 for debugging

        // Sender and Recipient
        $mail->setFrom($sender_email, 'PeakBasa Contact Form'); 
        $mail->addAddress($recipient_email); 
        $mail->addReplyTo($email, $name); 

        // Content
        $mail->isHTML(true); 
        $mail->Subject = "New Contact Message from: " . htmlspecialchars($name);
        $mail->Body = "<h2>New Message via PeakBasa Contact Form</h2>" .
                      "<p><strong>Name:</strong> " . htmlspecialchars($name) . "</p>" .
                      "<p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>" .
                      "<hr>" .
                      "<p><strong>Message:</strong></p>" .
                      "<p>" . nl2br(htmlspecialchars($message_body)) . "</p>"; 
        $mail->AltBody = "Name: " . $name . "\nEmail: " . $email . "\n\nMessage:\n" . $message_body;

        $mail->send();
        
        // Success
        $feedback_message = "Thank you, " . htmlspecialchars($name) . "! Your message has been sent successfully.";
        $feedback_type = "success"; 

    } catch (Exception $e) {
        error_log("PHPMailer Error: " . $mail->ErrorInfo); 
        $feedback_message = "Your message was saved, but there was an error sending the email notification.";
        $feedback_type = "warning"; 
    }

} else {
    $feedback_message = "Invalid request method.";
}

// =========================================================
// Set Feedback and Redirect Back
// =========================================================
set_feedback_and_redirect:
$_SESSION['feedback_message'] = $feedback_message;
$_SESSION['feedback_type'] = $feedback_type; 
header("Location: moreinfo.php#contact"); // Redirect back to contact section
exit;

?>