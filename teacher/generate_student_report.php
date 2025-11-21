<?php
require_once __DIR__ . '/../vendor/autoload.php'; // path to Dompdf autoload
use Dompdf\Dompdf;
use Dompdf\Options;

require_once '../conn.php'; // Database connection

// --- 1. START SESSION & SECURITY CHECK ---
session_start();
if (empty($_SESSION['teacher_id'])) {
    // Redirect non-teachers if they try to access this directly
    header("Location: ../Verification/teacher_login.php"); 
    exit;
}

// --- 2. GET USER ID ---
$user_id = $_POST['user_id'] ?? null;

if (!$user_id || !filter_var($user_id, FILTER_VALIDATE_INT)) {
    die("Invalid or missing student ID.");
}

// --- 3. FETCH STUDENT DATA ---
// Get student info
$user_sql = "SELECT username, COALESCE(total_score, 0) AS total_score, COALESCE(current_level, 1) AS current_level FROM users WHERE user_id = ? AND role = 'student'";
$stmt_user = $conn->prepare($user_sql);
if (!$stmt_user) {
    die("Error preparing user query: " . $conn->error);
}
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user_result = $stmt_user->get_result();

if ($user_result->num_rows === 0) {
    die("Student not found.");
}
$user = $user_result->fetch_assoc();
$stmt_user->close();

// Get quiz history
$quiz_sql = "SELECT level, quiz_number, stars_earned FROM user_scores WHERE user_id = ? ORDER BY level, quiz_number";
$stmt_quiz = $conn->prepare($quiz_sql);
if (!$stmt_quiz) {
    die("Error preparing quiz query: " . $conn->error);
}
$stmt_quiz->bind_param("i", $user_id);
$stmt_quiz->execute();
$quiz_results = $stmt_quiz->get_result();
$quizzes = [];
while ($row = $quiz_results->fetch_assoc()) {
    $quizzes[] = $row;
}
$stmt_quiz->close();
$conn->close();

// --- 4. BUILD STYLED HTML FOR PDF ---

// Define Logo Path (adjust if necessary)
// IMPORTANT: Use an absolute path or a path accessible by the server where Dompdf runs.
// For simplicity, using a relative path assuming it's accessible. Consider Base64 encoding if paths are tricky.
$logoPath = '../ui/Illustration17.png'; // Make sure this path is correct relative to THIS script
$logoData = base64_encode(file_get_contents($logoPath)); // Encode image to embed it
$logoSrc = 'data:image/png;base64,' . $logoData;

$html = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Progress Report</title>
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap");

        body {
            font-family: "Poppins", sans-serif;
            font-size: 11pt;
            color: #333;
            line-height: 1.6;
        }
        .container {
            width: 90%;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #ec5757;
            padding-bottom: 15px;
            position: relative; /* Needed for absolute positioning of logo */
        }
        .header img {
            position: absolute; /* Position logo */
            top: 0;
            left: 0;
            width: 60px; /* Adjust size */
            height: 60px;
        }
        .header h1 {
            color: #ec5757;
            margin: 10px 0 5px; /* Adjust margins for logo */
            font-size: 20pt;
            font-weight: 700;
        }
        .student-info {
            background-color: #fdf0f0;
            border-left: 5px solid #ec5757;
            padding: 15px 20px;
            margin-bottom: 25px;
            border-radius: 8px;
        }
        .student-info p {
            margin: 5px 0;
            font-size: 11pt;
        }
        .student-info strong {
            display: inline-block;
            width: 120px; /* Align labels */
            color: #555;
        }
        h2 {
            color: #ec5757;
            font-size: 14pt;
            margin-bottom: 15px;
            border-bottom: 1px solid #fdecec;
            padding-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1); /* Subtle shadow */
        }
        th, td {
            border: 1px solid #f0f0f0;
            padding: 10px 12px;
            text-align: left;
        }
        th {
            background-color: #fdf6f6;
            color: #7c4646;
            font-weight: 600;
            font-size: 10pt;
            text-transform: uppercase;
        }
        td {
            font-size: 10pt;
        }
        tr:nth-child(even) {
            background-color: #fcfcfc;
        }
        .footer {
            text-align: center;
            font-size: 9pt;
            color: #888;
            margin-top: 30px;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="' . $logoSrc . '" alt="PeakBasa Logo">
            <h1>Student Progress Report</h1>
        </div>

        <div class="student-info">
            <p><strong>Student Name:</strong> ' . htmlspecialchars($user['username']) . '</p>
            <p><strong>Total Stars:</strong> ' . $user['total_score'] . ' ⭐</p>
            <p><strong>Highest Level Reached:</strong> Level ' . $user['current_level'] . '</p>
        </div>

        <h2>Quiz Performance</h2>';

if (!empty($quizzes)) {
    $html .= '
        <table>
            <thead>
                <tr>
                    <th>Level</th>
                    <th>Quiz Number</th>
                    <th>Stars Earned</th>
                </tr>
            </thead>
            <tbody>';

    foreach ($quizzes as $row) {
        $html .= '<tr>
                    <td>' . $row['level'] . '</td>
                    <td>' . $row['quiz_number'] . '</td>
                    <td>' . $row['stars_earned'] . ' ⭐</td>
                  </tr>';
    }

    $html .= '
            </tbody>
        </table>';
} else {
     $html .= '<p>No quiz data available for this student yet.</p>';
}

$html .= '
        <div class="footer">
            Generated on ' . date('F j, Y, g:i a') . ' by PeakBasa System
        </div>
    </div>
</body>
</html>';


// --- 5. CREATE AND OUTPUT PDF ---
$options = new Options();
$options->set('isRemoteEnabled', true); // Enable loading remote images/fonts if needed
$options->set('defaultFont', 'Poppins'); // Set default font

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);

// (Optional) Set paper size and orientation
$dompdf->setPaper('A4', 'portrait');

// Render the HTML as PDF
$dompdf->render();

// Output the generated PDF to Browser (inline view)
// Use "Attachment" => true to force download instead
$pdfFilename = preg_replace('/[^a-zA-Z0-9_ -]/', '', $user['username']) . "_Progress_Report.pdf"; // Sanitize filename
$dompdf->stream($pdfFilename, ["Attachment" => false]);
exit; // Important to prevent any further output
?>
