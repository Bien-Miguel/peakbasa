<?php
session_start();
require 'vendor/autoload.php'; // make sure this path is correct

use Dompdf\Dompdf;

// Get score and total questions from URL parameters
$score = isset($_GET['score']) ? intval($_GET['score']) : 0;
$total = isset($_GET['total']) ? intval($_GET['total']) : 10;

// Get username from session
$username = $_SESSION['username'] ?? 'Student';
$percentage = round(($score / $total) * 100);

// HTML template for PDF
$html = "
<h1>📚 Student Score Report</h1>
<p><strong>Student:</strong> $username</p>
<p><strong>Score:</strong> $score / $total ($percentage%)</p>
<p>Date: " . date('Y-m-d H:i:s') . "</p>
";

// Generate PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("Score_Report_$username.pdf", ["Attachment" => false]); // opens in browser
?>
