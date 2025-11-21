<?php
session_start();

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error_message'] = "Access denied. Admin privileges required.";
    header("Location: ../../Verification/login.php"); // Updated path
    exit();
}

// Establish database connection
require_once '../conn.php'; // Updated path since we're in adminPage folder

// Fetch all messages, newest first
$sql = "SELECT id, name, email, message, submitted_at FROM contact_messages ORDER BY submitted_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Messages</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
        .message-card { transition: all 0.3s ease; }
        .message-card:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
    </style>
</head>
<body>
    <div class="container mx-auto p-4 md:p-8">
        <!-- Navigation Header -->
        <div class="mb-4">
            <a href="../Main/main.php" class="inline-flex items-center text-indigo-600 hover:text-indigo-800 font-semibold transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Main
            </a>
        </div>

        <header class="bg-indigo-700 text-white p-6 rounded-lg shadow-xl mb-8">
            <h1 class="text-3xl font-bold">Admin Message Dashboard</h1>
            <p class="text-indigo-200 mt-1">Total Messages: <?= $result ? $result->num_rows : 0 ?></p>
        </header>

        <div class="space-y-6">
            <?php
            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo '<div class="message-card bg-white p-6 rounded-xl shadow-md border-l-4 border-indigo-500">';
                    
                    echo '<div class="flex justify-between items-start mb-4 border-b pb-2">';
                    echo '<div>';
                    echo '<h3 class="text-xl font-semibold text-gray-800">' . htmlspecialchars($row['name']) . '</h3>';
                    echo '<a href="mailto:' . htmlspecialchars($row['email']) . '" class="text-indigo-600 hover:underline text-sm">' . htmlspecialchars($row['email']) . '</a>';
                    echo '</div>';
                    echo '<p class="text-sm font-light text-gray-500">' . date("M j, Y, g:i A", strtotime($row['submitted_at'])) . '</p>';
                    echo '</div>';
                    
                    echo '<p class="text-gray-700 whitespace-pre-wrap leading-relaxed">' . htmlspecialchars($row['message']) . '</p>';
                    
                    echo '</div>';
                }
            } else {
                echo '<div class="text-center p-12 bg-white rounded-lg shadow-md">';
                echo '<p class="text-gray-500 text-lg">No messages have been submitted yet.</p>';
                echo '</div>';
            }
            $conn->close();
            ?>
        </div>
    </div>
</body>
</html>